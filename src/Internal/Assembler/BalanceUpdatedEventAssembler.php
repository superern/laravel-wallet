<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Assembler;

use Superern\Wallet\Internal\Events\BalanceUpdatedEvent;
use Superern\Wallet\Internal\Events\BalanceUpdatedEventInterface;
use Superern\Wallet\Internal\Service\ClockServiceInterface;
use Superern\Wallet\Models\Wallet;

final class BalanceUpdatedEventAssembler implements BalanceUpdatedEventAssemblerInterface
{
    public function __construct(
        private readonly ClockServiceInterface $clockService
    ) {
    }

    public function create(Wallet $wallet): BalanceUpdatedEventInterface
    {
        return new BalanceUpdatedEvent(
            $wallet->getKey(),
            $wallet->uuid,
            $wallet->getOriginalBalanceAttribute(),
            $this->clockService->now()
        );
    }
}
