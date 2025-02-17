<?php

declare(strict_types=1);

namespace Superern\Wallet\Test\Infra\Listeners;

use Superern\Wallet\Internal\Events\WalletCreatedEventInterface;
use Superern\Wallet\Test\Infra\Exceptions\UnknownEventException;
use DateTimeInterface;

final class WalletCreatedThrowListener
{
    public function handle(WalletCreatedEventInterface $walletCreatedEvent): never
    {
        $holderType = $walletCreatedEvent->getHolderType();
        $uuid = $walletCreatedEvent->getWalletUuid();
        $createdAt = $walletCreatedEvent->getCreatedAt()
            ->format(DateTimeInterface::ATOM)
        ;

        $message = hash('sha256', $holderType . $uuid . $createdAt);
        $code = $walletCreatedEvent->getWalletId() + (int) $walletCreatedEvent->getHolderId();
        assert($code > 1);

        throw new UnknownEventException($message, $code);
    }
}
