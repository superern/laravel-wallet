<?php

declare(strict_types=1);

namespace Superern\Wallet\Services;

use Superern\Wallet\Interfaces\ProductLimitedInterface;
use Superern\Wallet\Internal\Dto\AvailabilityDtoInterface;

/**
 * @internal
 */
final class BasketService implements BasketServiceInterface
{
    public function availability(AvailabilityDtoInterface $availabilityDto): bool
    {
        $basketDto = $availabilityDto->getBasketDto();
        $customer = $availabilityDto->getCustomer();
        foreach ($basketDto->items() as $itemDto) {
            $product = $itemDto->getProduct();
            if ($product instanceof ProductLimitedInterface && ! $product->canBuy(
                $customer,
                $itemDto->count(),
                $availabilityDto->isForce()
            )) {
                return false;
            }
        }

        return true;
    }
}
