<?php

declare(strict_types=1);

namespace App\Filament\Government\Pages;

use App\Filament\Exports\JobApplicationCsvExport;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * 役所パネル用 CSVダウンロードページ
 *
 * From/To の期間指定で応募データを CSV エクスポートする。
 */
class CsvDownload extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowDownTray;

    protected static ?string $navigationLabel = 'CSVダウンロード';

    protected static ?string $title = 'CSVダウンロード';

    /** @var array<string, mixed> */
    public ?array $data = [];

    /**
     * ページ初期化時にフォームをマウント
     */
    public function mount(): void
    {
        $this->form->fill();
    }

    /**
     * 期間指定フォーム定義
     */
    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                DatePicker::make('from')
                    ->label('開始日')
                    ->native(false)
                    ->displayFormat('Y/m/d'),
                DatePicker::make('to')
                    ->label('終了日')
                    ->native(false)
                    ->displayFormat('Y/m/d'),
            ])
            ->statePath('data');
    }

    /**
     * ページのコンテンツ（フォームを埋め込み）
     */
    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')]),
            ]);
    }

    /**
     * ヘッダーアクション（ダウンロードボタン）
     *
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('download')
                ->label('CSVダウンロード')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->action(function (): mixed {
                    $from = $this->data['from'] ?? null;
                    $to = $this->data['to'] ?? null;

                    return JobApplicationCsvExport::download(
                        from: $from ? Carbon::parse($from) : null,
                        to: $to ? Carbon::parse($to) : null,
                    );
                }),
        ];
    }
}
