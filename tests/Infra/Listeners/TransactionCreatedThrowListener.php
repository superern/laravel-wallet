<?php

declare(strict_types=1);

namespace Superern\Wallet\Test\Infra\Listeners;

use Superern\Wallet\Internal\Events\TransactionCreatedEventInterface;
use Superern\Wallet\Test\Infra\Exceptions\UnknownEventException;

final class TransactionCreatedThrowListener
{
    public function handle(TransactionCreatedEventInterface $transactionCreatedEvent): never
    {
        $type = $transactionCreatedEvent->getType();
        $createdAt = $transactionCreatedEvent->getCreatedAt()
            ->format(\DateTimeInterface::ATOM)
        ;

        $message = hash('sha256', $type . $createdAt);

        throw new UnknownEventException($message, $transactionCreatedEvent->getId());
    }
}
