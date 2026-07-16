<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = ['column_id', 'title', 'description', 'position', 'start_at', 'due_at'];

    public function column(): BelongsTo
    {
        return $this->belongsTo(Column::class);
    }

    public function comments(): HasMany
    {
        // Sort comments from oldest to newest to build a proper timeline chat
        return $this->hasMany(Comment::class)->orderBy('created_at', 'asc');
    }

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'due_at' => 'datetime',
        ];
    }
}
