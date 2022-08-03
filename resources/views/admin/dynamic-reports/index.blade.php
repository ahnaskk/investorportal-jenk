@extends('layouts.admin.admin_lte')
@section('content')

    <div class="inner admin-dsh header-tp">
        <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{ isset($page_title) ? $page_title : '' }} </h3>
        <a href="#" class="help-link">
            <i class="fa fa-question-circle" aria-hidden="true"></i>
            <div class="tool-tip">{{ isset($page_title) ? $page_title : '' }}</div>
        </a>
    </div>
    {{-- {{ Breadcrumbs::render('admin::template::index') }} --}}
    <div class="col-md-12">
        <div class="box">
            <div class="box-head ">
                @include('layouts.admin.partials.lte_alerts')
            </div>

             @php 
            $edit_action=$id=request()->segment(4);


             $id=request()->segment(3);  @endphp
 @if($id)
                         <div>
                          <a style="background-color:dodgerblue !important"  class="btn btn-info" href="{{ url()->previous() }}"><span class="glyphicon glyphicon-backward" aria-hidden="true"></span> Back</a>
                      </div>

                      @endif
                   
              
            <div class="box-body">
 
          @if($id && ($edit_action!='edit'))
           
           {{Form::open(['route'=>['admin::dynamic_report_export', $id],'method'=>'POST'])}}

            {{Form::submit('download',['class'=>'btn btn-primary','id'=>'form_filter','name'=>'download'])}}

           {{Form::close()}}
      

           @endif

                  @if(!$id || ($edit_action=='edit'))

                  @if($edit_action=='edit')

                 <form method="post" action="{{ route('admin::dynamic-report.update',$id) }}" id="generate_dynamic_report" >
                    @method('put')


                  @else
                <form method="post" action="{{route('admin::dynamic-report.store')}}" id="generate_dynamic_report">
                  @endif

                     @csrf
                <div class="row align-items-center">
                    <h2>Add Custom View</h2>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Name for Report <span class="validate_star">*</span></label>
                            <input class="form-control" value="{{ isset($dr->name)?$dr->name:old('name') }}"  placeholder="Name for Report" name="name" type="text" required id="inputName"> 
                        </div>
                    </div>  
                    <div class="col-auto my-1">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" >
                            <label class="form-check-label">
                                Set as Default
                            </label>
                        </div>
                    </div>
                   <div class="input-group">
                        <span class="help-block">Select Required Columns <span class="validate_star">*</span></span>
                        <div class="input-group-text">
                            <span class="glyphicon glyphicon-th-list" aria-hidden="true"></span>
                        </div>

                     {{Form::select('field_keys[]',$all_fields,isset($selected_field_keys)?$selected_field_keys:old('field_keys'),['class'=>'form-control js-investor-placeholder-multiple','id'=>'field_keys','multiple'=>'multiple','required'=>'required'])}}
                    </div>
                </div>
                <hr/>
               
               <div id="error_msg" class="box-body"></div>

                <div class="row" id="dynamic-fields">
                    <h3>Choose filter conditions:</h3>
                    <h4>All Conditions(All conditions must be met)</h4>
                    <div class="row">

                         @php  $selected_filter_keys=isset($selected_filter_keys)?$selected_filter_keys:''; 
                             $selected_operator=isset($selected_operator)?$selected_operator:''; 
                             $selected_key_search=isset($selected_key_search)?$selected_key_search:'';
                             $key_search=[];
                               @endphp


                       @if($edit_action=='edit')

                       @if($selected_filter_keys[0])

                       @foreach($selected_filter_keys as $key => $value)

                            <div class="row_class">

                            <div class="form-group col-md-3">
                           
                    {{Form::select('filter_keys[]',$filter_keys,($value)?$value:old('filter_keys'),['class'=>'form-control js-investor-placeholder-multiple select-to-select2 filter_keys wrapper_class'])}}

                        </div>
                        <div class="form-group col-md-3">
                            <select class='select-to-select2 filter_operator wrapper_class' name="filter_operator[]">
                                @foreach($operator as $key1=>$value1)
                            <option  {{ ($value1==$selected_operator[$key]?'selected':'') }} value="{{ $value1 }}">{{ $value1 }}</option>
                                @endforeach
                            </select>
                        <span id="error_message_for_filter_operator" class="text-danger error_message_for_filter_operator"></span> 
                        </div>
                        <div class="form-group col-md-3 ">

                          @if($value=='state_id')

                              @php $key_search=$states; @endphp

                            @elseif($value=='sub_status_id')
                             
                             @php $key_search=$substatus; @endphp

                             @elseif($value=='industry_id')
                                @php $key_search=$industry; @endphp

                             @elseif($value=='label')
                                 @php $key_search=$label; @endphp
                             @elseif($value=='source_id')
                                   @php $key_search=$source; @endphp
                             @elseif($value=='sub_status_flag')
                                @php $key_search=$substatusflag; @endphp
                             @elseif($value=='lender_id')
                             @php $key_search=$lender; @endphp
                             @elseif($value=='advance_type')
                              @php $key_search=$advancetype; @endphp
                             
                            @endif   

                        <select class='select-to-select2 key_search wrapper_class' name="key_search[]">

                                @foreach($key_search as $key2=>$value2)
                            <option  {{ ($key2==$selected_key_search[$key]?'selected':'') }} value="{{ $key2 }}">{{ $value2 }}</option>
                                @endforeach
                          

                        </select>

                    <span id="error_message_for_key_search" class="text-danger error_message_for_key_search"></span> 

                        </div>
                        <div class="form-group col-md-3">

                             <button type="button"  class="btn btn-danger remove-row_class">-</button>
                
                             
                    </div>
                </div>
                 @endforeach 
                 @else

               <div class="row_class">
                                 <div class="form-group col-md-3">
                           
                    {{Form::select('filter_keys[]',$filter_keys,old('filter_keys'),['class'=>'form-control js-investor-placeholder-multiple select-to-select2 filter_keys'])}}

                     <span id="error_message_for_filter_key" class="text-danger error_message_for_filter_key "></span>

                        </div>
                        <div class="form-group col-md-3">
                            <select class='select-to-select2 filter_operator wrapper_class' name="filter_operator[]">
                            </select>
                        <span id="error_message_for_filter_operator" class="text-danger error_message_for_filter_operator "></span> 
                        </div>
                        <div class="form-group col-md-3">
                            <select class='select-to-select2 key_search' name="key_search[]">
                            </select>
                            <span id="error_message_for_key_search" class="text-danger error_message_for_key_search"></span> 
                        </div>


                         <div class="form-group col-md-3">


                            <button type="button" style="background-color:dodgerblue !important" id="add-row" class="btn btn-info">
                            +
                            </button>
                        </div>

               </div>         

                        @endif
                       @else
                        <div class="row_class">
                            <div class="form-group col-md-3">
                           
                    {{Form::select('filter_keys[]',$filter_keys,0,['class'=>'form-control js-investor-placeholder-multiple select-to-select2 filter_keys'])}}

                        </div>
                        <div class="form-group col-md-3">
                            <select class='select-to-select2 filter_operator' name="filter_operator[]">
                            </select>
                      <span id="error_message_for_filter_operator" class="text-danger error_message_for_filter_operator"></span> 
                        </div>
                        <div class="form-group col-md-3">

                             <select class='select-to-select2 key_search' name="key_search[]">
                            </select>
                          
                        <span id="error_message_for_key_search" class="text-danger error_message_for_key_search"></span> 
                        </div>

                       @endif

                        <div class="form-group col-md-3">


                            <button type="button" style="background-color:dodgerblue !important" id="add-row" class="btn btn-info">
                            +
                            </button>
                        </div>

                    </div>



                    </div>

                   

                </div>

                <div class="row">
                        <div class="col-md-3 offset-md-6">
                         <a href="{{ url('admin/dynamic-report') }}" class="btn btn-primary pull-right" style="margin-left:10px">Cancel</a>
                         <!--  <button class="btn btn-info pull-right generate_report" id="generate_report" type="submit">Generate</button> -->

                       <!--   <button class="btn btn-info pull-right generate_report" id="generate_report">Generate</button> -->

                          <input type="button" value="Generate" class="btn btn-info pull-right generate_report" id="generate_report">

                        </div>
                    </div>
           </form>

