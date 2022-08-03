<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Traits\CreditCardStripe;
use App\Http\Controllers\Controller;
use App\Http\Requests\GeneratePaymentRequest;
use App\Http\Requests\LenderPayment;
use App\Http\Requests\PaymentCheckPaymentRequest;
use App\Http\Requests\StorePaymentRequest;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use MTB;
use PaymentHelper;
use Yajra\DataTables\Html\Builder;

class PaymentController extends Controller
{
    use CreditCardStripe;
 
    public function __construct()
    {}

    /**
     * Display a listing of the Open items in payments.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function openItems(Request $request, Builder $tableBuilder)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return MTB::getOpenItemsForAdmin();
        }
        $result = PaymentHelper::openItems($tableBuilder);

        return view('admin.payments.open-items', $result);
    }

    /**
     * Calculate net payment for lender payment generation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function netPaymentAll(Request $request)
    {
        $data = $request->all();
        $net_payment = 0;
        if (isset($data['data'])) {
            $list = $data['data'];
        } else {
            $list[] = [
                'merchant_id' => $data['merchant_id'],
                'rate'        => $data['rate'],
                'length'      => $data['length'],
            ];
        }
        if (!empty($list)) {
            $result = PaymentHelper::netPaymentAll($list);
            return response()->json(['status' => 1, 'result' => $result]);
        } else {
            return response()->json(['status' => 0]);
        }
    }

    /**
     * Calculate withpoiut net payment for lender payment generation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function netPayment(Request $request)
    {
        $data = $request->all();
        $list = [];
        if (isset($data['data'])) {
            $list = $data['data'];
        } else {
            $list[] = [
                'merchant_id' => $data['merchant_id'],
                'rate'        => $data['rate'],
                'length'      => $data['length'],
            ];
        }
        if ($list) {
            $net_payment = PaymentHelper::netPayment($list);
            return response()->json(['status' => 1, 'net_payment' => $net_payment]);
        }
    }

    /**
     * DebitPaymentLimit function.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function debitPaymentLimit(Request $request)
    {
        $result = PaymentHelper::debitPaymentLimit($request);

        return response()->json(['status' => 1, 'msg' => $result]);
    }

    /**
     * Lender payment check function.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function lenderPaymentCheck(Request $request)
    {
        $result = PaymentHelper::lenderPaymentCheck($request);

        return response()->json($result);
    }

    /**
     * Payment check function.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function paymentCheck(PaymentCheckPaymentRequest $request)
    {
        $validatedData = $request->validated();
        $result = PaymentHelper::paymentCheck($request);

        return response()->json($result);
    }

    /**
     * Create payment for merchant function.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $tableBuilder
     * @param  $merchant_id
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, Builder $tableBuilder, $merchant_id = 0)
    {
        $result = PaymentHelper::create($request, $tableBuilder, $merchant_id);
        if ($result['error']) {
            if ($result['permission_error']) {
                return view('admin.permission_denied');
            }
            return redirect()->back()->with('error', $result['message']);
        }
        return view('admin.payments.create', $result['result']);
    }

    /**
     * Share check data for datatable in create payment for merchant.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function shareCheck(Request $request)
    {
        $result = PaymentHelper::shareCheck($request);

        return $result;
    }

    /**
     * Share check data for datatable in create payment for merchant.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function NetAmountCalculation(Request $request)
    {
        $result = PaymentHelper::NetAmountCalculation($request);

        return response()->json(['pay' => $result['payment'], 'payment' => round($result['payment'] * $result['max_participant_fund_per'], 2), 'net_amount' => round($result['net_amount'], 2)]);
    }

    /**
     * Store payment for merchant function.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePaymentRequest $request)
    {
        try {
            $validatedData = $request->validated();
            DB::beginTransaction();
            $result = PaymentHelper::store($request);
            $message = $result['message'];
            if (!$result['status']) {
                throw new Exception($message ?? 'Something Went Wrong', 1);
            }
            $result = $result['result'];
            DB::commit();
            return redirect()->route('admin::merchants::view', $result)->with('message', $message);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors($e->getMessage())->withInput();
        }
    }

    /**
     * Regenerate payment for merchant function.
     *
     * @param  $participent_payment_id
     * @param  $type
     * @return \Illuminate\Http\Response
     */
    public function reGeneratePayment($participent_payment_id, $type)
    {
        try {
            DB::beginTransaction();
            $result = PaymentHelper::reGeneratePayment($participent_payment_id, $type);
            $message = $result['message'];
            if (!$result['status']) {
                throw new Exception($message ?? 'Something Went Wrong', 1);
            }
            DB::commit();
            $return['result'] = 'success';

            return redirect()->back()->with('message', $message);
        } catch (Exception $e) {
            $message = $e->getMessage();
            DB::rollback();

            return redirect()->back()->withErrors($message);
        }
    }

