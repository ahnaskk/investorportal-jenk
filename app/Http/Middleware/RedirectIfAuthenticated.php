<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\User;
class RedirectIfAuthenticated
{
    public function handle($request, Closure $next, $guard = null)
    {   
        $user_exist = User::join('user_has_roles', 'users.id', '=', 'user_has_roles.model_id');
        $user_exist = $user_exist->join('roles', 'roles.id', '=', 'user_has_roles.role_id');
        $user_exist = $user_exist->where('users.email', $request->email)->where(function ($q) {
            $q->whereNotIn('roles.id', [User::OVERPAYMENT_ROLE,User::AGENT_FEE_ROLE]);
        });
        $user_exist = $user_exist->whereNotNull('users.email');
        $user_exist = $user_exist->whereNotNull('users.password');
        $user_exist = $user_exist->first();
      if($user_exist){
            if($user_exist->name=="INVESTOR"){
                if($user_exist->login_board=="new"){
                    return redirect()->to('investors');
                }
            }
      }else {
            if (Auth::guard($guard)->check()) {
                if ((Auth::guard($guard)->user()->hasRole('admin')) || (Auth::guard($guard)->user()->hasRole('editor')) || (Auth::guard($guard)->user()->hasRole('viewer')) || (Auth::guard($guard)->user()->hasRole('lender')) || (Auth::guard($guard)->user()->hasRole('company'))|| (Auth::guard($guard)->user()->hasRole('collection user'))) {
                    return redirect()->route('admin::dashboard::index');
                } elseif (Auth::guard($guard)->user()->hasRole('branch manager')) {
                    return redirect()->route('branch::marketplace::index');
                } elseif (Auth::guard($guard)->user()->hasRole('collection user')) {
                    return redirect()->route('admin::merchants::index');
                } elseif (Auth::guard($guard)->user()->hasRole('investor')) {
                    return redirect()->route('investor::dashboard::index');
                } else {
                    return redirect()->route('admin::dashboard::index');
                }
            }
        }
        
        return $next($request);
    }
}
