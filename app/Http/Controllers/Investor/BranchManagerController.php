<?php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Library\Interfaces\Controller\CRUDController;
use App\Library\Repository\Interfaces\IMarketPlaceRepository;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\Library\Repository\Interfaces\ISubStatusRepository;
use Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\Html\Builder;

class BranchManagerController extends Controller implements CRUDController
{
    public function __construct(ISubStatusRepository $subStatus, IRoleRepository $role, IMarketPlaceRepository $marketPlace)
    {
        $this->subStatus = $subStatus;
        $this->role = $role;
        $this->marketPlace = $marketPlace;
    }

    public function dashboard($value = '')
    {
        return view('branchmanager.index');
    }

    public function index(Builder $tableBuilder)
    {
        $page_title = 'Marketplace';
        $tableBuilder->ajax(route('branch::marketplace::data'));
        $tableBuilder = $tableBuilder->columns([['data' => 'id', 'name' => 'id', 'title' => 'ID'], ['data' => 'name', 'name' => 'name', 'title' => 'Name'], ['data' => 'funded', 'name' => 'funded', 'title' => 'Funded'], ['data' => 'pmnts', 'name' => 'pmnts', 'title' => 'Payments'], ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false]]);

        return view('branchmanager.marketplace.index', compact('page_title', 'tableBuilder'));
    }

    public function create()
    {
        $statuses = $this->subStatus->getAll()->pluck('name', 'id');
        $action = 'create';

        return view('branchmanager.marketplace.create', compact('statuses', 'action'));
    }

    public function storeCreate(Requests\CreateMarketPlaceRequest $request)
    {
        $marketplace = $this->marketPlace->create($request->only('name', 'id', 'sub_status_id', 'funded', 'rtr', 'commission', 'pmnts', 'max_participant_fund', 'participant_rtr', 'mgmnt_fee', 'syndication_fee', 'pmnt_amount', 'total_payment'));
        $request->session()->flash('message', 'New MarketPlace created!');

        return redirect()->route('branch::marketplace::edit', ['id' => $marketplace->id]);
    }

    public function update(Requests\CreateMarketPlaceRequest $request, $id)
    {
        if ($marketplace = $this->marketPlace->find($id)) {
            $marketplace->update($request->only('name', 'id', 'sub_status_id', 'funded', 'rtr', 'commission', 'pmnts', 'max_participant_fund', 'participant_rtr', 'mgmnt_fee', 'syndication_fee', 'pmnt_amount', 'total_payment'));
            $request->session()->flash('message', 'MarketPlace Updated!');
        }

        return redirect()->route('branch::marketplace::edit', ['id' => $marketplace->id]);
    }

    public function edit($id)
    {
        if ($marketplace = $this->marketPlace->find($id)) {
            $statuses = $this->subStatus->getAll()->pluck('name', 'id');
            $action = 'edit';

            return view('branchmanager.marketplace.create', compact('statuses', 'action', 'marketplace'));
        }
    }

    public function delete(Request $request, $id)
    {
        if ($this->marketPlace->delete($id)) {
            $request->session()->flash('message', 'Marketplace deleted');
        }

        return redirect()->route('branch::marketplace::index');
    }

    public function rowData()
    {
        $data = $this->marketPlace->datatable(['id', 'name', 'funded', 'pmnts', 'created_at', 'updated_at']);

        return \DataTables::of($data)->addColumn('action', function ($data) {
            return '<a href="'.route('branch::marketplace::edit', ['id' => $data->id]).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>'.Form::open(['route' => ['branch::marketplace::delete', 'id' => $data->id], 'method' => 'POST', 'onsubmit' => 'return confirm("Are you sure you want to delete?")']).Form::submit('Delete', ['class' => 'btn btn-xs btn-danger']).Form::close();
        })->editColumn('created_at', function ($data) {
            return \FFM::datetime($data->created_at);
        })->filterColumn('created_at', function ($query, $keyword) {
            $keyword = \FFM::dbdatetime($keyword);
            $sql = 'created_at  like ?';
            $query->whereRaw($sql, ["%{$keyword}%"]);
        })->editColumn('updated_at', function ($data) {
            return \FFM::datetime($data->updated_at);
        })->filterColumn('updated_at', function ($query, $keyword) {
            $keyword = \FFM::dbdatetime($keyword);
            $sql = 'updated_at  like ?';
            $query->whereRaw($sql, ["%{$keyword}%"]);
        })->make(true);
    }
}
