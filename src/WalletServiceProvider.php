<?php

declare(strict_types=1);

namespace Superern\Wallet;

use Superern\Wallet\External\Api\TransactionQueryHandler;
use Superern\Wallet\External\Api\TransactionQueryHandlerInterface;
use Superern\Wallet\External\Api\TransferQueryHandler;
use Superern\Wallet\External\Api\TransferQueryHandlerInterface;
use Superern\Wallet\Internal\Assembler\AvailabilityDtoAssembler;
use Superern\Wallet\Internal\Assembler\AvailabilityDtoAssemblerInterface;
use Superern\Wallet\Internal\Assembler\BalanceUpdatedEventAssembler;
use Superern\Wallet\Internal\Assembler\BalanceUpdatedEventAssemblerInterface;
use Superern\Wallet\Internal\Assembler\ExtraDtoAssembler;
use Superern\Wallet\Internal\Assembler\ExtraDtoAssemblerInterface;
use Superern\Wallet\Internal\Assembler\OptionDtoAssembler;
use Superern\Wallet\Internal\Assembler\OptionDtoAssemblerInterface;
use Superern\Wallet\Internal\Assembler\TransactionCreatedEventAssembler;
use Superern\Wallet\Internal\Assembler\TransactionCreatedEventAssemblerInterface;
use Superern\Wallet\Internal\Assembler\TransactionDtoAssembler;
use Superern\Wallet\Internal\Assembler\TransactionDtoAssemblerInterface;
use Superern\Wallet\Internal\Assembler\TransactionQueryAssembler;
use Superern\Wallet\Internal\Assembler\TransactionQueryAssemblerInterface;
use Superern\Wallet\Internal\Assembler\TransferDtoAssembler;
use Superern\Wallet\Internal\Assembler\TransferDtoAssemblerInterface;
use Superern\Wallet\Internal\Assembler\TransferLazyDtoAssembler;
use Superern\Wallet\Internal\Assembler\TransferLazyDtoAssemblerInterface;
use Superern\Wallet\Internal\Assembler\TransferQueryAssembler;
use Superern\Wallet\Internal\Assembler\TransferQueryAssemblerInterface;
use Superern\Wallet\Internal\Assembler\WalletCreatedEventAssembler;
use Superern\Wallet\Internal\Assembler\WalletCreatedEventAssemblerInterface;
use Superern\Wallet\Internal\Decorator\StorageServiceLockDecorator;
use Superern\Wallet\Internal\Events\BalanceUpdatedEvent;
use Superern\Wallet\Internal\Events\BalanceUpdatedEventInterface;
use Superern\Wallet\Internal\Events\TransactionCreatedEvent;
use Superern\Wallet\Internal\Events\TransactionCreatedEventInterface;
use Superern\Wallet\Internal\Events\WalletCreatedEvent;
use Superern\Wallet\Internal\Events\WalletCreatedEventInterface;
use Superern\Wallet\Internal\Repository\TransactionRepository;
use Superern\Wallet\Internal\Repository\TransactionRepositoryInterface;
use Superern\Wallet\Internal\Repository\TransferRepository;
use Superern\Wallet\Internal\Repository\TransferRepositoryInterface;
use Superern\Wallet\Internal\Repository\WalletRepository;
use Superern\Wallet\Internal\Repository\WalletRepositoryInterface;
use Superern\Wallet\Internal\Service\ClockService;
use Superern\Wallet\Internal\Service\ClockServiceInterface;
use Superern\Wallet\Internal\Service\ConnectionService;
use Superern\Wallet\Internal\Service\ConnectionServiceInterface;
use Superern\Wallet\Internal\Service\DatabaseService;
use Superern\Wallet\Internal\Service\DatabaseServiceInterface;
use Superern\Wallet\Internal\Service\DispatcherService;
use Superern\Wallet\Internal\Service\DispatcherServiceInterface;
use Superern\Wallet\Internal\Service\JsonService;
use Superern\Wallet\Internal\Service\JsonServiceInterface;
use Superern\Wallet\Internal\Service\LockService;
use Superern\Wallet\Internal\Service\LockServiceInterface;
use Superern\Wallet\Internal\Service\MathService;
use Superern\Wallet\Internal\Service\MathServiceInterface;
use Superern\Wallet\Internal\Service\StateService;
use Superern\Wallet\Internal\Service\StateServiceInterface;
use Superern\Wallet\Internal\Service\StorageService;
use Superern\Wallet\Internal\Service\StorageServiceInterface;
use Superern\Wallet\Internal\Service\TranslatorService;
use Superern\Wallet\Internal\Service\TranslatorServiceInterface;
use Superern\Wallet\Internal\Service\UuidFactoryService;
use Superern\Wallet\Internal\Service\UuidFactoryServiceInterface;
use Superern\Wallet\Internal\Transform\TransactionDtoTransformer;
use Superern\Wallet\Internal\Transform\TransactionDtoTransformerInterface;
use Superern\Wallet\Internal\Transform\TransferDtoTransformer;
use Superern\Wallet\Internal\Transform\TransferDtoTransformerInterface;
use Superern\Wallet\Models\Transaction;
use Superern\Wallet\Models\Transfer;
use Superern\Wallet\Models\Wallet;
use Superern\Wallet\Services\AssistantService;
use Superern\Wallet\Services\AssistantServiceInterface;
use Superern\Wallet\Services\AtmService;
use Superern\Wallet\Services\AtmServiceInterface;
use Superern\Wallet\Services\AtomicService;
use Superern\Wallet\Services\AtomicServiceInterface;
use Superern\Wallet\Services\BasketService;
use Superern\Wallet\Services\BasketServiceInterface;
use Superern\Wallet\Services\BookkeeperService;
use Superern\Wallet\Services\BookkeeperServiceInterface;
use Superern\Wallet\Services\CastService;
use Superern\Wallet\Services\CastServiceInterface;
use Superern\Wallet\Services\ConsistencyService;
use Superern\Wallet\Services\ConsistencyServiceInterface;
use Superern\Wallet\Services\DiscountService;
use Superern\Wallet\Services\DiscountServiceInterface;
use Superern\Wallet\Services\EagerLoaderService;
use Superern\Wallet\Services\EagerLoaderServiceInterface;
use Superern\Wallet\Services\ExchangeService;
use Superern\Wallet\Services\ExchangeServiceInterface;
use Superern\Wallet\Services\PrepareService;
use Superern\Wallet\Services\PrepareServiceInterface;
use Superern\Wallet\Services\PurchaseService;
use Superern\Wallet\Services\PurchaseServiceInterface;
use Superern\Wallet\Services\RegulatorService;
use Superern\Wallet\Services\RegulatorServiceInterface;
use Superern\Wallet\Services\TaxService;
use Superern\Wallet\Services\TaxServiceInterface;
use Superern\Wallet\Services\TransactionService;
use Superern\Wallet\Services\TransactionServiceInterface;
use Superern\Wallet\Services\TransferService;
use Superern\Wallet\Services\TransferServiceInterface;
use Superern\Wallet\Services\WalletService;
use Superern\Wallet\Services\WalletServiceInterface;
use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionCommitting;
use Illuminate\Database\Events\TransactionRolledBack;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use function config;
use function dirname;
use function function_exists;

