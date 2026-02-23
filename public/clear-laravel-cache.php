<?php
// Laravel キャッシュクリアスクリプト（緊急用）
// 使用後は必ず削除すること

$basePath = dirname(__DIR__);
$result = [];

// bootstrap/cache/ 内のキャッシュファイルを削除
$cacheFiles = [
    $basePath . '/bootstrap/cache/routes-v7.php',
    $basePath . '/bootstrap/cache/config.php',
    $basePath . '/bootstrap/cache/services.php',
    $basePath . '/bootstrap/cache/packages.php',
];

foreach ($cacheFiles as $file) {
    if (file_exists($file)) {
        $deleted = @unlink($file);
        $result[basename($file)] = $deleted ? 'deleted' : 'failed';
    } else {
        $result[basename($file)] = 'not_found';
    }
}

// storage/framework/views/ 内のビューキャッシュを削除
$viewCachePath = $basePath . '/storage/framework/views';
if (is_dir($viewCachePath)) {
    $files = glob($viewCachePath . '/*');
    $deletedCount = 0;
    foreach ($files as $file) {
        if (is_file($file) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            if (@unlink($file)) {
                $deletedCount++;
            }
        }
    }
    $result['view_cache'] = $deletedCount . ' files deleted';
}

// storage/framework/cache/data/ 内のキャッシュを削除
$dataCachePath = $basePath . '/storage/framework/cache/data';
if (is_dir($dataCachePath)) {
    $files = glob($dataCachePath . '/*/*');
    $deletedCount = 0;
    foreach ($files as $file) {
        if (is_file($file)) {
            if (@unlink($file)) {
                $deletedCount++;
            }
        }
    }
    $result['data_cache'] = $deletedCount . ' files deleted';
}

header('Content-Type: application/json');
echo json_encode([
    'status' => 'success',
    'timestamp' => date('Y-m-d H:i:s'),
    'result' => $result,
    'message' => 'Laravel cache cleared. Please delete this file immediately!',
    'warning' => 'This file should be deleted for security reasons.',
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
