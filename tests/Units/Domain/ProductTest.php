<?php

declare(strict_types=1);

namespace Superern\Wallet\Test\Units\Domain;

use Superern\Wallet\Exceptions\ProductEnded;
use Superern\Wallet\Internal\Exceptions\ExceptionInterface;
use Superern\Wallet\Internal\Service\DatabaseServiceInterface;
use Superern\Wallet\Models\Transaction;
use Superern\Wallet\Models\Transfer;
use Superern\Wallet\Models\Wallet;
use Superern\Wallet\Objects\Cart;
use Superern\Wallet\Test\Infra\Factories\BuyerFactory;
use Superern\Wallet\Test\Infra\Factories\ItemFactory;
use Superern\Wallet\Test\Infra\Factories\ItemWalletFactory;
use Superern\Wallet\Test\Infra\Models\Buyer;
use Superern\Wallet\Test\Infra\Models\Item;
use Superern\Wallet\Test\Infra\Models\ItemWallet;
use Superern\Wallet\Test\Infra\TestCase;

/**
 * @internal
 */
final class ProductTest extends TestCase
{
    public function testPay(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        /** @var Item $product */
        $product = ItemFactory::new()->create([
            'quantity' => 1,
        ]);

        self::assertSame($buyer->balanceInt, 0);
        $buyer->deposit($product->getAmountProduct($buyer));

        self::assertSame($buyer->balanceInt, $product->getAmountProduct($buyer));
        $transfer = $buyer->pay($product);
        self::assertNotNull($transfer);
        self::assertSame($transfer->status, Transfer::STATUS_PAID);

        $withdraw = $transfer->withdraw;
        $deposit = $transfer->deposit;

        self::assertInstanceOf(Transaction::class, $withdraw);
        self::assertInstanceOf(Transaction::class, $deposit);

        self::assertInstanceOf(Buyer::class, $withdraw->payable);
        self::assertInstanceOf(Item::class, $deposit->payable);

        self::assertSame($buyer->getKey(), $withdraw->payable->getKey());
        self::assertSame($product->getKey(), $deposit->payable->getKey());

        self::assertInstanceOf(Buyer::class, $transfer->from->holder);
        self::assertInstanceOf(Wallet::class, $transfer->from);
        self::assertInstanceOf(Item::class, $transfer->to->holder);
        self::assertInstanceOf(Wallet::class, $transfer->to->wallet);

        self::assertSame($buyer->wallet->getKey(), $transfer->from->getKey());
        self::assertSame($buyer->getKey(), $transfer->from->holder->getKey());
        self::assertSame($product->wallet->getKey(), $transfer->to->getKey());
        self::assertSame($product->getKey(), $transfer->to->holder->getKey());

        self::assertSame(0, $buyer->balanceInt);
        self::assertNull($buyer->safePay($product));
    }

    public function testRefund(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        /** @var Item $product */
        $product = ItemFactory::new()->create([
            'quantity' => 1,
        ]);

        self::assertSame(0, $buyer->balanceInt);
        $buyer->deposit($product->getAmountProduct($buyer));

        self::assertSame($product->getAmountProduct($buyer), $buyer->balanceInt);
        $transfer = $buyer->pay($product);
        self::assertNotNull($transfer);
        self::assertSame(Transfer::STATUS_PAID, $transfer->status);

        self::assertTrue($buyer->refund($product));
        self::assertSame($product->getAmountProduct($buyer), $buyer->balanceInt);
        self::assertSame(0, $product->balanceInt);

        $transfer->refresh();
        self::assertSame(Transfer::STATUS_REFUND, $transfer->status);

        self::assertFalse($buyer->safeRefund($product));
        self::assertSame($product->getAmountProduct($buyer), $buyer->balanceInt);

        $transfer = $buyer->pay($product);
        self::assertNotNull($transfer);
        self::assertSame(0, $buyer->balanceInt);
        self::assertSame($product->getAmountProduct($buyer), $product->balanceInt);
        self::assertSame(Transfer::STATUS_PAID, $transfer->status);

        self::assertTrue($buyer->refund($product));
        self::assertSame($product->getAmountProduct($buyer), $buyer->balanceInt);
        self::assertSame(0, $product->balanceInt);

        $transfer->refresh();
        self::assertSame(Transfer::STATUS_REFUND, $transfer->status);
    }

    public function testForceRefund(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        /** @var Item $product */
        $product = ItemFactory::new()->create([
            'quantity' => 1,
        ]);

        self::assertSame(0, $buyer->balanceInt);
        $buyer->deposit($product->getAmountProduct($buyer));

        self::assertSame($product->getAmountProduct($buyer), $buyer->balanceInt);

        $buyer->pay($product);
        self::assertSame(0, $buyer->balanceInt);
        self::assertSame($product->getAmountProduct($buyer), $product->balanceInt);

        $product->withdraw($product->balanceInt);
        self::assertSame(0, $product->balanceInt);

        self::assertFalse($buyer->safeRefund($product));
        self::assertTrue($buyer->forceRefund($product));

        self::assertSame(-$product->getAmountProduct($buyer), $product->balanceInt);
        self::assertSame($product->getAmountProduct($buyer), $buyer->balanceInt);
        $product->deposit(-$product->balanceInt);
        $buyer->withdraw($buyer->balance);

        self::assertSame(0, $product->balanceInt);
        self::assertSame(0, $buyer->balanceInt);
    }

