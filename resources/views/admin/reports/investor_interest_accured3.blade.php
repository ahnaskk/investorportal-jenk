@extends('layouts.admin.admin_lte')

@section('content')
<div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($title)?$title:''}} </h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Net Zero Interest</div>     
      </a>
      
  </div>
    <div class="col-md-12">
    <div class="box">
        <div class="box-body">


            <div class="form-group">
                <div class="filter-group-wrap" >
                    <div class="filter-group" >
                        {{Form::open(['route'=>'admin::reports::investor-export','id'=>'investor-form'])}}


                     <div class="serch-bar">
                      
                         </div>
                     </div>


                    <div class="row">



                        <div class="col-md-4">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                                </div>

                                  {{Form::select('investors[]',$investors,1,['class'=>'form-control','id'=>'investors','multiple'=>'multiple'])}}

                            </div>
                            <span class="help-block">Investors </span>
                        </div>
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
                                    <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                                </div>
                                <input class="form-control datepicker" id="date_end1" name="date_end1" placeholder="{{\FFM::defaultDateFormat('format')}}"
                                       type="text" autocomplete="off"/>
                                <input type="hidden" name="date_end" id="date_end" class="date_parse">
                            </div>
                            <span class="help-block">Date</span>
                        </div>



                      </div>
                      
                        
                        
                        <div class="invest-ment">
                            <div class="btn-box inhelpBlock ">
                                    <input type="button" value="Apply Filter" class="btn btn-success investor_interest" id="date_filter"
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
        </div>
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

            var investmentData = '<table class="table dataTable no-footer" cellpadding="0" cellspacing="0" border="0" style=""> <tr class="text-danger"><td>Investor Id</td><td>Funded</td><td>Commission</td><td>share</td><td>Prepaid Amount</td><td>Total</td></tr>';

           // var investment = JSON.parse((obj.investment_data).replace(/&quot;/g,'"'));
            console.log();
            $.each(obj.investment_data, function (key, val) {
                investmentData = investmentData + '<tr>' +
                        '<td >' + val.investor_id + '</td>' +
                        '<td>' + val.funded + '</td>' +
                        '<td>' + val.commission + '</td>' +
                        '<td>' + val.share + '</td>' +
                        '<td>' + val.s_prepaid_status + '</td>' +
                        '<td>' + val.total + '</td>' +
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
            dateLessThan: '#date_end1'
        },
        date_end1: {
            required: false,
            dateITA: true,
            dateGreaterThan: "#date_start1"
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
