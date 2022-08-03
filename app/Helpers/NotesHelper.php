<?php

namespace App\Helpers;
use App\MNotes;
use Illuminate\Support\Facades\DB;
use App\Library\Repository\MNotesRepository;
use App\Library\Repository\Interfaces\IMNotesRepository;
use Permissions;
use Form;
use App\Merchant;
use Yajra\DataTables\Html\Builder;
use FFM;

class NotesHelper 
{
    public function __construct(IMNotesRepository $mNotes)
    {
        $this->mNotes = $mNotes;
    }

    public function indexNotes($tableBuilder,$merchant_id){

        $page_title = 'notes';
        $tableBuilder->ajax(route('admin::notes::data', $merchant_id));
        $tableBuilder = $tableBuilder->columns([['data' => 'id', 'name' => 'id', 'title' => 'Id'], ['data' => 'note', 'name' => 'note', 'title' => 'Note'], ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false]]);

        return ['page_title' => $page_title, 'tableBuilder' => $tableBuilder];        
    }

    public function noteUpdate_s($request,$merchant_id){
        
        ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = $request->user()->id;
        $action = 'create';
        $mNotes = MNotes::where('merchant_id', $merchant_id)->first();
        $mnotes_count = MNotes::where('merchant_id', $merchant_id)->count();
        $merchant = Merchant::where('id', $merchant_id)->first();
        $mNote = MNotes::where('merchant_id', $merchant_id)->orderByDesc('created_at')->get()->toArray();
        if ($mNotes) {
            $action = 'edit';
        }

        return ['mNotes' => $mNotes, 'merchant_id' => $merchant_id, 'action' =>$action, 'mNote' => $mNote, 'mnotes_count' => $mnotes_count, 'merchant' => $merchant];        
    }

    public function createNotes($merchant_id){

        $action = 'create';
        $mNotes = MNotes::where('merchant_id', $merchant_id)->orderByDesc('created_at')->get()->toArray();

        return ['action' => $action ,'mNotes' => $mNotes];
    }

    public function notesStoreCreate($request, $merchant_id){
        try{
            $request_var = $request->all();
            $request_var['merchant_id'] = $merchant_id;
            $request_var['added_by'] = $request->user()->name;
            if(!$this->mNotes->createRequest($request_var)){
             throw new Exception("Something went wrong",1);
             }
            $return['result'] = 'success';
        } catch(Exception $e){
            $return['result'] = $e->getMessage();
        }
        return $return;
    }


    public function updateNotes($request, $merchant_id){
       try{
          if(!$this->mNotes->updateRequest($request)){
            throw new Exception("Something went wrong",1);
          }
          $return['result'] = 'success';
       } catch(Exception $e){
         $return['result'] = $e->getMessage();
       }
       return $return;
    }

    public function editNotes($merchant_id){

        $action = 'edit';
        $merchant_id= $merchant_id;
        $mNotes = $this->mNotes->find($merchant_id);

        return['action' =>$action,'mNotes' => $mNotes,'merchant_id' => $merchant_id];
    }

    public function deleteNotes($request, $id){
       try{
          if (!$this->mNotes->delete($id)) {
            throw new Exception("Something went wrong",1);
          }
          $return['result'] = 'success';
       } catch(Exception $e){
          $return['result'] = $e->getMessage();
         }
      return $return;
      
    }

    public function rowDataNotes(){

      $data = $this->mNotes->datatable(['id', 'note']);

      return \DataTables::of($data)
      ->addColumn('action', function ($data) {
      return '<a href="'.route('admin::notes::edit', ['id' => $data->id]).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>'.Form::open(['route' => ['admin::notes::delete', 'id' => $data->id], 'method' => 'POST', 'onsubmit' => 'return confirm("Are you sure want to delete ?")']).Form::submit('Delete', ['class' => 'btn btn-xs btn-danger']).Form::close();
            })
      ->make(true);        
    }

    public function addNoteMerchants($request){

        $merchantId = $request->merchant_id;
        $note = $request->note;
        if ($merchantId && $note) {
            $note1 = ['merchant_id' => $merchantId, 'note' => $note, 'added_by' => $request->user()->name];
            $add = $this->mNotes->createRequest($note1);
            if ($add) {
                return response()->json(['status' => 1, 'note' => $add->note, 'created_at' => FFM::datetime($add->created_at), 'added_by' => $request->user()->name]);
            }
        } else {
            return response()->json(['status' => 0]);
        }
    }

    public function merchantNote($request){

        $merchantId = $request->merchant_id;
        if ($merchantId) {
            $merchant_name = Merchant::where('id', $merchantId)->value('name');
            $notes = MNotes::select('m_notes.note', 'm_notes.created_at', 'm_notes.added_by')->where('merchant_id', $merchantId)->orderBy('m_notes.created_at', 'desc')->get()->map(function ($note) {
                return ['note' => $note['note'], 'created_at' => FFM::datetime($note['created_at']), 'added_by' => $note['added_by']];
            })->toArray();
            if (! empty($notes)) {
                return response()->json(['status' => 1, 'result' => $notes, 'merchant_name' => $merchant_name, 'merchant_id' => $merchantId]);
            } else {
                return response()->json(['status' => 0, 'msg' => '', 'merchant_name' => $merchant_name, 'merchant_id' => $merchantId]);
            }
        } else {
            return response()->json(['status' => 0]);
        }
    }


}

