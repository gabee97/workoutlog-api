<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_user_id')->default(0)->after('id');

            // Remove uniques antigos (globais)
            $table->dropUnique(['name']);
            $table->dropUnique(['slug']);

            // Cria uniques por dono
            $table->unique(['owner_user_id', 'name']);
            $table->unique(['owner_user_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            $table->dropUnique(['owner_user_id', 'name']);
            $table->dropUnique(['owner_user_id', 'slug']);

            $table->unique('name');
            $table->unique('slug');

            $table->dropColumn('owner_user_id');
        });
    }
};

