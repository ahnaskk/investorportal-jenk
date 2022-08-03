@extends('layouts.admin.admin_lte')
@section('content')
<div class="inner admin-dsh header-tp">
    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
    <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">All Accounts</div>
    </a>
</div>
{{ Breadcrumbs::render('admin::investors::index') }}
<div class="col-md-12">
    <div class="box">
        <div class="box-head ">
            @include('layouts.admin.partials.lte_alerts')
        </div>
        <div class="box-body">
            {{Form::open(['route'=>'admin::reports::investor-list-download'])}}
            <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                <div class="form-box-styled">
                    <div class="row">
                        <div class="col-md-3">
                            {{Form::select('investor_type',$investor_types,'',['class'=>'form-control','placeholder'=>'Filter Investor ','id'=>'investor_type'])}}
                            <span class="help-block">Investor Type</span>
                        </div>
                        @if(!Auth::user()->hasRole('company'))
                        <div class="col-md-3">
                            {{Form::select('velocity',[''=>'All']+$companies,'',['class'=>'form-control','id'=>'velocity'])}}
                            <span class="help-block">Companies</span>
                        </div>
                        @endif
                        <div class="col-md-3">
                            {{Form::select('role_id',[''=>'All']+$Roles,'',['class'=>'form-control','id'=>'role_id'])}}
                            <span class="help-block">Account Type</span>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <div class="input-group check-box-wrap">
                                    <div class="input-group-text">
                                        {{ Form::radio('active_status','', false,['class' => 'active_status','id' => 'active_status_all']) }}
                                        <label for="active_status_all">All</label>
                                        {{ Form::radio('active_status','1', true,['class' => 'active_status','id' => 'active_status_enable']) }}
                                        <label for="active_status_enable">Enable</label>
                                        {{ Form::radio('active_status','2', false,['class' => 'active_status', 'id' => 'active_status_disable']) }}
                                        <label for="active_status_disable">Disable</label>
                                    </div>
                                </div>
                                <span class="help-block">Enable/Disable Investors</span>
                            </div>
                        </div>
                        <!-- <div class="col-md-3"> -->
                        <!-- <br> -->
                        <!-- <div class="input-group"> -->
                        <!-- {{Form::select('liquidity',[''=>'All','1' => 'Excluded', '0' => 'Included'],"",['class'=>'form-control','id'=>'liquidity'])}} -->
                        <!-- </div> -->
                        <!-- <span class="help-block">Liquidity</span> -->
                        <!-- </div> -->
                        <div class="col-md-3">
                            <div class="form-group">
                                {{ Form::select('auto_invest_label',$label,'',['class'=>'form-control','id'=>'auto_invest_label','placeholder'=>'Select Auto Invest Label']) }}
                                <span class="help-block">Auto Invest Label</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            
                            <div class="form-group">
                                <div class="input-group check-box-wrap">
                                    <div class="input-group-text">
                                        {{ Form::radio('auto_generation','', true,['class' => 'auto_generation','id' => 'label_all']) }}
                                        <label for="label_all">All</label>
                                        {{ Form::radio('auto_generation','1', false,['class' => 'auto_generation','id' => 'label_enable']) }}
                                        <label for="label_enable">Enable</label>
                                        {{ Form::radio('auto_generation','2', false,['class' => 'auto_generation' , 'id' => 'label_disable']) }}
                                        <label for="label_disable">Disable</label>
                                    </div>
                                </div>
                                <span class="help-block">Enable/Disable Automatic Report Generation </span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            
                            {{Form::select('notification_recurence',$recurrence_types,'',['class'=>'form-control','id'=>'notification_recurence'])}}
                            <span class="help-block">Payout Frequency</span>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <div class="input-group check-box-wrap">
                                    <div class="input-group-text">
                                        {{ Form::radio('active_status_companies','1', true,['class' => 'active_status','id' => 'active_status_companies_enable']) }}
                                        <label for="active_status_companies_enable">Enable</label>
                                        {{ Form::radio('active_status_companies','2', false,['class' => 'active_status', 'id' => 'active_status_companies_disable']) }}
                                        <label for="active_status_companies_disable">Disable</label>
                                    </div>
                                </div>
                                <span class="help-block">Enable/Disable Companies</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                        <div class="input-group check-box-wrap">
                            <label>Velocity Owned</label>
                            <div class="input-group-text">
                                <label class="chc">
                                    <input type="checkbox" name="velocity_owned" value="1" id="velocity_owned"/>
                                    <span class="checkmark chek-m"></span>
                                    <span class="chc-value">Click Here</span>
                                </label>
                            </div>
                        </div>
                    </div>
                        <div class="col-sm-12 pull-right btn-wrap btn-right">
                            <div class="btn-box">
                                <input type="button" value="Apply Filter" class="btn btn-success" id="date_filter" name="student_dob">
                                @if(@Permissions::isAllow('Investors','Download'))
                                {{Form::submit('download',['class'=>'btn btn-warning','id'=>'form_filter'])}}
                                @endif
                                @if(!Auth::user()->hasRole('viewer'))
                                @if(@Permissions::isAllow('Investors','Create'))
                                <a href="{{route('admin::investors::create')}}" class="btn btn-primary pull-right create-btn">Create Account</a>
                                @endif
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="row pull-right">
                        <p>
                            @if(@Permissions::isAllow('Investors','View'))
                            <button class="btn btn-xs btn-sm btn-info" type="button"><i class="glyphicon glyphicon-view"></i> T =  Transactions</button>
                            <button class="btn btn-xs btn-sm btn-primary" type="button"><i class="glyphicon glyphicon-view"></i> D =  Document</button>
                            @endif
                            @if(@Permissions::isAllow('Investors','Edit'))
                            <button class="btn btn-xs btn-sm btn-secondary" type="button"><i class="glyphicon glyphicon-view"></i> B =  Bank</button>
                            @endif
                            @if(@Permissions::isAllow('Advance Plus Investments Report','view'))
                            <button class="btn btn-xs btn-sm btn-secondary" type="button"><i class="glyphicon glyphicon-view"></i> APIR =  Advance Plus Investments Report</button>
                            @endif
                        </p>
                    </div>
                </div>
                {{Form::close()}}
                <div class="row">
                    <div class="col-sm-12 table-responsive align">
                        {!! $tableBuilder->table(['class' => 'table table-bordered','id'=>'investor'],true) !!}
                    </div>
                </div>
            </div>
        </div>
        <!-- /.box-body -->
    </div>
</div>
@stop
<!-- <script src="{{ asset ('js/bootstrap-toggle.min.js') }}"></script> -->
@section('scripts')
<!-- <script src="{{ asset ('bower_components/datatables.net/js/jquery.dataTables.min.js') }}" type="text/javascript"></script> -->
<!-- <script src="{{ asset ('bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}" type="text/javascript"></script> -->
{!! $tableBuilder->scripts() !!}
<script type="text/javascript">
var table = window.LaravelDataTables["investor"];
if(performance.navigation.type == 2)
{
    $(document).ready(function(e){
        console.log('ready')
        $("#date_filter").click();
    })
}
$(document).ready(function() {
    $('#date_filter').on('click',function(e) {
        e.preventDefault();
        table.ajax.reload();
        table.draw();
    });
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
<link href="{{ asset('css/bootstrap-toggle.min.css') }}" rel="stylesheet" />
<link href="{{ asset('/css/optimized/investors.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop
