<?php

declare(strict_types=1);

namespace Superern\Wallet\Test\Infra\Models;

use Superern\Wallet\Interfaces\Wallet;
use Superern\Wallet\Interfaces\WalletFloat;
use Superern\Wallet\Traits\HasWalletFloat;
use Superern\Wallet\Traits\HasWallets;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $name
 * @property string $email
 *
 * @method int getKey()
 */
final class UserMulti extends Model implements Wallet, WalletFloat
{
    use HasWalletFloat;
    use HasWallets;

    public function getTable(): string
    {
        return 'users';
    }
}
