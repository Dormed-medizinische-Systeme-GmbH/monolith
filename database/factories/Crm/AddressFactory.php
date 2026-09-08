<?php

namespace Database\Factories\Crm;

use App\Modules\Crm\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    protected $model = Address::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'street' => fake()->streetName(),
            'house_number' => (string) fake()->numberBetween(1, 199),
            'postal_code' => fake()->numerify('#####'),
            'city' => fake()->city(),
            'country_code' => 'DE',
        ];
    }
}
