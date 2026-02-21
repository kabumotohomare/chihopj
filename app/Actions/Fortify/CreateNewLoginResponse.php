<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class CreateNewLoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        $user = $request->user();

        // ユーザーのロールに応じてリダイレクト（プロフィール未登録の場合）
        if ($user && $user->role === 'worker' && ! $user->workerProfile) {
            return redirect()->route('worker.register');
        }

        if ($user && $user->role === 'company' && ! $user->companyProfile) {
            return redirect()->route('company.register');
        }

        // プロフィール登録済みの場合はダッシュボードへ
        return redirect()->route('dashboard');
    }
}
