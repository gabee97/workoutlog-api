<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ExerciseController;
use App\Http\Controllers\Api\V1\MuscleGroupController;
use App\Http\Controllers\Api\V1\WorkoutController;
use App\Http\Controllers\Api\V1\WorkoutExerciseController;
use App\Http\Controllers\Api\V1\WorkoutExerciseSetController;

Route::prefix('v1')->group(function () {

    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);

        // Muscle Groups
        Route::apiResource('muscle-groups', \App\Http\Controllers\Api\V1\MuscleGroupController::class);
        Route::post('muscle-groups/{muscleGroup}/hide', [MuscleGroupController::class, 'hide']);
        Route::delete('muscle-groups/{muscleGroup}/hide', [MuscleGroupController::class, 'unhide']);

        // Exercises
        Route::apiResource('exercises', \App\Http\Controllers\Api\V1\ExerciseController::class);
        Route::post('exercises/{exercise}/hide', [ExerciseController::class, 'hide']);
        Route::delete('exercises/{exercise}/hide', [ExerciseController::class, 'unhide']);

        // Workouts
        Route::apiResource('workouts', \App\Http\Controllers\Api\V1\WorkoutController::class);
        Route::post('workouts/{workout}/hide', [WorkoutController::class, 'hide']);
        Route::delete('workouts/{workout}/hide', [WorkoutController::class, 'unhide']);
        Route::post('workouts/{workout}/clone', [WorkoutController::class, 'clone']); // copiar default pro usuário
        Route::apiResource('workouts', WorkoutController::class);

        // Relacionar exercícios ao treino
        Route::post('workouts/{workout}/exercises', [WorkoutExerciseController::class, 'store']);
        Route::put('workout-exercises/{workoutExercise}', [WorkoutExerciseController::class, 'update']);
        Route::delete('workout-exercises/{workoutExercise}', [WorkoutExerciseController::class, 'destroy']);

        // Sets
        Route::post('workout-exercises/{workoutExercise}/sets', [WorkoutExerciseSetController::class, 'store']);
        Route::put('workout-exercise-sets/{workoutExerciseSet}', [WorkoutExerciseSetController::class, 'update']);
        Route::delete('workout-exercise-sets/{workoutExerciseSet}', [WorkoutExerciseSetController::class, 'destroy']);
    });

});
