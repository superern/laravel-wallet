<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Assembler;

use Superern\Wallet\Interfaces\Customer;
use Superern\Wallet\Internal\Dto\AvailabilityDto;
use Superern\Wallet\Internal\Dto\AvailabilityDtoInterface;
use Superern\Wallet\Internal\Dto\BasketDtoInterface;

final class AvailabilityDtoAssembler implements AvailabilityDtoAssemblerInterface
{
    public function create(Customer $customer, BasketDtoInterface $basketDto, bool $force): AvailabilityDtoInterface
    {
        return new AvailabilityDto($customer, $basketDto, $force);
    }
}
