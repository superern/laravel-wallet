<?php

declare(strict_types=1);

namespace Superern\Wallet\Services;

use Superern\Wallet\Models\Wallet;

/**
 * @api
 */
interface RegulatorServiceInterface
{
    /**
     * @deprecated Fixed naming.
     * @see forget
     */
    public function missing(Wallet $wallet): bool;

    public function forget(Wallet $wallet): bool;

    public function diff(Wallet $wallet): string;

    public function amount(Wallet $wallet): string;

    public function sync(Wallet $wallet, float|int|string $value): bool;

    public function increase(Wallet $wallet, float|int|string $value): string;

    public function decrease(Wallet $wallet, float|int|string $value): string;

    public function committing(): void;

    public function committed(): void;

    public function purge(): void;
}
