<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Assembler;

use Superern\Wallet\Internal\Dto\TransferDtoInterface;
use Illuminate\Database\Eloquent\Model;

interface TransferDtoAssemblerInterface
{
    public function create(
        int $depositId,
        int $withdrawId,
        string $status,
        Model $fromModel,
        Model $toModel,
        int $discount,
        string $fee,
        ?string $uuid
    ): TransferDtoInterface;
}
