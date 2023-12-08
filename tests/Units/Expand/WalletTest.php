<?php

declare(strict_types=1);

namespace Superern\Wallet\Test\Units\Expand;

use Superern\Wallet\Test\Infra\Factories\BuyerFactory;
use Superern\Wallet\Test\Infra\Models\Buyer;
use Superern\Wallet\Test\Infra\PackageModels\MyWallet;
use Superern\Wallet\Test\Infra\TestCase;

/**
 * @internal
 */
final class WalletTest extends TestCase
{
    public function testAddMethod(): void
    {
        config([
            'wallet.wallet.model' => MyWallet::class,
        ]);

        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();

        /** @var MyWallet $wallet */
        $wallet = $buyer->wallet;

        self::assertSame('hello world', $wallet->helloWorld());
    }
}
