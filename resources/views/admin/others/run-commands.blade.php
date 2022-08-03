@extends('layouts.admin.admin_lte')

@section('content')
    <div class="inner admin-dsh header-tp">

        <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
        <a href="#" class="help-link">
            <i class="fa fa-question-circle" aria-hidden="true"></i>
            <div class="tool-tip">{{isset($page_title)?$page_title:''}}</div>
        </a>

    </div>
    <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary box-sm-wrap">
                {!! Form::open(['route'=>'admin::run-commands', 'method'=>'POST' , 'id' => 'faqForm']) !!}






            <div class="box-body box-body-sm">
                @include('layouts.admin.partials.lte_alerts')
                <div class="form-group">
                    <label for="command">Command <span color="#FF0000"> * </span></label>
                    {!! Form::text('command',isset($faq)? $faq->command : old('command'),['id'=>'command','class'=>'form-control','placeholder'=>'Enter command']) !!}
                </div>
                <?php $userId = Auth::user()->id;?>
                {!! Form::hidden('creator_id',$userId) !!}



            <!-- /.box-body -->

                <div class="btn-wrap btn-right">
                    <div class="btn-box">

                        {!! Form::submit('RUN',['class'=>'btn btn-primary']) !!}
                        <a href="/dashboard" class="btn btn-danger">Cancel</a>


                    </div>
                </div>
                {!! Form::close() !!}
            </div>

            @section('scripts')

            @stop
            @section('styles')
                <link href="{{ asset('/css/optimized/create_new_editor.css') }}" rel="stylesheet" type="text/css"/>
            @stop






        </div>
        <!-- /.box -->
    </div>
@stop
