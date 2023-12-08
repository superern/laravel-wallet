# Configuration
Though this package is crafted to suit most of your needs by default, you can edit the configuration file to suit certain demands.

## Configure default wallet
Customize `name`,`slug` and `meta` of default wallet.

```php[config/wallet.php]
'default' => [
            'name' => 'Ethereum',
            'slug' => 'ETH',
            'meta' => [],
        ],
```
## Extend base Wallet model
You can extend base Wallet model by creating a new class that extends `Superern\Wallet\Models\Wallet` and registering the new class in `config/wallet.php`.
Example `MyWallet.php`

```php[App/Models/MyWallet.php]
use Superern\Wallet\Models\Wallet as WalletBase;

class MyWallet extends WalletBase {
    public function helloWorld(): string { return "hello world"; }
}
```
### Register base Wallet model

```php[config/wallet.php]
    'wallet' => [
        'table' => 'wallets',
        'model' => MyWallet::class,
        'creating' => [],
        'default' => [
            'name' => 'Default Wallet',
            'slug' => 'default',
            'meta' => [],
        ],
    ],
```
```php
   echo $user->wallet->helloWorld();
```
This same method above, can be used to extend the base `Transfer` and `Transaction` models and registering the extended models in the configuration file.
### Changing wallet decimal places

You can change the default wallet decimal places, in wallet config file. This can be useful when working with fractional numbers.

```php[config/wallet.php]
  /**
     * Base model 'wallet'.
     */
    'wallet' => [
        ....
        'creating' => [
            'decimal_places' => 18,
        ],
       ....
    ],
```

