<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MuscleGroup;
use Illuminate\Support\Str;


class MuscleGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = [
            'Peito',
            'Costas',
            'Ombros',
            'Bíceps',
            'Tríceps',
            'Antebraço',
            'Abdômen',
            'Lombar',
            'Glúteos',
            'Quadríceps',
            'Posterior de coxa',
            'Panturrilha',
        ];

        foreach ($groups as $index => $name) {
            MuscleGroup::create([
                'owner_user_id' => 0,
                'name'       => $name,
                'slug'       => Str::slug($name),
                'sort_order' => $index + 1,
            ]);
        }
    }
}
