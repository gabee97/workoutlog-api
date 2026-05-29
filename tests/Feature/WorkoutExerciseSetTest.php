<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\MuscleGroup;
use App\Models\User;
use App\Models\Workout;
use App\Models\WorkoutDay;
use App\Models\WorkoutExercise;
use App\Models\WorkoutExerciseSet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkoutExerciseSetTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Workout $workout;
    protected WorkoutDay $workoutDay;
    protected WorkoutExercise $workoutExercise;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        
        $muscleGroup = MuscleGroup::create(['name' => 'Peito', 'owner_user_id' => 0]);
        $exercise = Exercise::create(['name' => 'Supino', 'muscle_group_id' => $muscleGroup->id, 'owner_user_id' => 0]);

        $this->workout = Workout::create([
            'name' => 'Treino A',
            'owner_user_id' => $this->user->id,
            'slug' => 'treino-a'
        ]);

        $this->workoutDay = WorkoutDay::create([
            'workout_id' => $this->workout->id,
            'name' => 'Dia 1',
            'sort_order' => 1
        ]);

        $this->workoutExercise = WorkoutExercise::create([
            'workout_day_id' => $this->workoutDay->id,
            'exercise_id' => $exercise->id,
            'target_sets' => 3
        ]);
    }

    public function test_can_add_set_to_exercise()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/workout-exercises/{$this->workoutExercise->id}/sets", [
                'reps' => 10,
                'weight' => 50.5,
                'rest_seconds' => 90,
                'rir' => 2,
                'notes' => 'Série pesada'
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.reps', 10)
            ->assertJsonPath('data.set_number', 1);

        $this->assertDatabaseHas('workout_exercise_sets', [
            'workout_exercise_id' => $this->workoutExercise->id,
            'reps' => 10,
            'set_number' => 1
        ]);
    }

    public function test_can_update_set()
    {
        $set = WorkoutExerciseSet::create([
            'workout_exercise_id' => $this->workoutExercise->id,
            'set_number' => 1,
            'reps' => 8
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/workout-exercise-sets/{$set->id}", [
                'reps' => 12
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.reps', 12);

        $this->assertDatabaseHas('workout_exercise_sets', [
            'id' => $set->id,
            'reps' => 12
        ]);
    }

    public function test_can_delete_set_and_reorder()
    {
        WorkoutExerciseSet::create([
            'workout_exercise_id' => $this->workoutExercise->id,
            'set_number' => 1,
            'reps' => 10
        ]);
        $set2 = WorkoutExerciseSet::create([
            'workout_exercise_id' => $this->workoutExercise->id,
            'set_number' => 2,
            'reps' => 12
        ]);
        WorkoutExerciseSet::create([
            'workout_exercise_id' => $this->workoutExercise->id,
            'set_number' => 3,
            'reps' => 15
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/workout-exercise-sets/{$set2->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('workout_exercise_sets', ['id' => $set2->id]);
        
        $this->assertDatabaseHas('workout_exercise_sets', [
            'workout_exercise_id' => $this->workoutExercise->id,
            'reps' => 10,
            'set_number' => 1
        ]);
        $this->assertDatabaseHas('workout_exercise_sets', [
            'workout_exercise_id' => $this->workoutExercise->id,
            'reps' => 15,
            'set_number' => 2 // Reordenado de 3 para 2
        ]);
    }

    public function test_cannot_modify_other_users_sets()
    {
        $otherUser = User::factory()->create();
        $set = WorkoutExerciseSet::create([
            'workout_exercise_id' => $this->workoutExercise->id,
            'set_number' => 1,
            'reps' => 8
        ]);

        $response = $this->actingAs($otherUser)
            ->putJson("/api/v1/workout-exercise-sets/{$set->id}", [
                'reps' => 12
            ]);

        $response->assertStatus(403);
    }
}
