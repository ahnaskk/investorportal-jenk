@extends('layouts.admin.admin_lte')

@section('content')

	<?php
	$date_end = date('Y-m-d');
	$date_start = date('Y-m-d', strtotime('-1 days', strtotime($date_end)));
	?>

    <div class="inner admin-dsh header-tp">

        <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
        <a href="#" class="help-link">
            <i class="fa fa-question-circle" aria-hidden="true"></i>
            <div class="tool-tip">Profit Carry Forward</div>
        </a>

    </div>

    <div class="col-md-12">

        <div class="box">
            <div class="box-body">


                <div class="form-box-styled">
                    {{Form::open(['route'=>'admin::get-profit-carryforwards-data'])}}

                    <div class="row">
                        <div class="col-lg-3">
							<span class="help-block">From Date </span>
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                                </div>
                                <input autocomplete="off" class="form-control datepicker" id="date_start1"
                                       name="start_date1" placeholder="{{\FFM::defaultDateFormat('format')}}"
                                       type="text" value="{{$date_start}}"/>
                                <input type="hidden" name="start_date" id="date_start" value="{{$date_start}}"
                                       class="date_parse">
                            </div>

                        </div>
                        <div class="col-lg-3">
							<span class="help-block">To Date</span>
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                                </div>
                                <input autocomplete="off" class="form-control datepicker" id="date_end1"
                                       name="end_date1" placeholder="{{\FFM::defaultDateFormat('format')}}"
                                       type="text" value="{{$date_end}}"/>
                                <input type="hidden" name="end_date" id="date_end" value="{{$date_end}}"
                                       class="date_parse">
                            </div>

                        </div>


                        <div class="col-lg-3">
							<span class="help-block">Type</span>
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                                </div>

                                {{Form::select('type[]',['1' => 'OverPayment', '2' => 'Profit','3' => 'RTR'],"",['class'=>'form-control','id'=>'type','multiple'=>'multiple'])}}
                            </div>
                        </div>

						<div class="col-lg-3">
							<span class="help-block">Merchants</span>
							<div class="input-group">
								<div class="input-group-text">
									<span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
								</div>

								{{Form::select('merchants[]',[],"",['class'=>'form-control js-merchant-placeholder-multiple','id'=>'merchants','multiple'=>'multiple'])}}
							</div>
						</div>

                        <div class=" col-lg-3">
							<span class="help-block">Investors </span>
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                                </div>

                                {{Form::select('investors[]',[],'',['class'=>'form-control js-investor-placeholder-multiple','id'=>'investors','multiple'=>'multiple'])}}

                            </div>
                        </div>

                        <div class="col-md-1">
							<span class="help-block">Filter </span>
                            <div class="btn-wrap btn-right">
                                <div class="btn-box">
                                    <input type="button" value="Apply Filter" class="btn btn-success" id="apply"
                                           name="Apply Button">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-1">
							<span class="help-block">Delete </span>
                            <div class="btn-wrap btn-right">
                                <div class="input-group">
                                    <a href="#" class="btn  btn-danger delete_multi" style="margin: 0 0 20px"
                                       id="delete_multi_investment_filter">
                                        Delete <span style="display: none;" id="i_count"></span> Filtered </a>
                                </div>
                            </div>
                        </div>


                    </div>

                    <div class="row">
                        <!--                        <div class="col-md-12">
                                                    <div class="btn-wrap btn-right">
                                                        <div class="btn-box">
                                                            <input type="button" value="Apply Filter" class="btn btn-success" id="apply" name="Apply Button">
                                                        </div>
                                                    </div>
                                                </div>-->


                        {{Form::close()}}
                    </div>


                    <div class="row">
                        <div class="offset-md-10 col-md-2">
                            <a href="#" class="btn  btn-danger delete_multi" style="margin: 0 0 20px"
                               id="delete_multi_investment"><i class="glyphicon glyphicon-trash"></i> Delete <span
                                        style="display: none;" id="i_count"></span> Selected </a>
                        </div>
                    </div>

                    <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                        <div class="grid table-responsive">
                        {!! $tableBuilder->table(['class' => 'table table-bordered'], true) !!}
                        <!--  <div class="blockCust pull-right" style="padding-bottom: 15px">
                        {{Form::submit('Download report',['class'=>'btn btn-primary','id'=>'form_filter'])}}
                                </div>-->
                        </div>
                    </div>

                </div>
                <!-- /.box-body -->
            </div>
        </div>

        @stop

        @section('scripts')
            <script src="{{ asset('/js/moment.min.js') }}"></script>
            <script src="{{ asset('/js/jquery-mask.min.js') }}"></script>
            {!! $tableBuilder->scripts() !!}
            <script src="{{ asset('/js/custom/report.js') }}"></script>
            <script src="{{ asset('/js/custom/merchantSelect2.js') }}"></script>
            <script src="{{ asset('/js/custom/investorSelect2.js') }}"></script> 
            <script>
				$('#delete_investment').on('click', function (e) {
					if ($(this).is(':checked', true)) {
						var count = 0;
						$(".delete_bulk_investments").prop('checked', true);
					} else {
						$(".delete_bulk_investments").prop('checked', false);
					}
				});
				$('#delete_payment').on('click', function () {
					if ($(this).is(':checked', true)) {
						var count = 0;
						$(".delete_bulk").prop('checked', true);
						$('.delete_bulk:checked').each(function () {
							count = count + 1;
						});
					} else {
						$('.delete_bulk').prop('checked', false);
					}
				});

				function uncheckMain() {
					var uncheck = 0;
					$('input:checkbox.delete_bulk').each(function () {
						if (!this.checked) {
							uncheck = 1;
							$('#delete_payment').prop('checked', false);
						}
					});
					if (uncheck == 0) {
						$('#delete_payment').prop('checked', true);
					}
				}

				function uncheckMainInvestment() {
					var uncheck = 0;
					$('input:checkbox.delete_bulk_investments').each(function () {
						if (!this.checked) {
							uncheck = 1;
							$('#delete_investment').prop('checked', false);
						}
					});
					if (uncheck == 0) {
						$('#delete_investment').prop('checked', true);
					}
				}


				$('#delete_multi_investment_filter').on('click', function () {
					var date_start = $('#date_start').val();
					var date_end = $('#date_end').val();
					var merchants = $('#merchants').val();
					var investors = $('#investors').val();
					var type = $('#type').val();

					if (confirm('Do you really want to delete the filtered  items?')) {
						$(".loadering").css("display", "block");
						$.ajax(
							{
								type   : 'POST',
								data   : {
									'date_start': date_start,
									'date_end'  : date_end,
									'merchants' : merchants,
									'investors' : investors,
									'type' : type,
									'_token'    : _token
								},
								url    : "{{route('admin::carryforwards.deletemultiple_filter')}}",
								success: function (data) {
									table.ajax.reload();
								}
							});
					}


				});


				$('#delete_multi_investment').on('click', function () {
					var el = this;
					var id_arr = [];
					var count = 0;
					$('.delete_bulk_investments:checked').each(function () {
						id_arr.push($(this).val());
						count = count + 1;
					});
					if (confirm('Do you really want to delete the selected (' + count + ') items?')) {

						if (id_arr.length > 0) {
							$(".loadering").css("display", "block");
							$.ajax({
								       type   : 'POST',
								       data   : {
									       'multi_id': id_arr,
									       '_token'  : _token
								       },
								       url    : "{{route('admin::carryforwards.deletemultiple')}}",
								       success: function (data) {
									       table.ajax.reload();
								       }
							       });
						} else {
							alert('Please select atleast one record to delete.');
						}
					}
				});


            </script>
        @stop

        @section('styles')
            <link href="{{ asset('/css/optimized/Investor_Assignment_Report.css?ver=5') }}" rel="stylesheet"
                  type="text/css"/>
            <link href="{{ asset('/css/bower_components/breadcrumbs/breadcrumb1.css?ver=5') }}" rel="stylesheet"
                  type="text/css"/>
            <style type="text/css">
				.mtop {
					top : -18px;
				}

				.dataTables_filter {
					display : none;
				}

            </style>

@stop
