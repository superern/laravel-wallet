<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Assembler;

use Superern\Wallet\Internal\Events\WalletCreatedEvent;
use Superern\Wallet\Internal\Events\WalletCreatedEventInterface;
use Superern\Wallet\Internal\Service\ClockServiceInterface;
use Superern\Wallet\Models\Wallet;

final class WalletCreatedEventAssembler implements WalletCreatedEventAssemblerInterface
{
    public function __construct(
        private readonly ClockServiceInterface $clockService
    ) {
    }

    public function create(Wallet $wallet): WalletCreatedEventInterface
    {
        return new WalletCreatedEvent(
            $wallet->holder_type,
            $wallet->holder_id,
            $wallet->uuid,
            $wallet->getKey(),
            $this->clockService->now()
        );
    }
}
