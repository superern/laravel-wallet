<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Dto;

use Superern\Wallet\Interfaces\ProductInterface;
use Countable;
use Generator;

interface BasketDtoInterface extends Countable
{
    public function total(): int;

    /**
     * @return array<mixed>
     */
    public function meta(): array;

    /**
     * @return non-empty-array<int|string, ItemDtoInterface>
     */
    public function items(): array;

    /**
     * @return Generator<ProductInterface>
     */
    public function cursor(): Generator;
}
