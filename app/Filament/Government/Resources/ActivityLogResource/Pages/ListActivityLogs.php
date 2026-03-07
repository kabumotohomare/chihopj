<?php

declare(strict_types=1);

namespace App\Filament\Government\Resources\ActivityLogResource\Pages;

use App\Filament\Government\Resources\ActivityLogResource;
use Filament\Resources\Pages\ListRecords;

/**
 * 役所パネル用 操作ログ一覧ページ
 */
class ListActivityLogs extends ListRecords
{
    protected static string $resource = ActivityLogResource::class;
}
