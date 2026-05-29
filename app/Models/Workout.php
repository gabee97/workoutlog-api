<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Workout extends Model
{
    protected $fillable = [
        'owner_user_id',
        'name',
        'slug',
        'description',
        'is_active',
        'sort_order',
    ];

    public function workoutDays(): HasMany
    {
        return $this->hasMany(WorkoutDay::class)->orderBy('sort_order');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function scopeVisibleTo(Builder $query, int $userId): Builder
    {
        return $query->whereIn('owner_user_id', [0, $userId]);
    }
}
