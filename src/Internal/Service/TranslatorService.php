<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Service;

use Illuminate\Contracts\Translation\Translator;

final class TranslatorService implements TranslatorServiceInterface
{
    public function __construct(
        private readonly Translator $translator
    ) {
    }

    public function get(string $key): string
    {
        $value = $this->translator->get($key);
        assert(is_string($value));

        return $value;
    }
}
