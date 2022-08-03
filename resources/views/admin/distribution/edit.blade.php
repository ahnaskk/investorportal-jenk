@extends('layouts.admin.admin_lte')

@section('content')
<div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>Distribution Edit</h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Distribution Edit</div>     
      </a>
      
  </div>
{{ Breadcrumbs::render('velocityDistributionEdit') }}

    <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary">

            
                    <!-- form start -->

                {!! Form::open(['route'=>['admin::vdistribution::update',$vId], 'method'=>'POST','onsubmit'=>"return validateForm()"]) !!}
                     {{ Form::hidden('edit', 'y') }}
                @include('layouts.admin.partials.lte_alerts')
            <div class="box-body col-md-12">
                <div class="row">
                    <div class="col-md-4 col-sm-12 form-group">
                        <label for="exampleInputEmail1">Investor <span class="validate_star">*</span></label>
                        <select id="investor_id" name="investor_id" class="form-control" required="required">
                            <option value="0">Select An Investor</option>
                            @foreach($investors as $investor)

                            <option data-liquidity='{{$investor->userDetails["liquidity"]}}' data-management-fee='{{$investor->management_fee}}' data-synd-fee='{{$investor->global_syndication}}' data-name='{{$investor->name}}' {{ ( (old('user_id'))?'selected': ($vDist->investor_id==$investor->id?'selected':'') ) }} value="{{$investor->id}}">{{$investor->name}} 

                                - {{$investor->userDetails['liquidity']}}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 col-sm-12 form-group">
                        <label for="exampleInputEmail1">Date <span class="validate_star">*</span></label>
                        {!! Form::text('date1',(old('date')?(old('date')):isset($vDist))?$vDist->date:'',['class'=>'form-control datepicker','placeholder' => \FFM::defaultDateFormat('format'),'required','autocomplete' => 'off']) !!} 
                        <input type="hidden" name="date" class="date_parse" value="{{(old('date')?(old('date')):isset($vDist))?$vDist->date:''}}">
                    </div>      
                    
                    <div class="col-md-4 col-sm-12 form-group">
                        <label for="exampleInputEmail1">Amount <span class="validate_star">*</span></label>
                        {!! Form::text('amount',(old('amount')?old('amount'):isset($vDist))? $vDist->amount : '',['class'=>'form-control','required','id'=>'amount']) !!}
                        {!! Form::hidden('id',isset($vId)?$vId:0) !!}
                    </div>
                </div>
                <div class="row">
                        <div class="form-group col-md-4 col-sm-12">
                            <label for="exampleInputEmail1">Adam Schwartz</label>
                            {!! Form::text('entity1',old('entity1')?old('entity1'):(isset($vDist)? $vDist->entity1 : ''),['class'=>'form-control','id'=>'entity1']) !!}
                        </div>

                        <div class="form-group col-md-4 col-sm-12">
                            <label for="exampleInputEmail1">Adam Frieman</label>
                            {!! Form::text('entity2',( (old('entity2'))?old('entity2'):(isset($vDist)? ($vDist->entity2) : '')),['class'=>'form-control','id'=>'entity2']) !!}
                        </div>

                        <div class="form-group col-md-4 col-sm-12">
                            <label for="exampleInputEmail1">Chris Clark</label>
                            {!! Form::text('entity3',old('entity3')?old('entity3'):(isset($vDist)? $vDist->entity3 : ''),['class'=>'form-control','id'=>'entity3']) !!}
                    </div>
                </div>
                <div class="row">
                     <div class="form-group col-md-4 col-sm-12">
                        <label for="exampleInputEmail1">Trace Feinstein</label>
                        {!! Form::text('entity4',old('entity4')?old('entity4'): (isset($vDist)? $vDist->entity4 : ''),['class'=>'form-control','id'=>'entity4']) !!}
                    </div>

                    <div class="form-group col-md-4 col-sm-12">
                        <label for="exampleInputEmail1">Lisa Fallah</label>
                        {!! Form::text('entity5',old('entity5')?old('entity5'):(isset($vDist)? $vDist->entity5 : ''),['class'=>'form-control','id'=>'entity5']) !!}
                    </div>


                    <div class="form-group col-md-4 col-sm-12">
                        <label for="exampleInputEmail1">Melissa Zelin</label>
                        {!! Form::text('entity6',old('entity6')?old('entity6'):(isset($vDist)? $vDist->entity6 : ''),['class'=>'form-control','id'=>'entity6']) !!}
                    </div>
                </div>
            </div>

                <!-- /.box-body -->
                <div class="row">
                    <div class="col-md-12 btn-wrap btn-right">
                        <!-- /.box-body -->
                        <div class="btn-box">
                            <a class="btn btn-success" href="{{URL::to('admin/vdistribution')}}">Back To Lists</a>
                            {!! Form::submit('Update',['class'=>'btn btn-primary']) !!}                            
                        </div>
                    </div>
                </div>


            </div>

            </div>
            
           

        


            {!! Form::close() !!}
        </div>
        <!-- /.box -->


    </div>


@stop

@section('scripts')
<script type="text/javascript">

function validateForm(){
   /* alert(parseFloat($('#entity1').val()) + parseFloat($('#entity2').val()) + parseFloat($('#entity3').val()) + parseFloat($('#entity4').val()) + parseFloat($('#entity5').val()) + parseFloat($('#entity6').val()));*/
//alert();

/*alert(parseFloat($('#entity2').val()));
alert((parseFloat($('#entity1').val()) + parseFloat($('#entity2').val()) + parseFloat($('#entity3').val()) + parseFloat($('#entity4').val()) + parseFloat($('#entity5').val()) + parseFloat($('#entity6').val())));
*/
if(Math.abs(parseFloat($('#amount').val()) || 0) < (parseFloat($('#entity1').val()) || 0 + parseFloat($('#entity2').val()) || 0 + parseFloat($('#entity3').val()) || 0 + parseFloat($('#entity4').val()) || 0 + parseFloat($('#entity5').val()) || 0 + parseFloat($('#entity6').val()) || 0))
{

    alert('Distribution Exceeds The Actual Amount!')
    return false;
}
else{
    return true;
}
}

$('#amount').keypress(function(event) {    
    if(event.which == 46 && $(this).val().indexOf('.') != -1) {
        event.preventDefault();
    } // prevent if already dot
    if(event.which == 44
    && $(this).val().indexOf(',') != -1) {
        event.preventDefault();
    } // prevent if already comma
});

/*    $(document).ready(function(){
    $('#amount').blur(function(){
        value_t = $('#amount').val();
        single_val_t = value_t/6/
$('#entity1').val(single_val_t)
      
        
    });
});
*/
</script>
@stop



@section('styles')
  <style type="text/css">
    li.breadcrumb-item.active{
      color: #2b1871!important;
    }
    li.breadcrumb-item a{
       color: #6B778C;
    }
  </style>
 <link href="{{ asset('/css/optimized/velocity_edit.css?ver=5') }}" rel="stylesheet" type="text/css" />
 

@stop