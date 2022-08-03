<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Library\Interfaces\Controller\CRUDController;
use App\Library\Repository\Interfaces\ISubStatusRepository;
use Illuminate\Http\Request;
use Yajra\DataTables\Html\Builder;

class StatusController extends Controller implements CRUDController
{
    public function __construct(ISubStatusRepository $subStatus)
    {
        $this->subStatus = $subStatus;
    }

    public function index(Builder $tableBuilder)
    {
        $page_title = 'All Status';
        $returnData   = $this->subStatus->index();
        $tableBuilder = $returnData['tableBuilder'];

        return view('admin.sub_status.index', compact('page_title', 'tableBuilder'));
    }

    public function create()
    {
        $action = 'create';
        $page_title = 'Add Status';

        return view('admin.sub_status.create', compact('page_title', 'action'));
    }

    public function storeCreate(Requests\AdminCreateSubStatusRequest $request)
    {
        try {
            $sub_status = $this->subStatus->createRequest($request);
            $request->session()->flash('message', 'New status created!');

            return redirect()->route('admin::sub_status::index');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function update(Requests\AdminUpdateSubStatusRequest $request)
    {
        try {
            $subStatus = $this->subStatus->updateRequest($request);
            $request->session()->flash('message', 'Status Updated');

            return redirect()->route('admin::sub_status::index');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function edit($id,Request $request)
    {
        $page_title = 'Edit Status';
        try {
            $Sub_status = $this->subStatus->find($id);
            if(!$Sub_status){
                $request->session()->flash('error','Invalid Status Id');
                return redirect(route('admin::sub_status::index'));
            }
            if ($subStatus = $this->subStatus->find($id)) {
                $action = 'edit';

                return view('admin.sub_status.create', compact('page_title', 'subStatus', 'action'));
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function delete(Request $request, $id)
    {
        try {
            if ($this->subStatus->delete($id)) {
                $request->session()->flash('message', 'Status deleted');
                return redirect()->route('admin::sub_status::index');
            } else {
                return redirect()->to('admin/sub_status/')->withErrors('Can not delete status successfully ,already referred !');
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function rowData()
    {
        $data = $this->subStatus->datatable(['id', 'name']);
        return $this->subStatus->rowData($data);
    }
}
