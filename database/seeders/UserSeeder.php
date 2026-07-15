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
        // 1. Create 1 Super Admin for testing login
        DB::transaction(function () {
            User::factory()->admin()
                ->has(Board::factory()->count(5)
                    ->has(Column::factory()->count(4)->sequenceWithNameAndPosition()
                        ->has(Task::factory()->count(5))
                    )
                )
                ->create();
        });

        // 2. Create 1 Manager Assistant
        DB::transaction(function () {
            User::factory()->manager()->create();
        });

        // 3. Create 50 regular users without any subscription
        DB::transaction(function () {
            User::factory()->count(50)->create()->each(function (User $user) {
                Board::factory()->count(rand(1, 3))->create(['user_id' => $user->id])->each(function (Board $board){
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
        DB::transaction(function () {
            User::factory()->count(30)->subscribed()->create()->each(function (User $user) {
                Board::factory()->count(rand(4, 7))->create(['user_id' => $user->id])->each(function (Board $board){
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
        DB::transaction(function () {
            User::factory()->count(20)->expiredSubscription()->create()->each(function (User $user) {
                Board::factory()->count(rand(5, 6))->create(['user_id' => $user->id])->each(function (Board $board) {
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
        // If you need to seed a large amount of data (e.g., 20,000+ users),
        // processing it in small chunks prevents PHP from running out of memory.
        set_time_limit(0);

        $totalUsers = 20000;
        $chunkSize = 100;

        for ($i = 0; $i < $totalUsers; $i += $chunkSize) {
            DB::transaction(function () use ($chunkSize) {
                User::factory()->count($chunkSize)->expiredSubscription()->create()->each(function (User $user) {
                    Board::factory()->count(rand(5, 6))->create(['user_id' => $user->id])->each(function (Board $board) {
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

            // Force PHP's garbage collector to free up memory used by Eloquent models
            // generated in the current chunk iteration.
            gc_collect_cycles();
        }
        */
    }
}
