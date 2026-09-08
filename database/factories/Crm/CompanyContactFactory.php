<?php

namespace Database\Factories\Crm;

use App\Modules\Crm\Models\Company;
use App\Modules\Crm\Models\CompanyContact;
use App\Modules\Crm\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CompanyContact>
 */
class CompanyContactFactory extends Factory
{
    protected $model = CompanyContact::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'person_id' => Person::factory(),
            'role' => fake()->optional()->jobTitle(),
            'is_primary' => false,
        ];
    }

    public function primary(): static
    {
        return $this->state(['is_primary' => true]);
    }
}
