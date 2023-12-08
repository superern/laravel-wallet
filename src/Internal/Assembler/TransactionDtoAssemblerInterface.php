<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Assembler;

use Superern\Wallet\Internal\Dto\TransactionDtoInterface;
use Illuminate\Database\Eloquent\Model;

interface TransactionDtoAssemblerInterface
{
    /**
     * @param null|array<mixed> $meta
     */
    public function create(
        Model $payable,
        int $walletId,
        string $type,
        float|int|string $amount,
        bool $confirmed,
        ?array $meta,
        ?string $uuid
    ): TransactionDtoInterface;
}
