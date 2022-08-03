@extends('layouts.admin.admin_lte')

@section('content')
    <div class="inner admin-dsh header-tp">

        <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{ isset($page_title) ? $page_title : '' }} </h3>
        <a href="#" class="help-link">
            <i class="fa fa-question-circle" aria-hidden="true"></i>
            <div class="tool-tip">{{ isset($page_title) ? $page_title : '' }}</div>
        </a>

    </div>
    @if ($action == 'Create')
        {{ Breadcrumbs::render('create_template') }}
    @else
        {{ Breadcrumbs::render('edit_template') }}
    @endif
    <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary branch_mng box-sm-wrap">
            <!-- form start -->
            @if ($action == 'Create')
                {!! Form::open(['route' => 'admin::template::storeCreate', 'method' => 'POST', 'id' => 'template_form']) !!}
            @else
                {!! Form::open(['route' => ['admin::template::update', 'id' => $template->id], 'method' => 'POST', 'id' => 'template_form']) !!}
            @endif
            <div class="box-body box-body-sm">
                @include('layouts.admin.partials.lte_alerts')
                @if ($action != 'Create' and $template->type == 'email')
                    <div class="btn-wrap btn-right">
                        <div class="btn-box">
                            <a href="{{ route('admin::template::sample-email', ['id' => $template->id]) }}" title="Send sample email with this template to admin email" class="btn btn-success">Send sample</a>
                        </div>
                    </div>
                @endif
                <div class="form-group">
                    <label for="subject">Allowable Short Codes<span class="validate_star"></span></label>
                    <p id="shorcodes" style="color:#00a762"> </p>
                </div>

                <div class="form-group">

                    @if ($action == 'Create')
                        <label for="subject">Template Code
                         <!--   <span class="validate_star">*</span> -->
                        </label>
                        {!! Form::select('temp_code', $template_codes, isset($template) ? $template->temp_code : old('temp_code'), ['class' => 'form-control', 'placeholder' => 'Select Template Code', 'id' => 'temp_code', 'autocomplete' => 'off']) !!}
                    @else
                        <label for="subject">Template Code</label>
                        {{ $template->temp_code_name }}
                        {!! Form::hidden('id', $template->id, ['class' => 'form-control', 'id' => 'subject', 'placeholder' => 'Enter Title']) !!}
                        {!! Form::hidden('temp_code', $template->temp_code, ['class' => 'form-control', 'id' => 'temp_code_hidden', 'placeholder' => 'Enter Title']) !!}
                    @endif
                </div>

                <div class="form-group">
                    <label for="subject">Title<span class="validate_star">*</span></label>
                    {!! Form::text('title', isset($template->title) ? $template->title : old('name'), ['class' => 'form-control', 'id' => 'subject', 'placeholder' => 'Enter Title']) !!}
                </div> 

                <div class="form-group">
                    <label for="subject">Subject<span class="validate_star">*</span></label>
                    {!! Form::text('subject', isset($template->subject) ? $template->subject : old('subject'), ['class' => 'form-control', 'id' => 'subject', 'placeholder' => 'Enter Subject']) !!}
                </div>



                <div class="form-group">
                    <label for="type">Template Type<span class="validate_star">*</span></label>
                    {!! Form::select('type', $template_types, isset($template) ? $template->type : old('type'), ['class' => 'form-control', 'placeholder' => 'Select Type', 'id' => 'type']) !!}
                </div>

                <div class="form-group">
                    <label for="type">Template Body<span class="validate_star">*</span></label>
                    {!! Form::textarea('template', isset($template) ? $template->template : old('template'), ['class' => '', 'id' => 'template']) !!}
                </div>
                <div class="form-group">
                    <div class="col-md-6">
                        <label for="type">Template enabled</label>
                        {!! Form::select('enable', $enable, isset($template) ? $template->enable : old('enable'), ['class' => 'form-control', 'id' => 'enable']) !!}
                    </div>
                </div>
                <div class="form-group" id="roles-parent">
                    <div class="col-md-6">
                        <label for="type">Role</label>
                        {{ Form::select('roles[]', $roles, (isset($template) && isset($template->assignees) ? $template->assignees : null), ['class' => 'form-control js-roles-placeholder-multiple', 'id' => 'roles', 'multiple' => 'multiple']) }}
                    </div>
                </div>

                <div class="btn-wrap btn-right">
                    <div class="btn-box">
                        @if (@Permissions::isAllow('Template Management', 'View'))
                            <a href="{{ URL::to('admin/template') }}" class="btn btn-success">View Templates</a>
                        @endif
                        @if ($action == 'Create')
                            {!! Form::submit('Create', ['class' => 'btn btn-primary bran-mng-bt']) !!}
                        @else
                            {!! Form::submit('Update', ['class' => 'btn btn-primary']) !!}
                        @endif

                    </div>
                </div>
            </div>

            {!! Form::close() !!}
        </div>
        <!-- /.box -->
    </div>
@stop

@section('scripts')
    <script type="text/javascript" src="{{ asset('vendor/summernote/codemirror.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendor/summernote/code-mirror-xml.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendor/summernote/code-mirror-formatting.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendor/summernote/summernote.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            var temp_code = $('#temp_code_hidden').val();
            availableshortCode(temp_code);
            $('#template').summernote({
                placeholder: 'Enter Template',
                height: 700,
                codemirror: { // codemirror options
                    theme: 'monokai',
                    lineNumbers: true,
                }
            });


            $('#temp_code').change(function(e) {
                var temp_code = $('#temp_code').val();
                availableshortCode(temp_code);
            });
        });

        $('.js-roles-placeholder-multiple').select2({
            placeholder: 'Select Role(s)'
        });
        $('#type').change(function (){
            if ($(this).val() == 'email') {
                $('#roles-parent').show();
            } else {
                $('#roles-parent').hide();
            }
        });
        // function selectTheme(id) {
        //     //alert(id);
        //     var URL_useTheme = "{{ URL::to('admin/template/getTheme') }}";
        //     var des = $('#themes_sel' + id).html();
        //     console.log(des);
        //     $.ajax({
        //         type: 'GET',
        //         data: {
        //             'design': des,
        //             '_token': _token
        //         },
        //         url: URL_useTheme,
        //         success: function(data) {
        //             //    console.log(data.name); 
        //             $('#template').summernote('code', data.name);
        //         },
        //         error: function(data) {

        //         }
        //     });

        // }


        function availableshortCode(temp_code) {
            switch (temp_code) {
                case "NOTES":
                    var short_code_avail = '[merchant_name], [author], [note], [merchant_view_link], [date_time]';
                    break;
                case "GPDF":
                    var short_code_avail = '[investor_name]';
                    break;
                case 'GRPDF':
                    var short_code_avail = '[investor_name], [heading]';
                    break;
                case "FREQA":
                    var short_code_avail = '[investor_name],[merchant_view_link]';
                    break;
                case "FREQR":
                    var short_code_avail = '[investor_name],[merchant_view_link]';
                    break;
                case "FUNDR":
                    var short_code_avail = '[merchant_name], [investor_name], [merchant_view_link], [amount], [investor_view_link]';
                    break;
                case "FREDT":
                    var short_code_avail = '[investor_name], [merchant_name], [merchant_view_link], [amount], [document_url]';
                    break;
                case "INVTR":
                    var short_code_avail = '[investor_name],[username],[password],[login_view],[android_view],[ios_view]';
                    break;
                case "MERC":
                    var short_code_avail = '[merchant_name],[username],[password],[login_view],[android_view],[ios_view]';
                    break;
                case "PENDL":
                    var short_code_avail = '[pending_payment_table]';
                    break;
                case "COMPC":
                    var short_code_avail = '[company_name]';
                    break;
                case "MCSS":
                    var short_code_avail = '[new_status], [merchant_name], [merchant_view_link]';
                    break;
                case 'MSAC':
                    var short_code_avail = '[merchant_name], [merchant_view_link]';
                    break;
                case 'MSPP':
                    var short_code_avail = '[merchant_name], [merchant_view_link], [days]';
                    break;
                case 'MSCS':
                    var short_code_avail = '[merchant_name], [merchant_view_link]';
                    break;
                case "PAYC":
                case "PAYCO":
                    var short_code_avail = '[percentage],[merchant_name],[merchant_view_link]';
                    break;
                case "PENDP":
                    var short_code_avail = '[days], [merchant_name], [merchant_view_link], [date]';
                    break;
                case "MPLCE":
                    var short_code_avail = '[merchant_view_link], [merchant_name]';
                    break;
                case "MERD":
                    var short_code_avail = '[merchant_view_link],[merchant_name],[creator],[merchant_details]';
                    break;
                case "RECR":
                    var short_code_avail = '[merchant_name],[yes_link],[no_link]';
                    break;
                case "LIQAL":
                    var short_code_avail =  '[investor_name], [amount], [action_link]';
                    break;
                case 'MPSYF':
                    var short_code_avail = '[merchant_name], [merchant_view_link]';
                    break;
                case 'DONP':
                    var short_code_avail = '[content]';
                    break;
                case 'INCOA':
                    var short_code_avail = '[username], [email], [phone], [company], [content]';
                    break;
                case 'INCOO':
                    var short_code_avail = '[username]';
                    break;
                case 'REQMM':
                    var short_code_avail = '[merchant_name], [merchant_view_link], [amount]';
                    break;
                case 'REPOF':
                case 'CRMMU':
                case 'CRMMC':
                    var short_code_avail = '[merchant_name], [merchant_view_link]';
                    break;
                case 'CRMIC':
                case 'CRMIU':
                    var short_code_avail = '[investor_name], [investor_view_link]';
                    break;
                case 'INSUA':
                case 'INSUO':
                    var short_code_avail = '[investor_name], [date_time], [email], [phone]';
                    break;
                case 'CFWDD':
                    var short_code_avail = '[merchant_id], [amount]';
                    break;
                case 'IOID':
                    var short_code_avail = '[investor_name], [content]';
                    break;
                case 'FAPN':
                    var short_code_avail = '[content]';
                    break;
                case 'MDBAT':
                    var short_code_avail = '[last_payment], [last_status]';
                    break;
                case 'DOLDL':
                    var short_code_avail = '[title], [total_count], [deleted_count], [date]';
                    break;
                case 'RERQA':
                    var short_code_avail = '[merchant_name], [merchant_view_link]';
                    break;
                case 'MACHC':
                    var short_code_avail = '[title], [count_total], [count_payment], [count_fee], [checked_time], [total_settled], [total_settled_payment], [total_settled_fee], [total_rcode], [total_rcode_amount], [total_rcode_fee]';
                    break;
                case 'RCOML':
                    var short_code_avail = '[title], [rcode_report_table]';
                    break;
                case 'MACHR':
                    var short_code_avail = '[count_total], [count_payment], [count_fee], [payment_date], [checked_time], [count_total_processing], [count_payment_processing], [count_fee_processing], [total_processed], [total_processed_payment], [total_processed_fee]';
                    break;
                case 'ACHSR':
                    var short_code_avail = '[payment_date], [count_total], [checked_time], [count_total_processing], [total_processed]';
                    break;
                case 'PYPS':
                    var short_code_avail = '[merchant_name], [merchant_view_link], [paused_type], [paused_by], [paused_at]';
                    break;
                case 'PYRS':
                    var short_code_avail = '[merchant_name], [merchant_view_link], [resumed_by], [resumed_at]';
                    break;
                case 'MRTD':
                    var short_code_avail = '[merchant_name], [payment_link]';
                    break;
                case 'PYMNT':
                case 'PYMNA':
                    var short_code_avail = '[merchant_name], [content], [amount], [date], [card_number]';
                    break;
                case 'IAPR':
                    var short_code_avail = '[date], [totalCount], [debitAcceptedAmount], [creditAcceptedAmount], [debitProcessingAmount], [creditProcessingAmount], [debitReturnedAmount], [creditReturnedAmount], [checked_time]';
                    break;
                case 'IARR':
                    var short_code_avail = '[date], [totalCount], [debitAcceptedAmount], [creditAcceptedAmount], [debitReturnedAmount], [creditReturnedAmount], [checked_time]';
                    break;
                case 'ACRR':
                    var short_code_avail = '[investor_view_link], [investor_name], [type], [amount], [date], [liquidity]';
                    break;
                case 'ACDR':
                    var short_code_avail = '[Creator], [type], [creator_name], [text_type], [amount], [date]';
                    break;
                case 'ACSR':
                    var short_code_avail = '[investor_view_link], [investor_name], [type], [amount], [date], [liquidity]';
                    break;
                case 'ACDP':
                    var short_code_avail = '[date], [totalCount], [confirm_url]';
                    break;
                case 'MACHD':
                    var short_code_avail = '[date_time], [count]';
                    break;
                case 'MUNIT':
                    var short_code_avail = '[date_time], [type], [count]';
                    break;
                case 'TWFEN':
                    var short_code_avail = '[email]';
                    break;
                case 'TWFD':
                    var short_code_avail = '[email], [action_link]';
                    break;
                case 'ACHDF':
                    var short_code_avail = '[future_payments_count], [merchant_name], [makeup_payments], [default_payment_amount], [url]';
                    break;
                case 'MACC':
                    var short_code_avail = '[creator_name], [merchant_name], [merchant_view_link], [payment_amount], [checked_time]';
                    break;
                default:
                    var short_code_avail = '[name], [offer], [title]';
                    break;
            }

            $('#shorcodes').html(short_code_avail);

        }

    </script>
