<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkoutDay extends Model
{
    protected $fillable = [
        'workout_id',
        'name',
        'sort_order',
    ];

    public function workout(): BelongsTo
    {
        return $this->belongsTo(Workout::class);
    }

    public function workoutExercises(): HasMany
    {
        return $this->hasMany(WorkoutExercise::class)->orderBy('sort_order');
    }
}
