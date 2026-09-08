<?php

namespace Database\Factories\Crm;

use App\Modules\Crm\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'legal_name' => fn (array $attributes) => fake()->boolean(40) ? $attributes['name'].' GmbH' : null,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
