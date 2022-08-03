<?php

namespace App\Http\Middleware;

use Closure;
use Permissions;

class IsValid
{
    public function handle($request, Closure $next, $module = null, $perm = null)
    {
        $res = Permissions::isAllow($module, $perm);
        if ($res) {
            return $next($request);
        }

        return abort(404);
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
