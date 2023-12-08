<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Service;

interface UuidFactoryServiceInterface
{
    public function uuid4(): string;
}
