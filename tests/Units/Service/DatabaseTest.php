<?php

declare(strict_types=1);

namespace Superern\Wallet\Test\Units\Service;

use Superern\Wallet\Internal\Exceptions\ExceptionInterface;
use Superern\Wallet\Internal\Exceptions\TransactionFailedException;
use Superern\Wallet\Internal\Service\DatabaseServiceInterface;
use Superern\Wallet\Test\Infra\TestCase;

/**
 * @internal
 */
final class DatabaseTest extends TestCase
{
    /**
     * @throws ExceptionInterface
     */
    public function testCheckCode(): void
    {
        $this->expectException(TransactionFailedException::class);
        $this->expectExceptionCode(ExceptionInterface::TRANSACTION_FAILED);
        $this->expectExceptionMessage('Transaction failed. Message: hello');

        app(DatabaseServiceInterface::class)->transaction(static function (): never {
            throw new \RuntimeException('hello');
        });
    }
}
