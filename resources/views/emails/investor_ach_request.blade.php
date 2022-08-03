@extends('emails.layouts.header')
<?php
if($Creator!="Admin"){
    $url=url('admin/investors/portfolio/'.$investor_id);
} else {
    $url=url('investors/dashboard');
}
?>
<?php $InvestorLink='<a target="_blank" href="'.$url.'">'.$Investor.'</a>'; ?>
<?php $type=strtoupper(str_replace('_',' ',$type)); ?>
<?php $text_type=""; ?>
@section('content')
<tr>
    <td style="padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;">
        @if($Creator=="Admin")
        Hello {!! $InvestorLink !!}! <br>
        @else
        Hello Admin! <br>
        @endif
        <!-- same_day_debit/debit -->
        @if(in_Array($type,['DEBIT','SAME DAY DEBIT']))
        <?php $text_type = "Transfer to Velocity"; ?>
        <!-- same_day_credit/credit -->
        @elseif(in_Array($type,['CREDIT','SAME DAY CREDIT']))
        <?php $text_type = "Transfer to your Bank"; ?>
        @endif
        {{-- @if($Creator=="Admin")
        Admin has initiated a request for an Investor ACH of {{$type}}.
        @else
        Investor {!! $InvestorLink !!} has initiated a request for an ACH payment of {{$type}}.
        @endif  --}}
        @if($Creator=="Admin")
        Admin {{ $creator_name }} has initiated a request to {{$text_type}} as a part of ACH payment.
        @else
        Investor {!! $InvestorLink !!} has initiated a request to {{$text_type}} as a part of ACH payment.
        @endif
        <table width="100%" border="1" cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947; white-space: nowrap;">Amount requested:</th>
                    <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d; white-space: nowrap;">{{ $amount }}</td>
                </tr>
                <tr>
                    <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947; white-space: nowrap;">Date of request:</th>
                    <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d; white-space: nowrap;">{{ $date }}</td>
                </tr>
            </thead>
        </table>
    </td>
</tr>
@endsection
