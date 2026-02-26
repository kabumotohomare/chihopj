<?php

declare(strict_types=1);

use App\Models\Location;
use App\Models\WorkerProfile;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('components.layouts.auth')] class extends Component {
    use WithFileUploads;

    // お名前（本名）
    #[Validate('required|string|max:50')]
    public string $name = '';

    // 基本情報
    #[Validate('required|string|max:50')]
    public string $handle_name = '';

    #[Validate('nullable|image|max:2048|mimes:jpeg,jpg,png,gif')]
    public $icon;

    #[Validate('required|in:male,female,other')]
    public string $gender = '';

    // 生年月日
    #[Validate('required|integer|min:1900')]
    public string $birth_year = '';

    #[Validate('required|integer|min:1|max:12')]
    public string $birth_month = '';

    #[Validate('required|integer|min:1|max:31')]
    public string $birth_day = '';

    // ひとことメッセージ
    #[Validate('nullable|string|max:200')]
    public string $message = '';

    // 現在のお住まい1
    public ?string $current_1_prefecture = null;

    #[Validate('required|exists:locations,id')]
    public ?int $current_location_1_id = null;

    #[Validate('required|string|max:200')]
    public string $current_address = '';

    #[Validate('required|string|max:30')]
    public string $phone_number = '';

    // 現在のお住まい2
    public ?string $current_2_prefecture = null;

    #[Validate('nullable|exists:locations,id')]
    public ?int $current_location_2_id = null;

    // 同意チェックボックス
    #[Validate('accepted', message: 'ふるさと住民制度実施要綱に同意する必要があります。')]
    public bool $agree_to_terms = false;

    // 年月日リスト
    public $years = [];

    public $months = [];

    public $days = [];

    public function mount(): void
    {
        // 認証済みユーザーで既にプロフィールが登録されている場合は編集画面にリダイレクト
        if (auth()->check() && auth()->user()->workerProfile) {
            $this->redirect(route('worker.profile'), navigate: true);

            return;
        }

        // 年リストを生成（現在年 - 18歳 から 現在年 - 80歳 まで）
        $currentYear = (int) date('Y');
        $this->years = range($currentYear - 18, $currentYear - 80);

        // 月リストを生成
        $this->months = range(1, 12);

        // 日リストを初期化（1-31日）
        $this->days = range(1, 31);
    }

    public function render(): mixed
    {
        $prefectures = Location::whereNull('city')->orderBy('code')->get();

        return view('livewire.worker.register', [
            'prefectures' => $prefectures,
            'current_1_cities' => $this->current_1_prefecture ? Location::where('prefecture', $this->current_1_prefecture)->whereNotNull('city')->orderBy('code')->get() : collect(),
            'current_2_cities' => $this->current_2_prefecture ? Location::where('prefecture', $this->current_2_prefecture)->whereNotNull('city')->orderBy('code')->get() : collect(),
        ]);
    }

    public function updatedCurrent1Prefecture($value): void
    {
        $this->current_location_1_id = null;
    }

    public function updatedCurrent2Prefecture($value): void
    {
        $this->current_location_2_id = null;
    }

    public function updatedBirthYear(): void
    {
        $this->updateDays();
    }

    public function updatedBirthMonth(): void
    {
        $this->updateDays();
    }

    private function updateDays(): void
    {
        if (!$this->birth_year || !$this->birth_month) {
            $this->days = range(1, 31);

            return;
        }

        $year = (int) $this->birth_year;
        $month = (int) $this->birth_month;

        // 月の日数を取得
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $this->days = range(1, $daysInMonth);

        // 選択中の日が新しい日数を超える場合はリセット
        if ($this->birth_day && (int) $this->birth_day > $daysInMonth) {
            $this->birth_day = '';
        }
    }

    public function register(): void
    {
        $this->validate();

        // 生年月日を結合
        $birthdate = sprintf('%04d-%02d-%02d', $this->birth_year, $this->birth_month, $this->birth_day);

        // usersテーブルのnameを更新
        auth()
            ->user()
            ->update([
                'name' => $this->name,
            ]);

        // ひらいず民プロフィールを作成
        WorkerProfile::create([
            'user_id' => auth()->id(),
            'handle_name' => $this->handle_name,
            'icon' => $this->icon ? $this->icon->store('icons', 'public') : null,
            'gender' => $this->gender,
            'birthdate' => $birthdate,
            'message' => $this->message ?: null,
            'birth_location_id' => null,
            'current_location_1_id' => $this->current_location_1_id,
            'current_address' => $this->current_address,
            'phone_number' => $this->phone_number,
            'current_location_2_id' => $this->current_location_2_id ?: null,
        ]);

        session()->flash('status', 'ひらいず民プロフィールを登録しました。');

        $this->redirect(route('worker.profile'), navigate: true);
    }
}; ?>

