<?php
/**
 * 緊急キャッシュクリアスクリプト
 * このファイルは使用後必ず削除してください
 */

$basePath = dirname(__DIR__);
$results = [];

// 1. OPcache クリア
if (function_exists('opcache_reset')) {
    opcache_reset();
    $results['opcache'] = 'cleared';
}

// 2. Realpath cache クリア
clearstatcache(true);
$results['realpath_cache'] = 'cleared';

// 3. ビューキャッシュを全削除
$viewPath = $basePath . '/storage/framework/views';
$viewFiles = glob($viewPath . '/*.php');
$viewCount = 0;
foreach ($viewFiles as $file) {
    if (unlink($file)) {
        $viewCount++;
    }
}
$results['view_cache'] = $viewCount . ' files deleted';

// 4. Livewire キャッシュを削除
$livewirePath = $basePath . '/storage/framework/cache/livewire';
if (is_dir($livewirePath)) {
    $livewireFiles = glob($livewirePath . '/*');
    $livewireCount = 0;
    foreach ($livewireFiles as $file) {
        if (is_file($file) && unlink($file)) {
            $livewireCount++;
        }
    }
    $results['livewire_cache'] = $livewireCount . ' files deleted';
}

// 5. ルートキャッシュを削除
$routeCache = $basePath . '/bootstrap/cache/routes-v7.php';
if (file_exists($routeCache) && unlink($routeCache)) {
    $results['route_cache'] = 'deleted';
} else {
    $results['route_cache'] = 'not_found';
}

// 6. コンフィグキャッシュを削除
$configCache = $basePath . '/bootstrap/cache/config.php';
if (file_exists($configCache) && unlink($configCache)) {
    $results['config_cache'] = 'deleted';
} else {
    $results['config_cache'] = 'not_found';
}

// 7. データキャッシュを削除
$dataPath = $basePath . '/storage/framework/cache/data';
if (is_dir($dataPath)) {
    $dirs = glob($dataPath . '/*', GLOB_ONLYDIR);
    $dataCount = 0;
    foreach ($dirs as $dir) {
        $files = glob($dir . '/*');
        foreach ($files as $file) {
            if (is_file($file) && unlink($file)) {
                $dataCount++;
            }
        }
    }
    $results['data_cache'] = $dataCount . ' files deleted';
}

// 8. ファイル確認
$registerFile = $basePath . '/resources/views/livewire/worker/register.blade.php';
$fileExists = file_exists($registerFile);
$fileSize = $fileExists ? filesize($registerFile) : 0;
$fileModified = $fileExists ? date('Y-m-d H:i:s', filemtime($registerFile)) : 'not found';

$results['source_file'] = [
    'exists' => $fileExists,
    'size' => $fileSize,
    'modified' => $fileModified,
];

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'status' => 'success',
    'timestamp' => date('Y-m-d H:i:s'),
    'results' => $results,
    'next_steps' => [
        '1. Clear browser cache (Ctrl+Shift+Delete)',
        '2. Access: http://133.88.118.54/worker/register in incognito mode',
        '3. You should see yellow debug info',
        '4. DELETE THIS FILE IMMEDIATELY: /clear-all-cache.php',
    ],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
