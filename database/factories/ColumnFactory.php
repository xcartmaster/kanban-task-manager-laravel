<?php

namespace Database\Factories;

use App\Models\Board;
use App\Models\Column;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Column>
 */
class ColumnFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'board_id' => Board::factory(),
            'name' => 'Default Column', // fallback value
            'position' => 0, // we will override this dynamically
        ];
    }

    public function sequenceWithNameAndPosition(): static
    {
        $columnsWithNameAndPosition = [
            ['name' => 'Todo',        'position' => 0],
            ['name' => 'In Progress', 'position' => 1],
            ['name' => 'Review',      'position' => 2],
            ['name' => 'Done',        'position' => 3]
        ];

        return $this->sequence(...$columnsWithNameAndPosition);
    }
}
