@extends('layouts.admin.admin_lte')

@section('content')

    <div class="inner admin-dsh header-tp">

        <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{ isset($title) ? $title : '' }} </h3>
        <a href="#" class="help-link">
            <i class="fa fa-question-circle" aria-hidden="true"></i>
            <div class="tool-tip">{{ isset($title) ? $title : '' }}</div>
        </a>

    </div>
    {{ Breadcrumbs::render('admin::admins::index') }}
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
                            <a href="{{ route('admin::admins::create') }}" class="btn btn-primary admin-btn">Create Admin
                                Users</a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 table-responsive">
                            {!! $tableBuilder->table(['class' => 'table table-bordered '], true) !!}
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
    <link href="{{ asset('/css/optimized/admin_user.css?ver=5') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/css/bower_components/breadcrumbs/breadcrumb1.css?ver=5') }}" rel="stylesheet"
        type="text/css" />
@stop
