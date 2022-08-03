<?php

namespace App\Http\Controllers\Investor;

use App\CompanyAmount;
use App\Document;
use App\Http\Controllers\Controller;
use App\Jobs\CommonJobs;
use App\Library\Repository\Interfaces\IMerchantRepository;
use App\Library\Repository\Interfaces\IParticipantPaymentRepository;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\Mailboxrow;
use App\Marketplace;
use App\Merchant;
use App\MerchantUser;
use App\Settings;
use App\Template;
use DateTime;
use DateTimeZone;
use FFM;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use MTB;
use PDF;
use Carbon\Carbon;
use Yajra\DataTables\Html\Builder;

class MarketplaceController extends Controller
{
    private $merchant;

    public function __construct(IRoleRepository $role, IMerchantRepository $merchant, IParticipantPaymentRepository $partPay)
    {
        $this->role = $role;
        $this->merchant = $merchant;
        $this->partPay = $partPay;
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();

            return $next($request);
        });
    }

    public function list(Request $request)
    {
        $funds = Merchant::where('marketplace_status', 1)->select('funded', 'payment_amount', 'pmnts', 'factor_rate', 'merchants.commission', 'merchants.m_mgmnt_fee', 'm_syndication_fee', 'm_s_prepaid_status', 'rtr', 'max_participant_fund', 'merchants.name as business_en_name', 'merchants.id', 'underwriting_fee', 'complete_percentage', 'max_participant_fund_per', 'marketplace_permission', 'merchant_user.status as invest_status')->where('active_status', 1)->where('merchants.sub_status_id', '=', 1)->leftJoin('merchant_user', 'merchant_user.merchant_id', 'merchants.id')->groupBy('merchants.id')->with('FundingRequests');
        if ($request->filter == 2) {
            $funds = $funds->whereRaw('max_participant_fund=funded');
        } elseif ($request->filter == 1) {
            $funds = $funds->whereRaw('max_participant_fund<funded');
        }
        $funds = $funds->get();
        if (isset($request->filter)) {
            $filter_id = $request->filter;
        } else {
            $filter_id = 0;
        }

        return view('investor.marketplace.list', compact('funds', 'filter_id'));
    }

    public function funds_request(Request $request)
    {
        $user_id = $this->user->id;
        $investor = $request->user();
        $smount = MerchantUser::where(['merchant_id' => $request->id, 'user_id' => $this->user->id])->sum('amount');
        if ($smount) {
            return redirect()->back()->with('error', 'You already funded this deal. Please contact admin if you want to make any changes.');
        }
        $merchant = Merchant::where(['id' => $request->id])->select('factor_rate', 'funded', 'date_funded', 'name', 'pmnts', 'commission', 'rtr', 'id', 'payment_amount', 'merchants.name as business_en_name', 'advance_type', 'm_syndication_fee', 'underwriting_fee', 'label', 'm_s_prepaid_status', 'max_participant_fund', 'funded')->first();
        $companies = DB::table('company_amount')->where('merchant_id', $request->id)->pluck('max_participant', 'company_id')->toArray();
        $t_max = array_sum($companies);
        $total = $request->amount_1 + $smount;
        $t_amount = $t_max + $total;
        $avilable = $merchant->max_participant_fund - $t_max;
        $rtr = $total * $request->factor_rate;
        $mgmnt_fee = ($request->mgmnt_fee / 100) * $rtr;
        $rtr_net = $rtr - $mgmnt_fee;
        $part_pay_amount = $rtr / $request->pmnts;
        $underwriting_fee = $request->underwriting_fee / 100 * $rtr;
        $m_syndication_fee_per = $request->syndication_fee;
        $underwriting_fee = $request->underwriting_fee / 100 * $total;
        $commission_amount = $request->commission / 100 * $total;
        if (trim($merchant->advance_type) == 'weekly_ach') {
            $estimated_term_months = $request->pmnts / 4;
        } else {
            $estimated_term_months = $request->pmnts / 20;
        }
        $global_syndication = 0;
        if ($request->syndication_fee != 0) {
            $global_syndication = $request->syndication_fee / 100 * ($merchant->m_s_prepaid_status == 2 ? $request->amount_1 : $rtr);
        }
        $duetotal = $total + $commission_amount + $global_syndication + $underwriting_fee;
        $share = $total * 100 / $merchant->funded;
        $go_url = '"'.\URL::to('admin/merchants/view', $merchant->id).'"';
        $admins = $this->role->allAdminUsers();
        foreach ($admins as $admin) {
            $timezone = 'America/New_York';
            $date = new DateTime(date('m/d/Y h:i A'), new DateTimeZone($timezone));
            $date_en = $date->format('m/d/Y h:i A');
            $ip_server = $_SERVER['REMOTE_ADDR'];
            if ($merchant->advance_type == 'credit_card_split') {
                $credit_card = 'Yes';
            } else {
                $credit_card = 'No';
            }
            if ($merchant->label == 1) {
                $mca = 'Yes';
            } else {
                $mca = 'No';
            }
            $pdfData = ['merchant' => $merchant->name, 'merchant_id' => $merchant->id, 'iid' => $investor->id, 'participant' => $investor->name, 'business_en_name' => $merchant->business_en_name, 'advance_type' => $merchant->advance_type, 'investor_name' => $investor->investor_name, 'merchant_date' => date('m/d/Y'), 'participant_date' => isset($investor->agreement_date) ? FFM::date($investor->agreement_date) : '', 'funded' => $merchant->funded, 'date_funded' => date('m/d/Y', strtotime($merchant->date_funded)), 'm_syndication_fee' => $global_syndication, 'm_syndication_fee_per' => $m_syndication_fee_per, 'rtr_gross' => $rtr, 'rtr_net' => $rtr_net, 'factor_rate' => $request->factor_rate, 'underwriting_fee_per' => $request->underwriting_fee, 'underwriting_fee' => $underwriting_fee, 'daily_payment' => $merchant->payment_amount, 'estimated_turns' => $merchant->pmnts, 'upfront_commission_per' => $request->commission, 'upfront_commission' => $request->commission * $total / 100, 'participant_commission' => $request->commission * $total / 100, 'management_fee_per' => $request->mgmnt_fee, 'management_fee' => $mgmnt_fee, 'participant_percent' => $share, 'participant_funded_amount' => $total, 'participant_rtr' => $rtr, 'duetotal' => $duetotal, 'user_id' => $user_id, 'pmnts' => $request->pmnts, 'date_en' => $date_en, 'server' => $ip_server, 'payment_amount' => $part_pay_amount, 'estimated_term_months' => $estimated_term_months, 'commission_amount' => $commission_amount, 'mca' => $mca, 'credit_card' => $credit_card, 'rtr' => $merchant->rtr];
            $request->session()->put('pdf_generation_data', $pdfData);

            return view('investor.marketplace.agrement_web', $pdfData);
        }
    }

    public function funds_request_pdf(Request $request)
    {
        $to_email_id = Settings::first()->pluck('email')->first();
        $to_email_id = explode(',', $to_email_id);
        $company_id = $this->user->company;
        $filter = $request->session()->get('pdf_generation_data');
        $pdfData = ['merchant' => $filter['merchant'], 'merchant_id' => $filter['merchant_id'], 'iid' => $filter['iid'], 'participant' => $filter['participant'], 'business_en_name' => $filter['business_en_name'], 'advance_type' => $filter['advance_type'], 'investor_name' => $filter['investor_name'], 'merchant_date' => $filter['merchant_date'], 'participant_date' => $filter['participant_date'], 'funded' => $filter['funded'], 'date_funded' => $filter['date_funded'], 'm_syndication_fee' => $filter['m_syndication_fee'], 'm_syndication_fee_per' => $filter['m_syndication_fee_per'], 'rtr_gross' => $filter['rtr_gross'], 'rtr_net' => $filter['rtr_net'], 'factor_rate' => $filter['factor_rate'], 'underwriting_fee_per' => $filter['underwriting_fee_per'], 'underwriting_fee' => $filter['underwriting_fee'], 'daily_payment' => $filter['daily_payment'], 'estimated_turns' => $filter['estimated_turns'], 'upfront_commission_per' => $filter['upfront_commission_per'], 'upfront_commission' => $filter['upfront_commission'], 'participant_commission' => $filter['participant_commission'], 'management_fee_per' => $filter['management_fee_per'], 'management_fee' => $filter['management_fee'], 'participant_percent' => $filter['participant_percent'], 'participant_funded_amount' => $filter['participant_funded_amount'], 'participant_rtr' => $filter['participant_rtr'], 'duetotal' => $filter['duetotal'], 'user_id' => $filter['user_id'], 'pmnts' => $filter['pmnts'], 'date_en' => $filter['date_en'], 'server' => $filter['server'], 'payment_amount' => $filter['payment_amount'], 'estimated_term_months' => $filter['estimated_term_months'], 'mca' => $filter['mca'], 'credit_card' => $filter['credit_card'], 'rtr' => $filter['rtr']];
        if (! $request->signed) {
            $request->session()->flash('flash_message', 'Please sign the agreement');

            return view('investor.marketplace.agrement_web', $pdfData);
        }
        $folderPath = public_path('signature/');
        File::deleteDirectory($folderPath);
        if (! file_exists($folderPath)) {
            File::makeDirectory($folderPath, $mode = 0777, true, true);
        }
        $image_parts = explode(';base64,', $request->signed);
        $image_type_aux = explode('image/', $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);
        $file = $folderPath.uniqid().'.'.$image_type;
        file_put_contents($file, $image_base64);
        $request = MerchantUser::insert(['merchant_id' => $filter['merchant_id'], 'amount' => $filter['participant_funded_amount'], 'user_id' => $filter['user_id'], 'mgmnt_fee' => $filter['management_fee_per'], 'syndication_fee_percentage' => $filter['m_syndication_fee_per'], 'pre_paid' => $filter['m_syndication_fee'], 'invest_rtr' => $filter['participant_rtr'], 'share' => $filter['participant_percent'], 'status' => 0, 'commission_amount' => $filter['commission_amount'], 'under_writing_fee' => $filter['underwriting_fee'], 'under_writing_fee_per' => $filter['underwriting_fee_per'], 'commission_per' => $filter['upfront_commission_per']]);
        if ($request) {
            $merchants = Merchant::select('max_participant_fund', 'funded')->where('id', $filter['merchant_id'])->first();
            $companies = DB::table('company_amount')->where('merchant_id', $filter['merchant_id'])->pluck('max_participant', 'company_id')->toArray();
            $company_amount = [];
            $reminding_per = 0;
            if ($companies) {
                foreach ($companies as $key => $value) {
                    $company_amount[$key]['company_id'] = $key;
                    if ($company_id == $key) {
                        if ($value == 0) {
                            $company_amount[$key]['max_participant'] = $filter['participant_funded_amount'];
                        } else {
                            $company_amount[$key]['max_participant'] = $value + $filter['participant_funded_amount'];
                        }
                        CompanyAmount::updateOrCreate(['company_id' => $company_amount[$key]['company_id'], 'merchant_id' => $filter['merchant_id']], ['max_participant' => $company_amount[$key]['max_participant']]);
                    } else {
                        if ($merchants->max_participant_fund == $filter['participant_funded_amount']) {
                            $company_amount[$key]['max_participant'] = 0;
                        } else {
                            $max = $filter['participant_funded_amount'] / $merchants->max_participant_fund * 100;
                            $reminding_per = 100 - $max;
                            $per = $value / $merchants->max_participant_fund * $reminding_per;
                            $company_amount[$key]['max_participant'] = $per / 100 * $merchants->max_participant_fund;
                        }
                        CompanyAmount::updateOrCreate(['company_id' => $company_amount[$key]['company_id'], 'merchant_id' => $filter['merchant_id']], ['max_participant' => $company_amount[$key]['max_participant']]);
                    }
                }
            }
            $message['content'] = '<a href='.url('admin/investors/portfolio/'.$filter['user_id']).'>'.$filter['participant'].'</a> Invested '.FFM::dollar($filter['participant_funded_amount']).' in the merchant <a href='.url('admin/merchants/view/'.$filter['merchant_id']).'>'.$filter['merchant'].'</a>';
            $message['investor'] = $filter['participant'];
            $message['title'] = 'Investment request';
            $message['merchant_name'] = $filter['merchant'];
            $message['merchant_id'] = $filter['merchant_id'];
            $message['amount'] = $filter['participant_funded_amount'];
            $message['user_id'] = $filter['user_id'];
            $message['timestamp'] = time();
            $message['status'] = 'funding_request';
            $message['subject'] = 'Funding request | Velocitygroupusa';
            $message['to_mail'] = $to_email_id;
            $message['unqID'] = unqID();
            $mailboxdb = new Mailboxrow();
            $mailboxdb->content = $message['content'];
            $mailboxdb->title = $message['title'];
            $mailboxdb->timestamp = $message['timestamp'];
            $mailboxdb->user_id = $filter['user_id'];
            $mailboxdb->permission_user = $filter['user_id'];
            $mailboxdb->save();
            try {
                $email_template = Template::where([
                    ['temp_code', '=', 'FUNDR'], ['enable', '=', 1],
                ])->first();
                if ($email_template) {
                    if ($email_template->assignees) {
                        $template_assignee = explode(',', $email_template->assignees);
                        $bcc_mails = [];
                        foreach ($template_assignee as $assignee) {
                            $role_mails = $this->role->allUserRoleData($assignee)->pluck('email')->toArray();
                            $role_mails = array_diff($role_mails, $to_email_id);
                            $bcc_mails[] = $role_mails;
                        }
                        $message['bcc'] = Arr::flatten($bcc_mails);
                    }
                    $settings = Settings::where('keys', 'admin_email_address')->first();
                    $admin_email = $settings->values;
                    $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                    dispatch($emailJob);
                    $message['bcc'] = [];
                    $message['to_mail'] = $admin_email;
                    $emailJob = (new CommonJobs($message));
                    dispatch($emailJob);
                    
                }
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }
        if (! Schema::hasTable('merchant_fund_details')) {
            Schema::create('merchant_fund_details', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('merchant_id');
                $table->integer('pmnts')->nullable();
                $table->float('factor_rate', 12, 3)->nullable();
                $table->timestamps();
            });
        }
        $merchant = DB::table('merchant_fund_details')->where(['merchant_id' => $filter['merchant_id']])->first();
        if ($merchant) {
            DB::table('merchant_fund_details')->where(['merchant_id' => $filter['merchant_id']])->update(['pmnts' => $filter['pmnts'], 'factor_rate' => $filter['factor_rate']]);
        } else {
            DB::table('merchant_fund_details')->insert(['merchant_id' => $filter['merchant_id'], 'pmnts' => $filter['pmnts'], 'factor_rate' => $filter['factor_rate']]);
        }
        $pdfData['signature'] = $file;

        return $this->fundPdf($pdfData);
    }

    public function OLD_page($value = '')
    {
        return view('investor.index');
    }

    public function OLD_index(Request $request, Builder $tableBuilder)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return MTB::investorMerchantListView($this->user->id);
        }
        $page_title = 'Investor Dashboard';
        $tableBuilder->ajax(route('investor::dashboard::index'));
        $tableBuilder->parameters(['footerCallback' => "function(t,o,a,l,m){var n=this.api(),o=table.ajax.json();$(n.column(1).footer()).html('Total');$(n.column(4).footer()).html(o.funded),$(n.column(6).footer()).html(o.rtr),$(n.column(8).footer()).html(o.participant_paid)}", 'lengthMenu' => [100, 50]]);
        $tableBuilder = $tableBuilder->columns([['className' => 'details-control', 'orderable' => false, 'data' => null, 'defaultContent' => '', 'title' => ''], ['data' => 'name', 'name' => 'name', 'title' => 'Merchant'], ['data' => 'id', 'name' => 'id', 'title' => 'MID'], ['data' => 'date_funded', 'name' => 'date_funded', 'title' => 'Date Funded'], ['data' => 'funded', 'name' => 'funded', 'title' => 'Funded', 'searchable' => false], ['data' => 'commission', 'name' => 'cmmsn', 'title' => 'CMMSN', 'orderable' => false, 'searchable' => false], ['data' => 'participant_rtr', 'name' => 'rtr', 'title' => 'RTR', 'searchable' => false], ['data' => 'factor_rate', 'name' => 'rate', 'title' => 'Rate', 'orderable' => false, 'searchable' => false], ['data' => 'participant_paid', 'name' => 'ctd', 'title' => 'CTD', 'searchable' => false], ['data' => 'complete', 'name' => 'complete', 'title' => 'Complete', 'orderable' => false, 'searchable' => false], ['data' => 'status', 'name' => 'status', 'title' => 'Status', 'orderable' => false, 'searchable' => false], ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'searchable' => false]]);
        $month = date('m', strtotime('0 month'));
        $year = date('Y', strtotime('0 month'));
        $month1 = date('m', strtotime('-1 month'));
        $year1 = date('Y', strtotime('-1 month'));
        $month2 = date('m', strtotime('-2 month'));
        $year2 = date('Y', strtotime('-2 month'));
        $month3 = date('m', strtotime('-3 month'));
        $year3 = date('Y', strtotime('-3 month'));
        $month4 = date('m', strtotime('-4 month'));
        $year4 = date('Y', strtotime('-4 month'));
        $userId = $this->user->id;
        $chart_data['0']['funded'] = Merchant::whereHas('investments', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->whereMonth('date_funded', '=', $month)->whereYear('date_funded', '=', $year)->sum('funded');
        $chart_data['1']['funded'] = Merchant::whereHas('investments', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->whereMonth('date_funded', '=', $month1)->whereYear('date_funded', '=', $year1)->sum('funded');
        $chart_data['2']['funded'] = Merchant::whereHas('investments', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->whereMonth('date_funded', '=', $month2)->whereYear('date_funded', '=', $year2)->sum('funded');
        $chart_data['3']['funded'] = Merchant::whereHas('investments', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->whereMonth('date_funded', '=', $month3)->whereYear('date_funded', '=', $year3)->sum('funded');
        $chart_data['4']['funded'] = Merchant::whereHas('investments', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->whereMonth('date_funded', '=', $month4)->whereYear('date_funded', '=', $year4)->sum('funded');
        $chart_data['0']['ctd_month'] = Merchant::whereHas('investments', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->whereMonth('date_funded', '=', $month)->whereYear('date_funded', '=', $year)->sum('ctd');
        $chart_data['1']['ctd_month'] = Merchant::whereHas('investments', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->whereMonth('date_funded', '=', $month1)->whereYear('date_funded', '=', $year1)->sum('ctd');
        $chart_data['2']['ctd_month'] = Merchant::whereHas('investments', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->whereMonth('date_funded', '=', $month2)->whereYear('date_funded', '=', $year2)->sum('ctd');
        $chart_data['3']['ctd_month'] = Merchant::whereHas('investments', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->whereMonth('date_funded', '=', $month3)->whereYear('date_funded', '=', $year3)->sum('ctd');
        $chart_data['4']['ctd_month'] = Merchant::whereHas('investments', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->whereMonth('date_funded', '=', $month4)->whereYear('date_funded', '=', $year4)->sum('ctd');
        $chart_data['0']['rtr_month'] = Merchant::whereHas('investments', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->whereMonth('date_funded', '=', $month)->whereYear('date_funded', '=', $year)->sum('rtr');
        $chart_data['1']['rtr_month'] = Merchant::whereHas('investments', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->whereMonth('date_funded', '=', $month1)->whereYear('date_funded', '=', $year1)->sum('rtr');
        $chart_data['2']['rtr_month'] = Merchant::whereHas('investments', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->whereMonth('date_funded', '=', $month2)->whereYear('date_funded', '=', $year2)->sum('rtr');
        $chart_data['3']['rtr_month'] = Merchant::whereHas('investments', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->whereMonth('date_funded', '=', $month3)->whereYear('date_funded', '=', $year3)->sum('rtr');
        $chart_data['4']['rtr_month'] = Merchant::whereHas('investments', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->whereMonth('date_funded', '=', $month4)->whereYear('date_funded', '=', $year4)->sum('rtr');
        $chart_data = array_reverse($chart_data);

        return view('investor.dashboard.index', compact('page_title', 'tableBuilder', 'chart_data'));
    }

    public function OLD_openItems(Request $request, Builder $tableBuilder)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return MTB::investorMerchantoOpenList($this->user);
        }
        $page_title = 'Open items';
        $tableBuilder->ajax(route('investor::openitems'));
        $tableBuilder = $tableBuilder->columns([['data' => 'name', 'name' => 'name', 'title' => 'Merchant'], ['data' => 'id', 'name' => 'id', 'title' => 'MID', 'searchable' => false], ['data' => 'date_funded', 'name' => 'date_funded', 'title' => 'Date Funded', 'searchable' => false], ['data' => 'pending_amount', 'name' => 'funded', 'title' => 'Pending amount', 'searchable' => false], ['data' => 'fund_collect_status', 'name' => 'funded', 'title' => 'FUNDS COLLECTED', 'searchable' => false], ['data' => 'signed_addenum', 'name' => 'funded', 'title' => 'SIGNED ADDENDUM', 'searchable' => false]]);

        return view('investor.dashboard.open-items', compact('page_title', 'tableBuilder'));
    }

    public function OLD_view(Request $request, Builder $tableBuilder, $id)
    {
        if ($merchant = $this->merchant->findIfBelongsToUser($id, $this->user->id)) {
            if ($request->ajax() || $request->wantsJson()) {
                return MTB::investorMerchantDetailsView($id, $this->user);
            }
            $page_title = 'Investor Dashboard';
            $tableBuilder->ajax(route('investor::dashboard::view', ['id' => $id]));
            $payments = DB::table('participent_payments')->where('merchant_id', $id)->get();
            $total_mgmnt_paid = 0;
            $paid_to_participant = 0;
            $ctd_sum = 0;
            foreach ($payments as $key => $value) {
                $total_mgmnt_paid = $total_mgmnt_paid + $value->mgmnt_fee;
                $total_syndication_fee = $total_syndication_fee + $value->syndication_fee;
                $paid_to_participant = $paid_to_participant + $value->amount;
                $ctd_sum = $ctd_sum + $value->total_payment;
            }
            $paid_to_participant = FFM::dollar($paid_to_participant);
            $balance = FFM::dollar($merchant->rtr - $ctd_sum);
            $total_mgmnt_paid = FFM::dollar($total_mgmnt_paid);
            $total_syndication_fee = FFM::dollar($total_syndication_fee);
            $tableBuilder = $tableBuilder->columns([['data' => 'payment_date', 'name' => 'payment_date', 'title' => 'Date Settled'], ['data' => 'merchant', 'name' => 'name', 'title' => 'Merchant', 'orderable' => false, 'searchable' => false], ['data' => 'total_payment', 'name' => 'total_payment', 'title' => 'Total Payment', 'searchable' => false], ['data' => 'participant_share', 'name' => 'participant_share', 'title' => 'Participant Share ', 'searchable' => false], ['data' => 'mgmnt_fee', 'name' => 'mgmnt_fee', 'title' => 'MGMNT FEE', 'searchable' => false], ['data' => 'syndication_fee', 'name' => 'syndication_fee', 'title' => 'MGMNT FEE', 'searchable' => false], ['data' => 'amount', 'name' => 'amount', 'title' => 'TO PARTICIPANT', 'searchable' => false], ['data' => 'transaction_type', 'name' => 'transaction_type', 'title' => 'Transaction Type', 'searchable' => false]]);
            $month = date('m', strtotime('0 month'));
            $year = date('Y', strtotime('0 month'));
            $month1 = date('m', strtotime('-1 month'));
            $year1 = date('Y', strtotime('-1 month'));
            $month2 = date('m', strtotime('-2 month'));
            $year2 = date('Y', strtotime('-2 month'));
            $month3 = date('m', strtotime('-3 month'));
            $year3 = date('Y', strtotime('-3 month'));
            $chart_data['0']['total_payment']['1'] = DB::table('participent_payments')->where('merchant_id', $id)->whereMonth('payment_date', '=', $month)->whereYear('payment_date', '=', $year)->sum('total_payment');
            $chart_data['1']['total_payment']['1'] = DB::table('participent_payments')->where('merchant_id', $id)->whereMonth('payment_date', '=', $month1)->whereYear('payment_date', '=', $year1)->sum('total_payment');
            $chart_data['2']['total_payment']['1'] = DB::table('participent_payments')->where('merchant_id', $id)->whereMonth('payment_date', '=', $month2)->whereYear('payment_date', '=', $year2)->sum('total_payment');
            $chart_data['3']['total_payment']['1'] = DB::table('participent_payments')->where('merchant_id', $id)->whereMonth('payment_date', '=', $month3)->whereYear('payment_date', '=', $year3)->sum('total_payment');
            $chart_data['0']['total_payment']['2'] = DB::table('participent_payments')->where('transaction_type', 99988)->where('merchant_id', $id)->whereMonth('payment_date', '=', $month)->whereYear('payment_date', '=', $year)->sum('total_payment');
            $chart_data['1']['total_payment']['2'] = DB::table('participent_payments')->where('transaction_type', 99988)->where('merchant_id', $id)->whereMonth('payment_date', '=', $month1)->whereYear('payment_date', '=', $year1)->sum('total_payment');
            $chart_data['2']['total_payment']['2'] = DB::table('participent_payments')->where('transaction_type', 99988)->where('merchant_id', $id)->whereMonth('payment_date', '=', $month2)->whereYear('payment_date', '=', $year2)->sum('total_payment');
            $chart_data['3']['total_payment']['2'] = DB::table('participent_payments')->where('transaction_type', 99988)->where('merchant_id', $id)->whereMonth('payment_date', '=', $month3)->whereYear('payment_date', '=', $year3)->sum('total_payment');
            $chart_data = array_reverse($chart_data);

            return view('investor.dashboard.details', compact('page_title', 'tableBuilder', 'merchant', 'total_mgmnt_paid', 'paid_to_participant', 'ctd_sum', 'chart_data', 'balance'));
        }
    }

    public function listdocs($mid, Request $request, Builder $tableBuilder)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return MTB::marketPlaceDocumentsView($mid);
        }
        $page_title = 'Investor Dashboard';
        $tableBuilder = $tableBuilder->columns(MTB::marketPlaceDocumentsView($mid, true));
        $tableBuilder->parameters(['sDom' => 't']);

        return view('investor.marketplace.documents', compact('page_title', 'tableBuilder'));
    }

    public function viewDoc($mid, $docid)
    {
        if ($document = Document::find($docid)) {
            $fileName = Storage::disk('s3')->temporaryUrl($document->file_name,Carbon::now()->addMinutes(2));
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);
            // if (in_array($ext, FFM::viewableDocExtensions())) {
                // return view('investor.marketplace.viewDocument', compact('fileName', 'ext'));
            // } else {
                $headers = ['Content-Description' => 'File Transfer', 'Content-Disposition' => "attachment; filename=$document->file_name", 'filename' => $document->file_name];
                $file = Storage::disk('s3')->get($document->file_name);

                return response($file)->withHeaders($headers);
            // }
        }
    }

    public function request_pdf(Request $request)
    {
        $pdf = PDF::loadView('investor.marketplace.request_pdf');

        return $pdf->stream('borrower_apllication.pdf');
    }

    private function fundPdf($data)
    {
        $pdf = PDF::loadView('investor.marketplace.fund_pdf', $data);
        $filepaths3 = 'Agreement_'.uniqid().'.pdf';
        $mid = $data['merchant_id'];
        $iid = $data['iid'];
        $fileName = 'marketplace/9332/92'.$filepaths3;
        $data = ['document_type_id' => 1, 'merchant_id' => $mid, 'investor_id' => $iid, 'title' => 'Participation Agreement', 'file_name' => $fileName, 'status' => 1];
        Document::create($data);
        $storge = Storage::disk('s3')->put($fileName, $pdf->output(), config('filesystems.disks.s3.privacy'));

        return $pdf->stream('fund_pdf.pdf');
    }
}
