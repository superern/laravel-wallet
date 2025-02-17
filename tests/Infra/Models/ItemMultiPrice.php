<?php

declare(strict_types=1);

namespace Superern\Wallet\Test\Infra\Models;

use Superern\Wallet\Interfaces\Customer;
use Superern\Wallet\Interfaces\ProductLimitedInterface;
use Superern\Wallet\Models\Wallet;
use Superern\Wallet\Services\CastService;
use Superern\Wallet\Test\Infra\Exceptions\PriceNotSetException;
use Superern\Wallet\Traits\HasWallet;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $name
 * @property int $quantity
 * @property int $price
 * @property array<string, int> $prices
 *
 * @method int getKey()
 */
final class ItemMultiPrice extends Model implements ProductLimitedInterface
{
    use HasWallet;

    /**
     * @var string[]
     */
    protected $fillable = ['name', 'quantity', 'price', 'prices'];

    protected $casts = [
        'prices' => 'array',
    ];

    public function getTable(): string
    {
        return 'items';
    }

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

        if (array_key_exists($wallet->currency, $this->prices)) {
            return $this->prices[$wallet->currency];
        }

        throw new PriceNotSetException("Price not set for {$wallet->currency} currency");
    }

    public function getMetaProduct(): ?array
    {
        return null;
    }
}
