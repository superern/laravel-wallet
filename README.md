![Laravel Wallet](https://user-images.githubusercontent.com/5111255/48687709-a7c2fa00-ebd3-11e8-8714-c4f3efe93f02.png)


laravel-wallet - Easy work with virtual wallet.


* **Package**: laravel-wallet
* **PHP Version**: 8.0+
* **Laravel Version**: `9.x`
* **[Composer](https://getcomposer.org/):** `composer require superern/laravel-wallet`

### Upgrade Guide

To perform the migration, you will be [helped by the instruction](https://superern.github.io/laravel-wallet/#/upgrade-guide).

### Extensions

| Extension                                                 | Description                                                                |
|-----------------------------------------------------------|----------------------------------------------------------------------------|
| [Swap](https://github.com/superern/laravel-wallet-swap)      | Addition to the laravel-wallet library for quick setting of exchange rates |
| [uuid](https://github.com/superern/laravel-wallet-uuid)      | Addition to laravel-wallet to support model uuid keys                      | 
| [Warm Up](https://github.com/superern/laravel-wallet-warmup) | Addition to the laravel-wallet library for refresh balance wallets         | 

### Usage
Add the `HasWallet` trait and `Wallet` interface to model.
```php
use Superern\Wallet\Traits\HasWallet;
use Superern\Wallet\Interfaces\Wallet;

class User extends Model implements Wallet
{
    use HasWallet;
}
```

Now we make transactions.

```php
$user = User::first();
$user->balanceInt; // 0

$user->deposit(10);
$user->balance; // 10
$user->balanceInt; // int(10)

$user->withdraw(1);
$user->balance; // 9

$user->forceWithdraw(200, ['description' => 'payment of taxes']);
$user->balance; // -191
```

### Purchases

Add the `CanPay` trait and `Customer` interface to your `User` model.
```php
use Superern\Wallet\Traits\CanPay;
use Superern\Wallet\Interfaces\Customer;

class User extends Model implements Customer
{
    use CanPay;
}
```

Add the `HasWallet` trait and interface to `Item` model.

Starting from version 9.x there are two product interfaces:
- For an unlimited number of products (`ProductInterface`);
- For a limited number of products (`ProductLimitedInterface`);

An example with an unlimited number of products:
```php
use Superern\Wallet\Traits\HasWallet;
use Superern\Wallet\Interfaces\Customer;
use Superern\Wallet\Interfaces\ProductInterface;

class Item extends Model implements ProductInterface
{
    use HasWallet;

    public function getAmountProduct(Customer $customer): int|string
    {
        return 100;
    }

    public function getMetaProduct(): ?array
    {
        return [
            'title' => $this->title, 
            'description' => 'Purchase of Product #' . $this->id,
        ];
    }
}
```

Example with a limited number of products:
```php
use Superern\Wallet\Traits\HasWallet;
use Superern\Wallet\Interfaces\Customer;
use Superern\Wallet\Interfaces\ProductLimitedInterface;

class Item extends Model implements ProductLimitedInterface
{
    use HasWallet;

    public function canBuy(Customer $customer, int $quantity = 1, bool $force = false): bool
    {
        /**
         * This is where you implement the constraint logic. 
         * 
         * If the service can be purchased once, then
         *  return !$customer->paid($this);
         */
        return true; 
    }
    
    public function getAmountProduct(Customer $customer): int|string
    {
        return 100;
    }

    public function getMetaProduct(): ?array
    {
        return [
            'title' => $this->title, 
            'description' => 'Purchase of Product #' . $this->id,
        ];
    }
}
```

I do not recommend using the limited interface when working with a shopping cart. 
If you are working with a shopping cart, then you should override the `PurchaseServiceInterface` interface. 
With it, you can check the availability of all products with one request, there will be no N-queries in the database.

Proceed to purchase.

```php
$user = User::first();
$user->balance; // 100

$item = Item::first();
$user->pay($item); // If you do not have enough money, throw an exception
var_dump($user->balance); // 0

if ($user->safePay($item)) {
  // try to buy again
}

var_dump((bool)$user->paid($item)); // bool(true)

var_dump($user->refund($item)); // bool(true)
var_dump((bool)$user->paid($item)); // bool(false)
```

### Eager Loading

```php
// When working with one wallet
User::with('wallet');

// When using the multi-wallet functionality
User::with('wallets');
```

### How to work with fractional numbers?
Add the `HasWalletFloat` trait and `WalletFloat` interface to model.
```php
use Superern\Wallet\Traits\HasWalletFloat;
use Superern\Wallet\Interfaces\WalletFloat;
use Superern\Wallet\Interfaces\Wallet;

class User extends Model implements Wallet, WalletFloat
{
    use HasWalletFloat;
}
```

Now we make transactions.

```php
$user = User::first();
$user->balance; // 100
$user->balanceFloat; // 1.00

$user->depositFloat(1.37);
$user->balance; // 237
$user->balanceFloat; // 2.37
```
