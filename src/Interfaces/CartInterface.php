<?php

declare(strict_types=1);

namespace Superern\Wallet\Interfaces;

use Superern\Wallet\Internal\Dto\BasketDtoInterface;
use Superern\Wallet\Internal\Exceptions\CartEmptyException;

/**
 * A kind of cart hydrate, needed for a smooth transition from a convenient dto to a less convenient internal dto.
 */
interface CartInterface
{
    /**
     * @throws CartEmptyException
     */
    public function getBasketDto(): BasketDtoInterface;
}
