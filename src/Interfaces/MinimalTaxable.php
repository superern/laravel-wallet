<?php

declare(strict_types=1);

namespace Superern\Wallet\Interfaces;

interface MinimalTaxable extends Taxable
{
    public function getMinimalFee(): float|int;
}
