<?php

declare(strict_types=1);

namespace Superern\Wallet\Interfaces;

use Superern\Wallet\Exceptions\AmountInvalid;
use Superern\Wallet\Exceptions\BalanceIsEmpty;
use Superern\Wallet\Exceptions\InsufficientFunds;
use Superern\Wallet\External\Contracts\ExtraDtoInterface;
use Superern\Wallet\Internal\Exceptions\ExceptionInterface;
use Superern\Wallet\Internal\Exceptions\TransactionFailedException;
use Superern\Wallet\Models\Transaction;
use Superern\Wallet\Models\Transfer;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\RecordsNotFoundException;

interface Wallet
{
    /**
     * @param array<mixed>|null $meta
     *
     * @throws AmountInvalid
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function deposit(int|string $amount, ?array $meta = null, bool $confirmed = true): Transaction;

    /**
     * @param array<mixed>|null $meta
     *
     * @throws AmountInvalid
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function withdraw(int|string $amount, ?array $meta = null, bool $confirmed = true): Transaction;

    /**
     * @param array<mixed>|null $meta
     *
     * @throws AmountInvalid
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function forceWithdraw(int|string $amount, ?array $meta = null, bool $confirmed = true): Transaction;

    /**
     * @param ExtraDtoInterface|array<mixed>|null $meta
     *
     * @throws AmountInvalid
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function transfer(self $wallet, int|string $amount, ExtraDtoInterface|array|null $meta = null): Transfer;

    /**
     * @param ExtraDtoInterface|array<mixed>|null $meta
     */
    public function safeTransfer(
        self $wallet,
        int|string $amount,
        ExtraDtoInterface|array|null $meta = null
    ): ?Transfer;

    /**
     * @param ExtraDtoInterface|array<mixed>|null $meta
     *
     * @throws AmountInvalid
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function forceTransfer(
        self $wallet,
        int|string $amount,
        ExtraDtoInterface|array|null $meta = null
    ): Transfer;

    public function canWithdraw(int|string $amount, bool $allowZero = false): bool;

    public function getBalanceAttribute(): string;

    public function getBalanceIntAttribute(): int;

    /**
     * @return HasMany<Transaction>
     */
    public function walletTransactions(): HasMany;

    /**
     * @return MorphMany<Transaction>
     */
    public function transactions(): MorphMany;

    /**
     * @return HasMany<Transfer>
     */
    public function transfers(): HasMany;
}
