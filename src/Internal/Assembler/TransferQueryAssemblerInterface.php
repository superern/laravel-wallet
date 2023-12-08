<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Assembler;

use Superern\Wallet\Internal\Query\TransferQueryInterface;

interface TransferQueryAssemblerInterface
{
    /**
     * @param non-empty-array<int|string, string> $uuids
     */
    public function create(array $uuids): TransferQueryInterface;
}
