<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<style type="text/css">
		table td 		{ padding: 0; }
		body 			{ /*background-color: #f8f7ff; */margin: 0;}
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
			.btn-wrap 			{ display: inline-block; width: 100%; text-align: center !important; padding: 5px 0 10px !important }
			.content 			{ padding: 10px 20px 25px !important }
		}
		
	</style>

</head>


<body>

	<div class="wrapper">


	    	<table width="100%" border="0" align="center" cellspacing="0" cellpadding="0" >

	    	

				<tr>
					<td class="main-wrap" style="padding: 0; ">
						<!-- Header -->
						<table width="100%" border="0" cellspacing="0" cellpadding="0">
							<tbody>
								
								
								<tr>
									<td >
										<div class="wrapper-inner" >
											<table width="100%" cellpadding="0" cellspacing="0" style="">
												<tr>
									<td class="logo" style="    
									font-size: 22px;
									/*border-top: 3px solid #f2f1fb;*/
									color: #45485d;
									line-height: 30px;
									padding: 30px 0 0;
									background: #fff;
									font-family: Arial, sans-serif, Helvetica, Verdana;
									font-weight: 700;
									text-align: center;">
									Hi ,
									</td>													
								</tr>
												
												<tr>
													<td class="content" style="    
	font-size: 16px;
    line-height: 32px;
    color: #000;
    text-align: justify;
    letter-spacing: 0em;
    padding:20px 0 15px 0;
    background: #fff;
    font-family: Arial, sans-serif, Helvetica, Verdana;">


														We'd like to confirm that you enabled two-step verification on the account {{$email}}
The next time you log in with your email address and password, you'll need to enter a 6-digit code to access your account.
If you can't use your phone, you can enter the emergency recovery key you saved during setup. 
<!-- If you didn't save your recovery key or suspect somebody has seen it, go to your two-step verification page to create a new one.

If it wasn't you who set up two-step verification, let us know immediately. -->

														
													</td>													
												</tr>
												
											

											
												
												
											</table>
										</div>
									</td>												
								</tr>
								
								<tr>									
									<td class="footer" style="padding:0 35px 35px; font-size: 14px; color: #b1b0b9; font-family: Arial, sans-serif, Helvetica, Verdana; ">
										<div class="wrapper-inner">
										<!-- 	<a href="#" style="color: #b1b0b9; text-decoration: none;">Terms and Conditions</a>   |    <a href="#" style="color: #b1b0b9; text-decoration: none;">Privacy Policy</a> <br>	 -->	
											<p style="margin: 7px 0 0; font-size: 13px;text-align: center;">
												Velocity Group USA, Inc. <br>

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

