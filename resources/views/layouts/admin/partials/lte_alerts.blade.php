@if (count($errors) > 0)
    <div class="box-body alert-box-body">
        <div class="alert alert-danger alert-dismissable fade show" role="alert">
            <button type="button" class="close" data-bs-dismiss="alert" aria-hidden="true">&times;</button>
            <!-- <h4><i class="icon fa fa-ban"></i> Error</h4> -->
            @foreach ($errors->all() as $error_arr)
                <p><?PHP print_r($error_arr);?></p>
            @endforeach
        </div>
    </div>
@endif

@if (Session::has('message'))
    <div class="box-body alert-box-body">
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <button type="button" class="close"  data-bs-dismiss="alert" aria-hidden="true" id="close">&times;</button>
            <h4><i class="fa fa-check" aria-hidden="true"></i> Success</h4>
            {!! Session::get('message') !!}
        </div>
    </div>
@endif

@if (Session::has('success'))
    <div class="box-body alert-box-body">
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <button type="button" class="close"  data-bs-dismiss="alert"  aria-hidden="true" id="close">&times;</button>
            {!! Session::get('success') !!}
        </div>
    </div>
@endif

@if (Session::has('msg'))
    <div class="box-body alert-box-body" role="alert">
        <div class="alert alert-success alert-dismissable fade show" role="alert">
            <button type="button" class="close" data-bs-dismiss="alert" aria-hidden="true">&times;</button>
            <h4><i class="fa fa-check" aria-hidden="true"></i> Success</h4>
            {!! Session::get('msg') !!}
        </div>
    </div>
@endif



@if (Session::has('error'))
    <div class="box-body alert-box-body">
        <div class="alert alert-danger alert-dismissable fade show" role="alert">
            <button type="button" class="close" data-bs-dismiss="alert" aria-hidden="true">&times;</button>
            <h4><i class="icon fa fa-ban"></i> Failed</h4>
            {!!Session::get('error') !!}
        </div>
    </div>
@endif

