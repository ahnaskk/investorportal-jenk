<?php

namespace App\Helpers;

use App\Merchant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;

class AuthHelper
{
    public static function getDefaultLoginToken()
    {
        $user = \Auth::user();
        $authToken = ($user) ? session('auth_sanctum_token_'.$user->id) : false;
        $model = Sanctum::$personalAccessTokenModel;
        $accessToken = $model::findToken($authToken);
        if (! $accessToken and $user) {
            $user->sanctumTokens()->delete();
            $token = $user->createSanctumToken(request()->device_name.'-'.$user->email);
            $authToken = $token->plainTextToken;
            session(['auth_sanctum_token_'.$user->id => $authToken]);
        }

        return $authToken;
    }
}
