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
use CollectionUserHelper;

class CollectionUserController extends Controller
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
        $result=CollectionUserHelper::indexCollection();
        return view('admin.collectionusers.index',$result);
    }

    public function create()
    {
        $result = CollectionUserHelper::createCollectionUsers();

        return view('admin.collectionusers.create',$result);
    }

    public function storeCreate(Requests\AdminCreateCollectionUserRequest $request)
    {
        try {
            DB::beginTransaction();
            $result = CollectionUserHelper::storeCreateCollectionUser($request);
            if($result['result'] != 'success') throw new Exception($result['result'],1);
            $request->session()->flash('message', 'New Collection User Created!');
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
            $result = CollectionUserHelper::deleteCollectionUsers($request,$id);
            if($result['result'] != 'success') throw new Exception($result['result'],1);
            $request->session()->flash('message', 'Collection User deleted!');
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function update(Requests\AdminUpdateCollectionUserRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            $result=CollectionUserHelper::updateCollectionUsers($request,$id);
            if($result['result'] != 'success') throw new Exception($result['result'],1);
            $request->session()->flash('message', 'Collection User Updated!');
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function rowData()
    {
       return CollectionUserHelper::rowDataCollectionUsers();
    }

    public function edit(Request $request, $id)
    {
        try {
            $result = CollectionUserHelper::editCollectionUsers($request, $id);
            return view('admin.collectionusers.create',$result);
            
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
}
