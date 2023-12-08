<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Assembler;

use Superern\Wallet\Internal\Query\TransactionQuery;
use Superern\Wallet\Internal\Query\TransactionQueryInterface;

final class TransactionQueryAssembler implements TransactionQueryAssemblerInterface
{
    /**
     * @param non-empty-array<int|string, string> $uuids
     */
    public function create(array $uuids): TransactionQueryInterface
    {
        return new TransactionQuery($uuids);
    }
}
