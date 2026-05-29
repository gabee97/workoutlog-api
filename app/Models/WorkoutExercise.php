<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkoutExercise extends Model
{
    protected $fillable = [
        'workout_day_id',
        'exercise_id',
        'target_sets',
        'min_reps',
        'max_reps',
        'rest_seconds',
        'notes',
        'sort_order',
    ];

    public function workoutDay(): BelongsTo
    {
        return $this->belongsTo(WorkoutDay::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function sets()
    {
        return $this->hasMany(WorkoutExerciseSet::class);
    }
}
