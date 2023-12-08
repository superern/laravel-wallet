<?php

declare(strict_types=1);

namespace Superern\Wallet\Traits;

use Superern\Wallet\Exceptions\BalanceIsEmpty;
use Superern\Wallet\Exceptions\InsufficientFunds;
use Superern\Wallet\Exceptions\ProductEnded;
use Superern\Wallet\Interfaces\ProductInterface;
use Superern\Wallet\Internal\Exceptions\ExceptionInterface;
use Superern\Wallet\Internal\Exceptions\ModelNotFoundException;
use Superern\Wallet\Internal\Exceptions\RecordNotFoundException;
use Superern\Wallet\Internal\Exceptions\TransactionFailedException;
use Superern\Wallet\Models\Transfer;
use Superern\Wallet\Objects\Cart;
use Illuminate\Database\RecordsNotFoundException;
use function current;

/**
 * @psalm-require-extends \Illuminate\Database\Eloquent\Model
 * @psalm-require-implements \Superern\Wallet\Interfaces\Customer
 */
trait CanPay
{
    use CartPay;

    /**
     * @throws ProductEnded
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function payFree(ProductInterface $product): Transfer
    {
        return current($this->payFreeCart(app(Cart::class)->withItem($product)));
    }

    public function safePay(ProductInterface $product, bool $force = false): ?Transfer
    {
        return current($this->safePayCart(app(Cart::class)->withItem($product), $force)) ?: null;
    }

    /**
     * @throws ProductEnded
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function pay(ProductInterface $product, bool $force = false): Transfer
    {
        return current($this->payCart(app(Cart::class)->withItem($product), $force));
    }

    /**
     * @throws ProductEnded
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function forcePay(ProductInterface $product): Transfer
    {
        return current($this->forcePayCart(app(Cart::class)->withItem($product)));
    }

    public function safeRefund(ProductInterface $product, bool $force = false, bool $gifts = false): bool
    {
        return $this->safeRefundCart(app(Cart::class)->withItem($product), $force, $gifts);
    }

    /**
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ModelNotFoundException
     * @throws ExceptionInterface
     */
    public function refund(ProductInterface $product, bool $force = false, bool $gifts = false): bool
    {
        return $this->refundCart(app(Cart::class)->withItem($product), $force, $gifts);
    }

    /**
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ModelNotFoundException
     * @throws ExceptionInterface
     */
    public function forceRefund(ProductInterface $product, bool $gifts = false): bool
    {
        return $this->forceRefundCart(app(Cart::class)->withItem($product), $gifts);
    }

    public function safeRefundGift(ProductInterface $product, bool $force = false): bool
    {
        return $this->safeRefundGiftCart(app(Cart::class)->withItem($product), $force);
    }

    /**
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ModelNotFoundException
     * @throws ExceptionInterface
     */
    public function refundGift(ProductInterface $product, bool $force = false): bool
    {
        return $this->refundGiftCart(app(Cart::class)->withItem($product), $force);
    }

    /**
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ModelNotFoundException
     * @throws ExceptionInterface
     */
    public function forceRefundGift(ProductInterface $product): bool
    {
        return $this->forceRefundGiftCart(app(Cart::class)->withItem($product));
    }
}
