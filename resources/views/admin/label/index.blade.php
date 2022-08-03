@extends('layouts.admin.admin_lte')

@section('content')

<div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{$page_title}} </h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">{{$page_title}}</div>     
      </a>
      
  </div>
{{ Breadcrumbs::render('admin::label::index') }}
<div class="col-md-12">
    <div class="box">
        <div class="box-head ">
            @include('layouts.admin.partials.lte_alerts')

        </div>
        <div class="box-body">
            <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                <div class="row">
                    <div class="col-sm-10"></div>
                     @if (!Auth::user()->hasRole('viewer')) 
                     @if(@Permissions::isAllow('Settings Label','Create'))
                    <div class="col-sm-2" style="padding-bottom:15px">

                        <a href="{{route('admin::label::create')}}" class="btn btn-primary" style="float: right;">Add Label</a>

                    </div>
                     @endif
                    @endif
                </div>
      

                        {!! $tableBuilder->table(['class' => 'table table-bordered','id'=>'status'],true);
                            $tableBuilder->parameters(['stateSave' => true])
                         !!}
      
           
            </div>
        </div>
        <!-- /.box-body -->
    </div>
</div>
@stop

@section('scripts')

   {!! $tableBuilder->scripts() !!}
        
@stop

@section('styles')
 <link href="{{ asset('/css/bower_components/breadcrumbs/breadcrumb1.css?ver=5') }}" rel="stylesheet" type="text/css" />
 <link href="{{ asset('/css/optimized/Sub_Status.css?ver=5') }}" rel="stylesheet" type="text/css" />
 
@stop