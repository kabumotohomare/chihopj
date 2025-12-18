<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationSeeder extends Seeder
{
    /**
     * 市区町村データをシード
     */
    public function run(): void
    {
        // 外部キー制約を一時的に無効化
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 既存データをクリア
        DB::table('locations')->truncate();

        // 外部キー制約を再度有効化
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $csvFile = base_path('files/市区町村コード.csv');

        if (! file_exists($csvFile)) {
            $this->command->error("CSVファイルが見つかりません: {$csvFile}");

            return;
        }

        $file = fopen($csvFile, 'r');

        if ($file === false) {
            $this->command->error('CSVファイルを開けません');

            return;
        }

        // ヘッダー行をスキップ
        fgetcsv($file);

        $locations = [];
        $count = 0;

        while (($row = fgetcsv($file)) !== false) {
            // 空行をスキップ
            if (empty($row[0])) {
                continue;
            }

            $code = trim($row[0]);
            $prefecture = trim($row[1]);
            $city = ! empty($row[2]) ? trim($row[2]) : null;

            // 都道府県のみのレコード（市区町村名が空）
            if ($city === null || $city === '') {
                $locations[] = [
                    'code' => $code,
                    'prefecture' => $prefecture,
                    'city' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            } else {
                // 市区町村を含むレコード
                $locations[] = [
                    'code' => $code,
                    'prefecture' => $prefecture,
                    'city' => $city,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $count++;

            // 500件ごとにバッチ挿入
            if (count($locations) >= 500) {
                DB::table('locations')->insert($locations);
                $locations = [];
            }
        }

        // 残りのデータを挿入
        if (! empty($locations)) {
            DB::table('locations')->insert($locations);
        }

        fclose($file);

        $this->command->info("市区町村データを{$count}件登録しました。");
    }
}
