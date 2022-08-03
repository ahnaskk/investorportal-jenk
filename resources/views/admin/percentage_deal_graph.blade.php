@extends('layouts.admin.admin_lte')

@section('content')

<div class="inner admin-dsh header-tp">

  <h3><i class="fa fa-angle-right" aria-hidden="true"></i>Graph </h3>
  <a href="#" class="help-link">
    <i class="fa fa-question-circle" aria-hidden="true"></i>
    <div class="tool-tip">Graph</div>     
  </a>
</div>
{{ Breadcrumbs::render('admin::percentageDeal') }}
<div class="box box-primary">
  <div class="box-body">
    @if (Session::has('message'))
    <div class="alert {{Session::has('message_important')?'alert-danger':'alert-success'}} alert-dismissible" role="alert">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    {{Session::get('message')}}{{Session::forget('message')}}
    </div> 
   @endif
        {{Form::open(['route'=>'admin::download-graph','id'=>'payment-form'])}}
        <div class="form-filter full-width" >
        <div class="form-filter-wrap">
          <div class="row">
          <div class="col-md-6 form-group">
              <span class="help-block">From Date</span>
            <div class="input-group">
              <div class="input-group-text">
                <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
              </div>
              <input class="form-control from_date1 datepicker" id="date_start1" value="" name="date_start1" placeholder="{{\FFM::defaultDateFormat('format')}}" type="text" autocomplete="off"/>
              <input type="hidden" name="date_start" id="date_start" class="date_parse">
            </div>
          </div>
          <div class="col-md-6 form-group">
              <span class="help-block">To Date</span>
            <div class="input-group">
              <div class="input-group-text">
                <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
              </div>
              <input class="form-control to_date1 datepicker" id="date_end1" value="" name="date_end1" placeholder="{{\FFM::defaultDateFormat('format')}}" type="text" autocomplete="off"/>
              <input type="hidden" name="date_end" id="date_end" class="date_parse">
            </div>
          </div>

        </div> 
        <div class="row">
          <div class="col-sm-3 form-group">
              <span class="help-block">Group by <span class="error">*</span></span>
            {{Form::select('attribute',$attribute,'',['class'=>'form-control','id'=>'attribute'])}}


          </div>

          <div class="col-sm-3 form-group">
              <span class="help-block">Value <span class="error">*</span></span>
            {{Form::select('graph_value',$graph_value,'',['class'=>'form-control','id'=>'graph_value'])}}


          </div>



         <div class="col-sm-3 form-group">
             <span class="help-block">Label <span class="error">*</span></span>
           {!! Form::select('label',$labels,'',['class'=>'form-control','id'=>'label']) !!}


         </div>


          <div class="col-sm-3 form-group">
              <span class="help-block">Filter Lenders</span>
           {{Form::select('lenders',$len,'',['class'=>'form-control','id'=>'lender','placeholder'=>'Select  Lender (optional)'])}}

         </div> 

          @if(!Auth::user()->hasRole(['company']))
                   <div class="col-sm-3 ">
                       <span class="help-block">Company</span>
            <div class="input-group">
              <div class="input-group-text">
                <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
              </div>
              {{Form::select('owner',$companies,'',['class'=>'form-control js-company-placeholder','id'=>'owner','placeholder'=>'Select Company'])}}



            </div>

          </div>  




         <div class="col-sm-9 ">
             <span class="help-block">Filter Investor</span>
          {{Form::select('invested_amount[]',$investors,'',['class'=>'form-control js-investor-placeholder-multiple','id'=>'invested_amount','multiple'=>'multiple'])}}
        </div>  
        @endif      




      </div> 
      <div class="row">
        <div class="col-sm-12">
          <div class="btn-box">
            <a href="javascript:void(0)" class="btn btn-danger" id="clearFilterBtn">Clear Filter</a>
            <a class="btn btn-primary" id="applyFilterBtn">Update Graph</a>
            @if(@Permissions::isAllow('Merchant Graph','Download'))  
              {{Form::submit('download',['class'=>'btn btn-success','id'=>'form_filter'])}}
              @endif

          </div>
          </div>
        </div>
      </div> 




    </div>
    

    
    {{Form::close()}}
    <div class="btn-wrap btn-left">
      <div class="btn-box">
        <label class="grid" for=""><p> Total : <span id ="tot"></span></p>  
          <label class="grid" for=""><p> Average : <span id ="avg"></span></p>    
          </div>
        </div>              


        <div id="myChart_div" style="width:100%">
          <!-- <canvas id="myChart" ></canvas> -->
        </div>
      
    </div>
    @stop

    @section('scripts')
    <script type="text/javascript">
     
     $(document).ready(function () {
      $('.close').click(function(){
        $('.alert').hide();
      });
       $('#clearFilterBtn').on('click',function(e)
         {
             //$('#invested_amount').val('');
             $('#invested_amount').select2('val', ['']);
            // $('#owner').val('');
             $('#owner').select2('val', ['']);
             $('#lender').select2('val', ['']);
             $('#label').select2('val', ['']);
             $('#graph_value').select2('val', ['']);
             $('#attribute').select2('val', ['']);
             $('#date_end,#date_end1').val('');
             $('#date_start,#date_start1').val('');

            
         });

        
      update_graph();

  // company filter  show investors
  var URL_getInvestor = "{{ URL::to('admin/getInvestorsforOwner') }}";

  $('#owner').change(function(e)
  {
    var company=$('#owner').val();         
    var investors = []; 
    if(company)
    {
     $.ajax({
      type: 'GET',
      data: {'changeid': 1, 'company':company, '_token': _token},
      url: URL_getInvestor,
      success: function (data) {        
       $('#invested_amount').attr('selected','selected').val(data).trigger('change.select2');                 
     },
     error: function (data) {

     }
   });
   }
 });

  $(".js-investor-placeholder-multiple").select2({
    placeholder: "Select Investor(optional)"
  });
        let startDt = $('#date_start').val() && new Date($('#date_start').val());
        if(startDt){
            $('#date_end1').datepicker('setStartDate', startDt);
        }
        $('#date_start1').on('changeDate', function(selected){
            let endDateSelected = $('#date_end').val() && new Date($('#date_end').val());
            if($('#date_start').val() && new Date($('#date_start').val())){
            let minDate = new Date(selected.date.valueOf());          
            if(endDateSelected && endDateSelected < minDate){
                $("#date_end1").datepicker('update', "");
            }
            $('#date_end1').datepicker('setStartDate', minDate);
            }else{
              $('#date_end1').datepicker('setStartDate', '');
            }
        })

});
      function horizontalGraph(data,label,el){
        let html = document.createElement("div")
        html.classList.add("graph-outer")
        let backgroundColor = [
        '#4AD4C0',
        '#FFA844',
        '#E54563',
        'rgb(255, 153, 204)',
        'rgb(0,0,128,0.4)',                                       
        'rgb(0,0,128)',
        'rgb(0,0,255)', 
        'rgb(0,128,0)',
        'rgb(0,128,128)',
        'rgb(0,255,0)',
        'rgb(0,255,255)',
        'rgb(128,0,0)',
        'rgb(128,0,128)',
        'rgb(128,128,0)',
        'rgb(128,128,128)',
        'rgb(192,192,192)',
        'rgb(255,0,0)',
        'rgb(255,0,255)',
        'rgb(0,128,0,0.3)',
        'rgb(0,255,255,0.4)',
        'rgb(128,128,0,0.3)',
        'rgb(255,0,0,0.8)',
        'rgb(255,255,0)',
        'rgb(153, 255, 51)',
        'rgb(0, 204, 153)',
        'rgb(102, 0, 255)',
        'rgb(0, 153, 255)',
        'rgb(255, 204, 255)',
        'rgb(255, 204, 153)',
        'rgb(204, 204, 255)',
        'rgb(51, 51, 0)',
        'rgb(204, 255, 255)',
        'rgb(102, 102, 153)',
        'rgb(255, 255, 153)',
        'rgb(102, 153, 153,0.4)',
        'rgb(204, 255, 255,0.4)',
        'rgb(255, 102, 0)',
        'rgb(153, 153, 102)',
        'rgb(0, 51, 0)',
        'rgb(51, 153, 51)',
        'rgb(204, 204, 0)',
        'rgb(0, 51, 102)',
        'rgb(204, 255, 153)',
        'rgb(153, 255, 51)',
        'rgb(255, 204, 204)',
        'rgb(102, 255, 153)',
        'rgb(102, 153, 153)',
        'rgb(51, 17, 0)',
        'rgb(51, 102, 0)',
        'rgb(153, 0, 255)',
        'rgb(0, 102, 255)',
        'rgb(255, 255, 153)',
        ]
        if(data && label && data.length > 0 && label.length > 0){
          let sum = data.reduce(function(sum,amount){
            sum += amount
            return sum
          },0)
          let combinedArray = []
          let dataObj
          for (let i =0 ; i< data.length ; i++){
            dataObj = {}
            dataObj["x"] = data[i]
            dataObj["percentage"] = Math.round( (data[i] / sum ) * 100 )
            dataObj["y"] = label[i]
            combinedArray.push(dataObj)
          }
          //Draw
          for (let i =0; i < combinedArray.length; i++){
            let sub = document.createElement("div")
            sub.classList.add("bar-wrapper")
            //label
            let label = document.createElement("h5")
            label.innerText = combinedArray[i].y
            //bar
            let barOuter = document.createElement("span")
            let progress = document.createElement("span")
            progress.classList.add("progress")
            let percentageString = "";
            if(combinedArray[i].percentage < 1){
              percentageString = "1%"
            }
            else percentageString = combinedArray[i].percentage+"%"
            progress.setAttribute("style", "max-width:"+percentageString+";background:"+backgroundColor[i%backgroundColor.length]+";animation-delay:"+i*0.1+"s;");
            //tooltip
            let tooltip = document.createElement("span")
            barOuter.appendChild(progress)
            sub.appendChild(label)
            sub.appendChild(barOuter)
            html.appendChild(sub)
          }
          while(el.firstChild){
            el.removeChild(el.firstChild);
          }
          el.appendChild(html)
        }
        else{
          while(el.firstChild){
            el.removeChild(el.firstChild);
          }
          let warn = document.createElement("p")
          warn.innerText = "This investor has no data available."
          el.appendChild(warn)
        } 
      }
     function update_graph(flag) {

      var investor = $('#invested_amount').val();
      var lender = $('#lender').val();
      var attribute = $('#attribute').val();
      var graph_value = $('#graph_value').val();
      var label = $('#label').val();
      var date_start = $('#date_start').val();
      var date_end = $('#date_end').val();  
      $.ajax({
       headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },

      url: '/admin/update-graph',
      type: 'POST',
      /* send the csrf-token and the input to the controller */
      data: {investor:investor,lender:lender,attribute:attribute,graph_value:graph_value,label:label,date_start:date_start,date_end:date_end,flag:flag},

      /* remind that 'data' is the response of the AjaxController */
      success: function (result) {
	      calculatecontsize = result.length * 70;
	      // $('#myChart_div').css({"height":calculatecontsize + "px"} );
	      if(typeof(result)=='object')
        {       
        var amount = [];
        var label_name=[];
        var label_all=[];   
        total =0;
        percent = 0;
        for(var i in result) {
            var singleAmount=parseFloat(result[i].amount);
            total = parseFloat(total)+parseFloat(singleAmount);
        }

       for(var i in result) {
        if(result[i].amount){
         amount.push(result[i].amount);
         label_name.push((result[i].name)); 
         percent = Math.round((result[i].amount / total) * 100 * 100) / 100;
         result[i].amount=parseFloat(result[i].amount);
         if(result[i].name){ 
            let is_numeric_or_alpha; 
            if(Number.isFinite(result[i].name)){
              is_numeric_or_alpha = result[i].name.toFixed(2)
            }
            else{
              is_numeric_or_alpha = result[i].name
            } 
            label_all.push(is_numeric_or_alpha + ": $" + result[i].amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') + " ("+ percent + "%)" );}
         else{
          let isZero;
          if(result[i].name ==  0){
              isZero = '0'
          }
          else isZero = "Total "
          label_all.push(isZero + ": $" + result[i].amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') + " ("+ percent + "%)" );
        }
      }
      }
      average = total/result.length;
     
     if(average)
      {

    }else{
      average =0;   
      } 
      
     $("#avg").html( "$" + average.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')); 
     $("#tot").html( "$" + total.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')); 
        

      data = {
       datasets: [{
        data: amount,
        backgroundColor: [
        'rgb(255, 153, 204)',
        'rgb(0,0,128,0.4)',                                       
        'rgb(0,0,0)',
        'rgb(0,0,128)',
        'rgb(0,0,255)', 
        'rgb(0,128,0)',
        'rgb(0,128,128)',
        'rgb(0,255,0)',
        'rgb(0,255,255)',
        'rgb(128,0,0)',
        'rgb(128,0,128)',
        'rgb(128,128,0)',
        'rgb(128,128,128)',
        'rgb(192,192,192)',
        'rgb(255,0,0)',
        'rgb(255,0,255)',
        'rgb(0,128,0,0.3)',
        'rgb(0,255,255,0.4)',
        'rgb(128,128,0,0.3)',
        'rgb(255,0,0,0.8)',
        'rgb(255,255,0)',
        'rgb(153, 255, 51)',
        'rgb(0, 204, 153)',
        'rgb(102, 0, 255)',
        'rgb(0, 153, 255)',
        'rgb(255, 204, 255)',
        'rgb(255, 204, 153)',
        'rgb(204, 204, 255)',
        'rgb(51, 51, 0)',
        'rgb(204, 255, 255)',
        'rgb(102, 102, 153)',
        'rgb(255, 255, 153)',
        'rgb(102, 153, 153,0.4)',
        'rgb(204, 255, 255,0.4)',
        'rgb(255, 102, 0)',
        'rgb(153, 153, 102)',
        'rgb(0, 51, 0)',
        'rgb(51, 153, 51)',
        'rgb(204, 204, 0)',
        'rgb(0, 51, 102)',
        'rgb(204, 255, 153)',
        'rgb(153, 255, 51)',
        'rgb(255, 204, 204)',
        'rgb(102, 255, 153)',
        'rgb(102, 153, 153)',
        'rgb(51, 17, 0)',
        'rgb(51, 102, 0)',
        'rgb(153, 0, 255)',
        'rgb(0, 102, 255)',
        'rgb(255, 255, 153)',
        ],
      }],
      labels: label_all
    };
    // document.getElementById('myChart_div').innerHTML='';
    // document.getElementById('myChart_div').innerHTML='<canvas  id="myChart" ></canvas>';
      horizontalGraph(amount,label_all,document.getElementById('myChart_div'));

//     var ctx = document.getElementById('myChart').getContext('2d');
//     var myPieChart = new Chart(ctx,{
//       type: 'horizontalBar',
//       data: data,
//       options: {
//         curvature: 1,
// 	      maintainAspectRatio: false,
// 	      /*legend: {
//           labels: {
//             fontSize: 19,
//             fontColor: 'black',
//             responsive: true
//           }
//         },*/
// 	      legend: {
// 		      display: false
// 	      },
// 	      scales: {
// 		      yAxes: [{
//             gridLines: {
//                 display:false,
//                 drawBorder: false,
//             },
//             angleLines: {
//               display: false
//             },
// 			      barThickness: 10,  // number (pixels) or 'flex'
// 			      maxBarThickness: 10 // number (pixels)
//           }],
//           xAxes:[{
//             display:false,
//             gridLines: {
//                 display:false,
//                 drawBorder: false,
//             },
//             angleLines: {
//               display: false
//             }
//           }]
// 	      },

//         tooltips: {
//           callbacks: {
//             title: function(tooltipItem, data) {
//               return data['labels'][tooltipItem[0]['index']];
//             },
//             label: function(tooltipItem, data) {
//            // return "$"+(data['datasets'][0]['data'][tooltipItem['index']]).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
//            return "";
//          },

//          afterLabel: function(tooltipItem, data) {       
//           var dataset = data['datasets'][0];
//           var percent = Math.round((dataset['data'][tooltipItem['index']] / total) * 100 * 100) / 100;              
//          // return '(' + percent + '%)';
//          return "";
//        }
//      },
//      backgroundColor: '#FFF',
//      titleFontSize: 29,
//      titleFontColor: '#0066ff',
//      bodyFontColor: '#000',

//      bodyFontSize: 21,
//      displayColors: false,
//      labels:50

//    }


//  }




// });    


}  // end if object
  }  //end succes 
}); 

}


$("#payment-form").validate({
  rules:{
    attribute:{
      required:true
    },
    graph_value:{
      required:true
    },
    label:{
      required:true
    }
  }
})


$("#applyFilterBtn").click(function (evt) {
  evt.preventDefault();
  $("#payment-form").validate();
  if($("#payment-form").valid()){
    update_graph('web');
  }
});

$("#applyDownloadBtn").click(function (evt) {
  update_graph('download');
});




</script>


<script src="{{ asset('/js/Chart.min.js') }}"></script>



@stop



@section('styles')
<link href="{{ asset('/css/optimized/Liquidity_Log.css?ver=5') }}" rel="stylesheet" type="text/css" />


<style type="text/css">

  .bar-wrapper{
    display:flex;
    background:#FCFBFF;
    justify-content:center;
    flex-direction:column;
    padding:20px 10px;
    border: 1px solid #F1F2FB;
    margin-bottom:20px;
    border-radius:10px;
  }
  .bar-wrapper h5 {
    font-weight:600;
    color:#48486E;
    margin:0 0 8px 0;
  }
  .bar-wrapper span{
    display:block;
    width:100%;
    background:#E0E3EE;
    box-shadow: 0 5px 20px rgba(1,5,129,0.05);
    height:10px;
    border-radius:5px;
  }
  .bar-wrapper span > span{
    box-shadow: 0 5px 20px rgba(1,5,129,0.05);
    opacity:0;
  }
  .btn-box > label.grid{
    display:flex;
    justify-content:space-between;
  }
  .btn-box{
    margin-top:20px;
  }
  .btn-box,.grid{
    width:100%;
  }
  .grid > p , .grid > .grid{
    width:50%;
    display:flex;
  }
  .grid > .grid{
    justify-content:flex-end;
  }
  .grid > .grid p{
    text-align:right;
    display:block;
  }
  .progress{
    animation:fill ease-in 0.8s forwards;
  }
  #myChart {
    width: 80% !important;
    margin-left: 10%;
    margin-top:10px;
  }
  .error,span.error{
    color: #dc3545ed;
  }
  @keyframes fill {
    0%{
      width:0%;
      opacity:0.8;
    }
    100%{
      width:100%;
      opacity:1;
    }
  }
  @media(max-width:1199px) {
    #myChart {
      width: 100% !important;
      margin-left: 0;
      margin-top:10px;
    }
  }

  /*#myChart_div{
      height: 1000px;
  }*/


</style>

<style type="text/css">
    li.breadcrumb-item.active{
      color: #2b1871!important;
    }
    li.breadcrumb-item a{
       color: #6B778C;
    }
    .select2-selection__rendered {
      display: inline !important;
    }
    .select2-search--inline {
      float: none !important;
    }
</style>


@stop
