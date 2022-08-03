<?php

namespace App\Helpers;

use App\AchRequest;
use App\Bank;
use App\CompanyAmount;
use App\Exports\Data_arrExport;
use App\FundingRequests;
use App\Helpers\MerchantHelper;
use App\Helpers\MerchantUserHelper;
use App\Http\Controllers\Admin\Traits\CreditCardStripe;
use App\Http\Controllers\Admin\Traits\DocumentUploader;
use App\Library\Repository\Interfaces\IMerchantRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Http\Requests\Admin\StoryMerchantRequest;
use App\Http\Requests\AdminUpdateMerchantRequest;
use App\InvestorTransaction;
use App\Jobs\CommonJobs;
use App\Jobs\CRMjobs;
use App\Label;
use App\LiquidityLog;
use App\MarketpalceInvestors;
use App\MbatchMarchant;
use App\Merchant;
use App\MerchantBankAccount;
use App\MerchantPaymentTerm;
use App\MerchantStatusLog;
use App\MerchantUser;
use App\MNotes;
use App\SubStatusFlag;
use App\Models\Transaction;
use App\Models\Views\MerchantUserView;
use App\ParticipentPayment;
use App\PaymentInvestors;
use App\PaymentPause;
use App\ReassignHistory;
use App\Settings;
use App\SubStatus;
use App\Template;
use App\TermPaymentDate;
use App\User;
use App\UserActivityLog;
use App\UserDetails;
use App\VelocityFee;
use Carbon\Carbon;
use DateTime;
use Exception;
use FFM;
use Form;
use GPH;
use Hashids\Hashids;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use MTB;
use PayCalc;
use PDF;
use Permissions;
use Spatie\Permission\Models\Role;
use Stripe\Charge;
use Stripe\Stripe;
use Yajra\DataTables\Html\Builder;


class PaymentTermHelper
{
    public function __construct(IMerchantRepository $merchant)
    {
         $this->merchant = $merchant;  
    }

	public function createPaymentTerm($request,$mid)
	{
        $page_title = 'Create Payment Term';
        $company_id = 0;
        $merchant_array = $this->merchant->merchant_details($mid, $company_id);
        $merchant_data = $merchant_array['merchant'];
        $balance = $merchant_array['balance_merchant'];
        $today = date('Y-m-d');
        $future_payment_total = Merchant::find($mid)->termPayments()->where('payment_date', '>', $today)->where('status', 0)->sum('payment_amount');
        $processing_payment_total = Merchant::find($mid)->termPayments()->where('status', TermPaymentDate::ACHProcessing)->sum('payment_amount');
        $anticipated_balance = $balance - $future_payment_total - $processing_payment_total;
        $advance_types = config('custom.ach_advance_types');
        $tomorrow = Carbon::now()->addDay()->toDateString();
        if ($tomorrow <= $merchant_data->payment_end_date) {
            $min_start_date = Carbon::parse($merchant_data->payment_end_date)->addDay()->toDateString();
        } else {
            $min_start_date = $tomorrow;
        }
        $start_date = null;
        $advance_type = null;
        $terms = null;
        $end_date = null;
        $payment_amount = null;
        if ($anticipated_balance < 0) {
            $anticipated_balance = 0;
        } elseif ($anticipated_balance > 0) {
            $payment_amount = $merchant_data->payment_amount;
            $terms_division = $anticipated_balance / $payment_amount;
            $terms = floor($terms_division);
            $terms_reminder = fmod($anticipated_balance, $payment_amount);
            if ($terms < 1) {
                $terms = 1;
                $payment_amount = $anticipated_balance;
            } elseif ($terms_reminder > 1) {
                $terms++;
            }
            $start_date = PayCalc::getWorkingDay($min_start_date);
            $advance_type = $merchant_data->advance_type;
            $end_date = $this->merchant->getEndDate($start_date, $advance_type, $terms);
        }
        $preset_values = ['start_date' => $start_date, 'advance_type' => $advance_type, 'terms' => $terms, 'end_date' => $end_date, 'payment_amount' => sprintf('%.2f', $payment_amount)];
        $merchant = ['merchant_id' => $mid, 'name' => $merchant_data->name, 'balance' => $balance, 'anticipated_balance' => $anticipated_balance, 'first_payment' => FFM::date($merchant_data->first_payment), 'last_payment_date' => FFM::date($merchant_data->last_payment_date), 'payment_end_date' => FFM::date($merchant_data->payment_end_date), 'payment_paused' => $merchant_data->payment_pause_id, 'payment_pause' => isset($merchant_data->payment_pause_id) ? PaymentPause::find($merchant_data->payment_pause_id) : null];

        return ['page_title'=>$page_title,'merchant'=>$merchant,'advance_types'=>$advance_types,'min_start_date'=>$min_start_date,'preset_values'=>$preset_values];


	}

