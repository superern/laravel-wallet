<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Exceptions;

use InvalidArgumentException;

final class CartEmptyException extends InvalidArgumentException implements InvalidArgumentExceptionInterface
{
}
