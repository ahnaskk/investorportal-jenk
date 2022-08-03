@extends('layouts.admin.admin_lte')

@section('content')

    <div class="box">
        <div class="box-body">


            <div class="form-group">
                <div class="filter-group-wrap" >
                    <div class="filter-group" >
                        {{Form::open(['route'=>'admin::reports::investor-export','id'=>'investor-form'])}}


 <div class="serch-bar">
   <!--<div  class="row">
<div class="col-sm-12 merchant-ass">           

                        <div class="col-md-4 check-click checktime1">
                            <div class="input-group">
                                <div class="input-group-text">
                                   <label class="chc chc01"><input  id="date_type" name="date_type"
                                   type="checkbox" value="true"/> <span class="checkmark chek-mm"></span>
                                  </label>
                                 </div>   
                               </div>
                            <span class="grid inputInfoLg small">Check if filter based on Merchant Added Date (Funded Date by default)</span>
                        </div>




 <div class="date-star" id="date-star">

    <div class="col-md-4">
            <div class="input-group">
                 <div class="input-group-text">
                <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
              </div>
                <input class="form-control from_date1" id="date_start" name="start_date"  placeholder="MM/DD/YYYY" type="date"/>
                <span id="invalid-date_start" />
            </div>
           <span class="help-block">From date (Assigned date)</span>
               </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                                </div>
                                <input class="form-control to_date1" id="date_end" name="end_date" placeholder="MM/DD/YYYY"
                                       type="date"/>
                            </div>
                            <span class="help-block">To date</span>
                        </div>
                        </div>
                        


  <div id="time_filter" class="check-time" style="display:none;">
       <div class="col-sm-12">
         <div class="row">

            <div class="col-md-3 serch-timeer-one">
                <div class="input-group serch-two">
                    <div class="input-group-text">
                        <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                        </div>
                         <input class="form-control from_date2" id="date_start1" name="date_start" placeholder="MM/DD/YYYY" type="date"/>
                      </div>
                    <span class="help-block">From date</span>
                </div>


                <div class="col-lg-3 serch-timeer">
                 <div class="input-group">
                    <div class="input-group-text">
                        <span class="glyphicon glyphicon-time" aria-hidden=" true"></span>
                        </div>
                          <input class="timepicker form-control from_time" type="text" id="time_start" name="time_start" placeholder="00:00:00">
                      </div>
                    <span class="help-block">From time</span>
                    </div>

           
                           <div class="col-md-3 serch-timeer-one">
                    <div class="input-group serch-two">
                        <div class="input-group-text">
                           <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
                           </div>
                           <input class="form-control to_date2" id="date_end1" name="date_end" placeholder="MM/DD/YYYY" type="date"/>
                        </div>
                     <span class="help-block">To date</span>
                </div>      
                      <div class="col-lg-3 serch-timeer">
                    <div class="input-group">
                        <div class="input-group-text">
                           <span class="glyphicon glyphicon-time" aria-hidden="true"></span>
                           </div>
                           <input class="timepicker form-control to_time" type="text" id="time_end" name="time_end" placeholder="00:00:00">
                        </div>
                     <span class="help-block">To time</span>
                 </div> 
               </div>
          </div>        
     </div>
</div>-->
    

<!-- assigned-filter-investor -->


        <!-- <div class="col-sm-12">           
                     <div class="col-md-4 check-click" style="padding-bottom: 36px;padding-top:0">
                              <div class="input-group">
                                 <div class="input-group-text">
                                   <label class="chc chc01"><input  id="date_type1" name="date_type1"
                                   type="checkbox" value="true"/> <span class="checkmark chek-mm"></span>
                                  </label>
                                 </div>   
                               </div>
                            <span class="grid inputInfoLg small">Check if filter based on Assigned Investor Date</span>
                         </div> -->


    <!--
          <p class="invest-hed">Investor Assigned Date and Time</p>


     <div class="date-star check-date-invest" id="date-star2">
       <div class="col-sm-12" style="padding-bottom:25px;padding-top:41px">
         <div class="row">
             <div class="col-md-3 serch-timeer-one" style="padding-left:0!important;padding-right:0!important;">
                <div class="input-group serch-two">
                    <div class="input-group-text">
                        <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                        </div>
                         <input class="form-control from_date1" id="date_start2" name="date_start" placeholder="MM/DD/YYYY" type="date">
                      </div>
                    <span class="help-block">From date</span>
                </div>


                <div class="col-lg-3 serch-timeerone">
                 <div class="input-group">
                    <div class="input-group-text">
                        <span class="glyphicon glyphicon-time" aria-hidden=" true"></span>
                        </div>
                          <input class="timepicker form-control from_time" type="text" id="time_start" name="time_start" placeholder="00:00:00">
                      </div>
                    <span class="help-block">From time</span>
                    </div>
                 

               
                <div class="col-md-3 serch-timeer-one">
                    <div class="input-group serch-two">
                        <div class="input-group-text">
                           <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
                           </div>
                           <input class="form-control to_date1" id="date_end2" name="date_end" placeholder="MM/DD/YYYY" type="date">
                        </div>
                     <span class="help-block">To date</span>
                </div>      
                      <div class="col-lg-3 serch-timeer" style="padding-left:0!important;padding-right:0!important;">
                    <div class="input-group">
                        <div class="input-group-text">
                           <span class="glyphicon glyphicon-time" aria-hidden="true"></span>
                           </div>
                           <input class="timepicker form-control to_time" type="text" id="time_end" name="time_end" placeholder="00:00:00">
                        </div>
                     <span class="help-block">To time</span>
                 </div>
               </div>
             </div>        
          </div>                  
        </div> --> 
     </div>
 </div>


