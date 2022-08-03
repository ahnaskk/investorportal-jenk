@extends('layouts.admin.admin_lte')

@section('content')

 <?php
                 $date_end = date('Y-m-d');
                 $date_start = date('Y-m-d', strtotime('-1 days', strtotime($date_end)));
                 //dd();
         ?>
<div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>Distributions </h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Distributions</div>     
      </a>

</div>

{{ Breadcrumbs::render('admin::vdistribution::lists') }}
    <div class="col-md-12">
    <div class="box">
        <div class="box-head ">
            @include('layouts.admin.partials.lte_alerts')

        </div>
        <div class="box-body">

                <div class="filter-group-wrap form-box-styled" >
                    {{Form::open(['url'=>route('admin::vdistribution::export')])}}

                    <div class="filter-group" >

                        <div class="row">
                        <div class="col-md-4">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                                </div>
                                <input class="form-control datepicker" autocomplete="off" id="date_start1" value="{{ $date_start }}" name="date_start1" placeholder="{{\FFM::defaultDateFormat('format')}}"
                                       type="text"/>
                                <input type="hidden" name="date_start" class="date_parse" id="date_start" value="{{ $date_start }}">
                            </div>
                            <span class="help-block">From Date</span>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                                </div>
                                <input class="form-control datepicker" id="date_end1" autocomplete="off" value="{{ $date_end }}" name="date_end1" placeholder="{{\FFM::defaultDateFormat('format')}}"
                                       type="text"/>
                                <input type="hidden" name="date_end" class="date_parse" id="date_end" value="{{ $date_end }}">
                            </div>
                            <span class="help-block">To Date</span>
                        </div>



                    <div class="col-md-4 btn-box">
                        <div class="input-group">
                            <input type="button" value="Apply Filter" class="btn btn-success" id="date_filter"
                                   name="student_dob">
                             @if(@Permissions::isAllow('Velocity Distributions','Download'))
                            {{Form::submit('download',['class'=>'btn btn-primary','id'=>'form_filter'])}}
                            @endif

                        </div>
                    </div>
                    </div>


                    {{Form::close()}}
                </div>
            </div>

            <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrapc">
                <div class="row">
                    <div class="col-md-10 col-sm-12"></div>              
                    <div class="col-md-2 col-sm-12 velo-dr" style="padding-bottom:15px">
                          @if(@Permissions::isAllow('Velocity Distributions','Create'))
                        <a href="{{route('admin::vdistribution::createVdistribution')}}" class="btn btn-primary">Create Velocity Distribution</a>
                            @endif
                    </div>

                </div> 

                    <div class="table-responsive">
                        {!! $tableBuilder->table(['class' => 'table table-bordered distributions'],true);
                        !!}
                    </div>

            </div>

        <!-- /.box-body -->
    </div>
</div>
@stop

@section('scripts')




        {!! $tableBuilder->scripts() !!}


    <script type="text/javascript">

        var table = window.LaravelDataTables["dataTableBuilder"];
        $("#date_start1").change(function(){
            localStorage.setItem('lastDateStart' ,$('#date_start').val())
        })
        $("#date_end1").change(function(){
            localStorage.setItem('lastDateEnd', $('#date_end').val())
        })

        if(performance.navigation.type == 2){
            $(document).ready(function(){
                $('#date_end').attr('value', localStorage.getItem('lastDateEnd'));
                $('#date_start').attr('value', localStorage.getItem('lastDateStart'));
                $('#date_start1').attr('value', moment(localStorage.getItem('lastDateStart'), 'YYYY-MM-DD').format("{{\FFM::defaultDateFormat('format')}}"));
                $('#date_end1').attr('value', moment(localStorage.getItem('lastDateEnd'), 'YYYY-MM-DD').format("{{\FFM::defaultDateFormat('format')}}"));
                table.draw()
            })
        }
        $(document).ready(function () {







            $('#date_filter').click(function (e) {
                e.preventDefault();
                table.draw();

            });

     });

    </script>


@stop

@section('styles')

  <link href="{{ asset('/css/bower_components/breadcrumbs/breadcrumb1.css?ver=5') }}" rel="stylesheet" type="text/css" />
  <link href="{{ asset('/css/optimized/velocity_distributions.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop
