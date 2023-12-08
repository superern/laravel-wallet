<?php

declare(strict_types=1);

namespace Superern\Wallet\Services;

use Superern\Wallet\Internal\Assembler\TransactionQueryAssemblerInterface;
use Superern\Wallet\Internal\Assembler\TransferQueryAssemblerInterface;
use Superern\Wallet\Internal\Dto\TransactionDtoInterface;
use Superern\Wallet\Internal\Dto\TransferDtoInterface;
use Superern\Wallet\Internal\Repository\TransactionRepositoryInterface;
use Superern\Wallet\Internal\Repository\TransferRepositoryInterface;
use Superern\Wallet\Models\Transaction;
use Superern\Wallet\Models\Transfer;

/**
 * @internal
 */
final class AtmService implements AtmServiceInterface
{
    public function __construct(
        private readonly TransactionQueryAssemblerInterface $transactionQueryAssembler,
        private readonly TransferQueryAssemblerInterface $transferQueryAssembler,
        private readonly TransactionRepositoryInterface $transactionRepository,
        private readonly TransferRepositoryInterface $transferRepository,
        private readonly AssistantServiceInterface $assistantService
    ) {
    }

    /**
     * @param non-empty-array<array-key, TransactionDtoInterface> $objects
     *
     * @return non-empty-array<string, Transaction>
     */
    public function makeTransactions(array $objects): array
    {
        if (count($objects) === 1) {
            $items = [$this->transactionRepository->insertOne(reset($objects))];
        } else {
            $this->transactionRepository->insert($objects);
            $uuids = $this->assistantService->getUuids($objects);
            $query = $this->transactionQueryAssembler->create($uuids);
            $items = $this->transactionRepository->findBy($query);
        }

        assert($items !== []);

        $results = [];
        foreach ($items as $item) {
            $results[$item->uuid] = $item;
        }

        return $results;
    }

    /**
     * @param non-empty-array<array-key, TransferDtoInterface> $objects
     *
     * @return non-empty-array<string, Transfer>
     */
    public function makeTransfers(array $objects): array
    {
        if (count($objects) === 1) {
            $items = [$this->transferRepository->insertOne(reset($objects))];
        } else {
            $this->transferRepository->insert($objects);
            $uuids = $this->assistantService->getUuids($objects);
            $query = $this->transferQueryAssembler->create($uuids);
            $items = $this->transferRepository->findBy($query);
        }

        assert($items !== []);

        $results = [];
        foreach ($items as $item) {
            $results[$item->uuid] = $item;
        }

        return $results;
    }
}