@endif

                <div id="CreateNewReport2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                    <div class="row">


                        <!-- <div class="col-sm-10" style="padding-bottom:15px">
                            <a style="background-color:dodgerblue !important" class="btn btn-info"
                                href="{{ url()->previous() }}"><span class="glyphicon glyphicon-backward"
                                    aria-hidden="true"></span> Back</a>
                        </div> -->
                        <div class="col-sm-2" style="padding-bottom:15px">

                            <!-- <button style="float:right; margin-bottom:8px;background-color:dodgerblue !important"
                                type="button" class="btn btn-info" data-bs-toggle="modal"
                                data-bs-target="#CreateNewReportModal">
                                <span class="glyphicon glyphicon glyphicon-book" aria-hidden="true"></span>
                                Create a dynamic report
                            </button> -->

                        </div>

                    </div>
                    <div class="row">
                         @if($edit_action!='edit')
                        <div class="col-sm-12 table-responsive">
                            {!! $tableBuilder->table(['class' => 'table table-bordered','id'=>'branch'],true) !!}
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- /.box-body -->
        </div>
    </div>

@stop

@section('scripts')

    {!! $tableBuilder->scripts() !!}

    <script>
        $(".js-investor-placeholder-multiple").select2({
        placeholder: "Select Columns for reports"
    });
        var table = window.LaravelDataTables["branch"];
        $(document).ready(function() {

        $('#generate_dynamic_report').validate({ 
            errorClass: 'errors',
            rules: {
                name: {
                    required: true
                },
                'field_keys':{
                   required: true

                }           
                
            },
            messages: {
            name: "Enter Name",
            'field_keys':"Select Columns"
            
        } 
        });

              $error=0;
              error_msg='';
             $(document).on('click','.remove-row_class', function(e){

               $(this).closest("div.row_class").remove();
               e.preventDefault();
            
            });
        
           $('.filter_operator').change(function(){
             $filter_operator= $(this).closest('div.row').find('.filter_operator');
               if($filter_operator)
               {
                     $error=0;
                    $(this).closest('div.row').find('.error_message_for_filter_operator').html('');
               }

           });

           $('.key_search').change(function(){
             $key_search= $(this).closest('div.row').find('.key_search');
               if($key_search)
               {
                    $error=0;
                    $(this).closest('div.row').find('.error_message_for_key_search').html('');
               }
           }); 

         $('.filter_keys').change(function(){

             $filter_key=$(this).closest('div.row').find('.filter_keys').val();
             $filter_operator= $(this).closest('div.row_class').find('.filter_operator');
             $key_search= $(this).closest('div.row_class').find('.key_search');

             if($filter_key)
             {
                 condition_filter($filter_key,$filter_operator,$key_search);
             }
             else
             {
                 $(this).closest('div.row').find('.error_message_for_filter_operator').html('');
                 $(this).closest('div.row').find('.error_message_for_key_search').html('');

             }
       
        });

$('#generate_report').on('click',function(e)
          {   

       var id_arr=[];
       var id_op=[];
       var id_key=[];
       error_msg='';
       $error=0;
      
      $('.filter_keys').each(function() {
          id_arr.push($(this).val()); 
      });

      $('.filter_operator').each(function() {
          id_op.push($(this).val()); 
      });

      $('.key_search').each(function() {
          id_key.push($(this).val()); 
      });

    
      $.each(id_arr, function (i, val) {


          
            if(i!=0)
            {
                  if(id_arr[i]=='' || (id_op[i]==null || id_op[i]=='') || (id_key[i]==null || id_key[i]=='') )
                  {
                       $error=$error+1;

                  }
            }
            else{
                   if(i==0)
                   {  $error=0;
                    
                       if(id_arr[i]=='' && (id_op[i]==null || id_op[i]=='') && (id_key[i]==null || id_key[i]=='') )
                       {
                           $error=0;
                       }
                       else if(id_arr[i]!='' && (id_op[i]==null || id_op[i]=='') || (id_key[i]==null || id_key[i]=='') )
                       {
                           $error=$error+1;
                       }



                   }


             }


                  
         });

         if($error>0)
         {

            error_msg+='<div class="alert alert-danger alert-dismissable col-ssm-12" >';

            error_msg+='Please select or remove unselected filter condition rows</div>';
            $('#error_msg').html(error_msg);
            e.preventDefault();


         }
         else
         {

             $('#generate_dynamic_report').submit(); 

         }


         });

            $('.create_submit').click(function(e) {
                $('.create').hide();
                $('.please-wait').show();
                setTimeout(function() {
                    $('.create').show();
                    $('.please-wait').hide();
                }, 1000);
            });

            $("#branch").on("click", ".delete_report", function() {
                var id = $(this).data("id");
                if (confirm('Do you really want to delete the selected report ?')) {
                    $.ajax({
                        type: 'POST',
                        data: {
                            '_token': _token,
                            _method: 'delete'
                        },
                        url: "{{ url('admin/dynamic-report-investor') }}" + '/' + id,
                        success: function(data) {
                            table.ajax.reload(null, false);
                        }
                    });
                }
            });

            $("#branch").on("click", ".edit_report", function() {
                var id = $(this).data("id");
                $.ajax({
                    type: 'GET',
                    url: "{{ url('admin/dynamic-report-investor') }}" + '/' + id + '/edit',
                    success: function(data) {
                        console.log(data);
                        var post_url = "{{ url('admin/dynamic-report-investor') }}" + '/' + id;
                        $('#editReportForm').attr('action', post_url);
                        $('#edit_name').val(data.name);
                        $('#edit_description').val(data.description);
                        //table.ajax.reload(null, false);
                    }
                });
            });

            $('#apply').click(function(e) {
                table.ajax.reload();
            });

            $('.decimal').keypress(function(e) {
                var character = String.fromCharCode(e.keyCode)
                var newValue = this.value + character;
                if (isNaN(newValue) || parseFloat(newValue) * 100 % 1 > 0) {
                    e.preventDefault();
                    return false;
                }
            });
            $('.merchant_fields').hide();
            $('.field_keys').hide();
            $('#report_type').on('change', function() {
                if (this.value === '1') {
                    $('.merchant_fields').hide();
                    $('.field_keys').show();
                } else {
                    $('.merchant_fields').show();
                    $('.field_keys').hide();
                }

            });

            function initializeSelect2(selectElementObj) {
                selectElementObj.select2();
            }

            $(".select-to-select2").each(function() {
                initializeSelect2($(this));
            });

            $("#add-row").on("click", function() {

                  $filter_key=$(this).closest('div.row').find('.filter_keys').val();
                  $filter_operator= $(this).closest('div.row').find('.filter_operator').val();
                  $key_search= $(this).closest('div.row').find('.key_search').val();




                 if($filter_key &&  $filter_operator && $key_search)
                   {


                var htmlString = `<div class="row row_class"> 
                 <div> 
                    <div class="form-group col-md-3">

                   {{Form::select('filter_keys[]',$filter_keys,0,['class'=>'form-control js-investor-placeholder-multiple select-to-select2 filter_keys'])}}
                       
                    </div>
                    <div class="form-group col-md-3">
                        <select class='select-to-select2 filter_operator' name='filter_operator[]'>
                        </select>
                         <span id="error_message_for_filter_operator" class="text-danger error_message_for_filter_operator"></span> 
                    </div>
                    <div class="form-group col-md-3">
                          <select class='select-to-select2 key_search' name="key_search[]">
                            </select>
                          <span id="error_message_for_key_search" class="text-danger error_message_for_key_search"></span> 
                    </div>
                    <div class="form-group col-md-3">
                        <button type="button" id="remove-row" class="btn btn-danger"> - </button>
                    </div></div>
                    </div>`;

                
                        var domNodes = $($.parseHTML(htmlString))
                        $('#dynamic-fields').append(domNodes);

                domNodes.each(function() {
                    initializeSelect2($(this).find('select'));

                     $(document).on('click','#remove-row', function(){
                    $(this).closest('div.row').remove();
                })

    $('.filter_keys').change(function(){

        $filter_key=$(this).closest('div.row').find('.filter_keys').val();
        $filter_operator= $(this).closest('div.row_class').find('.filter_operator');
        $key_search= $(this).closest('div.row_class').find('.key_search');

       if($filter_key)
            {  
                condition_filter($filter_key,$filter_operator,$key_search);
            }
            else
            {

                 $(this).closest('div.row').find('.error_message_for_filter_key').html('');
                 $(this).closest('div.row').find('.error_message_for_filter_operator').html('');
                 $(this).closest('div.row').find('.error_message_for_key_search').html('');


            }

        });


        })

            }
            else

                { 
                 var text1='<font color="red">Please add search key</font>';    
                 var text2='<font color="red">Please Select Operator</font>';
                 var text3='<font color="red">Please Select Filter</font>';
                 $(this).closest('div.row').find('.error_message_for_filter_operator').html(text2);

                 $(this).closest('div.row').find('.error_message_for_key_search').html(text1);

                  $(this).closest('div.row').find('.error_message_for_filter_key').html(text3);

                }

            });

        });
    </script>

  <script type="text/javascript">
      
function condition_filter($filter_key,$filter_operator,$key_search)
    {
    
    $.ajax({
        type: "POST",
        url: "/admin/filter_operator",
        data:{'_token': _token,'filter_key':$filter_key},
        success: function(data){
            if(data.status==1){
                $filter_operator.empty();
                $filter_operator.append('<option value="">Select Operator</option>');
                operator = data.operator;
                for(var i = 0; i < operator.length; i++){

                 $filter_operator.append("<option>"+operator[i]+"</option>");

                }
                $key_search.empty();
                $key_search.append('<option value="">Select values</option>');
                search = data.key_search;

                $.each(search, function( key, value ) {
                   $key_search.append("<option value="+key+">"+value+"</option>");
                  
                });

              

            }


        }
    });


    }
 


  </script>


@stop

@section('styles')
    <link href="{{ asset('/css/bower_components/breadcrumbs/breadcrumb1.css?ver=5') }}" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('/css/optimized/branch_manager.css?ver=5') }}" rel="stylesheet" type="text/css" />

    <link href="{{ asset('/css/optimized/genarated_csv_pdf.css?ver=5') }}" rel="stylesheet" type="text/css" />

    




    <style>
        .select2-container--open .select2-dropdown--below {
            z-index: 9999;
        }

        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            margin: 0;
        }

    </style>


@stop
