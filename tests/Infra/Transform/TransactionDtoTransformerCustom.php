<?php

declare(strict_types=1);

namespace Superern\Wallet\Test\Infra\Transform;

use Superern\Wallet\Internal\Dto\TransactionDtoInterface;
use Superern\Wallet\Internal\Transform\TransactionDtoTransformer;
use Superern\Wallet\Internal\Transform\TransactionDtoTransformerInterface;

final class TransactionDtoTransformerCustom implements TransactionDtoTransformerInterface
{
    public function __construct(
        private readonly TransactionDtoTransformer $transactionDtoTransformer
    ) {
    }

    public function extract(TransactionDtoInterface $dto): array
    {
        $bankMethod = null;
        if ($dto->getMeta() !== null) {
            $bankMethod = $dto->getMeta()['bank_method'] ?? null;
        }

        return array_merge($this->transactionDtoTransformer->extract($dto), [
            'bank_method' => $bankMethod,
        ]);
    }
}
