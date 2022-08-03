<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class VisitLog
{
    public function handle(Request $request, Closure $next)
    {
        visitor()->visit(); // create a visit log
        return $next($request);
    }
}
