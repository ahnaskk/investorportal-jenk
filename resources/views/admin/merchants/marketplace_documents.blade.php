@extends('layouts.admin.admin_lte')
@section('content')
<div class="inner admin-dsh header-tp">

      <h3><i class="fa fa-angle-right" aria-hidden="true"></i>Documents Upload </h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Documents Upload </div>     
      </a>
      
  </div>


  {{ Breadcrumbs::render('merchantDocumentsupload',$merchant) }}


  @if($valid_merchant==1)
  <div class="col-md-12">
<div class="box">
       <div class="box-body">
        <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">

 <div class="row">

            <div class="col-md-8 col-md-offset-2">

                <div id="mydropbox" class="dropzone">
                <div class="fallback">
                    <input name="file" type="file" multiple />
                </div>
                </div>


            </div>

  </div>

        <div class="row">

               <div class="table-responsive">
                    {!! $tableBuilder->table(['class' => 'table table-bordered'], true) !!}
                </div>

        </div>

</div>
</div>
</div>
</div>
@else

  <div class="col-md-12">
<div class="box">
       <div class="box-body">
        <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">

 <div class="row">

            <div class="col-md-8 col-md-offset-2">

                <span>Invalid Merchant</span>


            </div>

  </div>


</div>
</div>
</div>
</div>
@endif
@stop

@section('scripts')

    <script src="{{ asset ("bower_components/datatables.net/js/jquery.dataTables.min.js") }}"
            type="text/javascript"></script>

    <script src="{{ asset ("bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js") }}"
            type="text/javascript"></script>


    <script src="{{ asset ("vendor/dropzone/dropzone.js") }}"
            type="text/javascript"></script>

    <script src="{{ asset ("vendor/notyf/notyf.min.js") }}"
            type="text/javascript"></script>

    {!! $tableBuilder->scripts() !!}
    
    <script type="text/javascript">
        function myfunction(id){
             other = document.getElementById("type_"+id).value;
             if(other==9){
               document.getElementById("other_type_"+id).style.display = "inline-block";
                
             }else{
                document.getElementById("other_type_"+id).style.display = "none";
             }
    
        }

        Dropzone.autoDiscover = false;
        var table = window.LaravelDataTables["dataTableBuilder"];
        
        var notyf = new Notyf();
        var myDropzone = new Dropzone("div#mydropbox",
                { 
                    url: "{{route("admin::merchant_investor::merchant_documents_upload::merchant-upload-docs-admin",compact('mid'))}}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    acceptedFiles: '.jpg, .jpeg, .doc, .csv, .xlsx, .xls, .docx, .ppt, .odt, .ods, .odp, .pdf'
                    
            });
            
         myDropzone.on("success", function (response) {
            
            if(response.status=='success')
            {
                   notyf.confirm("Uploaded successfully");
                  
            }
            else
            {
                  notyf.confirm("not upload successfully");
               
                
            }
               table.draw('page'); 
           
    });


   $(document).on("click", '.updatedoc', function (e) {

            var title = $("#title_" + $(this).data('id')).val();
            var type = $("#type_" + $(this).data('id')).val();
            var other_type = $("#other_type_" + $(this).data('id')).val();


            var data = {title: title, type: type, other_type:other_type}

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                data: data,
                url: $(this).data('url'),

            }).done(function(data){
                 if(data.type==9){
              $("#type_"+data.doc_id).append("<option value='+other_type+' selected='selected'>"+data.other_type+"</option>");
              document.getElementById("other_type_"+data.doc_id).style.display = "none";
              }
                notyf.confirm("updated !");
            });

        });


        $(document).on("click", '.deletedoc', function (e) {

            if ( confirm("Are you sure want to delete this doc ? ")) {
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: 'POST',
                    url: $(this).data('url'),

                }).done(function () {

                    $(".dz-preview").remove();
                    //$(".ddz-default").html('Drop files here to upload');
                   
                    notyf.confirm("Deleted successfully");
                    table.draw('page');

                });
            }


        });

    </script>
   

@stop

@section('styles')
    <link href="{{ asset('/css/optimized/document.css?ver=5') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/css/bower_components/breadcrumbs/breadcrumb1.css') }}" rel="stylesheet" type="text/css" />
@stop