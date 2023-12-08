<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Service;

use Superern\Wallet\Internal\Exceptions\ExceptionInterface;
use Superern\Wallet\Internal\Exceptions\TransactionFailedException;
use Illuminate\Database\RecordsNotFoundException;

interface DatabaseServiceInterface
{
    /**
     * @template T
     * @param callable(): T $callback
     * @return T
     *
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function transaction(callable $callback): mixed;
}
