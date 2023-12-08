<?php

declare(strict_types=1);

namespace Superern\Wallet\Traits;

use Superern\Wallet\Exceptions\BalanceIsEmpty;
use Superern\Wallet\Exceptions\InsufficientFunds;
use Superern\Wallet\Interfaces\ProductInterface;
use Superern\Wallet\Interfaces\Wallet;
use Superern\Wallet\Internal\Assembler\TransferDtoAssemblerInterface;
use Superern\Wallet\Internal\Exceptions\ExceptionInterface;
use Superern\Wallet\Internal\Exceptions\TransactionFailedException;
use Superern\Wallet\Internal\Service\MathServiceInterface;
use Superern\Wallet\Models\Transaction;
use Superern\Wallet\Models\Transfer;
use Superern\Wallet\Services\AtmServiceInterface;
use Superern\Wallet\Services\AtomicServiceInterface;
use Superern\Wallet\Services\CastServiceInterface;
use Superern\Wallet\Services\ConsistencyServiceInterface;
use Superern\Wallet\Services\DiscountServiceInterface;
use Superern\Wallet\Services\TaxServiceInterface;
use Superern\Wallet\Services\TransactionServiceInterface;
use Illuminate\Database\RecordsNotFoundException;
use function app;

/**
 * Trait HasGift.
 *
 * @psalm-require-extends \Illuminate\Database\Eloquent\Model
 */
trait HasGift
{
    /**
     * Give the goods safely.
     */
    public function safeGift(Wallet $to, ProductInterface $product, bool $force = false): ?Transfer
    {
        try {
            return $this->gift($to, $product, $force);
        } catch (ExceptionInterface) {
            return null;
        }
    }

    /**
     * From this moment on, each user (wallet) can give the goods to another user (wallet). This functionality can be
     * organized for gifts.
     *
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function gift(Wallet $to, ProductInterface $product, bool $force = false): Transfer
    {
        return app(AtomicServiceInterface::class)->block($this, function () use ($to, $product, $force): Transfer {
            $mathService = app(MathServiceInterface::class);
            $discount = app(DiscountServiceInterface::class)->getDiscount($this, $product);
            $amount = $mathService->sub($product->getAmountProduct($this), $discount);
            $fee = app(TaxServiceInterface::class)->getFee($product, $amount);

            if (! $force) {
                app(ConsistencyServiceInterface::class)->checkPotential($this, $mathService->add($amount, $fee));
            }

            $transactionService = app(TransactionServiceInterface::class);
            $metaProduct = $product->getMetaProduct();
            $withdraw = $transactionService->makeOne(
                $this,
                Transaction::TYPE_WITHDRAW,
                $mathService->add($amount, $fee),
                $metaProduct
            );
            $deposit = $transactionService->makeOne($product, Transaction::TYPE_DEPOSIT, $amount, $metaProduct);

            $castService = app(CastServiceInterface::class);

            $transfer = app(TransferDtoAssemblerInterface::class)->create(
                $deposit->getKey(),
                $withdraw->getKey(),
                Transfer::STATUS_GIFT,
                $castService->getWallet($to),
                $castService->getWallet($product),
                $discount,
                $fee,
                null
            );

            $transfers = app(AtmServiceInterface::class)->makeTransfers([$transfer]);

            return current($transfers);
        });
    }

    /**
     * Santa without money gives a gift.
     *
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function forceGift(Wallet $to, ProductInterface $product): Transfer
    {
        return $this->gift($to, $product, true);
    }
}
