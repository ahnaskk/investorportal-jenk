<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\FirewallUser;
use App\Firewall;
use Illuminate\Database\Eloquent\Builder;

class FirewallMiddleware
{
    public function handle($request, Closure $next)
    {
        $whitelist_ips = $request->user()->firewalls();
        //firewall based on roles
        $user_id = $request->user()->id;
        $role_id = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('users.id',$user_id)->pluck('roles.id')->first();
        $current_ip = $request->ip();
        $fwallips = Firewall::where('ip_address',$current_ip)->first();
        if($fwallips){
            $firewall_roles = FirewallUser::where('role_id',$role_id)->whereIn('firewall_id',$fwallips)->get();
            if(count($firewall_roles) > 0 ){
                return $next($request);
            }
        }
        
        //firewall based on users
        
        if ($whitelist_ips->count() > 0) {
            $whitelist_ips = $whitelist_ips->pluck('ip_address');
            if ($whitelist_ips->contains($request->ip())) {
                return $next($request);
            } else {
                Auth::logout();

                return redirect()->to('/login');
            }
        } else {
            return $next($request);
        }
    }
}
