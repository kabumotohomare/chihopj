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
    </style>
</head>

<body class="antialiased bg-[#F5F3F0] text-[#3E3A35]">
    <!-- ヘッダー -->
    <header class="sticky top-0 z-50 bg-white/90 backdrop-blur-sm border-b border-gray-200" x-data="{ mobileMenuOpen: false }">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex items-center justify-between h-16">
                <!-- ロゴ -->
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/presets/logo.png') }}" alt="ふるぼの - みんなの平泉ロゴ" class="h-10 w-auto">
                    <span class="text-2xl font-bold text-[#FF6B35]">ふるぼの - みんなの平泉</span>
                </div>

                <!-- デスクトップメニュー -->
                <div class="hidden md:flex items-center gap-6">
                    <a href="#jobs" class="text-[#3E3A35] hover:text-[#FF6B35] transition-colors font-medium">
                        お手伝い
                    </a>
                    @auth
                        <a href="{{ url('/dashboard') }}"
                            class="bg-[#FF6B35] hover:bg-[#E55A28] text-white px-6 py-2 rounded-full transition-colors font-medium">
                            ダッシュボード
                        </a>
                    @else
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}"
                                class="bg-[#FF6B35] hover:bg-[#E55A28] text-white px-6 py-2 rounded-full transition-colors font-medium">
                                ログイン
                            </a>
                        @endif
                    @endauth
                </div>

                <!-- モバイルメニューボタン -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-[#3E3A35] p-2">
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
                    class="block text-[#3E3A35] hover:text-[#FF6B35] transition-colors font-medium py-2">
                    地域のお手伝い
                </a>
                @auth
                    <a href="{{ url('/dashboard') }}"
                        class="block bg-[#FF6B35] hover:bg-[#E55A28] text-white text-center px-6 py-2 rounded-full transition-colors font-medium">
                        ダッシュボード
                    </a>
                @else
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}"
                            class="block bg-[#FF6B35] hover:bg-[#E55A28] text-white text-center px-6 py-2 rounded-full transition-colors font-medium">
                            ログイン
                        </a>
                    @endif
                @endauth
            </div>
        </div>
    </header>

    <!-- ヒーローセクション -->
    <section class="relative h-screen min-h-[600px] overflow-hidden">
        <!-- 背景画像 -->
        <div class="absolute inset-0">
            <img src="{{ asset('images/presets/fv.jpg') }}" alt="地方の風景" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-r from-black/60 via-black/40 to-transparent"></div>
        </div>

        <!-- コンテンツ -->
        <div class="relative h-full flex items-center">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <h1
                        class="text-4xl md:text-6xl lg:text-7xl font-bold text-white mb-6 leading-tight drop-shadow-2xl">
                        平泉を、<br>手伝いませんか？
                    </h1>
                    <p class="text-xl md:text-2xl text-white/90 mb-10 drop-shadow-lg">
                        お手伝いで"ひらいずみ暮らし"を味わおう。
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="#jobs"
                            class="bg-[#FF6B35] hover:bg-[#E55A28] text-white px-8 py-4 rounded-full transition-all transform hover:scale-105 font-bold text-lg shadow-2xl text-center">
                            お手伝いを見る
                        </a>
                        @guest
                            @if (Route::has('login'))
                                <a href="{{ route('login') }}"
                                    class="bg-white hover:bg-gray-50 text-[#FF6B35] border-2 border-white px-8 py-4 rounded-full transition-all transform hover:scale-105 font-bold text-lg shadow-2xl text-center">
                                    はじめる
                                </a>
                            @endif
                        @endguest
                    </div>
                </div>
            </div>
        </div>

        <!-- スクロールインジケーター -->
        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
            <a href="#jobs" class="text-white/80 hover:text-white transition-colors">
                <i class="fas fa-chevron-down text-3xl"></i>
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

                                <!-- 企業名 -->
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
                        <img src="{{ asset('images/presets/logo.png') }}" alt="ふるぼの - みんなの平泉ロゴ" class="h-8 w-auto">
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
                <p>© 2025 MEKABU.co. All rights reserved.</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>

</html>
