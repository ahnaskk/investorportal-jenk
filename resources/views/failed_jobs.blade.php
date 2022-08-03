@extends('layouts.admin.admin_lte')
@section('content')
<div class="inner admin-dsh header-tp">
    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
    <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip"> Add Ach Request</div>
    </a>
</div>
<div class="col-md-12">
    <div class="card box-primary">
        <div class="table-responsive grid">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>id</th>
                        <th>payload</th>
                        <th>exception</th>
                        <th>connection</th>
                        <th>queue</th>
                        <th>failed_at</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($failed_jobs as $job): ?>
                    <tr>
                        <td>{{$job->id}}</td>
                        <td>
                            <div class="table-responsive grid">
                                <table class="table table-bordered">
                                    <?php $payload=json_decode($job->payload,true); ?>
                                    <?php foreach ($payload as $payload_key => $payload_value): ?>
                                    <tr>
                                        <th>{{$payload_key}}</th>
                                        @if(!is_array($payload_value))
                                        <th>{{$payload_value}}</th>
                                        @else
                                        <th>
                                            <div class="table-responsive grid">
                                                <table class="table table-bordered">
                                                    <?php foreach ($payload_value as $payload_value_key => $payload_value_value): ?>
                                                    <tr>
                                                        <th>{{$payload_value_key}}</th>
                                                        <?php $payload_value_valueArray=explode(';',$payload_value_value); ?>
                                                        @if(count($payload_value_valueArray)==1)
                                                        <th>{{$payload_value_value}}</th>
                                                        @else
                                                        <th>
                                                            <div class="table-responsive grid">
                                                                <table class="table table-bordered">
                                                                    <?php foreach ($payload_value_valueArray as $payload_value_valueArray_key => $payload_value_valueArray_value): ?>
                                                                    <tr>
                                                                        <th>{{$payload_value_valueArray_value}}</th>
                                                                    </tr>
                                                                    <?php endforeach; ?>
                                                                </table>
                                                            </div>
                                                        </th>
                                                        @endif
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </table>
                                            </div>
                                        </th>
                                        @endif
                                    </tr>
                                    <?php endforeach; ?>
                                </table>
                            </div>
                        </td>
                        <td>{{$job->connection}}</td>
                        <td>{{$job->exception}}</td>
                        <td>{{$job->queue}}</td>
                        <td>{{$job->failed_at}}</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop
@section('scripts')
<script src="{{ asset ("js/bootstrap-toggle.min.js") }}"></script>
<script src="{{ asset ('bower_components/bootstrap-tagsinput-latest/dist/bootstrap-tagsinput.min.js') }}"
    type="text/javascript"></script>
@stop