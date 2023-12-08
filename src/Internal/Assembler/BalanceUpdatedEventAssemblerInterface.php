<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Assembler;

use Superern\Wallet\Internal\Events\BalanceUpdatedEventInterface;
use Superern\Wallet\Models\Wallet;

interface BalanceUpdatedEventAssemblerInterface
{
    public function create(Wallet $wallet): BalanceUpdatedEventInterface;
}
