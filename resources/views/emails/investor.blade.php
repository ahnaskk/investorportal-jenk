<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<style type="text/css">
		table td 		{ padding: 0; }
		body 			{ background-color: #f8f7ff; margin: 0;}
		.wrapper 		{ width: 100%; table-layout: fixed;}
		.wrapper-inner  { width: 100%; max-width: 600px; margin: 0 auto;	}

		/*--- Media Queries --*/
		@media screen and (max-width: 460px) {
			td.logo, 
			td.top-right 		{ width: 100%; display: inline-block; text-align: center !important;  }
			td.logo 			{ padding: 20px 0 20px !important; }
			td.logo img 		{ max-width: 80%; height: auto; }
			td.top-right    	{ padding: 5px 0 10px !important; }
			td.top-right p 		{ text-align: center !important; color: #585858 !important; margin: 10px 0 10px !important; }
			.content 			{ padding: 0 20px !important; }
			.footer-top 		{ padding: 0 20px !important; }
			.footer 			{ padding: 25px 20px !important; }
			.main-wrap 			{ padding: 0 !important }
			.name 				{ font-size: 18px !important; }
			.btn-wrap 			{ display: inline-block; width: 100%; text-align: center !important; padding: 5px 0 10px !important }
			.content 			{ padding: 10px 20px 25px !important }
			.spacer 			{ padding: 20px 0 0 !important; }
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
									<td style="background: #191e4a">
										<div class="wrapper-inner">
											<table width="100%" cellpadding="0" cellspacing="0" style="">
												<tr>
													<td class="logo" style=" font-size:0pt; line-height:0pt; padding: 25px 0 25px 0; background: #191e4a">
														<img src="{{URL::to('images/logo.png')}}" width="250" height="52" >
													</td>													
												</tr>
												<tr>
													<td class="spacer" style=" font-size: 0pt; line-height: 0pt; padding: 25px; background: #fff; border-radius: 10px 10px 0 0;"></td> </tr>
											</table>
										</div>
									</td>												
								</tr>

								<tr>
									<td >
										<div class="wrapper-inner">
											<table width="100%" cellpadding="0" cellspacing="0" style="">
												<tr>
													<td class="logo" style="font-size:22px; color: #70748e; line-height:0pt; padding: 0; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700; ">
														Dear {{$investor_name}},
													</td>													
												</tr>
												<tr>
													<td class="content"  style="font-size:32px; line-height: 44px; color: #1f244c; letter-spacing: -0.04em; padding: 25px 30px 15px 30px; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700;">
														Download our app and start tracking your investments today.
													</td>													
												</tr>
												<tr>
													<td>
														<table width="100%" cellpadding="0" cellspacing="0" style="">
															<tr>
																<td class="btn-wrap" style="font-size:0pt; line-height:0pt; padding: 12px 10px 30px 0; text-align: right; background: #fff">

																	
																	<a href="/coming_soon">
																	<img src="{{URL::to('images/google-btn.png')}}" width="180" height="59" >
																	</a>


																</td>
																<td class="btn-wrap" style="font-size:0pt; line-height:0pt; padding: 12px 0 30px 10px; text-align: left; background: #fff">
																	<a href="/coming_soon"><img src="{{URL::to('images/apple-store.png')}}" width="180" height="59" ></a>
																</td>													
															</tr>
														</table>
													</td>													
												</tr>
												<tr>
													<td class="content" style="font-size:18px; line-height: 30px; color: #63678a; letter-spacing: -0.04em; padding: 5px 30px 15px 30px; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700;">
														<p style="margin: 0; font-size: 20px; color: #1f244c; font-weight: bold; padding: 0">Login credentials</p>
														Username : {{$username}}<br>
														Password : {{$password}}
														<p style="font-size: 16px; color: #666; font-weight: normal; letter-spacing: 0">Use above credentials to login to the mobile app and web portal. <a href="{{URL::to('login')}}" style="color: #e85a44; font-weight: bold;" target="_blank">Click here</a> to login to web portal. </p>
													</td>													
												</tr>
												<tr>
													<td class="banner" style=" font-size:0pt; line-height:0pt;">
														<img src="{{URL::to('images/mockup-dark.jpg')}}" width="600" height="362" style="max-width: 100%; width: 100%; height: auto; " >
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
											<p style="margin: 7px 0 0; font-size: 13px;">Velocity Group USA, Inc. <br>

												290 Broadhollow Rd. Suite #220E Melville, NY 11747 <br>

												Office: (631) 201-0703 <br></p>
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