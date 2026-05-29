<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkoutExerciseSet extends Model
{
    protected $fillable = [
        'workout_exercise_id',
        'set_number',
        'reps',
        'weight',
        'rest_seconds',
        'rir',
        'notes',
    ];

    public function workoutExercise(): BelongsTo
    {
        return $this->belongsTo(WorkoutExercise::class);
    }
}
