<?php

declare(strict_types=1);

use App\Models\CompanyProfile;
use App\Models\Location;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('components.layouts.app')]
class extends Component
{
    use WithFileUploads;

    #[Validate('nullable|image|max:2048|mimes:jpeg,jpg,png,gif|dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000')]
    public $icon;

    // 企業プロフィール情報
    public ?string $prefecture = null;

    #[Validate('required|exists:locations,id')]
    public ?int $location_id = null;

    #[Validate('required|string|max:200')]
    public string $address = '';

    #[Validate('required|string|max:50')]
    public string $representative = '';

    #[Validate('required|string|max:30')]
    public string $phone_number = '';

    // 都道府県・市区町村リスト
    public $prefectures = [];

    public $cities = [];

    public function mount(): void
    {
        // 認証済みユーザーで既にプロフィールが登録されている場合は詳細画面にリダイレクト
        if (auth()->check() && auth()->user()->companyProfile) {
            $this->redirect(route('company.profile'), navigate: true);

            return;
        }

        // 都道府県リストを取得
        $this->prefectures = Location::whereNull('city')
            ->orderBy('code')
            ->get();
    }

    public function updatedPrefecture($value): void
    {
        if (empty($value)) {
            $this->cities = [];
            $this->location_id = null;
        } else {
            $this->cities = $this->getCities($value);
            $this->location_id = null;
        }
    }

    private function getCities(?string $prefecture)
    {
        if (! $prefecture) {
            return [];
        }

        return Location::where('prefecture', $prefecture)
            ->whereNotNull('city')
            ->orderBy('code')
            ->get();
    }

    public function register(): void
    {
        $this->validate();

        // 企業プロフィール作成
        CompanyProfile::create([
            'user_id' => auth()->id(),
            'icon' => $this->icon ? $this->icon->store('icons', 'public') : null,
            'location_id' => $this->location_id,
            'address' => $this->address,
            'representative' => $this->representative,
            'phone_number' => $this->phone_number,
        ]);

        session()->flash('status', '企業プロフィールを登録しました。');

        $this->redirect(route('company.profile'), navigate: true);
    }
}; ?>

<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-6">
        <div>
            <h1 class="text-2xl font-bold">企業プロフィール登録</h1>
            <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-2">
                事業者としてのプロフィールを登録してください
            </p>
        </div>

        <form wire:submit="register" class="flex flex-col gap-6">
            <!-- アイコン画像 -->
            <flux:field>
                <flux:label>アイコン画像 <span class="text-zinc-500">(任意)</span></flux:label>

                @if ($icon && is_object($icon) && method_exists($icon, 'getMimeType'))
                    @php
                        $mimeType = $icon->getMimeType();
                        $isImage = str_starts_with($mimeType, 'image/');
                    @endphp
                    @if ($isImage)
                        <div class="mb-4">
                            <img src="{{ $icon->temporaryUrl() }}"
                                alt="プレビュー"
                                class="size-24 rounded-full object-cover border-2 border-zinc-300 dark:border-zinc-600">
                        </div>
                    @endif
                @endif

                <input type="file"
                    wire:model="icon"
                    accept="image/jpeg,image/jpg,image/png,image/gif"
                    class="block w-full text-sm text-zinc-900 border border-zinc-300 rounded-lg cursor-pointer bg-zinc-50 dark:text-zinc-400 focus:outline-none dark:bg-zinc-700 dark:border-zinc-600 dark:placeholder-zinc-400">

                <flux:description>
                    推奨: 正方形の画像、最小100x100px、最大2MB（JPEG、PNG、GIF形式）
                </flux:description>

                <flux:error name="icon" />
            </flux:field>

            <!-- 所在地 -->
            <div class="flex flex-col gap-2">
                <label class="text-sm font-medium">
                    所在地 <span class="text-red-500">*</span>
                </label>
                <div class="flex flex-col sm:flex-row gap-2">
                    <div class="flex-1">
                        <select wire:model.live="prefecture" 
                                class="w-full px-3 py-2 rounded-lg border border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white focus:ring-2 focus:ring-blue-500">
                            <option value="">都道府県を選択</option>
                            @foreach($prefectures as $pref)
                                <option value="{{ $pref->prefecture }}">{{ $pref->prefecture }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1">
                        <select wire:model="location_id" 
                                @if(empty($cities)) disabled @endif
                                class="w-full px-3 py-2 rounded-lg border border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            <option value="">市区町村を選択</option>
                            @if(!empty($cities))
                                @foreach($cities as $city)
                                    <option value="{{ $city->id }}">{{ $city->city }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                @error('location_id')
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- 所在地住所 -->
            <flux:field>
                <flux:label>所在地住所 <span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="address" placeholder="例：◯◯町1-2-3 ◯◯ビル4F" />
                <flux:description>
                    市区町村以降の住所を入力してください（200文字以内）
                </flux:description>
                <flux:error name="address" />
            </flux:field>

            <!-- 担当者名 -->
            <flux:field>
                <flux:label>担当者名 <span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="representative" placeholder="例：山田太郎" />
                <flux:error name="representative" />
            </flux:field>

            <!-- 電話番号 -->
            <flux:field>
                <flux:label>電話番号 <span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="phone_number" placeholder="例：03-1234-5678" />
                <flux:description>
                    担当者の電話番号を入力してください
                </flux:description>
                <flux:error name="phone_number" />
            </flux:field>

            <!-- 送信ボタン -->
            <div class="flex flex-col sm:flex-row gap-4 justify-end">
                <flux:button href="{{ route('welcome') }}" variant="ghost" class="order-2 sm:order-1">
                    キャンセル
                </flux:button>
                <flux:button type="submit" variant="primary" class="order-1 sm:order-2">
                    登録する
                </flux:button>
            </div>
        </form>
    </div>
</div>
