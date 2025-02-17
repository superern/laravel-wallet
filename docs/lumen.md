[composer](_include/composer.md ':include')

## Add the service to the app

[Editing the application file](https://lumen.laravel.com/docs/5.8/providers#registering-providers) `bootstrap/app.php`
```php
$app->register(\Superern\Wallet\WalletServiceProvider::class);
```

You also need to add two lines to the "Register Container Bindings" section of the bootstrap/app.php file:
```php
\Illuminate\Support\Facades\Cache::setApplication($app);
$app->registerDeferredProvider(\Illuminate\Cache\CacheServiceProvider::class);
```

Make sure you have Facades and Eloquent enabled.
```php
$app->withFacades();

$app->withEloquent();
```

Start the migration and use the library.

## You can use it for customization

Sometimes it is useful...

### Run Migrations
Publish the migrations with this artisan command:
```bash
php artisan vendor:publish --tag=laravel-wallet-migrations
```

### Configuration
You can publish the config file with this artisan command:
```bash
php artisan vendor:publish --tag=laravel-wallet-config
```

After installing the package, you can proceed to [use it](basic-usage).
