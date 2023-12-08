<?php

declare(strict_types=1);

namespace Superern\Wallet\Interfaces;

use Superern\Wallet\Exceptions\BalanceIsEmpty;
use Superern\Wallet\Exceptions\InsufficientFunds;
use Superern\Wallet\External\Contracts\ExtraDtoInterface;
use Superern\Wallet\Internal\Exceptions\ExceptionInterface;
use Superern\Wallet\Internal\Exceptions\RecordNotFoundException;
use Superern\Wallet\Internal\Exceptions\TransactionFailedException;
use Superern\Wallet\Models\Transfer;
use Illuminate\Database\RecordsNotFoundException;

interface Exchangeable
{
    /**
     * @param ExtraDtoInterface|array<mixed>|null $meta
     *
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function exchange(Wallet $to, int|string $amount, ExtraDtoInterface|array|null $meta = null): Transfer;

    /**
     * @param ExtraDtoInterface|array<mixed>|null $meta
     */
    public function safeExchange(Wallet $to, int|string $amount, ExtraDtoInterface|array|null $meta = null): ?Transfer;

    /**
     * @param ExtraDtoInterface|array<mixed>|null $meta
     *
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function forceExchange(Wallet $to, int|string $amount, ExtraDtoInterface|array|null $meta = null): Transfer;
}
