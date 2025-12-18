<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ユーザーロールを確認するミドルウェア
 */
class EnsureUserHasRole
{
    /**
     * リクエストを処理
     *
     * @param  string  $role  必要なロール
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        // ユーザーが認証されていない、またはロールが一致しない場合は403エラー
        if (! $user || $user->role !== $role) {
            abort(403, 'Access Denied');
        }

        return $next($request);
    }
}
