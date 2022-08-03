@extends('layouts.admin.admin_lte')


@section('content')
 <div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>View Documents </h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">View Documents</div>     
      </a>
      
  </div>


<div class="col-md-12">
    <div class="box box-primary">
        <div class="box-body col-md-12">
            <section class="graph-sec">
                <div class="row">
                    <div class="col-md-8 col-md-offset-2">


                        <div id="mydropbox" class="dropzone">

                        </div>
                    </div>


                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="table-container">
                    <input class="form-control" id="mid" name="mid" type="hidden" value="{{$mid}}"/>
                            <!-- {!! $tableBuilder->table(['class' => 'table table-bordered'], true) !!} -->
                    <table class="table">
                        <tr>   
      <th scope="col">Document Name</th>
      <th scope="col">File</th>   
    </tr>
               @if ($docs)
                    @foreach ($docs as $doc)
                     <tr>
            <td>{{$doc['basename']}} </td>   <td><i class="fa fa-file-pdf-o"></i> <a href="{{public_path('merchant_documents').'/'.$mid.'/'.$doc['basename'] }}" target="_blank">{{$doc['basename']}} </a></td> 
        </tr> 
           @endforeach 
              @else
              <tr>
             <td width="50%">No Documents Uploaded </td>   </tr>
            @endif    
    
    </table>

                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@stop

@section('scripts')
<script src="{{ asset ('bower_components/datatables.net/js/jquery.dataTables.min.js') }}"
type="text/javascript"></script>
<script src="{{ asset ('bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"
type="text/javascript"></script>
<script src="{{ asset ('/js/updated/moment.min.js') }}" type="text/javascript"></script>
<script src="{{ asset ('/js/updated/bootstrap-datetimepicker.min.js') }}" type="text/javascript"></script>
<!-- <script src="https://twitter.github.io/typeahead.js/js/handlebars.js"></script> -->


 <script>
         $(document).ready( function () {
   $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  }); 
               $('#users-table').DataTable({
               processing: true,
               serverSide: true,
                   ajax: {
                url: '/admin/reports/agreement',
                type: 'POST',          
                dataType: 'json', 
                'data': function (data) {
                  data._token = _token;
                data.mid = $('#mid').val(); 
                  return data;
                }, 
              },
           
               columns: [
                        { data: 'dirname', name: 'dirname' },
                        { data: 'basename', name: 'basename' }
                     
                     ]
            });
         });
         </script>


@stop

@section('styles')
<link href="{{ asset('/css/optimized/document.css') }}" rel="stylesheet" type="text/css" />
@stop