<?php

declare(strict_types=1);

use Superern\Wallet\Internal\Assembler\AvailabilityDtoAssembler;
use Superern\Wallet\Internal\Assembler\BalanceUpdatedEventAssembler;
use Superern\Wallet\Internal\Assembler\ExtraDtoAssembler;
use Superern\Wallet\Internal\Assembler\OptionDtoAssembler;
use Superern\Wallet\Internal\Assembler\TransactionCreatedEventAssembler;
use Superern\Wallet\Internal\Assembler\TransactionDtoAssembler;
use Superern\Wallet\Internal\Assembler\TransactionQueryAssembler;
use Superern\Wallet\Internal\Assembler\TransferDtoAssembler;
use Superern\Wallet\Internal\Assembler\TransferLazyDtoAssembler;
use Superern\Wallet\Internal\Assembler\TransferQueryAssembler;
use Superern\Wallet\Internal\Events\BalanceUpdatedEvent;
use Superern\Wallet\Internal\Events\TransactionCreatedEvent;
use Superern\Wallet\Internal\Events\WalletCreatedEvent;
use Superern\Wallet\Internal\Repository\TransactionRepository;
use Superern\Wallet\Internal\Repository\TransferRepository;
use Superern\Wallet\Internal\Repository\WalletRepository;
use Superern\Wallet\Internal\Service\ClockService;
use Superern\Wallet\Internal\Service\ConnectionService;
use Superern\Wallet\Internal\Service\DatabaseService;
use Superern\Wallet\Internal\Service\DispatcherService;
use Superern\Wallet\Internal\Service\JsonService;
use Superern\Wallet\Internal\Service\LockService;
use Superern\Wallet\Internal\Service\MathService;
use Superern\Wallet\Internal\Service\StateService;
use Superern\Wallet\Internal\Service\StorageService;
use Superern\Wallet\Internal\Service\TranslatorService;
use Superern\Wallet\Internal\Service\UuidFactoryService;
use Superern\Wallet\Internal\Transform\TransactionDtoTransformer;
use Superern\Wallet\Internal\Transform\TransferDtoTransformer;
use Superern\Wallet\Models\Transaction;
use Superern\Wallet\Models\Transfer;
use Superern\Wallet\Models\Wallet;
use Superern\Wallet\Services\AssistantService;
use Superern\Wallet\Services\AtmService;
use Superern\Wallet\Services\AtomicService;
use Superern\Wallet\Services\BasketService;
use Superern\Wallet\Services\BookkeeperService;
use Superern\Wallet\Services\CastService;
use Superern\Wallet\Services\ConsistencyService;
use Superern\Wallet\Services\DiscountService;
use Superern\Wallet\Services\EagerLoaderService;
use Superern\Wallet\Services\ExchangeService;
use Superern\Wallet\Services\PrepareService;
use Superern\Wallet\Services\PurchaseService;
use Superern\Wallet\Services\RegulatorService;
use Superern\Wallet\Services\TaxService;
use Superern\Wallet\Services\TransactionService;
use Superern\Wallet\Services\TransferService;
use Superern\Wallet\Services\WalletService;

return [
    /**
     * Arbitrary Precision Calculator.
     *
     * 'scale' - length of the mantissa
     */
    'math' => [
        'scale' => 64,
    ],

    /**
     * Storage of the state of the balance of wallets.
     */
    'cache' => [
        'driver' => env('WALLET_CACHE_DRIVER', 'array'),
        'ttl' => env('WALLET_CACHE_TTL', 24 * 3600),
    ],

    /**
     * A system for dealing with race conditions.
     */
    'lock' => [
        'driver' => env('WALLET_LOCK_DRIVER', 'array'),
        'seconds' => env('WALLET_LOCK_TTL', 1),
    ],

    /**
     * Internal services that can be overloaded.
     */
    'internal' => [
        'clock' => ClockService::class,
        'connection' => ConnectionService::class,
        'database' => DatabaseService::class,
        'dispatcher' => DispatcherService::class,
        'json' => JsonService::class,
        'lock' => LockService::class,
        'math' => MathService::class,
        'state' => StateService::class,
        'storage' => StorageService::class,
        'translator' => TranslatorService::class,
        'uuid' => UuidFactoryService::class,
    ],

    /**
     * Services that can be overloaded.
     */
    'services' => [
        'assistant' => AssistantService::class,
        'atm' => AtmService::class,
        'atomic' => AtomicService::class,
        'basket' => BasketService::class,
        'bookkeeper' => BookkeeperService::class,
        'regulator' => RegulatorService::class,
        'cast' => CastService::class,
        'consistency' => ConsistencyService::class,
        'discount' => DiscountService::class,
        'eager_loader' => EagerLoaderService::class,
        'exchange' => ExchangeService::class,
        'prepare' => PrepareService::class,
        'purchase' => PurchaseService::class,
        'tax' => TaxService::class,
        'transaction' => TransactionService::class,
        'transfer' => TransferService::class,
        'wallet' => WalletService::class,
    ],

    /**
     * Repositories for fetching data from the database.
     */
    'repositories' => [
        'transaction' => TransactionRepository::class,
        'transfer' => TransferRepository::class,
        'wallet' => WalletRepository::class,
    ],

    /**
     * Objects of transformer from DTO to array.
     */
    'transformers' => [
        'transaction' => TransactionDtoTransformer::class,
        'transfer' => TransferDtoTransformer::class,
    ],

    /**
     * Builder class, needed to create DTO.
     */
    'assemblers' => [
        'availability' => AvailabilityDtoAssembler::class,
        'balance_updated_event' => BalanceUpdatedEventAssembler::class,
        'extra' => ExtraDtoAssembler::class,
        'option' => OptionDtoAssembler::class,
        'transaction' => TransactionDtoAssembler::class,
        'transfer_lazy' => TransferLazyDtoAssembler::class,
        'transfer' => TransferDtoAssembler::class,
        'transaction_created_event' => TransactionCreatedEventAssembler::class,
        'transaction_query' => TransactionQueryAssembler::class,
        'transfer_query' => TransferQueryAssembler::class,
    ],

    /**
     * Package system events.
     */
    'events' => [
        'balance_updated' => BalanceUpdatedEvent::class,
        'wallet_created' => WalletCreatedEvent::class,
        'transaction_created' => TransactionCreatedEvent::class,
    ],

    /**
     * Base model 'transaction'.
     */
    'transaction' => [
        'table' => 'transactions',
        'model' => Transaction::class,
    ],

    /**
     * Base model 'transfer'.
     */
    'transfer' => [
        'table' => 'transfers',
        'model' => Transfer::class,
    ],

    /**
     * Base model 'wallet'.
     */
    'wallet' => [
        'table' => 'wallets',
        'model' => Wallet::class,
        'creating' => [],
        'default' => [
            'name' => 'Default Wallet',
            'slug' => 'default',
            'meta' => [],
        ],
    ],
];
