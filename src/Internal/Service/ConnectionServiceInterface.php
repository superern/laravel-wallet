<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Service;

use Illuminate\Database\ConnectionInterface;

interface ConnectionServiceInterface
{
    public function get(): ConnectionInterface;
}
