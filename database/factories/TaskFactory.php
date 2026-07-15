<?php

namespace Database\Factories;

use App\Models\Column;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start_at = fake()->dateTimeBetween('-5 days', '15 days');

        return [
            'column_id' => Column::factory(),
            'title' => fake()->sentence(rand(3, 4)),
            'description' => Collection::times(rand(1, 2), fn() => fake()->paragraph(rand(2, 3)))->join("\n\n"),
            'position' => rand(0, 10),
            'start_at' => $start_at,
            'due_at' => Carbon::parse($start_at)->addDays(rand(10, 30)),
        ];
    }
}
