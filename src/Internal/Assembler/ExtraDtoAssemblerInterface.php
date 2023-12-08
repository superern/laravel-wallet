<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Assembler;

use Superern\Wallet\External\Contracts\ExtraDtoInterface;

interface ExtraDtoAssemblerInterface
{
    /**
     * @param ExtraDtoInterface|array<mixed>|null $data
     */
    public function create(ExtraDtoInterface|array|null $data): ExtraDtoInterface;
}
