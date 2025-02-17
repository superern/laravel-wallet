<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Service;

use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

final class StateService implements StateServiceInterface
{
    private const RANDOM_BYTES = 3;

    /**
     * Keeps the state of balance
     */
    private const PREFIX_STATE = 'wallet_s::';

    /**
     * Stores a callback reference
     */
    private const PREFIX_FORK_REF = 'wallet_f::';

    /**
     * Stores a pair of uuid with forkId
     */
    private const PREFIX_FORK_ID = 'wallet_fc::';

    /**
     * Stores all uuids for a particular forkId
     */
    private const PREFIX_HASHMAP = 'wallet_hm::';

    private readonly CacheRepository $store;

    public function __construct(CacheFactory $cacheFactory)
    {
        $this->store = $cacheFactory->store('array');
    }

    /**
     * @param string[] $uuids
     * @param callable(): array<string, string> $value
     */
    public function multiFork(array $uuids, callable $value): void
    {
        $forkId = $this->getForkId();

        $values = [
            self::PREFIX_FORK_REF . $forkId => $value,
            self::PREFIX_HASHMAP . $forkId => $uuids,
        ];

        foreach ($uuids as $uuid) {
            $values[self::PREFIX_STATE . $uuid] = null;
            $values[self::PREFIX_FORK_ID . $uuid] = $forkId;
        }

        $this->store->setMultiple($values);
    }

    public function get(string $uuid): ?string
    {
        $value = $this->store->get(self::PREFIX_STATE . $uuid);
        if ($value !== null) {
            return $value;
        }

        $forkId = $this->store->pull(self::PREFIX_FORK_ID . $uuid);
        if ($forkId === null) {
            return null;
        }

        /** @var null|callable(): array<string, string> $callable */
        $callable = $this->store->pull(self::PREFIX_FORK_REF . $forkId);
        if ($callable === null) {
            return null;
        }

        $insertValues = [];
        $results = $callable();
        foreach ($results as $rUuid => $rValue) {
            $insertValues[self::PREFIX_STATE . $rUuid] = $rValue;
        }

        // set new values
        $this->store->setMultiple($insertValues);

        /** @var array<string> $uuids */
        $uuids = $this->store->pull(self::PREFIX_HASHMAP . $forkId, []);
        $deleteKeys = array_map(static fn (string $uuid) => self::PREFIX_FORK_ID . $uuid, $uuids);

        // delete callables by uuids
        $this->store->deleteMultiple($deleteKeys);

        return $results[$uuid] ?? null;
    }

    public function drop(string $uuid): void
    {
        $deleteKeys = [self::PREFIX_STATE . $uuid];

        $forkId = $this->store->pull(self::PREFIX_FORK_ID . $uuid);
        if ($forkId !== null) {
            $deleteKeys[] = self::PREFIX_FORK_REF . $forkId;
            $deleteKeys[] = self::PREFIX_HASHMAP . $forkId;
        }

        $this->store->deleteMultiple($deleteKeys);
    }

    private function getForkId(): string
    {
        do {
            $forkId = bin2hex(random_bytes(self::RANDOM_BYTES));
        } while ($this->store->has(self::PREFIX_FORK_REF . $forkId));

        return $forkId;
    }
}
