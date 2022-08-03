<?php

namespace App\Helpers;

use App\Http\Controllers\Controller;
use App\Http\Requests\DynamicReportRequest;
use App\Merchant;
use App\MerchantDetails;
use App\MessageType;
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

class DynamicReportHelper
{
	public function __construct(Builder $tableBuilder)
    {
        $this->tableBuilder = $tableBuilder;
    }

    public function getAllDynamicReports()
    {
    	$page_title = 'Dynamic Reports';
        $this->tableBuilder->ajax(route('admin::dynamic-report.data'));
        $this->tableBuilder->parameters([
            'responsive' => true,
            'autoWidth' => false,
            'processing' => true,
            'aaSorting' => [],
            'pagingType' => 'input',
            'serverSide' => false,
            'stateSave' => true,
        ]);
         $this->tableBuilder->parameters(['fnCreatedRow' => "function (nRow, aData, iDataIndex) {\n           var info = this.dataTable().api().page.info();\n           var page = info.page;\n           var length = info.length;\n           var index = (page * length + (iDataIndex + 1));\n           $('td:eq(0)', nRow).html(index).addClass('txt-center');\n         }", 'pagingType' => 'input']);
        $this->tableBuilder = $this->tableBuilder->columns([
            [
                'data' => '_id',
                'name' => '_id',
                'title' => '#',
                'searchable' => false,
                'orderable' => false,
            ],
            ['data' => 'name', 'name' => 'name', 'title' => 'Name'],
            ['data' => 'description', 'name' => 'description', 'title' => 'Description'],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Created at'],
            ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false],
        ]);
        $all_fields = DynamicReport::all_fields();
        $filter_keys=Merchant::getTableColumns();

