<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Service;

use Superern\Wallet\Internal\Exceptions\RecordNotFoundException;

interface StorageServiceInterface
{
    public function flush(): bool;

    public function forget(string $uuid): bool;

    /**
     * @throws RecordNotFoundException
     */
    public function get(string $uuid): string;

    public function sync(string $uuid, float|int|string $value): bool;

    /**
     * @throws RecordNotFoundException
     */
    public function increase(string $uuid, float|int|string $value): string;

    /**
     * @template T of non-empty-array<string>
     *
     * @param T $uuids
     *
     * @return non-empty-array<value-of<T>, string>
     *
     * @throws RecordNotFoundException
     */
    public function multiGet(array $uuids): array;

    /**
     * @param non-empty-array<string, float|int|string> $inputs
     */
    public function multiSync(array $inputs): bool;

    /**
     * @template T of non-empty-array<string, float|int|string>
     *
     * @param T $inputs
     *
     * @return non-empty-array<key-of<T>, string>
     *
     * @throws RecordNotFoundException
     */
    public function multiIncrease(array $inputs): array;
}
