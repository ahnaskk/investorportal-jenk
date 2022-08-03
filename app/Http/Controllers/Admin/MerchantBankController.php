<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use App\Merchant;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use PayCalc;
use PDF;
use Permissions;
use Spatie\Permission\Models\Role;
use Stripe\Charge;
use Stripe\Stripe;
use Yajra\DataTables\Html\Builder;
use App\Helpers\MerchantBankHelper;

class MerchantBankController extends Controller
{

   public function bank_details_list($merchant_id, Builder $tableBuilder, Request $request)
    {
        $page_title = 'Bank Details List';
        if ($request->ajax() || $request->wantsJson()) {
            return $this->getBankAccounts($merchant_id);
        }
        $tableBuilder->ajax(['url' => route('admin::merchants::bank.data', ['merchant_id' => $merchant_id]), 'type' => 'post', 'data' => 'function(data) {
                data._token = "'.csrf_token().'";
            }']);
        $tableBuilder->parameters(['fnCreatedRow' => "function (nRow, aData, iDataIndex) {\n               var info = this.dataTable().api().page.info();\n               var page = info.page;\n               var length = info.length;\n               var index = (page * length + (iDataIndex + 1));\n               $('td:eq(0)', nRow).html(index).addClass('txt-center');\n           }", 'pagingType' => 'input']);
        $tableBuilder->parameters(['responsive' => true, 'autoWidth' => false, 'processing' => true, 'aaSorting' => [], 'pagingType' => 'input']);
        $tableBuilder = $tableBuilder->columns([['className' => 'details-control', 'orderable' => false, 'data' => 'id', 'name' => 'id', 'defaultContent' => '', 'title' => '#'], ['data' => 'account_holder_name', 'name' => 'account_holder_name', 'title' => 'Account Holder Name'], ['data' => 'bank_name', 'name' => 'bank_name', 'title' => 'Bank Name'], ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false]]);
        $merchant = Merchant::where('id', $merchant_id)->first();
        return view('admin.merchants.bank.bank_list', compact('page_title', 'tableBuilder', 'merchant_id', 'merchant'));
    }

    public function getBankAccounts($merchant_id)
    {
         return MerchantBankHelper::getAllBanks($merchant_id);
    }

    public function createBankAccount($merchant_id)
    {
        $result=MerchantBankHelper::createBank($merchant_id);
        return view('admin.merchants.bank.bank_update',$result);
    }
    public function editBankAccount($merchant_id, $id)
    {
        $result=MerchantBankHelper::editBank($merchant_id,$id);
        return view('admin.merchants.bank.bank_update', $result);
    }

    public function updateBankAccount($merchant_id, Request $request)
    {
        MerchantBankHelper::updateBank($request,$merchant_id);
        return redirect()->back();
    }
    
    public function deleteBankAccount(Request $request, $merchant_id, $id)
    {
         MerchantBankHelper::deleteBank($request,$merchant_id,$id);
         return redirect()->back();
    }

   
 }   