<?php

declare(strict_types=1);

namespace Superern\Wallet\Test\Infra;

use Superern\Wallet\Test\Infra\PackageModels\Transaction;
use Superern\Wallet\Test\Infra\PackageModels\Transfer;
use Superern\Wallet\Test\Infra\PackageModels\Wallet;
use Superern\Wallet\Test\Infra\Services\MyExchangeService;
use Superern\Wallet\WalletServiceProvider;
use Illuminate\Config\Repository;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

/**
 * @internal
 */
abstract class TestCase extends OrchestraTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        DB::transactionLevel() && DB::rollBack();
    }

    final public function expectExceptionMessageStrict(mixed $message): void
    {
        assert(is_string($message));

        $this->expectExceptionMessageMatches("~^{$message}$~");
    }

    /**
     * @param Application $app
     *
     * @return non-empty-array<int, string>
     */
    final protected function getPackageProviders($app): array
    {
        // Bind eloquent models to IoC container
        $app['config']->set('wallet.services.exchange', MyExchangeService::class);
        $app['config']->set('wallet.transaction.model', Transaction::class);
        $app['config']->set('wallet.transfer.model', Transfer::class);
        $app['config']->set('wallet.wallet.model', Wallet::class);

        return [WalletServiceProvider::class, TestServiceProvider::class];
    }

    /**
     * @param Application $app
     */
    final protected function getEnvironmentSetUp($app): void
    {
        /** @var Repository $config */
        $config = $app['config'];

        // database
        $config->set('database.connections.testing.prefix', 'tests');
        $config->set('database.connections.pgsql.prefix', 'tests');
        $config->set('database.connections.mysql.prefix', 'tests');

        /** @var array<string, mixed> $mysql */
        $mysql = $config->get('database.connections.mysql');
        $config->set('database.connections.mariadb', array_merge($mysql, [
            'port' => 3307,
        ]));

        // new table name's
        $config->set('wallet.transaction.table', 'transaction');
        $config->set('wallet.transfer.table', 'transfer');
        $config->set('wallet.wallet.table', 'wallet');

        $config->set('wallet.cache.driver', $config->get('cache.driver'));
        $config->set('wallet.lock.driver', $config->get('cache.driver'));
    }
}
