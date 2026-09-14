<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Core\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Role>
 */
final class RoleFactory extends Factory
{
    protected $model = Role::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(1),
            'name' => fake()->word(),
            'is_active' => true,
        ];
    }
}
