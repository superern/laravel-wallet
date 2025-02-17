## User Model

Add the `CanPay` trait and `Customer` interface to your User model.

> The trait `CanPay` already inherits `HasWallet`, reuse will cause an error.

```php
use Superern\Wallet\Traits\CanPay;
use Superern\Wallet\Interfaces\Customer;

class User extends Model implements Customer
{
    use CanPay;
}
```

## Item Model

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

## Proceed to purchase

Find the user and check the balance.

```php
$user = User::first();
$user->balance; // 100
```

Find the goods and check the cost.

```php
$item = Item::first();
$item->getAmountProduct($user); // 100
```

The user can buy a product, buy...

```php
$user->pay($item);
$user->balance; // 0
```

What happens if the user does not have the funds?
The same as with the [withdrawal](withdraw#failed).

```php
$user->balance; // 0
$user->pay($item);
// throw an exception
```

The question arises, how do you know that the product is purchased?

```php
(bool)$user->paid($item); // bool(true)
```

## Safe Pay

To not write `try` and `catch` use `safePay` method.

```php
if ($user->safePay($item)) {
  // try to buy again )
}
```

It worked! 
