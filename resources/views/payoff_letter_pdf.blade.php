<!DOCTYPE html>
<html>

<head>
    <title>PayOff Letter</title>
    <style>
        .container {
            width: 710px;
            /* margin: 0 auto; */
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .bordered-table td,
        .bordered-table th {
            border: 1px solid #ddd;
            padding: 5px 10px;
        }

        .bordered-table th {
            background: #f6f6f6;
        }

        p {
            line-height: 24px;
            margin: 0 0 25px;
        }

        .address span {
            display: block;
        }
    </style>
</head>

<body>
    <div class="container">

        <table class="main-table" style="font-family:Arial, Helvetica, sans-serif;">
            <tr>
                <td style="padding: 10px 0 10px;">
                    <table class="main-table">
                        <tr>
                            <td style="width:30%" valign="top">
                                <img src="images/velocity_logo_lg.png" width="300px">
                            </td>
                            <td style="text-align: right;">
                                <h5 style="font-size: 14px; margin: 0 0 2px; color: #111;">{{$business_name}}</h5>
                                <p class="address" style="font-size: 14px; margin: 0; color: #333;">
                                    <span>{{$business_address}}</span><span>{{$business_city}}</span>
                                    <span>{{$business_state}}</span><span>{{$business_zip}}<span></p>
                                <p style="font-size: 14px; margin: 0; color: #333;">ATTN : {{$full_name}}</p>
                            </td>
                        </tr>
                    </table>
                </td>

            </tr>
            <tr>
                <td style="font-size: 13px; color: #222; padding: 0 0 15px;">
                    <div style="background-color: #eee; border-radius: 5px; padding: 10px 20px;">
                        <table class="main-table" border="0">
                            <tr>
                                <td>
                                    {{$Currentdate}}
                                </td>
                                <td style="text-align: right;">
                                    PAYOFF BALANCE: <span>{{$loan_balance}}</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>

            <tr>
                <td style="font-size: 13px; color: #222;">
                    <p>Dear {{$first_name}},</p>
                    <p>Velocity Group USA is servicing your advance for Velocity Group USA Inc. The outstanding balance
                        of
                        your account is
                        {{$loan_balance}}. This balance does not include any transactions that are currently pending.
                        Once all
                        payments have
                        cleared the bank account we will terminate your UCC filing(if applicable) and we will reconcile
                        your
                        account and any over payment will be refunded within 10 business days. This balance is not
                        including
                        any other fees per our agreement which you are still subject to.</p>
                    <p style="margin: 0 0 10px;">If you choose to pay this balance today, please pay {{$loan_balance}}
                        to the
                        following bank account:</p>
                    <table class="bordered-table" style="margin: 0 0 15px;">
                        <tr>
                            <th>Beneficiary</th>
                            <th>Account Information</th>
                        </tr>
                        <tr>
                            <td rowspan="2">Velocity Group USA Inc <br>
                                290 Broadhollow Rd,Ste 220<br>
                                Melville, NY 11747

                            </td>
                            <td>Account Number : 6500822344</td>
                        </tr>

                        <tr>
                            <td>Routing Number : 221172186</td>
                        </tr>

                    </table>

                    <p>If you have any questions or concerns,please feel free to contact us at 866-790-3550 or
                        velocitymerchantsupport@curepayment.com</p>
                    <p>We greatly appreciate your business and look forward to assisting you with any funding needs you
                        or
                        your business may have in the future!</p>
                </td>
            </tr>

        </table>

    </div>
</body>

</html>
