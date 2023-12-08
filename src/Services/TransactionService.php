<?php

declare(strict_types=1);

namespace Superern\Wallet\Services;

use Superern\Wallet\Interfaces\Wallet;
use Superern\Wallet\Internal\Assembler\TransactionCreatedEventAssemblerInterface;
use Superern\Wallet\Internal\Dto\TransactionDtoInterface;
use Superern\Wallet\Internal\Exceptions\RecordNotFoundException;
use Superern\Wallet\Internal\Service\DispatcherServiceInterface;
use Superern\Wallet\Models\Transaction;

/**
 * @internal
 */
final class TransactionService implements TransactionServiceInterface
{
    public function __construct(
        private readonly TransactionCreatedEventAssemblerInterface $transactionCreatedEventAssembler,
        private readonly DispatcherServiceInterface $dispatcherService,
        private readonly AssistantServiceInterface $assistantService,
        private readonly RegulatorServiceInterface $regulatorService,
        private readonly PrepareServiceInterface $prepareService,
        private readonly CastServiceInterface $castService,
        private readonly AtmServiceInterface $atmService,
    ) {
    }

    /**
     * @throws RecordNotFoundException
     */
    public function makeOne(
        Wallet $wallet,
        string $type,
        float|int|string $amount,
        ?array $meta,
        bool $confirmed = true
    ): Transaction {
        assert(in_array($type, [Transaction::TYPE_DEPOSIT, Transaction::TYPE_WITHDRAW], true));

        $dto = $type === Transaction::TYPE_DEPOSIT
            ? $this->prepareService->deposit($wallet, (string) $amount, $meta, $confirmed)
            : $this->prepareService->withdraw($wallet, (string) $amount, $meta, $confirmed);

        $transactions = $this->apply([
            $dto->getWalletId() => $wallet,
        ], [$dto]);

        return current($transactions);
    }

    /**
     * @param non-empty-array<int, Wallet> $wallets
     * @param non-empty-array<int, TransactionDtoInterface> $objects
     *
     * @throws RecordNotFoundException
     *
     * @return non-empty-array<string, Transaction>
     */
    public function apply(array $wallets, array $objects): array
    {
        $transactions = $this->atmService->makeTransactions($objects); // q1
        $totals = $this->assistantService->getSums($objects);
        assert(count($objects) === count($transactions));

        foreach ($totals as $walletId => $total) {
            $wallet = $wallets[$walletId] ?? null;
            assert($wallet instanceof Wallet);

            $object = $this->castService->getWallet($wallet);
            assert($object->getKey() === $walletId);

            $this->regulatorService->increase($object, $total);
        }

        foreach ($transactions as $transaction) {
            $this->dispatcherService->dispatch($this->transactionCreatedEventAssembler->create($transaction));
        }

        return $transactions;
    }
}
