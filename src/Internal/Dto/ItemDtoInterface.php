<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Dto;

use Superern\Wallet\Interfaces\ProductInterface;
use Superern\Wallet\Interfaces\Wallet;
use Countable;

interface ItemDtoInterface extends Countable
{
    /**
     * @return ProductInterface[]
     */
    public function getItems(): array;

    public function getPricePerItem(): int|string|null;

    public function getProduct(): ProductInterface;

    public function getReceiving(): ?Wallet;
}
