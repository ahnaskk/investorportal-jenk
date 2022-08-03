<?php

namespace App\Helpers;

use App\MerchantUser;
use App\ParticipentPayment;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Merchant;
use App\MerchantBankAccount;
use Yajra\DataTables\Html\Builder;
use Permissions;
use Form;
use FFM;


class MerchantBankHelper
{

	 public static function createBank($merchant_id)
	 {
	 	$page_title = 'Bank Details';
        $action = 'create';
        $merchant = Merchant::where('id', $merchant_id)->first();
        return ['page_title'=>$page_title,'merchant_id'=>$merchant_id,'action'=>$action,'merchant'=>$merchant];

	 }

     public static function editBank($merchant_id,$id)
     {
        $bank_details = MerchantBankAccount::where('id', $id)->first();
        if (strlen($bank_details->account_number) >= 4) {
            $masked_accountNo = FFM::mask_cc($bank_details->account_number);
        } else {
            $masked_accountNo = $bank_details->account_number;
        }
        $page_title = 'Bank Details';
        $action = 'Edit';
        $merchant = Merchant::where('id', $merchant_id)->first();

        return ['page_title'=>$page_title,'bank_details'=>$bank_details,'action'=>$action,'id'=>$id,'merchant_id'=>$merchant_id,'merchant'=>$merchant,'masked_accountNo'=>$masked_accountNo];

     }

	 public static function updateBank($request,$merchant_id)
	 {
         $BankModel = new MerchantBankAccount;
        try {
            $input_type = $request->type;
            $default_credit = $request->default_credit;
            $default_debit = $request->default_debit;
            $type = '';
            if (isset($request->type)) {
                $type = implode(',', $request->type);
            }
            if ($request->id) {
                if (! empty($request->account_number)) {
                    $params = ['account_number' => $request->account_number, 'routing_number' => $request->routing_number, 'bank_name' => $request->bank_name, 'account_holder_name' => $request->account_holder_name, 'default_credit' => $default_credit, 'default_debit' => $default_debit, 'type' => $type, 'merchant_id' => $request->merchant_id];
                } else {
                    $params = ['routing_number' => $request->routing_number, 'bank_name' => $request->bank_name, 'account_holder_name' => $request->account_holder_name, 'default_credit' => $default_credit, 'default_debit' => $default_debit, 'type' => $type, 'merchant_id' => $request->merchant_id];
                }
                $return_function = $BankModel->selfUpdate($params, $request->id);
                if ($return_function['result'] != 'success') {
                    throw new \Exception($return_function['result'], 1);
                }
                $msg = 'Bank Details Updated Successfully';
            } else {
                $params = ['account_number' => $request->account_number, 'routing_number' => $request->routing_number, 'bank_name' => $request->bank_name, 'account_holder_name' => $request->account_holder_name, 'merchant_id' => $request->merchant_id, 'default_credit' => $default_credit, 'default_debit' => $default_debit, 'type' => $type];
                $return_function = $BankModel->selfCreate($params);
                if ($return_function['result'] != 'success') {
                    throw new \Exception($return_function['result'], 1);
                }
                $msg = 'Bank Details Created Successfully';
            }
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();

            return redirect()->back()->withInput()->withErrors($e->getMessage());
        }
        $request->session()->flash('message', $msg);

       return 1;



	 }

     public static function getAllBanks($merchant_id)
     {

        $data = MerchantBankAccount::select('account_holder_name', 'bank_name', 'merchant_bank_accounts.id', 'merchant_bank_accounts.merchant_id', 'merchants.name as merchant_name')->where('merchant_bank_accounts.merchant_id', $merchant_id)->join('merchants', 'merchants.id', 'merchant_bank_accounts.merchant_id');

        return \DataTables::of($data)->addColumn('action', function ($data) {
            $edit = $del = '';
            if (Permissions::isAllow('Bank', 'Edit')) {
                $edit = '<a href="'.route('admin::merchants::bank.edit', ['merchant_id' => $data->merchant_id, 'id' => $data->id]).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>';
            }
            if (Permissions::isAllow('Bank', 'Delete')) {
                $del = Form::open(['route' => ['admin::merchants::bank.delete', 'merchant_id' => $data->merchant_id, 'id' => $data->id], 'method' => 'POST', 'onsubmit' => 'return confirm("Are you sure want to delete ?")']).Form::submit('Delete', ['class' => 'btn btn-xs btn-danger']).Form::close();
            }

            return $edit.$del;
        })->addIndexColumn()->make(true);

     }

     public static function deleteBank($request,$merchant_id,$id)
     {
        $delete = MerchantBankAccount::find($id)->delete();
        if ($delete) {
            $merchant = Merchant::select('id', 'ach_pull')->where('id', $merchant_id)->first();
            if ($merchant->ach_pull) {
                $bank_account_count = $merchant->bankAccountDebit()->count();
                if ($bank_account_count == 0){
                    $merchant->ach_pull = 0;
                    $merchant->save();
                }
            }
            $request->session()->flash('message', 'Bank Account Deleted Successfully');
        } else {
            $request->session()->flash('error', 'Bank Account Deletion Failed');
        }


        return 1;

     }


}
