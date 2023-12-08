<?php

declare(strict_types=1);

namespace Superern\Wallet\Test\Units\Service;

use Superern\Wallet\Internal\Service\DatabaseServiceInterface;
use Superern\Wallet\Internal\Service\MathServiceInterface;
use Superern\Wallet\Objects\Cart;
use Superern\Wallet\Test\Infra\PackageModels\Transaction;
use Superern\Wallet\Test\Infra\PackageModels\Transfer;
use Superern\Wallet\Test\Infra\PackageModels\Wallet;
use Superern\Wallet\Test\Infra\TestCase;

/**
 * @internal
 */
final class SingletonTest extends TestCase
{
    public function testCart(): void
    {
        self::assertNotSame($this->getRefId(Cart::class), $this->getRefId(Cart::class));
    }

    public function testMathInterface(): void
    {
        self::assertSame($this->getRefId(MathServiceInterface::class), $this->getRefId(MathServiceInterface::class));
    }

    public function testTransaction(): void
    {
        self::assertNotSame($this->getRefId(Transaction::class), $this->getRefId(Transaction::class));
    }

    public function testTransfer(): void
    {
        self::assertNotSame($this->getRefId(Transfer::class), $this->getRefId(Transfer::class));
    }

    public function testWallet(): void
    {
        self::assertNotSame($this->getRefId(Wallet::class), $this->getRefId(Wallet::class));
    }

    public function testDatabaseService(): void
    {
        self::assertSame(
            $this->getRefId(DatabaseServiceInterface::class),
            $this->getRefId(DatabaseServiceInterface::class)
        );
    }

    private function getRefId(string $object): string
    {
        return spl_object_hash(app($object));
    }
}