	public function editPaymentTerm($request,$mid,$id)
	{
		$page_title = 'Edit Payment Term';
        $company_id = 0;
        $merchant_array = $this->merchant->merchant_details($mid, $company_id);
        $today = Carbon::now()->toDateString();
        $merchant_data = $merchant_array['merchant'];
        $balance = $merchant_array['balance_merchant'];
        $future_payment_total = Merchant::find($mid)->termPayments()->where('payment_date', '>', $today)->where('status', 0)->sum('payment_amount');
        $processing_payment_total = Merchant::find($mid)->termPayments()->where('status', TermPaymentDate::ACHProcessing)->sum('payment_amount');
        $anticipated_balance = $balance - $future_payment_total - $processing_payment_total;
        if ($anticipated_balance < 0) {
            $anticipated_balance = 0;
        }
        $merchant = ['merchant_id' => $mid, 'name' => $merchant_data->name, 'balance' => $balance, 'anticipated_balance' => $anticipated_balance, 'first_payment' => FFM::date($merchant_data->first_payment), 'last_payment_date' => FFM::date($merchant_data->last_payment_date), 'payment_end_date' => FFM::date($merchant_data->payment_end_date), 'payment_paused' => $merchant_data->payment_pause_id, 'payment_pause' => isset($merchant_data->payment_pause_id) ? PaymentPause::find($merchant_data->payment_pause_id) : null];
        $tomorrow = Carbon::now()->addday()->toDateString();
        $term = MerchantPaymentTerm::where('merchant_id', $mid)->find($id);
        $editable_date = PayCalc::getPreviousWorkingDay($term->start_at);
        $current_term = false;
        if ($term->start_at <= $today && $today <= $term->end_at) {
            $current_term = true;
        }
        $payment_started = false;
        if ($term->start_at < $today || $today > $term->end_at) {
            $payment_started = true;
        }
        $payments = $term->payments()->where('status', '>', 0)->orderByDesc('payment_date');
        $paid_payments = $payments->count();
        if ($payments->count()) {
            $payment_started = true;
            $last_payment_date = $payments->first()->payment_date;
            if ($last_payment_date > $editable_date) {
                $editable_date = $last_payment_date;
            }
        }
        $last_payment = $term->merchant->last_payment_date;
        if ($last_payment) {
            if ($last_payment > $editable_date) {
                $editable_date = $last_payment;
            }
        }
        if ($today > $editable_date) {
            $editable_date = $today;
        }
        $pending_payments = $term->payments()->where('status', 0)->where('payment_date', '>', $today);
        $pending_payments_count = $pending_payments->count();
        $pending_payments_total = $pending_payments->sum('payment_amount');
        $term = (object) ['id' => $term->id, 'merchant_id' => $term->merchant_id, 'advance_type' => $term->advance_type, 'pmnts' => $term->pmnts, 'payment_amount' => $term->payment_amount, 'payment_left' => $term->actual_payment_left, 'actual_payment_left' => $pending_payments_count, 'created_at' => $term->created_at, 'start_at' => $term->start_at, 'end_at' => $term->end_at, 'current_term' => $current_term, 'payment_started' => $payment_started, 'editable_date' => $editable_date, 'paid_payments' => $paid_payments, 'payment_left_total' => $pending_payments_total];
        $advance_types = config('custom.ach_advance_types');
        $holidays = config('custom.holidays');
        $holidays = array_keys($holidays);
        return ['page_title'=>$page_title,'merchant'=>$merchant,'advance_types'=>$advance_types,'term'=>$term,'tomorrow'=>$tomorrow,'holidays'=>$holidays];

	}


	public function deletePaymentTerm($extra=[])
	{
        $mid=$extra['mid'];
        $id=$extra['id'];
        $request=$extra['request'];

		$deletable = true;
        $today = Carbon::now()->toDateString();
        $term = MerchantPaymentTerm::where('merchant_id', $mid)->find($id);
        if ($term) {
            if ($term->start_at < $today || $today > $term->end_at) {
                $deletable = false;
            }
            $payments = $term->payments()->where('status', '>', 0)->orderByDesc('payment_date');
            $paid_payments = $payments->count();
            if ($paid_payments) {
                $deletable = false;
            }
            if ($deletable) {
                $term->payments()->delete();
                $term->delete();
                $merchant = Merchant::find($mid);
                $latest_term = $merchant->paymentTerms()->orderByDesc('end_at')->first();
                if ($latest_term) {
                    if ($merchant->payment_end_date != $latest_term->end_at) {
                        $merchant->payment_end_date = $latest_term->end_at;
                        $merchant->update();
                    }
                } else {
                    $merchant->payment_end_date = null;
                    $merchant->update();
                }

                return redirect()->back()->with('message', 'Term deleted successfully.');
            }

            return redirect()->back()->with('error', 'Cannot delete this term.');
        }

        return redirect()->back()->with('error', 'No term found with this ID.');



	}

