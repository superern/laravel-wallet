<?php

declare(strict_types=1);

namespace Superern\Wallet\Services;

use Superern\Wallet\Internal\Dto\TransactionDtoInterface;
use Superern\Wallet\Internal\Dto\TransferDtoInterface;
use Superern\Wallet\Models\Transaction;
use Superern\Wallet\Models\Transfer;

/**
 * @api
 */
interface AtmServiceInterface
{
    /**
     * Helps to get to create a bunch of transaction objects.
     *
     * @param non-empty-array<array-key, TransactionDtoInterface> $objects
     *
     * @return non-empty-array<string, Transaction>
     */
    public function makeTransactions(array $objects): array;

    /**
     * Helps to get to create a bunch of transfer objects.
     *
     * @param non-empty-array<array-key, TransferDtoInterface> $objects
     *
     * @return non-empty-array<string, Transfer>
     */
    public function makeTransfers(array $objects): array;
}
