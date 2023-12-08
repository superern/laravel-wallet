<?php

declare(strict_types=1);

namespace Superern\Wallet\Services;

use Superern\Wallet\Interfaces\Customer;
use Superern\Wallet\Internal\Dto\BasketDtoInterface;
use Superern\Wallet\Models\Transfer;

/**
 * @internal
 */
final class PurchaseService implements PurchaseServiceInterface
{
    public function __construct(
        private readonly CastServiceInterface $castService
    ) {
    }

    public function already(Customer $customer, BasketDtoInterface $basketDto, bool $gifts = false): array
    {
        $status = $gifts
            ? [Transfer::STATUS_PAID, Transfer::STATUS_GIFT]
            : [Transfer::STATUS_PAID];

        $arrays = [];
        $wallets = [];
        $productCounts = [];
        $query = $customer->transfers();
        foreach ($basketDto->items() as $itemDto) {
            $wallet = $this->castService->getWallet($itemDto->getReceiving() ?? $itemDto->getProduct());
            $wallets[$wallet->uuid] = $wallet;

            $productCounts[$wallet->uuid] = ($productCounts[$wallet->uuid] ?? 0) + count($itemDto);
        }

        foreach ($wallets as $wallet) {
            /**
             * As part of my work, "with" was added, it gives a 50x boost for a huge number of returns. In this case,
             * it's a crutch. It is necessary to come up with a more correct implementation of the internal and external
             * interface for "purchases".
             */
            $arrays[] = (clone $query)
                ->with(['deposit', 'withdraw.wallet'])
                ->where('to_id', $wallet->getKey())
                ->whereIn('status', $status)
                ->orderBy('id', 'desc')
                ->limit($productCounts[$wallet->uuid])
                ->get()
                ->all()
            ;
        }

        return array_merge(...$arrays);
    }
}
