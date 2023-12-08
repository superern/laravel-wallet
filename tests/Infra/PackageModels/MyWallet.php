<?php

declare(strict_types=1);

namespace Superern\Wallet\Test\Infra\PackageModels;

final class MyWallet extends \Superern\Wallet\Models\Wallet
{
    public function helloWorld(): string
    {
        return 'hello world';
    }
}
