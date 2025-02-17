<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Query;

/**
 * @immutable
 * @internal
 */
final class TransferQuery implements TransferQueryInterface
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
