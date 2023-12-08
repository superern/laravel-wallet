<?php

declare(strict_types=1);

namespace Superern\Wallet\Test\Infra\Models;

use Superern\Wallet\Interfaces\Confirmable;
use Superern\Wallet\Interfaces\Wallet;
use Superern\Wallet\Traits\CanConfirm;
use Superern\Wallet\Traits\HasWallet;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $name
 * @property string $email
 *
 * @method int getKey()
 */
final class UserConfirm extends Model implements Wallet, Confirmable
{
    use HasWallet;
    use CanConfirm;

    /**
     * @var string[]
     */
    protected $fillable = ['name', 'email'];

    public function getTable(): string
    {
        return 'users';
    }
}
