<?php

declare(strict_types=1);

use App\Models\Location;
use App\Models\WorkerProfile;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('components.layouts.app')]
class extends Component
{
    use WithFileUploads;

    // 基本情報
    #[Validate('required|string|max:50')]
    public string $handle_name = '';

    #[Validate('nullable|image|max:2048|mimes:jpeg,jpg,png,gif|dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000')]
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

    // テキストエリア
    #[Validate('nullable|string|max:200')]
    public string $experiences = '';

    #[Validate('nullable|string|max:200')]
    public string $want_to_do = '';

    #[Validate('nullable|string|max:200')]
    public string $good_contribution = '';

    // 出身地
    public ?string $birth_prefecture = null;

    #[Validate('required|exists:locations,id')]
    public ?int $birth_location_id = null;

    // 現在のお住まい1
    public ?string $current_1_prefecture = null;

    #[Validate('required|exists:locations,id')]
    public ?int $current_location_1_id = null;

    // 現在のお住まい2
    public ?string $current_2_prefecture = null;

    #[Validate('nullable|exists:locations,id')]
    public ?int $current_location_2_id = null;

    // 移住に関心のある地域1
    public ?string $favorite_1_prefecture = null;

    #[Validate('nullable|exists:locations,id')]
    public ?int $favorite_location_1_id = null;

    // 移住に関心のある地域2
    public ?string $favorite_2_prefecture = null;

    #[Validate('nullable|exists:locations,id')]
    public ?int $favorite_location_2_id = null;

    // 移住に関心のある地域3
    public ?string $favorite_3_prefecture = null;

    #[Validate('nullable|exists:locations,id')]
    public ?int $favorite_location_3_id = null;

    // 興味のあるお手伝い
    public array $available_action = [];

    // 都道府県リスト
    public $prefectures = [];

    // 市区町村リスト
    public $birth_cities = [];

    public $current_1_cities = [];

    public $current_2_cities = [];

    public $favorite_1_cities = [];

    public $favorite_2_cities = [];

    public $favorite_3_cities = [];

    // 年月日リスト
    public $years = [];

    public $months = [];

    public $days = [];

    public function mount(): void
    {
        // 既にプロフィールが登録されている場合は編集画面にリダイレクト
        if (auth()->user()->workerProfile) {
            $this->redirect(route('worker.profile'), navigate: true);

            return;
        }

        // 都道府県リストを取得
        $this->prefectures = Location::whereNull('city')
            ->orderBy('code')
            ->get();

        // 年リストを生成（現在年 - 18歳 から 現在年 - 80歳 まで）
        $currentYear = (int) date('Y');
        $this->years = range($currentYear - 18, $currentYear - 80);

        // 月リストを生成
        $this->months = range(1, 12);

        // 日リストを初期化（1-31日）
        $this->days = range(1, 31);
    }

    public function updatedBirthPrefecture($value): void
    {
        if (empty($value)) {
            $this->birth_cities = [];
            $this->birth_location_id = null;
        } else {
            $this->birth_cities = $this->getCities($value);
            $this->birth_location_id = null;
        }
    }

    public function updatedCurrent1Prefecture($value): void
    {
        if (empty($value)) {
            $this->current_1_cities = [];
            $this->current_location_1_id = null;
        } else {
            $this->current_1_cities = $this->getCities($value);
            $this->current_location_1_id = null;
        }
    }

    public function updatedCurrent2Prefecture($value): void
    {
        if (empty($value)) {
            $this->current_2_cities = [];
            $this->current_location_2_id = null;
        } else {
            $this->current_2_cities = $this->getCities($value);
            $this->current_location_2_id = null;
        }
    }

    public function updatedFavorite1Prefecture($value): void
    {
        if (empty($value)) {
            $this->favorite_1_cities = [];
            $this->favorite_location_1_id = null;
        } else {
            $this->favorite_1_cities = $this->getCities($value);
            $this->favorite_location_1_id = null;
        }
    }

