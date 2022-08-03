<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Library\Interfaces\Controller\CRUDController;
use App\Library\Repository\Interfaces\IMNotesRepository;
use App\Merchant;
use App\MNotes;
use Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\Html\Builder;
use NotesHelper;
use Illuminate\Support\Facades\DB;
use FFM;
use App\Http\Requests\AdminCreateNoteRequest;

class NotesController extends Controller implements CRUDController
{
    public function __construct(IMNotesRepository $mNotes)
    {
        $this->mNotes = $mNotes;
    }

    public function index(Builder $tableBuilder, $merchant_id)
    {
            $result=NotesHelper::indexNotes($tableBuilder,$merchant_id);
            return view('admin.notes.index',$result);
    }

    public function update_s(Request $request, $merchant_id = '')
    {   
            $result=NotesHelper::noteUpdate_s($request, $merchant_id);
            return view('admin.notes.create',$result);

    }

    public function create($merchant_id)
    {  
            $result=NotesHelper::createNotes($merchant_id);
            return view('admin.notes.create',$result);

    }        
    

    public function storeCreate(AdminCreateNoteRequest $request, $merchant_id)
    {
        try {
            DB::beginTransaction();
            $result=NotesHelper::notesStoreCreate($request, $merchant_id);
            if($result['result'] != 'success') throw new Exception($result['result'],1);
            $request->session()->flash('message', 'New Note created!');
            DB::commit();
            return redirect()->route('admin::notes::update', ['id' => $merchant_id]);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function update(AdminCreateNoteRequest $request, $merchant_id)
    {
        try {
            DB::beginTransaction();
            $result=NotesHelper::updateNotes($request,$merchant_id);
            if($result[result] != 'success') throw new Exception($result['result'],1);
            $request->session()->flash('message', 'notes Updated');
            DB::commit();
            return redirect()->route('admin::notes::update', ['id' => $merchant_id, 'nid' => $merchant_id]);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function edit($merchant_id)
    {
        try {
            $result=NotesHelper::editNotes($merchant_id);
            if ($result) {
                return view('admin.notes.create',$result);
            }
        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage());
        }
    }
    public function delete(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $result=NotesHelper::deleteNotes($request,$id);
            if($result['result'] != 'success') throw new Exception($result['result'],1);
            $request->session()->flash('message', 'notes deleted');
            DB::commit();
            return redirect()->route('admin::notes::lists', $id);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function rowData()
    {
        try {
            return NotesHelper::rowDataNotes();
        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage());
        }
    }


    public function addNotes(Request $request)
    {   
        return NotesHelper::addNoteMerchants($request);

    }

    public function merchantNotes(Request $request)
    {
        return NotesHelper::merchantNote($request);

    }
}
