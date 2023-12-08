<?php

declare(strict_types=1);

namespace Superern\Wallet\External\Api;

use Superern\Wallet\Interfaces\Wallet;
use Superern\Wallet\Models\Transfer;
use Superern\Wallet\Services\AssistantServiceInterface;
use Superern\Wallet\Services\AtomicServiceInterface;
use Superern\Wallet\Services\PrepareServiceInterface;
use Superern\Wallet\Services\TransferServiceInterface;

/**
 * @internal
 */
final class TransferQueryHandler implements TransferQueryHandlerInterface
{
    public function __construct(
        private readonly AssistantServiceInterface $assistantService,
        private readonly TransferServiceInterface $transferService,
        private readonly PrepareServiceInterface $prepareService,
        private readonly AtomicServiceInterface $atomicService
    ) {
    }

    public function apply(array $objects): array
    {
        $wallets = $this->assistantService->getWallets(
            array_map(static fn (TransferQuery $query): Wallet => $query->getFrom(), $objects),
        );

        $values = array_map(
            fn (TransferQuery $query) => $this->prepareService->transferLazy(
                $query->getFrom(),
                $query->getTo(),
                Transfer::STATUS_TRANSFER,
                $query->getAmount(),
                $query->getMeta(),
            ),
            $objects
        );

        return $this->atomicService->blocks($wallets, fn () => $this->transferService->apply($values));
    }
}
