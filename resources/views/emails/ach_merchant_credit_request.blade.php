@extends('emails.layouts.header')
@section('content')
<tr>
    <td style="padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;">

        Hello ! <br>
        Admin {{ $content['creator_name'] }} has initiated a request for <a href="{{ $content['merchant_url'] }}">{{ $content['merchant_name']}}</a> as a part of ACH Credit payment.
        <table width="100%" border="1" cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947; white-space: nowrap;">Amount requested:</th>
                    <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d; white-space: nowrap;">{{$content['payment_amount']}}</td>
                </tr>
                <tr>
                    <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947; white-space: nowrap;">Time of request:</th>
                    <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d; white-space: nowrap;">{{ $content['checked_time'] }}</td>
                </tr>
            </thead>
        </table>
    </td>
</tr>
@endsection
