<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Assembler;

use Superern\Wallet\Internal\Events\TransactionCreatedEventInterface;
use Superern\Wallet\Models\Transaction;

interface TransactionCreatedEventAssemblerInterface
{
    public function create(Transaction $transaction): TransactionCreatedEventInterface;
}
