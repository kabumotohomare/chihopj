<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
    <style>
        body {
            font-family: 'Noto Sans JP', sans-serif;
        }
    </style>
</head>

<body class="min-h-screen bg-[#F5F3F0] antialiased">
    <div class="flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10 animate-fade-in-up">
        <div class="flex w-full max-w-md flex-col gap-6">
            <!-- ロゴとサイト名 -->
            <a href="{{ route('welcome') }}" class="flex flex-col items-center gap-3 font-medium" wire:navigate>
                <img src="{{ asset('images/presets/logo.png') }}" alt="みんなの平泉ロゴ" class="h-16 w-auto">
                <span class="text-2xl font-bold text-[#FF6B35]">みんなの平泉</span>
            </a>

            <!-- コンテンツカード -->
            <div class="bg-[#FFF8E7] rounded-2xl shadow-lg p-8">
                {{ $slot }}
            </div>
        </div>
    </div>
    @fluxScripts
</body>

</html>
