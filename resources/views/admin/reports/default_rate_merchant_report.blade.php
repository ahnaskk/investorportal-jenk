@extends('layouts.admin.admin_lte')

@section('content')

<?php


$date_end = date('Y-m-d');
$date_start = date('Y-m-d', strtotime('-1 days', strtotime($date_end)));
?>


<div class="inner admin-dsh header-tp">

  <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
  <a href="#" class="help-link">
    <i class="fa fa-question-circle" aria-hidden="true"></i>
    <div class="tool-tip">Default Rate Report(Merchant)</div>     
  </a>
</div>
 {{ Breadcrumbs::render('admin::reports::default-rate-merchant-report-data') }}
<div class="col-md-12">
  <div class="box">
    <div class="box-body">

     <div class="filter-group-wrap" >
      <div class="form-box-styled" >       
         {{Form::open(['route'=>'admin::reports::def-payment-rep-export','id'=>'payment-form'])}}

       <div class="row">
        
                           <div class="col-md-6 report_rate">
 <div class="input-group">
  <div class="input-group-text">
    <span class="fa fa-industry" aria-hidden="true"></span>
  </div>
  {!! Form::select('investor_type[]',$investor_types,'',['class'=>'form-control js-investor-type-placeholder-multiple','id'=> 'investor_type', 'multiple'=>'multiple']) !!}


</div>
<span class="help-block">Investor Type </span>
</div> 
 




                        <div class="col-md-6 report_rate">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                                </div>

                                  {{Form::select('investors[]',[],'',['class'=>'form-control js-investor-placeholder-multiple ','id'=>'investors','multiple'=>'multiple'])}}

                            </div>
                            <span class="help-block">Investors </span>
                          </div> 

                          @if(!Auth::user()->hasRole(['company']))   

                             <div class="col-md-6 report_rate">
                   
                
                  <div class="input-group">
                      <div class="input-group-text">
                           <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                          </div>

                        {{Form::select('company',$companies,'',['class'=>'form-control js-company-placeholder','id'=>'company','placeholder'=>'Select Company'])}}

                            </div>
                            <span class="help-block">Company</span>           

                 
                </div> 

                 @endif 

                 <div class="col-md-6">
                                <div class="input-group check-box-wrap">

                                    <div class="input-group-text">
                                        <label class="chc">
                                            <input type="checkbox" name="velocity_owned" value="1" id="velocity_owned"/>
                                            <span class="checkmark chek-m"></span>
                                            <span class="chc-value">Click Here</span>
                                        </label>
                                    </div>
                                    <span class="help-block">Velocity Owned </span>
                                </div>
                             </div>

                  </div> 

                  <div class="row">


       <div class="col-md-6 report_rate">
                  <div class="input-group">
                      <div class="input-group-text">
                           <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                          </div>

                             {{Form::select('sub_status[]',$sub_statuses,[4,22],['class'=>'form-control js-status-placeholder-multiple','id'=>'sub_status','multiple'=>'multiple'])}}


                            </div>
                            <span class="help-block">Status</span>           

                </div>

                     <div class="col-md-6 report_rate">
                       <div class="date-star" id="payment_date">
                      <div class="col-md-6 report-input">
                        <div class="input-group">

                          <div class="input-group-text">
                            <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                          </div>
                          <input class="form-control from_date1 datepicker" autocomplete="off" id="date_start_def1" name="date_start1" placeholder="{{\FFM::defaultDateFormat('format')}}" type="text" value="{{ $date_start }}"/>
                          <input type="hidden" class="date_parse" name="date_start" id="date_start_def" value="{{ $date_start }}">
                        </div>
                        <span class="help-block">From Date</span>
                      </div>
                      <div class="col-md-6 report-input">
                        <div class="input-group">
                          <div class="input-group-text">
                           <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                         </div>
                         <input class="form-control to_date1 datepicker" autocomplete="off" id="date_end_def1" name="date_end1" placeholder="{{\FFM::defaultDateFormat('format')}}" type="text" value="{{ $date_end }}"/>
                         <input type="hidden" name="date_end" id="date_end_def" value="{{ $date_end }}" class="date_parse">
                       </div>
                       <span class="help-block">To Date</span>
                     </div>
                    </div>
                  </div>

                </div>     


                        <div class="row">

                              <div class="col-md-6 report_rate">
                            <div class="form-group">
                            <div class="input-group check-box-wrap">
                                <div class="input-group-text">
                                    <label class="chc">
                                        {{Form::checkbox('funded_date',1,null,['id'=>'funded_date'])}}
                                        <span class="checkmark chek-m"></span>
                                        <span class="chc-value">Check this</span>
                                    </label>
                                    
                                </div>
                                <span class="help-block">Filter with Funding Date </span>
                             </div>
                        </div>
                        
                        </div>


                         <div class="col-md-3">
                   
              
                  <div class="input-group">
                      <div class="input-group-text">
                           <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                          </div>

         {{Form::select('days',[0 =>'0-60',61=>'61-90',91=>'91-120',121=>'121-150',150=>'150+'],"",['class'=>'form-control','id'=>'days','placeholder'=>'Select Days'])}}

                            </div>
                            <span class="help-block">Days</span>           

                 
                </div> 
                <div class="col-md-6 report_rate">
                  <div class="input-group">
                      <div class="input-group-text">
                          <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                      </div>

                        {{Form::select('isos[]',$isos,'',['class'=>'form-control js-iso-placeholder-multiple','id'=>'isos','multiple'=>'multiple'])}}

                  </div>
                  <span class="help-block">ISO </span>
                </div> 
                

                 <!--          <div class="col-md-6 report_rate">
                          <div class="input-group">
                            <div class="input-group-text">
                              <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                            </div>

                            {{Form::select('investors[]',$investors,1,['class'=>'form-control js-investor-placeholder-multiple ','id'=>'investor_id','multiple'=>'multiple'])}}

                          </div>
                          <span class="help-block">Investors </span>
                        </div>  -->


                   


                  

                    </div>
     






                       <div class="row">





                        <div class="col-md-6  btn-wrap btn-right">
                                <!--<div class="pull-right" style="padding-bottom: 15px">

                                    {{Form::submit('Download report',['class'=>'btn btn-primary','id'=>'form_filter'])}}

                                  </div>-->
                                  <div class="btn-box">
                                    <input type="button" value="Apply Filter" class="btn btn-success" id="apply_filter"
                                    name="apply-filter">
                            @if(@Permissions::isAllow('Default Rate Merchant Report','Download')) 
                           {{Form::submit('download',['class'=>'btn btn-primary','id'=>'form_filter'])}}
                           @endif

                                  </div>
                                </div>
                              </div>


                            </div>

                            {{Form::close()}}
                          </div>

                        </div>

                        <div class="box">
                          <div class="box-body">
                            <div class="container-fluid">
                             <table class="table table-bordered" id="users-table">
                              <thead>
                                <tr>
                                  <th>Id</th>
                                  <th>Merchant</th> 
                                  <th>Funded Date</th>
                                  <th>Default Date</th>
                                  <th> Default Invested Amount</th>
                                  <th> Default RTR Amount </th>        
                                  <th> Name of ISO </th> 
                                </tr>
                              </thead>
                         <tfoot align="right">
                      <tr><th></th><th></th><th></th><th></th><th></th><th></th></tr>
                    </tfoot>
                              <tbody>
                              </tbody>
                            </table>
                          </div>

                          <!-- /.box-body -->
                        </div>
                      </div>




                    </div>

                  </div>

                  @stop

                  @section('scripts')
