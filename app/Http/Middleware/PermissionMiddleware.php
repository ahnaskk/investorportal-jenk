<?php

namespace App\Http\Middleware;

use Closure;
use Permissions;

class PermissionMiddleware
{
    public function handle($request, Closure $next, $module = null)
    {
        $res = Permissions::checkAuth($module);
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
