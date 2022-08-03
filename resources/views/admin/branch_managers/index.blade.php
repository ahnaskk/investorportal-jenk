@extends('layouts.admin.admin_lte')

@section('content')
    <div class="inner admin-dsh header-tp">
        <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{ isset($title) ? $title : '' }} </h3>
        <a href="#" class="help-link">
            <i class="fa fa-question-circle" aria-hidden="true"></i>
            <div class="tool-tip">Branch Manager</div>
        </a>
    </div>
    <div class="col-md-12">
        <div class="box">
            <div class="box-head ">
                @include('layouts.admin.partials.lte_alerts')
            </div>
            <div class="box-body">
                <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                    <div class="row">
                        <div class="col-sm-10"></div>
                        @if (@Permissions::isAllow('Branch Manager', 'Create'))
                            <div class="col-sm-2" style="padding-bottom:15px">
                                <a href="{{ route('admin::branch_managers::create') }}" class="btn btn-primary"
                                    style="float:right; margin-bottom:8px">Create Branch Manager</a>
                            </div>
                        @endif
                    </div>
                    <div class="row">
                        <div class="col-sm-12 table-responsive">
                            {!! $tableBuilder->table(['class' => 'table table-bordered', 'id' => 'branch'], true) !!}
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
    <link href="{{ asset('/css/optimized/branch_manager.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop
