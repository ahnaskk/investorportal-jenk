<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Investor
{
    public function handle($request, Closure $next, $guard = null)
    {
        if(Auth::user()->login_board=='new'){        
            Auth::guard($guard)->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->to('investors');
            //return redirect()->to('/login');
        }
        if (Auth::guard($guard)->guest()) {
            return $this->failResponse($request);
        }

        return $next($request);
    }

    protected function failResponse($request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response('Unauthorized.', 401);
        } else {
            return redirect()->guest('login');
        }
    }
}
