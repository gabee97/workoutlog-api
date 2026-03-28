<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('exercises', function (Blueprint $table) {
            $table->id();

            $table->string('name')->unique();
            $table->string('slug')->unique();

            $table->foreignId('muscle_group_id')
                ->constrained('muscle_groups')
                ->restrictOnDelete();

            $table->string('equipment')->nullable();

            $table->unsignedTinyInteger('level')
                ->nullable()
                ->comment('1 muito fácil, 5 muito difícil (complexidade de execução)');

            $table->text('instructions')->nullable();
            $table->string('video_url')->nullable();

            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
};
