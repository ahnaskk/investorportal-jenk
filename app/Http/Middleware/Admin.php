<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class Admin
{
    public function handle($request, Closure $next, $guard = null)
    {
        error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
        $db = session('DB_DATABASE');
        if ($db) {
            \Config::set('database.connections.mysql.database', $db);
            DB::purge('mysql');
        }
        $app_mode = config('app.env');
        if (Auth::guard($guard)->guest()) {
            return $this->failResponse($request);
        }
        if (! $request->user()->hasRole('admin') && ! $request->user()->hasRole('company') && ! $request->user()->hasRole('lender') && ! $request->user()->hasRole('editor') && ! $request->user()->hasRole('viewer') && ! $request->user()->hasRole('collection user') && ! $request->user()->hasRole('merchant')) {
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
