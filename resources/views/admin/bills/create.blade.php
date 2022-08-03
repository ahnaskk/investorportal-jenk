@extends('layouts.admin.admin_lte')

@section('content')
    <div class="inner admin-dsh header-tp">
        <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{ isset($page_title) ? $page_title : '' }} </h3>
        <a href="#" class="help-link">
            <i class="fa fa-question-circle" aria-hidden="true"></i>
            <div class="tool-tip">Bill Create</div>
        </a>
    </div>
    @if ($action == 'create')
        {{ Breadcrumbs::render('admin::bills::create') }}
    @endif
    <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary sub_adm">
            @include('layouts.admin.partials.lte_alerts')
            <!-- form start -->
            @if ($action == 'create')
                {!! Form::open(['route' => ['admin::bills::storeCreate'], 'method' => 'POST', 'id' => 'crete_bill_form']) !!}
            @else
                {!! Form::open(['route' => ['admin::bills::update', $bill_id], 'method' => 'POST']) !!}
                {{ Form::hidden('edit', 'yes') }}
            @endif
            <div class="box-body ">
                <div class="form-box-styled mb-15">
                    <input type="button" id="unselect" name="unselect" value="Unselect" class="btn btn-success">
                    <input type="button" id="select_all" name="select_all" value="Select All" class="btn btn-success">
                </div>
                <div class="row">
                    <div class="form-group col-md-4 ">
                        <label for="exampleInputGroupBy">Company</label>
                        {!! Form::select('company', $companies, old('company'), ['class' => 'form-control js-company-placeholder', 'placeholder' => 'Select Company', 'id' => 'company']) !!}
                    </div>
                    <div class="form-group col-md-4">
                        <label for="exampleInputEmail1">Merchants</label>
                        <div class="input-group">
                            <div class="input-group-text">
                                <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                            </div>
                            <select id="merchants" name="merchant[]" class="form-control" multiple="multiple"></select>
                        </div>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="exampleInputEmail1">Account No. <span class="validate_star">*</span></label>
                        {{ Form::select('account_no', $bank_accounts, old('account_no'), ['id' => 'account_no', 'class' => 'form-control', 'data-placeholder' => 'Select Account']) }}
                        <span id="invalid-account_no"></span>
                    </div>

                    <div class="form-group col-md-12">
                        <label for="exampleInputEmail1">Investor <span class="validate_star">*</span></label>
                        <select id="investor_id" name="investor_id[]" class="form-control" required multiple="multiple">
                            @if ($action == 'create')
                                @foreach ($investors as $investor)
                                    <option data-liquidity='{{ $investor->userDetails['liquidity'] }}'
                                        data-management-fee='{{ $investor->management_fee }}'
                                        data-synd-fee='{{ $investor->global_syndication }}'
                                        data-name='{{ $investor->name }}'
                                        {{ collect(old('investor_id'))->contains($investor->id) ? 'selected' : '' }}
                                        value="{{ $investor->id }}">{{ $investor->name }} -
                                        {{ $investor->investor_type == 2 ? 'Equity' : 'Debt' }}
                                        - {{ $investor->userDetails['liquidity'] }}
                                    </option>
                                @endforeach
                            @else
                                @foreach ($selected_investors as $investor)
                                    <option data-liquidity='{{ $investor->liquidity }}'
                                        data-management-fee='{{ $investor->management_fee }}'
                                        data-synd-fee='{{ $investor->global_syndication }}'
                                        data-name='{{ $investor->name }}' selected="selected"
                                        value="{{ $investor->id }}">
                                        {{ $investor->name }} -
                                        {{ $investor->investor_type == 2 ? 'Equity' : 'Debt' }}
                                        - {{ $investor->liquidity }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        <span id="invalid-investor_id"></span>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-4">
                        <label for="exampleInputEmail1">Category <span class="validate_star">*</span></label>
                        {!! Form::text('category_notes', isset($mBills) ? $mBills->category_notes : old('category_notes'), ['class' => 'form-control', 'required' => 'required', 'id' => 'category_notes']) !!}
                        <!--  {{ Form::select('categories[]', $categories, '', ['class' => 'form-control js-category-placeholder-multiple', 'id' => 'categories', 'multiple' => 'multiple']) }} -->
                        <span id="invalid-category_notes"></span>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="exampleInputEmail1">Amount <span class="validate_star">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-dollar"></i></span>
                            {!! Form::text('amount', isset($mBills) ? $mBills->amount : old('amount'), ['class' => 'form-control accept_digit_only amount', 'required', 'id' => 'amount']) !!}
                            <span id="invalid-amount"></span>
                        </div>
                    </div>
                    <?php $userId = Auth::user()->id; ?>
                    {!! Form::hidden('creator_id', $userId) !!}
                    <div class="form-group col-md-4">
                        <label for="exampleInputEmail1">Date <span class="validate_star">*</span></label>
                        {!! Form::text('date1', isset($mBills) ? $mBills->date : old('date'), ['class' => 'form-control datepicker', 'required', 'id' => 'billdate1', 'placeholder' => \FFM::defaultDateFormat('format'), 'autocomplete' => 'off']) !!}
                        <input type="hidden" name="date" class="date_parse"
                            value="{{ isset($mBills) ? $mBills->date : old('date') }}" id="billdate">
                        <span id="invalid-billdate1"></span>
                    </div>
                    <div class="btn-wrap btn-right col-md-12">
                        <div class="btn-box">
                            @if (@Permissions::isAllow('Transactions', 'View')) <a
                                    class="btn btn-success"
                                    href="{{ URL::to('admin/investors/transaction-report') }}">Back to lists</a>
                            @endif
                            @if ($action == 'create')
                                @if (@Permissions::isAllow('Transactions', 'Create'))
                                    {!! Form::submit('Create', ['class' => 'btn btn-primary']) !!}
                                @endif
                            @else
                                @if (@Permissions::isAllow('Transactions', 'Edit'))
                                    {!! Form::submit('Update', ['class' => 'btn btn-primary']) !!}
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
            <!-- /.box -->
        </div>
    </div>
@stop
@section('scripts')
    <script src="{{ asset('select2/js/select2.full.min.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            var URL_getInvestor = "{{ URL::to('admin/getInvestors') }}";
            var URL_getMerchant = "{{ URL::to('admin/getMerchants') }}";
            var URL_getAllInvestors = "{{ URL::to('admin/getAllInvestors') }}";
            $("input.amount").keypress(function(event) {
                return /\d/.test(String.fromCharCode(event.keyCode));
            });

            $("#unselect").click(function(e) {
                $('#investor_id').val('').trigger("change.select2");
            });

            $('#select_all').click(function() {
                $('#investor_id option').prop('selected', true).trigger("change.select2");
                $('#merchants').val('').trigger("change.select2");
            });


            $('#merchants, #company').change(function(e) {
                var merchant_id = $('#merchants').val();
                var company = $('#company').val();

                $.ajax({
                    type: 'GET',
                    data: {
                        'merchantId': merchant_id,
                        'company': company,
                        '_token': _token
                    },
                    url: URL_getInvestor,
                    success: function(data) {
                        var test = [];
                        var result = data.items;
                        for (var i in result) {
                            test.push(result[i].id);
                        }
                        if (test) {
                            $('#investor_id').attr('selected', 'selected').val(test).trigger(
                                'change.select2');
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

            $(".js-company-placeholder").select2({
                placeholder: "Select A Company"
            });

            // date validate for bill 
            jQuery.validator.addMethod("date", function(value, element, params) {
                return moment(params).isValid();
            });
            $('#crete_bill_form').validate({ // initialize the plugin
                errorClass: 'errors',
                rules: {
                    category_notes: {
                        required: true
                    },
                    amount: {
                        required: true,
                    },
                    date1: {
                        required: true,
                        date: function() {
                            return $('#billdate').val();
                        },
                    },
                    "investor_id[]": {
                        required: true,
                    },
                    account_no: {
                        required: true,
                    }

                },
                messages: {
                    category_notes: "Enter Category",
                    amount: "Enter Amount",
                    account_no: {
                        required: "Select A/C No"
                    },
                    date1: {
                        required: "Enter Date",
                        date: "Enter Valid date",
                    },
                    investor_id: "Select Investor",
                },
                errorPlacement: function(error, element) {
                    error.appendTo('#invalid-' + element.attr('id'));
                },
                onfocusout: function(element) {
                    if ($(element).val()) {
                        $(element).valid();
                    }
                }
            });

            $("#amount").keypress(function(e) {
                if (e.which != 46 && e.which != 45 && e.which >= 37 && !(e.which >= 48 && e.which <= 57)) {
                    return false;

                }
            });

            function numberWithCommas(x) {
                var parts = x.toString().split(".");
                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                return parts.join(".");
            }
        });

        var URL_account = "{{ URL::to('admin/bills/accountSelect') }}";

        $('#account_nobb').select2({
            'placeholder': 'Select Account',
            ajax: {
                url: URL_account,
                dataType: 'json',
                data: function(params) {
                    return {
                        q: params.term, // search term
                        page: params.page,
                        investorId: $("#investor_id").val()
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
        })

    </script>
@stop

@section('styles')
    <style type="text/css">
        .adminSelect .select2-hidden-accessible {
            display: none;
        }

        .breadcrumb {
            padding: 8px 15px;
            margin-bottom: 20px;
            list-style: none;
            background-color: #f5f5f5;
            border-radius: 4px;
        }

        .breadcrumb>li {
            display: inline-block;
        }

        li.breadcrumb-item a {
            color: #6B778C;
        }

        .breadcrumb>li+li::before {
            padding: 0 5px;
            color: #ccc;
            content: "/\00a0";
        }

        li.breadcrumb-item.active {
            color: #2b1871 !important;
        }

        .select2-selection__rendered {
            display: inline !important;
        }

        .select2-search--inline {
            float: none !important;
        }

    </style>
    <link href="{{ asset('/css/optimized/create_bills.css?ver=5') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/css/optimized/Default_Rate_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop
