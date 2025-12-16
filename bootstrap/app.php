<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // ログイン後のリダイレクトをカスタマイズ
        $middleware->redirectUsersTo(function (Request $request) {
            $user = $request->user();

            // 登録直後（セッションに保存されている場合）
            if (session()->has('register_redirect')) {
                $redirectUrl = session()->pull('register_redirect');

                return $redirectUrl;
            }

            // ユーザーのロールに応じてリダイレクト（プロフィール未登録の場合）
            if ($user) {
                if ($user->role === 'worker' && ! $user->workerProfile) {
                    return route('worker.register');
                }

                if ($user->role === 'company' && ! $user->companyProfile) {
                    return route('company.register');
                }
            }

            // 通常のログイン後のリダイレクト
            return route('dashboard');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
