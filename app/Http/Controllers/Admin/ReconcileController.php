<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\Library\Repository\Interfaces\IUserRepository;
use App\Reconciles;
use App\Merchant;
use FFM;
use App\User;
use Form;
use MTB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\Html\Builder;
use App\Exports\Data_arrExport;
use Maatwebsite\Excel\Facades\Excel;

class ReconcileController extends Controller
{
    protected $role;
    protected $user;

    public function __construct(IRoleRepository $role, IUserRepository $user)
    {
        $this->role = $role;
        $this->user = $user;
    }

    public function index(Builder $tableBuilder)
    {
        $page_title = 'Branch Manager';
        $tableBuilder->ajax(route('admin::reconcile::data'));
        $tableBuilder->parameters(['responsive' => true, 'autoWidth' => false, 'processing' => true, 'aaSorting' => []]);
        $tableBuilder = $tableBuilder->columns([['className' => 'details-control', 'data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'orderable' => false, 'defaultContent' => '', 'title' => 'No'], ['data' => 'name', 'name' => 'name', 'title' => 'Name'], ['data' => 'email', 'name' => 'email', 'title' => 'Email'], ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Created At'], ['data' => 'updated_at', 'name' => 'updated_at', 'title' => 'Updated At'], ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false]]);

        return view('admin.reconcile.index', compact('page_title', 'tableBuilder'));
    }

    public function lcreate(Request $request, $id = null)
    {
        if ($request->days && $id) {
            $title = 'Fund';
            $days = $request->days;
            $days_arr = explode(',', $days);
            $exising_days = DB::table('reconciles')->whereIn('day', $days_arr)->where('lender_id', $id)->pluck('day');
            foreach ($exising_days as $key => $value) {
                if (($key = array_search($value, $days_arr)) !== false) {
                    unset($days_arr[$key]);
                }
            }
            $days = implode(',', $days_arr);
            $lender = DB::table('users')->select('id', 'name')->where('id', $id)->first();
            $amount = DB::table('participent_payments')->join('merchants', 'participent_payments.merchant_id', 'merchants.id')->where('merchants.lender_id', $id)->whereIn('payment_date', $days_arr)->sum('final_participant_share');
            $vp_amount = DB::table('participent_payments')->join('merchants', 'participent_payments.merchant_id', 'merchants.id')->where('merchants.lender_id', $id)->whereIn('payment_date', $days_arr)->join('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id')->join('users', 'payment_investors.user_id', 'users.id')->where('users.creator_id', 58)->sum('payment_investors.participant_share');

            return view('admin.reconcile.lcreate', compact('days', 'lender', 'amount', 'exising_days', 'title', 'vp_amount'));
        } else {
            return redirect()->back()->withErrors('Please select validate days before you select lender. ');
        }
    }

    public function create($id = null)
    {
        $page_title = 'Create Reconcile Merchant';
        $action = 'create';
        $lenders2 = $this->role->allLenders();
        foreach ($lenders2 as $key => $value1) {
            $lenders[$value1->id] = $value1->name;
        }

        return view('admin.reconcile.create', compact('lenders', 'page_title', 'action'));
    }

    public function store(Request $request)
    {
        $batch = time();
        $days_arr = explode(',', $request->new_days);
        foreach ($days_arr as $day) {
            Reconciles::insert(['lender_id' => $request->lender, 'batch' => $batch, 'amount' => $request->total_amount, 'actual_amount' => $request->actual_amount, 'day' => $day]);
        }
        try {
            $request->session()->flash('message', 'Reconciled :'.$request->new_days);

            return redirect()->to('admin/reconcile/create');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function edit(Request $request, $id)
    {
        try {
            ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
            $userId = $request->user()->id;
            if ($branch_manager = $this->user->findBranchManager($id)) {
                $page_title = 'Edit Branch Manager';
                if (empty($permission)) {
                    $branchAccess = User::where('id', $id)->where('creator_id', $userId)->first();
                    if (empty($branchAccess)) {
                        return view('admin.permission_denied');
                    } else {
                    }
                }
                $action = 'edit';

                return view('admin.reconcile.create', compact('page_title', 'branch_manager', 'action'));
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function update(Requests\AdminUpdateBranchManagerRequest $request, $id)
    {
        try {
            if ($this->user->updateBranchManager($id, $request)) {
                $request->session()->flash('message', 'Branch Manager Updated.');
            }

            return redirect()->back();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function delete(Request $request, $id)
    {
        try {
            $delete = \DB::table('reconciles')->where('batch', $id)->delete();
            if ($delete) {
                $request->session()->flash('message', 'reconciles deleted');

                return redirect()->back();
            } else {
                return redirect()->to('admin/sub_status/')->withErrors('Cannot delete sub-status, already referred !');
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }


       public function reconcilationRequest(Request $request, Builder $tableBuilder)
    {
        $page_title = 'Reconciliation Request';
        ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        if (empty($permission)) {
            $investor = $this->role->allInvestors();
            $subadmininvestor = $investor->whereIn('company', $userId);
            foreach ($subadmininvestor as $key1 => $value) {
                $subinvestors[] = $value->id;
            }
        }
        if ($request->ajax() || $request->wantsJson()) {
            return \MTB::ReconcilationRequest($request->merchants, $request->reconciliation_status);
        }
        $tableBuilder->ajax(['url' => route('admin::merchants::reconcilation-request'), 'data' => 'function(d){d.merchants = $("#merchants").val();d.reconciliation_status = $("#reconciliation_status").val(); }']);
        $tableBuilder->parameters(['responsive' => true, 'autoWidth' => false, 'processing' => true, 'aaSorting' => [], 'pagingType' => 'input']);
        $tableBuilder = $tableBuilder->columns([['className' => 'details-control', 'orderable' => false, 'searchable' => false, 'name' => 'DT_RowIndex', 'data' => 'DT_RowIndex', 'defaultContent' => '', 'title' => '#'], ['data' => 'name', 'name' => 'name', 'title' => 'Merchant'], ['data' => 'reconciliation_status', 'name' => 'reconciliation_status', 'title' => 'Reconciliation'], ['data' => 'date_funded', 'name' => 'date_funded', 'title' => 'Funded Date'], ['data' => 'days', 'name' => 'days', 'title' => 'Days'], ['data' => 'ip', 'name' => 'ip', 'title' => 'Ip address'], ['data' => 'date', 'name' => 'date', 'title' => 'Date of response']]);
        $merchant = Merchant::where('active_status', 1);
        if (empty($permission)) {
            $merchant->whereHas('investmentData', function ($q) use ($subinvestors, $permission) {
                if (empty($permission)) {
                    $q->whereIn('user_id', $subinvestors);
                }
            });
        }
        $merchants = $merchant->pluck('name', 'id');
        $reconciliation_status = ['yes' => 'Yes', 'no' => 'No'];

        return view('admin.merchants.reconcilation_request', compact('page_title', 'tableBuilder', 'merchants', 'reconciliation_status'));
    }

    public function reconciliationRequestDownload(Request $request)
    {
        $fileName = 'Reconciliation request '.FFM::datetimeExcel(date('Y-m-d H:i:s')).'.csv';
        $details = \MTB::reconciliationRequestDetails($request->merchants, $request->reconciliation_status);
        $result = $details->get()->toArray();
        $excel_array[] = ['No', 'Merchant', 'Reconciliation', 'Funded Date', 'Days', 'IP address', 'Date of response'];
        $i = 1;
        if (! empty($result)) {
            foreach ($result as $value) {
                $excel_array[$i]['No'] = $i;
                $excel_array[$i]['Merchant'] = $value->name;
                $excel_array[$i]['Reconciliation'] = $value->reconciliation_status;
                $excel_array[$i]['Funded Date'] = FFM::date($value->date_funded);
                $excel_array[$i]['Days'] = $value->days;
                $excel_array[$i]['IP address'] = $value->ip;
                $excel_array[$i]['Date of response'] = ($value->created_at != null) ? FFM::datetime($value->created_at) : null;
                $i++;
            }
        }
        $export = new Data_arrExport($excel_array);

        return Excel::download($export, $fileName);
    }

   public function mailLog(Request $request, Builder $tableBuilder)
    {
        $page_title = 'Mail Log';
        ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        if (empty($permission)) {
            $investor = $this->role->allInvestors();
            $subadmininvestor = $investor->whereIn('company', $userId);
            foreach ($subadmininvestor as $key1 => $value) {
                $subinvestors[] = $value->id;
            }
        }
        if ($request->ajax() || $request->wantsJson()) {
            return \MTB::mailLog($request->merchants, $request->date_from, $request->date_to, $request->mail_type);
        }
        $tableBuilder->ajax(['url' => route('admin::merchants::mail-log'), 'data' => 'function(d){d.merchants = $("#merchants").val();d.mail_type = $("#mail_type").val();d.date_from = $("#date_from").val();d.date_to = $("#date_to").val(); }']);
        $tableBuilder->parameters(['responsive' => true, 'autoWidth' => false, 'processing' => true, 'aaSorting' => [], 'pagingType' => 'input']);
        $tableBuilder = $tableBuilder->columns([['className' => 'details-control', 'orderable' => false, 'searchable' => false, 'name' => 'DT_RowIndex', 'data' => 'DT_RowIndex', 'defaultContent' => '', 'title' => '#'], ['data' => 'name', 'name' => 'name', 'title' => 'Merchant'], ['data' => 'title', 'name' => 'title', 'title' => 'Title'], ['data' => 'type', 'name' => 'type', 'title' => 'Type'], ['data' => 'status', 'name' => 'status', 'title' => 'Status'], ['data' => 'failed_reason', 'name' => 'failed_reason', 'title' => 'Failed Message'], ['data' => 'to_email', 'name' => 'to_email', 'title' => 'Email'], ['data' => 'date', 'name' => 'date', 'title' => 'Created At']]);
        $merchant = Merchant::where('active_status', 1);
        $mail_types = config('custom.mail_log_types');
        if (empty($permission)) {
            $merchant->whereHas('investmentData', function ($q) use ($subinvestors, $permission) {
                if (empty($permission)) {
                    $q->whereIn('user_id', $subinvestors);
                }
            });
        }
        $merchants = $merchant->pluck('name', 'id');

        return view('admin.merchants.reconcilation_mail_log', compact('page_title', 'tableBuilder', 'merchants', 'mail_types'));
    }

    public function mailLogDownload(Request $request)
    {
        $fileName = 'Mail log '.FFM::datetimeExcel(date('Y-m-d H:i:s')).'.csv';
        $details = \MTB::mailLogDetails($request->merchants, $request->date_from, $request->date_to, $request->mail_type);
        $result = $details->get()->toArray();
        $excel_array[] = ['No', 'Merchant', 'Title', 'Type', 'Status', 'Failed Message', 'Email', 'Created At'];
        $mail_types = config('custom.mail_log_types');
        $i = 1;
        if (! empty($result)) {
            foreach ($result as $value) {
                $excel_array[$i]['No'] = $i;
                $excel_array[$i]['Merchant'] = strtoupper($value->name);
                $excel_array[$i]['Title'] = $value->title;
                $excel_array[$i]['Type'] = $mail_types[$value->type];
                $excel_array[$i]['Status'] = $value->status;
                $excel_array[$i]['Failed Message'] = $value->failed_message;
                $excel_array[$i]['Email'] = $value->to_mail;
                $excel_array[$i]['Date'] = ($value->created_at != null) ? FFM::datetime($value->created_at) : null;
                $i++;
            }
        }
        $export = new Data_arrExport($excel_array);

        return Excel::download($export, $fileName);
    }




}
