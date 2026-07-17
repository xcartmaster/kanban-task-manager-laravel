<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Cviebrock\EloquentSluggable\Sluggable;

class Board extends Model
{
    use HasFactory;
    use Sluggable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['user_id', 'name', 'position', 'slug'];

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array<string, mixed>
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    /**
     * Get the user that owns the board.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the columns for the board.
     */
    public function columns(): HasMany
    {
        return $this->hasMany(Column::class)->orderBy('position', 'asc');
    }

    /**
     * The users that belong to the board.
     */
    public function members(): belongsToMany
    {
        return $this->belongsToMany(User::class, 'board_user')->withPivot('role')->withTimestamps();
    }
}
