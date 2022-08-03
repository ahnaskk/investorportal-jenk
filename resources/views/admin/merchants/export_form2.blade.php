@extends('layouts.admin.admin_lte')

@section('content')
<div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Revenue Recognition Export</div>     
      </a>
      
  </div>
  {{ Breadcrumbs::render('admin::merchants::export-deals2') }}
    <div class="col-md-12">
    <div class="box">
        <div class="box-head ">
            @include('layouts.admin.partials.lte_alerts')

        </div>
        <div class="box-body">
            <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                <div class="grid">
                    <div class="form-box-styled">
                        <h3 class="ex-fr">Revenue Recognition - Velocity</h3>
                        <div class="row">
                            {{Form::open(['route'=>'admin::merchants::export-deals2'])}}

                            <div class="row">
                <div class="form-group col-md-4">
                     
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                                </div>
                                <input class="form-control from_date1 datepicker" autocomplete="off" id="revenuedate1" name="date_start" placeholder="{{\FFM::defaultDateFormat('format')}}" type="text" value=""/>
                                <input type="hidden" name="date_start" class="date_parse" id="revenuedate" value="">
                            </div>
                </div>

                            <!-- <div class="col-md-4" >                                
                                {{Form::text('date_reveneue',$default_date,['class'=>'dat-exp form-control','id'=>'revenuedate',])}}
                            </div> -->
                            <div class="col-md-4" >
                                {{Form::submit("export",['class'=>'btn btn-success'])}}
                            </div>
                            {{Form::close()}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.box-body -->
    </div>
</div>
    <link href="{{ asset('/css/optimized/Revenue_Recognition.css?ver=5') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/css/bower_components/breadcrumbs/breadcrumb1.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop
@section('scripts')
 <script src="{{ asset ('bower_components/bootstrap-tagsinput-latest/dist/bootstrap-tagsinput.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('/js/moment.min.js') }}"></script>
<script src="{{ asset('/js/jquery-mask.min.js') }}"></script>
@stop

