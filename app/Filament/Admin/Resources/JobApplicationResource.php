<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\JobApplicationResource\Pages;
use App\Filament\Exports\JobApplicationCsvExport;
use App\Models\JobApplication;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * 管理者パネル用 応募管理リソース（全機能）
 */
class JobApplicationResource extends Resource
{
    /** @var class-string */
    protected static ?string $model = JobApplication::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = '応募管理';

    protected static ?string $modelLabel = '応募';

    protected static ?string $pluralModelLabel = '応募管理';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Select::make('status')
                    ->label('ステータス')
                    ->options([
                        'applied' => '応募中',
                        'accepted' => '承認',
                        'rejected' => '不承認',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('jobPost.job_title')
                    ->label('求人タイトル')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('jobPost.company.name')
                    ->label('企業名')
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
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
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
            'edit' => Pages\EditJobApplication::route('/{record}/edit'),
        ];
    }
}
