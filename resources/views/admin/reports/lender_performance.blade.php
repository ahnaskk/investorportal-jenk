@extends('layouts.admin.admin_lte')

@section('content')

<div class="inner admin-dsh header-tp">
    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>Lender Performance</h3>
    <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Lender Performance</div>     
    </a>
</div>

<div class="col-md-12">
<div class="box">
    <div class="box-body">
        <div class="form-group">
            <div class="filter-group-wrap" >
                <div class="filter-group" >
                    {{Form::open(['route'=>'admin::reports::lender-performance-export'])}}


                    <?php
                    $dates = [];
                    if(isset($_GET['month']))
                    {
                        $monthYear =  $_GET['month'];
                        
                    }
                    else{
                        $monthYear =  date('m-Y'); //7-2018 // $_GET['month'];
                    }

                    
                    $date = explode('-', $monthYear);
                    $month = isset($date[0]) ? $date[0] : '';
                    $year = isset($date[1]) ? $date[1] : '';

                    if ($monthYear) {
                        $start = new \DateTime($year . '-' . $month . '-01');
                        $interval = new DateInterval('P1D');
                        $end = new \DateTime($year . '-' . $month . '-31');

                        $period = new DatePeriod($start, $interval, $end);

                        foreach ($period as $day) {
                            if ($day->format('N') == 6 || $day->format('N') == 7) {
                                $compulsory[$day->format('d')] = true;
                            }
                        }
                      

                        foreach ($compulsory as $key => $value) {
                            $dates[] = $year . '-' . $month . '-' . $key;
                        }
                    }
                    ?> 

                    
                  

                       

                    <div class="col-md-4">
                        <div class="input-group">
                            <div class="input-group-text">
                                <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                            </div>

                            <input class="form-control" id="valid_dates" name="valid_dates" type="text" value="{{ $monthYear }}" autocomplete="off"/>

                        </div>

                        <span class="help-block">Month</span>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group">
                            <div class="input-group-text">
                                <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                            </div>

                            <input class="form-control multi-datepicker" id="dates1" name="dates1" type="text" autocomplete="off"/>
                            <input type="hidden" name="dates" id="dates" class="date_parse">
                        </div>
                        <span class="help-block">From Date</span>
                    </div>

                
                <div class="btn-box " style="margin-bottom: 25px;">
                    <div class="input-group">
                        <input type="button" value="Apply Filter" class="btn btn-success" id="date_filter" name="student_dob">
                          {{Form::submit('download',['class'=>'btn btn-primary','id'=>'form_filter'])}}
                    </div>
                </div>
            </div>

            {{Form::close()}}
        </div>
        <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">

            <div class="row">
                <div class="col-sm-12">

                    <div class="table-container grid table-responsive" > 
                        {!! $tableBuilder->table(['class' => 'table table-bordered'], true) !!}
                    </div>

                </div>
            </div>
        </div>
    </div>
    <!-- /.box-body -->
</div>
</div>
</div>

@stop

@section('scripts')
<script src="{{asset('bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js')}}"></script>
<script type="text/javascript">
//Date picker
$('#valid_dates').datepicker({
    autoclose: true,
    format: "mm-yyyy",
    viewMode: "months",
    minViewMode: "months",
    clearBtn: true,
    todayBtn: "linked"

});
var default_date_format = "{{\FFM::defaultDateFormat('format')}}";

$('#dates1').datepicker({
    autoclose: true,
    format: default_date_format.toLowerCase(),
    multidate: true,
    clearBtn: true,
    todayBtn: "linked"
});

var arrayFromPHP = @json($dates);

var newArr = [];
$.each(arrayFromPHP, function(i,v){
    newArr.push(moment(v, 'YYYY-MM-DD').format(default_date_format));
});
$('#dates').val(arrayFromPHP);
$('#dates1').datepicker('setDates', newArr);
$('#dates1').on('change changeDate', function(){
    var val = $(this).val();
    if(val)
    {
        val = val.split(',');
        var new_arr = val.map(item => {
            let year = moment(item, default_date_format).year();
            if(year.toString().length == 1 || year.toString().length == 2) {
                year = moment(year, 'YY').format('YYYY');
            }
            return moment(item, default_date_format).set('year', year).format(default_date_format); 
        });
        var new_arr1 = val.map(item => {
            let year = moment(item, default_date_format).year();
            if(year.toString().length == 1 || year.toString().length == 2) {
                year = moment(year, 'YY').format('YYYY');
            }
            return moment(item, default_date_format).set('year', year).format('YYYY-MM-DD');
        });
        if(new_arr) {
            new_arr = new_arr.join(',');    
            new_arr1 = new_arr1.join(',');
            $(this).val(new_arr);
            $(this).datepicker('update');
            $(this).siblings('.date_parse').val(new_arr1);
            if($(this).valid() == false) {
                $(this).val('');
                $(this).datepicker('update');
                $(this).siblings('.date_parse').val('');
            }
        }
    }else {
        $(this).siblings('.date_parse').val('');
    }
});


</script>

<script src="{{ asset ('bower_components/datatables.net/js/jquery.dataTables.min.js') }}" type="text/javascript"></script>
<script src="{{ asset ('bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}" type="text/javascript"></script>

<script src="{{ asset ('js/updated/dataTables.buttons.min.js') }}" type="text/javascript"></script>
<script src="{{ asset ('js/updated/jszip.min.js') }}" type="text/javascript"></script>
<script src="{{ asset ('js/updated/pdfmake.min.js') }}" type="text/javascript"></script>
<script src="{{ asset ('js/updated/vfs_fonts.js') }}" type="text/javascript"></script>
<script src="{{ asset ('js/updated/buttons.html5.min.js') }}" type="text/javascript"></script>


{!! $tableBuilder->scripts() !!}

<script type="text/javascript">
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

    $('#valid_dates').change(function () {

        window.location = '?month=' + $('#valid_dates').val();

    });

});

</script>
@stop

@section('styles')
<link href="{{ asset('/css/optimized/Lender_Performance_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />


@stop
