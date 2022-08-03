@extends('layouts.admin.admin_lte')
@section('content')
    <?php
    $date_end = date('Y-m-d');
    $date_start = date('Y-m-d', strtotime('-1 days', strtotime($date_end)));
    ?>
    <div class="inner admin-dsh header-tp">
        <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{ isset($page_title) ? $page_title : '' }}</h3>
        <a href="#" class="help-link">
            <i class="fa fa-question-circle" aria-hidden="true"></i>
            <div class="tool-tip">All Transactions</div>
        </a>
    </div>
    <div class="col-md-12">
        <div class="box">
            <div class="box-head ">
                @include('layouts.admin.partials.lte_alerts')
            </div>
            <div class="box-body">
                <div class="form-group">
                    <div class="form-filter full-width">
                        <div class="form-filter-wrap">
                            {{ Form::open(['url' => route('admin::bills::export'), 'id' => 'billFilter']) }}
                            <div class="filter-group">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <div class="input-group-text">
                                                <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
                                            </div>
                                            <input class="form-control datepicker" autocomplete="off" id="date_start1"
                                                value="{{ $date_start }}" name="date_start1"
                                                placeholder="{{ \FFM::defaultDateFormat('format') }}" type="text" />
                                            <input type="hidden" name="date_start" id="date_start"
                                                value="{{ $date_start }}">
                                        </div>
                                        <span class="help-block">From Date</span>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <div class="input-group-text">
                                                <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                                            </div>
                                            <input class="form-control datepicker" autocomplete="off" id="date_end1"
                                                value="{{ $date_end }}" name="date_end1"
                                                placeholder="{{ \FFM::defaultDateFormat('format') }}" type="text" />
                                            <input type="hidden" name="date_end" id="date_end" value="{{ $date_end }}">
                                        </div>
                                        <span class="help-block">To Date</span>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <div class="input-group-text">
                                                <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                                            </div>
                                            {{ Form::select('investors[]', $investors, '', ['class' => 'form-control js-investor-placeholder-multiple', 'id' => 'investors', 'multiple' => 'multiple']) }}
                                        </div>
                                        <span class="help-block">Investors</span>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <div class="input-group-text">
                                                <span class="fa fa-id-card" aria-hidden="true"></span>
                                            </div>
                                            <select id="account_no_f" name="account_no_f" class="form-control">
                                                <option value=""> Select Account No </option>
                                                @foreach ($bank_accounts as $accounts)
                                                    <option value="{{ $accounts->account_no }}">
                                                        {{ $accounts->account_no }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <span class="help-block">Account No. </span>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <div class="input-group-text">
                                                <span class="fa fa-list-alt" aria-hidden="true"></span>
                                            </div>
                                            {{ Form::select('categories[]', $categories, '', ['class' => 'form-control js-category-placeholder-multiple', 'id' => 'categories', 'multiple' => 'multiple']) }}
                                        </div>
                                        <span class="help-block">Transaction Categories</span>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <div class="input-group-text">
                                                <span class="fa fa-list-alt" aria-hidden="true"></span>
                                            </div>
                                            {{ Form::select('companies', $companies, '', ['class' => 'form-control js-company-placeholder-multiple', 'id' => 'companies', 'placeholder' => 'Select Company']) }}
                                        </div>
                                        <span class="help-block">Company</span>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <div class="input-group-text">
                                                <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                                            </div>
                                            {!! Form::select('investor_type[]', $investor_types, '', ['class' => 'form-control', 'id' => 'investor_type', 'multiple' => 'multiple']) !!}
                                        </div>
                                        <span class="help-block">Investor Type </span>
                                    </div>

                                    <div class="col-md-8">
                                        <div class="btn-box btn-right">
                                            <div class="input-group">
                                                <input type="submit" value="Apply Filter" class="btn btn-success"
                                                    id="date_filter" name="student_dob">
                                                @if (@Permissions::isAllow('Transactions', 'Download'))
                                                    {{ Form::submit('download', ['class' => 'btn btn-primary', 'id' => 'form_filter']) }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>

                <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                    <div class="row">
                        <div class="col-sm-10"></div>
                        @if (@Permissions::isAllow('Transactions', 'Create'))
                            <div class="col-sm-2" style="padding-bottom:15px; padding-right: 20px">
                                <a href="{{ route('admin::bills::create') }}" class="btn btn-primary"
                                    style="float: right;">Create Bill</a>
                            </div>
                        @endif
                    </div>
                    <div class="row">
                        <div class="col-sm-12 table-responsive">
                            {!! $tableBuilder->table(['class' => 'table table-bordered bill'], true) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /.box-body -->
@stop
@section('scripts')
    <script src="{{ asset('select2/js/select2.full.min.js') }}"></script>
    {!! $tableBuilder->scripts() !!}
    <script type="text/javascript">
        var table = window.LaravelDataTables["dataTableBuilder"];
        $(document).ready(function() {
            $(".js-company-placeholder-multiple").select2({
                placeholder: "Select Company"
            });

            $(".js-investor-placeholder-multiple").select2({
                placeholder: "Select Investor(s)"
            });

            $(".js-category-placeholder-multiple").select2({
                placeholder: "Select Category(ies)"
            });

            $(".js-investor_type-placeholder-multiple").select2({
                placeholder: "Select Investor Type"
            });

            $('#date_filter').click(function(e) {
                e.preventDefault();
                table.draw();
            });
            jQuery.validator.addMethod("date", function(value, element, params) {
                return moment(params).isValid();
            });
            // date validate for bill
            $('#billFilter').validate({ // initialize the plugin
                errorClass: 'errors',
                rules: {
                    date_start1: {
                        // required: true,
                        date: function() {
                            return $('#date_start').val();
                        },
                    },
                },
                messages: {
                    date_start1: {
                        required: "Enter valid date"
                    },
                },
            });

            $(".accept_digit_only").keypress(function(evt) {
                var theEvent = evt || window.event;
                var key = theEvent.keyCode || theEvent.which;
                key = String.fromCharCode(key);
                if (key.length == 0) return;
                var regex = /^[0-9.,\b]+$/;
                if (!regex.test(key)) {
                    theEvent.returnValue = false;
                    if (theEvent.preventDefault) theEvent.preventDefault();
                }
            });

            $('#dataTableBuilder tbody').on('click', 'td.details-control ', function() {
                var tr = $(this).closest('tr');
                var row = table.row(tr);
                if (row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                } else {
                    row.child(format(row.data())).show();
                    tr.addClass('shown');
                }
            });

            var Url = "{{ URL::to('admin/investors/transactions/') }}";

            function format(obj) {
                var investorTable = $(
                    '<table class="table dataTable no-footer" cellpadding="0" cellspacing="0" border="0" style=""> <tr class="text-danger"><td class="partic"><b>Investor</b></td><td><b>Amount</b></td></tr></table>'
                );
                var investor = /*JSON.parse*/ ((obj.investor));
                $.each(investor, function(key, val) {
                    var investorRow = $('<tr>' +
                        '<td><a href="' + Url + '/' + val.investor_id + '" >' + val.investor_name +
                        '</a></td>' +
                        '<td>' + val.amount + '</td></tr>');
                    investorTable.append(investorRow);
                });
                return investorTable;
            }
            var URL_account = "{{ URL::to('admin/bills/accountSelect') }}";

            $('#account_notttttttttt').select2({
                'placeholder': 'Select Account',
                ajax: {
                    url: URL_account,
                    dataType: 'json',
                    data: function(params) {
                        return {
                            q: params.term, // search term
                            page: params.page,
                            investorId: $("#investors").select2('val')
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: $.map(data.items, function(item) {
                                return {
                                    text: item.account_name,
                                    slug: item.account_name,
                                    id: item.id
                                }
                            })
                        };
                    }
                }
            });
        });

    </script>
@stop
@section('styles')
    <link href="{{ asset('/css/optimized/bills.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop
