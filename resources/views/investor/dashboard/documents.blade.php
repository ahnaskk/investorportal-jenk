@extends('layouts.investor.admin_lte')

@section('content')
 
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



<!--     <section class="content graph-sec">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">


                <div id="mydropbox" class="dropzone">

                </div>
            </div>


        </div>

        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="table-container table-responsive invstDocPage">
                    {!! $tableBuilder->table(['class' => 'table table-bordered'], true) !!}

                </div>
            </div>
        </div>
    </section> -->

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
        var table = window.LaravelDataTables["dataTableBuilder"];
        var notyf = new Notyf();


        var myDropzone = new Dropzone("div#mydropbox",
                {
                    url: "{{route("investor::dashboard::upload-docs",['id'=>$merchantId])}}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

        myDropzone.on("success", function (response) {

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
                    table.draw('page');

                });
            }


        });

    </script>


@stop

@section('styles')
    <link href="{{asset('vendor/dropzone/dropzone.css')}}" rel="stylesheet" type="text/css"/>
    <link href="{{asset('vendor/notyf/notyf.min.css')}}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('/css/optimized/document.css') }}" rel="stylesheet" type="text/css" />

@stop