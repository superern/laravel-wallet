<?php

declare(strict_types=1);

namespace Superern\Wallet\Test\Infra\Models;

use Superern\Wallet\Interfaces\Wallet;
use Superern\Wallet\Interfaces\WalletFloat;
use Superern\Wallet\Traits\HasWalletFloat;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $name
 * @property string $email
 *
 * @method int getKey()
 */
final class UserFloat extends Model implements Wallet, WalletFloat
{
    use HasWalletFloat;

    /**
     * @var string[]
     */
    protected $fillable = ['name', 'email'];

    public function getTable(): string
    {
        return 'users';
    }
}
