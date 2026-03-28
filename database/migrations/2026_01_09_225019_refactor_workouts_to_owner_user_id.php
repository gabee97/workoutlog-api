<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('workouts', function (Blueprint $table) {

            // 1) Primeiro remove FK + coluna user_id (isso destrava a remoção dos índices)
            $table->dropConstrainedForeignId('user_id');

            // 2) Agora sim remove os uniques antigos (se existirem)
            // (o Laravel vai tentar pelo nome padrão; se der "index not found", a gente troca pelo nome exato)
            $table->dropUnique(['user_id', 'name']);
            $table->dropUnique(['user_id', 'slug']);

            // 3) Adiciona owner_user_id
            $table->unsignedBigInteger('owner_user_id')->default(0)->after('id');

            // 4) Cria uniques novos por dono
            $table->unique(['owner_user_id', 'name']);
            $table->unique(['owner_user_id', 'slug']);
        });
    }


    public function down(): void
    {
        Schema::table('workouts', function (Blueprint $table) {
            $table->dropUnique(['owner_user_id', 'name']);
            $table->dropUnique(['owner_user_id', 'slug']);

            $table->dropColumn('owner_user_id');

            // Volta pro user_id
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->unique(['user_id', 'name']);
            $table->unique(['user_id', 'slug']);
        });
    }
};