	public function storeMerchantPaymentTerm($request,$mid)
	{
        $advance_types = config('custom.ach_advance_types');
        $request->validate([
            'advance_type' => ['required', Rule::in(array_keys($advance_types))],
            'terms' => 'required|integer|gt:0',
            'payment_amount' => 'required|numeric|gt:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);
        $company_id = 0;
        $merchant_array = $this->merchant->merchant_details($mid, $company_id);
        $merchant = $merchant_array['merchant'];
        $unwanted_sub_status = config('custom.unwanted_sub_status');
        if (in_array($merchant->sub_status_id, $unwanted_sub_status)) {
            return redirect()->back()->with('error', 'Invalid Sub status');
        }
        $ctd = $merchant_array['ctd_sum'];
        $payment_left = $merchant_array['payment_left'];
        $amount_difference = $merchant_array['amount_difference'];
        $actual_payment_left = $merchant_array['actual_payment_left'];
        $rtr = $merchant->rtr;
        $pmnts = $merchant->pmnts;
        $complete_percentage = $merchant->complete_percentage;
        $payment_amount = $merchant->payment_amount;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $today = Carbon::now()->toDateString();
        $current_term = Merchant::find($mid)->termPayments()->whereBetween('payment_date', [$start_date, $end_date]);
        if ($current_term->count()) {
            $error = 'A term exists in the current date range.';
            $request->flash();

            return redirect()->back()->with('error', $error);
        } else {
            $current_term = $merchant_array['merchant'];
            $terms = $request->terms;
            $payment_amount = $request->payment_amount;
            $payment_term = MerchantPaymentTerm::create(['merchant_id' => $mid, 'advance_type' => $request->advance_type, 'pmnts' => $terms, 'payment_amount' => $payment_amount, 'actual_payment_left' => $terms, 'start_at' => $request->start_date, 'end_at' => $request->end_date, 'status' => 0, 'created_by' => $request->user()->name]);
            $term_dates = $this->merchant->storeTermdates($payment_term);
            $merchant = Merchant::find($mid);
            $latest_term = $merchant->paymentTerms()->orderByDesc('end_at')->first();
            if ($merchant->payment_end_date != $latest_term->end_at) {
                $merchant->payment_end_date = $latest_term->end_at;
                $merchant->update();
            }

            return redirect()->back()->with('message', 'Terms Added');
        }
        $request->flash();

        return redirect()->back()->with('error', 'Terms not Added');  


	}


	public function updateMerchantPaymentTerm($request,$mid)
	{
        $advance_types = array_keys(config('custom.ach_advance_types'));
        $validator = Validator::make($request->all(), ['term_id' => 'required|exists:merchant_payment_terms,id', 'advance_type' => ['required', Rule::in($advance_types)], 'terms' => 'required|integer|gt:0', 'payment_amount' => 'required|numeric', 'start_date' => 'required|date', 'end_date' => 'required|date']);
        if ($validator->fails()) {
            $error = '';
            foreach (array_values($validator->messages()->toArray()) as $msg) {
                $error = $error.implode(' ', $msg);
            }

            return redirect()->back()->with('error', $error);
        }
        $terms = $request->terms;
        $payment_amount = $request->payment_amount;
        $merchant = Merchant::find($mid);
        $unwanted_sub_status = config('custom.unwanted_sub_status');
        if (in_array($merchant->sub_status_id, $unwanted_sub_status)) {
            return redirect()->back()->with('error', 'Invalid Sub status');
        }
        $term = MerchantPaymentTerm::where('merchant_id', $mid)->where('id', $request->term_id)->first();
        if ($term) {
            $duplicate_term = $merchant->termPayments()->where('term_id', '!=', $request->term_id)->whereBetween('payment_date', [$request->start_date, $request->end_date]);
            if ($duplicate_term->count()) {
                $error = 'A term exists in the current date range.';

                return redirect()->back()->with('error', $error);
            }
            $paid_payments = $term->payments()->where('status', 1)->count();
            if ($paid_payments) {
                $error = 'Term Payment has been started.';
                $term->update(['pmnts' => $terms, 'actual_payment_left' => $terms - $paid_payments, 'end_at' => $request->end_date, 'updated_by' => $request->user()->name]);
            } else {
                $term->update(['merchant_id' => $mid, 'advance_type' => $request->advance_type, 'pmnts' => $terms, 'actual_payment_left' => $terms, 'payment_amount' => $payment_amount, 'start_at' => $request->start_date, 'end_at' => $request->end_date, 'updated_by' => $request->user()->name]);
            }
            $term_dates = $this->merchant->updateTermdates($term, $paid_payments);
            $merchant = Merchant::find($mid);
            $latest_term = $merchant->paymentTerms()->orderByDesc('end_at')->first();
            if ($merchant->payment_end_date != $latest_term->end_at) {
                $merchant->payment_end_date = $latest_term->end_at;
                $merchant->update();
            }

            return redirect()->back()->with('message', 'Terms Updated');
        }

        return redirect()->back()->with('error', 'Terms not Updated');


	}

    public function updatePaymentTerm($request,$mid)
    {
            if (Permissions::isAllow('ACH', 'Edit')) {
                $payment_id = $request->payment_id;
                $payment_amount = $request->payment_amount;
                if ($payment_id) {
                    $payment = TermPaymentDate::find($payment_id);
                    if ($payment) {
                        if ($payment->merchant_id == $mid) {
                            if ($payment_amount >= 0) {
                                if ($payment_amount != $payment->payment_amount) {
                                    $current_time = Carbon::now();
                                    $next_working_day = PayCalc::getWorkingDay($current_time->addDay()->toDateString());
                                    if ($payment->payment_date < $next_working_day) {
                                        $editable = false;
                                    } elseif ($payment->payment_date > $next_working_day) {
                                        $editable = true;
                                    } elseif ($payment->payment_date == $next_working_day) {
                                        $date_time = $current_time;
                                        $cutoff_time = new Carbon($next_working_day.' 14:45:00');
                                        if ($date_time->lessThan($cutoff_time)) {
                                            $editable = true;
                                        } else {
                                            $editable = false;
                                        }
                                    }
                                    if ($editable) {
                                        $payment->update(['payment_amount' => $payment_amount]);
                                        $term_payments_count = $payment->paymentTerm->payments()->count();
                                        if ($term_payments_count == 1) {
                                            $payment->paymentTerm->update(['payment_amount' => $payment_amount]);
                                        }
                                        DB::commit();
                                        $status = 'message';
                                        $reason = 'Payment Updated for '.FFM::date($payment->payment_date);
                                    } else {
                                        $status = 'error';
                                        $reason = 'This payment is not editable.';
                                    }
                                } else {
                                    $status = 'error';
                                    $reason = 'No change in payment amount.';
                                }
                            } else {
                                $status = 'error';
                                $reason = 'Invalid payment amount.';
                            }
                        } else {
                            $status = 'error';
                            $reason = 'Invalid Merchant.';
                        }
                    } else {
                        $status = 'error';
                        $reason = 'No Payment on this ID.';
                    }
                } else {
                    $status = 'error';
                    $reason = 'Invalid request.';
                }
            } else {
                $status = 'error';
                $reason = 'No permission.';
            }
            $request->session()->flash($status, $reason);

          return 1;


    }

    public function addPaymentTerm($request,$mid)
    {
        $status = 0;
        $message = '';
        $validator = Validator::make($request->all(), ['merchant_id' => 'required|exists:merchants,id', 'payment_date' => 'required|date', 'payment_amount' => 'required|numeric|gt:0']);
        if ($validator->fails()) {
            foreach (array_values($validator->messages()->toArray()) as $msg) {
                $message = $message.implode(' ', $msg);
            }
            goto skipAddTermPayment;
        }
        $payment_date = $request->payment_date;
        $payment_amount = $request->payment_amount;
        $check_working_day = PayCalc::checkWorkingDay($payment_date);
        if (! $check_working_day) {
            $message = 'Given date is a holiday.';
        }
        $check_payment = TermPaymentDate::where(['merchant_id' => $mid, 'payment_date' => $payment_date])->count();
        if ($check_payment == 0) {
            $today = Carbon::now()->tz('America/New_York');
            $next_working_day = PayCalc::getWorkingDay($today->clone()->addDay()->toDateString());
            if ($payment_date >= $next_working_day) {
                $payment_date_formatted = FFM::date($payment_date);
                if ($payment_date == $next_working_day) {
                    $cutoff_time = new Carbon($today->toDateString().' 14:45:00', 'America/New_York');
                    if (! $today->lessThan($cutoff_time)) {
                        $message = "You can't add payment to next working day after 2:45 PM";
                        goto skipAddTermPayment;
                    }
                }
                $status = 1;
                $message = "New payment added for $payment_date_formatted";
                $term = MerchantPaymentTerm::where(['merchant_id' => $mid, ['start_at', '<=', $payment_date], ['end_at', '>=', $payment_date]]);
                if ($term->count() > 0) {
                    $message .= ' in exisiting term';
                    $term = $term->first();
                    $term->pmnts = $term->pmnts + 1;
                    $term->actual_payment_left = $term->actual_payment_left + 1;
                    $term->update();
                } else {
                    $term = MerchantPaymentTerm::create(['merchant_id' => $mid, 'advance_type' => 'daily_ach', 'pmnts' => 1, 'payment_amount' => $payment_amount, 'actual_payment_left' => 1, 'start_at' => $payment_date, 'end_at' => $payment_date, 'status' => 0, 'created_by' => $request->user()->name]);
                    $message .= ' with new term';
                    $end_date = Merchant::where('id', $mid)->value('payment_end_date');
                    if ($end_date < $payment_date) {
                        $update_end_date = Merchant::where('id', $mid)->update(['payment_end_date' => $payment_date]);
                    }
                }
                $create_payment = TermPaymentDate::create(['merchant_id' => $mid, 'payment_amount' => $payment_amount, 'payment_date' => $payment_date, 'term_id' => $term->id, 'status' => TermPaymentDate::ACHNotPaid]);
            } else {
                $message = 'You can add payment only for next working day onwards.';
            }
        } else {
            $message = 'Existing payment is on the given date.';
        }
        skipAddTermPayment:

        return ['status' => $status, 'msg' => $message];


    }

    public function makeUpPaymentTerms($request)
    {
        $validator = Validator::make($request->all(), ['merchant_id' => 'required|exists:merchants,id']);
        if ($validator->fails()) {
            $error = '';
            foreach (array_values($validator->messages()->toArray()) as $msg) {
                $error = $error.implode(' ', $msg);
            }

            return response()->json(['status' => 0, 'msg' => $error]);
        }
        $current_time = Carbon::now();
        $today = $current_time->toDateString();
        $mid = $request->merchant_id;
        $company_id = 0;
        $merchant_array = $this->merchant->merchant_details($mid, $company_id);
        $merchant_data = $merchant_array['merchant'];
        $balance = $merchant_array['balance_merchant'];
        $merchant = Merchant::find($mid);
        $advance_type = $merchant->advance_type;
        $payment_amount = $merchant->payment_amount;
        $future_payment_total = Merchant::find($mid)->termPayments()->where('payment_date', '>', $today)->where('status', 0)->sum('payment_amount');
        $processing_payment_total = Merchant::find($mid)->termPayments()->where('status', TermPaymentDate::ACHProcessing)->sum('payment_amount');
        $anticipated_balance = $balance - $future_payment_total - $processing_payment_total;
        if ($anticipated_balance > 1) {
            $terms_division = $anticipated_balance / $payment_amount;
            $terms = floor($terms_division);
            $terms_reminder = fmod($anticipated_balance, $payment_amount);
            if ($terms < 1) {
                $terms = 1;
                $payment_amount = $anticipated_balance;
            }
            $message = 'Makeup term created with default values';
            $status = 1;
            $day_after_tomorrow = $current_time->addDays(2);
            if ($day_after_tomorrow->toDateString() <= $merchant->payment_end_date) {
                $start_at = Carbon::parse($merchant->payment_end_date)->addDay();
            } else {
                $start_at = $day_after_tomorrow;
            }
            $next_friday = $start_at->copy()->next('Friday');
            $date_found = false;
            while (! $date_found) {
                $check_working_day = PayCalc::checkWorkingDay($next_friday->toDateString());
                if ($check_working_day) {
                    $date_found = true;
                    $start_at = $next_friday->copy()->toDateString();
                } else {
                    $day_before_next_friday = $next_friday->copy()->subDay();
                    $check_working_day = PayCalc::checkWorkingDay($day_before_next_friday->toDateString());
                    if ($check_working_day && $day_before_next_friday->toDateString() >= $start_at->toDateString()) {
                        $date_found = true;
                        $start_at = $day_before_next_friday->toDateString();
                    } else {
                        $next_friday = $next_friday->next('Friday');
                    }
                }
            }
            $end_at = $this->merchant->getEndDate($start_at, $advance_type, $terms);
            $payment_term = MerchantPaymentTerm::create(['merchant_id' => $mid, 'advance_type' => $advance_type, 'pmnts' => $terms, 'payment_amount' => $payment_amount, 'actual_payment_left' => $terms, 'start_at' => $start_at, 'end_at' => $end_at, 'status' => 0]);
            $term_dates = $this->merchant->storeTermdates($payment_term);
            $merchant->payment_end_date = $end_at;
            $merchant->update();
            if ($terms_division >= 1 && $terms_reminder) {
                $reminder_payment = TermPaymentDate::where(['merchant_id' => $mid, 'term_id' => $payment_term->id, 'payment_date' => $end_at])->first();
                if ($reminder_payment) {
                    $reminder_payment->payment_amount = $reminder_payment->payment_amount + $terms_reminder;
                    $reminder_payment->update();
                }
            }
        } else {
            $message = 'No balance to makeup';
            $status = 0;
        }

        return ['msg' => $message, 'status' => $status];


    }

    public function paymentTerm($request,$mid)
    {
        $page_title = 'ACH Terms';
        $company_id = 0;
        $merchant_data = Merchant::select(
            'id',
            'sub_status_id',
            'payment_pause_id',
            'payment_amount',
            'name',
            'advance_type',
            'first_payment',
            'last_payment_date',
            'payment_end_date',
            'ach_pull',
            'rtr'
        )->where('id', $mid)->first();
        $unwanted_sub_status = config('custom.unwanted_sub_status');
        $sub_statuses = DB::table('sub_statuses')->pluck('name', 'id')->toArray();
        $payment_total = ParticipentPayment::where('merchant_id', $mid)->where('is_payment', 1)->sum('payment');
        $balance = $merchant_data->rtr - $payment_total;
        $all_terms = MerchantPaymentTerm::where('merchant_id', $mid)->oldest()->get();
        $today = Carbon::now()->toDateString();
        $next_working_day = PayCalc::getWorkingDay($today);
        $advance_types = config('custom.advance_types');
        $old_terms = [];
        if ($all_terms) {
            foreach ($all_terms as $term) {
                $current_term = false;
                if ($term->start_at <= $next_working_day && $next_working_day <= $term->end_at) {
                    $current_term = true;
                }
                $is_active = true;
                if ($term->end_at < $today) {
                    $is_active = false;
                }
                $payment_started = false;
                if ($term->start_at < $today || $today > $term->end_at) {
                    $payment_started = true;
                } else {
                    $payments = $term->payments()->where('status', '>', 0)->orderByDesc('payment_date')->count();
                    if ($payments) {
                        $payment_started = true;
                    }
                }
                $pending_payments = $term->payments()->where('status', 0)->where('payment_date', '>', $today)->count();
                $old_terms[] = (object) [
                    'id' => $term->id,
                    'advance_type' => $term->advance_type,
                    'pmnts' => $term->pmnts,
                    'payment_amount' => $term->payment_amount,
                    'payment_left' => $term->actual_payment_left,
                    'actual_payment_left' => $pending_payments,
                    'start_at' => $term->start_at,
                    'end_at' => $term->end_at,
                    'current_term' => $current_term,
                    'payment_started' => $payment_started,
                    'created_at' => $term->created_at,
                    'created_by' => $term->created_by,
                    'updated_at' => $term->updated_at,
                    'updated_by' => $term->updated_by,
                    'is_active' => $is_active,
                ];
            }
        }
        $ach_pause = null;
        $sub_status = $sub_statuses[$merchant_data->sub_status_id];
        if (in_array($merchant_data->sub_status_id, $unwanted_sub_status) || $merchant_data->ach_pull != 1) {
            $ach_status = 'Inactive';
            $future_payment_total = 0;
            $active = false;
        } else {
            if (isset($merchant_data->payment_pause_id)) {
                $ach_status = 'Paused';
                $ach_pause = PaymentPause::find($merchant_data->payment_pause_id);
                $ach_paused_date = Carbon::parse($ach_pause->paused_at)->toDateString();
            } else {
                $ach_status = 'Active';
            }
            $future_payment_total = Merchant::find($mid)->termPayments()->where('payment_date', '>', $today)->where('status', 0)->sum('payment_amount');
            $active = true;
        }
        $processing_payment_total = Merchant::find($mid)->termPayments()->where('status', 2)->sum('payment_amount');
        $anticipated_balance = $balance - $future_payment_total - $processing_payment_total;
        $makeup_payments = null;
        if ($anticipated_balance > 1) {
            $makeup_payments = sprintf('%.2f', abs($anticipated_balance) / $merchant_data->payment_amount);
        }
        $merchant = [
            'merchant_id' => $mid,
            'name' => $merchant_data->name,
            'advance_type' => $advance_types[$merchant_data->advance_type],
            'payment_amount' => $merchant_data->payment_amount,
            'balance' => max($balance, 0),
            'first_payment' => FFM::date($merchant_data->first_payment),
            'last_payment_date' => FFM::date($merchant_data->last_payment_date),
            'payment_end_date' => FFM::date($merchant_data->payment_end_date),
            'payment_paused' => $merchant_data->payment_pause_id,
            'payment_pause' => $ach_pause,
            'future_payment_total' => $future_payment_total,
            'processing_payment_total' => $processing_payment_total,
            'anticipated_balance' => max($anticipated_balance, 0),
            'makeup_payments' => $makeup_payments,
            'sub_status' => $sub_status,
            'status' => $ach_status,
        ];
        $current_time = Carbon::now()->setTimeZone('America/New_York');
        $next_working_day = PayCalc::getWorkingDay($current_time->clone()->addDay()->toDateString());
        $cutoff_time = new Carbon($today.' 14:45:00', 'America/New_York');
        $term_ps = TermPaymentDate::where('merchant_id', $mid)->orderBy('payment_date', 'DESC')->get();
        $term_payments = [];
        foreach ($term_ps as $term_payment) {
            $editable = false;
            $ach_payment_status = null;
            $ach_style = '';
            if ($term_payment->status == 1) {
                $ach_style = 'background-color: #c3e6cb';
            } elseif ($term_payment->status == 0) {
                if ($active) {
                    if ($term_payment->payment_date < $next_working_day) {
                        $editable = false;
                    } elseif ($term_payment->payment_date > $next_working_day) {
                        $editable = true;
                    } elseif ($term_payment->payment_date == $next_working_day) {
                        if ($current_time->lessThan($cutoff_time)) {
                            $editable = true;
                        } else {
                            $editable = false;
                        }
                    }
                    if ($ach_pause) {
                        if ($term_payment->payment_date >= $ach_paused_date) {
                            $ach_payment_status = $ach_status;
                        }
                    }
                } else {
                    if ($term_payment->payment_date >= $today) {
                        $ach_payment_status = $sub_status;
                    }
                }
            } elseif ($term_payment->status == -1) {
                $ach_style = 'background-color: #ff3434';
                if ($term_payment->ach) {
                    $ach_payment_status = $term_payment->StatusName.' ('.$term_payment->ach->status_response.')';
                }
            }
            $term_payments[] = (object) [
                'id' => $term_payment->id,
                'term_id' => $term_payment->term_id,
                'payment_date' => FFM::date($term_payment->payment_date),
                'payment_amount' => FFM::dollar($term_payment->payment_amount),
                'payment_amount_actual' => $term_payment->payment_amount,
                'status' => $term_payment->status,
                'status_type' => $ach_payment_status ?? $term_payment->StatusName,
                'ach_style' => $ach_style ?? '',
                'editable' => $editable,
                'advance_type' => $advance_types[$term_payment->paymentTerm->advance_type],
                'total_payments' => FFM::dollar($term_payment->payments()->sum('payment')),
            ];
        }
        $term_dates = $term_ps->pluck('payment_date')->toArray();
        $holidays = config('custom.holidays');
        $holidays = array_keys($holidays);
        $holidays = array_merge($term_dates, $holidays);
        $merchants = Merchant::where('id', $mid)->first();

        return ['page_title'=>$page_title,'merchant'=>$merchant,'advance_types'=>$advance_types,'old_terms'=>$old_terms,'today'=>$today,'term_payments'=>$term_payments,'active'=>$active,'holidays'=>$holidays,'next_working_day'=>$next_working_day,'merchants'=>$merchants];

    }

    public function deleteTermPaymentFunction($mid, $tid, $id)
    {
        $deletable = true;
        $status = false;
        $payment = TermPaymentDate::where(['merchant_id' => $mid, 'term_id' => $tid, 'id' => $id, 'status' => TermPaymentDate::ACHNotPaid])->first();
        if ($payment) {
            $today = Carbon::now()->tz('America/New_York');
            $next_working_day = PayCalc::getWorkingDay($today->clone()->addDay()->toDateString());
            $cutoff_time = new Carbon($today->toDateString().' 14:45:00', 'America/New_York');
            $payment_date = $payment->payment_date;
            $date_formatted = FFM::date($payment_date);
            if ($payment_date < $next_working_day) {
                $deletable = false;
            } elseif ($payment_date > $next_working_day) {
                $deletable = true;
            } elseif ($payment_date == $next_working_day) {
                if ($today->lessThan($cutoff_time)) {
                    $deletable = true;
                } else {
                    $deletable = false;
                }
            }
            if ($deletable) {
                $payment->delete();
                $term = MerchantPaymentTerm::find($tid);
                if ($term->payments()->count() > 0) {
                    $term_end_at = $term->payments()->max('payment_date');
                    if ($term->end_at != $term_end_at) {
                        $term->end_at = $term_end_at;
                    }

                    $term->pmnts = $term->pmnts - 1;
                    $term->actual_payment_left = $term->actual_payment_left - 1;
                    $term->update();
                } else {
                    $term->delete();
                }
                $end_date = Merchant::where('id', $mid)->value('payment_end_date');
                if ($end_date == $payment_date) {
                    $payment_end_date = TermPaymentDate::where('merchant_id', $mid)->max('payment_date');
                    $update_end_date = Merchant::where('id', $mid)->update(['payment_end_date' => $payment_end_date]);
                }
                $status = true;
                $message = "ACH Schedule on $date_formatted deleted successfully.";
            } else {
                $message = "Cannot delete this ACH Schedule on $date_formatted.";
            }
        } else {
            $message = 'Cannot find this ACH Schedule.';
        }
        $return = ['status' => $status, 'message' => $message, 'id' => $id];

        return $return;
    }

    public function setPaymentTerms()
    {
        $merchant_end_date = Merchant::whereNotNull('payment_end_date')->update(['payment_end_date' => null, 'payment_pause_id' => null]);
        $delete_terms = DB::table('merchant_payment_terms')->truncate();
        $delete_term_payments = DB::table('term_payment_dates')->truncate();
        $merchants_with_invest = MerchantUser::distinct('merchant_user.merchant_id')->pluck('merchant_id')->toArray();
        $merchants = Merchant::select('id', 'pmnts', 'advance_type', 'payment_amount', 'date_funded', 'created_at', 'lender_id', 'first_payment', 'payment_end_date')->has('paymentTerms', '<=', 0)->whereIn('id', $merchants_with_invest)->get();
        if ($merchants) {
            foreach ($merchants as $merchant) {
                $terms = $this->merchant->createTerms($merchant);
                echo "\nMerchant-$merchant->id -> Term-$terms->id";
            }

            return true;
        }

        return false;


    }

    public function resumePayment($request)
    {
        $unwanted_sub_status = config('custom.unwanted_sub_status');
        $validator = Validator::make($request->all(), ['merchant_id' => 'required|exists:merchants,id']);
        if ($validator->fails()) {
            $error = '';
            foreach (array_values($validator->messages()->toArray()) as $msg) {
                $error = $error.implode(' ', $msg);
            }
            return response()->json(['status' => 0, 'msg' => $error]);
        }
        $id = $request->merchant_id;
        $merchant = Merchant::find($id);
        if (in_array($merchant->sub_status_id, $unwanted_sub_status)) {
            return response()->json(['status' => 0, 'msg' => 'Invalid Sub status']);
        }
        if ($merchant && $merchant->payment_pause_id) {
            $payment_pause_id = $merchant->payment_pause_id;
            $today = Carbon::now()->toDateString();
            $yesterday = Carbon::now()->subDay()->toDateString();
            $paused_date = Carbon::parse($merchant->paymentPause->paused_at)->toDateString();
            if ($today >= $paused_date) {
                if ($today > $paused_date) {
                    $terms = $merchant->paymentTerms()->whereHas('payments', function (EloquentBuilder $query) use ($yesterday, $paused_date) {
                        $query->whereBetween('payment_date', [$paused_date, $yesterday]);
                    })->get();
                    foreach ($terms as $term) {
                        $paused_dates = [];
                        $payments = $term->payments()->whereBetween('payment_date', [$paused_date, $yesterday])->where('status', TermPaymentDate::ACHNotPaid);
                        $paymentsUpdate = $payments->update(['status' => TermPaymentDate::ACHCancelled]);
                        $payments = $payments->get();
                        foreach ($payments as $payment) {
                            if ($term->advance_type == 'weekly_ach' || $term->advance_type == 'biweekly_ach' || $term->advance_type == 'monthly_ach') {
                                $next_payment = $term->payments()->where('payment_date', '>', $payment->payment_date)->orderBy('payment_date')->first();
                                if ($next_payment && $next_payment->payment_date > $today) {
                                    $next_payment_date = PayCalc::getWorkingDay($next_payment->payment_date);
                                    if ($next_payment_date < $payment->payment_date) {
                                        $payment->update(['payment_date' => $next_payment_date, 'status' => 0]);
                                        continue;
                                    }
                                }
                            }
                            $paused_dates[] = $payment;
                        }
                        $paused_dates_count = count($paused_dates);
                        if ($paused_dates_count) {
                            if ($term->advance_type == 'weekly_ach') {
                                $start_at = Carbon::parse($merchant->payment_end_date)->addWeek()->toDateString();
                            } elseif ($term->advance_type == 'biweekly_ach') {
                                $start_at = Carbon::parse($merchant->payment_end_date)->addWeeks(2)->toDateString();
                            } elseif ($term->advance_type == 'monthly_ach') {
                                $start_at = Carbon::parse($merchant->payment_end_date)->addMonth()->toDateString();
                            } else {
                                $start_at = Carbon::parse($merchant->payment_end_date)->addDay()->toDateString();
                            }
                            $start_at = PayCalc::getWorkingDay($start_at);
                            $end_at = $this->merchant->getEndDate($start_at, $term->advance_type, $paused_dates_count);
                            foreach ($paused_dates as $paused_payment) {
                                $paused_payment->update(['pause_id' => $payment_pause_id, 'status' => -1]);
                            }
                            $term->pmnts = $term->pmnts - $paused_dates_count;
                            $term->update();
                            $payment_term = MerchantPaymentTerm::create(['merchant_id' => $merchant->id, 'advance_type' => $term->advance_type, 'pmnts' => $paused_dates_count, 'payment_amount' => $term->payment_amount, 'actual_payment_left' => $paused_dates_count, 'start_at' => $start_at, 'end_at' => $end_at, 'status' => 0]);
                            $term_dates = $this->merchant->storeTermdates($payment_term);
                            $merchant->payment_end_date = $end_at;
                            $merchant->update();
                        }
                    }
                }
                $resumed_by = $request->user()->name;
                $payment_resume = $this->merchant->resumePayment($merchant, $resumed_by);
                if ($payment_resume) {
                    return response()->json(['status' => 1, 'msg' => 'Payment Resumed']);
                }
            }
            return response()->json(['status' => 0, 'msg' => 'Paused Date is greater than Today.']);
        }

        return response()->json(['status' => 0, 'msg' => 'Invalid Input']);

    }

    public function setPaymentTermForWhomDontHave()
    {
        $merchants_with_invest = MerchantUser::distinct('merchant_user.merchant_id')->pluck('merchant_id')->toArray();
        $merchants = Merchant::select('id', 'pmnts', 'advance_type', 'payment_amount', 'date_funded', 'created_at', 'lender_id', 'first_payment', 'payment_end_date')->doesntHave('paymentTerms')->whereIn('id', $merchants_with_invest)->get();
        if ($merchants) {
            foreach ($merchants as $merchant) {
                $terms = $this->merchant->createTerms($merchant);
                echo "\nMerchant-$merchant->id -> Term-$terms->id";
            }

            return true;
        }

        return false;

    }



}


