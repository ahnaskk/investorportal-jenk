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


class CollectionUserHelper 
{
    public function __construct(IRoleRepository $role, IUserRepository $user,Builder $tableBuilder)
    {
        $this->role = $role;
        $this->user = $user;
        $this->tableBuilder =$tableBuilder;
    }

    public function indexCollection(){

        $page_title = 'All Collection User';
        $this->tableBuilder->ajax(route('admin::collection_users::data'));
        $this->tableBuilder->parameters(['responsive' => true, 'autoWidth' => false, 'processing' => true, 'aaSorting' => [], 'pagingType' => 'input']);
        $tableBuilder = $this->tableBuilder->columns([['className' => 'details-control', 'data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'defaultContent' => '', 'title' => '#'], ['data' => 'name', 'name' => 'name', 'title' => 'Name'], ['data' => 'email', 'name' => 'email', 'title' => 'Email'], ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Created At'], ['data' => 'updated_at', 'name' => 'updated_at', 'title' => 'Updated At'], ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false]]);

        return ['page_title' => $page_title,'tableBuilder' => $tableBuilder];
    }

    public function createCollectionUsers(){

        $page_title = 'Create Collection User';
        $action = 'Create';

        return ['page_title' => $page_title, 'action' => $action];
    }

    public function storeCreateCollectionUser($request){

      try {
        if (!$this->user->createCollectionUser($request)) {
            throw new Exception("Something went wrong",1);
                
            }
            $return['result'] = 'success';
          
      } catch (Exception $e) {
          $return['result'] = $e->getMessage();
      }
        return $return;
    }


    public function deleteCollectionUsers($request,$id){

      try {
        if ($this->user->deleteCollectionUser($id)) {
            DB::table('user_has_roles')->where('model_id', $id)->delete();
        }
        else{
            throw new Exception("Something went wrong",1);
        }
        if (!User::find($id)->delete()) {
            throw new Exception("Something went wrong",1);
        }
        $return['result'] = 'success';
      } catch(Exception $e){
        $return['result'] = $e->getMessage();
      } 
      return $return;     
    }

    public function updateCollectionUsers($request, $id){

      try{
         if (!$this->user->updateCollectionUser($id, $request)) {
           throw new Exception("Something went wrong",1);
          }
          $return['result'] = 'success';
      } catch(Exception $e){
            $return['result'] = $e->getMessage();
          }
     return $return; 
    }

    public function editCollectionUsers($request, $id){

       ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
            $userId = $request->user()->id;
            if ($collection_user = $this->user->findCollectonUser($id)) {
                $page_title = 'Edit Collection User';
                if (empty($permission)) {
                    $branchAccess = User::where('id', $id)->where('company', $userId)->first();
                    if (empty($branchAccess)) {
                        return view('admin.permission_denied');
                    } else {
                    }
                }
                $action = 'edit'; 
            }
      return ['page_title' =>$page_title, 'collection_user' => $collection_user, 'action' => $action];          
    }

    public function rowDataCollectionUsers(){

        session_set('all_users', $users = User::withTrashed()->select('id', 'name')->get()->getDictionary());
        return \DataTables::collection($this->role->allCollectionUser())->editColumn('created_at', function ($data) {
            $created_date = 'Created On '.\FFM::datetime($data['created_at']).' by '.get_user_name_with_session($data['creator_id']);

            return "<a title='$created_date'>".\FFM::datetime($data['created_at']).'</a>';
        })->editColumn('updated_at', function ($data) {
            return \FFM::datetime($data['updated_at']);
        })->addColumn('action', function ($data) {
            $return = '';
            if (Permissions::isAllow('Collection Users', 'Edit')) {
                $return .= '<a href="'.route('admin::collection_users::edit', ['id' => $data['id']]).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>';
            }
            if (Permissions::isAllow('Collection Users', 'Delete')) {
                $return .= Form::open(['route' => ['admin::collection_users::delete', 'id' => $data['id']], 'method' => 'POST', 'onsubmit' => 'return confirm("Are you sure want to delete ?")']).Form::submit('Delete', ['class' => 'btn btn-xs btn-danger']).Form::close();
            }

            return $return;
        })->addIndexColumn()->rawColumns(['created_at', 'updated_at', 'action'])->make(true);
    }


}
