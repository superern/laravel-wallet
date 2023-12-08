<?php

declare(strict_types=1);

namespace Superern\Wallet\Services;

use Superern\Wallet\Exceptions\AmountInvalid;
use Superern\Wallet\External\Contracts\ExtraDtoInterface;
use Superern\Wallet\Interfaces\Wallet;
use Superern\Wallet\Internal\Dto\TransactionDtoInterface;
use Superern\Wallet\Internal\Dto\TransferLazyDtoInterface;
use Superern\Wallet\Models\Wallet as WalletModel;

/**
 * @api
 */
interface PrepareServiceInterface
{
    /**
     * @param null|array<mixed> $meta
     *
     * @throws AmountInvalid
     */
    public function deposit(
        Wallet $wallet,
        float|int|string $amount,
        ?array $meta,
        bool $confirmed = true,
        ?string $uuid = null
    ): TransactionDtoInterface;

    /**
     * @param null|array<mixed> $meta
     *
     * @throws AmountInvalid
     */
    public function withdraw(
        Wallet $wallet,
        float|int|string $amount,
        ?array $meta,
        bool $confirmed = true,
        ?string $uuid = null
    ): TransactionDtoInterface;

    /**
     * @param ExtraDtoInterface|array<mixed>|null $meta
     *
     * @throws AmountInvalid
     */
    public function transferLazy(
        Wallet $from,
        Wallet $to,
        string $status,
        float|int|string $amount,
        ExtraDtoInterface|array|null $meta = null
    ): TransferLazyDtoInterface;

    /**
     * @param ExtraDtoInterface|array<mixed>|null $meta
     *
     * @throws AmountInvalid
     */
    public function transferExtraLazy(
        Wallet $from,
        WalletModel $fromWallet,
        Wallet $to,
        WalletModel $toWallet,
        string $status,
        float|int|string $amount,
        ExtraDtoInterface|array|null $meta = null
    ): TransferLazyDtoInterface;
}
