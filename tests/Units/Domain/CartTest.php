<?php

declare(strict_types=1);

namespace Superern\Wallet\Test\Units\Domain;

use Superern\Wallet\Internal\Exceptions\CartEmptyException;
use Superern\Wallet\Internal\Exceptions\ExceptionInterface;
use Superern\Wallet\Internal\Exceptions\ModelNotFoundException;
use Superern\Wallet\Internal\Service\MathServiceInterface;
use Superern\Wallet\Models\Transfer;
use Superern\Wallet\Objects\Cart;
use Superern\Wallet\Services\PurchaseServiceInterface;
use Superern\Wallet\Test\Infra\Factories\BuyerFactory;
use Superern\Wallet\Test\Infra\Factories\ItemFactory;
use Superern\Wallet\Test\Infra\Factories\ItemMetaFactory;
use Superern\Wallet\Test\Infra\Models\Buyer;
use Superern\Wallet\Test\Infra\Models\Item;
use Superern\Wallet\Test\Infra\Models\ItemMeta;
use Superern\Wallet\Test\Infra\PackageModels\Transaction;
use Superern\Wallet\Test\Infra\TestCase;
use Illuminate\Database\Eloquent\Collection;
use function count;

/**
 * @internal
 */
final class CartTest extends TestCase
{
    public function testCartClone(): void
    {
        /** @var ItemMeta $product */
        $product = ItemMetaFactory::new()->create([
            'quantity' => 1,
        ]);

        $cart = app(Cart::class);

        $cartWithItems = $cart->withItems([$product]);
        $cartWithMeta = $cart->withMeta([
            'product_id' => $product->getKey(),
        ]);

        self::assertCount(0, $cart->getItems());
        self::assertCount(1, $cartWithItems->getItems());

        self::assertSame([], $cart->getMeta());
        self::assertSame([
            'product_id' => $product->getKey(),
        ], $cartWithMeta->getMeta());
    }

    public function testCartMeta(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        /** @var ItemMeta $product */
        $product = ItemMetaFactory::new()->create([
            'quantity' => 1,
        ]);

        $expected = 'pay';

        $cart = app(Cart::class)
            ->withItems([$product])
            ->withMeta([
                'type' => $expected,
            ])
        ;

        self::assertSame(0, $buyer->balanceInt);
        self::assertNotNull($buyer->deposit($cart->getTotal($buyer)));

        $transfers = $buyer->payCart($cart);
        self::assertCount(1, $transfers);

        $transfer = current($transfers);

        /** @var Transaction[] $transactions */
        $transactions = [$transfer->deposit, $transfer->withdraw];
        foreach ($transactions as $transaction) {
            self::assertSame($product->price, $transaction->meta['price']);
            self::assertSame($product->name, $transaction->meta['name']);
            self::assertSame($expected, $transaction->meta['type']);
        }
    }

    public function testCartGetBasketDtoCartEmpty(): void
    {
        $this->expectException(CartEmptyException::class);
        $this->expectExceptionCode(ExceptionInterface::CART_EMPTY);
        app(Cart::class)->getBasketDto();
    }

    public function testCartMetaItemNoMeta(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        /** @var Item $product */
        $product = ItemFactory::new()->create([
            'quantity' => 1,
        ]);

        $expected = 'pay';

        $cart = app(Cart::class)
            ->withItems([$product])
            ->withMeta([
                'type' => $expected,
            ])
        ;

        self::assertSame(0, $buyer->balanceInt);
        self::assertNotNull($buyer->deposit($cart->getTotal($buyer)));

        $transfers = $buyer->payCart($cart);
        self::assertCount(1, $transfers);

        $transfer = current($transfers);

        /** @var Transaction[] $transactions */
        $transactions = [$transfer->deposit, $transfer->withdraw];
        foreach ($transactions as $transaction) {
            self::assertCount(1, $transaction->meta);
            self::assertSame($expected, $transaction->meta['type']);
        }
    }

    public function testPay(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        /** @var Collection<int, Item> $products */
        $products = ItemFactory::times(10)->create([
            'quantity' => 1,
        ]);

        $cart = app(Cart::class)->withItems($products);
        foreach ($cart->getItems() as $product) {
            self::assertSame(0, $product->getBalanceIntAttribute());
        }

        self::assertSame($buyer->balance, $buyer->wallet->balance);
        self::assertNotNull($buyer->deposit($cart->getTotal($buyer)));
        self::assertSame($buyer->balance, $buyer->wallet->balance);

        $transfers = $buyer->payCart($cart);
        self::assertCount(count($cart), $transfers);
        self::assertTrue((bool) app(PurchaseServiceInterface::class)->already($buyer, $cart->getBasketDto()));
        self::assertSame(0, $buyer->balanceInt);

        foreach ($transfers as $transfer) {
            self::assertSame(Transfer::STATUS_PAID, $transfer->status);
            self::assertNull($transfer->status_last);
        }

        foreach ($cart->getItems() as $product) {
            /** @var Item $product */
            self::assertSame($product->balance, (string) $product->getAmountProduct($buyer));
        }

        self::assertTrue($buyer->refundCart($cart));
        foreach ($transfers as $transfer) {
            $transfer->refresh();
            self::assertSame(Transfer::STATUS_REFUND, $transfer->status);
            self::assertSame(Transfer::STATUS_PAID, $transfer->status_last);
        }
    }

