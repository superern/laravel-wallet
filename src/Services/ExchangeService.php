<?php

declare(strict_types=1);

namespace Superern\Wallet\Services;

/**
 * @internal
 */
final class ExchangeService implements ExchangeServiceInterface
{
    public function convertTo(string $fromCurrency, string $toCurrency, float|int|string $amount): string
    {
        return (string) $amount;
    }
}
