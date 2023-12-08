<?php

declare(strict_types=1);

namespace Superern\Wallet\Test\Units\Service;

use Superern\Wallet\Test\Infra\TestCase;
use Superern\Wallet\WalletConfigure;

/**
 * @internal
 */
final class WalletConfigureTest extends TestCase
{
    public function testIgnoreMigrations(): void
    {
        self::assertTrue(WalletConfigure::isRunsMigrations());

        WalletConfigure::ignoreMigrations();
        self::assertFalse(WalletConfigure::isRunsMigrations());

        WalletConfigure::reset();
        self::assertTrue(WalletConfigure::isRunsMigrations());
    }
}
