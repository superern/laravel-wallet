<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Service;

use Ramsey\Uuid\UuidFactory;

final class UuidFactoryService implements UuidFactoryServiceInterface
{
    public function __construct(
        private readonly UuidFactory $uuidFactory
    ) {
    }

    public function uuid4(): string
    {
        return $this->uuidFactory->uuid4()
            ->toString()
        ;
    }
}
