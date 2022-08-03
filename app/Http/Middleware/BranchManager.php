<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class BranchManager
{
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->guest()) {
            return $this->failResponse($request);
        }
        if (! $request->user()->hasRole('branch manager')) {
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
