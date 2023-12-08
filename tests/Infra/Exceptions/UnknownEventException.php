<?php

declare(strict_types=1);

namespace Superern\Wallet\Test\Infra\Exceptions;

use Superern\Wallet\Internal\Exceptions\RuntimeExceptionInterface;
use RuntimeException;

final class UnknownEventException extends RuntimeException implements RuntimeExceptionInterface
{
}
