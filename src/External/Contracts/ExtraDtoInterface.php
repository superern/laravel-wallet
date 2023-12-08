<?php

declare(strict_types=1);

namespace Superern\Wallet\External\Contracts;

interface ExtraDtoInterface
{
    public function getDepositOption(): OptionDtoInterface;

    public function getWithdrawOption(): OptionDtoInterface;

    public function getUuid(): ?string;
}
