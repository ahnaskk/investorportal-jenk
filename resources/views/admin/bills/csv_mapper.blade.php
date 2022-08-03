@extends('layouts.admin.admin_lte')
@section('content')

    <div class="inner admin-dsh header-tp">
        <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{ isset($page_title) ? $page_title : '' }} </h3>
        <a href="#" class="help-link">
            <i class="fa fa-question-circle" aria-hidden="true"></i>
            <div class="tool-tip">Import Bills</div>
        </a>
    </div>
    <div class="col-md-12">
        <div class="box">
            <div class="box">
                <div class="box-body">
                    @include('layouts.admin.partials.lte_alerts')
                    <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap">
                        {{ Form::open(['route' => 'admin::bills::csvprocess', 'method' => 'POST', 'id' => 'bill_create_form']) }}
                        <div class="form-group">
                            <div class="filter-group-wrap">
                                <div class="filter-group">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                {!! Form::select('investor_type', $investor_type, '', ['class' => 'form-control js-investor-type-placeholder', 'placeholder' => 'Select Investor Type', 'id' => 'investor_type']) !!}
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="button" id="unselect" name="unselect" value="Unselect"
                                                class="btn btn-success">
                                            <input type="button" id="select_all" name="select_all" value="Select All"
                                                class="btn btn-success">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="exampleInputEmail1">Merchants</label>
                                                <div class="input-group">
                                                    <div class="input-group-text">
                                                        <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                                                    </div>
                                                    <select id="merchants" name="merchant[]" class="form-control"
                                                        multiple="multiple">
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12" id="all_investors">
                                            <div class="form-group">
                                                <label for="exampleInputGroupBy">Investors <span
                                                        class="validate_star">*</span></label>
                                                <select id="investor_id" name="investor_id[]" class="form-control"
                                                    multiple="multiple" required="required">
                                                    <option>Select Investors</option>
                                                    @foreach ($investors as $investor)
                                                        <option data-liquidity='{{ $investor->userDetails['liquidity'] }}'
                                                            data-management-fee='{{ $investor->management_fee }}'
                                                            data-synd-fee='{{ $investor->global_syndication }}'
                                                            data-name='{{ $investor->name }}'
                                                            {{ isset($mBills) ? ($mBills->investor_id == $investor->id ? 'selected' : '') : '' }}
                                                            value="{{ $investor->id }}">{{ $investor->name }} -
                                                            {{ $investor->investor_type == 2 ? 'Equity' : 'Debt' }}
                                                            - {{ $investor->userDetails['liquidity'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <span id="invalid-investor_id"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="exampleInputGroupBy">Company</label>
                                                {!! Form::select('company', $companies, '', ['class' => 'form-control js-company-placeholder', 'placeholder' => 'Select Company', 'id' => 'company']) !!}
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="exampleInputEmail1">Account No. <span
                                                        class="validate_star">*</span></label>
                                                <select required="" id="account_no" name="account_no" class="form-control">
                                                    <option value=""> Select Account No. </option>
                                                    @foreach ($accounts as $account)
                                                        <option value="{{ $account->account_no }}"
                                                            {{ isset($mBills) ? ($mBills->account_no == $account->account_no ? 'selected' : '') : '' }}
                                                            ) ?>{{ $account->bank_name . ' ' . $account->account_no }}</option>
                                                    @endforeach
                                                </select>
                                                <span id="invalid-account_no"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="grid">
                                        <div class="paymntGnrtBox grid">
                                            <div class="card card-primary">
                                                <div class="card-header">Csv Mapper</div>
                                                <div class="card-body">
                                                    @if (isset($csv_data))
                                                        <div class="row">
                                                            <div class="grid table-responsive paymentTdy">
                                                                <div class="col-md-3 col-sm-12 csv-name">
                                                                    <label class="merch-name"> Bill Date </label>
                                                                </div>
                                                                <div class="col-md-4 col-sm-12 csv-name">
                                                                    <label class="merch-name"> Note </label>
                                                                </div>
                                                                <div class="col-md-3 col-sm-12 csv-name">
                                                                    <label class="merch-name"> Debit </label>
                                                                </div>
                                                                <br>
                                                                {!! csrf_field() !!}
                                                                @foreach ($csv_data as $data)
                                                                    @if ($data[0] && $data[2])
                                                                        <div class="col-md-3 col-sm-12 csv-name">
                                                                            <input type="text" class="form-control"
                                                                                name="bill_date[]"
                                                                                value="{{ $data[0] }}">
                                                                        </div>
                                                                        <div class="col-md-4 col-sm-12 csv-name">
                                                                            <input typ="text" class="form-control"
                                                                                name="note[]" value="{{ $data[1] }}">
                                                                        </div>
                                                                        <div class="col-md-3 col-sm-12 csv-name">
                                                                            <input type="text" class="form-control"
                                                                                name="debit[]" value="{{ $data[2] }}">
                                                                        </div>
                                                                        <br> <br> <br>
                                                                    @endif
                                                                @endforeach
                                                                <div class="col-md-12">
                                                                    {{ Form::submit('Process', ['class' => 'btn btn-lg btn-success', 'id' => 'sub_button']) }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
@section('scripts')
    <script src="{{ asset('select2/js/select2.full.min.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $(".js-company-placeholder").select2({
                placeholder: "Select a Company"
            });

            $(".js-investor-type-placeholder").select2({
                placeholder: "Select a Investor Type"
            });

            var URL_getInvestor = "{{ URL::to('admin/getInvestors') }}";
            var URL_getMerchant = "{{ URL::to('admin/getMerchants') }}";
            var URL_getAllInvestors = "{{ URL::to('admin/getAllInvestors') }}";
            $("#unselect").click(function(e) {
                $('#investor_id').val('').trigger("change.select2");
                $('#merchants').val('').trigger("change.select2");
            });
            $('#select_all').click(function() {
                $('#investor_id option').prop('selected', true).trigger("change.select2");
                $('#merchants').val('').trigger("change.select2");
            });
            $('#merchants, #company ,#investor_type').change(function(e) {
                var merchant_id = $('#merchants').val();
                var company = $('#company').val();
                var investor_type = $('#investor_type').val();
                var test = [];
                $.ajax({
                    type: 'GET',
                    data: {
                        'merchantId': merchant_id,
                        'company': company,
                        'investor_type': investor_type,
                        '_token': _token
                    },
                    url: URL_getInvestor,
                    success: function(data) {
                        if (data.status == 1) {
                            var result = data.items;
                            for (var i in result) {
                                test.push(result[i].id);
                            }
                            $('#investor_id').attr('selected', 'selected').val(test).trigger(
                                'change.select2');
                        } else {
                            //$('#dept_investors option').removeAttr('selected').trigger("change.select2");  
                        }
                    }
                });
            });

            $('#merchants').select2({
                'placeholder': 'Select Merchants',
                ajax: {
                    url: URL_getMerchant,
                    dataType: 'json',
                    data: function(params) {
                        return {
                            q: params.term, // search term
                            page: params.page
                        };
                    },
                    processResults: function(data) {
                        return {

                            results: $.map(data.items, function(item) {
                                return {
                                    text: item.merchant_name,
                                    slug: item.merchant_name,
                                    id: item.id,
                                    selected: true,
                                }
                            })
                        };
                    }
                }
            }).change(function(data) {
                $('#investor_id').attr('selected', 'selected').val('').trigger('change.select2');
            });

            $('#investor_id').select2({
                'placeholder': 'Select Investors',
                ajax: {
                    url: URL_getAllInvestors,
                    dataType: 'json',
                    data: function(params) {
                        return {
                            q: params.term, // search term
                            page: params.page,
                            merchantId: $("#merchants").select2('val'),
                            company: $("#company").select2('val')
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: $.map(data.items, function(item) {
                                return {
                                    text: item.investor_name,
                                    slug: item.investor_name,
                                    id: item.id,
                                    selected: true,
                                }
                            })
                        };
                    }
                }
            });

            $('#investor_type').change(function(e) {
                $('#investor_id option').val('').trigger("change.select2");
                $('#merchants option').val('').trigger("change.select2");
            });
        });

    </script>
@stop

@section('styles')
    <link href="{{ asset('/css/optimized/create_bills.css?ver=5') }}" rel="stylesheet" type="text/css" />
    <style type="text/css">
        .input-group .select2 {
            flex: 1 1 auto;
            width: min-content !important;
        }
    </style>
@stop
