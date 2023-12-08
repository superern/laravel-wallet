<?php

declare(strict_types=1);

namespace Superern\Wallet\Traits;

use Superern\Wallet\Exceptions\BalanceIsEmpty;
use Superern\Wallet\Exceptions\InsufficientFunds;
use Superern\Wallet\External\Contracts\ExtraDtoInterface;
use Superern\Wallet\Interfaces\Wallet;
use Superern\Wallet\Internal\Assembler\ExtraDtoAssemblerInterface;
use Superern\Wallet\Internal\Assembler\TransferLazyDtoAssemblerInterface;
use Superern\Wallet\Internal\Exceptions\ExceptionInterface;
use Superern\Wallet\Internal\Exceptions\RecordNotFoundException;
use Superern\Wallet\Internal\Exceptions\TransactionFailedException;
use Superern\Wallet\Internal\Service\MathServiceInterface;
use Superern\Wallet\Models\Transfer;
use Superern\Wallet\Services\AtomicServiceInterface;
use Superern\Wallet\Services\CastServiceInterface;
use Superern\Wallet\Services\ConsistencyServiceInterface;
use Superern\Wallet\Services\ExchangeServiceInterface;
use Superern\Wallet\Services\PrepareServiceInterface;
use Superern\Wallet\Services\TaxServiceInterface;
use Superern\Wallet\Services\TransferServiceInterface;
use Illuminate\Database\RecordsNotFoundException;

/**
 * @psalm-require-extends \Illuminate\Database\Eloquent\Model
 */
trait CanExchange
{
    /**
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function exchange(Wallet $to, int|string $amount, ExtraDtoInterface|array|null $meta = null): Transfer
    {
        return app(AtomicServiceInterface::class)->block($this, function () use ($to, $amount, $meta): Transfer {
            app(ConsistencyServiceInterface::class)->checkPotential($this, $amount);

            return $this->forceExchange($to, $amount, $meta);
        });
    }

    public function safeExchange(Wallet $to, int|string $amount, ExtraDtoInterface|array|null $meta = null): ?Transfer
    {
        try {
            return $this->exchange($to, $amount, $meta);
        } catch (ExceptionInterface) {
            return null;
        }
    }

    /**
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function forceExchange(Wallet $to, int|string $amount, ExtraDtoInterface|array|null $meta = null): Transfer
    {
        return app(AtomicServiceInterface::class)->block($this, function () use ($to, $amount, $meta): Transfer {
            $extraAssembler = app(ExtraDtoAssemblerInterface::class);
            $prepareService = app(PrepareServiceInterface::class);
            $mathService = app(MathServiceInterface::class);
            $castService = app(CastServiceInterface::class);
            $taxService = app(TaxServiceInterface::class);
            $fee = $taxService->getFee($to, $amount);
            $rate = app(ExchangeServiceInterface::class)->convertTo(
                $castService->getWallet($this)
                    ->getCurrencyAttribute(),
                $castService->getWallet($to)
                    ->currency,
                1
            );

            $extraDto = $extraAssembler->create($meta);
            $withdrawOption = $extraDto->getWithdrawOption();
            $depositOption = $extraDto->getDepositOption();
            $withdrawDto = $prepareService->withdraw(
                $this,
                $mathService->add($amount, $fee),
                $withdrawOption->getMeta(),
                $withdrawOption->isConfirmed(),
                $withdrawOption->getUuid(),
            );
            $depositDto = $prepareService->deposit(
                $to,
                $mathService->floor($mathService->mul($amount, $rate, 1)),
                $depositOption->getMeta(),
                $depositOption->isConfirmed(),
                $depositOption->getUuid(),
            );
            $transferLazyDto = app(TransferLazyDtoAssemblerInterface::class)->create(
                $this,
                $to,
                0,
                $fee,
                $withdrawDto,
                $depositDto,
                Transfer::STATUS_EXCHANGE,
                $extraDto->getUuid()
            );

            $transfers = app(TransferServiceInterface::class)->apply([$transferLazyDto]);

            return current($transfers);
        });
    }
}
