@if (count($errors) > 0)
    <div class="box-body">
        <div class="alert alert-danger alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h4><i class="icon fa fa-ban"></i> Error</h4>
            @foreach ($errors->all() as $error)
                <li>{!! $error !!}</li>
            @endforeach
        </div>
    </div>
@endif

@if (Session::has('message'))
    <div class="box-body">
        <div class="alert alert-info alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h4><i class="icon fa fa-ban"></i> Success</h4>
            {!! Session::get('message') !!}
        </div>
    </div>
@endif

@if (Session::has('error'))
    <div class="box-body">
        <div class="alert alert-danger alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h4><i class="icon fa fa-ban"></i> Fail</h4>
            {!!Session::get('error') !!}
        </div>
    </div>
@endif