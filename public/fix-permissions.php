<?php
/**
 * 緊急パーミッション修正・診断スクリプト
 * 
 * 使用方法: https://hiraizumin.com/fix-permissions.php にアクセス
 * 使用後必ず削除してください
 */

// セキュリティ: 本番環境でのみ実行可能にする
$allowedIPs = ['127.0.0.1', '::1']; // 必要に応じてIPを追加
// if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', $allowedIPs)) {
//     http_response_code(403);
//     die('Access denied');
// }

$basePath = dirname(__DIR__);
$results = [];
$errors = [];

// 1. 現在のパーミッション状態を確認
$results['current_permissions'] = [];

$checkDirs = [
    'storage/framework/views',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/logs',
    'bootstrap/cache',
];

foreach ($checkDirs as $dir) {
    $fullPath = $basePath . '/' . $dir;
    if (is_dir($fullPath)) {
        $perms = fileperms($fullPath);
        $owner = function_exists('posix_getpwuid') ? posix_getpwuid(fileowner($fullPath)) : null;
        $group = function_exists('posix_getgrgid') ? posix_getgrgid(filegroup($fullPath)) : null;
        
        $results['current_permissions'][$dir] = [
            'permissions' => substr(sprintf('%o', $perms), -4),
            'owner' => $owner['name'] ?? fileowner($fullPath),
            'group' => $group['name'] ?? filegroup($fullPath),
            'writable' => is_writable($fullPath),
        ];
    } else {
        $errors[] = "Directory not found: $dir";
    }
}

// 2. パーミッション修正を試行
$results['permission_fixes'] = [];

foreach ($checkDirs as $dir) {
    $fullPath = $basePath . '/' . $dir;
    if (is_dir($fullPath)) {
        // 書き込み権限を追加
        $oldPerms = fileperms($fullPath);
        $newPerms = $oldPerms | 0775;
        
        if (@chmod($fullPath, $newPerms)) {
            $results['permission_fixes'][$dir] = 'chmod 775 applied';
        } else {
            $errors[] = "Failed to chmod: $dir";
        }
    }
}

// 3. ビューキャッシュをクリア
$viewPath = $basePath . '/storage/framework/views';
$viewFiles = glob($viewPath . '/*.php');
$viewCount = 0;
foreach ($viewFiles as $file) {
    if (basename($file) !== '.gitignore' && @unlink($file)) {
        $viewCount++;
    }
}
$results['view_cache_cleared'] = $viewCount . ' files deleted';

// 4. 設定キャッシュをクリア
$configCache = $basePath . '/bootstrap/cache/config.php';
if (file_exists($configCache) && @unlink($configCache)) {
    $results['config_cache'] = 'deleted';
}

$routeCache = $basePath . '/bootstrap/cache/routes-v7.php';
if (file_exists($routeCache) && @unlink($routeCache)) {
    $results['route_cache'] = 'deleted';
}

// 5. Webサーバープロセス情報
$results['web_server'] = [
    'php_user' => function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['name'] : posix_geteuid(),
    'php_group' => function_exists('posix_getgrgid') ? posix_getgrgid(posix_getegid())['name'] : posix_getegid(),
];

// 6. 修正後のパーミッション状態
$results['after_permissions'] = [];
foreach ($checkDirs as $dir) {
    $fullPath = $basePath . '/' . $dir;
    if (is_dir($fullPath)) {
        $perms = fileperms($fullPath);
        $results['after_permissions'][$dir] = [
            'permissions' => substr(sprintf('%o', $perms), -4),
            'writable' => is_writable($fullPath),
        ];
    }
}

// 7. テストファイル作成
$testFile = $basePath . '/storage/framework/views/test-write-' . time() . '.txt';
$canWrite = @file_put_contents($testFile, 'test');
if ($canWrite) {
    @unlink($testFile);
    $results['write_test'] = 'SUCCESS - Can write to views directory';
} else {
    $errors[] = 'FAILED - Cannot write to views directory';
    $results['write_test'] = 'FAILED';
}

// 出力
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'status' => empty($errors) ? 'success' : 'partial_success',
    'timestamp' => date('Y-m-d H:i:s'),
    'results' => $results,
    'errors' => $errors,
    'next_steps' => [
        '1. このファイルを削除: rm public/fix-permissions.php',
        '2. ブラウザのキャッシュをクリア（Ctrl+Shift+Delete）',
        '3. https://hiraizumin.com/worker/edit に再アクセス',
        '4. まだエラーが出る場合は、サーバー管理者に連絡してください',
    ],
    'warning' => '⚠️ このファイルは使用後必ず削除してください！',
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
