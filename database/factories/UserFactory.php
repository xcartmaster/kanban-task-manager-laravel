<?php

namespace Database\Factories;

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
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => 'user',
            'is_subscribed' => false,
            'subscription_ends_at' => null,
        ];
    }

    public function subscribed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_subscribed' => true,
            'subscription_ends_at' => fake()->dateTimeBetween('+1 month', '+12 months'),
        ]);
    }

    public function expiredSubscription(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_subscribed' => false,
            'subscription_ends_at' => fake()->dateTimeBetween('-6 months', '-1 day'),
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
        ]);
    }

    /**
     * Indicate that the user is an admin and should be linked to their boards via pivot.
     */
    public function adminAsBoardOwner(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
        ])->afterCreating(function (User $user) {
            // Retrieve all boards that were just created for this user
            $boards = $user->boards;

            // Securely attach the user to each board with pivot data without breaking factory chains
            $user->sharedBoards()->attach($boards, ['role' => 'owner']);
        });
    }

    public function manager(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'manager',
            'name' => 'Manager Assistant',
            'email' => 'manager@example.com',
        ]);
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
}
