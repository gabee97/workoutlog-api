<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_workout_programs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('workout_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedInteger('order_index')
                ->comment('Ordem do treino dentro do programa do usuário');

            $table->boolean('is_active')
                ->default(true);

            $table->date('started_at')
                ->nullable()
                ->comment('Data em que o treino entrou no programa do usuário');

            $table->date('ended_at')
                ->nullable()
                ->comment('Data em que o treino deixou de ser usado no programa');

            $table->timestamps();

            $table->unique(['user_id', 'workout_id']);
            $table->unique(['user_id', 'order_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_workout_programs');
    }
};
