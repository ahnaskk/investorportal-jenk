<?php

namespace App\Helpers;
use App\Label;
use Illuminate\Support\Facades\DB;
use App\Library\Repository\LabelRepository;
use App\Library\Repository\Interfaces\ILabelRepository;
use Permissions;
use Form;


class LabelHelper 
{
    public function __construct(ILabelRepository $label)
    {
        $this->label = $label;
    }

    public function allLabels($tableBuilder){

        $page_title = 'All Label';
        $tableBuilder->ajax(route('admin::label::data'));
        $tableBuilder = $tableBuilder->columns([['data' => 'rownum', 'name' => 'rownum', 'title' => '#', 'searchable' => false, 'orderable' => false], ['data' => 'name', 'name' => 'name', 'title' => 'Name'], ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false]]);
        $tableBuilder->parameters(['responsive' => true, 'autoWidth' => false, 'processing' => true, 'pagingType' => 'input']);

        return ['page_title' => $page_title,'tableBuilder' => $tableBuilder];

    }

    public function rowLabel(){

        $data = $this->label->datatable(['id', 'name']);
        return \DataTables::of($data)
           ->addColumn('action', function ($data) {
           $return = '';
           if (Permissions::isAllow('Settings Label', 'Edit')) {
                    $return .= '<a href="'.route('admin::label::edit', ['id' => $data->id]).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>';
           }
           if (Permissions::isAllow('Settings Label', 'Delete')) {
                    $return .= Form::open(['route' => ['admin::label::delete', 'id' => $data->id], 'method' => 'POST', 'onsubmit' => 'return confirm("Are you sure want to delete ?")']).Form::submit('Delete', ['class' => 'sub-bt btn btn-xs btn-danger']).Form::close();
           }
            return $return;
            })
            ->make(true);
                
    }    

    public function deleteLabel($request, $id){

        try {
             if (!$this->label->delete($id)) {
             throw new \Exception("Something Went Wrong", 1);
             }
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result']=$e->getMessage();
          }
            return $return;

    }

    public function createLabel(){

        $action = 'create';
        $page_title = 'Add Label';
        return ['action' => $action,'page_title' => $page_title];
    }

    public function storeLabel($request){

        try {
             if(!$this->label->createRequest($request)){
               throw new Exception("Something went wrong",1);
             }
         $return['result']='success';
        } catch (\Exception $e) {
            $return['result']=$e->getMessage();
          }
          return $return;        
    }


    public function updateLabel($request){
        try{
            if(!$this->label->updateRequest($request)){
                throw new Exception("Something went wrong",1);
            }
            $return['result'] = 'success';
        } catch(\Exception $e){
            $return['result'] = $e->getMessage();
          }
          return $return;
    }

    public function editLabel($id,$request){

        $action = 'edit';
        $page_title = 'Edit Label';
        $label = $this->label->find($id);
        return ['action' => $action,'page_title' => $page_title,'label' => $label];
                
    }


}
