<?php

declare(strict_types=1);

namespace Superern\Wallet\Services;

use Superern\Wallet\Interfaces\Wallet;
use Superern\Wallet\Models\Wallet as WalletModel;
use Illuminate\Database\Eloquent\Model;

/**
 * @api
 */
interface CastServiceInterface
{
    public function getWallet(Wallet $object, bool $save = true): WalletModel;

    public function getHolder(Model|Wallet $object): Model;

    public function getModel(object $object): Model;
}
