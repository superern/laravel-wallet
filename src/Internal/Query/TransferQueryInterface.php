<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Query;

interface TransferQueryInterface
{
    /**
     * @return non-empty-array<int|string, string>
     */
    public function getUuids(): array;
}
