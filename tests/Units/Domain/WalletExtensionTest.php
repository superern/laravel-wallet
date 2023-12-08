<?php

declare(strict_types=1);

namespace Superern\Wallet\Test\Units\Domain;

use Superern\Wallet\Internal\Transform\TransactionDtoTransformerInterface;
use Superern\Wallet\Test\Infra\Factories\BuyerFactory;
use Superern\Wallet\Test\Infra\Models\Buyer;
use Superern\Wallet\Test\Infra\PackageModels\Transaction;
use Superern\Wallet\Test\Infra\PackageModels\TransactionMoney;
use Superern\Wallet\Test\Infra\TestCase;
use Superern\Wallet\Test\Infra\Transform\TransactionDtoTransformerCustom;

/**
 * @internal
 */
final class WalletExtensionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app?->bind(TransactionDtoTransformerInterface::class, TransactionDtoTransformerCustom::class);
    }

    public function testCustomAttribute(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        self::assertFalse($buyer->relationLoaded('wallet'));
        $transaction = $buyer->deposit(1000, [
            'bank_method' => 'VietComBank',
        ]);

        self::assertTrue($transaction->getKey() > 0);
        self::assertSame($transaction->amountInt, $buyer->balanceInt);
        self::assertInstanceOf(Transaction::class, $transaction);
        self::assertSame('VietComBank', $transaction->bank_method);
    }

    public function testTransactionMoneyAttribute(): void
    {
        $this->app?->bind(\Superern\Wallet\Models\Transaction::class, TransactionMoney::class);

        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        self::assertFalse($buyer->relationLoaded('wallet'));
        /** @var TransactionMoney $transaction */
        $transaction = $buyer->deposit(1000, [
            'currency' => 'EUR',
        ]);

        self::assertTrue($transaction->getKey() > 0);
        self::assertSame($transaction->amountInt, $buyer->balanceInt);
        self::assertInstanceOf(TransactionMoney::class, $transaction);
        self::assertSame('1000', $transaction->currency->getAmount());
        self::assertSame('EUR', $transaction->currency->getCurrency()->getCode());
    }

    public function testNoCustomAttribute(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        self::assertFalse($buyer->relationLoaded('wallet'));
        $transaction = $buyer->deposit(1000);

        self::assertSame($transaction->amountInt, $buyer->balanceInt);
        self::assertInstanceOf(Transaction::class, $transaction);
        self::assertNull($transaction->bank_method);
    }
}
