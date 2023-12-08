<?php

declare(strict_types=1);

namespace Superern\Wallet\Services;

use Superern\Wallet\Interfaces\Wallet;

/**
 * @api
 */
interface DiscountServiceInterface
{
    public function getDiscount(Wallet $customer, Wallet $product): int;
}
