@extends('emails.layouts.header')
<?php
use Hashids\Hashids;
$hashids = new Hashids();
$merchant_id = $hashids->encode($merchant_id);
// $amount=base64_encode($amount);
$url=url("pm/$merchant_id/make-payment/$amount");
?>
@section('content')
<tr>
  <td style="padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;">
    Hope all is well. This is the Asset Recovery Department at Velocity Group USA. We noticed that there was an interruption to the delivery of receivables due to insufficient funds in your designated account today. Is everything OK with the business, {{ $merchant_name }}  ?<br>
    If this was a one-time issue, you can make a one time credit card payment here. <a href="{{ $url }}" target="_blank">Click Here</a>.<br>
    If not, please make sure that there is necessary funds available in your designated account today to deliver the receivables generated yesterday in addition to your next ACH debit.<br>
    In the event you have experienced a problem with your designated bank account or if there's an issue with the business, please contact me immediately so that we can discuss the necessary steps and work with you to resolve the same.<br>
    <br>
    Respectfully,<br>
    Lauren Esposito | Director of Collections<br>
    lesposito@curepayment.com<br>
    (631) 953-2625 Ext. 502<br>
    (800) 519-2234<br>
    Fax: (631) 953-2610<br>
    lesposito@curepayment.com <br>
    www.curepayment.com
  </td>
</tr>
@endsection

