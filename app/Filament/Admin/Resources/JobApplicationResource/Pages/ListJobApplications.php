<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\JobApplicationResource\Pages;

use App\Filament\Admin\Resources\JobApplicationResource;
use Filament\Resources\Pages\ListRecords;

/**
 * 管理者パネル用 応募一覧ページ
 */
class ListJobApplications extends ListRecords
{
    protected static string $resource = JobApplicationResource::class;
}
