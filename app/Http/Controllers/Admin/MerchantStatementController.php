<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Merchant;
use App\MerchantStatement;
use App\MerchantUser;
use App\ParticipentPayment;
use App\PaymentInvestors;
use App\User;
use Carbon\Carbon;
use FFM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PDF;
use Exception;
use Yajra\DataTables\Html\Builder;
use MerchantStatementHelper;

class MerchantStatementController extends Controller
{
    public function index(Request $request, Builder $tableBuilder)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return \MTB::getAllStatementsMerchant($request->start_date, $request->end_date, $request->merchants);
        }
        $response=MerchantStatementHelper::getAllStatements();
        return view('admin.merchants.statements.generatedPdfManager',$response);
    }

    public function create()
    {
        $page_title = 'Generate PDF For Merchants';
        return view('admin.merchants.statements.statement_generation', compact('page_title'));
    }

    public function store(Request $request)
    {
        try {
            $return_result=MerchantStatementHelper::storeMerchantStatement($request);
            if($return_result['result']!='success') throw new \Exception($return_result['result'], 1);
            return response()->json(['status' => 1, 'msg' => $return_result['msg']]);
        } catch (Exception $e) {
            return response()->json(['status' => 0, 'msg' => $e->getMessage()]);
        }
    }
    public function show($id)
    {
        try {
           $return_result=MerchantStatementHelper::showStatement($id);
           if($return_result['result']!='success') throw new Exception($return_result['result'], 1);
           return response()->json(['status' => 1, 'msg' => $return_result['msg']]);
          
        } catch (\Exception $e) {
           return response()->json(['status' => 0, 'msg' => $e->getMessage()]);
        }

    }
    public function edit(MerchantStatement $merchantStatement)
    {
    }

    public function update(Request $request, MerchantStatement $merchantStatement)
    {
    }

    public function destroy(Request $request)
    {
        try {
            $return_result=MerchantStatementHelper::destroyStatement($request);
            if($return_result['result']!='success') throw new \Exception($return_result['result'], 1);
            $request->session()->flash('message', 'Statement Deleted Successfully!');
            return response()->json(['status' => 1,'msg'=>'success']);

        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'msg' => $e->getMessage()]);
        }
    }

    public function delete_statements(Request $request)
    {
        try
        {
              $return_result=MerchantStatementHelper::deleteStatement($request);
              if($return_result['result']!='success') throw new Exception($return_result['result'], 1);
              $request->session()->flash('message', 'Statement Delete Successfully!');
              return response()->json(['status' => 1, 'msg' => $msg]);

        } catch (\Exception $e) {
           return response()->json(['status' => 0, 'msg' => $e->getMessage()]);
        }
       
    }
}
