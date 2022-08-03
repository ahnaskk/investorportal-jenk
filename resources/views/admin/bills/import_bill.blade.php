@extends('layouts.admin.admin_lte')
@section('content')

    <div class="inner admin-dsh header-tp">
        <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{ isset($page_title) ? $page_title : '' }} </h3>
        <a href="#" class="help-link">
            <i class="fa fa-question-circle" aria-hidden="true"></i>
            <div class="tool-tip">Import Bills</div>
        </a>
    </div>
    {{ Breadcrumbs::render('admin::bills::import_bill') }}
    <div class="col-md-12">
        <div class="box">
            <div class="box">
                <div class="box-body">
                    @include('layouts.admin.partials.lte_alerts')
                    <div id="example2_wrapper" class="form-inline dt-bootstrap">
                        <div class="col-md-12">
                            <div class="grid">
                                <div class="paymntGnrtBox grid">
                                    <div class="card card-primary">
                                        <div class="card-header">CSV Bill Upload</div>
                                        @if (@Permissions::isAllow('Transactions', 'Download'))
                                            <div class="card-body">
                                                <a class="download-btn" target="blank"
                                                    href="/templates/template_bill.csv">Download Templates</a>
                                                <div class="pull-left pul-pay col-md-12 col-sm-12">
                                                    {{ Form::open(['route' => 'admin::bills::csvupload', 'method' => 'POST', 'enctype' => 'multipart/form-data', 'id' => 'csv_form', 'files' => true]) }}
                                                    @csrf
                                                    <div class="form-group form-grp">
                                                        <label>
                                                            Csv File
                                                        </label>
                                                        {{ Form::file('csv') }}
                                                    </div>
                                                    {{ Form::submit('Upload', ['class' => 'btn btn-primary']) }}
                                                    {{ Form::close() }}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.box-body -->
            </div>
        </div>
    </div>
@stop
@section('scripts')
    <script src="{{ asset('bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}" type="text/javascript">
    </script>
@stop

@section('styles')
    <style type="text/css">
        li.breadcrumb-item.active {
            color: #2b1871 !important;
        }

        li.breadcrumb-item a {
            color: #6B778C;
        }

        .card-primary {
            border-color: #337ab7;
        }

        .card-primary>.card-header {
            color: #fff;
            background-color: #337ab7;
            border-color: #337ab7;
        }

    </style>
@stop
