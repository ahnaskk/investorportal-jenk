<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Library\Interfaces\Controller\CRUDController;
use App\Library\Repository\Interfaces\IMerchantBatchRepository;
use App\MbatchMarchant;
use App\Merchant;
use Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Permissions;
use Yajra\DataTables\Html\Builder;

class MerchantBatchesController extends Controller implements CRUDController
{
    public function __construct(IMerchantBatchRepository $batch)
    {
        $this->batch = $batch;
    }

    public function index(Builder $tableBuilder)
    {
        $page_title = 'Merchant Batches';
        $tableBuilder->ajax(route('admin::merchant_batches::data'));
        $tableBuilder = $tableBuilder->columns([['data' => 'DT_RowIndex', 'title' => '#', 'orderable' => false, 'searchable' => false], ['data' => 'name', 'name' => 'name', 'title' => 'Name'], ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false]]);
        $tableBuilder = $tableBuilder->parameters(['aaSorting' => [], 'columnDefs' => '[{orderable: false, targets: [0]}]', 'pagingType' => 'input']);

        return view('admin.merchant_batches.index', compact('page_title', 'tableBuilder'));
    }

    public function create()
    {
        $page_title = 'Create Batches';
        $action = 'create';
        $merchants = Merchant::orderBy('name')->where('active_status', 1)->pluck('name', 'id');
        $action = 'create';

        return view('admin.merchant_batches.create', compact('action', 'merchants', 'action', 'page_title'));
    }

    public function storeCreate(Requests\AdminUpdatebatchRequest $request)
    {
        try {
            $merchant_batches = $this->batch->createRequest($request);
            $request->session()->flash('message', 'New Merchant Batches Created!');

            return redirect()->route('admin::merchant_batches::edit', ['id' => $merchant_batches->id]);
        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function update(Request $request)
    {
        try {
            $batch = $this->batch->updateRequest($request);
            $request->session()->flash('message', 'Merchant Batches Updated!');

            return redirect()->back();
        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function edit($id)
    {
        $page_title = 'Update Batches';
        try {
            if ($batch = $this->batch->find($id)) {
                $selected_merchants = DB::table('mbatch_merchant')->where('mbatch_id', $id)->pluck('merchant_id');
                $selected_merchants = Merchant::whereIn('id', $selected_merchants)->select('name', 'id')->get();
                $action = 'edit';

                return view('admin.merchant_batches.create', compact('page_title', 'batch', 'action', 'selected_merchants', 'action'));
            }
        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function delete(Request $request, $id)
    {
        try {
            if ($this->batch->delete($id)) {
                $delete = MbatchMarchant::where('mbatch_id', $id)->delete();
            }
            {
                $request->session()->flash('message', 'Merchant Batches Deleted!');
            }

            return redirect()->route('admin::merchant_batches::index');
        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function rowData()
    {
        $data = $this->batch->datatable(['id', 'name']);

        return \DataTables::of($data)->addColumn('action', function ($data) {
            $return = '';
            if (Permissions::isAllow('Merchant Batches', 'Edit')) {
                $return .= '<a href="'.route('admin::merchant_batches::edit', ['id' => $data->id]).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>';
            }
            if (Permissions::isAllow('Merchant Batches', 'Delete')) {
                $return .= Form::open(['route' => ['admin::merchant_batches::delete', 'id' => $data->id], 'method' => 'POST', 'onsubmit' => 'return confirm("Are you sure want to delete ?")']).Form::submit('Delete', ['class' => 'btn btn-xs btn-danger']).Form::close();
            }

            return $return;
        })->addIndexColumn()->make(true);
    }
}
