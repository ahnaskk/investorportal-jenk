@extends('layouts.marketplace.admin_lte')

@section('content')
   


        
        <!-- /.col -->

          <a href="{{URL::to('investor/mailbox')}}" class="btn btn-primary mail-back mb-15">Back to lists</a>
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Read Mail</h3>
<!-- 
              <div class="box-tools pull-right">
                <a href="#" class="btn btn-box-tool" data-toggle="tooltip" title="" data-original-title="Previous"><i class="fa fa-chevron-left"></i></a>
                <a href="#" class="btn btn-box-tool" data-toggle="tooltip" title="" data-original-title="Next"><i class="fa fa-chevron-right"></i></a>
              </div> -->
            </div>
            <!-- /.box-header -->
            <div class="box-body no-padding">
              <div class="mailbox-read-info">
                <h3>{{$mailboxrow->title}}</h3>
                <h5>From: {{isset($mailboxrow->user->email)?$mailboxrow->user->email:''}}
                  <span class="mailbox-read-time pull-right">15 Feb. 2016 11:03 PM</span></h5>
              </div>
              <!-- /.mailbox-read-info -->
    
              <!-- /.mailbox-controls -->
              <div class="mailbox-read-message">
               

{{$mailboxrow->content}}

              </div>
              <!-- /.mailbox-read-message -->
            </div>
            <!-- /.box-body -->
            
            <!-- /.box-footer -->
            
            <!-- /.box-footer -->
          </div>
          <!-- /. box -->

        <!-- /.col -->
      </div>
      <!-- /.row -->
    <div>
</div>




@stop


@section('scripts')
   
@stop
@section('styles')
<style type="text/css">@import url({{ asset('/css/font-awesome3.2.1.css') }});
/* 
    FORM STYLING
*/
#fileselector {
    margin: 10px; 
}
#upload-file-selector {
    display:none;   
}
.margin-correction {
    margin-right: 10px;   
}</style>
    <link rel="stylesheet" href="{{ asset ("bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css") }}">
@stop