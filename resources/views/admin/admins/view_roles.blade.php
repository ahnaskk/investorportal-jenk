@extends('layouts.admin.admin_lte')

@section('content')
    <div class="inner admin-dsh header-tp">
        <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{ isset($page_title) ? $page_title : '' }} </h3>
        <a href="#" class="help-link">
            <i class="fa fa-question-circle" aria-hidden="true"></i>
            <div class="tool-tip">Roles and Permissions</div>
        </a>
    </div>
    {{ Breadcrumbs::render('admin::roles::show-role') }}
    <div class="col-md-12">
        <div class="box">
            <div class="box-head ">
                @include('layouts.admin.partials.lte_alerts')
            </div>
            <div class="box-body">
                <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                    <div class="row">
                        <div class="col-sm-10"></div>
                        @if (@Permissions::isAllow('Roles', 'Create'))
                            <div class="col-sm-2" style="padding-bottom:15px">
                                <a href="{{ route('admin::roles::create-role') }}" class="btn btn-primary admin-btn">Create Role </a>
                            </div>
                        @endif
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            {!! $tableBuilder->table(['class' => 'table table-bordered', 'id' => 'editor'], true) !!}
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
    <script src="{{ asset('js/bootstrap-toggle.min.js') }}"></script>
    <script>
        function updateTwoFactorStatus(role_id){
            var checkBox = document.getElementById("two_factor_required_status"+role_id);
            var two_factor_mandatory = 0;
            if(checkBox.checked==true){
                var two_factor_mandatory = 1;
            }
            $.ajax({
            type: 'POST',
                data: {'role_id':role_id,'_token': _token,'two_factor_mandatory':two_factor_mandatory},
                url: '/admin/updateTwoFactorMandatoryStatus',
                beforeSend: function () {
                    if(confirm("Are you sure?")){
                        // do something
                    } else { 
                        if(checkBox.checked==true){
                        document.getElementById("two_factor_required_status"+role_id).checked=false;
                        }else{
                        document.getElementById("two_factor_required_status"+role_id).checked=true;   
                        }
                        return false;
                    }
                },

                success: function (data) {
                    $('.box-head').html('<div class="alert alert-success" ><strong> </strong> Updated Successfully<button type="button" class="close" data-bs-dismiss="alert" aria-hidden="true">&times;</button></div>'); 
                    
                },
                error: function (data) {
                }
            });
        }
        $(document).ready(function() {
            window.onpopstate = function() {
                var table = $('#editor').DataTable();
                var info = table.page.info();
                var pageNo = info.page;
                if (pageNo == 0) {
                    pageNo = 0;
                } else {
                    pageNo = pageNo - 1;
                }
                table.page(pageNo).draw(false);
            };
            history.pushState({}, '');
        });
    </script>
@stop
@section('styles')
<link href="{{ asset('css/bootstrap-toggle.min.css') }}" rel='stylesheet'/>
    <link href="{{ asset('/css/optimized/editor.css?ver=5') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/css/bower_components/breadcrumbs/breadcrumb1.css?ver=5') }}" rel="stylesheet"
        type="text/css" />
@stop
