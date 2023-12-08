<?php

declare(strict_types=1);

namespace Superern\Wallet\Services;

use Superern\Wallet\Internal\Assembler\WalletCreatedEventAssemblerInterface;
use Superern\Wallet\Internal\Exceptions\ModelNotFoundException;
use Superern\Wallet\Internal\Repository\WalletRepositoryInterface;
use Superern\Wallet\Internal\Service\DispatcherServiceInterface;
use Superern\Wallet\Internal\Service\UuidFactoryServiceInterface;
use Superern\Wallet\Models\Wallet;
use Illuminate\Database\Eloquent\Model;

/**
 * @internal
 */
final class WalletService implements WalletServiceInterface
{
    public function __construct(
        private readonly WalletCreatedEventAssemblerInterface $walletCreatedEventAssembler,
        private readonly UuidFactoryServiceInterface $uuidFactoryService,
        private readonly DispatcherServiceInterface $dispatcherService,
        private readonly WalletRepositoryInterface $walletRepository
    ) {
    }

    public function create(Model $model, array $data): Wallet
    {
        $wallet = $this->walletRepository->create(array_merge(
            config('wallet.wallet.creating', []),
            [
                'uuid' => $this->uuidFactoryService->uuid4(),
            ],
            $data,
            [
                'holder_type' => $model->getMorphClass(),
                'holder_id' => $model->getKey(),
            ]
        ));

        $event = $this->walletCreatedEventAssembler->create($wallet);
        $this->dispatcherService->dispatch($event);
        $this->dispatcherService->lazyFlush();

        return $wallet;
    }

    public function findBySlug(Model $model, string $slug): ?Wallet
    {
        return $this->walletRepository->findBySlug($model->getMorphClass(), $model->getKey(), $slug);
    }

    public function findByUuid(string $uuid): ?Wallet
    {
        return $this->walletRepository->findByUuid($uuid);
    }

    public function findById(int $id): ?Wallet
    {
        return $this->walletRepository->findById($id);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function getBySlug(Model $model, string $slug): Wallet
    {
        return $this->walletRepository->getBySlug($model->getMorphClass(), $model->getKey(), $slug);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function getByUuid(string $uuid): Wallet
    {
        return $this->walletRepository->getByUuid($uuid);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function getById(int $id): Wallet
    {
        return $this->walletRepository->getById($id);
    }
}
