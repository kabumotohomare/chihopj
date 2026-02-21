<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
    <style>
        body {
            font-family: 'Noto Sans JP', sans-serif;
        }

        /* モバイルメニューのアニメーション */
        #mobile-menu {
            transform: translateX(-100%);
            transition: transform 0.3s ease-out;
        }

        #mobile-menu.show {
            transform: translateX(0);
        }

        #menu-overlay {
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease-out;
        }

        #menu-overlay.show {
            opacity: 1;
            pointer-events: auto;
        }

        /* デバッグ用：メニューの状態を確認 */
        #mobile-menu::before {
            content: 'Menu state: ' attr(data-state);
            position: absolute;
            top: 0;
            left: 0;
            background: yellow;
            color: black;
            padding: 4px;
            font-size: 10px;
            z-index: 1000;
        }
    </style>
</head>

<body class="min-h-screen bg-[#F5F3F0]">
    <!-- デバッグ用：常に表示されるテストボタン -->
    <div class="fixed bottom-4 right-4 z-[100]">
        <button onclick="console.log('TEST BUTTON CLICKED'); window.toggleMobileMenu(); return false;"
            class="bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg">
            TEST MENU
        </button>
    </div>

    <!-- Mobile Sidebar -->
    <div id="mobile-menu"
        class="fixed inset-y-0 left-0 z-50 w-64 border-e-4 border-[#FF6B35] bg-[#FFF8E7] p-6 shadow-lg lg:hidden">
        <!-- 閉じるボタン -->
        <button onclick="console.log('Close button clicked'); window.closeMobileMenu(); return false;"
            class="absolute top-4 right-4 text-[#3E3A35] hover:text-[#FF6B35]">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <!-- ロゴ -->
        <a href="{{ route('welcome') }}" class="mb-6 flex flex-col items-center gap-2" wire:navigate>
            <img src="{{ asset('images/presets/logo.png') }}" alt="みんなの平泉ロゴ" class="h-12 w-auto">
            <span class="text-lg font-bold text-[#FF6B35]">みんなの平泉</span>
        </a>

        <!-- メニュー -->
        <nav class="space-y-2">
            <h3 class="mb-2 text-sm font-semibold text-[#6B6760]">メニュー</h3>
            <a href="{{ route('dashboard') }}"
                class="flex items-center gap-2 rounded-lg px-3 py-2 text-[#3E3A35] hover:bg-[#FF6B35]/10 {{ request()->routeIs('dashboard') ? 'bg-[#FF6B35]/20' : '' }}"
                wire:navigate>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                ホーム画面に戻る
            </a>
        </nav>

        <div class="mt-auto pt-6">
            <a href="https://form.run/@furubono-l7N9omymBWguT5AQABCt" target="_blank"
                class="flex items-center gap-2 rounded-lg px-3 py-2 text-[#6B6760] hover:bg-[#4CAF50]/10">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                お問い合わせ
            </a>
        </div>
    </div>

    <!-- オーバーレイ -->
    <div id="menu-overlay" onclick="console.log('Overlay clicked'); window.closeMobileMenu(); return false;"
        class="fixed inset-0 z-40 bg-black bg-opacity-50 lg:hidden">
    </div>

    <!-- Header -->
    <flux:header container class="border-b-4 border-[#FF6B35] bg-[#FFF8E7]">
        <!-- ハンバーガーメニューボタン -->
        <button onclick="console.log('Button clicked'); window.toggleMobileMenu(); return false;"
            class="lg:hidden p-2 text-[#3E3A35] hover:text-[#FF6B35]" type="button">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <a href="{{ route('welcome') }}" class="ms-2 me-5 flex items-center space-x-2 rtl:space-x-reverse lg:ms-0"
            wire:navigate>
            <x-app-logo />
        </a>

        <flux:navbar class="-mb-px max-lg:hidden">
            <flux:navbar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
                wire:navigate class="text-[#3E3A35] hover:text-[#FF6B35]">
                ホーム画面に戻る
            </flux:navbar.item>
        </flux:navbar>

        <flux:spacer />

        @auth
            <!-- User Menu -->
            <flux:dropdown position="top" align="end">
                <flux:profile class="cursor-pointer" :initials="auth()->user()->initials()" />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name ?? 'ゲスト' }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>設定</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full"
                            data-test="logout-button">
                            ログアウト
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        @else
            <!-- Guest User Buttons -->
            <div class="flex gap-2">
                <flux:button :href="route('login')" variant="ghost" size="sm">
                    ログイン
                </flux:button>
                <flux:button :href="route('register')" variant="primary" size="sm">
                    会員登録
                </flux:button>
            </div>
        @endauth
    </flux:header>

    {{ $slot }}

    @fluxScripts

    <script>
        // グローバルスコープで関数を定義
        window.toggleMobileMenu = function() {
            console.log('=== toggleMobileMenu called ===');
            const menu = document.getElementById('mobile-menu');
            const overlay = document.getElementById('menu-overlay');

            console.log('menu:', menu);
            console.log('overlay:', overlay);

            if (!menu || !overlay) {
                console.error('Menu or overlay not found!');
                return;
            }

            const wasShowing = menu.classList.contains('show');
            console.log('Was showing:', wasShowing);

            menu.classList.toggle('show');
            overlay.classList.toggle('show');

            const isNowShowing = menu.classList.contains('show');
            console.log('Is now showing:', isNowShowing);

            // デバッグ用のdata属性を更新
            menu.setAttribute('data-state', isNowShowing ? 'open' : 'closed');

            // ボディのスクロールを制御
            if (isNowShowing) {
                document.body.style.overflow = 'hidden';
                console.log('Body scroll disabled');
            } else {
                document.body.style.overflow = '';
                console.log('Body scroll enabled');
            }

            console.log('=== toggleMobileMenu completed ===');
        };

        window.closeMobileMenu = function() {
            console.log('=== closeMobileMenu called ===');
            const menu = document.getElementById('mobile-menu');
            const overlay = document.getElementById('menu-overlay');

            if (!menu || !overlay) {
                console.error('Menu or overlay not found!');
                return;
            }

            menu.classList.remove('show');
            overlay.classList.remove('show');
            menu.setAttribute('data-state', 'closed');
            document.body.style.overflow = '';
            console.log('=== closeMobileMenu completed ===');
        };

        // Escapeキーでメニューを閉じる
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                console.log('Escape key pressed');
                window.closeMobileMenu();
            }
        });

        // Livewire navigate後も動作するように
        document.addEventListener('livewire:navigated', function() {
            console.log('Livewire navigated - reinitializing menu');
        });

        // ページ読み込み時の初期化
        console.log('=== Mobile menu script loaded ===');
        console.log('window.toggleMobileMenu:', typeof window.toggleMobileMenu);
        console.log('window.closeMobileMenu:', typeof window.closeMobileMenu);

        // メニュー要素が存在するか確認
        const menu = document.getElementById('mobile-menu');
        const overlay = document.getElementById('menu-overlay');
        console.log('Initial menu element:', menu);
        console.log('Initial overlay element:', overlay);

        if (menu) {
            menu.setAttribute('data-state', 'closed');
            console.log('Menu initial state set to: closed');
        }
    </script>
</body>

</html>