    /**
     * Revert payment for merchant function.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function RevertPayment(Request $request)
    {
        try {
            DB::beginTransaction();
            $return = PaymentHelper::RevertPayment($request);
            $message = $return['message'];
            if (!$return['status']) {
                throw new Exception($message ?? 'Something Went Wrong', 1);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $return['status'] = 0;
            $return['message'] = $e->getMessage();
        }

        return response()->json($return);
    }

    /**
     * Delete payment for merchant function.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $result = PaymentHelper::delete($request, $id);
            $message = $result['message'];
            if (!$result['status']) {
                throw new Exception($message ?? 'Something Went Wrong', 1);
            }
            $request->session()->flash('message', 'Payment Deleted!');
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();

            return redirect()->back()->withErrors($e->getMessage());
        }

        return redirect()->back();
    }

    /**
     * Delete multiple payment for merchant function.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function delete_multi_payment(Request $request)
    {
        try {
            DB::beginTransaction();
            $result = PaymentHelper::delete_multi_payment($request);
            $message = $result['message'];
            if (!$result['status']) {
                throw new Exception($message ?? 'Something Went Wrong', 1);
            }
            DB::commit();
            $request->session()->flash('message', $message);

            return response()->json(['status' => 1, 'msg' => $message]);
        } catch (Exception $ex) {
            DB::rollback();

            return response()->json(['status' => 0, 'msg' => $ex->getMessage()]);
        }
    }

    /**
     * Lender payment generation function.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $tableBuilder
     * @return \Illuminate\Http\Response
     */
    public function lender_payment_generation(Builder $tableBuilder, LenderPayment $request)
    {
        try {
            DB::beginTransaction();
            $result = PaymentHelper::lender_payment_generation($tableBuilder, $request);
            $message = $result['message'];
            if (!$result['status']) {
                throw new Exception($message ?? 'Something Went Wrong', 1);
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors($e->getMessage());
        }
        return view('admin.payments.lender_payment_generation', $result['result']);
    }

    /**
     * Lender payment generate batch function.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $tableBuilder
     * @return \Illuminate\Http\Response
     */
    public function  manage_payments_for_lenders(Request $request)
    {
        $data=$request->all();
        $single['_token']   = $data['_token'];
        $single['company']  = $data['company'];
        $list=[];
        foreach ($data['merchant'] as $merchant_id => $value) {
            $single['merchant']=[];
            if(isset($value['select_merchant'])){
                $single['name']                   = $value['name'];
                $single['merchant_id']            = $merchant_id;
                $single['merchant'][$merchant_id] = $value;
                $list[]=$single;   
            }
        }
        return response()->json(['list' => $list], 200);
    }

    /**
     * Lender payment generate function.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $tableBuilder
     * @return \Illuminate\Http\Response
     */
    public function add_payments_for_lenders(Request $request)
    {
        try {
            Session::put('lender_base_payment',true);
            $result = PaymentHelper::add_payments_for_lenders($request);
            $message = $result['message'];
            if (!$result['status']) {
                throw new Exception($message ?? 'Something Went Wrong', 1);
            }
            Session::put('lender_base_payment',false);
            $message='Successfully Added';
            $request->session()->flash('message', $message);
            $status=true;
        } catch (Exception $e) {
            $status=false;
            $message=$e->getMessage();
            // return redirect()->back()->withErrors($e->getMessage());
        }
        return response()->json(['status' => $status, 'message' => $message], 200);
        return redirect('admin/payment/lender_payment_generation');
    }

    /**
     * ACH payment send function from front end.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function achConfirmationStore(Request $request)
    {
        try {
            DB::beginTransaction();
            $result = PaymentHelper::achConfirmationStore($request);
            $message = $result['message'];
            $data = $result['result'];
            if (!$result['status']) {
                throw new Exception($message ?? 'Something Went Wrong', 1);
            }
            DB::commit();
            return Excel::download($data['exportCSV'], $data['fileName']);
        } catch (Exception $e) {
            DB::rollback();
            $message = $e->getMessage();
            $request->session()->flash('error', $message);
        }
        return false;
    }

    /**
     * ACH payment send page function.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function achConfirmation(Request $request)
    {
        $result = PaymentHelper::achConfirmation($request);

        return view('admin.payments.ach_payments', $result);
    }

    /**
     * ACH requests page function.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $tableBuilder
     * @return \Illuminate\Http\Response
     */
    public function achRequests(Request $request, Builder $tableBuilder)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $result = PaymentHelper::achRequestsData($request);
            return $result;
        }
        $result = PaymentHelper::achRequests($tableBuilder);
        return view('admin.payments.ach_requests', $result);
    }

    /**
     * ACH requests export function.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function achRequestsExport(Request $request)
    {
        $result = PaymentHelper::achRequestsExport($request);
        $export = $result['export'];
        $fileName = $result['fileName'];

        return Excel::download($export, $fileName);
    }

    public function achCheckSingleStatus($id)
    {
        try {
            DB::beginTransaction();
            $result = PaymentHelper::achCheckSingleStatus($id);
            $message = $result['message'];
            $data = $result['result'];
            if (!$result['status']) {
                throw new Exception($message ?? 'Something Went Wrong', 1);
            }
            $message =  $data['message'];
            $status =  $data['status'];
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $message = $e->getMessage();
            $data = null;
            $status = false;
        }

        return response()->json(['data' => $data, 'message' => $message, 'status' => $status], 200);
    }

    public function achCheckStatusCsv()
    {
        try {
            DB::beginTransaction();
            $result = PaymentHelper::achCheckStatusCsv();
            $message = $result['message'];
            $data = $result['result'];
            if (!$result['status']) {
                throw new Exception($message ?? 'Something Went Wrong', 1);
            }
            DB::commit();
            return Excel::download($data['exportCSV'], $data['fileName']);
        } catch (Exception $e) {
            DB::rollback();
            $message = $e->getMessage();
            Session::flash('error', $message);
        }
        return false;
    }

    public function achFees(Request $request, Builder $tableBuilder)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $result = PaymentHelper::achFeesData($request);
            return $result;
        }
        $result = PaymentHelper::achFees($tableBuilder);
        return view('admin.payments.ach_fees', $result);
    }

    public function achFeesExport(Request $request)
    {
        $result = PaymentHelper::achFeesExport($request);
        $export = $result['export'];
        $fileName = $result['fileName'];

        return Excel::download($export, $fileName);
    }

    public function investorAchRequests(Request $request, Builder $tableBuilder)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $result = PaymentHelper::investorAchRequestsData($request);
            return $result;
        }
        $result = PaymentHelper::investorAchRequests($tableBuilder);
        return view('admin.payments.investor_ach_requests', $result);
    }

    public function investorAchRequestsExport(Request $request)
    {
        $result = PaymentHelper::investorAchRequestsExport($request);
        $export = $result['export'];
        $fileName = $result['fileName'];

        return Excel::download($export, $fileName);
    }

    public function investorAchCheckSingleStatus($id)
    {
        $return = [];
        try {
            DB::beginTransaction();
            $result = PaymentHelper::investorAchCheckSingleStatus($id);
            $message = $result['result'];
            if (!$result['status']) {
                throw new Exception($message ?? 'Something Went Wrong', 1);
            }
            $return['result'] = $result['result'];
            $return['data'] = $result['data'];
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $return['result'] = $e->getMessage();
        }
        return response()->json($return, 200);
    }

    public function investorAchCheckAchRequestStatus(Request $request)
    {
        try {
            DB::beginTransaction();
            $result = PaymentHelper::investorAchCheckAchRequestStatus($request);
            $message = $result['result'];
            if ($message != 'success') {
                throw new Exception($message ?? 'Something Went Wrong', 1);
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $result['status'] = $e->getMessage();
        }
        return response()->json($result, 200);
    }

    public function achDoubleCheckStatus()
    {
        try {
            DB::beginTransaction();
            $result = PaymentHelper::achDoubleCheckStatus();
            $message = $result['message'];
            $data = $result['result'];
            if (!$result['status']) {
                throw new Exception($message ?? 'Something Went Wrong', 1);
            }
            DB::commit();
            return $data;
        } catch (Exception $e) {
            DB::rollback();
            $message = $e->getMessage();
            return $message;
        }
    }

    public function updateAchpayments(Request $request)
    {
        try {
            DB::beginTransaction();
            $result = PaymentHelper::updateAchpayments($request);
            $message = $result['message'];
            if (!$result['status']) {
                throw new Exception($message ?? 'Something Went Wrong', 1);
            }
            $request->session()->flash('message', $message);
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $request->session()->flash('error', $e->getMessage());
        }
        return redirect()->back();
    }

    public function changeAutoAchStatusMerchant(Request $request)
    {
        try {
            DB::beginTransaction();
            $result = PaymentHelper::changeAutoAchStatusMerchant($request);
            $message = $result['message'];
            if (!$result['status']) {
                throw new Exception($message ?? 'Something Went Wrong', 1);
            }
            $status = 1;
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $status = 0;
            $message = $e->getMessage();
        }
        return response()->json(['message' => $message, 'status' => $status]);
    }

    public function removeInvestorACHPendingVerification($data, Request $request)
    {
        $result = PaymentHelper::removeInvestorACHPendingVerification($data, $request);

        return view('admin.payments.ach_delete_old', $result);
    }

    public function removeInvestorACHPendingFunction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ach_ids' => 'required|array',
            'request_date' => 'required|date',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        try {
            DB::beginTransaction();
            $result = PaymentHelper::removeInvestorACHPendingFunction($request);
            $message = $result['message'];
            if (!$result['status']) {
                throw new Exception($message ?? 'Something Went Wrong', 1);
            }
            $request->session()->flash('message', $message);
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $request->session()->flash('error', $e->getMessage());
        }
        return redirect()->back();
    }
}
