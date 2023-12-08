<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Service;

interface TranslatorServiceInterface
{
    public function get(string $key): string;
}
