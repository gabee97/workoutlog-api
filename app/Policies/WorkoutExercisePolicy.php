<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkoutExercise;

class WorkoutExercisePolicy
{
    public function update(User $user, WorkoutExercise $workoutExercise): bool
    {
        return (int) $workoutExercise->workoutDay->workout->owner_user_id === (int) $user->id;
    }

    public function delete(User $user, WorkoutExercise $workoutExercise): bool
    {
        return (int) $workoutExercise->workoutDay->workout->owner_user_id === (int) $user->id;
    }
}
