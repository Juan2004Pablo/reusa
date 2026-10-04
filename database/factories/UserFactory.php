<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => null,
            'community' => null,
            'role' => UserRole::User,
            'is_active' => true,
            'terms_accepted_at' => now(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Admin,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withContact(): static
    {
        return $this->state(fn (array $attributes) => [
            'phone' => '300'.fake()->numerify('#######'),
            'community' => fake()->randomElement(['Laureles', 'Belén', 'El Poblado', 'Robledo', 'Envigado']),
        ]);
    }

    /**
     * Two-factor authentication is not enabled in ReUsa yet (no columns exist),
     * so this state is intentionally a no-op that keeps the kit's tests compiling.
     */
    public function withTwoFactor(): static
    {
        return $this;
    }
}
