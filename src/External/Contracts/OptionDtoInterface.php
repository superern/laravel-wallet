<?php

declare(strict_types=1);

namespace Superern\Wallet\External\Contracts;

interface OptionDtoInterface
{
    /**
     * @return null|array<mixed>
     */
    public function getMeta(): ?array;

    public function isConfirmed(): bool;

    public function getUuid(): ?string;
}
