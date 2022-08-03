<?php

namespace App\Http\Controllers\Auth;

use App\Firewall;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminFirewall;
use App\User;
use Permissions;
use Yajra\DataTables\Html\Builder;
use App\FirewallUser;

class FirewallController extends Controller
{
    public function index(Builder $tableBuilder)
    {
        $page_title = 'User Firewall';

        $table1 = app(Builder::class)->ajax(route('admin::firewall::usersdata'));
        $table1 = $table1->parameters(['responsive' => true, 'autoWidth' => false, 'processing' => true, 'aaSorting' => [], 'pagingType' => 'input']);
        $table1 = $table1->columns([['className' => 'details-control', 'orderable' => false, 'searchable' => false, 'data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'defaultContent' => '', 'title' => '#'], ['data' => 'name', 'name' => 'name', 'title' => 'Name'], ['data' => 'email', 'name' => 'email', 'title' => 'Email'], ['data' => 'whitelisted_ips', 'name' => 'whitelisted_ips', 'title' => 'Whitelisted IPs'], ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Created At'], ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false]]);
        
        $table2 = app(Builder::class)->ajax(route('admin::firewall::rolesdata'));
        $table2 = $table2->parameters(['responsive' => true, 'autoWidth' => false, 'processing' => true, 'aaSorting' => [], 'pagingType' => 'input']);
        $table2 = $table2->columns([['className' => 'details-control', 'orderable' => false, 'searchable' => false, 'data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'defaultContent' => '', 'title' => '#'], ['data' => 'name', 'name' => 'name', 'title' => 'Role'], ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false]]);

        return view('admin.admins.view_users_firewall', compact('page_title', 'table1', 'table2'));
    }

    public function getallUsersData()
    {
        $allUsers = User::where('company_status',1)->withCount('firewalls');
        session_set('all_users', $users = User::withTrashed()->select('id', 'name')->get()->getDictionary());

        return \DataTables::of($allUsers)->addColumn('action', function ($data) {
            $return = '';
            if (Permissions::isAllow('Firewall', 'Edit')) {
                $return .= '<a href="'.route('admin::firewall::view', ['id' => $data->id]).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>';
            }

            return $return;
        })->addColumn('whitelisted_ips', function ($data) {
            return $data->firewalls_count;
        })->editColumn('created_at', function ($data) {
            $created_date = 'Created On '.\FFM::datetime($data->created_at).' by '.get_user_name_with_session($data->creator_id);

            return "<a title='$created_date'>".\FFM::datetime($data->created_at).'</a>';
        })->addIndexColumn()->rawColumns(['action', 'created_at'])->make(true);
    }
    public function getallRolesData()
    {
        $allRoles = \DB::table('roles')->get();
        session_set('all_users', $users = User::withTrashed()->select('id', 'name')->get()->getDictionary());

        return \DataTables::of($allRoles)
        ->addColumn('action', function ($data) {
            $return = '';
            if (Permissions::isAllow('Firewall', 'Edit')) {
                $return .= '<a href="'.route('admin::firewall::viewroles', ['id' => $data->id]).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Add IP</a>';
            }

            return $return;
        })->addIndexColumn()->rawColumns(['action', 'created_at'])->make(true);
    }

    public function show($id)
    {
        $page_title = 'User IP Whitelist';
        $user = User::with('firewalls')->withCount('firewalls')->where('id', $id)->first();

        return view('admin.admins.view_user_firewall', compact('page_title', 'user'));
    }
    public function show_roles($id)
    {
        $page_title = 'User IP Whitelist';
        $roles = \DB::table('roles')->where('id', $id)->first();
        $whitelistedips = Firewall::with('firewallips')->whereHas('firewallips', function($query) use($id){
            $query->where('role_id',$id);
        })->get();
        return view('admin.admins.view_user_roles_firewall', compact('page_title', 'roles','whitelistedips'));
    }

    public function store(AdminFirewall $request)
    {
        $firewalls = User::find($request->user_id)->firewalls();
        if ($firewalls->count() > 0) {
            $added = 0;
            foreach ($firewalls->get() as $firewall) {
                if ($firewall->ip_address == $request->ip_address) {
                    $added = 1;
                    break;
                }
            }
            if ($added) {
                return redirect()->route('admin::firewall::view', ['id' => $request->user_id])->withErrors(['ip_address' => 'IP already whiltelisted.'])->withInput();
            }
        }
        $ip = Firewall::where('ip_address', $request->ip_address)->first();
        if ($ip) {
            $firewalls->attach($ip->id);
        } else {
            $new = new Firewall();
            $new->ip_address = $request->ip_address;
            $new->save();
            $firewalls->attach($new->id);
        }

        return redirect()->route('admin::firewall::view', ['id' => $request->user_id])->with('message', 'IP whiltelisted.');
    }

    public function storeRoles(AdminFirewall $request)
    {
        $users = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.id',$request->user_id)->pluck('users.id')->toArray();
    
       
        $firewalls = FirewallUser::where('role_id',$request->user_id);
        if ($firewalls->count() > 0) {
            $added = 0;
            foreach ($firewalls->get() as $firewall) {
                if ($firewall->ip_address == $request->ip_address) {
                    $added = 1;
                    break;
                }
            }
            if ($added) {
                return redirect()->route('admin::firewall::view', ['id' => $request->user_id])->withErrors(['ip_address' => 'IP already whiltelisted.'])->withInput();
            }
        }
        $ip = Firewall::where('ip_address', $request->ip_address)->first();
        if ($ip) {
            $input = ['firewall_id'=>$ip->id,'role_id'=>$request->user_id];
        } else {
            $new = new Firewall();
            $new->ip_address = $request->ip_address;
            $new->save();
            $input = ['firewall_id'=>$new->id,'role_id'=>$request->user_id];
        }
        
        $fw = FirewallUser::firstOrCreate($input);

        return redirect()->route('admin::firewall::viewroles', ['id' => $request->user_id])->with('message', 'IP whiltelisted.');
    }

    public function destroy(AdminFirewall $request)
    {
        if(isset($request->roles_base) && $request->roles_base == 'true'){
            $firewalls = FirewallUser::where('role_id',$request->role_id)->where('firewall_id',$request->ip_id)->first();
            if ($firewalls->count() > 0) {
                $firewalls = $firewalls->delete();
                if($firewalls){
                    return redirect()->route('admin::firewall::viewroles', ['id' => $request->role_id])->with('message', 'Removed whiltelisted IP.');
                }
            }
        }
        $firewalls = User::find($request->user_id)->firewalls();
        if ($firewalls->count() > 0) {
            $added = 0;
            foreach ($firewalls->get() as $firewall) {
                if ($firewall->ip_address == $request->ip_address) {
                    $added = 1;
                    $firewalls->detach($firewall->id);
                    break;
                }
            }
            if ($added) {
                return redirect()->route('admin::firewall::view', ['id' => $request->user_id])->with('message', 'Removed whiltelisted IP.');
            }
        }
        return redirect()->route('admin::firewall::view', ['id' => $request->user_id])->withErrors(['ip_address' => "Doesn't match IP address."]);
    }
}