<script src="{{ asset ('/js/updated/moment.min.js') }}" type="text/javascript"></script>
<script src="{{ asset ('/js/updated/bootstrap-datetimepicker.min.js') }}" type="text/javascript"></script> 
<script src="{{ asset ('bower_components/datatables.net/js/jquery.dataTablesSelect.js') }}" type="text/javascript"></script>
<script src="{{ asset('/js/jquery-mask.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('/js/custom/investorSelect2.js') }}"></script> 
                  <script type="text/javascript">
                    $(document).ready( function () {
                     $.ajaxSetup({
                      headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                      }
                    });  
                     $('#users-table').DataTable({
                       processing: true,
                       serverSide: true,
                       searching : true,
                       aaSorting: [[2, 'desc']],
                       bSortable: true,
                       bRetrieve: true,
                        ordering: true,                
                       pageLength: 10, 
                       pagingType: 'input',
                       fnFooterCallback: function ( row, data, start, end, display ) {    
                        var api = this.api(),data;
                       $(api.column(0).footer()).html(this.api().ajax.json().Total);                
                       $(api.column(4).footer()).html(this.api().ajax.json().t_inv);  
                       $(api.column(5).footer()).html(this.api().ajax.json().t_rtr);                 

                     api.rows().every( function ( rowIdx, tableLoop, rowLoop ) {
                    var data = this.data();
                  ///  console.log(data);
                // ... do something with data(), or this.node(), etc
                  } ); 

         // paging type input
 
  //end   paging type

                    },                   
                       ajax: {
                        url: '/admin/reports/defaultRateMerchantReportData',
                        type: 'POST',          
                        dataType: 'json', 
                        'data': function (data) {
                          data._token = _token; 
                      //  data.investor_id = $('#investor_id').select2('val');  
                data.start_date_def = $('#date_start_def').val();  
                data.end_date_def = $('#date_end_def').val();
                data.investors= $('#investors').val();
                data.isos= $('#isos').val();
                data.company=$('#company').val();
                data.sub_status=$('#sub_status').val();
                data.days=$('#days').val();
                data.velocity_owned = $("input[name=velocity_owned]:checked").val();
                data.funded_date=($('#funded_date').is(":checked"))?1:0;//$("#funded_date").attr("checked") ? 1 : 0; //$("#funded_date").val(); 

                data.investor_type=$('#investor_type').val();

                return data;
                
                        }, 
                      },
                      columns: [   
                      { data: 'id', name: 'id','orderable': false}, 
                      { data: 'name', name: 'name','orderable': true,render:function(data, type, row){
                       return "<a target='_blank' href='/admin/merchants/view/"+ row.id +"'>" + row.name.toUpperCase() + "</a>"} }, 
                      { data: 'date_funded', name: 'date_funded'}, 
                      { data: 'last_status_updated_date', name: 'last_status_updated_date',"searchable": false }, 
                      { data: 'Def_Inv', name: 'Def Inv' ,"searchable": false,"orderable": false}, 
                      { data: 'Def Rtr', name: 'Def Rtr',"searchable": false,'orderable': false},
                      { data: 'agent_name', name: 'merchants_details.agent_name'},

                      ] ,
              
   });  // end data table                                
    
  });   // end doc ready


/*   $('.timepicker').datetimepicker({

    format: 'HH:mm:ss'

  });*/
  

  $('#apply_filter').click(function () {   
 $("#users-table").DataTable().draw(true);       
}); 

 
    $(".js-iso-placeholder-multiple").select2({
      placeholder: "Select ISO"
    });

     $(".js-investor-type-placeholder-multiple").select2({
      placeholder: "Select Investors Type"
    });



 
                 

</script>


@stop

@section('styles')
<style type="text/css">
   .select2-selection__rendered {
      display: inline !important;
    }
    .select2-search--inline {
      float: none !important;
    }
</style>
<link href="{{ asset('/css/optimized/Default_Rate_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/bower_components/breadcrumbs/breadcrumb1.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop
