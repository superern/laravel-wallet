<?php

declare(strict_types=1);

namespace Superern\Wallet\Test\Infra\Models;

use Superern\Wallet\Interfaces\Customer;
use Superern\Wallet\Traits\CanPay;
use Superern\Wallet\Traits\HasWallets;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $name
 * @property string $email
 *
 * @method int getKey()
 */
final class Buyer extends Model implements Customer
{
    use CanPay;
    use HasWallets;

    public function getTable(): string
    {
        return 'users';
    }
}
