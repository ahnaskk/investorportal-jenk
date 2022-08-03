<?php
/**
 * Created by SREEJ32H.
 * User: iocod
 * Date: 5/11/17
 * Time: 12:14 AM.
 */

namespace App\Library\Repository;

use App\Library\Repository\Interfaces\ISubStatusRepository;
use App\SubStatus;
use App\Merchant;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Builder;
use Yajra\Datatables\Datatables;
use Permissions;
use Form;


class SubStatusRepository implements ISubStatusRepository
{
    public function __construct(Builder $tableBuilder)
    {
        $this->table = new SubStatus();
        $this->tableBuilder = $tableBuilder;
    }

    public function index()
    {
        $this->tableBuilder->ajax(route('admin::sub_status::data'));
        $this->tableBuilder = $this->tableBuilder->columns([['data' => 'rownum', 'name' => 'rownum', 'title' => '#', 'searchable' => false], ['data' => 'name', 'name' => 'name', 'title' => 'Name'], ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false]]);
        $this->tableBuilder->parameters(['responsive' => true, 'autoWidth' => false, 'processing' => true, 'pagingType' => 'input']);
        return [ 'tableBuilder' => $this->tableBuilder ];
    }

    public function rowData($data)
    {
        try{
            return DataTables::of($data)->addColumn('action', function ($data) {
                $return = '';
                if (Permissions::isAllow('Settings Sub Status', 'Edit')) {
                    $return .= '<a href="'.route('admin::sub_status::edit', ['id' => $data->id]).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>';
                }
                if (Permissions::isAllow('Settings Sub Status', 'Delete')) {
                    $return .= Form::open(['route' => ['admin::sub_status::delete', 'id' => $data->id], 'method' => 'POST', 'onsubmit' => 'return confirm("Are you sure want to delete ?")']).Form::submit('Delete', ['class' => 'sub-bt btn btn-xs btn-danger']).Form::close();
                }
                return $return;
            })->make(true);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
    public function getAll()
    {
        return $this->table->orderBy('name')->get();
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

    public function find($id)
    {
        return $this->table->find($id);
    }

    public function delete($id)
    {
        try{
            $count = Merchant::where('sub_status_id', $id)->count();
            if ($count <= 0) {
                if ($merchant = $this->find($id)) {
                    return $merchant->delete();
                }
            }
            return false;
        } catch (\Exception $e){
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function createRequest($arr, $request = 0)
    {
        $SubStatus = $this->table->create(['name'=>$arr['name']]);
        // $investor = \App\User::find($request->user_id);
        // $SubStatus->name = $arr['name'];
        // $SubStatus->id = $arr['id'];
        // $SubStatus->save();

        return $SubStatus;
    }

    public function updateRequest($request)
    {
        $SubStatus = $this->table->find($request->id);
        $SubStatus->update($request->all());
        $SubStatus->name = $request->name;
        $SubStatus->save();

        return $SubStatus;
    }
}
