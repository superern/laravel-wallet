<?php

declare(strict_types=1);

namespace Superern\Wallet\Test\Units\Service;

use Superern\Wallet\Internal\Service\JsonService;
use Superern\Wallet\Test\Infra\TestCase;

/**
 * @internal
 */
final class JsonServiceTest extends TestCase
{
    public function testJsonEncodeSuccess(): void
    {
        $jsonService = app(JsonService::class);
        self::assertNull($jsonService->encode(null));
        self::assertJson((string) $jsonService->encode([1]));
    }

    public function testJsonEncodeFailed(): void
    {
        $jsonService = app(JsonService::class);
        $array = [1];
        $array[] = &$array;

        self::assertNull($jsonService->encode($array));
    }
}
