<?php

namespace App\Http\Controllers\Investor;

use App\Document;
use App\DocumentType;
use App\Http\Controllers\Controller;
use App\InvestorDocuments;
use App\InvestorTransaction;
use App\Library\Repository\Interfaces\IInvestorTransactionRepository;
use App\MerchantUser;
use App\Models\Views\InvestorAchRequestView;
use App\ParticipentPayment;
use Carbon\Carbon;
use FFM;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Builder;

class InvestorController extends Controller
{
    public function __construct(IInvestorTransactionRepository $transaction)
    {
        $this->transaction = $transaction;
        $this->vDistributions = new InvestorTransaction();
    }

    public function index(Request $request, Builder $tableBuilder)
    {
        $page_title = 'Investor documents';
        $userId = $request->user()->id;
        $tableBuilder->ajax(['url' => route('investor::get_documents')]);
        $tableBuilder->columns([['className' => 'details-control', 'orderable' => false, 'data' => 'serail_no', 'defaultContent' => '', 'title' => 'No'], ['data' => 'title', 'name' => 'title', 'title' => 'Title', 'orderable' => false, 'searchable' => false], ['data' => 'document_type', 'name' => 'document_type', 'title' => 'Document type', 'orderable' => false, 'searchable' => false], ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Upload Date'], ['data' => 'action', 'name' => 'action', 'title' => 'Action']]);

        return view('investor.investor.view_documents', compact('tableBuilder', 'page_title'));
    }

    public function getInvestorDocument(Request $request)
    {
        $userId = $request->user()->id;
        $documentTypes = DocumentType::pluck('name', 'id');
        $doc_data = InvestorDocuments::where('investor_id', $userId)->get()->toArray();
        for ($i = 0; $i < count($doc_data); $i++) {
            $doc_data[$i]['index'] = $i + 1;
        }

        return \DataTables::collection($doc_data)->addColumn('serail_no', function ($doc_data) {
            return $doc_data['index'];
        })->addColumn('document_type', function ($doc_data) use ($documentTypes) {
            $doc_type = DocumentType::where('id', '=', $doc_data['document_type_id'])->first(['name']);

            return $doc_type['name'];
        })->editColumn('created_at', function ($doc_data) {
            return Carbon::createFromFormat('Y-m-d H:i:s', $doc_data['created_at'])->format('M j, Y');
        })->addColumn('action', function ($doc_data) {
            return '&nbsp;&nbsp;'.'<a href="'.route('investor::documents_upload::view', ['iid' => $doc_data['id'], 'id' => $doc_data['investor_id']]).'" target="_blank" class="btn btn-xs btn-success">View</a>&nbsp;&nbsp;';
        })->make(true);
    }

    public function viewInvestorDocument($docid, $iid)
    {
        if ($document = Document::find($docid)) {
            return $this->viewFiles($document->file_name);
        }
    }

    private function viewFiles($fileName)
    {
        d('5');
        // $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        $headers = ['Content-Description' => 'File Transfer', 'Content-Disposition' => "attachment; filename=$fileName", 'filename' => $fileName];
        // if (in_array($ext, FFM::viewableDocExtensions())) {
            $url = Storage::disk('s3')->temporaryUrl($fileName,Carbon::now()->addMinutes(2));

            return redirect()->to($url);
        // } else {
        //     $file = Storage::disk('s3')->get($fileName);

        //     return response($file)->withHeaders($headers);
        // }
    }

    public function transaction_view(Builder $tableBuilder)
    {
        $page_title = 'Transactions';
        $tableBuilder = $tableBuilder->columns([['data' => 'serail_no', 'name' => 'serail_no', 'title' => 'Sl No'], ['data' => 'description', 'name' => 'description', 'title' => 'Description'], ['data' => 'transaction_category', 'name' => 'transaction_category', 'title' => 'Transaction Category'], ['data' => 'transaction_type', 'name' => 'transaction_type', 'title' => 'Transaction Type'], ['data' => 'amount', 'name' => 'amount', 'title' => 'Amount']]);
        $tableBuilder->parameters(['order' => false, 'footerCallback' => 'function(t,o,a,l,m){var n=this.api(),o=table.ajax.json();$(n.column(4).footer()).html(o.total)}']);
        $tableBuilder->ajax(['url' => route('investor::transactions::get_transactions'), 'data' => 'function(d){ d.start_date = $("#date_start").val(); d.end_date = $("#date_end").val();}']);

        return view('investor.investor.view_transactions', compact('tableBuilder', 'page_title'));
    }

    public function getTransactions(Request $request)
    {
        $investorId = $request->user()->id;
        if ($investorId > 0) {
            $data_arr1 = InvestorTransaction::select('transaction_type', 'amount', 'transaction_category', 'created_at')->where('investor_id', '=', $investorId)->where('status', '=', InvestorTransaction::StatusCompleted)->get()->toArray();
            $data_arr2 = MerchantUser::select('share', 'amount', 'pre_paid', 'commission_amount', 'created_at')->where('user_id', '=', $investorId)->get()->toArray();
            $data_arr3 = ParticipentPayment::select('final_participant_share', 'transaction_type', 'participent_payments.created_at')->join('payment_investors', function ($join) use ($investorId) {
                $join->on('payment_investors.participent_payment_id', '=', 'participent_payments.id');
                $join->where('user_id', '=', $investorId);
            })->get()->toArray();
            for ($i = 0; $i < count($data_arr1); $i++) {
                $data_arr1[$i]['description'] = \ITran::getLabel($data_arr1[$i]['transaction_category']);
            }
            for ($i = 0; $i < count($data_arr2); $i++) {
                if (! isset($data_arr2[$i]['transaction_category'])) {
                    $data_arr2[$i]['transaction_category'] = 4;
                }
                if (! isset($data_arr2[$i]['transaction_type'])) {
                    $data_arr2[$i]['transaction_type'] = 1;
                }
                $data_arr2[$i]['description'] = 'Amount Invested For Merchant';
            }
            for ($i = 0; $i < count($data_arr3); $i++) {
                if (! isset($data_arr3[$i]['transaction_category'])) {
                    $data_arr3[$i]['transaction_category'] = 1;
                }
                $data_arr3[$i]['amount'] = $data_arr3[$i]['final_participant_share'];
                $data_arr3[$i]['transaction_type'] = 2;
                $data_arr3[$i]['description'] = 'Payment added by merchant';
            }
            $data_arr4 = array_merge($data_arr1, $data_arr2, $data_arr3);
            $total_amount = 0;
            for ($i = 0; $i < count($data_arr4); $i++) {
                $data_arr4[$i]['index'] = $i + 1;
                if (isset($data_arr4[$i]['pre_paid'])) {
                    if ($data_arr4[$i]['pre_paid'] > 0) {
                        $data_arr4[$i]['amount'] = $data_arr4[$i]['amount'] + $data_arr4[$i]['pre_paid'];
                    }
                }
                if (isset($data_arr4[$i]['commission_amount'])) {
                    if ($data_arr4[$i]['commission_amount'] > 0) {
                        $data_arr4[$i]['amount'] = $data_arr4[$i]['amount'] + $data_arr4[$i]['commission_amount'];
                    }
                }
                if ($data_arr4[$i]['transaction_type'] == 1) {
                    $total_amount = $total_amount - $data_arr4[$i]['amount'];
                }
                if ($data_arr4[$i]['transaction_type'] == 2) {
                    $total_amount = $total_amount + $data_arr4[$i]['amount'];
                }
            }

            return \DataTables::collection($data_arr4)->addColumn('transaction_category', function ($data_arr4) {
                return \ITran::getLabel($data_arr4['transaction_category']);
            })->addColumn('amount', function ($data_arr4) {
                return \FFM::dollar($data_arr4['amount']);
            })->addColumn('description', function ($data_arr4) {
                return $data_arr4['description'];
            })->editColumn('transaction_type', function ($data_arr4) {
                if ($data_arr4['transaction_type'] == 1) {
                    return 'Debit';
                } elseif ($data_arr4['transaction_type'] == 2) {
                    return 'Credit';
                }
            })->addColumn('serail_no', function ($data_arr4) {
                return $data_arr4['index'];
            })->with('total', \FFM::dollar($total_amount))->make(true);
        }
    }

    public function InvestorAchRequest_get_list_ajax(Request $request)
    {
        try {
            $search = isset($request['search_tag']) ? $request['search_tag'] : '';
            $datas = InvestorAchRequestView::orderBy('Investor');
            if ($search) {
                $datas->where('Investor', 'like', "%{$search}%");
            }
            $datas = $datas->select(DB::raw("upper(Investor) as name"), 'investor_id as id')
            ->pluck('name','id')->toArray();
            $data = [];
            foreach ($datas as $key => $value) {
                $single['id'] = $key;
                $single['name'] = $value;
                $data[] = $single;
            }
            $prepend['id'] = 0;
            $prepend['name'] = 'All';
            $data = Arr::prepend($data, $prepend);
            $return['items'] = $data;
        } catch (Exception $e) {
            $return['result'] = $e->getMessage();
        }

        return response()->json($return);
    }
}
