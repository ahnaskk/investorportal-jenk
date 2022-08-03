<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Company
{
    public function handle($request, Closure $next)
    {
        if ($request->user()->hasRole('company')) {
            return abort(404);
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
