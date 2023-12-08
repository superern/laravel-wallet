<?php

declare(strict_types=1);

namespace Superern\Wallet\Interfaces;

use Superern\Wallet\Exceptions\BalanceIsEmpty;
use Superern\Wallet\Exceptions\ConfirmedInvalid;
use Superern\Wallet\Exceptions\InsufficientFunds;
use Superern\Wallet\Exceptions\UnconfirmedInvalid;
use Superern\Wallet\Exceptions\WalletOwnerInvalid;
use Superern\Wallet\Internal\Exceptions\ExceptionInterface;
use Superern\Wallet\Internal\Exceptions\RecordNotFoundException;
use Superern\Wallet\Internal\Exceptions\TransactionFailedException;
use Superern\Wallet\Models\Transaction;
use Illuminate\Database\RecordsNotFoundException;

interface Confirmable
{
    /**
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws ConfirmedInvalid
     * @throws WalletOwnerInvalid
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function confirm(Transaction $transaction): bool;

    public function safeConfirm(Transaction $transaction): bool;

    /**
     * @throws UnconfirmedInvalid
     * @throws WalletOwnerInvalid
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function resetConfirm(Transaction $transaction): bool;

    public function safeResetConfirm(Transaction $transaction): bool;

    /**
     * @throws ConfirmedInvalid
     * @throws WalletOwnerInvalid
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function forceConfirm(Transaction $transaction): bool;
}
