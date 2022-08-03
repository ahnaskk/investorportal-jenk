<?php

namespace App\Helpers;
use App\Label;
use Illuminate\Support\Facades\DB;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\Library\Repository\Interfaces\IUserRepository;
use Permissions;
use Form;
use Yajra\DataTables\Html\Builder;
use App\User;

class BranchHelper 
{
    public function __construct(IRoleRepository $role, IUserRepository $user,Builder $tableBuilder)
    {
        $this->role = $role;
        $this->user = $user;
        $this->tableBuilder = $tableBuilder;
    }

    public function indexBranch(){
        
        $title = 'Branch Manager';
        $this->tableBuilder->ajax(route('admin::branch_managers::data'));
        $this->tableBuilder->parameters(['responsive' => true, 'autoWidth' => false, 'processing' => true, 'aaSorting' => [], 'pagingType' => 'input']);
        $tableBuilder = $this->tableBuilder->columns([['className' => 'details-control', 'data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'orderable' => false, 'defaultContent' => '', 'title' => 'No'], ['data' => 'name', 'name' => 'name', 'title' => 'Name'], ['data' => 'email', 'name' => 'email', 'title' => 'Email'], ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Created At'], ['data' => 'updated_at', 'name' => 'updated_at', 'title' => 'Updated At'], ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false]]);

        return ['title' => $title, 'tableBuilder' => $tableBuilder];

    }

    public function createBranch(){

        $branch_manager = [];
        $title = 'Create New Branch Manager';
        $action = 'Create';

        return ['branch_manager' => $branch_manager, 'title' => $title, 'action' =>$action];
    }

    public function storeCreateBranch($request){

        try{
            if (!$this->user->createBranchManager($request)) {
              throw new Exception("Something went wrong",1);
            }
            $return['result'] = 'success';

        }catch (\Exception $e) {
           $return['result'] = $e->getMessage();
        }
           return $return;
    }


    public function editBranch($request, $id){

        ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
            $userId = $request->user()->id;
            if ($branch_manager = $this->user->findBranchManager($id)) {
                $page_title = 'Edit Branch Manager';
                if (empty($permission)) {
                    $branchAccess = User::where('id', $id)->where('company', $userId)->first();
                }
                $action = 'edit';

                return ['page_title' => $page_title, 'branch_manager' => $branch_manager, 'action' =>$action];

    }
}

    public function updateBranch($request, $id){

        try{
            if (!$this->user->updateBranchManager($id, $request)) {
               throw new Exception("Something went wrong",1);
            }
            $return['result'] = 'success';

        }catch (\Exception $e) {
           $return['result'] = $e->getMessage();
        }
           return $return;

    }

    public function deleteBranch($request,$id){

        try{
            if ($this->user->deleteBranchManager($id)) {
                DB::table('user_has_roles')->where('model_id', $id)->delete();
            }
            if (!User::find($id)->delete()) {
                throw new Exception("Something went wrong",1);
            }
            $return['result'] = 'success';

        }catch (\Exception $e) {
           $return['result'] = $e->getMessage();
        }
           return $return;
    }

    public function rowDataBranch(){

        $data = $this->role->allBranchManager();
        $data = $data->toArray();

        return \DataTables::collection($data)->editColumn('created_at', function ($data) {
            return \FFM::datetime($data['created_at']);
        })->editColumn('updated_at', function ($data) {
            return \FFM::datetime($data['updated_at']);
        })->addColumn('action', function ($data) {
            $return = '';
            if (Permissions::isAllow('Branch Manager', 'Edit')) {
                $return .= '<a href="'.route('admin::branch_managers::edit', ['id' => $data['id']]).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>';
            }
            if (Permissions::isAllow('Branch Manager', 'Delete')) {
                $return .= Form::open(['route' => ['admin::branch_managers::delete', 'id' => $data['id']], 'method' => 'POST', 'onsubmit' => 'return confirm("Are you sure want to delete ?")']).Form::submit('Delete', ['class' => 'btn btn-xs btn-danger']).Form::close();
            }

            return $return;
        })->addIndexColumn()->make(true);
    }


}
