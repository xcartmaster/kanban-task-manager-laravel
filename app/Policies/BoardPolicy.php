<?php

namespace App\Policies;

use App\Models\Board;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BoardPolicy
{
    /**
     * Intercept all checks to grant global access for moderators.
     */
    public function before(User $user, string $ability): ?bool
    {
        // Admins and managers can bypass any policy checks to moderate spam
        if (in_array($user->role, ['admin', 'manager'])) {
            return true;
        }

        // Return null to let Laravel fall back to specific policy methods below
        return null;
    }

    /**
     * Determine whether the user can create a new board based on SaaS limits.
     */
    public function create(User $user): bool
    {
        // Delegate the business rules check directly to the User model method
        return $user->canCreateBoard();
    }

    public function view(User $user, Board $board): bool
    {
        // Regular users must be linked to the board as members
        return $board->members()->where('user_id', $user->id)->exists();
    }
}
