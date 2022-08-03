<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style type="text/css">
        table td {
            padding: 0;
        }

        body {
            background-color: #f8f7ff;
            margin: 0;
        }

        .wrapper {
            width: 100%;
            table-layout: fixed;
        }
        .wrapper-parent {
            width: 100%;
            max-width: 650px;
            background-color: #fff;
            margin: 0 auto;
        }
        .wrapper-inner p {
            margin-top: 0;
            margin-bottom: 0;
        }

        .wrapper-inner {
            width: 100%;
            max-width: 650px;
            margin: 0 auto;
        }
        .table          { border-right: 1px solid #e2e0ea; border-top: 1px solid #e2e0ea; }
        .table td       { border-left: 1px solid #e2e0ea; border-bottom: 1px solid #e2e0ea; font-size: 14px; padding: 5px 10px }
        /*--- Media Queries --*/
        @media screen and (max-width: 460px) {

            td.logo,
            td.top-right {
                width: 100%;
                display: inline-block;
                text-align: center !important;
            }

            td.logo {
                padding: 20px 0 20px !important;
            }

            td.logo img {
                max-width: 80%;
                height: auto;
            }

            td.top-right {
                padding: 5px 0 10px !important;
            }

            td.top-right p {
                text-align: center !important;
                color: #585858 !important;
                margin: 10px 0 10px !important;
            }

            .content {
                padding: 0 20px !important;
            }

            .footer-top {
                padding: 0 20px !important;
            }

            .footer {
                padding: 25px 20px !important;
            }

            .btn-wrap {
                display: inline-block;
                width: 100%;
                text-align: center !important;
                padding: 5px 0 10px !important
            }

            .content {
                padding: 10px 20px 25px !important
            }
        }

    </style>

</head>

<body>

    <div class="wrapper">

        <table width="100%" border="0" align="center" cellspacing="0" cellpadding="0">
            <tbody>
                <tr>
                    <td class="main-wrap" style="padding: 0; text-align: center; ;">
                        <!-- Header -->
                        <table width="100%" style="background: #eaf0f2;" border="0" cellspacing="0" cellpadding="0" align="center">
                            <tbody>
                                <tr>
                                    <td style="background: #181c3f">
                                        {{-- <div class="wrapper-inner"> --}}
                                            <table width="650" align="center" cellpadding="0" cellspacing="0">
                                                <tbody>
                                                    <tr>
                                                        <td class="logo"
                                                            style="text-align:center;font-size:0pt; line-height:0pt; padding: 25px 0 25px 0; background: #181c3f">
                                                            <img src="{{ URL::to('images/logo.png') }}" width="250"
                                                                height="52">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td
                                                            style="font-size: 0pt; line-height: 0pt; padding: 25px; background: #fff; border-radius: 0;line-height: 0pt; padding: 25px; background: #fff; border-radius: 10px 10px 0 0;">
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        {{-- </div> --}}
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <table class="mobile-shell" width="650" cellspacing="0" cellpadding="0" align="center" border="0">
                                            <div class="wrapper-parent">
                                                <div class="wrapper-inner">
                                                    @if($data)
                                                    {!! $template !!}
                                                    @else
                                                    <tr>
                                                        <td style="padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;">
                                                            No Data
                                                    </td>
                                                    </tr>
                                                    @endif
                                                    <tr>
                                                        <td style="padding: 30px 50px 20px; background: #fff; font-family: Arial, sans-serif, Helvetica, Verdana; font-size:15px; line-height: 26px; text-align:left; color: #555;">
                                                            Thanks,<br> <strong>Team Velocity</strong>
                                                        </td>
                                                    </tr>
                                                </div> 
                                            </div>
                                        </table>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="footer" style="padding:35px 35px 35px; text-align: center; font-size: 14px; color: #626262; font-family: Arial, sans-serif, Helvetica, Verdana; ">
                                        <div class="wrapper-inner">
                                            <p style="padding: 20px 40px 0 40px; font-family: Arial, sans-serif, Helvetica, Verdana; font-size:14px; line-height: 26px; text-align:center; color: #989898;">
                                                www.vgusa.com
                                            </p>
                                            <p style="margin: 7px 0 0; font-size: 13px;">Velocity Group USA, Inc. <br>

                                                290 Broadhollow Rd. Suite #220E Melville, NY 11747 <br>

                                                Office: (631) 201-0703 <br></p>
                                        </div>
                                        <br><br>
                                        <div class="wrapper-inner" style="max-width:800px">
                                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                <tr>
                                                    <td class="text" style="color:#7a7a7a; line-height: 22px; font-family: Arial, sans-serif, Helvetica, Verdana; font-size:13px; text-align:left;"><multiline><b>NOTICE:</b> This electronic transmission and any attachment are the confidential property of the sender, and the materials are privileged communications intended solely for the receipt, use, benefit, and information of the intended recipient indicated above. If you are not the intended recipient, you are hereby notified that any review, disclosure, copying, distribution, or the taking of any action in reliance on the contents of this electronic transmission is strictly prohibited, and may result in legal liability on your part. If you have received this email in error, please forward back to sender and destroy the electronic transmission.</multiline></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>
