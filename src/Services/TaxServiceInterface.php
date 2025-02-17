<?php

declare(strict_types=1);

namespace Superern\Wallet\Services;

use Superern\Wallet\Interfaces\Wallet;

/**
 * @api
 */
interface TaxServiceInterface
{
    public function getFee(Wallet $wallet, float|int|string $amount): string;
}
