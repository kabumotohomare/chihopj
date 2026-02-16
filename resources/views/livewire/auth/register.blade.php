<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <x-auth-header title="アカウントをつくる" description="平泉町とつながるために、あなたの情報を入力してください" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf
            <!-- Role Selection -->
            <flux:field>
                <flux:label>平泉町とのかかわりかた</flux:label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            type="radio"
                            name="role"
                            value="worker"
                            {{ old('role') === 'worker' ? 'checked' : '' }}
                            required
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                        />
                        <span>ひらいず民(平泉に参加したい人)</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            type="radio"
                            name="role"
                            value="company"
                            {{ old('role') === 'company' ? 'checked' : '' }}
                            required
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                        />
                        <span>ホスト(平泉で募集する人)</span>
                    </label>
                </div>
                @error('role')
                    <flux:error>{{ $message }}</flux:error>
                @enderror
            </flux:field>

            <!-- Name -->
            <flux:input
                name="name"
                label="お名前"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                placeholder="山田 太郎"
            />

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
                <flux:button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                    アカウントをつくる
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>すでにアカウントをお持ちですか?</span>
            <flux:link :href="route('login')" wire:navigate>ログインする</flux:link>
        </div>
    </div>
</x-layouts.auth>
