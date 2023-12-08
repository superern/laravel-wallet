<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Assembler;

use Superern\Wallet\Interfaces\Customer;
use Superern\Wallet\Internal\Dto\AvailabilityDtoInterface;
use Superern\Wallet\Internal\Dto\BasketDtoInterface;

interface AvailabilityDtoAssemblerInterface
{
    public function create(Customer $customer, BasketDtoInterface $basketDto, bool $force): AvailabilityDtoInterface;
}
