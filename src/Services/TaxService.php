<?php

declare(strict_types=1);

namespace Superern\Wallet\Services;

use Superern\Wallet\Interfaces\MaximalTaxable;
use Superern\Wallet\Interfaces\MinimalTaxable;
use Superern\Wallet\Interfaces\Taxable;
use Superern\Wallet\Interfaces\Wallet;
use Superern\Wallet\Internal\Service\MathServiceInterface;

/**
 * @internal
 */
final class TaxService implements TaxServiceInterface
{
    public function __construct(
        private readonly MathServiceInterface $mathService,
        private readonly CastServiceInterface $castService
    ) {
    }

    public function getFee(Wallet $wallet, float|int|string $amount): string
    {
        $fee = 0;
        if ($wallet instanceof Taxable) {
            $fee = $this->mathService->floor(
                $this->mathService->div(
                    $this->mathService->mul($amount, $wallet->getFeePercent(), 0),
                    100,
                    $this->castService->getWallet($wallet)
                        ->decimal_places
                )
            );
        }

        /**
         * Added minimum commission condition.
         *
         * @see https://github.com/superern/laravel-wallet/issues/64#issuecomment-514483143
         */
        if ($wallet instanceof MinimalTaxable) {
            $minimal = $wallet->getMinimalFee();
            if ($this->mathService->compare($fee, $minimal) === -1) {
                $fee = $minimal;
            }
        }

        if ($wallet instanceof MaximalTaxable) {
            $maximal = $wallet->getMaximalFee();
            if ($this->mathService->compare($maximal, $fee) === -1) {
                $fee = $maximal;
            }
        }

        return (string) $fee;
    }
}
