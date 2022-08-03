@extends('layouts.admin.admin_lte')
@section('content')
<div class="inner admin-dsh header-tp">
    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
    <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip"> Investor Syndication Report</div>
    </a>
</div>
{{ Breadcrumbs::render('generatedPdfView') }}
<div class="col-md-12">
    <div class="box box-primary">
        <div class="box-header">
            {!! Form::open(['url'=>route('admin::investors::syndication-report', ['id' => $invester_id]), 'method'=>'GET']) !!}
            <div class="box-body">
                <div class="row">
                    <div class="form-group col-md-3">
                        {!! Form::text('date_start1',$date_start,['class'=>'form-control datepicker', 'autocomplete'=>'off', 'placeholder'=>\FFM::defaultDateFormat('format')]) !!}
                        <input type="hidden" name="date_start" class="date_parse" value="{{$date_start}}">
                        <label for="date_start">Start Date</label>
                    </div>
                    <div class="form-group col-md-3">
                        {!! Form::text('date_end1',$date_end,['class'=>'form-control datepicker',  'autocomplete'=>'off', 'placeholder'=>\FFM::defaultDateFormat('format')]) !!}
                        <input type="hidden" name="date_end" class="date_parse" value="{{$date_end}}">
                        <label for="date_end">End Date</label>
                    </div>
                    <div class="form-group col-md-1">
                        <button type="submit" class="btn btn-success">Fetch</button>
                    </div>
                </div>
            </div>
            {!! Form::close() !!}
        </div>
        <div class="box-body">
            <table class="table">
                <thead>
                    <?php foreach ($data as $single): ?>
                        <tr>
                            <?php foreach ($single as $key => $value): ?>
                                <th>{{ $value }}</th>
                            <?php endforeach ?>
                        </tr>
                    <?php endforeach ?>
                </thead>
            </table>
        </div>
    </div>
</div>
<div role="alert" aria-live="assertive" aria-atomic="true" class="toast" data-autohide="false">
    <div class="toast-header">
        <svg class=" rounded mr-2" width="20" height="20" xmlns="http://www.w3.org/2000/svg"
        preserveAspectRatio="xMidYMid slice" focusable="false" role="img">
        <rect fill="#007aff" width="100%" height="100%" /></svg>
        <strong class="mr-auto">Bootstrap</strong>
        <small>11 mins ago</small>
        <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div class="toast-body">
        Hello, world! This is a toast message.
    </div>
</div>
@stop
@section('scripts')
<script src="{{ url('/vendor/sweetalert2/sweetalert2.min.js') }}"></script>
<script type="text/javascript">
Swal.fire('info!', 'This is Debugging Purposes', 'info');
</script>
@stop
@section('styles')
<style type="text/css">
li.breadcrumb-item.active{
    color: #2b1871!important;
}
li.breadcrumb-item a{
    color: #6B778C;
} 
</style>
<link rel="stylesheet" href="{{ url('/vendor/sweetalert2/sweetalert2.min.css') }}">
@endsection
