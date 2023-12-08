<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Repository;

use Superern\Wallet\Internal\Dto\TransferDtoInterface;
use Superern\Wallet\Internal\Query\TransferQueryInterface;
use Superern\Wallet\Models\Transfer;

interface TransferRepositoryInterface
{
    /**
     * @param non-empty-array<int|string, TransferDtoInterface> $objects
     */
    public function insert(array $objects): void;

    public function insertOne(TransferDtoInterface $dto): Transfer;

    /**
     * @return Transfer[]
     */
    public function findBy(TransferQueryInterface $query): array;

    /**
     * @param non-empty-array<int> $ids
     */
    public function updateStatusByIds(string $status, array $ids): int;
}
