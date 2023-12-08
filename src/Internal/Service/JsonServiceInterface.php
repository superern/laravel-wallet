<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Service;

interface JsonServiceInterface
{
    /**
     * @param array<mixed>|null $data
     */
    public function encode(?array $data): ?string;
}
