@extends('layouts.admin.admin_lte')

@section('content')

    <div class="inner admin-dsh header-tp">

        <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($title)?$title:''}}</h3>
        <a href="#" class="help-link">
            <i class="fa fa-question-circle" aria-hidden="true"></i>
            <div class="tool-tip">FAQ</div>
        </a>

    </div>

    @if(\Request::getRequestUri() == "/admin/investors/faq")
    {{ Breadcrumbs::render('InvestorFAQList') }}
    @endif
    @if(\Request::getRequestUri() == "/admin/merchants/faq")
    {{ Breadcrumbs::render('merchantFaq') }}
    @endif

    <div class="col-md-12">
        <div class="box">
            <div class="box-head ">
                @include('layouts.admin.partials.lte_alerts')

            </div>
            <div class="box-body">
                <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                    <div class="row">
                        <div class="col-sm-10"></div>
                        <div class="col-sm-12 btn-adm" style="padding-bottom:15px">
                            <a href="{{$create}}" class="btn btn-primary admin-btn">Create New</a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 table-responsive">
                            {!! $tableBuilder->table(['class' => 'table table-bordered '],true) !!}
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
    <link href="{{ asset('/css/optimized/admin_user.css?ver=5') }}" rel="stylesheet" type="text/css" />

@stop