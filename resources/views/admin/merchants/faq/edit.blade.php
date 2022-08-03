@extends('layouts.admin.admin_lte')

@section('content')
    <div class="inner admin-dsh header-tp">

        <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
        <a href="#" class="help-link">
            <i class="fa fa-question-circle" aria-hidden="true"></i>
            <div class="tool-tip">{{isset($page_title)?$page_title:''}}</div>
        </a>

    </div>
{{ Breadcrumbs::render('merchantEditFAQ',$merchant) }}
    <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary box-sm-wrap">


            <form method="POST" action="{{url("admin/merchants/$merchant_id/faq/$id")}}" accept-charset="UTF-8"  class="form-horizontal" enctype="multipart/form-data">
            @method('PATCH')
            @csrf
            @include('admin.merchants.faq.form')


        </div>
        <!-- /.box -->


    </div>


@stop

