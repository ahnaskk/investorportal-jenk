@extends('layouts.admin.admin_lte')
@section('content')
<div class="inner admin-dsh header-tp">
    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
    <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip"> Add Ach Request</div>
    </a>
</div>
{{ Breadcrumbs::render('transfer_to_velocity',$Investor) }}
<div class="col-md-12">
    <div class="box box-primary">
        {!! Form::open(['url'=>'admin/investors/achRequest/'.$Investor->id, 'method'=>'POST','id'=>'send_investor_form']) !!}
        <div class="box-body">
            <div class="row">
                <div class="form-group col-md-12">
                    <table with='100%'>
                        <tr>
                            <th>* Add Money to Velocity From the User's Bank Account</th>
                        </tr>
                        <tr>
                            <th>* Send Debit Request To Actum</th>
                        </tr>
                        <tr>
                            <th>* For Actum X (Amount) will Deduct From User's Bank Account </th>
                        </tr>
                        <tr>
                            <th>* For System X (Amount) will Add To Velocity </th>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-3">
                    {!! Form::text('investor_name',$Investor->name,['class'=>'form-control','readonly']) !!}
                    <label for="Investor">Investor <span class="validate_star">*</span></label>
                </div>
                <div class="form-group col-md-3">
                    {!! Form::number('amount','0',['class'=>'form-control','placeholder'=>'Enter the Required Amount','required','id'=> 'amount','min'=>'0.01','max'=>'100000','step'=>'0.01']) !!}
                    {!! Form::hidden('investor_id',$Investor->id) !!}
                    <label for="Amount">Amount <span class="validate_star">*</span></label>
                    <span id="invalid-inputName" />
                </div>
                <div class="form-group col-md-3">
                    <button type="submit" name="transaction_type" value="debit" class="btn btn-success">Send</button>
                </div>
                <div class="form-group col-md-2" hidden>
                    <button type="submit" name="transaction_type" value="same_day_debit" class="btn btn-info">Same Day Send</button>
                </div>
                <div class="form-group col-md-1">
                    @if(Permissions::isAllow('Investors','Edit'))
                    <a href="{{route('admin::investors::bank_details', ['id' => $Investor->id])}}" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-view"></i>Bank +</a>
                    @endif
                </div>
                <div class="form-group col-md-1">
                    @if(Permissions::isAllow('Investors','Edit'))
                    <a href="{{url('admin/investors/edit/'.$Investor->id)}}" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>
                    @endif
                </div>
            </div>
            <div class="row">
                <div class="box box-primary">
                    <?php foreach ($BankDetails as $key => $value): ?>
                        <div class="form-group col-md-4">
                            <div class="box-body">
                                <label class="flex-label">
                                    <div class="text-center">
                                        <?php $checked=""; ?>
                                        @if(!$key)
                                        <?php $checked="checked"; ?>
                                        @endif
                                        <input type="radio" required name="bank_id" {{$checked}} value="{{$value->id}}">
                                    </div>
                                    <table class="table">
                                        <tr>
                                            <th>Bank</th>
                                            <td>{{$value->name}}</td>
                                        </tr>
                                        <tr>
                                            <th>Account Holders</th>
                                            <td>{{$value->account_holder_name}}</td>
                                        </tr>
                                        <tr>
                                            <th>Account Number</th>
                                            <td>{{FFM::mask_cc($value->acc_number)}}</td>
                                        </tr>
                                        <tr>
                                            <th>Routing</th>
                                            <td>{{$value->routing}}</td>
                                        </tr>
                                        <tr hidden>
                                            <th>Bank Address</th>
                                            <td>{{$value->bank_address}}</td>
                                        </tr>
                                        <tr>
                                            <th>Bank Type </th>
                                            <td>{{$value->type}}</td>
                                        </tr>
                                    </table>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="row">
                @if(Session::has('message'))
                <p class="text-center alert alert-info">{{ Session::get('message') }}</p>
                @endif
                @if(Session::has('error'))
                <p class="text-center alert alert-danger">{{ Session::get('error') }}</p>
                @endif
            </div>
        </div>
        {!! Form::close() !!}
    </div>
</div>
@stop
@section('scripts')
<script src="{{ asset ("js/bootstrap-toggle.min.js") }}"></script>
<script src="{{ asset ('bower_components/bootstrap-tagsinput-latest/dist/bootstrap-tagsinput.min.js') }}" type="text/javascript"></script>
<script type="text/javascript">
$('#send_investor_form').submit(function() {
   return confirm("Are you sure you want to continue?");
  });
</script>
<script>
    $('#send_investor_form').validate({
        messages: {
            amount:{
                min: 'Enter a value grater than zero',
                step: 'Enter only two decimal points'
            }
        }
    })
</script>
@stop
@section('styles')
<link href="{{ asset('/css/optimized/create_new_investor.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset("css/bootstrap-toggle.min.css") }}" rel="stylesheet"/>
<link href="{{ asset('/css/optimized/create_merchant.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/optimized/Default_Settings.css?ver=5') }}" rel="stylesheet" type="text/css" />
@endsection
