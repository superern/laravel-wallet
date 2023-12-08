<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Assembler;

use Superern\Wallet\External\Contracts\OptionDtoInterface;

interface OptionDtoAssemblerInterface
{
    /**
     * @param null|array<mixed> $data
     */
    public function create(array|null $data): OptionDtoInterface;
}
