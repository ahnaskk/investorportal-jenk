<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Library\Repository\Interfaces\ISubStatusFlagRepository;
use Illuminate\Http\Request;

use Yajra\DataTables\Html\Builder;

class SubStatusFlagController extends Controller
{
    public function __construct(ISubStatusFlagRepository $sub_status_flag)
    {
        $this->sub_status_flag = $sub_status_flag;
    }

    public function index(Builder $tableBuilder)
    {
        $page_title = 'All Sub Status Flag';
        $returnData   = $this->sub_status_flag->index();
        $tableBuilder = $returnData['tableBuilder'];
        return view('admin.sub_status_flag.index', compact('page_title', 'tableBuilder'));
    }

    public function rowData()
    {
        try {
            $data = $this->sub_status_flag->datatable(['id', 'name']);
            return $this->sub_status_flag->rowData($data);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function delete(Request $request, $id)
    {
        try {
            if ($this->sub_status_flag->delete($id)) {
                $request->session()->flash('message', 'sub status flag deleted');
            } else {
                $request->session()->flash('error', 'Already refered'); 
            }
            return redirect()->route('admin::sub_status_flag::index');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function create()
    {
        $action = 'create';
        $page_title = 'Add Sub Status Flag';

        return view('admin.sub_status_flag.create', compact('page_title', 'action'));
    }

    public function storeCreate(Requests\AdminCreateSubStatusFlagRequest $request)
    {
        try {
            if($this->sub_status_flag->createRequest($request)){
                $request->session()->flash('message', 'New Sub status flag created.');
                return redirect()->route('admin::sub_status_flag::index');
            } else {
                return redirect()->back()->withErrors('Error, Please retry');
            }
            
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function update(Requests\AdminCreateSubStatusFlagRequest $request)
    {
        try {
            if($this->sub_status_flag->updateRequest($request)){
                $request->session()->flash('message', 'Sub status Flag Updated');
                return redirect()->route('admin::sub_status_flag::index');
            } else {
                return redirect()->back()->withErrors('Error, Please retry');
            }
            
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function edit($id,Request $request)
    {   
        $page_title = 'Edit Sub status flag';
        try {
            $Sub_status_flag= $this->sub_status_flag->find($id);
            if(!$Sub_status_flag){
                $request->session()->flash('error','Invalid Sub Status Flag Id');
                return redirect(route("admin::sub_status_flag::index"));
            }
            if ($sub_status_flag = $this->sub_status_flag->find($id)) {
                $action = 'edit';

                return view('admin.sub_status_flag.create', compact('page_title', 'sub_status_flag', 'action'));
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
}