        return ['page_title'=>$page_title,'tableBuilder'=>$this->tableBuilder,'all_fields'=>$all_fields,'filter_keys'=>$filter_keys];

    }

    public function filterOperator($request)
    {
          $filter_key = $request->filter_key;
          $operator=[];
          $key_search=[];
          try{

         if(!$filter_key) throw new Exception("somthing went for filter keys", 1);
         
              if (str_contains($filter_key, 'date')) {

                 $operator=['=','>','<','>=','<=','!=','LIKE'];
                
               }else
               {
                    $operator=['=','!='];

               }

               if($filter_key=='state_id')
               {
                   $key_search=States::pluck('state','id')->toArray();

               }

               if($filter_key=='sub_status_id')
               {
                   $key_search=SubStatus::pluck('name','id')->toArray();

               }

               if($filter_key=='industry_id')
               {
                   $key_search=Industries::pluck('name','id')->toArray();

               }

               if($filter_key=='label')
               {
                   $key_search=Label::pluck('name','id')->toArray();

               }

               if($filter_key=='source_id')
               {
                   $key_search=Sources::pluck('name','id')->toArray();

               }
               if($filter_key=='sub_status_flag')
               {
                   $key_search=SubStatusFlag::pluck('name','id')->toArray();

               }
               if($filter_key=='lender_id')
               {
                   $key_search=User::leftjoin('user_has_roles','user_has_roles.model_id','users.id')->where('user_has_roles.role_id',User::LENDER_ROLE)->pluck('users.name','users.id')->toArray();

               }
              if($filter_key=='advance_type')
                {
                      $key_search=Merchant::getAdvanceTypes();
                }

                $result['status']=1;
                $result['msg']='success';
                $result['operator']=$operator;
                $result['key_search']=$key_search;


            }
            catch (\Exception $e) {

                $result['status']=0;
                $result['msg']='failed';
           
        }

        return $result;

         
          
    }

    public function getRowData()
    {
    	$data = DynamicReport::get();
        $data = $data->toArray();
        session_set('all_users', User::select('id', 'name')->get()->getDictionary());

        return \DataTables::collection($data)->editColumn('name', function ($data) {
            return '<a href="'.route('admin::dynamic-report.show', $data['_id']).'">'.$data['name'].'</a>';
        })->addColumn('action', function ($data) {
            $return = '';

            $return .= '<a class="btn btn-xs btn-primary" href="'.route('admin::dynamic-report.show', $data['_id']).'"> <i class="glyphicon glyphicon-list-alt"></i>  </a>';
           
            $return .= '<a href="'.route('admin::dynamic-report.edit',$data['_id']).'" class="btn btn-xs btn-secondary edit_report"><i class="glyphicon glyphicon-edit"></i> </a>';

            $return .= '<a data-id="'.$data['_id'].'" class="btn btn-xs btn-danger delete_report"><i class="glyphicon glyphicon-trash"></i> </a>';

            return $return;
        })->editColumn('created_at', function ($data) {
            $created_date = 'Created On '.FFM::datetime($data['created_at']).' by '.get_user_name_with_session($data['created_by']);

            return "<a title='$created_date'>".FFM::datetime($data['created_at']).'</a>';
        })->rawColumns(['name', 'action', 'created_at'])->make(true);

    }

    public function storeReport($request)
    {
           try
           {

            $dr = new DynamicReport();
            $dr->name = $request->name;
            $dr->description = $request->description;
            $dr->field_keys = json_encode($request->field_keys, true);
            $dr->filter_keys = json_encode($request->filter_keys, true);
            $dr->filter_operator = json_encode($request->filter_operator, true);
            $dr->key_search = json_encode($request->key_search, true);
            $dr->created_at=date('Y-m-d');
            $dr->created_by=Auth::user()->name;
            $dr->user_type = 3;
            $dr->save();
            if(!$dr) throw new Exception("Error Processing Request", 1);
            $result['status']=1;
            $request->session()->flash('message', 'Report created!');

           }catch (\Exception $e) {
           $result['status']=0;
        }

        return $result;
    	  
            
    }
    public function updateReport($id,$request)
    {
        try
        {
            $dr = DynamicReport::findOrFail($id);
            if(!$dr) throw new Exception("somthing went wrong for updating ", 1);
            
            $dr->name = $request->name;
            $dr->description = $request->description;
            $dr->field_keys = json_encode($request->field_keys, true);
            $dr->filter_keys = json_encode($request->filter_keys, true);
            $dr->filter_operator = json_encode($request->filter_operator, true);
            $dr->key_search = json_encode($request->key_search, true);
            $dr->created_at=date('Y-m-d');
            $dr->created_by=Auth::user()->name;
            $dr->user_type = 3;
            $dr->save();
            $result['status']=1;
            $request->session()->flash('message', 'Report updated!');

        } catch (\Exception $e) {
           $result['status']=0;
        }

        return $result;
    	
    }

    public function showReport($id)
    {
    	session_set('all_merchants', Merchant::select('id', 'name')->get()->getDictionary());
        $filter_keys=Merchant::getTableColumns();
        $dr = DynamicReport::find($id);

        $all_fields = DynamicReport::all_fields();
        $table_columns = [['data' => 'id', 'name' => 'id', 'title' => '#', 'searchable' => false, 'orderable' => false,],
        ];

        foreach (json_decode($dr->field_keys) as $key) {
            //foreach (DynamicReport::all_fields() as $key => $val) {
            $table_columns[] = [
                'data' => $key,
                'name' => $key,
                'title' => changeCase($all_fields[$key]),
            ];
        }
        $page_title = $dr->name;
        $this->tableBuilder->ajax(route('admin::dynamic-report.report-data', ['id' => $id]));
        $this->tableBuilder->parameters([
            'responsive' => true,
            'autoWidth' => false,
            'processing' => true,
            'aaSorting' => [],
            'pagingType' => 'input',
        ]);
        $this->tableBuilder->parameters(['fnCreatedRow' => "function (nRow, aData, iDataIndex) {\n           var info = this.dataTable().api().page.info();\n           var page = info.page;\n           var length = info.length;\n           var index = (page * length + (iDataIndex + 1));\n           $('td:eq(0)', nRow).html(index).addClass('txt-center');\n         }", 'pagingType' => 'input']);
        $this->tableBuilder = $this->tableBuilder->columns($table_columns);
        $this->tableBuilder->parameters(['serverSide' => false]);
        $all_fields = DynamicReport::all_fields();

        return ['page_title'=>$page_title,'tableBuilder'=>$this->tableBuilder,'all_fields'=>$all_fields,'filter_keys'=>$filter_keys];

    }

    public function dynamicReportExport($id)
    {
    	  if($id)
       {
              $dr = DynamicReport::find($id);
              $all_fields = DynamicReport::all_fields();
              $fileName = $dr->name.'.csv';
              $field_keys=json_decode($dr->field_keys);
              $colunm_filter=[];
              $i=1;
             
              $excel_array=[];
              if(!empty($field_keys[0]))
              {
                  foreach ($field_keys as $key) {
                    $excel_array[0][0]='#';
                    $excel_array[0][]=changeCase($all_fields[$key]);
                    $colunm_filter[]=$key;

                  }

              }

        $filter_operator=json_decode($dr->filter_operator);
        $key_search=json_decode($dr->key_search);
        $filter_keys=json_decode($dr->filter_keys);
        $data = MerchantDetails::select($colunm_filter)->leftjoin('merchants','merchants.id','merchants_details.merchant_id');
        if(!empty($filter_keys[0]) && !empty($key_search[0]) && !empty($filter_operator[0]))
        {

        $data=$data->where(function($q) use ($filter_keys,$filter_operator,$key_search){
        
        foreach($filter_keys as $key => $value){
                 $keyword=$key_search[$key];
                 if($filter_operator[$key]=='LIKE')
                    {    
                      $q->where($value, $filter_operator[$key],  '%' . $keyword . '%');
                    }
                   else
                   {
                       $q->where($value, $filter_operator[$key],$keyword);
                   }

                }

       });

    }
       $data = $data->get()->toArray();
       $i=1;

         if(!empty($data))
         {
               foreach ($data as $key => $value) {

                    array_unshift($value,$i);
                   
                   $excel_array[$i]=$value;
                   $i++;
                   
               }

         }
         $export = new Data_arrExport($excel_array);
         return Excel::download($export,$fileName);

       }else
       {
            

       }

    }

    public function reportData($id)
    {
    	$dr = DynamicReport::find($id);
        $filter_operator=json_decode($dr->filter_operator);
        $key_search=json_decode($dr->key_search);
        $filter_keys=json_decode($dr->filter_keys);
        $data = MerchantDetails::leftjoin('merchants','merchants.id','merchants_details.merchant_id');
        if(!empty($filter_keys[0]) && !empty($key_search[0]) && !empty($filter_operator[0]))
        {
            $data=$data->where(function($q) use ($filter_keys,$filter_operator,$key_search){

                foreach($filter_keys as $key => $value){
                  $keyword=$key_search[$key];
                  if($filter_operator[$key]=='LIKE')
                    {  
                      $q->where($value, $filter_operator[$key],  '%' . $keyword . '%');
                   }
                   else
                   {
                      $q->where($value, $filter_operator[$key],$keyword);
                   }
                }
       });

    }
      $data = $data->get()->toArray();
      return \DataTables::collection($data)->make(true);

    }
    public function destroyReport()
    {
         try
         {
            if (!DynamicReport::destroy($id)) throw new Exception("Error Processing Request", 1);
            $result['status']=1;
            
         }catch (\Exception $e) {
           $result['status']=0;
        }
        return $result;
    }
   public function editDynamicReport($id)
   {
        $dr=DynamicReport::find($id);
        $page_title=$dr->name;
        $selected_operator=json_decode($dr->filter_operator);
        $selected_key_search=json_decode($dr->key_search);
        $selected_field_keys=json_decode($dr->field_keys);
        $selected_filter_keys=json_decode($dr->filter_keys);
        $all_fields = DynamicReport::all_fields();
        $filter_keys=Merchant::getTableColumns();
        $operator= ['=','>','<','>=','<=','!=','LIKE'];
        $states=States::pluck('state','id')->toArray();
        $substatus=SubStatus::pluck('name','id')->toArray();
        $industry=Industries::pluck('name','id')->toArray();
        $label=Label::pluck('name','id')->toArray();
        $source=Sources::pluck('name','id')->toArray();
        $substatusflag=SubStatusFlag::pluck('name','id')->toArray();
        $lender=User::leftjoin('user_has_roles','user_has_roles.model_id','users.id')->where('user_has_roles.role_id',User::LENDER_ROLE)->pluck('users.name','users.id')->toArray();
        $advancetype=Merchant::getAdvanceTypes();

        return ['all_fields'=>$all_fields,'filter_keys'=>$filter_keys,'selected_field_keys'=>$selected_field_keys,'tableBuilder'=>$this->tableBuilder,'dr'=>$dr,'selected_filter_keys'=>$selected_filter_keys,'selected_operator'=>$selected_operator,'page_title'=>$page_title,'operator'=>$operator,'selected_key_search'=>$selected_key_search,'states'=>$states,'substatus'=>$substatus,'industry'=>$industry,'label'=>$label,'source'=>$source,'substatusflag'=>$substatusflag,'lender'=>$lender,'advancetype'=>$advancetype];
   }





}	