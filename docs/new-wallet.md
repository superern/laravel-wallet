# Create a wallet and use it

You can create an unlimited number of wallets, but the `slug` for each wallet should be unique.

---

## User Model

Add the `HasWallet`, `HasWallets` trait's and `Wallet` interface to model.

```php
use Superern\Wallet\Traits\HasWallet;
use Superern\Wallet\Traits\HasWallets;
use Superern\Wallet\Interfaces\Wallet;

class User extends Model implements Wallet
{
    use HasWallet, HasWallets;
}
```

## Create a wallet

Find user:

```php
$user = User::first(); 
```

As the user uses `HasWallet`, he will have `balance` property. 
Check the user's balance.

```php
$user->balance; // 0
```

It is the balance of the wallet by default.
Create a new wallet.

```php
$user->hasWallet('my-wallet'); // bool(false)
$wallet = $user->createWallet([
    'name' => 'New Wallet',
    'slug' => 'my-wallet',
]);

$user->hasWallet('my-wallet'); // bool(true)

$wallet->deposit(100);
$wallet->balance; // 100

$user->deposit(10); 
$user->balance; // 10
```

## How to get the right wallet?

```php
$myWallet = $user->getWallet('my-wallet');
$myWallet->balance; // 100
```

## How to get the default wallet?

```php
$wallet = $user->wallet;
$wallet->balance; // 10
```

It worked! 
