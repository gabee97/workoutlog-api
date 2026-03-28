<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class MuscleGroup extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'is_active',
        'sort_order',
    ];

    public function exercises()
    {
        return $this->hasMany(Exercise::class);
    }

    public function scopeVisibleTo(Builder $query, int $userId): Builder
    {
        return $query->whereIn('owner_user_id', [0, $userId]);
    }
}
