<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Listeners;

use Superern\Wallet\Internal\Service\ConnectionServiceInterface;
use Superern\Wallet\Services\RegulatorServiceInterface;

final class TransactionCommittingListener
{
    public function __invoke(): void
    {
        /**
         * In fact, this if is not needed here.
         * But in order to protect the code from changes in the framework, I added a check here.
         */
        if (app(ConnectionServiceInterface::class)->get()->transactionLevel() === 1) {
            app(RegulatorServiceInterface::class)->committing();
        }
    }
}
