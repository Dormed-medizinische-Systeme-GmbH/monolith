<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Core\Models\Role;
use App\Modules\Core\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
final class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'is_admin' => false,
            'is_active' => true,
            // `role_id` ist NOT NULL (D-124) — ohne Rolle gibt es keinen
            // Mitarbeiter. Die Fabrik nimmt die erste vorhandene, damit Tests
            // nicht jedes Mal eine anlegen muessen.
            'role_id' => Role::query()->value('id') ?? Role::factory(),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (): array => ['is_admin' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }

    public function role(string $key): static
    {
        return $this->state(fn (): array => [
            'role_id' => Role::query()->where('key', $key)->value('id') ?? Role::factory()->state(['key' => $key]),
        ]);
    }

    public function withTwoFactor(): static
    {
        return $this->state(fn (): array => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn (): array => ['email_verified_at' => null]);
    }
}
