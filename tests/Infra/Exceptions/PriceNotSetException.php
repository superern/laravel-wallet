<?php

declare(strict_types=1);

namespace Superern\Wallet\Test\Infra\Exceptions;

use Superern\Wallet\Internal\Exceptions\InvalidArgumentExceptionInterface;
use InvalidArgumentException;

final class PriceNotSetException extends InvalidArgumentException implements InvalidArgumentExceptionInterface
{
}
