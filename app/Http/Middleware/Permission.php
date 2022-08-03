<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Permission
{
    public function handle($request, Closure $next, $guard = null)
    {
        ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        if ($permission) {
            $this->$accounts[1] = 'velocity';
            $this->$accounts[58] = 'VP Advance Funding';
        } else {
            $accounts[$request->user()->id] = $request->user()->name;
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
