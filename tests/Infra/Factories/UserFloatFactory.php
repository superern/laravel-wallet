<?php

declare(strict_types=1);

namespace Superern\Wallet\Test\Infra\Factories;

use Superern\Wallet\Test\Infra\Models\UserFloat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserFloat>
 */
final class UserFloatFactory extends Factory
{
    protected $model = UserFloat::class;

    public function definition(): array
    {
        return [
            'name' => fake()
                ->name,
            'email' => fake()
                ->unique()
                ->safeEmail,
        ];
    }
}
