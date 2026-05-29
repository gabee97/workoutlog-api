<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkoutExerciseSet;

class WorkoutExerciseSetPolicy
{
    public function update(User $user, WorkoutExerciseSet $workoutExerciseSet): bool
    {
        return (int) $workoutExerciseSet->workoutExercise->workoutDay->workout->owner_user_id === (int) $user->id;
    }

    public function delete(User $user, WorkoutExerciseSet $workoutExerciseSet): bool
    {
        return (int) $workoutExerciseSet->workoutExercise->workoutDay->workout->owner_user_id === (int) $user->id;
    }
}
