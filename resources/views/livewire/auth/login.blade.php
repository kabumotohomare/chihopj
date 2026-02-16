<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <!-- ヘッダー -->
        <div class="text-center">
            <h1 class="text-2xl font-bold text-[#3E3A35] mb-2">ただいま</h1>
            <p class="text-sm text-[#6B6760]">メールアドレスとパスワードを入力してください</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                label="メールアドレス"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Password -->
            <div class="relative">
                <flux:input
                    name="password"
                    label="パスワード"
                    type="password"
                    required
                    autocomplete="current-password"
                    placeholder="パスワードを入力"
                    viewable
                />

                @if (Route::has('password.request'))
                    <flux:link class="absolute top-0 text-sm end-0 text-[#4CAF50] hover:text-[#45A049]" :href="route('password.request')" wire:navigate>
                        パスワードをお忘れですか？
                    </flux:link>
                @endif
            </div>

            <!-- Remember Me -->
            <flux:checkbox name="remember" label="ログイン状態を保持する" :checked="old('remember')" />

            <div class="flex items-center justify-end">
                <button type="submit" class="w-full bg-[#FF6B35] hover:bg-[#E55A28] text-white px-6 py-3 rounded-full font-bold transition-all transform hover:scale-105 shadow-lg hover:shadow-xl" data-test="login-button">
                    ログイン
                </button>
            </div>
        </form>

        @if (Route::has('register'))
            <div class="text-sm text-center text-[#6B6760]">
                <span>アカウントをお持ちでないですか？</span>
                <a href="{{ route('register') }}" class="text-[#4CAF50] hover:text-[#45A049] font-medium ml-1" wire:navigate>新規登録</a>
            </div>
        @endif
    </div>
</x-layouts.auth>
