<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Query;

interface TransactionQueryInterface
{
    /**
     * @return non-empty-array<int|string, string>
     */
    public function getUuids(): array;
}
