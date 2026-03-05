<?php

declare(strict_types=1);

namespace App\Filament\Government\Resources;

use App\Filament\Exports\JobApplicationCsvExport;
use App\Filament\Government\Resources\JobApplicationResource\Pages;
use App\Models\JobApplication;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * 役所パネル用 応募一覧リソース（閲覧専用）
 */
class JobApplicationResource extends Resource
{
    /** @var class-string */
    protected static ?string $model = JobApplication::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = '応募一覧';

    protected static ?string $modelLabel = '応募';

    protected static ?string $pluralModelLabel = '応募一覧';

    /**
     * ナビゲーションから非表示にする（CSVダウンロードページに置き換え）
     */
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('jobPost.job_title')
                    ->label('求人タイトル')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('worker.name')
                    ->label('応募者名')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('worker.email')
                    ->label('メールアドレス')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('ステータス')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'applied' => '応募中',
                        'accepted' => '承認',
                        'rejected' => '不承認',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'applied' => 'warning',
                        'accepted' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('applied_at')
                    ->label('応募日')
                    ->dateTime('Y/m/d')
                    ->sortable(),
                TextColumn::make('judged_at')
                    ->label('判定日')
                    ->dateTime('Y/m/d')
                    ->sortable(),
            ])
            ->defaultSort('applied_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('ステータス')
                    ->options([
                        'applied' => '応募中',
                        'accepted' => '承認',
                        'rejected' => '不承認',
                    ]),
            ])
            ->headerActions([
                Action::make('csv_download')
                    ->label('CSVダウンロード')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->action(fn () => JobApplicationCsvExport::download()),
            ])
            ->actions([
                ViewAction::make(),
            ]);
    }

    /**
     * @return array<string, \Filament\Resources\Pages\PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobApplications::route('/'),
            'view' => Pages\ViewJobApplication::route('/{record}'),
        ];
    }
}
