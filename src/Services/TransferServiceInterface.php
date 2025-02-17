<?php

declare(strict_types=1);

namespace Superern\Wallet\Services;

use Superern\Wallet\Internal\Dto\TransferLazyDtoInterface;
use Superern\Wallet\Internal\Exceptions\ExceptionInterface;
use Superern\Wallet\Internal\Exceptions\RecordNotFoundException;
use Superern\Wallet\Internal\Exceptions\TransactionFailedException;
use Superern\Wallet\Models\Transfer;
use Illuminate\Database\RecordsNotFoundException;

/**
 * @api
 */
interface TransferServiceInterface
{
    /**
     * @param int[] $ids
     */
    public function updateStatusByIds(string $status, array $ids): bool;

    /**
     * @param non-empty-array<TransferLazyDtoInterface> $objects
     *
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     *
     * @return non-empty-array<string, Transfer>
     */
    public function apply(array $objects): array;
}
