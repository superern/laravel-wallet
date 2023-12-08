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
use Illuminate\Database\RecordsNotFoundException;

interface WalletFloat
{
    /**
     * @param null|array<mixed> $meta
     *
     * @throws AmountInvalid
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function depositFloat(float|int|string $amount, ?array $meta = null, bool $confirmed = true): Transaction;

    /**
     * @param null|array<mixed> $meta
     *
     * @throws AmountInvalid
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function withdrawFloat(float|int|string $amount, ?array $meta = null, bool $confirmed = true): Transaction;

    /**
     * @param null|array<mixed> $meta
     *
     * @throws AmountInvalid
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function forceWithdrawFloat(
        float|int|string $amount,
        ?array $meta = null,
        bool $confirmed = true
    ): Transaction;

    /**
     * @param null|array<mixed> $meta
     *
     * @throws AmountInvalid
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function transferFloat(
        Wallet $wallet,
        float|int|string $amount,
        ExtraDtoInterface|array|null $meta = null
    ): Transfer;

    /**
     * @param ExtraDtoInterface|array<mixed>|null $meta
     */
    public function safeTransferFloat(
        Wallet $wallet,
        float|int|string $amount,
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
    public function forceTransferFloat(
        Wallet $wallet,
        float|int|string $amount,
        ExtraDtoInterface|array|null $meta = null
    ): Transfer;

    public function canWithdrawFloat(float|int|string $amount): bool;

    public function getBalanceFloatAttribute(): string;

    public function getBalanceFloatNumAttribute(): float;
}
