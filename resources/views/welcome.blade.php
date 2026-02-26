<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>みんなの平泉｜ふるぼの feat. ふるさと住民票®</title>
    <meta name="description" content="平泉町のふるさと住民になって、地域活動に参加しよう！ふるさと住民票®と連動した新しい地域参画の提案。">

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
            font-weight: 500;
            letter-spacing: 0.05em;
        }

        /* Alpine.js x-cloak */
        [x-cloak] {
            display: none !important;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .animate-float {
            animation: float 4s ease-in-out infinite;
        }

        .animate-fade-in-up {
            animation: fadeInUp 1s ease-out forwards;
        }

        .animate-scale-in {
            animation: scaleIn 0.8s ease-out forwards;
        }

        .text-shadow-soft {
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .btn-pop {
            box-shadow: 0 4px 0 rgba(0, 0, 0, 0.15);
            transition: all 0.15s ease;
        }

        .btn-pop:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 0 rgba(0, 0, 0, 0.15);
        }

        .btn-pop:active {
            transform: translateY(2px);
            box-shadow: 0 2px 0 rgba(0, 0, 0, 0.15);
        }

        .rounded-organic {
            border-radius: 2rem 1.8rem 2rem 1.9rem / 1.9rem 2rem 1.8rem 2rem;
        }

        .step-number {
            font-weight: 900;
            letter-spacing: -0.02em;
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
                <a href="{{ url('/') }}" class="flex items-center gap-3 hover:opacity-80 transition-opacity">
                    <img src="{{ asset('images/presets/logo.png') }}" alt="ふるぼのロゴ" class="h-10 w-auto">
                    <span class="text-xl md:text-2xl font-bold text-[#FF6B35]">みんなの平泉</span>
                </a>

                <!-- デスクトップメニュー -->
                <div class="hidden md:flex items-center gap-6">
                    <a href="#jobs"
                        class="text-[#3E3A35] hover:text-[#FF6B35] transition-colors font-bold text-lg hover:scale-110 transform">
                        募集を見る
                    </a>
                    @auth
                        <a href="{{ url('/dashboard') }}"
                            class="bg-[#FF6B35] hover:bg-[#E55A28] text-white px-6 py-2.5 rounded-full transition-all font-bold shadow-lg hover:shadow-xl transform hover:scale-105">
                            @if (auth()->user()->role === 'worker')
                                {{ auth()->user()->workerProfile?->handle_name ?? (auth()->user()->name ?? 'ゲスト') }}さんの部屋
                            @else
                                {{ auth()->user()->name ?? 'ゲスト' }}さんの部屋
                            @endif
                        </a>
                    @else
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}"
                                class="bg-[#4CAF50] hover:bg-[#45A049] text-white px-6 py-2.5 rounded-full transition-all font-bold shadow-lg hover:shadow-xl transform hover:scale-105">
                                ようこそ
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
                    募集を見る
                </a>
                @auth
                    <a href="{{ url('/dashboard') }}" @click="mobileMenuOpen = false"
                        class="block text-[#3E3A35] hover:text-[#FF6B35] transition-colors font-bold py-2 text-center">
                        @if (auth()->user()->role === 'worker')
                            {{ auth()->user()->workerProfile?->handle_name ?? (auth()->user()->name ?? 'ゲスト') }}さんの部屋
                        @else
                            {{ auth()->user()->name ?? 'ゲスト' }}さんの部屋
                        @endif
                    </a>
                @else
                    <a href="{{ route('login') }}" @click="mobileMenuOpen = false"
                        class="block text-[#3E3A35] hover:text-[#FF6B35] transition-colors font-bold py-2 text-center">
                        ようこそ
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <section class="relative min-h-screen overflow-hidden">
        <div class="absolute inset-0">
            <img src="{{ asset('images/presets/fv.jpg') }}" alt="平泉の農村風景" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-b from-[#87CEEB]/40 via-[#B0E0E6]/30 to-[#FFF8E7]/50"></div>
        </div>

        <!-- メインコンテンツ -->
        <div class="relative h-full flex items-center justify-center py-16 sm:py-20 px-4">
            <div class="text-center max-w-4xl mx-auto w-full">
                <!-- メインキャッチコピー -->
                <div class="mb-6 sm:mb-8 animate-scale-in">
                    <h1 class="text-4xl sm:text-5xl md:text-7xl lg:text-8xl font-black text-[#FF6B35] mb-4 leading-tight"
                        style="text-shadow: 3px 3px 0px rgba(255, 255, 255, 0.8), 1px 1px 2px rgba(0, 0, 0, 0.15); letter-spacing: 0.05em;">
                        あつまれ<br>
                        <span class="text-[#4CAF50]">ひらいず民</span>！
                    </h1>
                </div>

                <!-- サブキャッチ -->
                <div class="mb-8 sm:mb-12 animate-fade-in-up px-2" style="animation-delay: 0.2s;">
                    <p class="text-lg sm:text-2xl md:text-3xl font-bold text-[#3E3A35]" style="letter-spacing: 0.08em;">
                        ~ 住んでいる人も、<br class="xs:inline sm:hidden">外から来た人も ~
                    </p>
                </div>

                <!-- 説明文 -->
                <div class="mb-8 sm:mb-12 animate-fade-in-up px-2" style="animation-delay: 0.4s;">
                    <div
                        class="bg-white/95 backdrop-blur-sm px-5 sm:px-7 py-4 sm:py-5 rounded-organic shadow-lg inline-block max-w-full">
                        <p class="text-base sm:text-xl md:text-2xl text-[#3E3A35] font-medium leading-relaxed"
                            style="letter-spacing: 0.05em;">
                            担い手として、<br class="xs:inline sm:hidden"><span
                                class="text-[#4CAF50] font-bold">平泉の暮らし</span>に参加しよう！<br>
                            <span class="text-[#FF6B35] font-bold">あなた</span>を待っている人がいます。
                        </p>
                    </div>
                </div>

                <!-- CTAボタン -->
                <div class="flex flex-col sm:flex-row gap-4 sm:gap-6 justify-center items-center animate-fade-in-up px-4"
                    style="animation-delay: 0.6s;">
                    <a href="#jobs"
                        class="btn-pop bg-[#FF6B35] hover:bg-[#E55A28] text-white px-8 sm:px-10 py-4 sm:py-5 rounded-full font-black text-lg sm:text-xl md:text-2xl transition-all w-full sm:w-auto"
                        style="letter-spacing: 0.1em;">
                        <i class="fas fa-hand-holding-heart mr-2"></i>
                        募集を見る
                    </a>
                    @guest
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}"
                                class="btn-pop bg-[#4CAF50] hover:bg-[#45A049] text-white px-8 sm:px-10 py-4 sm:py-5 rounded-full font-black text-lg sm:text-xl md:text-2xl transition-all w-full sm:w-auto"
                                style="letter-spacing: 0.1em;">
                                <i class="fas fa-door-open mr-2"></i>
                                はじめる
                            </a>
                        @endif
                    @endguest
                </div>

                <div class="mt-12 sm:mt-16 flex justify-center gap-4 sm:gap-6 md:gap-8 text-3xl sm:text-4xl md:text-5xl animate-fade-in-up"
                    style="animation-delay: 0.8s;">
                    <div class="animate-float bg-white/90 rounded-full p-3 sm:p-4 shadow-md"
                        style="animation-delay: 0s;">
                        <i class="fas fa-seedling text-[#4CAF50]"></i>
                    </div>
                    <div class="animate-float bg-white/90 rounded-full p-3 sm:p-4 shadow-md"
                        style="animation-delay: 0.8s;">
                        <i class="fas fa-heart text-[#FF6B35]"></i>
                    </div>
                    <div class="animate-float bg-white/90 rounded-full p-3 sm:p-4 shadow-md"
                        style="animation-delay: 1.6s;">
                        <i class="fas fa-home text-[#FFA000]"></i>
                    </div>
                    <div class="animate-float bg-white/90 rounded-full p-3 sm:p-4 shadow-md"
                        style="animation-delay: 2.4s;">
                        <i class="fas fa-users text-[#2196F3]"></i>
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

    <!-- 説明セクション -->
    <section class="py-16 md:py-24 bg-[#FFF8E7]">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto">
                <!-- タイトル -->
                <div class="text-center mb-10 animate-fade-in-up">
                    <h2 class="text-3xl md:text-4xl font-black text-[#FF6B35] mb-4 inline-flex items-center gap-3"
                        style="letter-spacing: 0.08em;">
                        <i class="fas fa-users text-4xl md:text-5xl"></i>
                        ひらいず民って？
                    </h2>
                </div>

                <!-- 説明カード -->
                <div class="bg-white rounded-organic shadow-md p-6 sm:p-8 md:p-10 animate-scale-in">
                    <div class="text-center space-y-6">
                        <!-- アイコン装飾 -->
                        <div class="flex justify-center gap-4 sm:gap-6 mb-8">
                            <div class="animate-float bg-[#FFE5D9] rounded-full p-4 shadow-sm">
                                <i class="fas fa-home text-3xl text-[#FF6B35]"></i>
                            </div>
                            <div class="animate-float bg-[#E8F5E9] rounded-full p-4 shadow-sm"
                                style="animation-delay: 1s;">
                                <i class="fas fa-heart text-3xl text-[#4CAF50]"></i>
                            </div>
                            <div class="animate-float bg-[#E3F2FD] rounded-full p-4 shadow-sm"
                                style="animation-delay: 2s;">
                                <i class="fas fa-handshake text-3xl text-[#2196F3]"></i>
                            </div>
                        </div>

                        <!-- 説明文 -->
                        <div class="text-lg md:text-xl text-[#3E3A35] leading-relaxed space-y-5"
                            style="letter-spacing: 0.05em;">
                            <p class="font-bold text-xl md:text-2xl text-[#FF6B35]">
                                住んでいる人も、外から来た人も、<br>
                                みんなが"平泉の人"です。
                            </p>
                            <p class="font-medium">
                                普段は離れていても、<br class="sm:hidden">
                                住民のように町と関わりを持つ人。<br>
                                それが<span
                                    class="text-[#4CAF50] font-black text-2xl">「ひらいず民」</span>です。<br><br>全国どこからでも、ひらいず民になると、<br>町で使える特典やサービス、<br>
                                行事やお手伝い募集などの情報が届きます。
                            </p>
                            <p class="text-base md:text-lg text-[#6B6760] pt-2">
                                <a href="https://www.town.hiraizumi.iwate.jp/%E3%80%8C%E3%81%B5%E3%82%8B%E3%81%95%E3%81%A8%E4%BD%8F%E6%B0%91%E3%80%8D%E3%82%92%E5%8B%9F%E9%9B%86%E3%81%97%E3%81%BE%E3%81%99%EF%BC%88%E3%81%B5%E3%82%8B%E3%81%95%E3%81%A8%E4%BD%8F%E6%B0%91%E5%88%B6-23557/"
                                    target="_blank" rel="noopener noreferrer"
                                    class="text-[#4CAF50] hover:text-[#45A049] font-medium underline decoration-2">ふるさと住民票®</a>の考えを取り入れています。
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 5ステップセクション -->
    <section class="py-16 md:py-24 bg-[#F5F3F0]">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <!-- タイトル -->
            <div class="text-center mb-14 animate-fade-in-up">
                <h2 class="text-3xl md:text-4xl font-black text-[#3E3A35] mb-4" style="letter-spacing: 0.08em;">
                    平泉の暮らしの楽しみ方
                </h2>
                <p class="text-lg text-[#6B6760] font-medium" style="letter-spacing: 0.05em;">
                    5つのステップで平泉とつながる
                </p>
            </div>

            <!-- 5ステップ縦並び -->
            <div class="max-w-3xl mx-auto space-y-5">
                <!-- ステップ1 -->
                <div class="flex items-start gap-4 md:gap-6 animate-fade-in-up">
                    <div
                        class="flex-shrink-0 w-16 h-16 md:w-20 md:h-20 bg-[#FF6B35] rounded-full flex items-center justify-center text-white shadow-md">
                        <span class="text-3xl md:text-4xl font-black step-number">1</span>
                    </div>
                    <div class="flex-1 bg-white rounded-organic shadow-md p-5 md:p-7">
                        <div class="flex items-center gap-3 mb-3">
                            <i class="fas fa-user-plus text-2xl md:text-3xl text-[#FF6B35]"></i>
                            <h3 class="text-xl md:text-2xl font-black text-[#3E3A35]" style="letter-spacing: 0.05em;">
                                ひらいず民（ふるさと住民）登録</h3>
                        </div>
                        <p class="text-[#6B6760] text-base md:text-lg leading-relaxed font-medium"
                            style="letter-spacing: 0.03em;">
                            どこに住んでいても平泉とかかわる最初の一歩（※もちろん無料）<br>ふるさと住民登録が認められると、各種特典を受けられます。
                        </p>
                    </div>
                </div>

                <!-- 矢印 -->
                <div class="flex justify-center py-1">
                    <i class="fas fa-arrow-down text-2xl text-[#6B6760] opacity-50 animate-bounce"></i>
                </div>

                <!-- ステップ2 -->
                <div class="flex items-start gap-4 md:gap-6 animate-fade-in-up" style="animation-delay: 0.1s;">
                    <div
                        class="flex-shrink-0 w-16 h-16 md:w-20 md:h-20 bg-[#4CAF50] rounded-full flex items-center justify-center text-white shadow-md">
                        <span class="text-3xl md:text-4xl font-black step-number">2</span>
                    </div>
                    <div class="flex-1 bg-white rounded-organic shadow-md p-5 md:p-7">
                        <div class="flex items-center gap-3 mb-3">
                            <i class="fas fa-search text-2xl md:text-3xl text-[#4CAF50]"></i>
                            <h3 class="text-xl md:text-2xl font-black text-[#3E3A35]" style="letter-spacing: 0.05em;">
                                平泉町の地域情報を見る</h3>
                        </div>
                        <p class="text-[#6B6760] text-base md:text-lg leading-relaxed font-medium"
                            style="letter-spacing: 0.03em;">
                            地域行事、季節のお祭り、ボランティアなど。募集をチェック
                        </p>
                    </div>
                </div>

                <!-- 矢印 -->
                <div class="flex justify-center py-1">
                    <i class="fas fa-arrow-down text-2xl text-[#6B6760] opacity-50 animate-bounce"></i>
                </div>

                <!-- ステップ3 -->
                <div class="flex items-start gap-4 md:gap-6 animate-fade-in-up" style="animation-delay: 0.2s;">
                    <div
                        class="flex-shrink-0 w-16 h-16 md:w-20 md:h-20 bg-[#2196F3] rounded-full flex items-center justify-center text-white shadow-md">
                        <span class="text-3xl md:text-4xl font-black step-number">3</span>
                    </div>
                    <div class="flex-1 bg-white rounded-organic shadow-md p-5 md:p-7">
                        <div class="flex items-center gap-3 mb-3">
                            <i class="fas fa-hand-paper text-2xl md:text-3xl text-[#2196F3]"></i>
                            <h3 class="text-xl md:text-2xl font-black text-[#3E3A35]" style="letter-spacing: 0.05em;">
                                メッセージを送ってつながる</h3>
                        </div>
                        <p class="text-[#6B6760] text-base md:text-lg leading-relaxed font-medium"
                            style="letter-spacing: 0.03em;">
                            地域で募集をする人（ホスト）にメッセージを送れます
                        </p>
                    </div>
                </div>

                <!-- 矢印 -->
                <div class="flex justify-center py-1">
                    <i class="fas fa-arrow-down text-2xl text-[#6B6760] opacity-50 animate-bounce"></i>
                </div>

                <!-- ステップ4 -->
                <div class="flex items-start gap-4 md:gap-6 animate-fade-in-up" style="animation-delay: 0.3s;">
                    <div
                        class="flex-shrink-0 w-16 h-16 md:w-20 md:h-20 bg-[#9C27B0] rounded-full flex items-center justify-center text-white shadow-md">
                        <span class="text-3xl md:text-4xl font-black step-number">4</span>
                    </div>
                    <div class="flex-1 bg-white rounded-organic shadow-md p-5 md:p-7">
                        <div class="flex items-center gap-3 mb-3">
                            <i class="fas fa-heart text-2xl md:text-3xl text-[#9C27B0]"></i>
                            <h3 class="text-xl md:text-2xl font-black text-[#3E3A35]" style="letter-spacing: 0.05em;">
                                一緒に町の活動に参加する</h3>
                        </div>
                        <p class="text-[#6B6760] text-base md:text-lg leading-relaxed font-medium"
                            style="letter-spacing: 0.03em;">
                            地域の活動や交流を楽しむ。あなたも私も皆ひらいず民！
                        </p>
                    </div>
                </div>

                <!-- 矢印 -->
                <div class="flex justify-center py-1">
                    <i class="fas fa-arrow-down text-2xl text-[#6B6760] opacity-50 animate-bounce"></i>
                </div>

                <!-- ステップ5 -->
                <div class="flex items-start gap-4 md:gap-6 animate-fade-in-up" style="animation-delay: 0.4s;">
                    <div
                        class="flex-shrink-0 w-16 h-16 md:w-20 md:h-20 bg-[#FFA000] rounded-full flex items-center justify-center text-white shadow-md">
                        <span class="text-3xl md:text-4xl font-black step-number">5</span>
                    </div>
                    <div class="flex-1 bg-white rounded-organic shadow-md p-5 md:p-7">
                        <div class="flex items-center gap-3 mb-3">
                            <i class="fas fa-gift text-2xl md:text-3xl text-[#FFA000]"></i>
                            <h3 class="text-xl md:text-2xl font-black text-[#3E3A35]" style="letter-spacing: 0.05em;">
                                "ひらいずみやげ"もらえるかも</h3>
                        </div>
                        <p class="text-[#6B6760] text-base md:text-lg leading-relaxed font-medium"
                            style="letter-spacing: 0.03em;">
                            ホストからお礼が出る募集もあります（※必須ではありません）
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 情報セクション -->
    <section id="jobs" class="py-16 md:py-24">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-14">
                <h2 class="text-3xl md:text-4xl font-black text-[#3E3A35] mb-4" style="letter-spacing: 0.08em;">
                    あなたが来るのを待っています
                </h2>
                <p class="text-lg text-[#6B6760] font-medium" style="letter-spacing: 0.05em;">
                    新着の募集をチェックしましょう
                </p>
            </div>

            @if ($latestJobs->isEmpty())
                <div class="text-center py-16">
                    <i class="fas fa-inbox text-6xl text-gray-300 mb-6"></i>
                    @auth
                        <p class="text-xl text-[#6B6760] mb-6">現在募集はありません。しばらくお待ちください。</p>
                        <a href="{{ url('/dashboard') }}"
                            class="inline-flex items-center gap-2 bg-[#4CAF50] hover:bg-[#45A049] text-white px-8 py-4 rounded-full transition-all transform hover:scale-105 font-bold text-lg shadow-lg btn-pop">
                            <i class="fas fa-door-open"></i>
                            <span>
                                @if (auth()->user()->role === 'worker')
                                    {{ auth()->user()->workerProfile?->handle_name ?? (auth()->user()->name ?? 'ゲスト') }}さんの部屋はこちら
                                @else
                                    {{ auth()->user()->name ?? 'ゲスト' }}さんの部屋はこちら
                                @endif
                            </span>
                        </a>
                    @else
                        <p class="text-xl text-[#6B6760] mb-6">ひらいず民（ふるさと住民）登録してお待ちください。</p>
                        <a href="{{ url('/register') }}"
                            class="inline-flex items-center gap-2 bg-[#FF6B35] hover:bg-[#E55A28] text-white px-8 py-4 rounded-full transition-all transform hover:scale-105 font-bold text-lg shadow-lg btn-pop">
                            <i class="fas fa-user-plus"></i>
                            <span>登録はこちらから</span>
                        </a>
                    @endauth
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
                        <span>募集をもっと見る</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            @endif
        </div>
    </section>

    <!-- ふるさと住民登録 -->
    <section class="py-16 md:py-24 bg-[#F5F3F0]">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto">
                <div class="bg-white rounded-organic shadow-md overflow-hidden animate-scale-in">
                    <img src="{{ asset('images/presets/hiraizumi_furusato_residents.jpg') }}" alt="平泉町ふるさと住民登録ポスター"
                        class="w-full h-auto object-cover">
                </div>
            </div>
        </div>
    </section>

    <!-- フッター -->
    <footer class="bg-[#3E3A35] text-white py-12">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-8 items-center">
                <div class="text-center md:text-left">
                    <div class="flex items-center gap-3 justify-center md:justify-start mb-4">
                        <img src="{{ asset('images/presets/logo.png') }}" alt="みんなの平泉ロゴ" class="h-8 w-auto">
                        <span class="text-xl font-bold">みんなの平泉</span>
                    </div>
                    <p class="text-sm text-gray-300">ふるぼの - ふるさと住民票®</p>
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
                <p>© 2026 ふるぼの All rights reserved.</p>
            </div>
        </div>
    </footer>

    @stack('scripts')

    <script src="//unpkg.com/alpinejs" defer></script>
</body>

</html>
