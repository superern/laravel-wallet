<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Dto;

use Superern\Wallet\Interfaces\Customer;

interface AvailabilityDtoInterface
{
    public function getBasketDto(): BasketDtoInterface;

    public function getCustomer(): Customer;

    public function isForce(): bool;
}
