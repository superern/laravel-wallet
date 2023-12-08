<?php

declare(strict_types=1);

namespace Superern\Wallet\Services;

use Superern\Wallet\Interfaces\ProductInterface;
use Superern\Wallet\Interfaces\Wallet;
use Superern\Wallet\Internal\Dto\BasketDtoInterface;
use Superern\Wallet\Internal\Dto\TransactionDtoInterface;
use Superern\Wallet\Internal\Dto\TransferDtoInterface;

/**
 * @api
 */
interface AssistantServiceInterface
{
    /**
     * @param non-empty-array<Wallet> $objects
     *
     * @return non-empty-array<int, Wallet>
     */
    public function getWallets(array $objects): array;

    /**
     * Helps to quickly extract the uuid from an object.
     *
     * @param non-empty-array<array-key, TransactionDtoInterface|TransferDtoInterface> $objects
     *
     * @return non-empty-array<array-key, string>
     */
    public function getUuids(array $objects): array;

    /**
     * Helps to quickly calculate the amount.
     *
     * @param non-empty-array<TransactionDtoInterface> $transactions
     *
     * @return array<int, string>
     */
    public function getSums(array $transactions): array;

    /**
     * Helps to get cart meta data for a product.
     *
     * @return array<mixed>|null
     */
    public function getMeta(BasketDtoInterface $basketDto, ProductInterface $product): ?array;
}
