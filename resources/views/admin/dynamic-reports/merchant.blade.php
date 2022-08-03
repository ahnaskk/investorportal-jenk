@extends('layouts.admin.admin_lte')
@section('content')

    <div class="inner admin-dsh header-tp">
        <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
        <a href="#" class="help-link">
            <i class="fa fa-question-circle" aria-hidden="true"></i>
            <div class="tool-tip">{{isset($page_title)?$page_title:''}}</div>
        </a>
    </div>
    {{--{{ Breadcrumbs::render('admin::template::index') }}--}}
    <div class="col-md-12">
        <div class="box">
            <div class="box-head ">
                @include('layouts.admin.partials.lte_alerts')
            </div>

            <div class="box-body">





                @isset($dynamic_data)
                <div class="form-group">
                    <div class="filter-group-wrap" >
                        <div class="filter-group" >
                            {{Form::open(['route'=>'admin::reports::investor-export','id'=>'investor-form'])}}
                        </div>
                        <div class="form-box-styled">
                            <div class="row">
                                <span class="help-block">Investors </span>
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <div class="input-group-text">
                                            <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                                        </div>

                                        {{Form::select('investors[]',$investors,1,['class'=>'form-control js-investor-placeholder-multiple','id'=>'investors','multiple'=>'multiple'])}}

                                    </div>

                                </div>


                                <div class="col-md-4">
                                    <div class="input-group">
                                        <div class="input-group-text">
                                            <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
                                        </div>
                                        <input class="form-control datepicker" autocomplete="off" id="date_start1" value="{{ date('Y-m-d', strtotime('-1 months', strtotime(date('Y-m-d')))) }}" name="date_start1" placeholder="{{\FFM::defaultDateFormat('format')}}" type="text"/>
                                        <input type="hidden" name="date_start" id="date_start" value="{{ date('Y-m-d', strtotime('-1 months', strtotime(date('Y-m-d')))) }}" class="date_parse">
                                    </div>
                                    <span class="help-block">From Date</span>
                                </div>

                                <div class="col-md-4">
                                    <div class="input-group">
                                        <div class="input-group-text">
                                            <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                                        </div>
                                        <input class="form-control datepicker" autocomplete="off" value="{{ date('Y-m-d') }}" id="date_end1" name="date_end1" placeholder="{{\FFM::defaultDateFormat('format')}}"
                                               type="text"/>
                                        <input type="hidden" name="date_end" id="date_end" value="{{ date('Y-m-d') }}" class="date_parse">
                                    </div>
                                    <span class="help-block">To Date</span>
                                </div>

                                @foreach($field_keys as $key)
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <div class="input-group-text">
                                                <span class="fa fa-money" aria-hidden="true"></span>
                                            </div>
                                            <input class="form-control decimal" autocomplete="off" value="" id="start_{{$key}}" name="start_{{$key}}" placeholder="0"    type="number"/>
                                            <input class="form-control decimal" autocomplete="off" value="" id="end_{{$key}}" name="end_{{$key}}" placeholder="0"    type="number"/>
                                        </div>
                                        <span class="help-block">{{changeCase($key)}}</span>
                                    </div>
                                @endforeach

                                {{-- @foreach($field_keys as $field_key)
                                 <div class="col-md-4">
                                     <div class="input-group">
                                         <div class="input-group-text">
                                             <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                                         </div>
                                         <input class="form-control datepicker" autocomplete="off" value="{{ date('Y-m-d') }}" id="date_end1" name="date_end1" placeholder="{{\FFM::defaultDateFormat('format')}}"
                                                type="text"/>
                                         <input type="hidden" name="date_end" id="date_end" value="{{ date('Y-m-d') }}" class="date_parse">
                                     </div>
                                     <span class="help-block">To Date</span>
                                 </div>
                                 @endforeach--}}


                                <div class="col-md-12">
                                    <div class="btn-wrap btn-right">
                                        <div class="btn-box inhelpBlock ">
                                            <input type="button" value="Apply Filter" class="btn btn-primary" id="apply"
                                                   name="Apply Button">

                                            <div class="blockCust pull-right">

                                            </div>
                                        </div>
                                    </div>

                                </div>


                            </div>
                        </div>
                    </div>
                </div>

                {{Form::close()}}
                @endisset










                <div id="CreateNewReport2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                    <div class="row">


                        <div class="col-sm-10" style="padding-bottom:15px">
                            <a style="background-color:dodgerblue !important"  class="btn btn-info" href="{{ url()->previous() }}"><span class="glyphicon glyphicon-backward" aria-hidden="true"></span> Back</a>
                        </div>
                        <div class="col-sm-2" style="padding-bottom:15px">

                            <button  style="float:right; margin-bottom:8px;background-color:dodgerblue !important" type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#CreateNewReportModal">
                                <span class="glyphicon glyphicon glyphicon-book" aria-hidden="true"></span>
                                Create a dynamic report
                            </button>

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



    <div class="modal fade" id="editReport"  aria-labelledby="CreateNewReportModalLabel" aria-hidden="true">
        <form id="editReportForm" method="post" action="">
            @method('PATCH')
            @csrf
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="CreateNewReportModalLabel">Edit Dyanamic Report</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        <div class="input-group">
                            <label for="edit_name" class="help-block">Report Name</label>
                            <input required id="edit_name" type="text" name="name" class="form-control">
                        </div>
                        <div class="input-group">
                            <label for="edit_description" class="help-block">Description</label>
                            <textarea required class="" name="description" id="edit_description" cols="100%" rows="10"></textarea>
                        </div>


                        <div class="input-group">
                            <span class="help-block">Select Required Columns</span>
                            <div class="input-group-text">
                                <span class="glyphicon glyphicon-th-list" aria-hidden="true"></span>
                            </div>

                            {{Form::select('field_keys[]',$all_fields,0,['class'=>'form-control js-investor-placeholder-multiple','id'=>'edit_field_keys','multiple'=>'multiple'])}}
                        </div>


                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Create</button>
                    </div>
                </div>
            </div>
        </form>
    </div>


    <!-- Modal -->
    <div class="modal fade" id="CreateNewReportModal"  aria-labelledby="CreateNewReportModalLabel" aria-hidden="true">
        <form method="post" action="{{route('admin::dynamic-report-investor.store')}}">
            @csrf
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="CreateNewReportModalLabel">Create new Dyanamic Report</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        <!--                <div class="input-group">
                                            <label for="edit_name" class="help-block"> Report Type</label>
                                            <select name="report_type" id="report_type" class="form-control">
                                                <option value="1">Investor</option>
                                                <option value="2">Merchant</option>
                                            </select>
                                        </div>-->

                        <div class="input-group">
                            <label for="name" class="help-block">Report Name</label>
                            <input required id="name" type="text" name="name" class="form-control">
                        </div>

                        <div class="input-group">
                            <label class="help-block">Description</label>
                            <textarea required class="" name="description" id="description" cols="100%" rows="10"></textarea>
                        </div>


                        <div class="input-group">
                            <span class="help-block">Select Required Columns</span>
                            <div class="input-group-text">
                                <span class="glyphicon glyphicon-th-list" aria-hidden="true"></span>
                            </div>

                            {{Form::select('field_keys[]',$all_fields,0,['class'=>'form-control js-investor-placeholder-multiple','id'=>'field_keys','multiple'=>'multiple'])}}
                        </div>


                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Create</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@stop


