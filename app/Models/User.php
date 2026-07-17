<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
// class User extends Authenticatable implements MustVerifyEmail
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function boards(): HasMany
    {
        return $this->hasMany(Board::class);
    }

    /**
     * Check if the user is allowed to create a new board based on SaaS limits and RBAC.
     */
    public function canCreateBoard(): bool
    {
        // 1. Admins and Managers have bypassed limits. Premium subscribers can create boards if subscription is active.
        if (in_array($this->role, array('admin', 'manager')) || ($this->is_subscribed && $this->subscription_ends_at && $this->subscription_ends_at->isFuture())) {
            return true;
        }

        // 2. Free tier users are limited to a maximum of 3 boards.
        return $this->boards()->count() < 3;
    }

    /**
     * The boards that the user belongs to.
     */
    public function sharedBoards(): BelongsToMany
    {
        return $this->belongsToMany(Board::class, 'board_user')->withPivot('role')->withTimestamps()->orderBy('position', 'asc');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_subscribed' => 'boolean',
            'subscription_ends_at' => 'datetime',
        ];
    }
}
