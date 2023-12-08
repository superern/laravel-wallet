<?php

declare(strict_types=1);

namespace Superern\Wallet\Test\Units\Service;

use Superern\Wallet\Services\BookkeeperService;
use Superern\Wallet\Test\Infra\Factories\BuyerFactory;
use Superern\Wallet\Test\Infra\Models\Buyer;
use Superern\Wallet\Test\Infra\TestCase;

/**
 * @internal
 */
final class BookkeeperTest extends TestCase
{
    public function testSync(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();

        $booker = app(BookkeeperService::class);
        self::assertTrue($booker->sync($buyer->wallet, 42));
        self::assertSame('42', $booker->amount($buyer->wallet));
    }

    public function testAmount(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();
        $buyer->deposit(42);
        $buyer->withdraw(11);
        $buyer->deposit(1);

        $booker = app(BookkeeperService::class);
        self::assertSame('32', $booker->amount($buyer->wallet));
    }

    public function testIncrease(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();

        $booker = app(BookkeeperService::class);
        self::assertSame('5', $booker->increase($buyer->wallet, 5));
        self::assertTrue($booker->forget($buyer->wallet));
        self::assertSame('0', $booker->amount($buyer->wallet));
    }

    public function testMultiIncrease(): void
    {
        /** @var Buyer $buyer */
        $buyer = BuyerFactory::new()->create();

        $booker = app(BookkeeperService::class);
        self::assertSame(
            [
                $buyer->wallet->uuid => '5',
            ],
            $booker->multiIncrease([
                $buyer->wallet->uuid => $buyer->wallet,
            ], [
                $buyer->wallet->uuid => 5,
            ]),
        );
        self::assertTrue($booker->forget($buyer->wallet));
        self::assertSame('0', $booker->amount($buyer->wallet));
    }
}
