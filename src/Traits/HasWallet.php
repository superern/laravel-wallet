<?php

declare(strict_types=1);

namespace Superern\Wallet\Traits;

use Superern\Wallet\Exceptions\AmountInvalid;
use Superern\Wallet\Exceptions\BalanceIsEmpty;
use Superern\Wallet\Exceptions\InsufficientFunds;
use Superern\Wallet\External\Contracts\ExtraDtoInterface;
use Superern\Wallet\Interfaces\Wallet;
use Superern\Wallet\Internal\Exceptions\ExceptionInterface;
use Superern\Wallet\Internal\Exceptions\TransactionFailedException;
use Superern\Wallet\Internal\Service\MathServiceInterface;
use Superern\Wallet\Models\Transaction;
use Superern\Wallet\Models\Transfer;
use Superern\Wallet\Models\Wallet as WalletModel;
use Superern\Wallet\Services\AtomicServiceInterface;
use Superern\Wallet\Services\CastServiceInterface;
use Superern\Wallet\Services\ConsistencyServiceInterface;
use Superern\Wallet\Services\PrepareServiceInterface;
use Superern\Wallet\Services\RegulatorServiceInterface;
use Superern\Wallet\Services\TransactionServiceInterface;
use Superern\Wallet\Services\TransferServiceInterface;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\RecordsNotFoundException;
use function app;
use function config;

/**
 * Trait HasWallet.
 *
 * @property WalletModel $wallet
 * @property string $balance
 * @property int $balanceInt
 * @psalm-require-extends \Illuminate\Database\Eloquent\Model
 * @psalm-require-implements \Superern\Wallet\Interfaces\Wallet
 */
trait HasWallet
{
    use MorphOneWallet;

    /**
     * The input means in the system.
     *
     * @throws AmountInvalid
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function deposit(int|string $amount, ?array $meta = null, bool $confirmed = true): Transaction
    {
        return app(AtomicServiceInterface::class)->block(
            $this,
            fn () => app(TransactionServiceInterface::class)
                ->makeOne($this, Transaction::TYPE_DEPOSIT, $amount, $meta, $confirmed)
        );
    }

    /**
     * Magic laravel framework method, makes it possible to call property balance.
     */
    public function getBalanceAttribute(): string
    {
        /** @var Wallet $this */
        return app(RegulatorServiceInterface::class)->amount(app(CastServiceInterface::class)->getWallet($this, false));
    }

    public function getBalanceIntAttribute(): int
    {
        return (int) $this->getBalanceAttribute();
    }

    /**
     * We receive transactions of the selected wallet.
     *
     * @return HasMany<Transaction>
     */
    public function walletTransactions(): HasMany
    {
        return app(CastServiceInterface::class)
            ->getWallet($this, false)
            ->hasMany(config('wallet.transaction.model', Transaction::class), 'wallet_id')
        ;
    }

    /**
     * all user actions on wallets will be in this method.
     *
     * @return MorphMany<Transaction>
     */
    public function transactions(): MorphMany
    {
        return app(CastServiceInterface::class)
            ->getHolder($this)
            ->morphMany(config('wallet.transaction.model', Transaction::class), 'payable')
        ;
    }

    /**
     * This method ignores errors that occur when transferring funds.
     */
    public function safeTransfer(
        Wallet $wallet,
        int|string $amount,
        ExtraDtoInterface|array|null $meta = null
    ): ?Transfer {
        try {
            return $this->transfer($wallet, $amount, $meta);
        } catch (ExceptionInterface) {
            return null;
        }
    }

    /**
     * A method that transfers funds from host to host.
     *
     * @throws AmountInvalid
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function transfer(Wallet $wallet, int|string $amount, ExtraDtoInterface|array|null $meta = null): Transfer
    {
        return app(AtomicServiceInterface::class)->block($this, function () use ($wallet, $amount, $meta): Transfer {
            /** @var Wallet $this */
            app(ConsistencyServiceInterface::class)->checkPotential($this, $amount);

            return $this->forceTransfer($wallet, $amount, $meta);
        });
    }

    /**
     * Withdrawals from the system.
     *
     * @throws AmountInvalid
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function withdraw(int|string $amount, ?array $meta = null, bool $confirmed = true): Transaction
    {
        return app(AtomicServiceInterface::class)->block($this, function () use (
            $amount,
            $meta,
            $confirmed
        ): Transaction {
            /** @var Wallet $this */
            app(ConsistencyServiceInterface::class)->checkPotential($this, $amount);

            return $this->forceWithdraw($amount, $meta, $confirmed);
        });
    }

    /**
     * Checks if you can withdraw funds.
     */
    public function canWithdraw(int|string $amount, bool $allowZero = false): bool
    {
        $mathService = app(MathServiceInterface::class);
        $wallet = app(CastServiceInterface::class)->getWallet($this);
        $balance = $mathService->add($this->getBalanceAttribute(), $wallet->getCreditAttribute());

        return app(ConsistencyServiceInterface::class)->canWithdraw($balance, $amount, $allowZero);
    }

    /**
     * Forced to withdraw funds from system.
     *
     * @throws AmountInvalid
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function forceWithdraw(
        int|string $amount,
        array|null $meta = null,
        bool $confirmed = true
    ): Transaction {
        return app(AtomicServiceInterface::class)->block(
            $this,
            fn () => app(TransactionServiceInterface::class)
                ->makeOne($this, Transaction::TYPE_WITHDRAW, $amount, $meta, $confirmed)
        );
    }

    /**
     * the forced transfer is needed when the user does not have the money, and we drive it. Sometimes you do. Depends
     * on business logic.
     *
     * @throws AmountInvalid
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function forceTransfer(
        Wallet $wallet,
        int|string $amount,
        ExtraDtoInterface|array|null $meta = null
    ): Transfer {
        return app(AtomicServiceInterface::class)->block($this, function () use ($wallet, $amount, $meta): Transfer {
            $transferLazyDto = app(PrepareServiceInterface::class)
                ->transferLazy($this, $wallet, Transfer::STATUS_TRANSFER, $amount, $meta)
            ;

            $transfers = app(TransferServiceInterface::class)->apply([$transferLazyDto]);

            return current($transfers);
        });
    }

    /**
     * the transfer table is used to confirm the payment this method receives all transfers.
     *
     * @return HasMany<Transfer>
     */
    public function transfers(): HasMany
    {
        /** @var Wallet $this */
        return app(CastServiceInterface::class)
            ->getWallet($this, false)
            ->hasMany(config('wallet.transfer.model', Transfer::class), 'from_id')
        ;
    }
}
