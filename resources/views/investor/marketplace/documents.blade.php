@extends('layouts.marketplace.admin_lte')
@section('styles')
<style type="text/css">
  .imgFrame {
      width: 100%;
      height: 500px; 
      background-size: contain;
      background-repeat: no-repeat;
      display: none;
  }
  .fileFrame {
    display: none;
  }
</style>
@endsection
@section('content')
    <div class="container" style="margin-top: 100px">
    <div class="row">
    <section class="content graph-sec">
        <div class="row">
            <div class="col-md-2 pull-right" style="padding-bottom: 50px;">


              <a class="form-control btn btn-success" href="{{URL::to('/investor/marketplace/marketplace')}}"> Back to Marketplace </a>
            </div>


        </div>

        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="table-container table-responsive invstDocPage">
                    {!! $tableBuilder->table(['class' => 'table table-bordered'], true) !!}

                </div>
            </div>
        </div>
    </section>
    
    <div class="modal fade bd-example-modal-xl"id="fileModal" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title text-capitalize" id="fileModalLabel"></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
              <div class="imgFrame"></div>
              <object class="fileFrame" type="application/pdf" width="100%" height="500px"  data=""></object>
          </div>
        </div>
      </div>
    </div>
     
@stop


@section('scripts')
  <script src="{{ asset ("bower_components/datatables.net/js/jquery.dataTables.min.js") }}"
          type="text/javascript"></script>

  <script src="{{ asset ("bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js") }}"
          type="text/javascript"></script>
      {!! $tableBuilder->scripts() !!}

<script>
 $('#fileModal').on('show.bs.modal', function (event) {
  var button = $(event.relatedTarget) // Button that triggered the modal
  var file = button.data('url') // Extract info from data-* attributes
  var title = button.data('title') // Extract info from data-* attributes
  var ext = button.data('ext') // Extract info from data-* attributes
  var modal = $(this)
  modal.find('.modal-title').text(title)
  if(ext == 'pdf'){
    modal.find('.imgFrame').css("display", "none")
    modal.find('.fileFrame').css("display", "block")
    modal.find('.fileFrame').attr('data',file + '#toolbar=0')
  }
  else {
    modal.find('.imgFrame').css("display", "block")
    modal.find('.fileFrame').css("display", "none")
    modal.find('.imgFrame').css("background-image", "url(" + file + ")")
    // var width = button.data('width') // Extract info from data-* attributes
    // var height = button.data('height') // Extract info from data-* attributes
    // modal.find('.fileFrame').attr('width',width)
    // modal.find('.fileFrame').attr('height',height)
  }
})
</script>
@stop
