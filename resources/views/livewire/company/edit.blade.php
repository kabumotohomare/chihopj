<?php

declare(strict_types=1);

use App\Models\CompanyProfile;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('components.layouts.app')]
class extends Component
{
    use WithFileUploads;

    #[Validate('nullable|image|max:2048|mimes:jpeg,jpg,png,gif')]
    public $icon;

    // 企業プロフィール情報
    #[Validate('required|string|max:200')]
    public string $address = '';

    #[Validate('required|string|max:50')]
    public string $representative = '';

    #[Validate('required|string|max:30')]
    public string $phone_number = '';

    public $profile = null;

    public $existingIcon = null;

    public function mount(): void
    {
        $this->profile = CompanyProfile::query()
            ->where('user_id', auth()->id())
            ->firstOrFail();
        
        // 既存データを初期値として設定
        $this->address = $this->profile->address;
        $this->representative = $this->profile->representative;
        $this->phone_number = $this->profile->phone_number;
        $this->existingIcon = $this->profile->icon;
    }

    public function update(): void
    {
        $this->validate();
        
        // アイコン画像の処理
        $iconPath = $this->existingIcon;
        if ($this->icon) {
            // 既存のアイコンを削除
            if ($this->existingIcon) {
                Storage::disk('public')->delete($this->existingIcon);
            }
            // 新しいアイコンを保存
            $iconPath = $this->icon->store('icons', 'public');
        }
        
        // プロフィール更新
        $this->profile->update([
            'icon' => $iconPath,
            'address' => $this->address,
            'representative' => $this->representative,
            'phone_number' => $this->phone_number,
        ]);
        
        session()->flash('status', '企業プロフィールを更新しました。');
        
        $this->redirect(route('company.profile'), navigate: true);
    }
}; ?>

<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-6">
        <div>
            <h1 class="text-2xl font-bold">企業プロフィール編集</h1>
            <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-2">
                企業プロフィール情報を編集してください
            </p>
        </div>

        <form wire:submit="update" class="flex flex-col gap-6">
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
                @elseif ($existingIcon)
                    <div class="mb-4">
                        <img src="{{ Storage::url($existingIcon) }}"
                            alt="現在のアイコン"
                            class="size-24 rounded-full object-cover border-2 border-zinc-300 dark:border-zinc-600">
                    </div>
                @endif

                <input type="file"
                    wire:model="icon"
                    accept="image/jpeg,image/jpg,image/png,image/gif"
                    class="block w-full text-sm text-zinc-900 border border-zinc-300 rounded-lg cursor-pointer bg-zinc-50 dark:text-zinc-400 focus:outline-none dark:bg-zinc-700 dark:border-zinc-600 dark:placeholder-zinc-400">

                <flux:description>
                    推奨: 正方形の画像、最大2MB（JPEG、PNG、GIF形式）
                </flux:description>

                <flux:error name="icon" />
            </flux:field>

            <!-- 所在地（固定表示） -->
            <flux:field>
                <flux:label>所在地</flux:label>
                <div class="rounded-lg border border-zinc-300 bg-zinc-50 px-4 py-3 text-sm text-zinc-700 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                    岩手県西磐井郡平泉町
                </div>
                <flux:description>
                    このサービスは平泉町内の事業者専用です
                </flux:description>
            </flux:field>

            <!-- 所在地住所 -->
            <flux:field>
                <flux:label>所在地住所 <span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="address" placeholder="例：平泉字泉屋1-1" />
                <flux:description>
                    町名以降の住所を入力してください（200文字以内）
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
                <flux:button href="{{ route('company.profile') }}" variant="ghost" class="order-2 sm:order-1" wire:navigate>
                    キャンセル
                </flux:button>
                <flux:button type="submit" variant="primary" class="order-1 sm:order-2">
                    更新する
                </flux:button>
            </div>
        </form>
    </div>
</div>
