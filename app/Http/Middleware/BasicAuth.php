<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BasicAuth
{
    public function handle(Request $request, Closure $next)
    {
        $AUTH_USER = 'admininvestor';
        $AUTH_PASS = 'admin9879871';
        header('Cache-Control: no-cache, must-revalidate, max-age=0');
        $has_supplied_credentials = ! (empty($_REQUEST['PHP_AUTH_USER']) && empty($_REQUEST['PHP_AUTH_PW']));
        $is_not_authenticated = (! $has_supplied_credentials || $_REQUEST['PHP_AUTH_USER'] != $AUTH_USER || $_REQUEST['PHP_AUTH_PW'] != $AUTH_PASS);
        if ($is_not_authenticated) {
            header('HTTP/1.1 401 Authorization Required');
            header('WWW-Authenticate: Basic realm="Access denied"');

            return response()->json(['status' => 401, 'error' => 'You are Unauthorised', 'data' => '']);
        }

        return $next($request);
    }
}
