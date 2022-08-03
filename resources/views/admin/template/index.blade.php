@extends('layouts.admin.admin_lte')
@section('content')

<div class="inner admin-dsh header-tp">
    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">{{isset($page_title)?$page_title:''}}</div>     
      </a>      
  </div>
  {{ Breadcrumbs::render('admin::template::index') }}
  <div class="col-md-12">
    <div class="box">
        <div class="box-head ">
            @include('layouts.admin.partials.lte_alerts')
        </div>

        <div class="box-body">
             <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                 <div class="row">
                    <div class="col-sm-auto">
                        {!! Form::select('filter_template', $template_type, null, ['id' => 'template_type', 'placeholder' => 'Select Type', 'class' => 'form-control']) !!}
                        <span class="help-block fw-bold">Template Type</span>
                    </div>
                    <div class="col-sm-2">
                        <input type="button" value="Apply Filter" class="btn btn-success" id="template_filter">
                    </div>
                    <div class="col-sm-5"></div>
                      @if(@Permissions::isAllow('Template Management','Create'))
                      <div class="col-sm-2" style="padding-bottom:15px">
                      <a href="{{route('admin::template::create')}}" class="btn btn-primary" style="float:right; margin-bottom:8px">Create Template</a>
                     </div>
                     @endif
                     </div>
                     <br>
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
<script type="text/javascript">
    $(document).ready(function(){
        var table = window.LaravelDataTables["branch"];
        $('#template_filter').click(function(e){
            var type = $('#template_type').val();
            table.draw();
        });
    });
</script>
@stop

@section('styles')
     <link href="{{ asset('/css/bower_components/breadcrumbs/breadcrumb1.css?ver=5') }}" rel="stylesheet" type="text/css" />
     <link href="{{ asset('/css/optimized/branch_manager.css?ver=5') }}" rel="stylesheet" type="text/css" />
    <style>
        span.help-block {
            color: #48486E;
        }
    </style>
@stop