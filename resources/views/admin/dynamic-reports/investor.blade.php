@extends('layouts.admin.admin_lte')
@section('content')

    <div class="inner admin-dsh header-tp">
        <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{ isset($page_title) ? $page_title : '' }} </h3>
        <a href="#" class="help-link">
            <i class="fa fa-question-circle" aria-hidden="true"></i>
            <div class="tool-tip">{{ isset($page_title) ? $page_title : '' }}</div>
        </a>
    </div>
    {{-- {{ Breadcrumbs::render('admin::template::index') }} --}}
    <div class="col-md-12">
        <div class="box">
            <div class="box-head ">
                @include('layouts.admin.partials.lte_alerts')
            </div>
            <div class="box-body">

                <form method="post" action="{{route('admin::dynamic-report.store')}}">
                <div class="row align-items-center">
                    <h2>Add Custom View</h2>
                     

                  
                     @csrf
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Name for Report <span class="validate_star">*</span></label>
                            <input class="form-control" required="required" placeholder="Name for Report" name="name" type="text">
                        </div>
                    </div>  
                    <div class="col-auto my-1">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" >
                            <label class="form-check-label">
                                Set as Default
                            </label>
                        </div>
                    </div>
                   <div class="input-group">
                        <span class="help-block">Select Required Columns</span>
                        <div class="input-group-text">
                            <span class="glyphicon glyphicon-th-list" aria-hidden="true"></span>
                        </div>

                        {{Form::select('field_keys[]',$all_fields,0,['class'=>'form-control js-investor-placeholder-multiple','id'=>'field_keys','multiple'=>'multiple'])}}
                    </div>
                </div>
                <hr/>
                <div class="row" id="dynamic-fields">
                    <h3>Choose filter conditions:</h3>
                    <h4>All Conditions(All conditions must be met)</h4>
                    <div class="row">
                        <div class="form-group col-md-3">
                            <select class='select-to-select2' multiple>
                                <option value='1'>option1</option>
                                <option value='2'>option2</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <select class='select-to-select2' multiple>
                                <option value='1'>option1</option>
                                <option value='2'>option2</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <select class='select-to-select2' multiple>
                                <option value='1'>option1</option>
                                <option value='2'>option2</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <button style="background-color:dodgerblue !important" id="add-row" class="btn btn-info">
                            +
                            </button>
                        </div>
                    </div>
                </div>
                <div class="row">
                        <div class="col-md-3 offset-md-6">
                            <button class="btn btn-primary pull-right" style="margin-left:10px" type="submit">Cancel</button>
                            <button class="btn btn-info pull-right" type="submit">Generate</button>
                        </div>
                    </div>



</form>




                <div id="CreateNewReport2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                    <div class="row">


                        <!-- <div class="col-sm-10" style="padding-bottom:15px">
                            <a style="background-color:dodgerblue !important" class="btn btn-info"
                                href="{{ url()->previous() }}"><span class="glyphicon glyphicon-backward"
                                    aria-hidden="true"></span> Back</a>
                        </div> -->
                        <div class="col-sm-2" style="padding-bottom:15px">

                            <!-- <button style="float:right; margin-bottom:8px;background-color:dodgerblue !important"
                                type="button" class="btn btn-info" data-bs-toggle="modal"
                                data-bs-target="#CreateNewReportModal">
                                <span class="glyphicon glyphicon glyphicon-book" aria-hidden="true"></span>
                                Create a dynamic report
                            </button> -->

                        </div>

                    </div>
                    <div class="row">
                        <div class="col-sm-12 table-responsive">
                            {!! $tableBuilder->table(['class' => 'table table-bordered','id'=>'branch'],true) !!}
                        </div>
                    </div>
                </div>
            </div>

            <!-- /.box-body -->
        </div>
    </div>





   
@stop


@section('scripts')

    {!! $tableBuilder->scripts() !!}

    <script>
        $(".js-investor-placeholder-multiple").select2({
        placeholder: "Select Columns for reports"
    });
        var table = window.LaravelDataTables["branch"];

        $(document).ready(function() {

            $('.create_submit').click(function(e) {
                $('.create').hide();
                $('.please-wait').show();
                setTimeout(function() {
                    $('.create').show();
                    $('.please-wait').hide();
                }, 1000);
            });

            $("#branch").on("click", ".delete_report", function() {
                var id = $(this).data("id");
                if (confirm('Do you really want to delete the selected report ?')) {
                    $.ajax({
                        type: 'POST',
                        data: {
                            '_token': _token,
                            _method: 'delete'
                        },
                        url: "{{ url('admin/dynamic-report-investor') }}" + '/' + id,
                        success: function(data) {
                            table.ajax.reload(null, false);
                        }
                    });
                }
            });




            $("#branch").on("click", ".edit_report", function() {
                var id = $(this).data("id");
                $.ajax({
                    type: 'GET',
                    url: "{{ url('admin/dynamic-report-investor') }}" + '/' + id + '/edit',
                    success: function(data) {
                        console.log(data);
                        var post_url = "{{ url('admin/dynamic-report-investor') }}" + '/' + id;
                        $('#editReportForm').attr('action', post_url);
                        $('#edit_name').val(data.name);
                        $('#edit_description').val(data.description);
                        //table.ajax.reload(null, false);
                    }
                });
            });

            $('#apply').click(function(e) {
                table.ajax.reload();
            });

            $('.decimal').keypress(function(e) {
                var character = String.fromCharCode(e.keyCode)
                var newValue = this.value + character;
                if (isNaN(newValue) || parseFloat(newValue) * 100 % 1 > 0) {
                    e.preventDefault();
                    return false;
                }
            });
            $('.merchant_fields').hide();
            $('.field_keys').hide();
            $('#report_type').on('change', function() {
                if (this.value === '1') {
                    $('.merchant_fields').hide();
                    $('.field_keys').show();
                } else {
                    $('.merchant_fields').show();
                    $('.field_keys').hide();
                }

            });


            function initializeSelect2(selectElementObj) {
                selectElementObj.select2();
            }

            $(".select-to-select2").each(function() {
                initializeSelect2($(this));
            });

            $("#add-row").on("click", function() {
                var htmlString = `<div class="row">
                    <div class="form-group col-md-3">
                        <select class='select-to-select2' multiple>
                            <option value='1'>option1</option>
                            <option value='2'>option2</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <select class='select-to-select2' multiple>
                            <option value='1'>option1</option>
                            <option value='2'>option2</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <select class='select-to-select2' multiple>
                            <option value='1'>option1</option>
                            <option value='2'>option2</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <button id="remove-row" class="btn btn-danger"> - </button>
                    </div>
                    </div>`;
                var domNodes = $($.parseHTML(htmlString))
                $('#dynamic-fields').append(domNodes);
                domNodes.each(function() {
                    initializeSelect2($(this).find('select'));
                })
                $(document).on('click','#remove-row', function(){
                    $(this).closest('div.row').remove();
                })
            });


        });
    </script>
@stop

@section('styles')
    <link href="{{ asset('/css/bower_components/breadcrumbs/breadcrumb1.css?ver=5') }}" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('/css/optimized/branch_manager.css?ver=5') }}" rel="stylesheet" type="text/css" />

    <link href="{{ asset('/css/optimized/genarated_csv_pdf.css?ver=5') }}" rel="stylesheet" type="text/css" />
    <style>
        .select2-container--open .select2-dropdown--below {
            z-index: 9999;
        }

        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            margin: 0;
        }

    </style>


@stop
