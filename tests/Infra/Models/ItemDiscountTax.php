<?php

declare(strict_types=1);

namespace Superern\Wallet\Test\Infra\Models;

use Superern\Wallet\Interfaces\Customer;
use Superern\Wallet\Interfaces\Discount;
use Superern\Wallet\Interfaces\ProductLimitedInterface;
use Superern\Wallet\Interfaces\Taxable;
use Superern\Wallet\Models\Wallet;
use Superern\Wallet\Services\CastService;
use Superern\Wallet\Traits\HasWallet;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $name
 * @property int $quantity
 * @property int $price
 *
 * @method int getKey()
 */
final class ItemDiscountTax extends Model implements ProductLimitedInterface, Discount, Taxable
{
    use HasWallet;

    /**
     * @var string[]
     */
    protected $fillable = ['name', 'quantity', 'price'];

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

        return $this->price + (int) $wallet->holder_id;
    }

    public function getMetaProduct(): ?array
    {
        return null;
    }

    public function getPersonalDiscount(Customer $customer): int
    {
        return (int) app(CastService::class)
            ->getWallet($customer)
            ->holder_id;
    }

    /**
     * Specify the percentage of the amount. For example, the product costs $100, the equivalent of 15%. That's $115.
     *
     * Minimum 0; Maximum 100 Example: return 7.5; // 7.5%
     */
    public function getFeePercent(): float
    {
        return 7.5;
    }
}
