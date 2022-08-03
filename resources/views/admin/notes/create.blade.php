@extends('layouts.admin.admin_lte')

@section('content')
    <div class="inner admin-dsh header-tp">

        <h3><i class="fa fa-angle-right" aria-hidden="true"></i>Notes </h3>
        <a href="#" class="help-link">
            <i class="fa fa-question-circle" aria-hidden="true"></i>
            <div class="tool-tip">Notes</div>
        </a>

    </div>
    {{ Breadcrumbs::render('merchantNotes',$merchant) }}
    <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary">

        @if(@Permissions::isAllow('Notes','Create'))
            <!-- form start -->

                {!! Form::open(['route'=>['admin::notes::storeCreate',$merchant_id], 'method'=>'POST','id'=>'create_note_form']) !!}





                @include('layouts.admin.partials.lte_alerts')

                <div class="box-body col-md-12">


                    <div class="form-group">
                        <label for="exampleInputEmail1">Note <span class="validate_star">*</span></label>

                        {!! Form::textarea('note','',['class'=>'form-control','required'=>'required','id'=>'note','rows' => 2, 'cols' => 20]) !!} {!! Form::hidden('merchant_id',isset($merchant_id)?$merchant_id:0) !!}
                        <span id="invalid-note"/>

                    </div>
                    <div class="btn-wrap btn-right">
                        <div class="btn-box">
                            <a class="btn btn-success" href="{{URL::to('admin/merchants/view',$merchant_id)}}">View
                                Merchant</a>
                            {!! Form::submit('Create',['class'=>'btn btn-primary']) !!}
                        </div>
                    </div>


                    {!! Form::close() !!}


                    @endif




                    @if($mNote)


<div class="card card-info">
<div class="card-header">

</div>
<div class="card-body">

                        @foreach($mNote as $data)


                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="card card-info">

                                        <div class="card-body">
                                            {!! $data['note'] !!}


                                        </div>
                                        <div class="card-header">
                                            by <b> {!! $data['added_by'] !!} </b><br>
                                            <small> {!! FFM::datetime($data['created_at']) !!}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        @endforeach

                    @endif


                </div>

                <!-- /.box-body -->


        </div>
        <!-- /.box -->
    </div>


@stop
@section('scripts')

<script type="text/javascript">
  $(document).on('submit', 'form', function() {
  $(this).find('button:submit, input:submit').attr('disabled', 'disabled');
});
</script>
<script>
$("#create_note_form").validate({
    errorClass: 'errors',
    rules: {
        note: {
            required: true,
            }
    },
    messages:{
        note: {
            required: "Please enter a Note",
        }
    },
    errorPlacement: function(error, element) {
            error.appendTo('#invalid-' + element.attr('id'));
        }
});
 </script>
@stop
@section('styles')
    <link href="{{ asset('/css/optimized/merchant_view_notes.css?ver=5') }}" rel="stylesheet" type="text/css"/>
@stop