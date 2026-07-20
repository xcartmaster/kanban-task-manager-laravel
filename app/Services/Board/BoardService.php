<?php

namespace App\Services\Board;

use App\Models\Board;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BoardService
{
    public function createBoardForUser(User $user, array $data): Board
    {
        return DB::transaction(function () use ($user, $data) {
            $board = $user->boards()->create([
                'name' => $data['name'],
                // Set the incrementing position based on currently existing user boards count
                'position' => $user->boards()->count(),
            ]);

            // 2. Immediately establish the Many-to-Many pivot link as the absolute owner
            $board->members()->attach($user->id, ['role' => 'owner']);

            return $board;
        });
    }
}
