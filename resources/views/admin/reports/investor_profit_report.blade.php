@extends('layouts.admin.admin_lte')

@section('content')
<div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">{{isset($page_title)?$page_title:''}}</div>     
      </a>
      
  </div>
  {{ Breadcrumbs::render('admin::reports::investor-profit-report') }}
    <div class="col-md-12">

    <div class="box">
        <div class="box-body">
       
                    <div class="form-box-styled" >
                   
                      <div class="row">
                  
                        
                        <div class="col-md-6">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                                </div>

                                {{Form::select('investors[]',$investors,'',['class'=>'form-control js-investor-placeholder-multiple','id'=>'investors','multiple'=>'multiple'])}}

                            </div>
                            <span class="help-block">Investors</span>
                        </div>

                        <div class="col-md-6">
                          <div class="btn-box">
                          <div class="input-group">
                              <input type="button" value="Apply Filter" class="btn btn-success" id="apply"
                                             name="Apply Button">

                            </div>
                          </div>
                        </div>
                        </div>
                     
                    </div>
                  
          


            <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                        <div class="table-container grid table-responsive" > 
                            {!! $tableBuilder->table(['class' => 'table table-bordered'], true) !!}
                        </div>
                    
            </div>  </div>
        </div>
        <!-- /.box-body -->
    </div>
  </div>

@stop

@section('scripts')
  {!! $tableBuilder->scripts() !!}
  <script src="{{ asset('/js/custom/report.js') }}"></script> 
@stop

@section('styles')
 <link href="{{ asset('/css/optimized/Investor_Profit_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />
 <link href="{{ asset('/css/bower_components/breadcrumbs/breadcrumb1.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop