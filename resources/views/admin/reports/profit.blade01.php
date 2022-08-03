@extends('layouts.admin.admin_lte')
@section('content')
<div class="col-md-12 col-sm-12">
  <div class="grid box box-padTB">
    <div class="form-group">
      <div class="filter-group-wrap" >
        @if(!Auth::user()->brokerage)
        
        <div class="filter-group" >
         {!! Form::open(['route'=>'admin::reports::profit','method'=>'POST']) !!}
         <div class="row">

<!--         <div class="col-md-3 col-sm-12 prf-re"> 
            <div class="prf-hd"><label>Sub-Admin</label></div>
            
            {{ Form::select('sub_admin[]',$allsubadmin,$selected_admin,['class'=>'form-control','id'=>'sub_admin','multiple'=>'multiple']) }}
            
         </div> -->
         
        <div class="col-md-3 col-sm-12 prf-re"> 
            <div class="prf-hd"><label>Sub-Admin</label></div>
            
           <?php
      $option = '<select multiple="multiple" name="sub_admin[]" id="sub_admin1" class="form-control" style="width:100%;padding:5px!important;line-height:22px!important;padding-left:0!important">';
                                        if (isset($selected_admin)) {
                                            foreach ($selected_admin as $a) {
                                                $id = $a['id'];
                                                $name = $a['name'];
                                                $option .= "<option value='$id' selected='selected'>$name</option>";
                                            }
                                        }
                                        $option .= '</select>';
                       echo $option;
                 ?>
            
          </div>      
             
       <?php
              $rate = [];

              for ($i = 0; $i <= 20; $i++) {
                  $rate[$i] = $i.'%';
              }
        ?>


   <div class="col-md-3 col-sm-12 prf-re"> 
    <div class="prf-hd"><label>Default Rate</label>
        {!! Form::select('rate',$rate,'',['class'=>'form-control','id'=>'rate']) !!}  

          </div> 
          </div>

            <div class="col-md-3 col-sm-12 prf-re">
              <div class="prf-hd"><label>Debits Included</label></div> 

              <label class="chc"><input type="checkbox" name="debits_togg" value="1" {{$billsAndDist?'checked':''}}><span class="checkmark checkk000"></span>
               </label>

            </div>


            <div class="col-md-3 col-sm-12"  style="">
             <div class="filt-bt">
               {{ Form::submit('Apply Filter',['class'=>'btn btn-primary','id'=>'status_filter']) }}


             </div>
           </div>                           
         </div>
       </div>

     </div>

     {!! Form::close() !!}
     @endif   
   </div>



  <div class="col-md-12 col-sm-12 proft-re">
   <table>
    <tr>
      <th class="prf">Prefered return: {{FFM::dollar($investor_profit)}}</th>
      <th class="prf">Velocity Profit: {{FFM::dollar($velocity_profit-$subadmin_profit)}}</th>
      
      @if($subadminsProfitArray)
      
      @foreach($subadminsProfitArray as $key=>$value)
      
      <th class="prf">{{isset($key)?$key:''}} Profit: {{FFM::dollar($value)}}</th>
      
      @endforeach
      
      @endif
      
      
      
    </tr>
  </table>
</div>

</div>
<?php
$rtr = 0;
?>
@foreach($investorArray as $investor_pro)
<?php
$rtr = $rtr + $investor_pro['rtr'];
?>
<div class="invet-prff">
 <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
 <div class="invet-prff">
 <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
  <div class="prfot-box {{$investor_pro['type']==1?'colo-in':''}}">
    <ul>
     <li><h3>Prefered return:</h3><span>{{FFM::dollar($investor_pro['investor_profit'])}}</span></li>
     <li><h3>Portfolio Profit:</h3><span>{{FFM::dollar($investor_pro['portfolio_profit'])}}</span></li>
     <li><h3>Velocity Profit:</h3><span>{{FFM::dollar($investor_pro['velocity_profit']-$investor_pro['subadmin_profit'])}}</span></li>
     
     <li>
      <h3>
       @if($investor_pro['subadmin_name'])
     {{isset($investor_pro['subadmin_name'])?$investor_pro['subadmin_name']:'sub-admin'}} Profit:</h3>
     <span>{{FFM::dollar($investor_pro['subadmin_profit'])}}</span></li>
     @endif  
   </ul>   
 </div>
 <div class="inst-nam">
 <h4>Investor: <i class="fa fa-user" aria-hidden="true"></i> {{$investor_pro['name']}} 
     @if($investor_pro['subadmin_name'])
     ( {{ $investor_pro['subadmin_name'] }} )
     @endif
   </h4>
   </div>
</div>
</div>
</div>
</div>
@endforeach

</div>

@stop

@section('scripts')
<script src="{{ asset ('bower_components/datatables.net/js/jquery.dataTables.min.js') }}" type="text/javascript"></script>

<script src="{{ asset ('bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}" type="text/javascript"></script>

<script src="{{asset('bower_components/chart.js/Chart.js')}}"></script>

<script type="text/javascript">
  var table = window.LaravelDataTables["dataTableBuilder"];
  
  $(document).ready(function(){
    $('#status_filter').click(function (e) {
      e.preventDefault();
      table.draw();
    });

  </script>



});
</script>

<script>
       var URL_getInvestorAdmin = "{{ URL::to('/admin/getInvestorAdmin') }}";
</script>

<script src='{{ asset("js/custom.js")}}' type="text/javascript"></script>


@stop

@section('styles')
<link rel="stylesheet" href="{{ asset ("bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css") }}">
<style type="text/css">
.ds-btn li{ list-style:none; float:left; padding:10px; }
.ds-btn li a span{padding-left:15px;padding-right:5px;width:100%;display:inline-block; text-align:left;}
.ds-btn li a span small{width:100%; display:inline-block; text-align:left;}
</style>
@stop


