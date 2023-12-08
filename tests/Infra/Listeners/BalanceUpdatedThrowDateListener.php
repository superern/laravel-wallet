<?php

declare(strict_types=1);

namespace Superern\Wallet\Test\Infra\Listeners;

use Superern\Wallet\Internal\Events\BalanceUpdatedEventInterface;
use Superern\Wallet\Test\Infra\Exceptions\UnknownEventException;
use DateTimeInterface;

final class BalanceUpdatedThrowDateListener
{
    public function handle(BalanceUpdatedEventInterface $balanceChangedEvent): never
    {
        throw new UnknownEventException(
            $balanceChangedEvent->getUpdatedAt()
                ->format(DateTimeInterface::ATOM),
            (int) $balanceChangedEvent->getBalance()
        );
    }
}
