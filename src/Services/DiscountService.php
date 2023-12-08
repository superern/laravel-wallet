<?php

declare(strict_types=1);

namespace Superern\Wallet\Services;

use Superern\Wallet\Interfaces\Customer;
use Superern\Wallet\Interfaces\Discount;
use Superern\Wallet\Interfaces\Wallet;

/**
 * @internal
 */
final class DiscountService implements DiscountServiceInterface
{
    public function getDiscount(Wallet $customer, Wallet $product): int
    {
        if ($customer instanceof Customer && $product instanceof Discount) {
            return (int) $product->getPersonalDiscount($customer);
        }

        return 0;
    }
}
