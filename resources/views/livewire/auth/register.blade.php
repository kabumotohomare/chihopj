<x-layouts.auth title="アカウント作成">
    @volt('auth.register')
        <?php
        
        use function Livewire\Volt\{state};
        
        // ステップとフォームデータの状態管理
        state([
            'step' => 1,
            'role' => null,
            'email' => '',
            'password' => '',
            'password_confirmation' => '',
        ]);
        
        // ステップ1: ロール選択後に次へ
        $selectRole = function (string $selectedRole): void {
            $this->role = $selectedRole;
            $this->step = 2;
        };
        
        // 前のステップに戻る
        $goBack = function (): void {
            $this->step = 1;
        };
        
        // ステップ2: アカウント登録
        $register = function (): void {
            // バリデーション
            $this->validate(
                [
                    'role' => ['required', 'in:worker,company'],
                    'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                    'password' => ['required', 'string', 'min:8', 'confirmed'],
                ],
                [
                    'role.required' => 'ロールを選択してください。',
                    'role.in' => '有効なロールを選択してください。',
                    'email.required' => 'メールアドレスを入力してください。',
                    'email.email' => '有効なメールアドレスを入力してください。',
                    'email.unique' => 'このメールアドレスは既に使用されています。',
                    'password.required' => 'パスワードを入力してください。',
                    'password.min' => 'パスワードは8文字以上で入力してください。',
                    'password.confirmed' => 'パスワードが一致しません。',
                ],
            );
        
            // ユーザー登録
            $user = \App\Models\User::create([
                'name' => 'ゲスト',
                'email' => $this->email,
                'password' => \Illuminate\Support\Facades\Hash::make($this->password),
                'role' => $this->role,
            ]);
        
            // ログイン
            auth()->login($user);
        
            // ロールに応じてリダイレクト
            if ($this->role === 'worker') {
                $this->redirect(route('worker.register'), navigate: true);
            } else {
                $this->redirect(route('company.register'), navigate: true);
            }
        };
        
        ?>

        <div class="flex flex-col gap-6">
            <!-- プログレスバー -->
            <div class="flex items-center justify-center gap-2 mb-4">
                <div class="flex items-center">
                    <div
                        class="flex items-center justify-center w-8 h-8 rounded-full {{ $step >= 1 ? 'bg-[#FF6B35] text-white' : 'bg-gray-200 text-gray-400' }} font-bold text-sm">
                        1
                    </div>
                    <span
                        class="ml-2 text-sm {{ $step >= 1 ? 'text-[#3E3A35] font-medium' : 'text-gray-400' }}">かかわりかた</span>
                </div>
                <div class="w-12 h-1 {{ $step >= 2 ? 'bg-[#FF6B35]' : 'bg-gray-200' }} rounded"></div>
                <div class="flex items-center">
                    <div
                        class="flex items-center justify-center w-8 h-8 rounded-full {{ $step >= 2 ? 'bg-[#FF6B35] text-white' : 'bg-gray-200 text-gray-400' }} font-bold text-sm">
                        2
                    </div>
                    <span
                        class="ml-2 text-sm {{ $step >= 2 ? 'text-[#3E3A35] font-medium' : 'text-gray-400' }}">ログイン情報登録</span>
                </div>
            </div>

            <!-- ステップ1: ロール選択 -->
            @if ($step === 1)
                <div class="text-center">
                    <h1 class="text-2xl font-bold text-[#3E3A35] mb-2">平泉町とのかかわりかた</h1>
                    <p class="text-sm text-[#6B6760]">あなたはどちらですか？</p>
                </div>

                <div class="flex flex-col gap-4">
                    <!-- ひらいず民ボタン -->
                    <button type="button" wire:click="selectRole('worker')"
                        class="group relative overflow-hidden bg-white p-6 rounded-2xl border-3 border-[#FF6B35] hover:bg-[#FFF8E7] transition-all transform hover:scale-105 shadow-lg hover:shadow-xl">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-[#FF6B35] rounded-full flex items-center justify-center">
                                <i class="fas fa-users text-white text-xl"></i>
                            </div>
                            <div class="flex-1 text-left">
                                <h3 class="text-xl font-bold text-[#FF6B35] mb-2">ひらいず民</h3>
                                <p class="text-sm text-[#6B6760] mb-2"><strong>どこに住んでいても登録できます</strong></p>
                                <p class="text-xs text-[#6B6760]">気になる企画に参加して、平泉とつながります。登録すると平泉町の「ふるさと住民票®」特典も受けられます。</p>
                            </div>
                            <i
                                class="fas fa-chevron-right text-[#FF6B35] text-xl group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </button>

                    <!-- ホストボタン -->
                    <button type="button" wire:click="selectRole('company')"
                        class="group relative overflow-hidden bg-white p-6 rounded-2xl border-3 border-[#4CAF50] hover:bg-[#F0F8F0] transition-all transform hover:scale-105 shadow-lg hover:shadow-xl">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-[#4CAF50] rounded-full flex items-center justify-center">
                                <i class="fas fa-bullhorn text-white text-xl"></i>
                            </div>
                            <div class="flex-1 text-left">
                                <h3 class="text-xl font-bold text-[#4CAF50] mb-2">ホスト</h3>
                                <p class="text-sm text-[#6B6760] mb-2">平泉で活動する個人・団体が登録できます</p>
                                <p class="text-xs text-[#6B6760]">情報発信や募集企画作成ができます。（※ご本人様確認後に利用いただけます）</p>
                            </div>
                            <i
                                class="fas fa-chevron-right text-[#4CAF50] text-xl group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </button>
                </div>
            @endif

            <!-- ステップ2: アカウント情報入力 -->
            @if ($step === 2)
                <div class="text-center">
                    <h1 class="text-2xl font-bold text-[#3E3A35] mb-2">アカウント情報を入力</h1>
                    <p class="text-sm text-[#6B6760]">
                        <span class="inline-flex items-center gap-1">
                            <i
                                class="fas {{ $role === 'worker' ? 'fa-users text-[#FF6B35]' : 'fa-bullhorn text-[#4CAF50]' }}"></i>
                            <span class="font-medium {{ $role === 'worker' ? 'text-[#FF6B35]' : 'text-[#4CAF50]' }}">
                                {{ $role === 'worker' ? 'ひらいず民' : 'ホスト' }}
                            </span>
                        </span>
                        として登録します
                    </p>
                </div>

                <form wire:submit="register" class="flex flex-col gap-6">
                    <!-- Email Address -->
                    <flux:field>
                        <flux:label>メールアドレス</flux:label>
                        <flux:input wire:model="email" type="email" autocomplete="email"
                            placeholder="email@example.com" />
                        <flux:error name="email" />
                    </flux:field>

                    <!-- Password -->
                    <flux:field>
                        <flux:label>パスワード</flux:label>
                        <flux:input wire:model="password" type="password" autocomplete="new-password"
                            placeholder="8文字以上で入力してください" viewable />
                        <flux:error name="password" />
                    </flux:field>

                    <!-- Confirm Password -->
                    <flux:field>
                        <flux:label>パスワード(確認)</flux:label>
                        <flux:input wire:model="password_confirmation" type="password" autocomplete="new-password"
                            placeholder="もう一度入力してください" viewable />
                    </flux:field>

                    <!-- ボタン -->
                    <div class="flex flex-col gap-3">
                        <button type="submit"
                            class="w-full bg-[#FF6B35] hover:bg-[#E55A28] text-white px-6 py-3 rounded-full font-bold transition-all transform hover:scale-105 shadow-lg hover:shadow-xl"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove>アカウントをつくる</span>
                            <span wire:loading>登録中...</span>
                        </button>

                        <button type="button" wire:click="goBack"
                            class="w-full bg-white border-2 border-gray-300 hover:border-[#FF6B35] text-[#3E3A35] px-6 py-3 rounded-full font-bold transition-all">
                            <i class="fas fa-arrow-left mr-2"></i>戻る
                        </button>
                    </div>
                </form>
            @endif

            <!-- ログインリンク -->
            <div class="text-sm text-center text-[#6B6760]">
                <span>すでにアカウントをお持ちですか?</span>
                <a href="{{ route('login') }}" class="text-[#4CAF50] hover:text-[#45A049] font-medium ml-1"
                    wire:navigate>ログインする</a>
            </div>
        </div>
    @endvolt
</x-layouts.auth>
