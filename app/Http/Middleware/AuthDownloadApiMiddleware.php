<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Auth\AuthController;
use App\Library\Repository\Interfaces\IMerchantRepository;
use Closure;

class AuthDownloadApiMiddleware
{
    public function __construct(IMerchantRepository $merchant)
    {
        $this->merchant = $merchant;
    }

    public function handle($request, Closure $next)
    {
        $token = $request->input('token');
        $tokenExtract = explode('-', $token);
        $auth = new AuthController($this->merchant);
        $auth->loginUserById($tokenExtract[0], $token);
        if (! $request->user()) {
            return response()->json(['status' => 401, 'error' => 'You are Unauthorised', 'data' => []]);
        }

        return $next($request);
    }
}
