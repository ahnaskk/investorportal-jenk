<?php

namespace App\Helpers;

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
use Yajra\DataTables\Html\Builder;
use Exception;

class MerchantStatementHelper
{
	public function __construct(Builder $tableBuilder)
    {
    	$this->tableBuilder=$tableBuilder;
        
    }

    public function getAllStatements()
    {
    	$page_title = 'Generated Statement Manager';

        $this->tableBuilder->ajax(['url' => route('admin::merchants-statements'), 'data' => 'function(d){ d.start_date = $("#date_start").val(); d.end_date = $("#date_end").val();d.merchants = $("#merchants").val();}'])->parameters(['aaSorting' => [], 'columnDefs' => '[{orderable: false, targets: [0]}]']);
        $this->tableBuilder->parameters(['fnCreatedRow' => "function (nRow, aData, iDataIndex) {\n               var info = this.dataTable().api().page.info();\n               var page = info.page;\n               var length = info.length;\n               var index = (page * length + (iDataIndex + 1));\n               $('td:eq(1)', nRow).html(index).addClass('txt-center');\n           }", 'pagingType' => 'input']);
        $this->tableBuilder->columns(\MTB::getAllStatementsMerchant(null, null, null, true));
        return ['page_title'=>$page_title,'tableBuilder'=>$this->tableBuilder];

    }
	public function storeMerchantStatement($request)
	{
		try {
			$merchants = $request->merchants;
			if (empty($merchants)) throw new Exception("Merchant not available", 1);
			$to_date = $request->endDate ?? Carbon::now()->addDays(5)->toDateString();
			$msg = '';
			foreach ($merchants as $id) {
				$merchant = Merchant::with('user')->find($id);
				$merchant_email = '';
				if($merchant->user_id!=null){
					$merchant_email = User::where('id',$merchant->user_id)->value('email');
				}
				$from_date = $merchant->date_funded;
				$total_payment = ParticipentPayment::where('merchant_id', $id)->where('participent_payments.is_payment', 1)->where('status', ParticipentPayment::StatusCompleted)->sum('payment');
				$fees = MerchantUser::select(
					'merchant_id', 
					'created_at', 
					DB::raw('
					sum(commission_amount) as commission,
					sum(up_sell_commission) as up_sell_commission,
					sum(pre_paid) as prepaid,
					sum(under_writing_fee) as underwriting_fee,
					sum(invest_rtr * (mgmnt_fee / 100)) as management_fee
					')
				);
				$fees = $fees->where('merchant_id', $id);
				$fees = $fees->first();
				$OverpaymentAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
				$OverpaymentAccount->where('user_has_roles.role_id', User::OVERPAYMENT_ROLE);
				$OverpaymentAccount = $OverpaymentAccount->first(['users.id']);
				$OverpaymentAccountMgmntFee = PaymentInvestors::where('merchant_id', $id)->where('user_id', $OverpaymentAccount->id)->sum('mgmnt_fee');
				$data = ParticipentPayment::select(
					'participent_payments.payment_date',
					'participent_payments.rcode',
					'participent_payments.payment',
					'merchants.rtr',
					'rcode.description as rcode_description'
				);
				$data = $data->leftJoin('merchants', 'merchants.id', 'participent_payments.merchant_id');
				$data = $data->leftJoin('rcode', 'rcode.id', 'participent_payments.rcode');
				$data = $data->where('participent_payments.is_payment', 1);
				$data = $data->where('status', ParticipentPayment::StatusCompleted);
				$data = $data->where('participent_payments.merchant_id', $id);
				if ($to_date) {
					$data = $data->where('payment_date', '<=', $to_date);
				}
				$data = $data->orderBy('participent_payments.payment_date', 'ASC')->get();
				$paymentList = [];
				$totalPaidToDate = 0;
				$balance = $merchant->rtr;
				foreach ($data as $key => $value) {
					$single['id']                = $key + 1;
					$single['date']              = FFM::date($value->payment_date);
					$single['rcode']             = $value->rcode;
					$single['rcode_description'] = $value->rcode_description;
					$single['payment']           = $value->payment;
					$totalPaidToDate            += $value->payment;
					$single['PaidToDate']        = $totalPaidToDate;
					$balance -= $value->payment;
					$single['balance'] = $balance;
					if ($balance < 0) {
						$single['balance'] = 0;
					}
					$paymentList[] = $single;
				}
				array_multisort(array_column($paymentList, 'id'), SORT_DESC, $paymentList);
				$commonName    = 'merchant_statements/'.$id.'/'.date(\FFM::defaultDateFormat('db'), strtotime($to_date)).'date_range_'.time();
				$funded_date   = Carbon::parse($merchant->date_funded)->format(\FFM::defaultDateFormat('db'));
				$end_date      = Carbon::parse($to_date)->format(\FFM::defaultDateFormat('db'));
				$final_balance = $merchant->rtr - $total_payment;
				$over_payment  = PaymentInvestors::where('merchant_id', $id)->sum('overpayment');
				if ($final_balance < 0) {
					$final_balance = 0;
				}
				$pdf = PDF::loadView('admin.merchants.statements.pdf', compact('data', 'fees', 'OverpaymentAccountMgmntFee', 'merchant', 'total_payment', 'final_balance', 'over_payment', 'end_date', 'funded_date', 'paymentList','merchant_email'));
				$filePDFName = $commonName.'.pdf';
				$load = Storage::disk('s3')->put($filePDFName, $pdf->output(), config('filesystems.disks.s3.privacy'));
				if(!$load) throw new Exception("something went wrong in storage creation", 1);
				$filePDFUrl = asset(Storage::disk('s3')->temporaryUrl($filePDFName,Carbon::now()->addMinutes(2)));
				$mail_status = 0;
				$db = MerchantStatement::insertGetId(['file_name' => $commonName, 'merchant_id' => $id, 'from_date' => $from_date, 'to_date' => $to_date, 'file_type' => 'pdf', 'mail_status' => $mail_status, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'creator_id' => $request->user()->id]);
				if(!$db) throw new Exception("merchant statements not created ", 1);
				$msg .= 'Statement Generated Successfully for '.$merchant->name.' till '.$end_date.'. <a class="btn btn-success" target="_blank" href='.$filePDFUrl.'>Click here to view</a><br>';
			}
			$result['result'] = 'success';
			$result['msg']    = $msg;
		} catch (\Exception $e) {
			$result['result'] = $e->getMessage();
		}
		return $result;
	}

    public function showStatement($id)
    {
        try{
    	    $merchant = Merchant::find($id);
            if (! $merchant) {
                throw new \Exception('Invalid Merchant', 1);
            }
            $statement = MerchantStatement::where('merchant_id', $id)->latest()->first();
            if (! $statement) {
                throw new \Exception('No statement found', 1);
            }
            $filePDFUrl = asset(Storage::disk('s3')->temporaryUrl($statement->file_name.'.pdf',Carbon::now()->addMinutes(2)));
            $msg = 'Last Statement of '.$merchant->name.' till '.\FFM::date($statement->to_date).'. <a class="btn btn-success" href='.$filePDFUrl.'>Click here to view</a><br>';

             $result['result']='success';
             $result['msg']=$msg;
         } catch (\Exception $e) {
            $return['result'] =$e->getMessage();
        }
            return $result;

    }
	public function destroyStatement($request)
	{
		try {
			$id_array = $request->multi_id;
			if (empty($id_array)) throw new \Exception("Empty Id", 1);
			foreach ($id_array as $id) {
				$st = MerchantStatement::find($id);
				if(!$st) throw new \Exception("Invalid Id", 1);
				if(!MerchantStatement::destroy($id)) throw new \Exception("Something went Wrong in MerchantStatement", 1);
				if(!Storage::disk('s3')->delete($st->file_name.'.pdf')) throw new \Exception("Something went Wrong Storage Deletion", 1);
			}
			$return['result'] ='success';
		} catch (\Exception $e) {
			$return['result'] =$e->getMessage();
		}
		return $return;
		
	}

    public function deleteStatement($request)
    {
        try
        {
    	$id_array = $request->multi_id;
        if (empty($id_array)) throw new Exception("Empty Id", 1);
        
            foreach ($id_array as $id) {
                $st = Statements::find($id);
                if(!$st) throw new Exception("Invalid Id", 1);
                
                if (!Statements::destroy($id)) throw new Exception("Something went Wrong in statements", 1);
                 if(!Storage::disk('s3')->delete($st->file_name.'.pdf')) throw new Exception("Something went Wrong Storage Deletion", 1);    
           }     
          $return['result'] ='success';
        
        }catch (\Exception $e) {
            $return['result'] =$e->getMessage();
        }
          return $return;

    }


    
}
