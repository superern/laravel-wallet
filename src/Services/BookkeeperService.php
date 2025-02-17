<?php

declare(strict_types=1);

namespace Superern\Wallet\Services;

use Superern\Wallet\Internal\Exceptions\RecordNotFoundException;
use Superern\Wallet\Internal\Service\LockServiceInterface;
use Superern\Wallet\Internal\Service\StorageServiceInterface;
use Superern\Wallet\Models\Wallet;
use PHPUnit\Framework\Attributes\CodeCoverageIgnore;

/**
 * @internal
 */
final class BookkeeperService implements BookkeeperServiceInterface
{
    public function __construct(
        private readonly StorageServiceInterface $storageService,
        private readonly LockServiceInterface $lockService
    ) {
    }

    #[CodeCoverageIgnore]
    public function missing(Wallet $wallet): bool
    {
        return $this->forget($wallet);
    }

    public function forget(Wallet $wallet): bool
    {
        return $this->storageService->forget($wallet->uuid);
    }

    /**
     * @throws RecordNotFoundException
     */
    public function amount(Wallet $wallet): string
    {
        return current($this->multiAmount([
            $wallet->uuid => $wallet,
        ]));
    }

    public function sync(Wallet $wallet, float|int|string $value): bool
    {
        return $this->multiSync([
            $wallet->uuid => $value,
        ]);
    }

    /**
     * @throws RecordNotFoundException
     */
    public function increase(Wallet $wallet, float|int|string $value): string
    {
        return current($this->multiIncrease([
            $wallet->uuid => $wallet,
        ], [
            $wallet->uuid => $value,
        ]));
    }

    public function multiAmount(array $wallets): array
    {
        try {
            return $this->storageService->multiGet(array_keys($wallets));
        } catch (RecordNotFoundException $recordNotFoundException) {
            $this->lockService->blocks(
                $recordNotFoundException->getMissingKeys(),
                function () use ($wallets, $recordNotFoundException) {
                    $balances = [];
                    foreach ($recordNotFoundException->getMissingKeys() as $uuid) {
                        $balances[$uuid] = $wallets[$uuid]->getOriginalBalanceAttribute();
                    }

                    $this->multiSync($balances);
                }
            );
        }

        return $this->storageService->multiGet(array_keys($wallets));
    }

    public function multiSync(array $balances): bool
    {
        return $this->storageService->multiSync($balances);
    }

    public function multiIncrease(array $wallets, array $incrementValues): array
    {
        try {
            return $this->storageService->multiIncrease($incrementValues);
        } catch (RecordNotFoundException $recordNotFoundException) {
            $objects = [];
            foreach ($recordNotFoundException->getMissingKeys() as $uuid) {
                $objects[$uuid] = $wallets[$uuid];
            }

            $this->multiAmount($objects);
        }

        return $this->storageService->multiIncrease($incrementValues);
    }
}