<div class="row">
  <div class="col-sm-12">
    <div class="col-md-4">
        <div class="input-group">
                      <div class="input-group-text">
                           <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                          </div>

                             {{Form::select('merchants[]',$merchants,1,['class'=>'form-control','id'=>'merchants','multiple'=>'multiple'])}}

                            </div>
                            <span class="help-block">Merchants</span>
                        </div>




                        <div class="col-md-4">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                                </div>

                                  {{Form::select('investors[]',$investors,1,['class'=>'form-control','id'=>'investors','multiple'=>'multiple'])}}

                            </div>
                            <span class="help-block">Investors </span>
                        </div>
      
                       <!-- <div class="col-md-4">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                                </div>

                                  {{Form::select('lenders[]',$lenders,null,['class'=>'form-control','id'=>'lenders','multiple'=>'multiple'])}}

                            </div>
                            <span class="help-block">Lenders </span>
                        </div>-->
      
                      <!-- <div class="col-md-4">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="fa fa-industry" aria-hidden="true"></span>
                                </div>

                                  {{Form::select('industries[]',$industries,null,['class'=>'form-control','id'=>'industries','multiple'=>'multiple'])}}

                            </div>
                            <span class="help-block">Industries </span>
                        </div> --> 
                      </div>
                      </div>
                      <!--<div class="col-md-4 check-click checktime1">
                            <div class="input-group">
                                <div class="input-group-text">
                                   <label class="chc chc01"><input  id="export_checkbox" name="export_checkbox"
                                   type="checkbox" value="true"/> <span class="checkmark chek-mm"></span>
                                  </label>
                                 </div>   
                               </div>
                            <span class="grid inputInfoLg small">Check if Download without Details</span>
                        </div>-->
                        
                        
                        <div class="invest-ment">
                            <div class="btn-box inhelpBlock ">
                                    <input type="button" value="Apply Filter" class="btn btn-success" id="date_filter"
                                           name="student_dob">

                                <div class="blockCust pull-right" style="padding-bottom: 15px">

                                    <!--{{Form::submit('Download report',['class'=>'btn btn-primary','id'=>'form_filter'])}}-->

                                </div>
                             </div>
                       </div>

                      </div>
                </div>
                    
                {{Form::close()}}
            </div>


            <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">

                <div class="row">
                    <div class="col-sm-12 grid table-responsive">

                   
                            {!! $tableBuilder->table(['class' => 'table table-bordered investorReport'], true) !!}
                        
                        <div class="blockCust pull-right" style="padding-bottom: 15px">

                            <!--{{Form::submit('Download report',['class'=>'btn btn-primary','id'=>'form_filter'])}}-->

                        </div>
                    
                    </div>
                </div>
            </div>
        
@stop

@section('scripts')

    <script src="{{ asset ('bower_components/datatables.net/js/jquery.dataTables.min.js') }}"
            type="text/javascript"></script>

    <script src="{{ asset ('bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"
            type="text/javascript"></script>
     <script src="{{ asset ('/js/updated/moment.min.js') }}" type="text/javascript"></script>
        <script src="{{ asset ('/js/updated/bootstrap-datetimepicker.min.js') }}" type="text/javascript"></script>          

    {!! $tableBuilder->scripts() !!}

    <script type="text/javascript">
        function format(obj) {

            var investmentData = '<table class="table dataTable no-footer" cellpadding="0" cellspacing="0" border="0" style=""> <tr class="text-danger"><td>Investor Id</td><td>Amount</td><td>Commission</td><td>Prepaid</td><td>Total</td><td>Interest Rate</td></tr>';

           // var investment = JSON.parse((obj.investment_data).replace(/&quot;/g,'"'));
            console.log();
            $.each(obj.investment_data, function (key, val) {
                investmentData = investmentData + '<tr>' +
                        '<td >' + val.investor_id + '</td>' +
                        '<td>' + val.amount + '</td>' +
                        '<td>' + val.commission + '</td>' +
                        
                        '<td>' + val.prepaid + '</td>' +
                        '<td>' + val.total + '</td>' +
                        '<td>' + val.interest_rate + '</td>' +
                        '</tr>';
            });

            return investmentData + '</table>';

        }

        var table = window.LaravelDataTables["dataTableBuilder"];


        $(document).ready(function () {

            $('#dataTableBuilder tbody').on('click', 'td.details-control ', function () {
                var tr = $(this).closest('tr');
                var row = table.row(tr);

                if (row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                }
                else {
                    row.child(format(row.data())).show();
                    tr.addClass('shown');
                }
            });

            $('#date_filter').click(function (e) {
                e.preventDefault();
                table.draw();
            });  


    
 
$("#investor-form").validate({
    errorClass: 'errors',  
    rules: {
        start_date: {
            required: false,
            dateITA: true,
            dateLessThan: '#date_end'
        },
        date_end: {
            required: false,
            dateITA: true,
            dateGreaterThan: "#date_start"
        }
    },
    onfocusout: function (element) {
        if ($('#date_start').val()) {
            $(element).valid();
        }
    },
    messages: {
       
      start_date: { dateITA :"Please enter valid date",                 
                },
            },
            
    
    errorPlacement: function(error, element) {
        error.appendTo('#invalid-' + element.attr('id'));
        }
 });
 
 


    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
 });

    </script>
    
    <script type="text/javascript">
   $('#date_type').click(function(){
        if($(this).is(':checked')){
           $('#time_filter').show();
           $('#date-star').hide();
        } else {
            $('#time_filter').hide();
            $('#date-star').show();
        }
        });



    $('.timepicker').datetimepicker({

        format: 'HH:mm:ss'

    }); 
    
    
    

    
    
    
    

</script> 




@stop
@section('styles')
<link href="{{ asset('/css/optimized/Transaction_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/optimized/Default_Rate_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop
