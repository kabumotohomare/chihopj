<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class CreateNewRegisterResponse implements RegisterResponseContract
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

        // ユーザーのロールに応じてリダイレクト
        if ($user && $user->role === 'worker') {
            return redirect()->route('worker.register');
        }

        if ($user && $user->role === 'company') {
            return redirect()->route('company.register');
        }

        // デフォルトのリダイレクト先
        return redirect()->route('dashboard');
    }
}
