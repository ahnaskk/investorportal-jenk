@extends('layouts.admin.admin_lte')

@section('content')
    <div class="inner admin-dsh header-tp">

        <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
        <a href="#" class="help-link">
            <i class="fa fa-question-circle" aria-hidden="true"></i>
            <div class="tool-tip">{{isset($page_title)?$page_title:''}}</div>
        </a>

    </div>
    @if(\Request::getRequestUri() == "/admin/investors/faq/$id/edit")
    {{ Breadcrumbs::render('investorEditFAQ') }}
    @elseif(\Request::getRequestUri() == "/admin/merchants/faq/$id/edit")
    {{ Breadcrumbs::render('merchantFaqEdit') }}
    @endif
    <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary box-sm-wrap">



       
            @include('admin.faq.form')



      


        </div>
        <!-- /.box -->


    </div>


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

