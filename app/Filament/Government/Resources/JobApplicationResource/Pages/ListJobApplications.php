<?php

declare(strict_types=1);

namespace App\Filament\Government\Resources\JobApplicationResource\Pages;

use App\Filament\Government\Resources\JobApplicationResource;
use Filament\Resources\Pages\ListRecords;

/**
 * 役所パネル用 応募一覧ページ
 */
class ListJobApplications extends ListRecords
{
    protected static string $resource = JobApplicationResource::class;
}
