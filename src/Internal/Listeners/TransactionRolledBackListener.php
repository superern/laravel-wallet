<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Listeners;

use Superern\Wallet\Internal\Service\ConnectionServiceInterface;
use Superern\Wallet\Services\RegulatorServiceInterface;

final class TransactionRolledBackListener
{
    public function __invoke(): void
    {
        if (app(ConnectionServiceInterface::class)->get()->transactionLevel() === 0) {
            app(RegulatorServiceInterface::class)->purge();
        }
    }
}
