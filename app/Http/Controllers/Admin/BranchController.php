<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\Library\Repository\Interfaces\IUserRepository;
use App\User;
use Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Permissions;
use Yajra\DataTables\Html\Builder;
use BranchHelper;
use Exception;

class BranchController extends Controller
{
    protected $role;
    protected $user;

    public function __construct(IRoleRepository $role, IUserRepository $user)
    {
        $this->role = $role;
        $this->user = $user;
    }

    public function index(Builder $tableBuilder)
    {
        $result=BranchHelper::indexBranch();
        return view('admin.branch_managers.index',$result);
    }

    public function create()
    {
        $result = BranchHelper::createBranch();
        return view('admin.branch_managers.create', $result);
    }

    public function storeCreate(Requests\AdminCreateBranchManagerRequest $request)
    {
        try {
           DB::beginTransaction();
           $result = BranchHelper::storeCreateBranch($request);
           if($result['result'] != 'success') throw new Exception($result['result']);
           $request->session()->flash('message', 'New Branch Manager Created!');
           DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
           DB::rollback();
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function edit(Request $request, $id)
    {

        $result = BranchHelper::editBranch($request, $id);
        return view('admin.branch_managers.create', $result);
    }

    public function update(Requests\AdminUpdateBranchManagerRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            $result = BranchHelper::updateBranch($request,$id);
            if($result['result'] != 'success') throw new Exception($result['result']);
            $request->session()->flash('message', 'Branch Manager Updated!');
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function delete(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $result = BranchHelper::deleteBranch($request,$id);
            if($result['result'] != 'success') throw new Exception($result['result']);
            $request->session()->flash('message', 'Branch Manager deleted!');
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function rowData()
    {
        return BranchHelper::rowDataBranch();
    }
}
