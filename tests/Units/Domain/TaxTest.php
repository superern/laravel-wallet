<?php

declare(strict_types=1);

namespace Superern\Wallet\Test\Units\Domain;

use Superern\Wallet\Exceptions\InsufficientFunds;
use Superern\Wallet\Internal\Exceptions\ExceptionInterface;
use Superern\Wallet\Internal\Service\MathServiceInterface;
use Superern\Wallet\Test\Infra\Factories\BuyerFactory;
use Superern\Wallet\Test\Infra\Factories\ItemTaxFactory;
use Superern\Wallet\Test\Infra\Models\Buyer;
use Superern\Wallet\Test\Infra\Models\ItemTax;
use Superern\Wallet\Test\Infra\TestCase;

/**
 * @internal
 */
final class TaxTest extends TestCase
{
    public function testPay(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        /** @var ItemTax $product */
        $product = ItemTaxFactory::new()->create([
            'quantity' => 1,
        ]);

        $math = app(MathServiceInterface::class);

        $fee = (int) $math->div($math->mul($product->getAmountProduct($buyer), $product->getFeePercent()), 100);
        $balance = (int) $math->add($product->getAmountProduct($buyer), $fee);

        self::assertSame(0, $buyer->balanceInt);
        $buyer->deposit($balance);

        self::assertNotSame(0, $buyer->balanceInt);
        $transfer = $buyer->pay($product);
        self::assertNotNull($transfer);

        $withdraw = $transfer->withdraw;
        $deposit = $transfer->deposit;

        self::assertSame($withdraw->amountInt, -$balance);
        self::assertSame($deposit->amountInt, $product->getAmountProduct($buyer));
        self::assertNotSame($deposit->amountInt, $withdraw->amountInt);
        self::assertSame((int) $transfer->fee, $fee);

        $buyer->refund($product);
        self::assertSame($buyer->balanceInt, $deposit->amountInt);
        self::assertSame($product->balanceInt, 0);

        $buyer->withdraw($buyer->balance);
        self::assertSame($buyer->balanceInt, 0);
    }

    public function testGift(): void
    {
        /**
         * @var Buyer $santa
         * @var Buyer $child
         */
        [$santa, $child] = BuyerFactory::times(2)->create();
        /** @var ItemTax $product */
        $product = ItemTaxFactory::new()->create([
            'quantity' => 1,
        ]);

        $math = app(MathServiceInterface::class);

        $fee = (int) $math->div($math->mul($product->getAmountProduct($santa), $product->getFeePercent()), 100);
        $balance = (int) $math->add($product->getAmountProduct($santa), $fee);

        self::assertSame($santa->balanceInt, 0);
        self::assertSame($child->balanceInt, 0);
        $santa->deposit($balance);

        self::assertNotSame($santa->balanceInt, 0);
        self::assertSame($child->balanceInt, 0);
        $transfer = $santa->wallet->gift($child, $product);
        self::assertNotNull($transfer);

        $withdraw = $transfer->withdraw;
        $deposit = $transfer->deposit;

        self::assertSame($withdraw->amountInt, -$balance);
        self::assertSame($deposit->amountInt, $product->getAmountProduct($santa));
        self::assertNotSame($deposit->amountInt, $withdraw->amountInt);
        self::assertSame($fee, (int) $transfer->fee);

        self::assertFalse($santa->safeRefundGift($product));
        self::assertTrue($child->refundGift($product));
        self::assertSame($santa->balanceInt, $deposit->amountInt);
        self::assertSame($child->balanceInt, 0);
        self::assertSame($product->balanceInt, 0);

        $santa->withdraw($santa->balance);
        self::assertSame($santa->balanceInt, 0);
    }

    public function testGiftFail(): void
    {
        $this->expectException(InsufficientFunds::class);
        $this->expectExceptionCode(ExceptionInterface::INSUFFICIENT_FUNDS);
        $this->expectExceptionMessageStrict(trans('wallet::errors.insufficient_funds'));

        /**
         * @var Buyer $santa
         * @var Buyer $child
         */
        [$santa, $child] = BuyerFactory::times(2)->create();
        /** @var ItemTax $product */
        $product = ItemTaxFactory::new()->create([
            'price' => 200,
            'quantity' => 1,
        ]);

        self::assertSame($santa->balanceInt, 0);
        self::assertSame($child->balanceInt, 0);
        $santa->deposit($product->getAmountProduct($santa));

        self::assertNotSame($santa->balanceInt, 0);
        self::assertSame($child->balanceInt, 0);
        $santa->wallet->gift($child, $product);

        self::assertSame($santa->balanceInt, 0);
    }
}
