<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Assembler;

use Superern\Wallet\External\Contracts\ExtraDtoInterface;
use Superern\Wallet\External\Dto\Extra;

final class ExtraDtoAssembler implements ExtraDtoAssemblerInterface
{
    public function __construct(
        private readonly OptionDtoAssemblerInterface $optionDtoAssembler
    ) {
    }

    public function create(ExtraDtoInterface|array|null $data): ExtraDtoInterface
    {
        if ($data instanceof ExtraDtoInterface) {
            return $data;
        }

        $option = $this->optionDtoAssembler->create($data);

        return new Extra($option, $option, null);
    }
}
