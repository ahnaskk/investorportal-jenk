@extends('layouts.admin.admin_lte')
@section('content')
<div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Payment Report</div>     
      </a>
      
  </div>
    <div class="col-md-12">

 <div class="box">
        <div class="box-body">

        	

<div class="form-group grid">
        <div class="filter-group-wrap" >
            <div class="filter-group grid" >
                 {{Form::open(['route'=>'admin::investors::defaultreportdownload'])}}
                <div class="col-md-12 mrchntVwDetails">                    
                 <div class="row">
                     <div class="col-md-4 report_rate">
                     <div class="input-group">
                      <div class="input-group-text">
                           <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                          </div>

                             {{Form::select('merchants[]',$merchants,1,['class'=>'form-control','id'=>'merchants','multiple'=>'multiple'])}}

                            </div>
                            <span class="help-block">Merchants</span>
                        </div> 
                        <div class="col-md-4 report_rate">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                                </div>

                                  {{Form::text('payment_date1',null,['class'=>'form-control datepicker','id'=>'payment_date1', 'autocomplete'=>'off'])}}
                                  <input type="hidden" name="payment_date" id="payment_date" class="date_parse">

                            </div>
                            <span class="help-block">Payment Date </span>
                        </div>
                        <div class="col-md-4 report-input">
                      <div class="input-group">
                        <div class="input-group-text">
                          <span class="fa fa-credit-card" aria-hidden="true"></span>
                        </div>
                        {{ Form::select('payment_type',[''=>'Select Payment Type','credit'=>'Credit','debit'=>'Debit'],'',['class'=>'form-control','id'=>'payment_type','data-placeholder'=>'Select Payment Type']) }}    

                      </div>
                      <span class="help-block">Payment Type</span>
                    </div> 
        </div>
</div>

  <div class="col-md-12 mrchntVwDetails">
        <div class="row">
        	 <div class="col-md-4 report_rate">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                                </div>

                                  {{Form::select('investors[]',$investors,1,['class'=>'form-control','id'=>'investors','multiple'=>'multiple'])}}

                            </div>
                            <span class="help-block">Investors </span>
                        </div>
            <div class="btn-box grid">
                                <!--<div class="pull-right" style="padding-bottom: 15px">

                                    {{Form::submit('Download report',['class'=>'btn btn-primary','id'=>'form_filter'])}}

                                </div>-->
                <div class="input-group col-md-4">
                    <input type="button" value="Apply Filter" class="btn btn-success" id="date_filter"
                                           name="student_dob">

                               

                                      <!-- {{Form::submit('download',['class'=>'btn btn-primary','id'=>'form_filter'])}} -->

                </div>
            </div>
        </div>
     </div>
                           
 </div>
    {{Form::close()}}
 </div>

</div>





   <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">

                <div class="row">
                    <div class="col-sm-12 grid table-responsive">

                            {!! $tableBuilder->table(['class' => 'table table-bordered'], true) !!}
                       
                      <!--  <div class="blockCust pull-right" style="padding-bottom: 15px">

                            {{Form::submit('Download report',['class'=>'btn btn-primary','id'=>'form_filter'])}}

                        </div>-->
                    
                    </div>
                </div>
            </div>
        </div>
        <!-- /.box-body -->
    </div>
</div>
@stop
@section('scripts')
    <script src="{{ asset ('bower_components/datatables.net/js/jquery.dataTables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset ('bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('select2/js/select2.full.min.js') }}"></script>
        {!! $tableBuilder->scripts() !!}
    <script type="text/javascript">
        var table = window.LaravelDataTables["dataTableBuilder"];
        $(document).ready(function () {
        $('#date_filter').click(function (e) {
                e.preventDefault();
                table.draw();
            });
               // date validate for bill
    $('#billFilter').validate({ // initialize the plugin
        errorClass: 'errors',
        rules: {
           date_start: {
               // required: true,
                date: true,
            },
        },
        messages: {
        date_start: { required :"Enter valid date"} ,
    },
    });

     $(".accept_digit_only").keypress(function (evt) {
     
     var theEvent = evt || window.event;
          var key = theEvent.keyCode || theEvent.which;
          key = String.fromCharCode(key);
          if (key.length == 0) return;
          var regex = /^[0-9.,\b]+$/;
          if (!regex.test(key)) {
              theEvent.returnValue = false;
              if (theEvent.preventDefault) theEvent.preventDefault();
          } 
     
});

       $('#dataTableBuilder tbody').on('click', 'td.details-control ', function () {
            var tr = $(this).closest('tr');
            var row = table.row(tr);

            if (row.child.isShown()) {
              row.child.hide();
              tr.removeClass('shown');
            }
            else {
              row.child(format(row.data())).show();
              tr.addClass('shown');
            }

          });


         //var Url="{{ URL::to('admin/investors/transactions/') }}";
         function format(obj) {

            var investorTable = $('<table class="table dataTable no-footer" cellpadding="0" cellspacing="0" border="0" style=""> <tr class="text-danger"><td class="partic"><b>Investor</b></td><td><b>Participant share</b></td><td class="partic"><b>Management Fee</b></td><td><b>Syndication Fee</b></td></tr></table>'); //<td>Action</td>

            var investor = /*JSON.parse*/((obj.investor));
            $.each(investor, function (key, val) {

             //  var participants = $();
             //  $.each(val.investor_name , function(inpart , outpart){

             //   participant = $((outpart.link).replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g,''));
             //   participant.popover({
             //   html : true ,
             //    placement : 'right',
             //    container : 'body',
             //    width : "100", 
             //    content: function(){
             //      return outpart.info.replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g,'');
             //    }
             //  });

             //   participants = participants.add(participant);
             //   participants = participants.add($('<p style="display: inline">&nbsp;&nbsp;</p>'));

             // });
            
             var investorRow = $('<tr>' +
                  '<td>' + val.investor + '</td>' +
                  '<td>' + val.participant_share + '</td>' +
                  '<td>' + val.mgmnt_fee + '</td>' +
                  '<td>' + val.syndication_fee + '</td></tr>');

              //investorRow.children().first().append(participants);

              investorTable.append(investorRow);

            });
            return  investorTable;

          }









var URL_account= "{{ URL::to('admin/bills/accountSelect') }}";


$('#account_notttttttttt').select2({
        'placeholder': 'Select Account',
        ajax: {
            url: URL_account,
            dataType: 'json',
            data: function (params) {
                return {
                    q: params.term, // search term
                    page: params.page,
                    investorId: $("#investors").select2('val')
                };
            },
            processResults: function (data) {
                return {
                    results: $.map(data.items, function (item) {
                        return {
                            text: item.account_name,
                            slug: item.account_name,
                            id: item.id
                        }
                    })
                };
            }
        }
    }).change(function () {

         //$('#account_no').val(null).trigger('change.select2');
    });




    });
    </script>
@stop
@section('styles')
     <link href="{{ asset('/css/optimized/bills.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop