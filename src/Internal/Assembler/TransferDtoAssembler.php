<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Assembler;

use Superern\Wallet\Internal\Dto\TransferDto;
use Superern\Wallet\Internal\Dto\TransferDtoInterface;
use Superern\Wallet\Internal\Service\UuidFactoryServiceInterface;
use Illuminate\Database\Eloquent\Model;

final class TransferDtoAssembler implements TransferDtoAssemblerInterface
{
    public function __construct(
        private readonly UuidFactoryServiceInterface $uuidService
    ) {
    }

    public function create(
        int $depositId,
        int $withdrawId,
        string $status,
        Model $fromModel,
        Model $toModel,
        int $discount,
        string $fee,
        ?string $uuid
    ): TransferDtoInterface {
        return new TransferDto(
            $uuid ?? $this->uuidService->uuid4(),
            $depositId,
            $withdrawId,
            $status,
            $fromModel->getMorphClass(),
            $fromModel->getKey(),
            $toModel->getMorphClass(),
            $toModel->getKey(),
            $discount,
            $fee
        );
    }
}
