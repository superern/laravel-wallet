<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Assembler;

use Superern\Wallet\Interfaces\Wallet;
use Superern\Wallet\Internal\Dto\TransactionDtoInterface;
use Superern\Wallet\Internal\Dto\TransferLazyDto;
use Superern\Wallet\Internal\Dto\TransferLazyDtoInterface;

final class TransferLazyDtoAssembler implements TransferLazyDtoAssemblerInterface
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
    ): TransferLazyDtoInterface {
        return new TransferLazyDto($fromWallet, $toWallet, $discount, $fee, $withdrawDto, $depositDto, $status, $uuid);
    }
}
