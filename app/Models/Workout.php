<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Workout extends Model
{
    public function scopeVisibleTo(Builder $query, int $userId): Builder
    {
        return $query->whereIn('owner_user_id', [0, $userId]);
    }
}
