<?php

declare(strict_types=1);

namespace Superern\Wallet\Test\Infra\Models;

use Superern\Wallet\Interfaces\Customer;
use Superern\Wallet\Interfaces\ProductLimitedInterface;
use Superern\Wallet\Models\Transfer;
use Superern\Wallet\Models\Wallet;
use Superern\Wallet\Services\CastService;
use Superern\Wallet\Services\CastServiceInterface;
use Superern\Wallet\Test\Infra\Helpers\Config;
use Superern\Wallet\Traits\HasWallet;
use Superern\Wallet\Traits\HasWallets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property string $name
 * @property int $quantity
 * @property int $price
 *
 * @method int getKey()
 */
final class Item extends Model implements ProductLimitedInterface
{
    use HasWallet;
    use HasWallets;

    /**
     * @var string[]
     */
    protected $fillable = ['name', 'quantity', 'price'];

    public function canBuy(Customer $customer, int $quantity = 1, bool $force = false): bool
    {
        $result = $this->quantity >= $quantity;

        if ($force) {
            return $result;
        }

        return $result && ! $customer->paid($this);
    }

    public function getAmountProduct(Customer $customer): int
    {
        /** @var Wallet $wallet */
        $wallet = app(CastService::class)->getWallet($customer);

        return $this->price + (int) $wallet->holder_id;
    }

    public function getMetaProduct(): ?array
    {
        return null;
    }

    /**
     * @param int[] $walletIds
     *
     * @return MorphMany<Transfer>
     */
    public function boughtGoods(array $walletIds): MorphMany
    {
        return app(CastServiceInterface::class)
            ->getWallet($this)
            ->morphMany(Config::classString('wallet.transfer.model', Transfer::class), 'to')
            ->where('status', Transfer::STATUS_PAID)
            ->where('from_type', Config::classString('wallet.wallet.model', Wallet::class))
            ->whereIn('from_id', $walletIds)
        ;
    }
}