    public function testOutOfStock(): void
    {
        $this->expectException(ProductEnded::class);
        $this->expectExceptionCode(ExceptionInterface::PRODUCT_ENDED);
        $this->expectExceptionMessageStrict(trans('wallet::errors.product_stock'));

        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        /** @var Item $product */
        $product = ItemFactory::new()->create([
            'quantity' => 1,
        ]);

        $buyer->deposit($product->getAmountProduct($buyer));
        $buyer->pay($product);
        $buyer->pay($product);
    }

    public function testForcePay(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        /** @var Item $product */
        $product = ItemFactory::new()->create([
            'quantity' => 1,
        ]);

        self::assertSame(0, $buyer->balanceInt);
        $buyer->forcePay($product);

        self::assertSame(-$product->getAmountProduct($buyer), $buyer->balanceInt);

        $buyer->deposit(-$buyer->balanceInt);
        self::assertSame(0, $buyer->balanceInt);
    }

    public function testPayFree(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        /** @var Item $product */
        $product = ItemFactory::new()->create([
            'quantity' => 1,
        ]);

        self::assertSame(0, $buyer->balanceInt);

        $transfer = $buyer->payFree($product);
        self::assertSame(Transaction::TYPE_DEPOSIT, $transfer->deposit->type);
        self::assertSame(Transaction::TYPE_WITHDRAW, $transfer->withdraw->type);

        self::assertSame(0, $buyer->balanceInt);
        self::assertSame(0, $product->balanceInt);

        $buyer->refund($product);
        self::assertSame(0, $buyer->balanceInt);
        self::assertSame(0, $product->balanceInt);
    }

    public function testFreePay(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        /** @var Item $product */
        $product = ItemFactory::new()->create([
            'quantity' => 1,
        ]);

        $buyer->forceWithdraw(1000);
        self::assertSame(-1000, $buyer->balanceInt);

        $transfer = $buyer->payFree($product);
        self::assertSame(Transaction::TYPE_DEPOSIT, $transfer->deposit->type);
        self::assertSame(Transaction::TYPE_WITHDRAW, $transfer->withdraw->type);

        self::assertSame(-1000, $buyer->balanceInt);
        self::assertSame(0, $product->balanceInt);

        $buyer->refund($product);
        self::assertSame(-1000, $buyer->balanceInt);
        self::assertSame(0, $product->balanceInt);
    }

    public function testPayFreeOutOfStock(): void
    {
        $this->expectException(ProductEnded::class);
        $this->expectExceptionCode(ExceptionInterface::PRODUCT_ENDED);
        $this->expectExceptionMessageStrict(trans('wallet::errors.product_stock'));

        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        /** @var Item $product */
        $product = ItemFactory::new()->create([
            'quantity' => 1,
        ]);

        self::assertNotNull($buyer->payFree($product));
        $buyer->payFree($product);
    }

    public function testPayCustomPrice(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        /**
         * @var Item $productIn
         * @var Item $productOutside
         */
        [$productIn, $productOutside] = ItemFactory::times(2)->create([
            'quantity' => 2,
            'price' => 5_000,
        ]);

        self::assertSame(0, $buyer->balanceInt);

        $buyer->deposit(6_000 + (int) $buyer->getKey());
        self::assertSame(6_000 + (int) $buyer->getKey(), $buyer->balanceInt);

        $cart = app(Cart::class)
            ->withItem($productIn, pricePerItem: 1_000)
            ->withItem($productIn)
        ;

        self::assertSame(6_000 + (int) $buyer->getKey(), (int) $cart->getTotal($buyer));

        $transfers = $buyer->payCart($cart);
        self::assertSame(0, $cart->getQuantity($productOutside));
        self::assertSame(2, $cart->getQuantity($productIn));
        self::assertSame(0, $buyer->balanceInt);
        self::assertCount(2, $transfers);

        self::assertTrue($buyer->refundCart($cart));
    }

    /**
     * @see https://github.com/superern/laravel-wallet/issues/237
     */
    public function testProductMultiWallet(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        /** @var ItemWallet $product */
        $product = ItemWalletFactory::new()->create([
            'quantity' => 1,
        ]);

        self::assertSame(0, $buyer->balanceInt);
        $buyer->deposit($product->getAmountProduct($buyer));
        self::assertSame((string) $product->getAmountProduct($buyer), $buyer->balance);

        $product->createWallet([
            'name' => 'testing',
        ]);
        app(DatabaseServiceInterface::class)->transaction(function () use ($product, $buyer) {
            $transfer = $buyer->pay($product);
            $product->transfer($product->getWalletOrFail('testing'), $transfer->deposit->amount, $transfer->toArray());
        });

        self::assertSame(0, $product->balanceInt);
        self::assertSame(0, $buyer->balanceInt);
        self::assertSame((string) $product->getAmountProduct($buyer), $product->getWalletOrFail('testing')->balance);
    }
}