    public function updatedFavorite2Prefecture($value): void
    {
        if (empty($value)) {
            $this->favorite_2_cities = [];
            $this->favorite_location_2_id = null;
        } else {
            $this->favorite_2_cities = $this->getCities($value);
            $this->favorite_location_2_id = null;
        }
    }

    public function updatedFavorite3Prefecture($value): void
    {
        if (empty($value)) {
            $this->favorite_3_cities = [];
            $this->favorite_location_3_id = null;
        } else {
            $this->favorite_3_cities = $this->getCities($value);
            $this->favorite_location_3_id = null;
        }
    }

    public function updatedBirthYear(): void
    {
        $this->updateDays();
    }

    public function updatedBirthMonth(): void
    {
        $this->updateDays();
    }

    private function getCities(?string $prefecture)
    {
        if (! $prefecture) {
            return [];
        }

        $cities = Location::where('prefecture', $prefecture)
            ->whereNotNull('city')
            ->orderBy('code')
            ->get();

        // デバッグログ
        \Log::info('getCities called', [
            'prefecture' => $prefecture,
            'cities_count' => $cities->count(),
            'first_city' => $cities->first()?->city,
        ]);

        return $cities;
    }

    private function updateDays(): void
    {
        if (! $this->birth_year || ! $this->birth_month) {
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
        $birthdate = sprintf(
            '%04d-%02d-%02d',
            $this->birth_year,
            $this->birth_month,
            $this->birth_day
        );

        // ワーカープロフィールを作成
        WorkerProfile::create([
            'user_id' => auth()->id(),
            'handle_name' => $this->handle_name,
            'icon' => $this->icon ? $this->icon->store('icons', 'public') : null,
            'gender' => $this->gender,
            'birthdate' => $birthdate,
            'experiences' => $this->experiences ?: null,
            'want_to_do' => $this->want_to_do ?: null,
            'good_contribution' => $this->good_contribution ?: null,
            'birth_location_id' => $this->birth_location_id,
            'current_location_1_id' => $this->current_location_1_id,
            'current_location_2_id' => $this->current_location_2_id ?: null,
            'favorite_location_1_id' => $this->favorite_location_1_id ?: null,
            'favorite_location_2_id' => $this->favorite_location_2_id ?: null,
            'favorite_location_3_id' => $this->favorite_location_3_id ?: null,
            'available_action' => ! empty($this->available_action) ? $this->available_action : null,
        ]);

        session()->flash('status', 'ワーカープロフィールを登録しました。');

        $this->redirect(route('worker.profile'), navigate: true);
    }
}; ?>

<div class="max-w-4xl mx-auto py-8 px-4">
    <div class="flex flex-col gap-6">
        <div>
            <h1 class="text-2xl font-bold">ワーカープロフィール登録</h1>
            <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-2">
                プロボノワーカーとしてのプロフィールを登録してください
            </p>
        </div>

        <form wire:submit="register" class="flex flex-col gap-6">
            <!-- ハンドルネーム -->
            <flux:field>
                <flux:label>ハンドルネーム <span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="handle_name" placeholder="例：山田太郎" />
                <flux:error name="handle_name" />
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

            <!-- 性別 -->
            <flux:field>
                <flux:label>性別 <span class="text-red-500">*</span></flux:label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            type="radio"
                            name="gender"
                            value="male"
                            wire:model="gender"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                        />
                        <span>男性</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            type="radio"
                            name="gender"
                            value="female"
                            wire:model="gender"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                        />
                        <span>女性</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            type="radio"
                            name="gender"
                            value="other"
                            wire:model="gender"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                        />
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
                        @foreach($years as $year)
                            <option value="{{ $year }}">{{ $year }}年</option>
                        @endforeach
                    </flux:select>
                    <flux:select wire:model.live="birth_month" placeholder="月">
                        @foreach($months as $month)
                            <option value="{{ $month }}">{{ $month }}月</option>
                        @endforeach
                    </flux:select>
                    <flux:select wire:model="birth_day" placeholder="日">
                        @foreach($days as $day)
                            <option value="{{ $day }}">{{ $day }}日</option>
                        @endforeach
                    </flux:select>
                </div>
                <flux:error name="birth_year" />
                <flux:error name="birth_month" />
                <flux:error name="birth_day" />
            </flux:field>

