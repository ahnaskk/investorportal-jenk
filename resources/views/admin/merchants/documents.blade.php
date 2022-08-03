@extends('layouts.admin.admin_lte')


@section('content')
<div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>View Documents </h3>
    <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">View Documents</div>     
    </a>
    
</div>
{{ Breadcrumbs::render('merchantDocumentsupload',$merchant) }}
@if($valid_merchant==1)
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
                            {!! $tableBuilder->table(['class' => 'table table-bordered'], true) !!}

                        </div>
                    </div>
                </div>
            </section>

        </div>
    </div>
</div>
@endif
<div class="col-md-12">
    <div class="box box-primary">
        <div class="box-body col-md-12">
            <section class="graph-sec">
                <div class="row">
                   @if($valid_merchant==0)
                 <div class="row">
                    <div class="col-md-12">
                        <span class="invalid-tag">Invalid merchant</span>
                    </div>
                </div>
                @endif


                </div>                
            </section>

        </div>
    </div>
</div>

                
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
    Dropzone.autoDiscover = false;
    var table = window.LaravelDataTables["dataTableBuilder"];
    var notyf = new Notyf();


    var myDropzone = new Dropzone("div#mydropbox",
    { 
        url: "{{route("admin::merchant_investor::document::upload-docs",compact('mid','iid'))}}",
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


        var data = {title: title, type: type}

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'POST',
            data: data,
            url: $(this).data('url'),

        }).done(function(){
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
               notyf.confirm("Deleted successfully");
               table.draw('page');

           });
        }


    });

</script>


@stop

@section('styles')
<link href="{{ asset('/css/optimized/document.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop