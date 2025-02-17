<?php

declare(strict_types=1);

namespace Superern\Wallet\Traits;

use Superern\Wallet\Exceptions\BalanceIsEmpty;
use Superern\Wallet\Exceptions\InsufficientFunds;
use Superern\Wallet\Exceptions\ProductEnded;
use Superern\Wallet\Interfaces\CartInterface;
use Superern\Wallet\Interfaces\ProductInterface;
use Superern\Wallet\Internal\Assembler\AvailabilityDtoAssemblerInterface;
use Superern\Wallet\Internal\Exceptions\ExceptionInterface;
use Superern\Wallet\Internal\Exceptions\ModelNotFoundException;
use Superern\Wallet\Internal\Exceptions\RecordNotFoundException;
use Superern\Wallet\Internal\Exceptions\TransactionFailedException;
use Superern\Wallet\Internal\Service\TranslatorServiceInterface;
use Superern\Wallet\Models\Transfer;
use Superern\Wallet\Objects\Cart;
use Superern\Wallet\Services\AssistantServiceInterface;
use Superern\Wallet\Services\AtomicServiceInterface;
use Superern\Wallet\Services\BasketServiceInterface;
use Superern\Wallet\Services\CastServiceInterface;
use Superern\Wallet\Services\ConsistencyServiceInterface;
use Superern\Wallet\Services\EagerLoaderServiceInterface;
use Superern\Wallet\Services\PrepareServiceInterface;
use Superern\Wallet\Services\PurchaseServiceInterface;
use Superern\Wallet\Services\TransferServiceInterface;
use Illuminate\Database\RecordsNotFoundException;
use function count;

/**
 * @psalm-require-extends \Illuminate\Database\Eloquent\Model
 * @psalm-require-implements \Superern\Wallet\Interfaces\Customer
 */
trait CartPay
{
    use HasWallet;

    /**
     * @throws ProductEnded
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     *
     * @return non-empty-array<Transfer>
     */
    public function payFreeCart(CartInterface $cart): array
    {
        return app(AtomicServiceInterface::class)->block($this, function () use ($cart) {
            $basketDto = $cart->getBasketDto();
            $basketService = app(BasketServiceInterface::class);
            $availabilityAssembler = app(AvailabilityDtoAssemblerInterface::class);
            app(EagerLoaderServiceInterface::class)->loadWalletsByBasket($this, $basketDto);
            if (! $basketService->availability($availabilityAssembler->create($this, $basketDto, false))) {
                throw new ProductEnded(
                    app(TranslatorServiceInterface::class)->get('wallet::errors.product_stock'),
                    ExceptionInterface::PRODUCT_ENDED
                );
            }

            app(ConsistencyServiceInterface::class)->checkPotential($this, 0, true);

            $transfers = [];
            $castService = app(CastServiceInterface::class);
            $prepareService = app(PrepareServiceInterface::class);
            $assistantService = app(AssistantServiceInterface::class);
            foreach ($basketDto->items() as $item) {
                foreach ($item->getItems() as $product) {
                    $transfers[] = $prepareService->transferExtraLazy(
                        $this,
                        $castService->getWallet($this),
                        $product,
                        $castService->getWallet($item->getReceiving() ?? $product),
                        Transfer::STATUS_PAID,
                        0,
                        $assistantService->getMeta($basketDto, $product)
                    );
                }
            }

            assert($transfers !== []);

            return app(TransferServiceInterface::class)->apply($transfers);
        });
    }

    /**
     * @return Transfer[]
     */
    public function safePayCart(CartInterface $cart, bool $force = false): array
    {
        try {
            return $this->payCart($cart, $force);
        } catch (ExceptionInterface) {
            return [];
        }
    }

    /**
     * @throws ProductEnded
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     *
     * @return non-empty-array<Transfer>
     */
    public function payCart(CartInterface $cart, bool $force = false): array
    {
        return app(AtomicServiceInterface::class)->block($this, function () use ($cart, $force) {
            $basketDto = $cart->getBasketDto();
            $basketService = app(BasketServiceInterface::class);
            $availabilityAssembler = app(AvailabilityDtoAssemblerInterface::class);
            app(EagerLoaderServiceInterface::class)->loadWalletsByBasket($this, $basketDto);
            if (! $basketService->availability($availabilityAssembler->create($this, $basketDto, $force))) {
                throw new ProductEnded(
                    app(TranslatorServiceInterface::class)->get('wallet::errors.product_stock'),
                    ExceptionInterface::PRODUCT_ENDED
                );
            }

            $prices = [];
            $transfers = [];
            $castService = app(CastServiceInterface::class);
            $prepareService = app(PrepareServiceInterface::class);
            $assistantService = app(AssistantServiceInterface::class);
            foreach ($cart->getBasketDto()->items() as $item) {
                foreach ($item->getItems() as $product) {
                    $productId = $product::class . ':' . $castService->getModel($product)->getKey();
                    $pricePerItem = $item->getPricePerItem();
                    if ($pricePerItem === null) {
                        $prices[$productId] ??= $product->getAmountProduct($this);
                        $pricePerItem = $prices[$productId];
                    }

                    $transfers[] = $prepareService->transferExtraLazy(
                        $this,
                        $castService->getWallet($this),
                        $product,
                        $castService->getWallet($item->getReceiving() ?? $product),
                        Transfer::STATUS_PAID,
                        $pricePerItem,
                        $assistantService->getMeta($basketDto, $product)
                    );
                }
            }

            if (! $force) {
                app(ConsistencyServiceInterface::class)->checkTransfer($transfers);
            }

            assert($transfers !== []);

            return app(TransferServiceInterface::class)->apply($transfers);
        });
    }

    /**
     * @throws ProductEnded
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     *
     * @return non-empty-array<Transfer>
     */
    public function forcePayCart(CartInterface $cart): array
    {
        return $this->payCart($cart, true);
    }

    public function safeRefundCart(CartInterface $cart, bool $force = false, bool $gifts = false): bool
    {
        try {
            return $this->refundCart($cart, $force, $gifts);
        } catch (ExceptionInterface) {
            return false;
        }
    }

    /**
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ModelNotFoundException
     * @throws ExceptionInterface
     */
    public function refundCart(CartInterface $cart, bool $force = false, bool $gifts = false): bool
    {
        return app(AtomicServiceInterface::class)->block($this, function () use ($cart, $force, $gifts) {
            $basketDto = $cart->getBasketDto();
            app(EagerLoaderServiceInterface::class)->loadWalletsByBasket($this, $basketDto);
            $transfers = app(PurchaseServiceInterface::class)->already($this, $basketDto, $gifts);
            if (count($transfers) !== $basketDto->total()) {
                throw new ModelNotFoundException(
                    "No query results for model [{$this->transfers()
                        ->getModel()
                        ->getMorphClass()}]",
                    ExceptionInterface::MODEL_NOT_FOUND
                );
            }

            $index = 0;
            $objects = [];
            $transferIds = [];
            $transfers = array_values($transfers);
            $castService = app(CastServiceInterface::class);
            $prepareService = app(PrepareServiceInterface::class);
            $assistantService = app(AssistantServiceInterface::class);
            foreach ($basketDto->items() as $itemDto) {
                foreach ($itemDto->getItems() as $product) {
                    $transferIds[] = $transfers[$index]->getKey();
                    $objects[] = $prepareService->transferExtraLazy(
                        $product,
                        $castService->getWallet($itemDto->getReceiving() ?? $product),
                        $transfers[$index]->withdraw->wallet,
                        $transfers[$index]->withdraw->wallet,
                        Transfer::STATUS_TRANSFER,
                        $transfers[$index]->deposit->amount,
                        $assistantService->getMeta($basketDto, $product)
                    );

                    ++$index;
                }
            }

            if (! $force) {
                app(ConsistencyServiceInterface::class)->checkTransfer($objects);
            }

            assert($objects !== []);

            $transferService = app(TransferServiceInterface::class);

            $transferService->apply($objects);

            return $transferService
                ->updateStatusByIds(Transfer::STATUS_REFUND, $transferIds)
            ;
        });
    }

    /**
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ModelNotFoundException
     * @throws ExceptionInterface
     */
    public function forceRefundCart(CartInterface $cart, bool $gifts = false): bool
    {
        return $this->refundCart($cart, true, $gifts);
    }

    public function safeRefundGiftCart(CartInterface $cart, bool $force = false): bool
    {
        try {
            return $this->refundGiftCart($cart, $force);
        } catch (ExceptionInterface) {
            return false;
        }
    }

    /**
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ModelNotFoundException
     * @throws ExceptionInterface
     */
    public function refundGiftCart(CartInterface $cart, bool $force = false): bool
    {
        return $this->refundCart($cart, $force, true);
    }

    /**
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ModelNotFoundException
     * @throws ExceptionInterface
     */
    public function forceRefundGiftCart(CartInterface $cart): bool
    {
        return $this->refundGiftCart($cart, true);
    }

    /**
     * Checks acquired product your wallet.
     *
     * @deprecated The method is slow and will be removed in the future
     * @see PurchaseServiceInterface
     */
    public function paid(ProductInterface $product, bool $gifts = false): ?Transfer
    {
        $cart = app(Cart::class)->withItem($product);
        $purchases = app(PurchaseServiceInterface::class)
            ->already($this, $cart->getBasketDto(), $gifts)
        ;

        return current($purchases) ?: null;
    }
}
