<?php

namespace Database\Seeders;

use App\Models\Exercise;
use App\Models\MuscleGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ExerciseSeeder extends Seeder
{
    public function run(): void
    {
        $exercises = [
            // Peito
            ['name' => 'Supino reto com barra', 'group' => 'Peito', 'equipment' => 'barra', 'level' => 3],
            ['name' => 'Supino com halteres', 'group' => 'Peito', 'equipment' => 'halteres', 'level' => 2],

            // Costas
            ['name' => 'Puxada frontal', 'group' => 'Costas', 'equipment' => 'máquina', 'level' => 1],
            ['name' => 'Remada curvada', 'group' => 'Costas', 'equipment' => 'barra', 'level' => 5],

            // Pernas
            ['name' => 'Agachamento livre', 'group' => 'Quadríceps', 'equipment' => 'barra', 'level' => 5],
            ['name' => 'Leg press', 'group' => 'Quadríceps', 'equipment' => 'máquina', 'level' => 1],

            // Bíceps
            ['name' => 'Rosca direta', 'group' => 'Bíceps', 'equipment' => 'barra', 'level' => 1],

            // Tríceps
            ['name' => 'Tríceps testa', 'group' => 'Tríceps', 'equipment' => 'barra', 'level' => 3],
        ];

        foreach ($exercises as $index => $item) {
            $muscleGroup = MuscleGroup::where('name', $item['group'])->first();

            if (!$muscleGroup) {
                continue;
            }

            Exercise::create([
                'owner_user_id'   => 0,
                'name'            => $item['name'],
                'slug'            => Str::slug($item['name']),
                'muscle_group_id' => $muscleGroup->id,
                'equipment'       => $item['equipment'],
                'level'           => $item['level'],
                'sort_order'      => $index + 1,
            ]);
        }
    }
}