            <!-- これまでの経験 -->
            <flux:field>
                <flux:label>これまでの経験 <span class="text-zinc-500">(任意)</span></flux:label>
                <flux:textarea wire:model="experiences" rows="3" placeholder="これまでの経験を入力してください（200文字以内）" />
                <flux:error name="experiences" />
            </flux:field>

            <!-- これからやりたいこと -->
            <flux:field>
                <flux:label>これからやりたいこと <span class="text-zinc-500">(任意)</span></flux:label>
                <flux:textarea wire:model="want_to_do" rows="3" placeholder="これからやりたいことを入力してください（200文字以内）" />
                <flux:error name="want_to_do" />
            </flux:field>

            <!-- 得意なことや貢献できること -->
            <flux:field>
                <flux:label>得意なことや貢献できること <span class="text-zinc-500">(任意)</span></flux:label>
                <flux:textarea wire:model="good_contribution" rows="3" placeholder="得意なことや貢献できることを入力してください（200文字以内）" />
                <flux:error name="good_contribution" />
            </flux:field>

            <!-- 出身地 -->
            <flux:field>
                <flux:label>出身地 <span class="text-red-500">*</span></flux:label>
                <div class="flex gap-2">
                    <flux:select wire:model.live="birth_prefecture" placeholder="都道府県を選択">
                        <option value="">都道府県を選択</option>
                        @foreach($prefectures as $prefecture)
                            <option value="{{ $prefecture->prefecture }}">{{ $prefecture->prefecture }}</option>
                        @endforeach
                    </flux:select>
                    <flux:select wire:model="birth_location_id" placeholder="市区町村を選択" :disabled="empty($birth_cities)">
                        <option value="">市区町村を選択</option>
                        @if(!empty($birth_cities))
                            @foreach($birth_cities as $city)
                                <option value="{{ $city->id }}">{{ $city->city }}</option>
                            @endforeach
                        @endif
                    </flux:select>
                </div>
                <flux:error name="birth_location_id" />
            </flux:field>

            <!-- 現在のお住まい1 -->
            <flux:field>
                <flux:label>現在のお住まい1 <span class="text-red-500">*</span></flux:label>
                <div class="flex gap-2">
                    <flux:select wire:model.live="current_1_prefecture" placeholder="都道府県を選択">
                        <option value="">都道府県を選択</option>
                        @foreach($prefectures as $prefecture)
                            <option value="{{ $prefecture->prefecture }}">{{ $prefecture->prefecture }}</option>
                        @endforeach
                    </flux:select>
                    <flux:select wire:model="current_location_1_id" placeholder="市区町村を選択" :disabled="empty($current_1_cities)">
                        <option value="">市区町村を選択</option>
                        @if(!empty($current_1_cities))
                            @foreach($current_1_cities as $city)
                                <option value="{{ $city->id }}">{{ $city->city }}</option>
                            @endforeach
                        @endif
                    </flux:select>
                </div>
                <flux:error name="current_location_1_id" />
            </flux:field>

            <!-- 現在のお住まい2 -->
            <flux:field>
                <flux:label>現在のお住まい2 <span class="text-zinc-500">(任意)</span></flux:label>
                <div class="flex gap-2">
                    <flux:select wire:model.live="current_2_prefecture" placeholder="都道府県を選択">
                        <option value="">都道府県を選択</option>
                        @foreach($prefectures as $prefecture)
                            <option value="{{ $prefecture->prefecture }}">{{ $prefecture->prefecture }}</option>
                        @endforeach
                    </flux:select>
                    <flux:select wire:model="current_location_2_id" placeholder="市区町村を選択" :disabled="empty($current_2_cities)">
                        <option value="">市区町村を選択</option>
                        @if(!empty($current_2_cities))
                            @foreach($current_2_cities as $city)
                                <option value="{{ $city->id }}">{{ $city->city }}</option>
                            @endforeach
                        @endif
                    </flux:select>
                </div>
                <flux:error name="current_location_2_id" />
            </flux:field>

