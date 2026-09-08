<?php

namespace Database\Factories\Crm;

use App\Modules\Crm\Models\Company;
use App\Modules\Crm\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    protected $model = Location::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => fake()->randomElement(['Hauptstandort', 'Praxis Nord', 'Filiale Süd', 'Zentrallager', 'Werkstatt']),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
