<?php

declare(strict_types=1);

namespace Superern\Wallet\Test\Units\Service;

use Superern\Wallet\Test\Infra\TestCase;
use Superern\Wallet\WalletServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;

/**
 * @internal
 */
final class DeferrableTest extends TestCase
{
    public function testCheckDeferrableProvider(): void
    {
        $walletServiceProvider = app()
            ->resolveProvider(WalletServiceProvider::class)
        ;

        self::assertInstanceOf(DeferrableProvider::class, $walletServiceProvider);
        self::assertNotEmpty($walletServiceProvider->provides());
    }
}
