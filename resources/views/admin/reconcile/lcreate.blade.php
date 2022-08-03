@extends('layouts.admin.admin_lte')

@section('content')

   <div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($title)?$title:''}} </h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Open Items</div>     
      </a>
      
  </div>
    <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary">

          
            <!-- form start -->
           <div class="box-body">
              @include('layouts.admin.partials.lte_alerts')
              <div class="row">
                <div class="col-md-12">
                    <h3>
                        {{$lender->name}}
                    </h3>
                </div>
                @if(count($exising_days))




                    <div class="col-md-12">
                         <span class="alert alert-danger">
                          <p>    
                        
                          <b>Already Reconciled:</b> <span > @foreach(array_unique($exising_days->toArray()) as $day){{FFM::date($day)}} @endforeach  
                          </span>
                       
                    </p>
                    </span>
                    </div>
                @endif
              </div>

                    
       
           <div class="row">

            {{Form::open(['to'=>''])}}
            <input type="hidden" name="days" value="{{$days}}">
            <input type="hidden" name="lender" value="{{$lender->id}}">

        <div class="col-md-4">
           <div class="form-group">
            <label for="exampleInputEmail1">New Days<span class="validate_star">*</span></label>
            <input name="new_days" class="form-control"  required=""  data-parsley-required-message="Amount Field is required"  type="text" aria-required="true" value="{{$days}}" placeholder="{{$days}}">
            <span id="invalid-inputName"></span></div>
        </div>   


        <div class="col-md-4">
            <div class="form-group">
                <label for="exampleInputEmail1">Total Amount <span class="validate_star">*</span></label>
                <input  onkeyup="javascript:document.getElementById('diffrence').innerText = this.value - (document.getElementById('inputcurrent').value)" name="total_amount" class="form-control"  required="" id="inputAmount" data-parsley-required-message="Amount Field is required"  type="text" aria-required="true" value="{{round($amount,2)}}">
                 <span id="invalid-inputName">
                  VP: {{round($vp_amount,2)}}  |  Velocity: {{round($amount-$vp_amount,2)}}
                </span>
            </div>
        </div>
          
            
         <div class="col-md-4">
             <div class="form-group">

                <label for="exampleInputEmail1">Actual Amount <span class="validate_star">*</span></label>
                    <input onkeyup="javascript:document.getElementById('diffrence').innerText = (document.getElementById('inputAmount').value)-this.value" class="form-control" placeholder="Enter the Amount" required="" name="actual_amount" id="inputcurrent"  data-parsley-required-message="Amount Field is required" name="Amount" type="text" aria-required="true">
                  <span id="invalid-inputName">
                </span>
            </div>
             <span class="text-warning lead">
                    There is a <b id="diffrence">-</b> difference between the actual amount and the system amount.
                </span>             
             </div>


            
            <div class="btn-wrap btn-right col-md-12">
                <div class="btn-box d-flex">
                      <a type="button" class="btn btn-light btn-lg btn-block" name="" href="{{URL::to('/admin/reconcile/create')}}" > Back </a>
                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                        Reconcile
                    </button>

                    
                </div>
            </div>
                 {{Form::close()}}
       </div>
    </div>





        </div>
        <!-- /.box -->


    </div>


@stop
 @section('scripts')
 <script type="text/javascript"></script>

</script>
@stop
@section('styles')
     <link href="{{ asset('/css/optimized/create_new_user_admin.css?ver=5') }}" rel="stylesheet" type="text/css" />

@stop