    public function testCartQuantity(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        /** @var Collection<int, Item> $products */
        $products = ItemFactory::times(10)->create([
            'quantity' => 10,
        ]);

        $cart = app(Cart::class);
        $amount = 0;
        $price = 0;
        $productsCount = count($products);
        for ($i = 0; $i < $productsCount - 1; ++$i) {
            self::assertNotNull($products[$i]);

            $rnd = random_int(1, 5);
            $cart = $cart->withItem($products[$i], $rnd);
            $price += $products[$i]->getAmountProduct($buyer) * $rnd;
            $amount += $rnd;
        }

        $buyer->deposit($price);
        self::assertCount($amount, $cart->getItems());

        $transfers = $buyer->payCart($cart);
        self::assertCount($amount, $transfers);

        self::assertTrue($buyer->refundCart($cart));
        foreach ($transfers as $transfer) {
            $transfer->refresh();
            self::assertSame(Transfer::STATUS_REFUND, $transfer->status);
        }
    }

    public function testModelNotFoundException(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionCode(ExceptionInterface::MODEL_NOT_FOUND);
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        /** @var Collection<int, Item> $products */
        $products = ItemFactory::times(10)->create([
            'quantity' => 10,
        ]);

        $cart = app(Cart::class);
        $total = 0;
        $productsCount = count($products);
        for ($i = 0; $i < $productsCount - 1; ++$i) {
            self::assertNotNull($products[$i]);

            $rnd = random_int(1, 5);
            $cart = $cart->withItem($products[$i], $rnd);
            $buyer->deposit($products[$i]->getAmountProduct($buyer) * $rnd);
            $total += $rnd;
        }

        self::assertCount($total, $cart->getItems());
        self::assertCount(count($products) - 1, $cart->getBasketDto()->items());
        self::assertCount($total, iterator_to_array($cart->getBasketDto()->cursor()));
        self::assertSame($total, $cart->getBasketDto()->total());

        $transfers = $buyer->payCart($cart);
        self::assertCount($total, $transfers);

        $refundCart = app(Cart::class)
            ->withItems($products) // all goods
        ;

        $buyer->refundCart($refundCart);
    }

    public function testBoughtGoods(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        /** @var Collection<int, Item> $products */
        $products = ItemFactory::times(10)->create([
            'quantity' => 10,
        ]);

        $cart = app(Cart::class);
        $total = [];
        foreach ($products as $product) {
            $quantity = random_int(1, 5);
            $cart = $cart->withItem($product, $quantity);
            $buyer->deposit($product->getAmountProduct($buyer) * $quantity);
            $total[$product->getKey()] = $quantity;
        }

        $transfers = $buyer->payCart($cart);
        self::assertCount(array_sum($total), $transfers);

        foreach ($products as $product) {
            $count = $product
                ->boughtGoods([$buyer->wallet->getKey()])
                ->count()
            ;

            self::assertSame($total[$product->getKey()], $count);
        }
    }

    /**
     * @see https://github.com/superern/laravel-wallet/issues/279
     */
    public function testWithdrawal(): void
    {
        $transactionLevel = Buyer::query()->getConnection()->transactionLevel();
        self::assertSame(0, $transactionLevel);

        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        /** @var Item $product */
        $product = ItemFactory::new()->create([
            'quantity' => 1,
        ]);

        $cart = app(Cart::class)->withItem($product, 1);

        foreach ($cart->getItems() as $item) {
            self::assertSame(0, $item->getBalanceIntAttribute());
        }

        $math = app(MathServiceInterface::class);

        self::assertSame($buyer->balance, $buyer->wallet->balance);
        self::assertNotNull($buyer->deposit($cart->getTotal($buyer)));
        self::assertSame(0, $math->compare($cart->getTotal($buyer), $buyer->balance));
        self::assertSame($buyer->balance, $buyer->wallet->balance);

        $transfers = $buyer->payCart($cart);
        self::assertCount(count($cart), $transfers);
        self::assertTrue((bool) app(PurchaseServiceInterface::class)->already($buyer, $cart->getBasketDto()));
        self::assertSame(0, $buyer->balanceInt);

        foreach ($transfers as $transfer) {
            self::assertSame(Transfer::STATUS_PAID, $transfer->status);
        }

        foreach ($cart->getItems() as $product) {
            /** @var Item $product */
            self::assertSame($product->balance, (string) $product->getAmountProduct($buyer));
        }

        self::assertTrue($buyer->refundCart($cart));
        self::assertSame(0, $math->compare($cart->getTotal($buyer), $buyer->balance));
        self::assertSame($transactionLevel, $buyer->getConnection()->transactionLevel()); // check case #1

        foreach ($transfers as $transfer) {
            $transfer->refresh();
            self::assertSame(Transfer::STATUS_REFUND, $transfer->status);
        }

        $withdraw = $buyer->withdraw($buyer->balance); // problem place... withdrawal
        self::assertNotNull($withdraw);
        self::assertSame(0, $buyer->balanceInt);

        // check in the database
        /** @var string $balance */
        $balance = $buyer->wallet::query()
            ->whereKey($buyer->wallet->getKey())
            ->getQuery()
            ->value('balance')
        ;

        self::assertSame(0, (int) $balance);
    }
}
