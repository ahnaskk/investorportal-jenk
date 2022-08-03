<?php
namespace App\Library\Helpers;
use Carbon\Carbon;
use DataTables;
use Permissions;
use Form;
use App\Bank;
use App\BankDetails;
use Yajra\DataTables\Html\Builder;
class BankHelper
{
    public function BankDetails($id)
    {
        return BankDetails::find($id);
    }
    public function getList($data)
    {
        $List = Bank::with('User');
        if(isset($data['investor_id'])){
            if($data['investor_id']){
                $List = $List->where('investor_id', $data['investor_id']);
            }
        }
        return $List;
    }
    public function Datatable($data)
    {
        return DataTables::of($data)
        ->addColumn('action', function ($data) {
            $return = '';
            if (Permissions::isAllow('Bank', 'Edit')) {
                $return .= '<a href="'.route('admin::investors::bank', ['id' => $data->id]).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>';
            }
            if (Permissions::isAllow('Bank', 'Delete')) {
                $return .= Form::open(['route' => ['admin::delete_bank_details', 'id' => $data->id], 'method' => 'POST', 'onsubmit' => 'return confirm("Are you sure want to delete ?")']).Form::submit('Delete', ['class' => 'btn btn-xs btn-danger']).Form::close();
            }
            return $return;
        })
        ->addColumn('bank_name', function ($data) {
            return $data->name;
        })
        ->editColumn('default_credit', function ($data) {
            return $data->default_credit ? 'Yes' : 'No';
        })
        ->editColumn('default_debit', function ($data) {
            return $data->default_debit ? 'Yes' : 'No';
        })
        ->addColumn('investor_name', function ($data) {
            return $data->User->name;
        })
        ->addIndexColumn()
        ->make(true);
    }
    public function Delete($id)
    {
        try {
            $SelfModel = new Bank;
            $return_function = $SelfModel->selfDelete($id);
            if ($return_function['result'] != 'success') {
                throw new Exception($return_function['result'], 1);
            }
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }
        return $return;        
    }
}
