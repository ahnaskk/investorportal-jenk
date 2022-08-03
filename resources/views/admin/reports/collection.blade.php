@extends('layouts.admin.admin_lte')

@section('content')

    <div class="box">
        <div class="box-body">


            <div class="form-group">
                <div class="filter-group-wrap" >
                    <div class="filter-group" >
                        {{Form::open(['route'=>'admin::reports::investor-export'])}}

                        <div class="col-md-4">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <input  id="date_type" name="date_type"
                                            type="checkbox" value="true"/>
                                </div>
                               
                            </div>
                            <span class="grid inputInfoLg small">Filtering based on 'Merchant Added Date' (Funded Date is taken by default)</span>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                                </div>
                                <input class="form-control datepicker" id="date_start1" name="start_date1" placeholder="{{\FFM::defaultDateFormat('format')}}"
                                       type="text" autocomplete="off"/>
                                <input type="hidden" name="start_date" id="date_start" class="date_parse">
                            </div>
                            <span class="help-block">From Date (Assigned Date)</span>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                                </div>
                                <input class="form-control datepicker" id="date_end1" name="end_date1" placeholder="{{\FFM::defaultDateFormat('format')}}"
                                       type="text" autocomplete="off"/>
                                <input type="hidden" name="end_date" id="date_end" class="date_parse">
                            </div>
                            <span class="help-block">To Date</span>
                        </div>

                      <div class="col-md-4">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                                </div>
<!--  -->
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
                        <div class="col-md-4">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                                </div>

                                  {{Form::select('lenders[]',$lenders,null,['class'=>'form-control','id'=>'lenders','multiple'=>'multiple'])}}

                            </div>
                            <span class="help-block">Lenders </span>
                        </div>
                        <div class="">
                            <div class="btn-box inhelpBlock ">
                                    <input type="button" value="Apply Filter" class="btn btn-success" id="date_filter"
                                           name="student_dob">

                                <div class="blockCust pull-right" style="padding-bottom: 15px">

                                    {{Form::submit('Download report',['class'=>'btn btn-primary','id'=>'form_filter'])}}

                                </div>
                            </div>
                        </div>


                    </div>

                </div>
                    
                    {{Form::close()}}
            </div>


            <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">

                <div class="row">
                    <div class="col-sm-12 table-responsive">

             
                    
                    </div>
                </div>
            </div>
        </div>
        <!-- /.box-body -->
    </div>
@stop

@section('scripts')

    <script src="{{ asset ('bower_components/datatables.net/js/jquery.dataTables.min.js') }}"
            type="text/javascript"></script>

    <script src="{{ asset ('bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"
            type="text/javascript"></script>



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


        });

    </script>

@stop

@section('styles')
    <link rel="stylesheet" href="{{ asset ("bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css") }}">
    <style type="text/css">
  
        td.details-control {
            background: url('{{asset("img/icons/details_open.png")}}') no-repeat center center;
            cursor: pointer;
        }

        tr.shown td.details-control {
            background: url('{{asset("img/icons/details_close.png")}}') no-repeat center center;
        }
    </style>
@stop