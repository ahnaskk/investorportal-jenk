@extends('layouts.admin.admin_lte')

@section('content')
   
<div class="inner admin-dsh header-tp">
    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>Mail Inbox</h3>
    <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Mail Inbox</div>     
    </a>
</div>


    
        
        <!-- /.col -->
        <div class="col-md-12">
          <div class="box box-primary mail-box">
            <div class="box-header with-border">

              
<!-- 
              <div class="box-tools pull-right">
                <a href="#" class="btn btn-box-tool" data-toggle="tooltip" title="" data-original-title="Previous"><i class="fa fa-chevron-left"></i></a>
                <a href="#" class="btn btn-box-tool" data-toggle="tooltip" title="" data-original-title="Next"><i class="fa fa-chevron-right"></i></a>
              </div> -->
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <a href="{{URL::to('admin/mailbox')}}" class="btn btn-primary btn-back">Back to lists</a>
              <div class="mail-container">

                <!-- <h3 class="box-title">Read Mail</h3> -->
              <div class="mailbox-read-info">
                <h3>{{$mailboxrow->title}}</h3>
                <h5 class="mail-from-head">From: {{isset($mailboxrow->user->email)?$mailboxrow->user->email:''}}
                  <span class="mailbox-read-time pull-right">{{date(\FFM::defaultDateFormat('db').' h:i:s A',($mailboxrow->timestamp))}}</span></h5>
              </div>
              <!-- /.mailbox-read-info -->
    
              <!-- /.mailbox-controls -->
              <div class="mailbox-read-message">
               

     {{$mailboxrow->content}}

              </div>
              <!-- /.mailbox-read-message -->
            </div>
            </div>
            <!-- /.box-body -->
            
            <!-- /.box-footer -->
            
            <!-- /.box-footer -->
          </div>
          <!-- /. box -->
        </div>
        <!-- /.col -->
 
      <!-- /.row -->





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