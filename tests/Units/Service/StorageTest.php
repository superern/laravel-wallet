<?php

declare(strict_types=1);

namespace Superern\Wallet\Test\Units\Service;

use Superern\Wallet\Internal\Decorator\StorageServiceLockDecorator;
use Superern\Wallet\Internal\Exceptions\ExceptionInterface;
use Superern\Wallet\Internal\Exceptions\RecordNotFoundException;
use Superern\Wallet\Internal\Service\StorageService;
use Superern\Wallet\Test\Infra\TestCase;

/**
 * @internal
 */
final class StorageTest extends TestCase
{
    public function testFlush(): void
    {
        $this->expectException(RecordNotFoundException::class);
        $this->expectExceptionCode(ExceptionInterface::RECORD_NOT_FOUND);
        $storage = app(StorageService::class);

        self::assertTrue($storage->sync('hello', 34));
        self::assertTrue($storage->sync('world', 42));
        self::assertSame('42', $storage->get('world'));
        self::assertSame('34', $storage->get('hello'));
        self::assertTrue($storage->flush());

        $storage->get('hello'); // record not found
    }

    public function testDecorator(): void
    {
        $this->expectException(RecordNotFoundException::class);
        $this->expectExceptionCode(ExceptionInterface::RECORD_NOT_FOUND);
        $storage = app(StorageServiceLockDecorator::class);

        self::assertTrue($storage->sync('hello', 34));
        self::assertTrue($storage->sync('world', 42));
        self::assertSame('42', $storage->get('world'));
        self::assertSame('34', $storage->get('hello'));
        self::assertTrue($storage->flush());

        $storage->get('hello'); // record not found
    }

    public function testIncreaseDecorator(): void
    {
        $storage = app(StorageServiceLockDecorator::class);

        $storage->multiSync([
            'hello' => 34,
        ]);

        self::assertSame('34', $storage->get('hello'));
        self::assertSame('42', $storage->increase('hello', 8));
    }
}
