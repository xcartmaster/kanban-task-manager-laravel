<?php

namespace Database\Factories;

use App\Models\Board;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Board>
 */
class BoardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = ucfirst(fake()->sentence(rand(2, 3), false) . ' ' . fake()->randomElement(['project', 'board']));

        // Generate the base slug from the generated board name
        $baseSlug = str($name)->slug();

        // Create a unique 4-character random hash to append to the slug
        $hash = substr(md5(uniqid()), 0, 4);

        return [
            'user_id' => User::factory(),
            'name' => $name,
            'slug' => $baseSlug . '-' . $hash
        ];
    }
}
