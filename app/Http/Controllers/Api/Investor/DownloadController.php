<?php

namespace App\Http\Controllers\Api\Investor;

use App\Document;
use App\Exports\DefaultRateMerchantReportExport;
use App\Exports\InvestmentReport;
use App\Exports\InvestorTransactionReportExport;
use App\Exports\PaymentReportExport;
use function App\Helpers\getFileExtension;
use InvestorHelper;
use App\Http\Controllers\Api\InvestorController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Controller;
use App\Http\Resources\ErrorResource;
use App\Library\Repository\Interfaces\IMerchantRepository;
use App\Statements;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class DownloadController extends Controller
{
    protected $role;
    protected $user;

    public function __construct(IMerchantRepository $merchant)
    {
        $this->merchant = $merchant;
        $this->setDefaultAuth();
        $this->middleware(function ($request, $next) {
            $this->setDefaultAuth();

            return $next($request);
        });
    }

    private function setDefaultAuth()
    {
        if (! Auth::user()) {
            return false;
        }
        $this->user = Auth::user();
        $this->role = optional($this->user->roles()->first()->toArray())['name'] ?? '';
    }

    public function getMerchantList(Request $request)
    {
        $investorController = new InvestorController($this->merchant);
        $user = $request->user();
        $fileName = $user->name.'_'.date('m-d-Y H:i:s');
        $result = $investorController->postInvestorMerchantList($request->merge(['is_export' => 'yes']));
        $total_funded=$result['total']['funded_total'];
        $total_rtr=$result['total']['rtr_total'];
        $total_ctd=$result['total']['ctd_total'];
        $merchants = optional($result)['data'] ?? [];
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename='.Str::slug($fileName).'.csv');
        $fp = fopen('php://output', 'w');
        // fputcsv($fp, ['No', 'Merchant', 'Date Funded', 'Funded', 'Commission', 'Under Writing Fee', 'Syndication Fee', 'RTR', 'Rate', 'CTD', 'Annualized Rate', 'Complete', 'Status', 'Last Successful Payment Date']);
        fputcsv($fp, ['No', 'Merchant', 'Date Funded', 'Funded', 'Syndication Fee', 'RTR', 'Rate', 'CTD', 'Annualized Rate', 'Complete', 'Status', 'Last Successful Payment Date']);
        $i = 1;
        foreach ($merchants as $index => $merchant) {
            // fputcsv($fp, [$i, optional($merchant)['name'] ?? '', optional($merchant)['date_funded'] ?? '', optional($merchant)['amount']['value'] ?? 0, optional($merchant)['commission']['value_percent'] ?? 0, optional($merchant)['under_writing_fee']['value'] ?? 0, optional($merchant)['syndication_fee']['value'] ?? 0, optional($merchant)['invest_rtr'] ?? 0, optional($merchant)['factor_rate'] ?? 0, optional($merchant)['ctd'] ?? 0, optional($merchant)['annualized_rate'] ?? 0, optional($merchant)['complete_percentage'] ?? 0, optional($merchant)['sub_statuses_name'] ?? 0, optional($merchant)['last_payment_date'] ?? 0]);


            fputcsv($fp, [$i, optional($merchant)['name'] ?? '', optional($merchant)['date_funded'] ?? '', optional($merchant)['amount']['value'] ?? 0, optional($merchant)['syndication_fee']['value'] ?? 0, optional($merchant)['invest_rtr'] ?? 0, optional($merchant)['factor_rate'] ?? 0, optional($merchant)['ctd'] ?? 0, optional($merchant)['annualized_rate'] ?? 0, optional($merchant)['complete_percentage'] ?? 0, optional($merchant)['sub_statuses_name'] ?? 0, optional($merchant)['last_payment_date'] ?? 0]);
            $i++;
        }
       // fputcsv($fp, [NULL,NULL,NULL,$total_funded,NULL,NULL,NULL,$total_rtr,NULL,$total_ctd,NULL,NULL,NULL,NULL]);
        fputcsv($fp, [NULL,NULL,NULL,$total_funded,NULL,$total_rtr,NULL,$total_ctd,NULL,NULL,NULL,NULL]);
        exit;
    }

    public function getReport(Request $request)
    {
        $fileName = $this->user->name.'_report_'.date('Y-m-d H:i:s');
        $investorController = new InvestorController($this->merchant);
        $columns = InvestorHelper::getColumns();
        $columns = collect($columns)->map(function ($column) {
            if (! empty(optional($column)['title'] ?? '')) {
                return ['name' => optional($column)['name'] ?? '', 'title' => optional($column)['title'] ?? ''];
            }
        })->filter(function ($column) {
            return $column;
        })->toArray();
        $columns = collect($columns)->pluck('title', 'name')->toArray();
        $report = InvestorHelper::getReport($request->merge(['is_export' => 'yes']));
        $merchants = optional($report)['data'] ?? [];
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename='.Str::slug($fileName).'.csv');
        $fp = fopen('php://output', 'w');
        fputcsv($fp, array_values($columns));
        $merchants = collect($merchants)->map(function ($merchant, $index) use ($columns, $fp) {
            $data = [];
            foreach ($columns as $name => $title) {
                $value = optional($merchant)[$name] ?? '';
                $value = (is_array($value)) ? json_encode($value) : $value;
                $data[] = $value;
            }
            fputcsv($fp, $data);

            return $data;
        })->filter(function ($merchant) {
            return is_array($merchant);
        })->toArray();
        exit;
    }

    public function getStatement(Request $request, int $statementId)
    {
        $type = $request->input('type', 'pdf');
        $statement = Statements::findOrFail($statementId);
        $fileName = $statement->file_name;
        $type = ($type == 'pdf') ? 'pdf' : 'xlsx';
        $fileName .= '.'.$type;
        try {
            $file_contents = Storage::disk('s3')->get('/'.$fileName);

            return $response = response($file_contents, 200, ['Content-Type' => 'application/json', 'Content-Disposition' => 'attachment; filename="'.Str::slug($statement->file_name).'.'.$type.'"']);
        } catch (FileNotFoundException $e) {
            abort(404);

            return new ErrorResource(['message' => 'Oops! Sorry file has not found!']);
        } catch (\ErrorException $e) {
            abort(404);

            return new ErrorResource(['message' => $e->getMessage()]);
        }
    }

    public function getDocument(Request $request, int $documentId)
    {
        $document = Document::findOrFail($documentId);
        $fileName = $document->file_name;
        $extension = getFileExtension($fileName);
        try {
            $file_contents = Storage::disk('s3')->get('/'.$fileName);

            return $response = response($file_contents, 200, ['Content-Type' => 'application/json', 'Content-Disposition' => 'attachment; filename="'.Str::slug($fileName).'.'.$extension.'"']);
        } catch (FileNotFoundException $e) {
            abort(404);
        } catch (\ErrorException $e) {
            abort(404);
        }
    }

    public function getInvestmentReport(Request $request)
    {
        return Excel::download(new InvestmentReport($request->from ?? '', $request->to ?? '', $request->merchant_id ?? ''), 'InvestmentReport.xlsx');
    }

    public function getInvestorTransactionReport(Request $request)
    {
        return Excel::download(new InvestorTransactionReportExport($request->from ?? '', $request->to ?? '', $request->account_no ?? ''), 'InvestorTransactionReportExport.xlsx');
    }

    public function getDefaultRateMerchantReport(Request $request)
    {
        return Excel::download(new DefaultRateMerchantReportExport($this->merchant, $request->from ?? '', $request->to ?? '', $request->days ?? ''), 'DefaultRateMerchantReportExport.xlsx');
    }

    public function getPaymentReport(Request $request)
    {
        return Excel::download(new PaymentReportExport($this->user->id, $request->merchant_id ?? '', $request->from ?? '', $request->to ?? ''), 'PaymentReportExport.xlsx');
    }
}
