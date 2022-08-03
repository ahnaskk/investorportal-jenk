<style type="text/css">
	table td { padding: 0; }
</style>

<body>

<table width="100%" border="0" cellspacing="0" cellpadding="0" style="background: #eaf0f2" align="center"> 
<tbody>

<tr>
		<td class="main-wrap-top" align="center" valign="top" style=" background: #181c3f; padding: 15px 0 0;"> 
			<table width="650" border="0" align="center" cellspacing="0" cellpadding="0" class="mobile-shell">
				<tr>
					<td class="logo" style="padding: 20px 50px; font-size:0pt; line-height:0pt; text-align:center; background: #181c3f;">
						<img src="{{URL::to('images/logo.png')}}" width="261" height="55" border="0" alt="">
					</td>
				</tr>
				<td class="logo" style="padding: 15px 50px; font-size:0pt; line-height:0pt; text-align:center; background: #fff; border-radius: 10px 10px 0 0;">
					@if($title)
					<span style=" padding: 10px 50px 10px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:center; font-size: 22px; font-weight: 700; color: #70748e;">
						{{ $title }}                                         
					</span>
					@endif
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
											
											<!-- content starts here  -->
                                            @yield('content')
											<!-- content end here  -->
											<tr>
												<td style="padding: 30px 50px 20px; background: #fff; font-family: Arial, sans-serif, Helvetica, Verdana; font-size:15px; line-height: 26px; text-align:left; color: #555;">
													Thanks,<br> <strong>Team Velocity</strong>
												</td>
											</tr>

											<tr>
												<td class="fluid-img" style="font-size:0pt; line-height:0pt; text-align:center; background: #fff; padding: 15px 0 20px 0;"><img src="{{URL::to('velocity-mail-images/logo-gray.png')}}" width="176" height="36" border="0" alt=""></td>
											</tr>

											<tr>
												<td style="padding: 20px 40px 0 40px; font-family: Arial, sans-serif, Helvetica, Verdana; font-size:14px; line-height: 26px; text-align:center; color: #989898;">
													www.vgusa.com
												</td>
											</tr>
											<tr>
												<td style="padding: 0 40px 15px 40px; font-family: Arial, sans-serif, Helvetica, Verdana; font-size:14px; line-height: 26px; text-align:center; color: #666; border-bottom: 1px solid #d3d7db;">
													Velocity Group USA, Inc. <br>

													290 Broadhollow Rd. Suite #220E Melville, NY 11747 <br>

													Office: (631) 201-0703 <br>
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





