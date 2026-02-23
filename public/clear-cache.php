<?php
// 一時的なキャッシュクリアスクリプト
// 使用後は必ず削除すること

$result = [
    'opcache_reset' => false,
    'realpath_cache_size' => false,
];

// OPcache のリセット
if (function_exists('opcache_reset')) {
    $result['opcache_reset'] = opcache_reset();
}

// realpath cache のクリア
if (function_exists('clearstatcache')) {
    clearstatcache(true);
    $result['realpath_cache_size'] = 'cleared';
}

header('Content-Type: application/json');
echo json_encode([
    'status' => 'success',
    'timestamp' => date('Y-m-d H:i:s'),
    'result' => $result,
    'message' => 'Cache cleared. Please delete this file after use.',
], JSON_PRETTY_PRINT);
