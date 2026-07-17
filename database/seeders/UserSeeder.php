<?php

namespace Database\Seeders;

use App\Models\Board;
use App\Models\Column;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create 1 Super Admin for testing login (with immediate structure)
        DB::transaction(function () {
            User::factory()->admin()
                ->has(Board::factory()->count(5)->sequence(fn ($sequence) => ['position' => $sequence->index])
                    ->has(Column::factory()->count(4)->sequenceWithNameAndPosition()
                        ->has(Task::factory()->count(rand(2, 6)))->sequence(fn ($sequence) => ['position' => $sequence->index])
                    )
                )
                ->create();
        });

        // 2. Create 1 Manager Assistant
        User::factory()->manager()->create();

        // 3. Create 50 regular users without any subscription
        User::factory()->count(50)->create()->each(function (User $user) {
            DB::transaction(function () use ($user) {
                Board::factory()->count(rand(1, 3))->sequence(fn ($sequence) => ['position' => $sequence->index])->create(['user_id' => $user->id])->each(function (Board $board){
                    Column::factory()->count(4)->sequenceWithNameAndPosition()->create(['board_id' => $board->id])->each(function (Column $column){
                        Task::factory()->count(rand(0, 5))
                            ->sequence(fn ($sequence) => [
                                'position' => $sequence->index
                            ])
                            ->create(['column_id' => $column->id]);
                    });
                });
            });
        });

        // 4. Create 30 users with an active premium subscription
        User::factory()->count(30)->subscribed()->create()->each(function (User $user) {
            DB::transaction(function () use ($user) {
                Board::factory()->count(rand(4, 7))->sequence(fn ($sequence) => ['position' => $sequence->index])->create(['user_id' => $user->id])->each(function (Board $board){
                   Column::factory()->count(4)->sequenceWithNameAndPosition()->create(['board_id' => $board->id])->each(function (Column $column){
                      Task::factory()->count(rand(0, 5))->sequence(fn ($sequence) => [
                          'position' => $sequence->index
                      ])
                      ->create(['column_id' => $column->id]);
                   });
                });
            });
        });

        // 5. Create 20 users with an expired subscription (to test limits)
        User::factory()->count(20)->expiredSubscription()->create()->each(function (User $user) {
            DB::transaction(function () use ($user) {
                Board::factory()->count(rand(5, 6))->sequence(fn ($sequence) => ['position' => $sequence->index])->create(['user_id' => $user->id])->each(function (Board $board) {
                    Column::factory()->count(4)->sequenceWithNameAndPosition()->create(['board_id' => $board->id])->each(function (Column $column) {
                        Task::factory()->count(rand(2, 7))
                            ->sequence(fn ($sequence) => [
                                'position' => $sequence->index
                            ])
                            ->create(['column_id' => $column->id]);
                    });
                });
            });
        });

        /*
        // 1. Disable query logging to prevent memory leaks from millions of INSERT statements
        DB::connection()->disableQueryLog();

        // 2. Prevent PHP execution timeout
        set_time_limit(0);

        $totalUsers = 1000;
        $chunkSize = 100;

        for ($i = 0; $i < $totalUsers; $i += $chunkSize) {
            // Generate chunk entries
            User::factory()->count($chunkSize)->expiredSubscription()->create()->each(function (User $user) {
                // Wrap each user infrastructure creation in a standalone atomic transaction
                DB::transaction(function () use ($user) {
                    Board::factory()->count(rand(5, 6))->sequence(fn ($sequence) => ['position' => $sequence->index])->create(['user_id' => $user->id])->each(function (Board $board) {
                        Column::factory()->count(4)->sequenceWithNameAndPosition()->create(['board_id' => $board->id])->each(function (Column $column) {
                            Task::factory()->count(rand(2, 7))
                                ->sequence(fn ($sequence) => [
                                    'position' => $sequence->index
                                ])
                                ->create(['column_id' => $column->id]);
                        });
                    });
                });
            });

            // 3. Force PHP's garbage collector to free up memory used by Eloquent models in current chunk
            gc_collect_cycles();
        }
        */

    }
}
