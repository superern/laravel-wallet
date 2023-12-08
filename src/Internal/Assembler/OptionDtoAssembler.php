<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Assembler;

use Superern\Wallet\External\Contracts\OptionDtoInterface;
use Superern\Wallet\External\Dto\Option;

final class OptionDtoAssembler implements OptionDtoAssemblerInterface
{
    public function create(array|null $data): OptionDtoInterface
    {
        return new Option($data);
    }
}
