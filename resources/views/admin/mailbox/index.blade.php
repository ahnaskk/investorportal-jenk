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
          <div class="box box-primary">
             <!--<div class="box-header with-border">


              <div class="box-tools pull-right">
                <div class="has-feedback">
                  <input type="text" class="form-control input-sm" placeholder="Search Mail">
                  <span class="glyphicon glyphicon-search form-control-feedback"></span>
                </div>
              </div>

            </div> -->
            <!-- /.box-header -->
            <div class="box-body no-padding">
    
                <div class="mailbox-controls">
                  <div class="row">
                    <!-- Check all button -->
                    <div class="col-md-6">
                      <button onClick="window.location.reload()" type="button" class="btn btn-success">Refresh</button>
                    </div>
                    <div class="col-md-6">
                      <div class="pull-right">                   
                       {{ $mailboxrows->links() }}
                      </div>
                    </div>
                  </div>
                  <!-- /.pull-right -->
                </div>
              
      
                <div class="table-responsive mailbox-messages grid">
                  <table class="table table-hover table-striped">
                    <tbody>
                      @foreach($mailboxrows as $mailboxrow)
                      <tr>
                      <td><div class="icheckbox_flat-blue" aria-checked="false" aria-disabled="false" style="position: relative;"><input type="checkbox" style="position: absolute; opacity: 0;"><ins class="iCheck-helper" style="position: absolute; top: 0%; left: 0%; display: block; width: 100%; height: 100%; margin: 0px; padding: 0px; background: rgb(255, 255, 255); border: 0px; opacity: 0;"></ins></div></td>
                      <td class="mailbox-star"><a href="#"><i class="fa fa-star text-yellow"></i></a></td>
                      <td class="mailbox-name"><a href="{{URL::to('admin/mailbox',$mailboxrow->id)}}">{{isset($mailboxrow->user->name)?$mailboxrow->user->name:''}}</a></td>
                      <td class="mailbox-subject"><a href="{{URL::to('admin/mailbox',$mailboxrow->id)}}"><b>{{$mailboxrow->title}}</b> </a>
                      </td>
                      <td class="mailbox-attachment"></td>
                      <td class="mailbox-date"> {{\FFM::datetime($mailboxrow->timestamp)}}</td>
                    </tr>
                    @endforeach

                    </tbody>
                  </table>
                  <!-- /.table -->
                </div>
      
              <!-- /.mail-box-messages -->
              <div class="box-footer no-padding">
              <div class="mailbox-controls pull-right">
                <!-- Check all button -->
              
                <!-- /.btn-group -->
              <!--   <button onClick="window.location.reload()" type="button" class="btn btn-default btn-sm"><i class="fa fa-refresh"></i></button>
                <div class="pull-right"> -->
           

           {{ $mailboxrows->links() }}
                  <!-- /.btn-group -->
                </div>
                <!-- /.pull-right -->
              </div>
            </div>
            <!-- /.box-body -->
            
            </div>
        </div>
          <!-- /. box -->
         <!-- /.col -->
  
      <!-- /.row -->
 



@stop


@section('scripts')
   
@stop
@section('styles')
<link href="{{ asset('/css/optimized/inbox.css?ver=5') }}" rel="stylesheet" type="text/css" />

@stop