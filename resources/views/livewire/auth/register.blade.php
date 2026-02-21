<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <!-- ヘッダー -->
        <div class="text-center">
            <h1 class="text-2xl font-bold text-[#3E3A35] mb-2">アカウントをつくる</h1>
            <p class="text-sm text-[#6B6760]">メールアドレスを入力してください</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf
            <!-- Role Selection -->
            <flux:field>
                <flux:label class="text-[#3E3A35] font-medium">平泉町とのかかわりかた</flux:label>
                <div class="flex flex-col sm:flex-row gap-3">
                    <label class="flex items-center gap-2 cursor-pointer bg-white p-3 rounded-lg border-2 border-gray-200 hover:border-[#FF6B35] transition-colors">
                        <input
                            type="radio"
                            name="role"
                            value="worker"
                            {{ old('role') === 'worker' ? 'checked' : '' }}
                            required
                            class="h-4 w-4 text-[#FF6B35] focus:ring-[#FF6B35] border-gray-300"
                        />
                        <span class="text-[#3E3A35]">ひらいず民(平泉に参加したい人)</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer bg-white p-3 rounded-lg border-2 border-gray-200 hover:border-[#4CAF50] transition-colors">
                        <input
                            type="radio"
                            name="role"
                            value="company"
                            {{ old('role') === 'company' ? 'checked' : '' }}
                            required
                            class="h-4 w-4 text-[#4CAF50] focus:ring-[#4CAF50] border-gray-300"
                        />
                        <span class="text-[#3E3A35]">ホスト(平泉で募集する人)</span>
                    </label>
                </div>
                @error('role')
                    <flux:error>{{ $message }}</flux:error>
                @enderror
            </flux:field>

            <!-- Email Address -->
            <flux:input
                name="email"
                label="メールアドレス"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Password -->
            <flux:input
                name="password"
                label="パスワード"
                type="password"
                required
                autocomplete="new-password"
                placeholder="8文字以上で入力してください"
                viewable
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                label="パスワード(確認)"
                type="password"
                required
                autocomplete="new-password"
                placeholder="もう一度入力してください"
                viewable
            />

            <div class="flex items-center justify-end">
                <button type="submit" class="w-full bg-[#FF6B35] hover:bg-[#E55A28] text-white px-6 py-3 rounded-full font-bold transition-all transform hover:scale-105 shadow-lg hover:shadow-xl" data-test="register-user-button">
                    アカウントをつくる
                </button>
            </div>
        </form>

        <div class="text-sm text-center text-[#6B6760]">
            <span>すでにアカウントをお持ちですか?</span>
            <a href="{{ route('login') }}" class="text-[#4CAF50] hover:text-[#45A049] font-medium ml-1" wire:navigate>ログインする</a>
        </div>
    </div>
</x-layouts.auth>
