<?php

declare(strict_types=1);

namespace Superern\Wallet\Test\Units\Api;

use Superern\Wallet\External\Api\TransferQuery;
use Superern\Wallet\External\Api\TransferQueryHandlerInterface;
use Superern\Wallet\Test\Infra\Factories\BuyerFactory;
use Superern\Wallet\Test\Infra\Models\Buyer;
use Superern\Wallet\Test\Infra\TestCase;
use function app;

/**
 * @internal
 */
final class TransferHandlerTest extends TestCase
{
    public function testWalletNotExists(): void
    {
        /** @var TransferQueryHandlerInterface $transferQueryHandler */
        $transferQueryHandler = app(TransferQueryHandlerInterface::class);

        /** @var Buyer $from */
        /** @var Buyer $to */
        [$from, $to] = BuyerFactory::times(2)->create();

        self::assertFalse($from->relationLoaded('wallet'));
        self::assertFalse($from->wallet->exists);
        self::assertFalse($to->relationLoaded('wallet'));
        self::assertFalse($to->wallet->exists);

        $transfers = $transferQueryHandler->apply([
            new TransferQuery($from, $to, 100, null),
            new TransferQuery($from, $to, 100, null),
            new TransferQuery($to, $from, 50, null),
        ]);

        self::assertSame(-150, $from->balanceInt);
        self::assertSame(150, $to->balanceInt);
        self::assertCount(3, $transfers);
    }
}
