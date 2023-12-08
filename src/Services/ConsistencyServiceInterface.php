<?php

declare(strict_types=1);

namespace Superern\Wallet\Services;

use Superern\Wallet\Exceptions\AmountInvalid;
use Superern\Wallet\Exceptions\BalanceIsEmpty;
use Superern\Wallet\Exceptions\InsufficientFunds;
use Superern\Wallet\Interfaces\Wallet;
use Superern\Wallet\Internal\Dto\TransferLazyDtoInterface;

/**
 * @api
 */
interface ConsistencyServiceInterface
{
    /**
     * @throws AmountInvalid
     */
    public function checkPositive(float|int|string $amount): void;

    /**
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     */
    public function checkPotential(Wallet $object, float|int|string $amount, bool $allowZero = false): void;

    public function canWithdraw(float|int|string $balance, float|int|string $amount, bool $allowZero = false): bool;

    /**
     * @param TransferLazyDtoInterface[] $objects
     *
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     */
    public function checkTransfer(array $objects): void;
}