            <!-- 移住に関心のある地域1 -->
            <flux:field>
                <flux:label>移住に関心のある地域1 <span class="text-zinc-500">(任意)</span></flux:label>
                <div class="flex gap-2">
                    <flux:select wire:model.live="favorite_1_prefecture" placeholder="都道府県を選択">
                        <option value="">都道府県を選択</option>
                        @foreach($prefectures as $prefecture)
                            <option value="{{ $prefecture->prefecture }}">{{ $prefecture->prefecture }}</option>
                        @endforeach
                    </flux:select>
                    <flux:select wire:model="favorite_location_1_id" placeholder="市区町村を選択" :disabled="empty($favorite_1_cities)">
                        <option value="">市区町村を選択</option>
                        @if(!empty($favorite_1_cities))
                            @foreach($favorite_1_cities as $city)
                                <option value="{{ $city->id }}">{{ $city->city }}</option>
                            @endforeach
                        @endif
                    </flux:select>
                </div>
                <flux:error name="favorite_location_1_id" />
            </flux:field>

            <!-- 移住に関心のある地域2 -->
            <flux:field>
                <flux:label>移住に関心のある地域2 <span class="text-zinc-500">(任意)</span></flux:label>
                <div class="flex gap-2">
                    <flux:select wire:model.live="favorite_2_prefecture" placeholder="都道府県を選択">
                        <option value="">都道府県を選択</option>
                        @foreach($prefectures as $prefecture)
                            <option value="{{ $prefecture->prefecture }}">{{ $prefecture->prefecture }}</option>
                        @endforeach
                    </flux:select>
                    <flux:select wire:model="favorite_location_2_id" placeholder="市区町村を選択" :disabled="empty($favorite_2_cities)">
                        <option value="">市区町村を選択</option>
                        @if(!empty($favorite_2_cities))
                            @foreach($favorite_2_cities as $city)
                                <option value="{{ $city->id }}">{{ $city->city }}</option>
                            @endforeach
                        @endif
                    </flux:select>
                </div>
                <flux:error name="favorite_location_2_id" />
            </flux:field>

            <!-- 移住に関心のある地域3 -->
            <flux:field>
                <flux:label>移住に関心のある地域3 <span class="text-zinc-500">(任意)</span></flux:label>
                <div class="flex gap-2">
                    <flux:select wire:model.live="favorite_3_prefecture" placeholder="都道府県を選択">
                        <option value="">都道府県を選択</option>
                        @foreach($prefectures as $prefecture)
                            <option value="{{ $prefecture->prefecture }}">{{ $prefecture->prefecture }}</option>
                        @endforeach
                    </flux:select>
                    <flux:select wire:model="favorite_location_3_id" placeholder="市区町村を選択" :disabled="empty($favorite_3_cities)">
                        <option value="">市区町村を選択</option>
                        @if(!empty($favorite_3_cities))
                            @foreach($favorite_3_cities as $city)
                                <option value="{{ $city->id }}">{{ $city->city }}</option>
                            @endforeach
                        @endif
                    </flux:select>
                </div>
                <flux:error name="favorite_location_3_id" />
            </flux:field>

            <!-- 興味のあるお手伝い -->
            <flux:field>
                <flux:label>興味のあるお手伝い <span class="text-zinc-500">(任意)</span></flux:label>
                <div class="flex flex-col gap-2">
                    <flux:checkbox wire:model="available_action" value="mowing" label="草刈り" />
                    <flux:checkbox wire:model="available_action" value="snowplow" label="雪かき" />
                    <flux:checkbox wire:model="available_action" value="diy" label="DIY" />
                    <flux:checkbox wire:model="available_action" value="localcleaning" label="地域清掃" />
                    <flux:checkbox wire:model="available_action" value="volunteer" label="災害ボランティア" />
                </div>
                <flux:error name="available_action" />
            </flux:field>

            <!-- 送信ボタン -->
            <div class="flex justify-end">
                <flux:button type="submit" variant="primary">
                    登録する
                </flux:button>
            </div>
        </form>
    </div>
</div>
