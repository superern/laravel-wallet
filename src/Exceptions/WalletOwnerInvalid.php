<?php

declare(strict_types=1);

namespace Superern\Wallet\Exceptions;

use Superern\Wallet\Internal\Exceptions\InvalidArgumentExceptionInterface;
use InvalidArgumentException;

final class WalletOwnerInvalid extends InvalidArgumentException implements InvalidArgumentExceptionInterface
{
}
