<?php

declare(strict_types=1);

namespace Superern\Wallet\Test\Infra\Listeners;

use Superern\Wallet\Internal\Events\BalanceUpdatedEventInterface;
use Superern\Wallet\Test\Infra\Exceptions\UnknownEventException;

final class BalanceUpdatedThrowIdListener
{
    public function handle(BalanceUpdatedEventInterface $balanceChangedEvent): never
    {
        throw new UnknownEventException(
            $balanceChangedEvent->getWalletUuid(),
            (int) $balanceChangedEvent->getBalance()
        );
    }
}
