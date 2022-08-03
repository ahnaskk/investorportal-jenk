@extends('layouts.admin.admin_lte')
@section('content')
<div class="inner admin-dsh header-tp">
  <h3><i class="fa fa-angle-right" aria-hidden="true"></i>Bank Details</h3>
  <a href="#" class="help-link">
    <i class="fa fa-question-circle" aria-hidden="true"></i>
    <div class="tool-tip">Bank Details List</div>
  </a>
</div>
{{ Breadcrumbs::render('Investorbank',$investor) }}
<div class="col-md-12">
  <div class="box">
    <div class="box-head ">
      @include('layouts.admin.partials.lte_alerts')
    </div>
    @php
    $id=isset($id)?$id:0;
    @endphp
    <div class="box-body">
      <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
        <div class="row">
          <div class="col-sm-10"></div>
          @if(@Permissions::isAllow('Bank Details','Create'))
          <div class="col-sm-2" style="padding-bottom:15px">
            <a href="{{ url('/admin/investors/bankCreate/'.$id) }}" class="btn btn-primary admin-btn">Create Bank Account </a>
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
  </div>
</div>
@stop
@section('scripts')
{!! $tableBuilder->scripts() !!}
<script>
$(document).ready(function() {
  window.onpopstate = function() {
    var table = $('#lender').DataTable();
    var info = table.page.info();
    var pageNo=info.page;
    if(pageNo==0) {
      pageNo=0;
    } else {
      pageNo=pageNo-1;
    }
    table.page(pageNo).draw(false);
  };
  history.pushState({}, '');
});
</script>
@stop
@section('styles')
<style type="text/css">
    .adminSelect .select2-hidden-accessible {
    display: none;
    }
    .breadcrumb {
        padding: 8px 15px;
        margin-bottom: 20px;
        list-style: none;
        background-color: #f5f5f5;
        border-radius: 4px;
    }
    .breadcrumb > li {
        display: inline-block;
    }
   li.breadcrumb-item a{
        color: #6B778C;
    }
    .breadcrumb > li + li::before {
        padding: 0 5px;
        color: #ccc;
        content: "/\00a0";
    }
    li.breadcrumb-item.active{
        color: #2b1871!important;
    }

</style>
<link href="{{ asset('/css/optimized/create_bank_account.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop
