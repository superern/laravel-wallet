<?php

declare(strict_types=1);

namespace Superern\Wallet\Services;

use Superern\Wallet\Internal\Assembler\TransferDtoAssemblerInterface;
use Superern\Wallet\Internal\Dto\TransferLazyDtoInterface;
use Superern\Wallet\Internal\Exceptions\ExceptionInterface;
use Superern\Wallet\Internal\Exceptions\RecordNotFoundException;
use Superern\Wallet\Internal\Exceptions\TransactionFailedException;
use Superern\Wallet\Internal\Repository\TransferRepositoryInterface;
use Superern\Wallet\Internal\Service\DatabaseServiceInterface;
use Superern\Wallet\Models\Transaction;
use Superern\Wallet\Models\Transfer;
use Illuminate\Database\RecordsNotFoundException;

/**
 * @internal
 */
final class TransferService implements TransferServiceInterface
{
    public function __construct(
        private readonly TransferDtoAssemblerInterface $transferDtoAssembler,
        private readonly TransferRepositoryInterface $transferRepository,
        private readonly TransactionServiceInterface $transactionService,
        private readonly DatabaseServiceInterface $databaseService,
        private readonly CastServiceInterface $castService,
        private readonly AtmServiceInterface $atmService,
    ) {
    }

    /**
     * @param int[] $ids
     */
    public function updateStatusByIds(string $status, array $ids): bool
    {
        return $ids !== [] && count($ids) === $this->transferRepository->updateStatusByIds($status, $ids);
    }

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
    public function apply(array $objects): array
    {
        return $this->databaseService->transaction(function () use ($objects): array {
            $wallets = [];
            $operations = [];
            foreach ($objects as $object) {
                $fromWallet = $this->castService->getWallet($object->getFromWallet());
                $wallets[$fromWallet->getKey()] = $fromWallet;

                $toWallet = $this->castService->getWallet($object->getToWallet());
                $wallets[$toWallet->getKey()] = $toWallet;

                $operations[] = $object->getWithdrawDto();
                $operations[] = $object->getDepositDto();
            }

            $transactions = $this->transactionService->apply($wallets, $operations);

            $links = [];
            $transfers = [];
            foreach ($objects as $object) {
                $withdraw = $transactions[$object->getWithdrawDto()->getUuid()] ?? null;
                assert($withdraw instanceof Transaction);

                $deposit = $transactions[$object->getDepositDto()->getUuid()] ?? null;
                assert($deposit instanceof Transaction);

                $fromWallet = $this->castService->getWallet($object->getFromWallet());
                $toWallet = $this->castService->getWallet($object->getToWallet());

                $transfer = $this->transferDtoAssembler->create(
                    $deposit->getKey(),
                    $withdraw->getKey(),
                    $object->getStatus(),
                    $fromWallet,
                    $toWallet,
                    $object->getDiscount(),
                    $object->getFee(),
                    $object->getUuid(),
                );

                $transfers[] = $transfer;
                $links[$transfer->getUuid()] = [
                    'deposit' => $deposit,
                    'withdraw' => $withdraw,
                    'from' => $fromWallet->withoutRelations(),
                    'to' => $toWallet->withoutRelations(),
                ];
            }

            $models = $this->atmService->makeTransfers($transfers);
            foreach ($models as $model) {
                $model->setRelations($links[$model->uuid] ?? []);
            }

            return $models;
        });
    }
}
