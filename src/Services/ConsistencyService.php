<?php

declare(strict_types=1);

namespace Superern\Wallet\Services;

use Superern\Wallet\Exceptions\AmountInvalid;
use Superern\Wallet\Exceptions\BalanceIsEmpty;
use Superern\Wallet\Exceptions\InsufficientFunds;
use Superern\Wallet\Interfaces\Wallet;
use Superern\Wallet\Internal\Dto\TransferLazyDtoInterface;
use Superern\Wallet\Internal\Exceptions\ExceptionInterface;
use Superern\Wallet\Internal\Service\MathServiceInterface;
use Superern\Wallet\Internal\Service\TranslatorServiceInterface;

/**
 * @internal
 */
final class ConsistencyService implements ConsistencyServiceInterface
{
    public function __construct(
        private readonly TranslatorServiceInterface $translatorService,
        private readonly MathServiceInterface $mathService,
        private readonly CastServiceInterface $castService
    ) {
    }

    /**
     * @throws AmountInvalid
     */
    public function checkPositive(float|int|string $amount): void
    {
        if ($this->mathService->compare($amount, 0) === -1) {
            throw new AmountInvalid(
                $this->translatorService->get('wallet::errors.price_positive'),
                ExceptionInterface::AMOUNT_INVALID
            );
        }
    }

    /**
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     */
    public function checkPotential(Wallet $object, float|int|string $amount, bool $allowZero = false): void
    {
        $wallet = $this->castService->getWallet($object, false);
        $balance = $this->mathService->add($wallet->getBalanceAttribute(), $wallet->getCreditAttribute());

        if (($this->mathService->compare($amount, 0) !== 0) && ($this->mathService->compare($balance, 0) === 0)) {
            throw new BalanceIsEmpty(
                $this->translatorService->get('wallet::errors.wallet_empty'),
                ExceptionInterface::BALANCE_IS_EMPTY
            );
        }

        if (! $this->canWithdraw($balance, $amount, $allowZero)) {
            throw new InsufficientFunds(
                $this->translatorService->get('wallet::errors.insufficient_funds'),
                ExceptionInterface::INSUFFICIENT_FUNDS
            );
        }
    }

    public function canWithdraw(float|int|string $balance, float|int|string $amount, bool $allowZero = false): bool
    {
        $mathService = app(MathServiceInterface::class);

        /**
         * Allow buying for free with a negative balance.
         */
        if ($allowZero && ! $mathService->compare($amount, 0)) {
            return true;
        }

        return $mathService->compare($balance, $amount) >= 0;
    }

    /**
     * @param TransferLazyDtoInterface[] $objects
     *
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     */
    public function checkTransfer(array $objects): void
    {
        $wallets = [];
        $totalAmount = [];
        foreach ($objects as $object) {
            $withdrawDto = $object->getWithdrawDto();
            $wallet = $this->castService->getWallet($object->getFromWallet(), false);
            $wallets[] = $wallet;

            $totalAmount[$wallet->uuid] = $this->mathService->sub(
                ($totalAmount[$wallet->uuid] ?? 0),
                $withdrawDto->getAmount()
            );
        }

        foreach ($wallets as $wallet) {
            $this->checkPotential($wallet, $totalAmount[$wallet->uuid] ?? -1);
        }
    }
}
