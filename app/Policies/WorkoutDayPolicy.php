<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkoutDay;

class WorkoutDayPolicy
{
    public function update(User $user, WorkoutDay $workoutDay): bool
    {
        return (int) $workoutDay->workout->owner_user_id === (int) $user->id;
    }

    public function delete(User $user, WorkoutDay $workoutDay): bool
    {
        return (int) $workoutDay->workout->owner_user_id === (int) $user->id;
    }
}
