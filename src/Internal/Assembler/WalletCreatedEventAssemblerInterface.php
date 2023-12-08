<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Assembler;

use Superern\Wallet\Internal\Events\WalletCreatedEventInterface;
use Superern\Wallet\Models\Wallet;

interface WalletCreatedEventAssemblerInterface
{
    public function create(Wallet $wallet): WalletCreatedEventInterface;
}
