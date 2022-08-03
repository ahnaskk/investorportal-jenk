
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style type="text/css">
        table td        { padding: 0; }
        body            { background-color: #f8f7ff; margin: 0;}
        .wrapper        { width: 100%; table-layout: fixed;}
        .wrapper-inner  { width: 100%; max-width: 600px; margin: 0 auto;    }


        /*--- Media Queries --*/
        @media screen and (max-width: 460px) {
            td.logo, 
            td.top-right        { width: 100%; display: inline-block; text-align: center !important;  }
            td.logo             { padding: 20px 0 20px !important; }
            td.logo img         { max-width: 80%; height: auto; }
            td.top-right        { padding: 5px 0 10px !important; }
            td.top-right p      { text-align: center !important; color: #585858 !important; margin: 10px 0 10px !important; }
            .content            { padding: 0 20px !important; }
            .footer-top         { padding: 0 20px !important; }
            .footer             { padding: 25px 20px !important; }
            .btn-wrap           { display: inline-block; width: 100%; text-align: center !important; padding: 5px 0 10px !important }
            .content            { padding: 10px 20px 25px !important }
        }
        
    </style>

</head>


<body>

    <div class="wrapper">


            <table width="100%" border="0" align="center" cellspacing="0" cellpadding="0">
                <tr>
                    <td class="main-wrap" style="padding: 0; text-align: center; ;">
                        <!-- Header -->
                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                                <tr>
                                    <td style="background: #181c3f">
                                        <div class="wrapper-inner">
                                            <table width="100%" cellpadding="0" cellspacing="0" style="">
                                                <tr>
                                                    <td class="logo" style=" font-size:0pt; line-height:0pt; padding: 25px 0 25px 0; background: #181c3f">
                                                        <img src="{{URL::to('images/logo.png')}}" width="250" height="52" >
                                                    </td>                                                   
                                                </tr>
                                                <tr>
                                                    <td style="font-size: 0pt; line-height: 0pt; padding: 25px; background: #fff; border-radius: 10px 10px 0 0;line-height: 0pt; padding: 25px; background: #fff; border-radius: 10px 10px 0 0;"></td> </tr>
                                            </table>
                                        </div>
                                    </td>                                               
                                </tr>

                                <tr>
                                    <td >
                                        <div class="wrapper-inner">
                                            <table width="100%" cellpadding="0" cellspacing="0" style="">
                                                <tr>
                                                    <td class="logo" style="font-size:22px; color: #70748e; line-height:24px; padding: 0; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700; ">
                                                        Hello!  

                                                        
                                                    </td>                                                   
                                                </tr>

                                                <tr>
                                                    <td style="font-size:22px; color: #c54e3f; line-height:24px; padding: 25px 0 0; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700; ">
                                                        Reset Password Notification!
                                                    </td>                                                   
                                                </tr>

                                                
                                                <tr>
                                                    <td class="content" style="font-size:17px;  letter-spacing: -0.04em; padding: 10px 30px 0 30px; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; ">
                                                        <div style="background: #fdfdff; border-radius: 8px; border: 1px solid #eeedf5; color: #535777;line-height: 30px; padding: 10px 25px 40px;">
                                                            <p>You are receiving this email because we received a password reset request for your account.</p>
                                                            <a href="{{$actionUrl}}" style="padding: 2px 45px; margin:10px 0 25px; display: inline-block; width: auto; text-decoration: none; border-radius: 35px; color: #fff; background: #d33724; font-size: 16px; font-weight: bold; ">Reset Password</a>
                                                            <p style="margin: 0;">This password reset link will expire in 60 minutes.</p>
                                                            <p style="margin: 0;">If you did not request a password reset, no further action is required.</p>
                                                        </div>                                      
                                                    </td>                                                   
                                                </tr>   
                                                <tr>
                                                    <td class="content" style="font-size:13px; line-height: 20px; color: #1f244c; letter-spacing: -0.04em; padding: 10px 30px 0 30px; text-align: left; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; ">
                                                        <p>If you’re having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:
                                                        </p>
                                                        <a style="word-break: break-all;" href="{{$actionUrl}}">{{$actionUrl}}</a>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 35px 30px 30px; background: #fff; font-family: Arial, sans-serif, Helvetica, Verdana; font-size:15px; line-height: 26px; text-align:left; color: #a4a4b9;">
                                                        Thanks,<br> <strong style="color: #7479a0">Investor Portal</strong>
                                                    </td>
                                                </tr>                                               
                                                
                                            </table>
                                        </div>
                                    </td>                                               
                                </tr>
                                
                                <tr>                                    
                                    <td class="footer" style="padding:35px 35px 35px; background: #e1e1eb
                                    ; text-align: center; font-size: 14px; color: #626262; font-family: Arial, sans-serif, Helvetica, Verdana; ">
                                        <div class="wrapper-inner">
                                            {{--<a href="#" style="color: #626262; text-decoration: none;">Terms and Conditions</a>   |    <a href="#" style="color: #626262; text-decoration: none;">Privacy Policy</a> <br>--}}
                                            <p style="margin: 7px 0 0; font-size: 13px;">
                                                Velocity Group USA, Inc. <br>

                                                290 Broadhollow Rd. Suite #220E Melville, NY 11747 <br>

                                                Office: (631) 201-0703 <br>
                                            </p>
                                        </div>
                                    </td>                                           
                                </tr>

                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>    


            
    
    </div>
</body>
</html>