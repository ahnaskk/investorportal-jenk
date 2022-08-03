<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    protected $role;
    protected $user;

    public function __construct()
    {
        $this->setDefaultAuth();
        $this->middleware(function ($request, $next) {
            $this->setDefaultAuth();

            return $next($request);
        });
    }

    private function setDefaultAuth()
    {
        if (! Auth::user()) {
            return false;
        }
        $this->user = Auth::user();
        $this->role = optional($this->user->roles()->first()->toArray())['name'] ?? '';
        if (! Auth::user()->hasRole('admin')) {
            abort(response()->json('Not found', 404));
        }
    }
}
