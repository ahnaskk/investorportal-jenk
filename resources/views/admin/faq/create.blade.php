@extends('layouts.admin.admin_lte')

@section('content')
    <div class="inner admin-dsh header-tp">

        <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
        <a href="#" class="help-link">
            <i class="fa fa-question-circle" aria-hidden="true"></i>
            <div class="tool-tip">{{isset($page_title)?$page_title:''}}</div>
        </a>

    </div>
    @php 

    @endphp
    @if(\Request::getRequestUri() == "/admin/investors/faq/create")
    {{ Breadcrumbs::render('investorsCreateFAQ') }}
    @elseif(\Request::getRequestUri() == "/admin/merchants/faq/create")
    {{ Breadcrumbs::render('merchantFaqCreate') }}
    @endif
    <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary box-sm-wrap">


            {!! Form::open(['url'=>$route, 'method'=>'POST' ,'id' => 'faqForm']) !!}
            @include('admin.faq.form')






        </div>
        <!-- /.box -->


    </div>


@stop
