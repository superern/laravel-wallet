<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Repository;

use Superern\Wallet\Internal\Dto\TransactionDtoInterface;
use Superern\Wallet\Internal\Query\TransactionQueryInterface;
use Superern\Wallet\Models\Transaction;

interface TransactionRepositoryInterface
{
    /**
     * @param non-empty-array<int|string, TransactionDtoInterface> $objects
     */
    public function insert(array $objects): void;

    public function insertOne(TransactionDtoInterface $dto): Transaction;

    /**
     * @return Transaction[]
     */
    public function findBy(TransactionQueryInterface $query): array;
}