@section('scripts')

    {!! $tableBuilder->scripts() !!}

    <script>
		$(".js-investor-placeholder-multiple").select2({
			                                               placeholder: "Select Columns for reports"
		                                               });
		var table = window.LaravelDataTables["branch"];

		$(document).ready(function () {

			$("#branch").on("click", ".delete_report", function () {
				var id = $(this).data("id");
				if (confirm('Do you really want to delete the selected report ?')) {
					$.ajax({
						       type: 'POST',
						       data: {
							       '_token': _token,
							       _method: 'delete'
						       },
						       url: "{{url('admin/dynamic-report-investor')}}" + '/' + id,
						       success: function (data) {
							       table.ajax.reload(null, false);
						       }
					       });
				}
			});




			$("#branch").on("click", ".edit_report", function () {
				var id = $(this).data("id");
				$.ajax({
					       type: 'GET',
					       url: "{{url('admin/dynamic-report-investor')}}" + '/' + id+ '/edit',
					       success: function (data) {
						       console.log(data);
						       var post_url = "{{url('admin/dynamic-report-investor')}}" + '/' + id;
						       $('#editReportForm').attr('action', post_url);
						       $('#edit_name').val(data.name);
						       $('#edit_description').val(data.description);
						       //table.ajax.reload(null, false);
					       }
				       });
			});

			$('#apply').click(function (e) {
				table.ajax.reload();
			});

			$('.decimal').keypress(function (e) {
				var character = String.fromCharCode(e.keyCode)
				var newValue = this.value + character;
				if (isNaN(newValue) || parseFloat(newValue) * 100 % 1 > 0) {
					e.preventDefault();
					return false;
				}
			});

		});
    </script>
@stop

@section('styles')
    <link href="{{ asset('/css/bower_components/breadcrumbs/breadcrumb1.css?ver=5') }}" rel="stylesheet" type="text/css" />
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