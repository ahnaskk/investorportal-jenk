@extends('layouts.admin.admin_lte')

@section('content')
<div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}}  </h3>
    <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">{{ $page_title }}</div>     
    </a>
    
</div>

<div class="col-md-12">
    <div class="box">
        <div class="box-head ">
            @include('layouts.admin.partials.lte_alerts')

        </div>
        <div class="box-body">
            <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap two-factor-wrapper">
                <p>{{ $description }}</p>
                @if($ach_ids)
                <form action="{{ route('admin::payments::investor-ach-status.delete') }}" method="post">
                    @csrf
                    <input hidden name="request_date" value="{{ $request_date }}">
                    @foreach ($ach_ids as $ach_id)
                    <input hidden name="ach_ids[]" value="{{ $ach_id }}">
                    @endforeach
                    <button class="btn btn-danger" onclick='return confirm("Are you sure that you want to delete this ACH requests?")'>Delete</button>  
                </form>
                @endif
            </div>
        </div>
        <!-- /.box-body -->
    </div>
</div>



@stop



@section('styles')
    <link href="{{ asset('/css/optimized/admin_user.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop