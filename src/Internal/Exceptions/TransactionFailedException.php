<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Exceptions;

use LogicException;

final class TransactionFailedException extends LogicException implements LogicExceptionInterface
{
}
