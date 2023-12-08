<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Assembler;

use Superern\Wallet\Internal\Events\TransactionCreatedEvent;
use Superern\Wallet\Internal\Events\TransactionCreatedEventInterface;
use Superern\Wallet\Internal\Service\ClockServiceInterface;
use Superern\Wallet\Models\Transaction;

final class TransactionCreatedEventAssembler implements TransactionCreatedEventAssemblerInterface
{
    public function __construct(
        private readonly ClockServiceInterface $clockService
    ) {
    }

    public function create(Transaction $transaction): TransactionCreatedEventInterface
    {
        return new TransactionCreatedEvent(
            $transaction->getKey(),
            $transaction->type,
            $transaction->wallet_id,
            $this->clockService->now(),
        );
    }
}
