<?php

declare(strict_types=1);

namespace Superern\Wallet\Interfaces;

interface Discount
{
    public function getPersonalDiscount(Customer $customer): float|int;
}
