@extends('layouts.admin.admin_lte')

@section('content')

@php $holidays = config('custom.holidays');  @endphp

<div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{$title}} </h3>

    <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">{{$title}}</div>
    </a>

</div>
{{ Breadcrumbs::render('admin::fullcalender') }}
<section>
    <div class="container-fluid grid">
        <div class="box-body">
            <div class="row">
                <div class="box-primary">
                    <div id='calendar'></div>
                </div>
            </div>
        </div>
    </div>

  
   
  </section>


@stop

@section('scripts')
 <script src="{{ asset('js/moment.min.js') }}"></script>

<script src="{{ asset('js/jquery-ui.custom.min.js') }} "></script>
<!-- <script src='http://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.1.0/fullcalendar.js'></script> -->

<script src='{{ asset('js/fullcalender.js') }}'></script>

<script type="text/javascript">
  
$(document).ready(function() {

   var app = @json($holidays);

  var calender = [];

  $.each(app, function( key, value ) {
      calender.push({
          title: value,
          start: key,
          className:"event-full",
          backgroundColor:'#ff0000',
      });
});

     
 // alert(calender);

console.log(calender);

$('#calendar').fullCalendar({
      header: {
        left: 'prev,next today',
        center: 'title',
       // right: 'month,agendaWeek,agendaDay'
       right: ''
      },
      defaultDate: new Date(),
      defaultView: 'month',
      editable: false,
      events:calender,
    });
    
  });


</script>


@stop

@section('styles')

<!-- <link rel='stylesheet' href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.1.0/fullcalendar.min.css" /> -->

<link rel='stylesheet' href="{{ asset('css/fullcalender.css?ver=5') }}" />
<link href="{{ asset('/css/bower_components/breadcrumbs/breadcrumb1.css?ver=5') }}" rel="stylesheet" type="text/css" />

<style type="text/css">


  .event-full {
  color: #fff;
  vertical-align: middle !important;
  text-align: center;
  opacity: 1;
}


.fc-sun { background-color:orange; }
.fc-sat { background-color:orange;  }


.fc-widget-header{ background-color:white;  }


  
 /* body {
    margin: 0;
    padding: 50px 0 0 0;
    font-family:"Lucida Grande", Helvetica, Arial, Verdana, sans-serif;
    font-size: 14px;
}

/*#calendar {
    width: 100%;
}*/
/*.holiday {
    background: lightgray;
}*/








</style>

@stop