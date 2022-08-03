<?php $card_number = substr($card_number, -4); ?>
<style type="text/css">
  table td { padding: 0; }
</style>

<body>

<table width="100%" border="0" cellspacing="0" cellpadding="0" style="background: #eaf0f2" align="center">
  <tbody>

  <tr>
    <td class="main-wrap-top" align="center" valign="top" style=" background: #fa5440; padding: 35px 0 0;">
      <table width="650" border="0" align="center" cellspacing="0" cellpadding="0" class="mobile-shell">
        <tr>
          <td style="font-size:0pt; line-height:0pt; text-align:center; padding: 25px 0 0;">

          </td>
        </tr>

        <td class="logo" style="padding: 35px 50px 30px; font-size:0pt; line-height:0pt; text-align:center; background: #fff;">
          <img src="{{URL::to('images/logo.png')}}" width="170"  border="0" alt="">
        </td>
      </table>
    </td>
  </tr>

  <tr>
    <td class="main-wrap" align="center" valign="top" style=" background: #eaf0f2; padding: 0;">
      <table width="650" border="0" align="center" cellspacing="0" cellpadding="0" class="mobile-shell" style="background: #fff; ">
        <tbody><tr>
          <td class="td" align="center" style="width:650px; min-width:650px; font-size:0pt; line-height:0pt; background: #eaf0f2; padding:0; margin:0; font-weight:normal; padding: 0;">
            <table width="100%" border="0" align="center" cellspacing="0" cellpadding="0" style="border-radius: 5px;">
              <tbody>

              <tr>
                <td style="padding: 0 0 50px;">
                  <!-- Header -->
                  <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tbody>


                    <tr>
                      <td style=" padding: 0 50px 10px; background: #fff; ">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #FCFDFE; border: 1px solid #f3f6f7;">
                          <tr>
                            <td style=" padding: 20px 50px 0; background: #FCFDFE; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:center; font-size: 15px; font-weight: bold; color: #888896;">
                              Hello {{$merchant_name}},<br>
                            </td>
                          </tr>

                          <tr>
                            <td style="padding:10px 50px 20px; background: #FCFDFE; font-size:16spx; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 24px; text-align:center; color: #605f73;">
                              {{--Recipt from Velocity Group USA Inc
                              <p style="font-size: 14px; color: #9999a7; margin: 0;">Recipt #1044-8067</p>--}}
                              @if($mail_to == 'admin')
                                We have just received a Credit Card payment (Card Number ** ** {{$card_number}}) against the interruption to the delivery of receivables from <a href="{{ url('/admin/merchants/view/'.$merchant_id) }}">{{$merchant_name}}</a>. The amount paid was {{$amount}} on {{$date}}.
                                @else
                              @if($wallet_amount)
                                This is the Accounting Department at Velocity Group USA. We have just received a Credit Card Payment (Card Number ** ** {{$card_number}}) for adding fund to your wallet. The amount paid was {{$amount}} (inclusive a processing fee of 3.75%) on {{$date}}. Your wallet has been added with {{$actual_amount}} and at present stands at {{$wallet_amount}} .
                              @else
                                This is the Asset Recovery Department at Velocity Group USA. We have just received a Credit Card payment (Card Number ** ** {{$card_number}}) against the interruption to the delivery of receivables from your designated account. The amount paid was {{$amount}} on {{$date}}.
                              @endif
                              @endif
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>


                    <tr>
                      <td style="padding:15px 50px 0; background: #fff; font-size:18px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:center; color: #7a7a7a;">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                          <tr>
                            <td style=" text-align:center;  padding: 5px 0; font-size: 14px; color: #999; text-transform: uppercase;">
                              Amount Paid
                            </td>
                            <td style=" text-align:center;  padding: 5px 0; font-size: 14px; color: #999; text-transform: uppercase;">
                              Date Paid
                            </td>
                            <td style=" text-align:center;  padding: 5px 0; font-size: 14px; color: #999; text-transform: uppercase;">
                              Payment Method
                            </td>
                          </tr>
                          <tr>
                            <td style="padding: 5px 0; font-size: 16px; color: #444; text-align: center;">
                              {{$amount}}
                            </td>
                            <td style="padding: 5px 0; font-size: 16px; color: #444; text-align: center;">
                              {{$date}}

                            </td>
                            <td style="padding: 5px 0; font-size: 16px; color: #444; text-align: center;">
                              Visa - {{$card_number}}
                            </td>
                          </tr>
                        </table>

                      </td>
                    </tr>



                    <tr>
                      <td style="padding:30px 50px 0; background: #fff; font-size:18px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #7a7a7a;">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                          <tr>
                            <td style="padding: 15px 30px; background: #F7F9FC;">
                              <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                  <td style="padding: 10px 0; color: #88898c; background: #F7F9FC; border-bottom: 1px solid #E6EBF1;">
                                    Payment to Velocity
                                  </td>
                                  <td style="padding: 10px 0; color: #88898c; background: #F7F9FC; text-align: right; border-bottom: 1px solid #E6EBF1;">
                                    {{$amount}}
                                  </td>
                                </tr>
                                <tr>
                                  <td style="padding: 10px 0; color: #21293e; font-weight: bold; background: #F7F9FC;">
                                    Amount Paid
                                  </td>
                                  <td style="padding: 10px 0;color: #21293e;  font-weight: bold; background: #F7F9FC; text-align: right;">
                                    {{$amount}}
                                  </td>
                                </tr>
                              </table>
                            </td>
                          </tr>

                        </table>

                      </td>
                    </tr>


                    <tr>
                      <td style="padding:30px 50px 0; background: #fff; font-size:15px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 24px; text-align:left; color: #605f73; ">
                        <p style="    border-top: 1px solid #f3f6f7;
													border-bottom: 1px solid #f3f6f7;
													padding: 25px 0;
												">
                          If you have any questions, contact us at <a href="mailto:info@vgusa.com" style="color: #1d64c5;">info@vgusa.com</a> or call at  <br><span style="color: #1d64c5;"> 631-201-0703</span>
                        </p>
                      </td>
                    </tr>

                    <!-- content end here  -->


                    <tr>
                      <td style="padding: 30px 50px 60px; background: #fff; font-family: Arial, sans-serif, Helvetica, Verdana; font-size:15px; line-height: 26px; text-align:left; color: #555;">
                        Thanks,<br> <strong>Team Velocity</strong>
                      </td>
                    </tr>


                    <tr>
                      <td style="padding: 20px 40px 0 40px; font-family: Arial, sans-serif, Helvetica, Verdana; font-size:14px; line-height: 26px; text-align:center; color: #989898;">
                        www.vgusa.com
                      </td>
                    </tr>
                    <tr>
                      <td style="padding: 0 40px 15px 40px; font-family: Arial, sans-serif, Helvetica, Verdana; font-size:14px; line-height: 26px; text-align:center; color: #666; border-bottom: 1px solid #d3d7db;">
                        Velocity Funding Group,  290 Broadhollow,  Offie:(631)201-0703
                      </td>
                    </tr>

                    <tr>
                      <td style="padding: 15px 0 25px;">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                          <tr>
                            <td class="text" style="color:#7a7a7a; line-height: 22px; font-family: Arial, sans-serif, Helvetica, Verdana; font-size:13px; text-align:left;"><multiline><b>NOTICE:</b> This electronic transmission and any attachment are the confidential property of the sender, and the materials are privileged communications intended solely for the receipt, use, benefit, and information of the intended recipient indicated above. If you are not the intended recipient, you are hereby notified that any review, disclosure, copying, distribution, or the taking of any action in reliance on the contents of this electronic transmission is strictly prohibited, and may result in legal liability on your part. If you have received this email in error, please forward back to sender and destroy the electronic transmission.</multiline></td>
                          </tr>
                        </table>
                      </td>
                    </tr>

                    </tbody>
                  </table>
                </td>


              </tr>

              </tbody></table>
          </td>
        </tr>

        </tbody></table>
    </td>
  </tr>
  </tbody>
</table>

</body>
