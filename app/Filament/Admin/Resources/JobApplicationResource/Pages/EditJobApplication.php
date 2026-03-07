<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\JobApplicationResource\Pages;

use App\Filament\Admin\Resources\JobApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

/**
 * 管理者パネル用 応募編集ページ
 */
class EditJobApplication extends EditRecord
{
    protected static string $resource = JobApplicationResource::class;

    /**
     * @return array<Actions\Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
        ];
    }
}
