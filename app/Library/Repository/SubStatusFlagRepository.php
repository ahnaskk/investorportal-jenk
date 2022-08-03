<?php

namespace App\Library\Repository;

use App\SubStatusFlag;
use App\Library\Repository\Interfaces\ISubStatusFlagRepository;
use Illuminate\Support\Facades\DB;
use Permissions;
use Form;
use Yajra\Datatables\Datatables;
use Yajra\DataTables\Html\Builder;
use App\Merchant;

class SubStatusFlagRepository implements ISubStatusFlagRepository
{
    public function __construct(Builder $tableBuilder)
    {
        $this->table = new SubStatusFlag();
        $this->tableBuilder = $tableBuilder;
    }

    public function getAll()
    {
        return $this->table->get();
    }

    public function index()
    {
        $this->tableBuilder->ajax(route('admin::sub_status_flag::data'));
        $this->tableBuilder = $this->tableBuilder->columns([['data' => 'rownum', 'name' => 'rownum', 'title' => '#', 'searchable' => false, 'orderable' => false], ['data' => 'name', 'name' => 'name', 'title' => 'Name'], ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false]]);
        $this->tableBuilder->parameters(['responsive' => true, 'autoWidth' => false, 'processing' => true, 'pagingType' => 'input']);
        return [ 'tableBuilder' => $this->tableBuilder ];
    }

    public function rowData($data)
    {
        return DataTables::of($data)->addColumn('action', function ($data) {
            $return = '';
            if (Permissions::isAllow('Settings Sub Status Flag', 'Edit')) {
                $return .= '<a href="'.route('admin::sub_status_flag::edit', ['id' => $data->id]).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>';
            }
            if (Permissions::isAllow('Settings Sub Status Flag', 'Delete')) {
                $return .= Form::open(['route' => ['admin::sub_status_flag::delete', 'id' => $data->id], 'method' => 'POST', 'onsubmit' => 'return confirm("Are you sure want to delete ?")']).Form::submit('Delete', ['class' => 'sub-bt btn btn-xs btn-danger']).Form::close();
            }

            return $return;
        })->make(true);
    }

   public function datatable($fields = null)
    {
        DB::statement(DB::raw('set @rownum=0'));
        $fields['rowrum'] = DB::raw('@rownum  := @rownum  + 1 AS rownum');
        if ($fields != null) {
            return $this->table->select($fields);
        }

        return $this->table;
    }

    public function createRequest($arr, $request = 0)
    {
        try {
            $sub_status_flag = $this->table->create(['name'=>$arr['name']]);
            return $sub_status_flag;
        } catch(\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
      
    }

    public function updateRequest($request)
    {
        try {
            $sub_status_flag = $this->table->find($request->id);
            $sub_status_flag->update($request->all());
            $sub_status_flag->name = $request->name;
            $sub_status_flag->save();
            return $sub_status_flag;
        } catch(\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $count=Merchant::where('sub_status_flag',$id)->count();

                if($count <= 0)
                {
                    if ($sub_status_flag = $this->find($id)) {
                        return $sub_status_flag->delete();
                    }
                }
            return false;
        } catch(\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function find($id)
    {
        return $this->table->find($id);
    }
}
