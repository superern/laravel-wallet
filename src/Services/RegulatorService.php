<?php

declare(strict_types=1);

namespace Superern\Wallet\Services;

use Superern\Wallet\Internal\Assembler\BalanceUpdatedEventAssemblerInterface;
use Superern\Wallet\Internal\Exceptions\RecordNotFoundException;
use Superern\Wallet\Internal\Repository\WalletRepositoryInterface;
use Superern\Wallet\Internal\Service\DispatcherServiceInterface;
use Superern\Wallet\Internal\Service\LockServiceInterface;
use Superern\Wallet\Internal\Service\MathServiceInterface;
use Superern\Wallet\Internal\Service\StorageServiceInterface;
use Superern\Wallet\Models\Wallet;
use PHPUnit\Framework\Attributes\CodeCoverageIgnore;

/**
 * @internal
 */
final class RegulatorService implements RegulatorServiceInterface
{
    /**
     * @var array<string, Wallet>
     */
    private array $wallets = [];

    /**
     * @var array<string, string>
     */
    private array $multiIncrease = [];

    public function __construct(
        private readonly BalanceUpdatedEventAssemblerInterface $balanceUpdatedEventAssembler,
        private readonly BookkeeperServiceInterface $bookkeeperService,
        private readonly DispatcherServiceInterface $dispatcherService,
        private readonly StorageServiceInterface $storageService,
        private readonly MathServiceInterface $mathService,
        private readonly LockServiceInterface $lockService,
        private readonly WalletRepositoryInterface $walletRepository
    ) {
    }

    #[CodeCoverageIgnore]
    public function missing(Wallet $wallet): bool
    {
        return $this->forget($wallet);
    }

    public function forget(Wallet $wallet): bool
    {
        unset($this->wallets[$wallet->uuid]);

        return $this->storageService->forget($wallet->uuid);
    }

    public function diff(Wallet $wallet): string
    {
        try {
            return $this->mathService->round($this->storageService->get($wallet->uuid));
        } catch (RecordNotFoundException) {
            return '0';
        }
    }

    public function amount(Wallet $wallet): string
    {
        return $this->mathService->round(
            $this->mathService->add($this->bookkeeperService->amount($wallet), $this->diff($wallet))
        );
    }

    public function sync(Wallet $wallet, float|int|string $value): bool
    {
        $this->persist($wallet);

        return $this->storageService->sync(
            $wallet->uuid,
            $this->mathService->round(
                $this->mathService->negative($this->mathService->sub($this->amount($wallet), $value))
            )
        );
    }

    public function increase(Wallet $wallet, float|int|string $value): string
    {
        $this->persist($wallet);

        try {
            $this->storageService->increase($wallet->uuid, $value);
        } catch (RecordNotFoundException) {
            $value = $this->mathService->round($value);
            $this->storageService->sync($wallet->uuid, $value);
        }

        return $this->amount($wallet);
    }

    public function decrease(Wallet $wallet, float|int|string $value): string
    {
        return $this->increase($wallet, $this->mathService->negative($value));
    }

    public function committing(): void
    {
        $balances = [];
        $incrementValues = [];
        foreach ($this->wallets as $wallet) {
            $diffValue = $this->diff($wallet);
            if ($this->mathService->compare($diffValue, 0) === 0) {
                continue;
            }

            $balances[$wallet->getKey()] = $this->amount($wallet);
            $incrementValues[$wallet->uuid] = $this->diff($wallet);
        }

        if ($balances === [] || $incrementValues === [] || $this->wallets === []) {
            return;
        }

        $this->walletRepository->updateBalances($balances);
        $this->multiIncrease = $this->bookkeeperService->multiIncrease($this->wallets, $incrementValues);
    }

    public function committed(): void
    {
        try {
            foreach ($this->multiIncrease as $uuid => $balance) {
                $wallet = $this->wallets[$uuid];

                $wallet->fill([
                    'balance' => $balance,
                ])->syncOriginalAttribute('balance');

                $event = $this->balanceUpdatedEventAssembler->create($wallet);
                $this->dispatcherService->dispatch($event);
            }
        } finally {
            $this->dispatcherService->flush();
            $this->purge();
        }
    }

    public function purge(): void
    {
        try {
            $this->lockService->releases(array_keys($this->wallets));
            $this->multiIncrease = [];
            foreach ($this->wallets as $wallet) {
                $this->forget($wallet);
            }
        } finally {
            $this->dispatcherService->forgot();
        }
    }

    private function persist(Wallet $wallet): void
    {
        $this->wallets[$wallet->uuid] = $wallet;
    }
}
