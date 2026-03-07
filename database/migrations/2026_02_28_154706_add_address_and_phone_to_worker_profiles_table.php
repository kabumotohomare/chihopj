<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * worker_profiles に現住所・電話番号カラムを追加
     */
    public function up(): void
    {
        Schema::table('worker_profiles', function (Blueprint $table) {
            $table->string('current_address')->nullable()->after('current_location_1_id')->comment('現住所');
            $table->string('phone_number', 20)->nullable()->after('current_address')->comment('電話番号');
        });
    }

    /**
     * 追加カラムを削除（ロールバック）
     */
    public function down(): void
    {
        Schema::table('worker_profiles', function (Blueprint $table) {
            $table->dropColumn(['current_address', 'phone_number']);
        });
    }
};
