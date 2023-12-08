<?php

declare(strict_types=1);

namespace Superern\Wallet\Services;

use Superern\Wallet\Interfaces\Customer;
use Superern\Wallet\Internal\Dto\BasketDtoInterface;
use Superern\Wallet\Models\Transfer;

/**
 * @api
 */
interface PurchaseServiceInterface
{
    /**
     * @return Transfer[]
     */
    public function already(Customer $customer, BasketDtoInterface $basketDto, bool $gifts = false): array;
}
