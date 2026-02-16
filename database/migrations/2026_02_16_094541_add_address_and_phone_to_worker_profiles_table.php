<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * マイグレーションを実行
     * 現在のお住まい1の詳細住所と電話番号を追加
     */
    public function up(): void
    {
        Schema::table('worker_profiles', function (Blueprint $table) {
            $table->string('current_address', 200)
                ->after('current_location_1_id')
                ->comment('現在のお住まい1の詳細住所（町名番地建物名）');
            $table->string('phone_number', 30)
                ->after('current_address')
                ->comment('電話番号');
        });
    }

    /**
     * マイグレーションをロールバック
     */
    public function down(): void
    {
        Schema::table('worker_profiles', function (Blueprint $table) {
            $table->dropColumn(['current_address', 'phone_number']);
        });
    }
};
