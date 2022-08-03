@extends('layouts.investor.admin_lte')

@section('content')
<div class="content-wrapper body-wrap grid">
    <div class="box">
        <div class="box-body">

        <div class="form-group">
                <div class="filter-group-wrap" >
                    <div class="filter-group" >
                        {{Form::open(['route'=>'investor::export::general::report'])}}
                        <div class="col-md-4">
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
                                </div>
                                <input class="form-control datepicker" id="date_start1" name="date_start1" placeholder="{{\FFM::defaultDateFormat('format')}}"
                                       type="text" autocomplete="off"/>
                                <input type="hidden" name="date_start" id="date_start" class="date_parse">
                            </div>
                            <span class="help-block">From Date</span>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
                                </div>
                                <input class="form-control datepicker" id="date_end1" name="date_end1" placeholder="{{\FFM::defaultDateFormat('format')}}" type="text" autocomplete="off"/>
                                <input type="hidden" name="date_end" id="date_end" class="date_parse">
                            </div>
                            <span class="help-block">To Date</span>
                        </div>

                        <div class="col-md-4">
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                                </div>

                                  {{Form::select('merchant_id[]',$merchants,1,['class'=>'form-control','id'=>'merchant_id','multiple'=>'multiple'])}}
<!-- 
                                <input class="form-control" id="merchant_id" name="merchant_id" placeholder="Enter merchant id"
                                       type="text"/> -->

                            </div>
                            <span class="help-block">Merchant</span>
                        </div>
                            </div>
                    <div class="btn-box">
                        <div class="input-group">
                            <input type="button" value="Apply Filter" class="form-control btn" id="date_filter"
                                   name="student_dob">

                        </div>
                    </div>
                </div>

                    <div class="col-md-2 pull-right" style="padding-bottom: 15px">
                    

                        {{Form::submit('download',['class'=>'btn btn-primary','id'=>'form_filter'])}}
                         {{Form::close()}}
                    </div>
                   
             


            </div>


            <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">

                <div class="row">
                    <div class="table-wrapper">

                        <div class="table-container" > 
                            {!! $tableBuilder->table(['class' => 'table table-bordered'], true) !!}
                        </div>
                    
                    </div>
                </div>
            </div>
        </div>
        <!-- /.box-body -->
    </div>
    </div>
@stop

@section('scripts')
                
    <script src="{{ asset ("bower_components/datatables.net/js/jquery.dataTables.min.js") }}"
            type="text/javascript"></script>

    <script src="{{ asset ("bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js") }}"
            type="text/javascript"></script>

    {!! $tableBuilder->scripts() !!}

    <script type="text/javascript">
        function format(obj) {

            var partpayment = '<table class="table dataTable no-footer" cellpadding="0" cellspacing="0" border="0" style=""> <tr class="text-danger"><td>Participant</td><td>Date</td><td>Debited</td><td>Participant Share</td><td>MGMT fees</td><td>Net amount</td><td>Rcode</td></tr>';

            var partPay = JSON.parse((obj.participant_payment).replace(/&quot;/g,'"'));
            $.each(partPay, function (key, val) {
                partpayment = partpayment + '<tr>' +
                      

                        '<td >' + val.participant + '</td>' +
                        '<td>' + val.ledger_date + '</td>' +
                        '<td>' + val.debited + '</td>' +
                        '<td>' + val.syndication_amount + '</td>' +
                        '<td>' + val.mgmnt_fee + '</td>' +
                        '<td>' + val.to_syndicate + '</td>' +
                        '<td>' + val.rcode + '</td>' +
                        '</tr>';
            });

            return partpayment + '</table>';

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

     /*       $('#form_filter').click(function (e) {
                //e.preventDefault();
               form.submit();
            });*/


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