@stop

@section('styles')
    <link href="{{ asset('/css/optimized/create_new_investor.css?ver=5') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/css/optimized/create_merchant.css?ver=5') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/css/optimized/create_new_branch_manager.css?ver=5') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/css/bower_components/breadcrumbs/breadcrumb1.css?ver=5') }}" rel="stylesheet"
        type="text/css" />
    <link rel="stylesheet" type="text/css" href="{{ asset('css/bower_components/summernote/codemirror.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/bower_components/summernote/monokai-theme.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/bower_components/summernote/summernote.min.css') }}">
    <style type="text/css">
        .skin-blue .note-editor .content {
            padding: 45px 30px 15px 30px !important;
            border: 0;
            font-size: 26px;
            line-height: 44px;
            color: #1f244c;
            letter-spacing: -0.04em;
            background: #fff;
            font-family: Arial, sans-serif, Helvetica, Verdana;
            font-weight: 700;
        }
        .skin-blue .note-editor table td.btn-wrap {
            display: table-cell;
            width: unset;
        }
        .note-editor .note-editing-area .note-editable table td, .note-editor .note-editing-area .note-editable table th {
            border:0;
        }
        .note-editor .note-editing-area .note-editable table[border="1"] td, .note-editor .note-editing-area .note-editable table[border="1"] th {
            border: 1px solid #100947;
        }
        .note-editable>tr {display:block;width:100%;}
        .note-editable td.download-icons, .note-editable .full-width {
            display: table;
            width: 100%;
        }
        .note-editable .banner img {
            min-width: 726px;
        }
        #select2-roles-container {
            margin: 0;
        }
    </style>
@endsection
