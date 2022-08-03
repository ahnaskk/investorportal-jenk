@extends('emails.layouts.header')
<?php $url=url('admin/investors/portfolio/'.$investor_id); ?>
<?php $InvestorLink='<a target="_blank" href="'.$url.'">'.$Investor.'</a>'; ?>
@section('content')
<tr>
  <td style="padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;">
    Hello {!! $InvestorLink !!}! <br>
    The ACH {{$type}} request initiated was processed successfully.
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
        <tr hidden>
          <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947; white-space: nowrap;">Present Liquidity:</th>
          <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d; white-space: nowrap;">{{ $liquidity }}</td>
        </tr>
      </thead>
    </table>
  </td>
</tr>
@endsection
