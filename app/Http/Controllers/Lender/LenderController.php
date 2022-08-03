<?php

namespace App\Http\Controllers\Lender;

use App\Http\Controllers\Controller;
use App\Library\Interfaces\Controller\CRUDController;
use App\Library\Repository\Interfaces\IMarketPlaceRepository;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\Library\Repository\Interfaces\ISubStatusRepository;
use Form;

class LenderController extends Controller implements CRUDController
{
    public function __construct(ISubStatusRepository $subStatus, IRoleRepository $role, IMarketPlaceRepository $marketPlace)
    {
        $this->subStatus = $subStatus;
        $this->role = $role;
        $this->marketPlace = $marketPlace;
    }

    public function dashboard($value = '')
    {
        return view('lender.index');
    }

    public function rowData()
    {
        $data = $this->marketPlace->datatable(['id', 'name', 'funded', 'pmnts', 'created_at', 'updated_at']);

        return \DataTables::of($data)->addColumn('action', function ($data) {
            return '<a href="'.route('branch::marketplace::edit', ['id' => $data->id]).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>'.Form::open(['route' => ['branch::marketplace::delete', 'id' => $data->id], 'method' => 'POST', 'onsubmit' => 'return confirm("Are you sure want to delete ?")']).Form::submit('Delete', ['class' => 'btn btn-xs btn-danger']).Form::close();
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
