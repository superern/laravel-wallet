<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Assembler;

use Superern\Wallet\Interfaces\Wallet;
use Superern\Wallet\Internal\Dto\TransactionDtoInterface;
use Superern\Wallet\Internal\Dto\TransferLazyDtoInterface;

interface TransferLazyDtoAssemblerInterface
{
    public function create(
        Wallet $fromWallet,
        Wallet $toWallet,
        int $discount,
        string $fee,
        TransactionDtoInterface $withdrawDto,
        TransactionDtoInterface $depositDto,
        string $status,
        ?string $uuid
    ): TransferLazyDtoInterface;
}
