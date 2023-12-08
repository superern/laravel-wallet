<?php

declare(strict_types=1);

namespace Superern\Wallet\Test\Infra\Services;

use Superern\Wallet\Internal\Service\ClockServiceInterface;
use DateTimeImmutable;

final class ClockFakeService implements ClockServiceInterface
{
    public const FAKE_DATETIME = '2010-01-28T15:00:00+02:00';

    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable(self::FAKE_DATETIME);
    }
}
