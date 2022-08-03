@extends('layouts.admin.admin_lte')

@section('content')
<div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{ $page_title }}</h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">{{ $page_title }}</div>     
      </a>
      
  </div>
  {{ Breadcrumbs::render('merchantBankAccounts',$merchant) }}
  <div class="col-md-12">
    <div class="box">
        <div class="box-head ">
            @include('layouts.admin.partials.lte_alerts')

        </div>

        <div class="box-body">
            <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">

              <div class="row">
                    <div class="col-sm-10"></div>
                    @if(@Permissions::isAllow('Bank','Create')) 
                     <div class="col-sm-2" style="padding-bottom:15px">
                      <a href="{{ route('admin::merchants::bank.create', ['merchant_id' => $merchant_id]) }}" class="btn btn-primary admin-btn">Create Bank Account </a>
                     </div>
                     @endif
                </div>
                
                <div class="row">
                    <div class="col-sm-12 table-responsive">
                        {!! $tableBuilder->table(['class' => 'table table-bordered'],true) !!}
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

       </script>
        
        

@stop

@section('styles')
   <link href="{{ asset('/css/optimized/create_bank_account.css?ver=5') }}" rel="stylesheet" type="text/css" />
   <link href="{{ asset('/css/bower_components/breadcrumbs/breadcrumb1.css') }}" rel="stylesheet" type="text/css" />
@stop