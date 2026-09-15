<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Core\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Site>
 */
final class SiteFactory extends Factory
{
    protected $model = Site::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->city(),
            'street' => fake()->streetName().' '.fake()->buildingNumber(),
            'postal_code' => fake()->postcode(),
            'city' => fake()->city(),
        ];
    }
}
