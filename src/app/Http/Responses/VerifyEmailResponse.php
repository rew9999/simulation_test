<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\VerifyEmailResponse as VerifyEmailResponseContract;

class VerifyEmailResponse implements VerifyEmailResponseContract
{
    public function toResponse($request)
    {
        return redirect()->route('mypage.edit')->with('status', 'メール認証が完了しました。プロフィールを設定してください。');
    }
}
