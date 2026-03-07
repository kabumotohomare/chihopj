<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ActivityLogResource\Pages;

use App\Filament\Admin\Resources\ActivityLogResource;
use Filament\Resources\Pages\ListRecords;

/**
 * 管理者パネル用 操作ログ一覧ページ
 */
class ListActivityLogs extends ListRecords
{
    protected static string $resource = ActivityLogResource::class;
}
