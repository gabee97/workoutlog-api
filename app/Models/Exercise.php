<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Exercise extends Model
{
    protected $fillable = [
        'owner_user_id',
        'name',
        'slug',
        'muscle_group_id',
        'equipment',
        'level',
        'instructions',
        'video_url',
        'is_active',
        'sort_order',
    ];

    public function muscleGroup()
    {
        return $this->belongsTo(MuscleGroup::class);
    }

    public function scopeVisibleTo(Builder $query, int $userId): Builder
    {
        return $query->whereIn('owner_user_id', [0, $userId]);
    }
}