final class WalletServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadTranslationsFrom(dirname(__DIR__) . '/resources/lang', 'wallet');

        Event::listen(TransactionBeginning::class, Internal\Listeners\TransactionBeginningListener::class);
        Event::listen(TransactionCommitting::class, Internal\Listeners\TransactionCommittingListener::class);
        Event::listen(TransactionCommitted::class, Internal\Listeners\TransactionCommittedListener::class);
        Event::listen(TransactionRolledBack::class, Internal\Listeners\TransactionRolledBackListener::class);

        // @codeCoverageIgnoreStart
        if (! $this->app->runningInConsole()) {
            return;
        }
        // @codeCoverageIgnoreEnd

        if (WalletConfigure::isRunsMigrations()) {
            $this->loadMigrationsFrom([dirname(__DIR__) . '/database']);
        }

        if (function_exists('config_path')) {
            $this->publishes([
                dirname(__DIR__) . '/config/config.php' => config_path('wallet.php'),
            ], 'laravel-wallet-config');
        }

        $this->publishes([
            dirname(__DIR__) . '/database/' => database_path('migrations'),
        ], 'laravel-wallet-migrations');
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(dirname(__DIR__) . '/config/config.php', 'wallet');

        /**
         * @var array{
         *     internal?: array<class-string|null>,
         *     services?: array<class-string|null>,
         *     cache?: array{driver: string|null},
         *     repositories?: array<class-string|null>,
         *     transformers?: array<class-string|null>,
         *     assemblers?: array<class-string|null>,
         *     events?: array<class-string|null>,
         *     transaction?: array{model?: class-string|null},
         *     transfer?: array{model?: class-string|null},
         *     wallet?: array{model?: class-string|null},
         * } $configure
         */
        $configure = config('wallet', []);

        $this->internal($configure['internal'] ?? []);
        $this->services($configure['services'] ?? [], $configure['cache'] ?? []);

        $this->repositories($configure['repositories'] ?? []);
        $this->transformers($configure['transformers'] ?? []);
        $this->assemblers($configure['assemblers'] ?? []);
        $this->events($configure['events'] ?? []);

        $this->bindObjects($configure);
    }

    /**
     * @return class-string[]
     */
    public function provides(): array
    {
        return array_merge(
            $this->internalProviders(),
            $this->servicesProviders(),
            $this->repositoriesProviders(),
            $this->transformersProviders(),
            $this->assemblersProviders(),
            $this->eventsProviders(),
            $this->bindObjectsProviders(),
        );
    }

    /**
     * @param array<class-string|null> $configure
     */
    private function repositories(array $configure): void
    {
        $this->app->singleton(
            TransactionRepositoryInterface::class,
            $configure['transaction'] ?? TransactionRepository::class
        );

        $this->app->singleton(
            TransferRepositoryInterface::class,
            $configure['transfer'] ?? TransferRepository::class
        );

        $this->app->singleton(WalletRepositoryInterface::class, $configure['wallet'] ?? WalletRepository::class);
    }

    /**
     * @param array<class-string|null> $configure
     */
    private function internal(array $configure): void
    {
        $this->app->alias($configure['storage'] ?? StorageService::class, 'wallet.internal.storage');
        $this->app->when($configure['storage'] ?? StorageService::class)
            ->needs('$ttl')
            ->giveConfig('wallet.cache.ttl');

        $this->app->singleton(ClockServiceInterface::class, $configure['clock'] ?? ClockService::class);
        $this->app->singleton(ConnectionServiceInterface::class, $configure['connection'] ?? ConnectionService::class);
        $this->app->singleton(DatabaseServiceInterface::class, $configure['database'] ?? DatabaseService::class);
        $this->app->singleton(DispatcherServiceInterface::class, $configure['dispatcher'] ?? DispatcherService::class);
        $this->app->singleton(JsonServiceInterface::class, $configure['json'] ?? JsonService::class);

        $this->app->when($configure['lock'] ?? LockService::class)
            ->needs('$seconds')
            ->giveConfig('wallet.lock.seconds', 1);

        $this->app->singleton(LockServiceInterface::class, $configure['lock'] ?? LockService::class);

        $this->app->when($configure['math'] ?? MathService::class)
            ->needs('$scale')
            ->giveConfig('wallet.math.scale', 64);

        $this->app->singleton(MathServiceInterface::class, $configure['math'] ?? MathService::class);
        $this->app->singleton(StateServiceInterface::class, $configure['state'] ?? StateService::class);
        $this->app->singleton(TranslatorServiceInterface::class, $configure['translator'] ?? TranslatorService::class);
        $this->app->singleton(UuidFactoryServiceInterface::class, $configure['uuid'] ?? UuidFactoryService::class);
    }

    /**
     * @param array<class-string|null> $configure
     * @param array{driver?: string|null} $cache
     */
    private function services(array $configure, array $cache): void
    {
        $this->app->singleton(AssistantServiceInterface::class, $configure['assistant'] ?? AssistantService::class);
        $this->app->singleton(AtmServiceInterface::class, $configure['atm'] ?? AtmService::class);
        $this->app->singleton(AtomicServiceInterface::class, $configure['atomic'] ?? AtomicService::class);
        $this->app->singleton(BasketServiceInterface::class, $configure['basket'] ?? BasketService::class);
        $this->app->singleton(CastServiceInterface::class, $configure['cast'] ?? CastService::class);
        $this->app->singleton(
            ConsistencyServiceInterface::class,
            $configure['consistency'] ?? ConsistencyService::class
        );
        $this->app->singleton(DiscountServiceInterface::class, $configure['discount'] ?? DiscountService::class);
        $this->app->singleton(
            EagerLoaderServiceInterface::class,
            $configure['eager_loader'] ?? EagerLoaderService::class
        );
        $this->app->singleton(ExchangeServiceInterface::class, $configure['exchange'] ?? ExchangeService::class);
        $this->app->singleton(PrepareServiceInterface::class, $configure['prepare'] ?? PrepareService::class);
        $this->app->singleton(PurchaseServiceInterface::class, $configure['purchase'] ?? PurchaseService::class);
        $this->app->singleton(TaxServiceInterface::class, $configure['tax'] ?? TaxService::class);
        $this->app->singleton(
            TransactionServiceInterface::class,
            $configure['transaction'] ?? TransactionService::class
        );
        $this->app->singleton(TransferServiceInterface::class, $configure['transfer'] ?? TransferService::class);
        $this->app->singleton(WalletServiceInterface::class, $configure['wallet'] ?? WalletService::class);

        // bookkeepper service
        $this->app->when(StorageServiceLockDecorator::class)
            ->needs(StorageServiceInterface::class)
            ->give(function () use ($cache) {
                return $this->app->make(
                    'wallet.internal.storage',
                    [
                        'cacheRepository' => $this->app->get(CacheFactory::class)
                            ->store($cache['driver'] ?? 'array'),
                    ],
                );
            });

        $this->app->when($configure['bookkeeper'] ?? BookkeeperService::class)
            ->needs(StorageServiceInterface::class)
            ->give(StorageServiceLockDecorator::class);

        $this->app->singleton(BookkeeperServiceInterface::class, $configure['bookkeeper'] ?? BookkeeperService::class);

        // regulator service
        $this->app->when($configure['regulator'] ?? RegulatorService::class)
            ->needs(StorageServiceInterface::class)
            ->give(function () {
                return $this->app->make(
                    'wallet.internal.storage',
                    [
                        'cacheRepository' => clone $this->app->make(CacheFactory::class)
                            ->store('array'),
                    ],
                );
            });

        $this->app->singleton(RegulatorServiceInterface::class, $configure['regulator'] ?? RegulatorService::class);
    }

    /**
     * @param array<class-string|null> $configure
     */
    private function assemblers(array $configure): void
    {
        $this->app->singleton(
            AvailabilityDtoAssemblerInterface::class,
            $configure['availability'] ?? AvailabilityDtoAssembler::class
        );

        $this->app->singleton(
            BalanceUpdatedEventAssemblerInterface::class,
            $configure['balance_updated_event'] ?? BalanceUpdatedEventAssembler::class
        );

        $this->app->singleton(ExtraDtoAssemblerInterface::class, $configure['extra'] ?? ExtraDtoAssembler::class);

        $this->app->singleton(
            OptionDtoAssemblerInterface::class,
            $configure['option'] ?? OptionDtoAssembler::class
        );

        $this->app->singleton(
            TransactionDtoAssemblerInterface::class,
            $configure['transaction'] ?? TransactionDtoAssembler::class
        );

        $this->app->singleton(
            TransferLazyDtoAssemblerInterface::class,
            $configure['transfer_lazy'] ?? TransferLazyDtoAssembler::class
        );

        $this->app->singleton(
            TransferDtoAssemblerInterface::class,
            $configure['transfer'] ?? TransferDtoAssembler::class
        );

        $this->app->singleton(
            TransactionQueryAssemblerInterface::class,
            $configure['transaction_query'] ?? TransactionQueryAssembler::class
        );

        $this->app->singleton(
            TransferQueryAssemblerInterface::class,
            $configure['transfer_query'] ?? TransferQueryAssembler::class
        );

        $this->app->singleton(
            WalletCreatedEventAssemblerInterface::class,
            $configure['wallet_created_event'] ?? WalletCreatedEventAssembler::class
        );

        $this->app->singleton(
            TransactionCreatedEventAssemblerInterface::class,
            $configure['transaction_created_event'] ?? TransactionCreatedEventAssembler::class
        );
    }

    /**
     * @param array<class-string|null> $configure
     */
    private function transformers(array $configure): void
    {
        $this->app->singleton(
            TransactionDtoTransformerInterface::class,
            $configure['transaction'] ?? TransactionDtoTransformer::class
        );

        $this->app->singleton(
            TransferDtoTransformerInterface::class,
            $configure['transfer'] ?? TransferDtoTransformer::class
        );
    }

    /**
     * @param array<class-string|null> $configure
     */
    private function events(array $configure): void
    {
        $this->app->bind(
            BalanceUpdatedEventInterface::class,
            $configure['balance_updated'] ?? BalanceUpdatedEvent::class
        );

        $this->app->bind(
            WalletCreatedEventInterface::class,
            $configure['wallet_created'] ?? WalletCreatedEvent::class
        );

        $this->app->bind(
            TransactionCreatedEventInterface::class,
            $configure['transaction_created'] ?? TransactionCreatedEvent::class
        );
    }

    /**
     * @param array{
     *     transaction?: array{model?: class-string|null},
     *     transfer?: array{model?: class-string|null},
     *     wallet?: array{model?: class-string|null},
     * } $configure
     */
    private function bindObjects(array $configure): void
    {
        $this->app->bind(Transaction::class, $configure['transaction']['model'] ?? null);
        $this->app->bind(Transfer::class, $configure['transfer']['model'] ?? null);
        $this->app->bind(Wallet::class, $configure['wallet']['model'] ?? null);

        // api
        $this->app->bind(TransactionQueryHandlerInterface::class, TransactionQueryHandler::class);
        $this->app->bind(TransferQueryHandlerInterface::class, TransferQueryHandler::class);
    }

    /**
     * @return class-string[]
     */
    private function internalProviders(): array
    {
        return [
            ClockServiceInterface::class,
            ConnectionServiceInterface::class,
            DatabaseServiceInterface::class,
            DispatcherServiceInterface::class,
            JsonServiceInterface::class,
            LockServiceInterface::class,
            MathServiceInterface::class,
            StateServiceInterface::class,
            TranslatorServiceInterface::class,
            UuidFactoryServiceInterface::class,
        ];
    }

    /**
     * @return class-string[]
     */
    private function servicesProviders(): array
    {
        return [
            AssistantServiceInterface::class,
            AtmServiceInterface::class,
            AtomicServiceInterface::class,
            BasketServiceInterface::class,
            CastServiceInterface::class,
            ConsistencyServiceInterface::class,
            DiscountServiceInterface::class,
            EagerLoaderServiceInterface::class,
            ExchangeServiceInterface::class,
            PrepareServiceInterface::class,
            PurchaseServiceInterface::class,
            TaxServiceInterface::class,
            TransactionServiceInterface::class,
            TransferServiceInterface::class,
            WalletServiceInterface::class,

            BookkeeperServiceInterface::class,
            RegulatorServiceInterface::class,
        ];
    }

    /**
     * @return class-string[]
     */
    private function repositoriesProviders(): array
    {
        return [
            TransactionRepositoryInterface::class,
            TransferRepositoryInterface::class,
            WalletRepositoryInterface::class,
        ];
    }

    /**
     * @return class-string[]
     */
    private function transformersProviders(): array
    {
        return [
            AvailabilityDtoAssemblerInterface::class,
            BalanceUpdatedEventAssemblerInterface::class,
            ExtraDtoAssemblerInterface::class,
            OptionDtoAssemblerInterface::class,
            TransactionDtoAssemblerInterface::class,
            TransferLazyDtoAssemblerInterface::class,
            TransferDtoAssemblerInterface::class,
            TransactionQueryAssemblerInterface::class,
            TransferQueryAssemblerInterface::class,
            WalletCreatedEventAssemblerInterface::class,
            TransactionCreatedEventAssemblerInterface::class,
        ];
    }

    /**
     * @return class-string[]
     */
    private function assemblersProviders(): array
    {
        return [TransactionDtoTransformerInterface::class, TransferDtoTransformerInterface::class];
    }

    /**
     * @return class-string[]
     */
    private function eventsProviders(): array
    {
        return [
            BalanceUpdatedEventInterface::class,
            WalletCreatedEventInterface::class,
            TransactionCreatedEventInterface::class,
        ];
    }

    /**
     * @return class-string[]
     */
    private function bindObjectsProviders(): array
    {
        return [TransactionQueryHandlerInterface::class, TransferQueryHandlerInterface::class];
    }
}