<div class="max-w-4xl mx-auto py-8 px-4">
    <div class="flex flex-col gap-6">
        <div>
            <h1 class="text-2xl font-bold">ひらいず民プロフィール登録</h1>
            <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-2">
                ひらいず民としてのプロフィールを登録してください。個人情報は公開されません。運営からのご本人様確認のため、利用させていただきます。
            </p>
        </div>

        <form wire:submit="register" class="flex flex-col gap-6">
            <!-- お名前（本名） -->
            <flux:field>
                <flux:label>お名前（本名） <span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="name" placeholder="例：山田 太郎" />
                <flux:description>
                    本名を入力してください。公開されません。
                </flux:description>
                <flux:error name="name" />
            </flux:field>

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
                            <img src="{{ $icon->temporaryUrl() }}" alt="プレビュー"
                                class="size-24 rounded-full object-cover border-2 border-zinc-300 dark:border-zinc-600">
                        </div>
                    @endif
                @endif

                <input type="file" wire:model="icon" accept="image/jpeg,image/jpg,image/png,image/gif"
                    class="block w-full text-sm text-zinc-900 border border-zinc-300 rounded-lg cursor-pointer bg-zinc-50 dark:text-zinc-400 focus:outline-none dark:bg-zinc-700 dark:border-zinc-600 dark:placeholder-zinc-400">

                <flux:description>
                    推奨: 正方形の画像、最大2MB（JPEG、PNG、GIF形式）
                </flux:description>

                <flux:error name="icon" />
            </flux:field>

            <!-- ニックネーム -->
            <flux:field>
                <flux:label>ニックネーム <span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="handle_name" placeholder="例：べんけい君" />
                <flux:error name="handle_name" />
            </flux:field>

            <!-- 性別 -->
            <flux:field>
                <flux:label>性別 <span class="text-red-500">*</span></flux:label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="gender" value="male" wire:model="gender"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300" />
                        <span>男性</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="gender" value="female" wire:model="gender"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300" />
                        <span>女性</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="gender" value="other" wire:model="gender"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300" />
                        <span>その他</span>
                    </label>
                </div>
                <flux:error name="gender" />
            </flux:field>

            <!-- 生年月日 -->
            <flux:field>
                <flux:label>生年月日 <span class="text-red-500">*</span></flux:label>
                <div class="flex gap-2">
                    <flux:select wire:model.live="birth_year" placeholder="年">
                        @foreach ($years as $year)
                            <option value="{{ $year }}">{{ $year }}年</option>
                        @endforeach
                    </flux:select>
                    <flux:select wire:model.live="birth_month" placeholder="月">
                        @foreach ($months as $month)
                            <option value="{{ $month }}">{{ $month }}月</option>
                        @endforeach
                    </flux:select>
                    <flux:select wire:model="birth_day" placeholder="日">
                        @foreach ($days as $day)
                            <option value="{{ $day }}">{{ $day }}日</option>
                        @endforeach
                    </flux:select>
                </div>
                <flux:error name="birth_year" />
                <flux:error name="birth_month" />
                <flux:error name="birth_day" />
            </flux:field>

            <!-- 現在のお住まい1 -->
            <flux:field>
                <flux:label>現在のお住まい1 <span class="text-red-500">*</span></flux:label>

                <div class="flex gap-2">
                    <select wire:model.live="current_1_prefecture"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-800">
                        <option value="">都道府県を選択</option>
                        @foreach ($prefectures as $prefecture)
                            <option value="{{ $prefecture->prefecture }}">{{ $prefecture->prefecture }}</option>
                        @endforeach
                    </select>
                    <select wire:model="current_location_1_id"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-800"
                        @disabled($current_1_cities->isEmpty())>
                        <option value="">市区町村を選択</option>
                        @foreach ($current_1_cities as $city)
                            <option value="{{ $city->id }}">{{ $city->city }}</option>
                        @endforeach
                    </select>
                </div>
                <flux:error name="current_location_1_id" />
            </flux:field>

            <!-- 町名番地建物名 -->
            <flux:field>
                <flux:label>町名番地建物名 <span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="current_address" placeholder="例：中央1-2-3 ○○マンション101号室" />
                <flux:description>
                    現在のお住まい1の町名・番地・建物名を入力してください
                </flux:description>
                <flux:error name="current_address" />
            </flux:field>

            <!-- 電話番号 -->
            <flux:field>
                <flux:label>電話番号 <span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="phone_number" type="tel" placeholder="例：090-1234-5678" />
                <flux:description>
                    ハイフン付きで入力してください
                </flux:description>
                <flux:error name="phone_number" />
            </flux:field>

            <!-- 現在のお住まい2 -->
            <flux:field>
                <flux:label>現在のお住まい2 <span class="text-zinc-500">(任意)</span></flux:label>
                <div class="flex gap-2">
                    <select wire:model.live="current_2_prefecture"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-800">
                        <option value="">都道府県を選択</option>
                        @foreach ($prefectures as $prefecture)
                            <option value="{{ $prefecture->prefecture }}">{{ $prefecture->prefecture }}</option>
                        @endforeach
                    </select>
                    <select wire:model="current_location_2_id"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-800"
                        @disabled($current_2_cities->isEmpty())>
                        <option value="">市区町村を選択</option>
                        @foreach ($current_2_cities as $city)
                            <option value="{{ $city->id }}">{{ $city->city }}</option>
                        @endforeach
                    </select>
                </div>
                <flux:error name="current_location_2_id" />
            </flux:field>

            <!-- ふるさと住民制度への同意 -->
            <flux:field>
                <div class="space-y-2">
                    <div class="flex items-start gap-3">
                        <input type="checkbox" wire:model="agree_to_terms" id="agree_to_terms"
                            class="mt-1 h-4 w-4 rounded border-gray-300 text-[#FF6B35] focus:ring-[#FF6B35]">
                        <label for="agree_to_terms" class="text-sm text-[#3E3A35] cursor-pointer">
                            平泉町ふるさと住民登録に同意する <span class="text-red-500">*</span>
                        </label>
                    </div>
                    <div class="ml-7 text-sm text-[#6B6760]">
                        平泉町ふるさと住民制度実施要綱は
                        <a href="https://hi-hp-production.s3.ap-northeast-1.amazonaws.com/wp-content/uploads/2025/03/20133954/20240417-140037.pdf"
                            target="_blank" class="text-[#4CAF50] hover:text-[#45A049] underline">
                            こちら
                        </a>
                    </div>
                </div>
                <flux:error name="agree_to_terms" />
            </flux:field>

            <!-- 送信ボタン -->
            <div class="flex justify-end">
                <flux:button type="submit" variant="primary">
                    登録
                </flux:button>
            </div>
        </form>
    </div>
</div>
