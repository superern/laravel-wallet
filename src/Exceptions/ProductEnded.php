<?php

declare(strict_types=1);

namespace Superern\Wallet\Exceptions;

use Superern\Wallet\Internal\Exceptions\LogicExceptionInterface;
use LogicException;

final class ProductEnded extends LogicException implements LogicExceptionInterface
{
}
