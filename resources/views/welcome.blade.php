<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>ふるぼの - みんなの平泉｜平泉のお手伝い案内所</title>
    <meta name="description" content="平泉町でのお手伝いを通じて、地域と繋がる体験ができます">

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=noto-sans-jp:400,500,600,700" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Noto Sans JP', sans-serif;
        }

        /* Alpine.js x-cloak */
        [x-cloak] {
            display: none !important;
        }

        /* どうぶつの森風のアニメーション */
        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.8);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.8s ease-out forwards;
        }

        .animate-scale-in {
            animation: scaleIn 0.6s ease-out forwards;
        }

        /* 手書き風の影 */
        .text-shadow-soft {
            text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.2);
        }

        /* ポップなボタンスタイル */
        .btn-pop {
            box-shadow: 0 6px 0 rgba(0, 0, 0, 0.2);
            transition: all 0.1s;
        }

        .btn-pop:active {
            transform: translateY(4px);
            box-shadow: 0 2px 0 rgba(0, 0, 0, 0.2);
        }
    </style>
</head>

<body class="antialiased bg-[#F5F3F0] text-[#3E3A35]">
    <!-- ヘッダー -->
    <header class="sticky top-0 z-50 bg-[#FFF8E7]/95 backdrop-blur-sm border-b-4 border-[#FF6B35]"
        x-data="{ mobileMenuOpen: false }">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex items-center justify-between h-16">
                <!-- ロゴ -->
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/presets/logo.png') }}" alt="ふるぼの - みんなの平泉ロゴ" class="h-12 w-auto">
                    <span class="text-xl md:text-2xl font-bold text-[#FF6B35]">ふるぼの - みんなの平泉</span>
                </div>

                <!-- デスクトップメニュー -->
                <div class="hidden md:flex items-center gap-6">
                    <a href="#jobs"
                        class="text-[#3E3A35] hover:text-[#FF6B35] transition-colors font-bold text-lg hover:scale-110 transform">
                        お手伝い
                    </a>
                    @auth
                        <a href="{{ url('/dashboard') }}"
                            class="bg-[#FF6B35] hover:bg-[#E55A28] text-white px-6 py-2.5 rounded-full transition-all font-bold shadow-lg hover:shadow-xl transform hover:scale-105">
                            @if (auth()->user()->role === 'worker')
                                {{ auth()->user()->workerProfile?->handle_name ?? auth()->user()->name }}さんの部屋
                            @else
                                {{ auth()->user()->name }}さんの部屋
                            @endif
                        </a>
                    @else
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}"
                                class="bg-[#4CAF50] hover:bg-[#45A049] text-white px-6 py-2.5 rounded-full transition-all font-bold shadow-lg hover:shadow-xl transform hover:scale-105">
                                ただいま
                            </a>
                        @endif
                    @endauth
                </div>

                <!-- モバイルメニューボタン -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-[#FF6B35] p-2">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </nav>

            <!-- モバイルメニュー -->
            <div x-show="mobileMenuOpen" x-cloak @click.away="mobileMenuOpen = false"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform -translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform -translate-y-2" class="md:hidden py-4 space-y-3">
                <a href="#jobs" @click="mobileMenuOpen = false"
                    class="block text-[#3E3A35] hover:text-[#FF6B35] transition-colors font-bold py-2 text-center">
                    お手伝い
                </a>
                @auth
                    <a href="{{ url('/dashboard') }}" @click="mobileMenuOpen = false"
                        class="block text-[#3E3A35] hover:text-[#FF6B35] transition-colors font-bold py-2 text-center">
                        @if (auth()->user()->role === 'worker')
                            {{ auth()->user()->workerProfile?->handle_name ?? auth()->user()->name }}さんの部屋
                        @else
                            {{ auth()->user()->name }}さんの部屋
                        @endif
                    </a>
                @else
                    <a href="{{ route('login') }}" @click="mobileMenuOpen = false"
                        class="block text-[#3E3A35] hover:text-[#FF6B35] transition-colors font-bold py-2 text-center">
                        ただいま
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- ヒーローセクション（どうぶつの森風） -->
    <section class="relative min-h-screen overflow-hidden">
        <!-- 平泉の農村風景背景 -->
        <div class="absolute inset-0">
            <img src="{{ asset('images/presets/fv.jpg') }}" alt="平泉の農村風景" class="w-full h-full object-cover">
            <!-- 明るく柔らかいオーバーレイ -->
            <div class="absolute inset-0 bg-gradient-to-b from-[#87CEEB]/40 via-[#B0E0E6]/30 to-[#FFF8E7]/50"></div>
        </div>

        <!-- 雲のイラスト風装飾 -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <!-- 雲1 -->
            <div class="absolute top-10 left-10 w-32 h-16 bg-white/40 rounded-full blur-xl animate-float"></div>
            <div class="absolute top-20 right-20 w-40 h-20 bg-white/30 rounded-full blur-xl animate-float"
                style="animation-delay: 1s;"></div>
            <div class="absolute top-40 left-1/3 w-36 h-18 bg-white/30 rounded-full blur-xl animate-float"
                style="animation-delay: 2s;"></div>
        </div>

        <!-- メインコンテンツ -->
        <div class="relative h-full flex items-center justify-center py-16 sm:py-20 px-4">
            <div class="text-center max-w-4xl mx-auto w-full">
                <!-- メインキャッチコピー -->
                <div class="mb-6 sm:mb-8 animate-scale-in">
                    <h1 class="text-4xl sm:text-5xl md:text-7xl lg:text-8xl font-black text-[#FF6B35] mb-4 leading-tight"
                        style="text-shadow: 4px 4px 8px rgba(255, 255, 255, 0.9), 2px 2px 4px rgba(0, 0, 0, 0.3);">
                        あつまれ<br>
                        <span class="text-[#4CAF50]">ひらいず民</span>！
                    </h1>
                </div>

                <!-- サブキャッチ -->
                <div class="mb-8 sm:mb-12 animate-fade-in-up px-2" style="animation-delay: 0.2s;">
                    <p class="text-lg sm:text-2xl md:text-3xl font-bold text-[#3E3A35]">
                        ~ 住んでいる人も、<br class="xs:inline sm:hidden">外から来た人も ~
                    </p>
                </div>

                <!-- 説明文 -->
                <div class="mb-8 sm:mb-12 animate-fade-in-up px-2" style="animation-delay: 0.4s;">
                    <div
                        class="bg-white/90 backdrop-blur-md px-4 sm:px-6 py-3 sm:py-4 rounded-2xl shadow-xl inline-block max-w-full">
                        <p class="text-base sm:text-xl md:text-2xl text-[#3E3A35] font-medium leading-relaxed">
                            お手伝いを通じて、<br class="xs:inline sm:hidden"><span
                                class="text-[#4CAF50] font-bold">平泉の暮らし</span>に参加しよう！<br>
                            <span class="text-[#FF6B35] font-bold">あなた</span>を待っている人がいます。
                        </p>
                    </div>
                </div>

                <!-- CTAボタン -->
                <div class="flex flex-col sm:flex-row gap-4 sm:gap-6 justify-center items-center animate-fade-in-up px-4"
                    style="animation-delay: 0.6s;">
                    <a href="#jobs"
                        class="btn-pop bg-[#FF6B35] hover:bg-[#E55A28] text-white px-8 sm:px-10 py-4 sm:py-5 rounded-full font-black text-lg sm:text-xl md:text-2xl transition-all transform hover:scale-110 shadow-2xl w-full sm:w-auto">
                        <i class="fas fa-hand-holding-heart mr-2"></i>
                        お手伝いを見る
                    </a>
                    @guest
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}"
                                class="btn-pop bg-[#4CAF50] hover:bg-[#45A049] text-white px-8 sm:px-10 py-4 sm:py-5 rounded-full font-black text-lg sm:text-xl md:text-2xl transition-all transform hover:scale-110 shadow-2xl w-full sm:w-auto">
                                <i class="fas fa-door-open mr-2"></i>
                                はじめる
                            </a>
                        @endif
                    @endguest
                </div>

                <!-- 装飾的なアイコン -->
                <div class="mt-12 sm:mt-16 flex justify-center gap-4 sm:gap-6 md:gap-8 text-3xl sm:text-4xl md:text-5xl animate-fade-in-up"
                    style="animation-delay: 0.8s;">
                    <div class="animate-float bg-white/80 backdrop-blur-sm rounded-full p-3 sm:p-4 shadow-lg"
                        style="animation-delay: 0s;">
                        <i class="fas fa-seedling text-[#4CAF50]"></i>
                    </div>
                    <div class="animate-float bg-white/80 backdrop-blur-sm rounded-full p-3 sm:p-4 shadow-lg"
                        style="animation-delay: 0.5s;">
                        <i class="fas fa-heart text-[#FF6B35]"></i>
                    </div>
                    <div class="animate-float bg-white/80 backdrop-blur-sm rounded-full p-3 sm:p-4 shadow-lg"
                        style="animation-delay: 1s;">
                        <i class="fas fa-home text-[#FFD700]"></i>
                    </div>
                    <div class="animate-float bg-white/80 backdrop-blur-sm rounded-full p-3 sm:p-4 shadow-lg"
                        style="animation-delay: 1.5s;">
                        <i class="fas fa-users text-[#87CEEB]"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- スクロールインジケーター -->
        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
            <a href="#jobs"
                class="bg-white/80 backdrop-blur-sm rounded-full p-3 shadow-lg hover:bg-white transition-all">
                <i class="fas fa-chevron-down text-3xl text-[#FF6B35]"></i>
            </a>
        </div>
    </section>

    <!-- お手伝い情報セクション -->
    <section id="jobs" class="py-16 md:py-24">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-[#3E3A35] mb-4">
                    あなたが来るのを待っています
                </h2>
                <p class="text-lg text-[#6B6760]">
                    新着のお手伝いをチェックしましょう
                </p>
            </div>

            @if ($latestJobs->isEmpty())
                <div class="text-center py-16">
                    <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                    <p class="text-xl text-[#6B6760]">現在、お手伝いはありません</p>
                </div>
            @else
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
                    @foreach ($latestJobs as $job)
                        <a href="{{ route('jobs.show', $job) }}"
                            class="group block bg-white rounded-2xl overflow-hidden shadow-md hover:shadow-2xl transition-all transform hover:-translate-y-2">
                            <!-- アイキャッチ画像 -->
                            <div class="aspect-video overflow-hidden bg-gray-100">
                                @if ($job->eyecatch)
                                    @if (str_starts_with($job->eyecatch, '/images/presets/'))
                                        <img src="{{ asset($job->eyecatch) }}" alt="{{ $job->job_title }}"
                                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                    @else
                                        <img src="{{ asset('storage/' . $job->eyecatch) }}"
                                            alt="{{ $job->job_title }}"
                                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                    @endif
                                @else
                                    <div
                                        class="w-full h-full flex items-center justify-center bg-gradient-to-br from-[#FFE5D9] to-[#FFF5ED]">
                                        <i class="fas fa-image text-6xl text-[#FF6B35]/30"></i>
                                    </div>
                                @endif
                            </div>

                            <div class="p-6">
                                <!-- やること -->
                                <h3
                                    class="text-xl font-bold text-[#3E3A35] mb-3 line-clamp-2 group-hover:text-[#FF6B35] transition-colors">
                                    {{ $job->job_title }}
                                </h3>

                                <!-- ホスト名 -->
                                <div class="flex items-center gap-2 text-sm text-[#6B6760]">
                                    <i class="fas fa-building text-[#FF6B35]"></i>
                                    <span>{{ $job->company->name }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="text-center">
                    <a href="{{ route('jobs.index') }}"
                        class="inline-flex items-center gap-2 bg-[#4CAF50] hover:bg-[#45A049] text-white px-8 py-4 rounded-full transition-all transform hover:scale-105 font-bold text-lg shadow-lg">
                        <span>募集中のお手伝いを見る</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            @endif
        </div>
    </section>

    <!-- フッター -->
    <footer class="bg-[#3E3A35] text-white py-12">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-8 items-center">
                <div class="text-center md:text-left">
                    <div class="flex items-center gap-3 justify-center md:justify-start mb-4">
                        <img src="{{ asset('images/presets/logo.png') }}" alt="ふるぼの - みんなの平泉ロゴ" class="h-10 w-auto">
                        <span class="text-xl font-bold">ふるぼの - みんなの平泉</span>
                    </div>
                    <p class="text-sm text-gray-300">お手伝いでみんなの地域とつながる</p>
                </div>

                <div class="text-center md:text-right">
                    <a href="https://mekabu.tech/privacy-policy" target="_blank" rel="noopener noreferrer"
                        class="text-sm text-gray-300 hover:text-white transition-colors inline-flex items-center gap-1">
                        <span>プライバシーポリシー</span>
                        <i class="fas fa-external-link-alt text-xs"></i>
                    </a>
                </div>
            </div>

            <div class="mt-8 pt-8 border-t border-gray-600 text-center text-sm text-gray-400">
                <p>© 2026 MEKABU.co. All rights reserved.</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>

</html>
