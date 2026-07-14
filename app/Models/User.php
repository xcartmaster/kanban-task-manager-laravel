<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use \Illuminate\Database\Eloquent\Relations\hasMany;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function boards(): hasMany
    {
        return $this->hasMany(Board::class);
    }

    public function canCreateBoard(): bool
    {
        // 1. Проверяем, активна ли подписка прямо сейчас.
        // Метод isFuture() проверяет, находится ли дата окончания в будущем.
        if ($this->is_subscribed && $this->subscription_ends_at && $this->subscription_ends_at->isFuture()) {
            return true;
        }

        // 2. Если подписки нет или она истекла, проверяем лимит бесплатных досок
        return $this->boards()->count() < 3;
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
