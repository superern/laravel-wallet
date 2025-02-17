<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Dto;

use Superern\Wallet\Interfaces\Wallet;

interface TransferLazyDtoInterface
{
    public function getFromWallet(): Wallet;

    public function getToWallet(): Wallet;

    public function getDiscount(): int;

    public function getFee(): string;

    public function getWithdrawDto(): TransactionDtoInterface;

    public function getDepositDto(): TransactionDtoInterface;

    public function getStatus(): string;

    public function getUuid(): ?string;
}
