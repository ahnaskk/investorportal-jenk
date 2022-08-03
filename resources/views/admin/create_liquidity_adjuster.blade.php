@extends('layouts.admin.admin_lte')

@section('content')
<div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">{{isset($page_title)?$page_title:''}}</div>     
      </a>
      
  </div>
@if($action=="create")
  {{ Breadcrumbs::render('edit_liquidity_adjuster') }}
@endif
    <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary box-sm-wrap">

            
            <!-- form start -->
            @if($action=="create")
               {!! Form::open(['route'=>'admin::admins::save_liquidity_adjuster', 'method'=>'POST','id'=>'crete_admin_form']) !!}
           
            @endif

                <div class="box-body box-body-sm">
                    
                  @include('layouts.admin.partials.lte_alerts')
                    <div class="form-group">
                        <label for="exampleInputEmail1">Amount <font color="#FF0000"> * </font></label>
                        {!! Form::text('liquidity_adjuster',isset($users)? $users->liquidity_adjuster : old('liquidity_adjuster'),['class'=>'form-control liquidity_adjuster','id'=>'liquidity_adjuster','placeholder'=>'Enter Liquidity Adjuster','maxlength'=>"255"]) !!}
                    </div>  

                      {!! Form::hidden('user_id',$id) !!}

                    <div class="btn-wrap btn-right">
                        <div class="btn-box" >
                          
                            <div class="btn btn-success"> <a href="{{URL::to('admin/admin/liquidity_adjuster')}}" style="color: #fff">View List</a></div>
                            
                            @if($action=="create")
                             {!! Form::submit('Update',['class'=>'btn btn-primary']) !!}
                            @endif
                           
                       </div>
                    </div>
            {!! Form::close() !!}
        </div>
    </div>
        <!-- /.box -->


    </div>


@stop
 @section('scripts')
       
   <script src='{{ asset("js/jquery_validate_min.js")}}' type="text/javascript"></script>

   <script>

    $('#liquidity_adjuster').keypress(function(event) {
      if ((event.which != 46 || $(this).val().indexOf('.') != -1) &&
        ((event.which < 48 || event.which > 57) &&
          (event.which != 0 && event.which != 8))) {
        event.preventDefault();
      }
      var text = $(this).val();
      if ((text.indexOf('.') != -1) &&
        (text.substring(text.indexOf('.')).length > 2) &&
        (event.which != 0 && event.which != 8) &&
        ($(this)[0].selectionStart >= text.length - 2)) {
        event.preventDefault();
      }
    });
    
    $(document).ready(function () {
    $('#crete_admin_form').validate({ // initialize the plugin
        errorClass: 'errors',
        rules: {
            liquidity_adjuster: {
                required: true,
                number:true
            },
          
        },
        messages: {
        liquidity_adjuster: {
            required: "Enter Liquidity Adjuster",
            number: "Enter a valid number"
        }
    }
        
    });



    
});
</script>
@stop
@section('styles')
  <link href="{{ asset('/css/optimized/create_new_editor.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop
