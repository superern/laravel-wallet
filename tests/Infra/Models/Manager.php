<?php

declare(strict_types=1);

namespace Superern\Wallet\Test\Infra\Models;

use Superern\Wallet\Interfaces\Wallet;
use Superern\Wallet\Traits\HasWallet;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $name
 * @property string $email
 *
 * @method int getKey()
 */
final class Manager extends Model implements Wallet
{
    use HasWallet;

    /**
     * @var string[]
     */
    protected $fillable = ['name', 'email'];
}
