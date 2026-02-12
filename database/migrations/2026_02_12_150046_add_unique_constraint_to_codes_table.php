<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 重複データを削除（idが小さい方を残す）
        DB::statement('
            DELETE c1 FROM codes c1
            INNER JOIN codes c2 
            WHERE c1.type = c2.type 
            AND c1.type_id = c2.type_id 
            AND c1.id > c2.id
        ');

        // ユニーク制約を追加
        Schema::table('codes', function (Blueprint $table) {
            $table->unique(['type', 'type_id'], 'codes_type_type_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('codes', function (Blueprint $table) {
            $table->dropUnique('codes_type_type_id_unique');
        });
    }
};
