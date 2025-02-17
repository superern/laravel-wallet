<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Query;

/**
 * @immutable
 * @internal
 */
final class TransactionQuery implements TransactionQueryInterface
{
    /**
     * @param non-empty-array<int|string, string> $uuids
     */
    public function __construct(
        private readonly array $uuids
    ) {
    }

    /**
     * @return non-empty-array<int|string, string>
     */
    public function getUuids(): array
    {
        return $this->uuids;
    }
}
