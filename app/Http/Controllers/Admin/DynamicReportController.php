<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DynamicReportRequest;
use App\Merchant;
use App\MerchantDetails;
use App\Models\DynamicReport;
use App\Template;
use App\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Yajra\DataTables\Html\Builder;
use App\SubStatus;
use App\Industries;
use App\States;
use App\Sources;
use App\Label;
use App\SubStatusFlag;
use Form;
use FFM;
use Illuminate\Support\Facades\Auth;
use function PHPUnit\Framework\countOf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Data_arrExport;
use Illuminate\Support\Facades\DB;
use DynamicReportHelper;

class DynamicReportController extends Controller
{
    public function index()
    {
        $response=DynamicReportHelper::getAllDynamicReports();
        return view('admin.dynamic-reports.index',$response);

    }

    public function filter_operator(Request $request)
    {
        try
        {
            $result=DynamicReportHelper::filterOperator($request);
            if($result['status']!=1) throw new Exception("Error Processing Request", 1);
            return response()->json(['status' => $result['status'],'operator'=>$result['operator'],'key_search'=>$result['key_search'],'msg'=>$result['msg']]);

         }catch (\Exception $e) {
           return response()->json(['status' => 0,'msg'=>$e->getMessage()]);
        }
       
    }

    public function rowData()
    {
         return DynamicReportHelper::getRowData();

    }

    public function create()
    {
        $page_title = 'Create a dynamic report';
        $action = 'Create';

        return view('admin.dynamic-reports.create', compact('page_title', 'action'));
    }

    public function store(DynamicReportRequest $request)
    {
       try
       {
            DB::beginTransaction();
            $result=DynamicReportHelper::storeReport($request);

            if($result['status']!=1) throw new Exception("Error Processing Request", 1);
            DB::commit(); 
           
       }
       catch (\Exception $e) {
           DB::rollback();
           return $e->getMessage();
        }
        return redirect()->to(route('admin::dynamic-report.index'));
    }

    public function update($id,DynamicReportRequest $request)
    {
         try
         {
            DB::beginTransaction();
            $result=DynamicReportHelper::updateReport($id,$request);
            if($result['status']!=1) throw new Exception("Error Processing Request", 1);
            DB::commit();

         }catch (\Exception $e) {
            DB::rollback();
           return $e->getMessage();
        }
        return redirect()->to(route('admin::dynamic-report.index'));

    }
    public function show(Builder $tableBuilder, $id)
    {
         $response=DynamicReportHelper::showReport($id);
         return view('admin.dynamic-reports.index',$response);
    }

    public function dynamic_report_export($id)
    {
        return DynamicReportHelper::dynamicReportExport($id);

    }

    public function report_data($id)
    {
        return DynamicReportHelper::reportData($id);
    }

    public function destroy(Request $request, $id)
    {
        try
        {
         DB::beginTransaction();    
        $result=DynamicReportHelper::destroyReport($id); 
        if($result['status']!=1) throw new Exception("Error Processing Request", 1);
         DB::commit();
        
        }catch (\Exception $e) {
           DB::rollback();
           return $e->getMessage();
        }
    }
    public function edit(Builder $tableBuilder,$id)
    {

        $response=DynamicReportHelper::editDynamicReport($id);
        return view('admin.dynamic-reports.index',$response);


    }
  
}
