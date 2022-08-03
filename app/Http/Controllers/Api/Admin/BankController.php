<?php

namespace App\Http\Controllers\Api\Admin;

use App\Bank;
use App\BankDetails;
use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BankAccountRequest;
use App\Http\Resources\SuccessResource;
use Illuminate\Http\Request;

class BankController extends AdminAuthController
{
    public function getIndex(Request $request)
    {
        $accounts = BankDetails::where('status', 1)->get()->toArray();
        $accounts = collect($accounts)->map(function ($account) {
            $account['created_at'] = \FFM::datetime($account['created_at']);

            return $account;
        });

        return new SuccessResource(['data' => $accounts]);
    }

    public function postCreate(BankAccountRequest $request)
    {
        $input['bank_name'] = $request->input('name');
        $input['account_no'] = $request->input('acc_number');
        $bankAccount = BankDetails::create($input);
        $request->session()->flash('message', 'Bank Details Created Successfully');

        return new SuccessResource(['bank_account' => $bankAccount]);
    }

    public function postUpdate(BankAccountRequest $request, BankDetails $bankAccount)
    {
        $input['bank_name'] = $request->input('name');
        $input['account_no'] = $request->input('acc_number');
        $bankAccount->update($input);
        $request->session()->flash('message', 'Bank Details Updated Successfully');

        return new SuccessResource(['bank_account' => $bankAccount]);
    }

    public function postDelete(Request $request, BankDetails $bankAccount)
    {
        $bankAccount->update(['status' => 0]);
        $request->session()->flash('message', 'Bank Account Deleted Successfully!');

        return new SuccessResource(['message' => 'Bank Account Deleted Successfully']);
    }

    public function postDeleteBank(Request $request, Bank $bank)
    {
        $bank->delete();
        $request->session()->flash('message', 'Bank Account Deleted Successfully!');

        return new SuccessResource(['message' => 'Bank Account Deleted Successfully']);
    }
}
