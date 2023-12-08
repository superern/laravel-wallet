<?php

declare(strict_types=1);

namespace Superern\Wallet\Test\Infra\Factories;

use Superern\Wallet\Test\Infra\Models\ItemMinTax;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ItemMinTax>
 */
final class ItemMinTaxFactory extends Factory
{
    protected $model = ItemMinTax::class;

    public function definition(): array
    {
        return [
            'name' => fake()
                ->domainName,
            'price' => random_int(1, 100),
            'quantity' => random_int(0, 10),
        ];
    }
}
