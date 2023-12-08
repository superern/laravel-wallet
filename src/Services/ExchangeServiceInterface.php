<?php

declare(strict_types=1);

namespace Superern\Wallet\Services;

/**
 * Currency exchange contract between wallets.
 *
 * @api
 */
interface ExchangeServiceInterface
{
    /**
     * Currency conversion method.
     */
    public function convertTo(string $fromCurrency, string $toCurrency, float|int|string $amount): string;
}
