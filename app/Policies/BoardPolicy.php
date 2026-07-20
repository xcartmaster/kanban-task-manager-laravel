<?php

namespace App\Policies;

use App\Models\Board;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BoardPolicy
{
    /**
     * Determine whether the user can create a new board based on SaaS limits.
     */
    public function create(User $user): bool
    {
        // Delegate the business rules check directly to the User model method
        return $user->canCreateBoard();
    }
}
