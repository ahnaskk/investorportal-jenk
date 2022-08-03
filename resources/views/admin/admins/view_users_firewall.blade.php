@extends('layouts.admin.admin_lte')

@section('content')
    <div class="inner admin-dsh header-tp">
        <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{ isset($page_title) ? $page_title : '' }} </h3>
        <a href="#" class="help-link">
            <i class="fa fa-question-circle" aria-hidden="true"></i>
            <div class="tool-tip">View User Firewall</div>
        </a>
    </div>
    {{ Breadcrumbs::render('admin::firewall::index') }}
    <div class="form-box-styled">
        <div class="row">
            <div class="col-md-6">
                <div class="input-group" data-select2-id="select2-data-139-ccg7">
                    <div class="input-group-text">
                        <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                    </div>
                    {{Form::select('user_roles',['users'=>'Users','roles'=>'Roles'],'users',['class'=>'form-control','id'=>'user_roles'])}}
                </div>
                <span class="help-block">Filter</span>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="box">
            <div class="box-head ">
                @include('layouts.admin.partials.lte_alerts')
            </div>
            <div class="box-body usertable">
                <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                    <div class="row">
                        <div class="col-sm-12">
                            {!! $table1->table(['class' => 'table table-bordered','id'=>'table1'], true);
                                $table1->parameters(['stateSave' => true]) !!}
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-body rolestable" style="display: none;">
                <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                    <div class="row">
                        <div class="col-sm-12">
                            {!! $table2->table(['class' => 'table table-bordered','id'=>'table2'], true);
                                $table2->parameters(['stateSave' => true]) !!}
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.box-body -->
        </div>
    </div>
@stop

@section('scripts')
    {!! $table1->scripts() !!}
    {!! $table2->scripts() !!} 
    <script>
        $(document).ready(function(){
           if($('.rolestable').css('display') == 'none'){
            $('#user_roles').val('users');
           }
            $('#user_roles').change(function (e) {
                var _this = $(this).val();
                if(_this == 'users'){
                    $('.usertable').show();
                    $('.rolestable').hide();
                }
                if(_this == 'roles'){
                    $('.usertable').hide();
                    $('.rolestable').show();
                }
            });
        });
    </script>
@stop

@section('styles')
<style>
.input-group .select2 {flex: 1 1 auto;width: min-content !important;}
.input-group-text{padding:6px 12px;font-size:14px;font-weight:400;line-height:1;color:#555;text-align:center;background-color:#eee;border:1px solid #ccc;border-radius:4px;}
</style>
<link href="{{ asset('/css/optimized/editor.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/bower_components/breadcrumbs/breadcrumb1.css?ver=5') }}" rel="stylesheet"
    type="text/css" />
@stop
