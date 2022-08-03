<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>TCP FSP MPA (1)</title>
	<meta name="generator" content="BCL easyConverter SDK 5.0.241">
	<meta name="title" content="MPA_v10120191114">
	<style type="text/css">
		* {
			box-sizing: border-box;
			font-family: Arial, Helvetica, sans-serif;
		}
		.page-br-b{
			page-break-before: always;
		}
		/*
		.page-br-a{
			page-break-after: always;
		} */
		body {
			margin-top: 0px;
			margin-left: 0px;
			margin: 0;
			padding: 0;
		}
		html{
			margin: 0;
			padding: 0;
		}
		body {
			background: #fff;
			letter-spacing: -0.03em;
		}

		table {
			width: 100%;
			font: 11px 'Arial';
			line-height: 13px;
		}

		.wrap-box {
			padding: 0 25px 0;
		}

		.wrapper {
			margin: 0 auto;
			/*width: 816px;*/
			width: 100%;
			min-width: 100%;
			background: #fff;
		}

		.header {
			background: #3ab54a;
			padding: 0px 25px;
			box-sizing: border-box;
		}

		.top-form {
			border: 1px solid #b4b4b5;
			border-radius: 4px;
			background: #ebebec;
		}

		.top-form td {
			font: 10px 'Arial';
			line-height: 14px;
		}

		.top-form>table>td {
			border-bottom: 0;
		}

		.top-form td span {
			display: block;
		}

		.top-form td:last-child {
			border-bottom: 0
		}

		.form-control {
			width: 100%;
			padding: 4px 5px;
			border: 1px solid #d6d9e4;
			margin: 2px 0 0;
			background: #f1f4ff;
			border-radius: 3px;
			font: bold 10px 'Arial';
			display: block;
		}

		.vertical-text {
			writing-mode: vertical-rl;
			text-orientation: mixed;
			height: 50px;
			text-align: center;
		}

		.top-form .form-control {
			background: #e5e8f3;
			padding: 1px 5px;
			height: 13px;
			border: none;
			border-radius: 0;
			border-color: transparent;
		}

		.section-table span.name {
			font-size: 12px;
		}

		.doller {
			color: #555;
			margin: 0 2px 0 0;
			display: inline-block;
		}

		/* Popup box BEGIN */
		.hover_bkgr_fricc {
			background: rgba(0, 0, 0, .4);
			cursor: pointer;
			display: none;
			height: 100%;
			position: fixed;
			text-align: center;
			top: 0;
			width: 100%;
			z-index: 10000;
			left: 0;
		}

		.hover_bkgr_fricc .helper {
			display: inline-block;
			height: 100%;
			vertical-align: middle;
		}

		.hover_bkgr_fricc>div {
			background-color: #fff;
			box-shadow: 10px 10px 60px #555;
			display: inline-block;
			height: auto;
			max-width: 551px;
			min-height: 100px;
			vertical-align: middle;
			width: 60%;
			position: relative;
			border-radius: 8px;
			padding: 15px 5%;
		}

		.popupCloseButton {
			background-color: #fff;
			border: 3px solid #999;
			border-radius: 50px;
			cursor: pointer;
			display: inline-block;
			font-family: arial;
			font-weight: bold;
			position: absolute;
			top: -20px;
			right: -20px;
			font-size: 25px;
			line-height: 30px;
			width: 30px;
			height: 30px;
			text-align: center;
		}

		.popupCloseButton:hover {
			background-color: #ccc;
		}

		.trigger_popup_fricc {
			cursor: pointer;
			font-size: 20px;
			margin: 20px;
			display: inline-block;
			font-weight: bold;
		}

		table td input {
			margin-top: 20px;
			display: block;
		}
	</style>
</head>
<body>
	<div class="wrapper">
		<div class="header">
			<table cellpadding="0" cellspacing="0" class="t0" style=" font: bold 10px 'Arial'; color: #ffffff; line-height: 12px;vertical-align:middle;padding:0;margin:0;">
				<tr style="vertical-align: middle;">
					<td rowspan="3" class="tr0 td0" style="vertical-align: middle;">
						<div class="logo"><img src="images/logo.png" alt="" width="200px"></div>
					</td>
					<td class="tr0 td0" style="padding: 30px 0 0 0;vertical-align:middle;">
						<p class="p0 ft2" style="margin:0;padding:0">P: <nobr>1-844-TEK-PYMT</nobr>
						</p>
					</td>
					<td class="tr0 td1" style="padding: 30px 0 0 0;vertical-align: middle;">
						<p class="p1 ft2" style="margin: 0;padding:0">F: <nobr>(201)944-6257</nobr>
						</p>
					</td>
					<td class="tr0 td2" style="padding: 30px 0 0 0;vertical-align: middle;">
						<p class="p1 ft2" style="margin: 0;padding:0">W: tekcardpayments.com</p>
					</td>
				</tr>
				<tr>
					<td class="tr1 td0">
						<p class="p0 ft2">E: info@tekcardpayments.com</p>
					</td>
					<td colspan="2" class="tr1 td3">
						<p class="p1 ft2">A: 160 Chubb Ave Suite #203, Lyndhurst, NJ 07071</p>
					</td>
				</tr>
				<tr>
					<td class="tr2 td0">
						<p class="p1 ft3">&nbsp;</p>
					</td>
					<td class="tr2 td1">
						<p class="p1 ft3">&nbsp;</p>
					</td>
					<td class="tr2 td2">
						<p class="p1 ft3">&nbsp;</p>
					</td>
				</tr>
			</table>
		</div>
		<p class="p2 ft4" style="margin: 15px 0 10px; text-align: left; padding-left: 252px; font: bold 20px 'Arial';font-family:Arial, Helvetica, sans-serif;line-height: 28px;">Merchant Processing Agreement</p>
		<div class="wrap-box">
			<div class="top-form">
				<table width="100%" cellpadding="0" cellspacing="0" style="color: #555;">
					<tbody>
						<tr>
							<td style="border-right: 1px solid #b5b5b7; width: 25px; line-height: 8px;">
								<p class="vertical-text">Offic Use Only</p>
							</td>
							<td>
								<table width="100%" cellpadding="0" cellspacing="0" style="color: #555;">
									<tr>
										<td style="border-right: 1px solid #b5b5b7; border-bottom: 1px solid #b5b5b7;">
											<span style="padding: 2px 5px;">Agent Name</span>
											<input type="text" class="form-control" style="border-radius: 0; margin: 0; border: 0" name="" value="Frank Dewey Ronan">
										</td>
									</tr>
									<tr>
										<td style="border-right: 1px solid #b5b5b7; width: 200px;">
											<span style="padding: 2px 5px;">Agent Code</span>
											<input type="text" class="form-control" style="border-radius: 0; margin: 0; border: 0" name="" value="AHJSD7582">
										</td>
									</tr>
								</table>
							</td>
							<td style="border-right: 1px solid #b5b5b7; width: 25px; line-height: 8px;">
								<p class="vertical-text">Offic Use Only</p>
							</td>
							<td>
								<table width="100%" cellpadding="0" cellspacing="0" style="color: #555;">
									<tr>
										<td colspan="3" style="border-bottom: 1px solid #b5b5b7;">
											<span style="padding: 2px 5px;">Merchant #</span>
											<input type="text" class="form-control" style="border-radius: 0; margin: 0; border: 0" name="" value="Ferris Allen">
										</td>
									</tr>
									<tr>
										<td style="border-right: 1px solid #b5b5b7;z-index:12;position:relative;">
											<span style="padding: 2px 5px;">SIC Code</span>
											<input type="text" class="form-control" style="border-radius: 0; margin: 0; border: 0;z-index:-1;display:block" name="" value="DAK9796S68A8SC">
										</td>
										<td style="border-right: 1px solid #b5b5b7;">
											<span style="padding: 2px 5px;">Fico Score</span>
											<input type="text" class="form-control" style="border-radius: 0; margin: 0; border: 0" name="" value="90">
										</td>
										<td>
											<span style="padding: 2px 5px;">Analyst</span>
											<input type="text" class="form-control" style="border-radius: 0; margin: 0; border: 0" name="" value="Fiona Wise">
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<style type="text/css">
			.section span,.section p,table *{
				font-family:Arial, Helvetica, sans-serif;
			}
				.md-input{
					width: 20px;
				}
				.section {
					border: 1px solid #cccccd;
					margin: 20px 0 0;
					padding: 0 0 0
				}
				.heading {
					background: #3ab54a;
					color: #fff;
					margin: 0;
					font: bold 10px 'Arial';
					padding: 4px 16px;
					font-family:Arial, Helvetica, sans-serif
				}

				.section-table {
					font: 12px 'Arial';
					padding: 0 5px
				}

				.section-table span {
					font: 10px 'Arial';
					margin: 7px 0 3px;
					display: inline-block;
				}

				.section-table .table td {
					padding: 0 3px
				}

				.section-table .table table td {
					padding: 0 3px
				}

				.section-table .form-control {
					margin: 0 0 5px
				}

				.pad-0 {
					padding: 0 !important
				}

				.checkbox-box {
					font-size: 11px;
				}

				.checkbox-box .checkbox {
					margin: 0 3px 5px 0;
					position: relative;
					top: 3px;
				}

				.checkbox-box .checkbox label {
					padding: 0
				}

				.no-border td {
					border: 0;
				}

				.section .form-control {
					height: 20px;
					margin-top: 5px;
				}

				span.md {
					font-size: 8px;
				}
				.table tr td span{
					font-family:Arial, Helvetica, sans-serif;
				}
			</style>
			<!-- Section -->
			<div class="section">
				<div class="heading">1. Merchant Information</div>
				<div class="section-table">
					<table cellpadding="0" cellspacing="0" class="table">
						<tr>
							<td width="300" style="width: 300px">
								<span style="font-family:Arial, Helvetica, sans-serif;">Account Name (Doing Business As)</span>
								<input type="text" class="form-control" name="" value="Chapman 711-2880 Nulla St. Mankato Mississippi">
							</td>
							<td width="50%" style="width: 300px">
								<span style="font-family:Arial, Helvetica, sans-serif;">Legal Name (If different from DBA)</span>
								<input type="text" class="form-control" name="" value="Chapman 711-2880 Nulla St. Mankato Mississippi">
							</td>
						</tr>
						<tr>
							<td width="50%">
								<span style="font-family:Arial, Helvetica, sans-serif;">DBA Address</span>
								<input type="text" class="form-control" name="" value="Chapman 711-2880 Nulla St. Mankato Mississippi">
							</td>
							<td width="50%">
								<span style="font-family:Arial, Helvetica, sans-serif;">Legal Address (If different from DBA)</span>
								<input type="text" class="form-control" name="" value="Chapman 711-2880 Nulla St. Mankato Mississippi">
							</td>
						</tr>
						<tr>
							<td width="50%" class="pad-0">
								<table cellpadding="0" cellspacing="0" class="table">
									<tr>
										<td width="33%">
											<span>City</span>
											<input type="text" class="form-control" name="" value="Manhattan">
										</td>
										<td width="33%">
											<span>State</span>
											<input type="text" class="form-control" name="" value="New York">
										</td>
										<td width="33%">
											<span>Zip Code</span>
											<input type="text" class="form-control" name="" value="10004">
										</td>
									</tr>
								</table>
							</td>
							<td width="50%" class="pad-0">
								<table cellpadding="0" cellspacing="0" class="table">
									<tr>
										<td width="33%">
											<span>City</span>
											<input type="text" class="form-control" name="" value="Manhattan">
										</td>
										<td width="33%">
											<span>State</span>
											<input type="text" class="form-control" name="" value="New York">
										</td>
										<td width="33%">
											<span>Zip Code</span>
											<input type="text" class="form-control" name="" value="10004">
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td width="50%" class="pad-0">
								<table cellpadding="0" cellspacing="0" class="table">
									<tr>
										<td width="65%">
											<span>Authorized Contact (Owner/Partner/Manager) </span>
											<input type="text" class="form-control" name="" value="+1-541-754-3010">
										</td>
										<td width="35%">
											<span>Telephone #</span>
											<input type="text" class="form-control" name="" value="+1-541-754-3010">
										</td>
									</tr>
								</table>
							</td>
							<td width="50%" class="pad-0">
								<table cellpadding="0" cellspacing="0" class="table">
									<tr>
										<td width="50%">
											<span>Telephone #</span>
											<input type="text" class="form-control" name="" value="+1-541-754-3010">
										</td>
										<td width="50%">
											<span>Fax #</span>
											<input type="text" class="form-control" name="" value="+1 323 555 1234">
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td width="50%" class="pad-0" colspan="2">
								<table cellpadding="0" cellspacing="0" class="table" style="vertical-align: top;">
									<tr style="vertical-align: top;">
										<td width="23%">
											<span>Website</span>
											<input type="text" class="form-control" name="" value="http://dribbble.com/">
										</td>
										<td width="54%" class="pad-0">
											<span>Type of ownership (Select one)</span>
											<table cellpadding="0" cellspacing="0" border="0">
												<tr>
													<td>
														<label class="checkbox-box" style="display:inline">
															<input type="checkbox" checked class="checkbox" style="display: inline;"><span style="display: inline-block;margin:0 0 10px 10px;line-height:18px;">Sole Proprietor</span>
														</label>
													</td>
													<td>
														<label class="checkbox-box" style="display:inline">
															<input type="checkbox" class="checkbox" style="display: inline;"><span style="display: inline-block;margin:0 0 10px 10px;line-height:18px;">Partnership</span>
														</label>
													</td>
													<td>
														<label class="checkbox-box" style="display:inline">
															<input type="checkbox" class="checkbox" style="display: inline;"><span style="display: inline-block;margin:0 0 10px 10px;line-height:18px;">Corporation</span>
														</label>
													</td>
													<td>
														<label class="checkbox-box" style="display:inline">
															<input type="checkbox" class="checkbox" style="display: inline;"><span style="display: inline-block;margin:0 0 10px 10px;line-height:18px;">LLC</span>
														</label>
													</td>
												</tr>
												<tr>
													<td>
														<label class="checkbox-box" style="display:inline">
															<input type="checkbox" class="checkbox" style="display: inline;"><span style="display: inline-block;margin:0 0 10px 10px;line-height:18px;">Non-Profit</span>
														</label>
													</td>
													<td>
														<label class="checkbox-box" style="display:inline">
															<input type="checkbox" class="checkbox" style="display: inline;"><span style="display: inline-block;margin:0 0 10px 10px;line-height:18px;">Government</span>
														</label>
													</td>
													<td>
														<label class="checkbox-box" style="display:inline">
															<input type="checkbox" class="checkbox" style="display: inline;"><span style="display: inline-block;margin:0 0 10px 10px;line-height:18px;">Association</span>
														</label>
													</td>
													<td>
														&nbsp;
													</td>
												</tr>
											</table>
										</td>
										<td width="23%">
											<span>Federal Tax I.D. Number</span>
											<input type="text" class="form-control" name="" value="332-34-3679">
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</div>
				<table style="margin: 10px 0 0;">
					<tr>
						<td width="50%" class="pad-0" colspan="2" style="background: #ebebec;">
							<table cellpadding="0" cellspacing="0" class="table">
								<tr>
									<td width="50%" style="padding: 10px 6px 10px 12px">
										<span>Merchant E-mail Address (Agent E-mail address cannot be accepted)</span>
										<input type="text" class="form-control" name="" value="velocitygroup@host.com">
									</td>
									<td width="50%" style="padding: 0 12px 6px 6px">
											<input type="checkbox" class="checkbox" style="display: inline;" checked>
											Opt in for paperless statements
											<span style="font-size: 10px; color: #999;">(If selected, an email address is mandatory. By opting in, Merchant agrees that all statements and correspondence relating to Merchant account will be sent to the email address stated on the left.</span>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</div>
			<!-- /.Section -->
			<!-- Section -->
			<div class="section" >
				<div class="heading">2A. Business Profile</div>

				<div class="section-table pad-0">
					<table cellpadding="0" cellspacing="0" class="table" style="font-size:10px;">
						<tr>
							<td class="pad-0">
								<table cellpadding="0" cellspacing="0" class="table">
									<tr>

										<td class="pad-0" style="width:400px;">
											<table cellpadding="0" cellspacing="0" class="table table-borderd" id="bsProfile">
												<tr>
													<td width="50%" style="border-right: 1px solid #ddd; padding: 0 10px;">
														<span>Type of Merchandise/Services/Products sold</span>
														<input type="text" class="form-control" name="">
													</td>
												</tr>
												<tr>
													<td width="50%" style="border-right: 1px solid #ddd;padding: 0 10px;margin-bottom:20px;">
														<table cellpadding="0" cellspacing="0" class="table table-borderd">
															<tr>
																<td width="32%" style="padding-left: 0;">
																	<span class="md">Years in Business</span>
																	<input type="text" class="form-control" name="">
																</td>
																<td width="38%" style="">
																	<span class="md">Length of current ownership </span>
																	<input type="text" class="form-control" name="">
																</td>
																<td width="32%" style="padding-right: 0;">
																	<span class="md"># of locations</span>
																	<input type="text" class="form-control" name="">
																</td>
															</tr>
														</table>
													</td>
													<span style="background:#ddd;width:100%;height:1px;"></span>
												</tr>
											</table>
											<table cellpadding="0" cellspacing="0" class="table table-borderd">
												<tr>
													<td width="78%" style="border-right: 1px solid #ddd;">
														<table cellpadding="0" cellspacing="0" class="table table-borderd">
															<tr>
																<td width="20%">
																	<span class="md">Monthly Volume </span>
																	<input type="text" class="form-control" name="" value="60">
																</td>
																<td width="20%">
																	<span class="md">Annual Volume</span>
																	<input type="text" class="form-control" name="" value="20">
																</td>
																<td width="20%">
																	<span class="md">Average Ticket Amount</span>
																	<input type="text" class="form-control" name="" value="20">
																</td>
																<td width="20%">
																	<span class="md"># of locations</span>
																	<input type="text" class="form-control" name="">
																</td>
																<td width="20%" style="font-size: 7px; font-weight: normal; line-height: 9px;">
																	Merchant certifies that the average ticket size, highest ticket and sales volume indicated is accurate and acknowledges any variance to this information could
																	result in delayed and/or withheld settlement of funds and/or termination of merchant account
																</td>
															</tr>
														</table>
													</td>
												</tr>
											</table>
										</td>

										<td style="border-right: 1px solid #ddd;width:60px;max-width:60px;">
											<span style="margin-bottom: 15px;">Percent of Business</span>
											<!-- Issue -->
											<table class="no-border" style="border-collapse:seperate;min-width: 110px;border-spacing:0 3px;font-size: 10px; font-weight: normal;">
												<tr style="background: #eee; margin: 0 0 3px; padding: 5px 0;" width="100%">
													<td style="padding:10px 3px;width: 100px;border-bottom: 3px solid #fff; margin: 5px 0;vertical-align:middle;" width="100%">Card swiped</td>
													<td style="padding:10px 3px;border-bottom: 3px solid #fff; margin: 5px 0;vertical-align:middle;" ><input type="text" class="form-percentage" style="width: 20px;height:15px;margin:auto 0;" value="20"> </td>
													<td style="padding:10px 3px;border-bottom: 3px solid #fff; margin: 5px 0;vertical-align:middle;">%</td>
												</tr>
												<tr style="background: #eee; margin: 0 0 3px; padding: 5px 0;" width="100%">
													<td style="padding:10px 3px;width: 100px;border-bottom: 3px solid #fff; margin: 5px 0;">Internet / eCommerce</td>
													<td style="padding:10px 3px;border-bottom: 3px solid #fff; margin: 5px 0;"><input type="text" class="form-percentage" style="width: 20px;height:15px;margin:auto 0;" value="70"> </td>
													<td style="padding:10px 3px;border-bottom: 3px solid #fff; margin: 5px 0;">%</td>
												</tr>
												<tr style="background: #eee; " width="100%">
													<td style="padding:10px 3px;width: 100px; padding: 5px 0; border-bottom: 3px solid #fff;">Card not present</td>
													<td style="padding:10px 3px;border-bottom: 3px solid #fff; margin: 5px 0;"><input type="text" class="form-percentage" style="width: 20px;height:15px;margin:auto 0;" value="10"> </td>
													<td style="padding:10px 3px;border-bottom: 3px solid #fff; margin: 5px 0;">%</td>
												</tr>
												<tr style="background: #eee; margin: 0 0 3px; padding: 5px 0;" width="100%">
													<td style="padding:10px 3px;width: 100px;border-bottom: 3px solid #fff; margin: 5px 0;"">Total <span style=" margin: 0 0 5px; line-height: 8px; display: block; color: #999; font-size: 7px">Must total 100%</span></td>
													<td style="padding:10px 3px;font-weight: bold; font-size: 15px;border-bottom: 3px solid #fff; margin: 5px 0;" ">100</td>
												<td style=" padding:10px 3px;border-bottom: 3px solid #fff; margin: 5px 0;">%</td>
												</tr>
											</table>
										</td>
										<td style="border-right: 1px solid #ddd; font-size: 8px; font-weight: normal;width:50px;max-width:50px;">

											<span class="md" style="word-break:break-all;">Has the Merchant previously and/or currently accept credit cards?</span>
											<label class="checkbox-box" style="width: 100%; display: block;">
												<input type="checkbox" class="checkbox" checked>Yes
											</label>
											<label class="checkbox-box" style="width: 100%; display: block;">
												<input type="checkbox" class="checkbox">No
											</label>
											If yes, provide copies of last 3 months of recent statement.
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td style="border-top: 1px solid #ddd; padding: 7px 0;">
								<table cellpadding="0" cellspacing="0" border="0">
									<tr>
										<td style="width: 110px">Does business conduct business seasonally?</td>
										<td>
											<label class="checkbox-box">
												<input type="checkbox" class="checkbox" checked>YES
											</label>
										</td>
										<td>
											<label class="checkbox-box">
												<input type="checkbox" class="checkbox">No
											</label>
										</td>
										<td style="width: 110px">
											If yes, please YES NO specify months:
										</td>
										<td>
											<label class="checkbox-box">
												<input type="checkbox" class="checkbox">Jan
											</label>
										</td>
										<td>
											<label class="checkbox-box">
												<input type="checkbox" class="checkbox">Feb
											</label>
										</td>
										<td>
											<label class="checkbox-box">
												<input type="checkbox" class="checkbox">Mar
											</label>
										</td>
										<td>
											<label class="checkbox-box">
												<input type="checkbox" class="checkbox">Apr
											</label>
										</td>
										<td>
											<label class="checkbox-box">
												<input type="checkbox" class="checkbox">May
											</label>
										</td>
										<td>
											<label class="checkbox-box">
												<input type="checkbox" class="checkbox" checked>Jun
											</label>
										</td>
										<td>
											<label class="checkbox-box">
												<input type="checkbox" class="checkbox">Jul
											</label>
										</td>
										<td>
											<label class="checkbox-box">
												<input type="checkbox" class="checkbox">Aug
											</label>
										</td>
										<td>
											<label class="checkbox-box">
												<input type="checkbox" class="checkbox">Sep
											</label>
										</td>
										<td>
											<label class="checkbox-box">
												<input type="checkbox" class="checkbox">Oct
											</label>
										</td>
										<td>
											<label class="checkbox-box">
												<input type="checkbox" class="checkbox">Now
											</label>
										</td>
										<td>
											<label class="checkbox-box">
												<input type="checkbox" class="checkbox">Dec
											</label>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td style="border-top: 1px solid #ddd; padding: 0;">
								<table cellpadding="0" cellspacing="0">
									<tr>
										<td style=";width: 240px; padding-right: 0; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd;">
											<table cellpadding="0" cellspacing="0" width="100%">
												<tr>
													<td>
														<table cellpadding="0" cellspacing="0" width="100%">
															<tr>
																<td>
																	<table cellpadding="0" cellspacing="0">
																		<tr>
																			<td style="width: 150px;">
																				Does the business use any third parties in the payment process?
																			</td>
																			<td>
																				<label class="checkbox-box">
																					<input type="checkbox" class="checkbox" checked>Yes
																				</label>
																			</td>
																			<td>
																				<label class="checkbox-box">
																					<input type="checkbox" class="checkbox">No
																				</label>
																			</td>
																		</tr>
																	</table>
																</td>
															</tr>
														</table>
													</td>
												</tr>
												<tr>
													<td>
														<table cellpadding="0" cellspacing="0" width="100%">
															<tr>
																<td>
																	<table cellpadding="0" cellspacing="0">
																		<tr>
																			<td style="width: 60px;">
																				If yes, please explain:

																			</td>
																			<td>
																				<textarea class="form-control"></textarea>
																			</td>
																		</tr>
																	</table>
																</td>
															</tr>
														</table>
													</td>
												</tr>
												<tr>
													<td>
														<table cellpadding="0" cellspacing="0" width="100%">
															<tr>
																<td>
																	<table cellpadding="0" cellspacing="0">
																		<tr>
																			<td style="width: 60px;">
																				If yes, please explain:

																			</td>
																			<td>
																				<textarea class="form-control"></textarea>
																			</td>
																		</tr>
																	</table>
																</td>
															</tr>
														</table>
													</td>
												</tr>
											</table>
										</td>

										<td style="padding: 0; border-bottom: 1px solid #ddd;" valign="top">

											<table cellpadding="0" cellspacing="0">
												<tr>
													<td style="border-bottom: 1px solid #ddd; padding: 5px 10px;">
														<table cellpadding="0" cellspacing="0">
															<tr>
																<td style="width: 250px;">
																	When is the cardholder billed for products/services?
																</td>
															</tr>
															<tr>
																<td style="vertical-align: middle;">
																	<label class="checkbox-box" style="display: block;margin:auto 0">
																		<input type="checkbox" class="checkbox">On order
																	</label>
																</td>
																<td>
																	<label class="checkbox-box">
																		<input type="checkbox" class="checkbox">Shipment
																	</label>
																</td>
															</tr>
														</table>
													</td>
												</tr>
												<tr>
													<td style="border-bottom: 1px solid #ddd; padding: 5px 10px;">
														<table cellpadding="0" cellspacing="0">
															<tr>
																<td style="width:auto">
																	Delivery of products:
																</td>
															</tr>
															<tr>
																<td style="vertical-align:middle;">
																		<input type="checkbox" class="checkbox" style="display:inline;margin: 0 2px 10px 0" checked>
																		<span style="display:inline-block;line-height:18px;margin:0 0 10px 5px;" >Time of sale</span>
																</td>
																<td style="vertical-align:middle;">
																	<input type="checkbox" class="checkbox" style="display:inline;margin: 0 2px 10px 0" checked>
																	<span style="display:inline-block;line-height:18px;margin:0 0 10px 5px;">1-3 Days</span>
																</td>
																<td style="vertical-align:middle;">
																	<input type="checkbox" class="checkbox" style="display:inline;margin: 0 2px 10px 0">
																	<span style="display:inline-block;line-height:18px;margin:0 0 10px 5px;">3-5 Days</span>
																</td>
																<td style="vertical-align:middle;">
																	<input type="checkbox" class="checkbox" style="display:inline;margin: 0 2px 10px 0">
																	<span style="display:inline-block;line-height:18px;margin:0 0 10px 5px;">5-15 Days</span>
																</td>
																<td style="vertical-align:middle;">
																	<input type="checkbox" class="checkbox" style="display:inline;margin: 0 2px 10px 0">
																	<span style="display:inline-block;line-height:18px;margin:0 0 10px 5px;">15 Days+</span>
																</td>
															</tr>
														</table>
													</td>
												</tr>
											</table>
										</td>

									</tr>


									<tr>
										<td style="width: 240px; border-right: 1px solid #ddd;">
											<table cellpadding="0" cellspacing="0" style="padding: 10px 0;">
												<tr>
													<td colspan="2" style="width: 250px;">
														Refund/Return Policy:
													</td>
												</tr>
												<tr style="vertical-align:middle;">
													<td width="30%" style="vertical-align:middle;">
														<input type="checkbox" class="checkbox" style="width: 13px;height:13px;margin-bottom:10px;display:inline" checked>
														<span style="display: inline-block;line-height:18px;margin:0 0 10px 10px;">No refund</span>
													</td>
													<td width="30%" style="vertical-align:middle;">
														<input type="checkbox" class="checkbox" style="width: 13px;height:13px;margin-bottom:10px;display:inline">
														<span style="display: inline-block;line-height:18px;margin:0 0 10px 10px;">Refund up to</span>
													</td>
													<td style="vertical-align:middle;">
														<input type="text" style="width:50px;height:12px; padding: 2px;" class="form-control">
													</td>
													<!-- <td width="50%">
													<div style="display: bloc;width:100%">
													<label class="checkbox-box" style="width: 40%; height: 24px; display: inline-block;">
													  	<input type="checkbox" class="checkbox" style="width: 15px;height:15px;">No refund
													</label>
													<label class="checkbox-box" style="width: 60%; height: 24px; display: inline-block;">
													  	<input type="checkbox" class="checkbox">Refund up to <input type="text" style="width: 23px;height:12px; padding: 2px; margin: 0 0 0 10px" class="form-control">
													</label>
													<label class="checkbox-box" style="width: 38%; height: 24px; display: inline-block;">
													  	<input type="checkbox" class="checkbox">Full refund
													</label>
													
													<label class="checkbox-box" style="width: 48%; height: 24px; display: inline-block;">
													  	<input type="checkbox" class="checkbox">Exchange only
													</label>
													<label class="checkbox-box" style="width: 38%; height: 24px; display: inline-block;">
													  	<input type="checkbox" class="checkbox">Other
													</label>
													<label class="checkbox-box" style="width: 48%; height: 24px; display: inline-block;">
													  	<input type="text" class="form-control" style="width: 50px;height:12px;">
													</label>
													</div>
												</td> -->
												</tr>
												<tr style="vertical-align:middle;">
													<td width="33%" style="vertical-align:middle;">
														<input type="checkbox" class="checkbox" style="width: 13px;height:13px;margin-bottom:10px;display:inline">
														<span style="display: inline-block;line-height:18px;margin:0 0 10px 10px;">Full refund</span>
													</td>
													<td width="33%" style="vertical-align:middle;" colspan="2">
														<input type="checkbox" class="checkbox" style="width: 13px;height:13px;margin-bottom:10px;display:inline">
														<span style="display: inline-block;line-height:18px;margin:0 0 10px 10px;">Exchange only</span>
													</td>
												</tr>
												<tr style="vertical-align:middle;margin-bottom:10px;">
													<td width="33%" style="vertical-align:middle;">
														<input type="checkbox" class="checkbox" style="width: 13px;height:13px;margin-bottom:10px;display:inline">
														<span style="display: inline-block;line-height:18px;margin:0 0 10px 10px;">Other</span>
													</td>
													<td width="66%" style="vertical-align:middle;margin-bottom:10px;" colspan="2">
														<input type="text" class="form-control" style="width: 90%;height:12px;">
													</td>
												</tr>
											</table>
										</td>

										<td>
											<span style="padding: 0; font: 12px 'Calibri'; line-height: 14px;font-family:Arial, Helvetica, sans-serif;">Detailed business description. (How products or services are sold)</span>
											<textarea class="form-control" style="width: 100%; height: 70px"></textarea>
										</td>
									</tr>



								</table>
							</td>
						</tr>
						<tr>
							<td style="border-top: 1px solid #ddd; font-size: 11px; color: #666; background: #f5f5f5; padding: 5px;">
								Failure to provide accurate information may result in a witholding of merchant funding per IRS regulations. (See Part IV, Section A.4 of your Program Guide available at tekcardpayments.com/terms-and-conditions for further information.)
							</td>
						</tr>
					</table>
				</div>
			</div>
			<!-- /.Section -->
			<!-- Section -->
			<div class="section" style="padding-bottom: 20px;">
				<div class="heading" style="margin: 0 0 5px;">2B. E-Commerce / Card Not Present / Phone / Mail Order </div>
				<div class="section-table pad-0">
					<table cellpadding="0" cellspacing="0" class="table">
						<tr>
							<td>
								<table cellpadding="0" cellspacing="0" class="table">
									<tr>
										<td style="width: 33.3333%">
											Current Gateway/Shopping Cart
										</td>
										<td style="width: 33.3333%">
											List All Applicable URL’s for your website
										</td>
										<td style="width: 33.3333%">
											&nbsp;
										</td>
									</tr>
									<tr>
										<td>
											<input type="text" class="form-control" value="Travolutionary">
										</td>
										<td>
											<input type="text" class="form-control">
										</td>
										<td>
											<input type="text" class="form-control">
										</td>
									</tr>
									<tr>
										<td>
											<table style="vertical-align:middle;">
												<td>Does your site have a secure SSL certificate?</td>
												<td style="vertical-align: top;">
													<label class="checkbox-box" style="width: 48%; height: 24px; display: inline-block;margin-top:0;">
														<input type="checkbox" class="checkbox" style="margin-top: 0;" checked>Yes
													</label>
												</td>
												<td>
													<label class="checkbox-box" style="width: 48%; height: 24px; display: inline-block;">
														<input type="checkbox" class="checkbox">No
													</label>
												</td>
											</table>
										</td>
										<td>
											<input type="text" class="form-control">
										</td>
										<td>
											<input type="text" class="form-control">
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<!-- /.Section -->
			<!-- Section -->
			<div class="section">
				<div class="heading" style="margin: 0 0 5px;">2C. Trade Reference</div>
				<div class="section-table pad-0">
					<table cellpadding="0" cellspacing="0" class="table">
						<tr>
							<td>
								<table cellpadding="0" cellspacing="0" class="table">
									<tr>
										<td style="width: 25%">
											Trade reference
										</td>
										<td style="width: 25%">
											Contact
										</td>
										<td style="width: 25%">
											Account #
										</td>
										<td style="width: 25%">
											Telephone #
										</td>
									</tr>
									<tr>
										<td>
											<input type="text" class="form-control">
										</td>
										<td>
											<input type="text" class="form-control">
										</td>
										<td>
											<input type="text" class="form-control">
										</td>
										<td>
											<input type="text" class="form-control" value="+1-541-754-3010">
										</td>
									</tr>

								</table>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<!-- /.Section -->
			<!-- Section -->
			<div class="section">
				<div class="heading" style="margin: 0 0 5px;">3. Banking Information: Please include a copy of a pre-printed voided check and/or a banking letter</div>
				<div class="section-table pad-0">
					<table cellpadding="0" cellspacing="0" class="table">
						<tr>
							<td>
								<table cellpadding="0" cellspacing="0" class="table">
									<tr>
										<td style="width: 33.333%">
											<span class="name">Name of Merchant’s Bank</span>
											<input type="text" class="form-control" value="JPMorgan-Chase">
										</td>
										<td style="width: 33.333%">
											<span class="name">Contact</span>
											<input type="text" class="form-control">
										</td>
										<td style="width: 33.333%">
											<span class="name">Bank Local Telephone #</span>
											<input type="text" class="form-control" value="+1-541-754-3010">
										</td>
									</tr>
									<tr>
										<td colspan="3">
											<table border="0" cellpadding="0" cellspacing="0">
												<tr>
													<td width="50%">
														<span class="name">Routing / ABA #</span>
														<input type="text" class="form-control">
													</td>
													<td width="50%">
														<span class="name">Account / DDA #</span>
														<input type="text" class="form-control">
													</td>
												</tr>
											</table>
											<p style="font-size: 10px; color: #999">
												AUTHORIZATION FOR AUTOMATIC FUNDS TRANSFER (ACH): The Merchant Bank is authorized to initiate or transmit automatic credit and/or debit and/or check entries to the account identified above and on the voided check supplied for this bank account. This settlement account, defined above, will utomatically be debited for all service amounts owed under this Merchant Agreement. Said authorization is granted to the Merchant Bank’s Processor and their agents. The transit routing and account number entered above MUST have ACH debit and credit capability and must match the information listed on the voided check provided with this application.
											</p>
										</td>

									</tr>

								</table>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<!-- /.Section -->
			<!-- Section -->
			<div class="section page-br">
				<div class="heading" style="margin: 0 0 5px;">4. Industry, Discount Rates and Pricing Methods</div>
				<div class="section-table pad-0">
					<table cellpadding="0" cellspacing="0" class="table">
						<tr>
							<td>
								<table cellpadding="0" cellspacing="0" class="table" style="padding-bottom: 10px;">
									<tr>
										<td>
											<table style="padding: 5px 0 0 0;">
												<tr>
													<td>Industry Type:</td>
													<td style="text-align: left;">
														<img src="images/icon-01.jpg" style="width: 40px" alt="">
														<label style="width: 100%; display: block;"><input type="radio" name="industry" style="display: inline;top:-3px;" checked> Retail</label>
													</td>
													<td style="text-align: left;">
														<img src="images/icon-02.jpg" style="width: 40px" alt="">

														<label style="width: 100%; display: block;"><input type="radio" name="industry" style="display: inline;top:-3px;"> Restaurant</label>
													</td>
													<td style="text-align: left;">
														<img src="images/icon-03.jpg" style="width: 40px" alt="">

														<label style="width: 100%; display: block;"><input type="radio" name="industry" style="display: inline;top:-3px;"> Fuel</label>
													</td>
													<td style="text-align: left;">
														<img src="images/icon-04.jpg" style="width: 40px" alt="">

														<label style="width: 100%; display: block;"><input type="radio" name="industry" style="display: inline;top:-3px;"> Moto</label>
													</td>
													<td style="text-align: left;">
														<img src="images/icon-05.jpg" style="width: 40px" alt="">

														<label style="width: 100%; display: block;"><input type="radio" name="industry" style="display: inline;top:-3px;"> E-Commerce</label>
													</td>
													<td style="text-align: left;">
														<img src="images/icon-06.jpg" style="width: 40px" alt="">

														<label style="width: 100%; display: block;"><input type="radio" name="industry" style="display: inline;top:-3px;"> Supermarket</label>
													</td>
													<td style="text-align: left;">
														<img src="images/icon-07.jpg" style="width: 40px" alt="">
														<label style="width: 100%; display: block;"><input type="radio" name="industry" style="display: inline;top:-3px;"> Retail</label>
													</td>
												</tr>
											</table>
										</td>
									</tr>
								</table>
							</td>

						<tr>
							<td>
								<table cellpadding="0" cellspacing="0" class="table">
									<tr>
										<td style="padding: 0 0 5px;">
											<table>
												<tr>
													<td style="padding: 0; width: 33%;max-width:200px;" valign="top">
														<table style="border: 1px solid #ddd;">
															<tr>
																<td style="background: #3e3f40; height: 50px; font-weight: bold; font-size: 12px; padding: 5px 8px; color: #fff;vertical-align:middle">
																	<table style="border: none;vertical-align:middle" height="20px">
																		<tr style="vertical-align: middle;">
																			<td style="vertical-align: middle;">
																				<input type="radio" name="option-a" style="margin:0 10px 0 0;"/>
																			</td>
																			<td style="vertical-align: middle;font-size: 12px;">Option A: Pass Through Interchange (Includes Dues & Assessments)</td>
																		</tr>
																	</table>
																</td>
															</tr>
															<tr>
																<td style="padding: 0 0 10px">
																	<img src="images/cross.png" width="15" style="margin: 5px 0 0 5px; position: relative; top: 3px;width:15px;height:15px;"> Gross Volume
																</td>
															</tr>
															<tr>
																<td style="">
																	<img src="images/cards-01.png" alt="" style="width: 190px;margin-top:20px;">
																	<p style="margin: 10px 0;">Pricing for Visa, Mastercard, Discover</p>
																</td>
															</tr>
															<tr>
																<td style="background: #eff0f5; padding: 3px 3px;">
																	<table>
																		<tr>
																			<td>Credit Interchange, Dues & Assessments</td>
																			<td>+</td>
																			<td><input type="text" style="background: #fff;" class="form-control md-input" name=""> </td>
																			<td>%</td>
																			<td style="font-size: 9px; line-height: 9px;">Discount Rate</td>
																		</tr>
																	</table>
																</td>
															</tr>
															<tr>
																<td style="background: #eff0f5; padding: 3px 3px;">
																	<table>
																		<tr>
																			<td>Credit Interchange, Dues & Assessments</td>
																			<td>+</td>
																			<td><input type="text" class="form-control md-input" style="background: #fff;" name=""> </td>
																			<td>%</td>
																			<td style="font-size: 9px; line-height: 9px;">Discount Rate</td>
																		</tr>
																	</table>
																</td>
															</tr>
															<tr>
																<td style="padding: 12px 5px 64px 5px;">
																	<img src="images/american.png" width="45">
																	<p style="margin: 0;">Pricing for American Express IC Plus (Select One)</p>
																</td>
															</tr>
															<tr>
																<td style="background: #eff0f5; padding: 3px 3px;">
																	<table>
																		<tr>
																			<td><input type="radio" name="" style="margin: 0 2px 0 0; position: relative; top: 2px;">Opt Blue</td>
																			<td style="width: 50px; text-align: right;"><input type="text" style="background: #fff;" class="form-control md-input" name=""> </td>
																			<td style="width: 8px; padding: 0;">%</td>
																			<td style="font-size: 9px; line-height: 9px; width: 37px">Discount Rate</td>
																		</tr>
																	</table>
																</td>
															</tr>
															<tr>
																<td style="background: #eff0f5; padding: 3px 3px;">
																	<table>
																		<tr>
																			<td><input type="radio" name="" style="margin: 0 2px 0 0; position: relative; top: 2px;"> Amex ESA
																				<label style="width: 100%; font-size: 11px; display: block;">SE # (required): <input type="text" class="form-control" style="width: 45px;  background: #fff; height: 17px; margin: 3px 0 0;" name=""></label>
																			</td>
																			<td>
																				<span style="display: inline-block;font-weight:bold">$</span>
																			</td>
																			<td style="width: 50px;  text-align: right;"><input type="text" class="form-control md-input" style="background: #fff;" name=""> </td>
																			<td style="width: 8px; padding: 0;">%</td>
																			<td style="font-size: 9px; line-height: 9px; width: 30px">Transaction
																				Fee</td>
																		</tr>
																	</table>
																</td>
															</tr>
															<tr>
																<td>
																	<p style="margin: 3px 0 0; height: 120px; color: #bdbdbd; font-size: 10px; line-height: 10px;">
																		Where Pass-Through Interchange is selected (Option 1), as indicated above, the fees quoted in the above fee schedule plus pass-through fees such as interchange fees, processing fees, and/or assessments from the Card Organization. Currently, the assessment fees are currently as follows: Visa: 0.14%, MasterCard: 0.13%, Discover: 0.13%, all of which are subject to change following a change notice to you via your monthly statement.
																	</p>
																</td>
															</tr>
														</table>
													</td>
													<td style="padding: 0; width: 33%;max-width:200px;" valign="top">
														<table style="border: 1px solid #ddd;">
															<tr>
																<td style="background: #3e3f40; height: 50px; font-weight: bold; font-size: 12px; padding: 5px 8px; color: #fff;">
																	<table style="width: 100%;" height="20px">
																		<tr width="100%">
																			<td style="vertical-align: middle;"><input type="radio" name="option-a" style="margin: 0 10px 0 0" checked></td>
																			<td style="vertical-align: middle;font-size: 12px;">Option B: ERR Pricing</td>
																		</tr>
																	</table>
																</td>
															</tr>
															<tr>
																<td style="padding: 0 0 10px">
																	<img src="images/cross.png" width="15" style="margin: 5px 0 0 5px; position: relative; top: 3px;width:15px;height:15px;"> Gross Volume
																</td>
															</tr>
															<tr>
																<td style="">
																	<img src="images/cards-01.png" alt="" style="width: 190px;margin-top:20px;">
																	<p style="margin: 10px 0 ;">Pricing for Visa, Mastercard, Discover</p>
																</td>
															</tr>
															<tr>
																<td style="background: #eff0f5; padding: 3px 3px;">
																	<table>
																		<tr>
																			<td>Credit Qual</td>
																			<td style="width: 50px; text-align: right;"><input type="text" style="background: #fff;" class="form-control md-input" name=""> </td>
																			<td style="width: 10px;">%</td>
																			<td style="width: 30px; font-size: 9px; line-height: 9px;">Discount Rate</td>
																		</tr>
																	</table>
																</td>
															</tr>
															<tr>
																<td style="background: #eff0f5; padding: 3px 3px;">
																	<table>
																		<tr>
																			<td>Non PIN Debit Qual (Signature Debit)</td>
																			<td style="width: 50px; text-align: right;"><input type="text" style="background: #fff;" class="form-control md-input" name=""> </td>
																			<td style="width: 10px;">%</td>
																			<td style="width: 30px; font-size: 9px; line-height: 9px;">Discount Rate</td>
																		</tr>
																	</table>
																</td>
															</tr>
															<tr>
																<td style="background: #eff0f5; padding: 3px 3px;">
																	<table>
																		<tr>
																			<td>Non Qual Surcharge (Credit and Non PIN Debit)</td>
																			<td style="width: 50px; text-align: right;"><input type="text" style="background: #fff;" class="form-control md-input" name=""> </td>
																			<td style="width: 10px;">%</td>
																			<td style="width: 30px; font-size: 9px; line-height: 9px;">Discount Rate</td>
																		</tr>
																	</table>
																</td>
															</tr>

															<tr>
																<td style="">
																	<img src="images/american.png" width="45">
																	<p>Pricing for American Express (Select One)</p>
																</td>
															</tr>
															<tr>
																<td style="background: #eff0f5; padding: 3px 3px;">
																	<table>
																		<tr>
																			<td><input type="radio" name="" style="margin: 0 2px 0 0; position: relative; top: 2px;">Opt Blue</td>
																			<td style="width: 50px; text-align: right;"><input type="text" style="background: #fff;" class="form-control md-input" name=""> </td>
																			<td style="width: 8px; padding: 0;">%</td>
																			<td style="font-size: 9px; line-height: 9px; width: 37px">Discount Rate</td>
																		</tr>
																	</table>
																</td>
															</tr>
															<tr>
																<td style="background: #eff0f5; padding: 3px 3px;">
																	<table>
																		<tr>
																			<td><input type="radio" name="" style="margin: 0 2px 0 0; position: relative; top: 2px;"> Amex ESA
																				<label style="width: 100%; font-size: 11px; display: block;">SE # (required): <input type="text" class="form-control" style="width: 45px;  background: #fff; height: 17px; margin: 3px 0 0;" name=""></label>
																			</td>
																			<td>
																				<span style="display: inline-block;font-weight:bold">$</span>
																			</td>
																			<td style="width: 50px;  text-align: right;"><input type="text" class="form-control md-input" style="background: #fff;" name=""> </td>
																			<td style="width: 8px; padding: 0;">%</td>
																			<td style="font-size: 9px; line-height: 9px; width: 30px">Transaction
																				Fee</td>
																		</tr>
																	</table>
																</td>
															</tr>
															<tr>
																<td>
																	<p style="margin: 3px 0 0; height:auto; color: #bdbdbd; font-size: 10px; line-height: 10px;">
																		Where ERR Pricing is selected (Option B), Enhanced Recover Reduced bills Non-qualifying Industry Program Fees on top of qualified rate outlined above on all transactions. Non-qualifying Industry Program Fees are the difference between the qualifying fees for the merchant’s current Industry Program Fee reduction program and the actual fees. The system assesses the merchant the difference for the Non-qualifying Industry Program Fees with an additional surcharge to this difference outlined above. Additional fees and charges may apply as outlined in this agreement and/or the terms and conditions.
																	</p>
																</td>
															</tr>
														</table>
													</td>
													<td style="padding: 0; width: 33%;max-width:200px;" valign="top">
														<table style="border: 1px solid #ddd;">
															<tr>
																<td style="background: #3e3f40; height: 50px; font-weight: bold; font-size: 12px; padding: 5px 8px; color: #fff;">
																	<table width="100%" style="min-height: 20px;" height="20px">
																		<tr width="100%">
																			<td style="vertical-align:middle"><input type="radio" name="option-a" style="margin:0 10px 0 0"></td>
																			<td style="vertical-align:middle;font-size:12px;">Option C: Flat Rate Pricing</td>
																		</tr>
																	</table>
																</td>
															</tr>
															<tr>
																<td style="padding: 0 0 10px">
																	<img src="images/cross.png" width="15" style="margin: 5px 0 0 5px; position: relative; top: 3px;width:15px;height:15px;"> Gross Volume
																</td>
															</tr>
															<tr>
																<td style="">
																	<img src="images/cards-01.png" alt="" style="width: 190px;margin-top:10px;">
																	<p style="margin: 10px 0;">Pricing for Visa, Mastercard, Discover</p>
																</td>
															</tr>
															<tr>
																<td style="background: #eff0f5; padding: 3px 3px;">
																	<table>
																		<tr>
																			<td>Credit (Bankcard)</td>
																			<td style="width: 50px; text-align: right;"><input type="text" style="background: #fff;" class="form-control md-input" name=""> </td>
																			<td style="width: 10px;">%</td>
																			<td style="width: 30px; font-size: 9px; line-height: 9px;">Discount Rate</td>
																		</tr>
																	</table>
																</td>
															</tr>
															<tr>
																<td style="background: #eff0f5; padding: 3px 3px;">
																	<table>
																		<tr>
																			<td>Non-PIN Debit (Signature Debit)</td>
																			<td style="width: 50px; text-align: right;"><input type="text" style="background: #fff;" class="form-control md-input" name=""> </td>
																			<td style="width: 10px;">%</td>
																			<td style="width: 30px; font-size: 9px; line-height: 9px;">Discount Rate</td>
																		</tr>
																	</table>
																</td>
															</tr>
															<tr>
																<td style="padding: 12px 5px 62px 5px;">
																	<img src="images/american.png" width="45" />
																	<p style="margin: 0;display:block">Pricing for American Express IC Plus (Select One)</p>
																</td>
															</tr>
															<tr>
																<td style="background: #eff0f5; padding: 3px 3px;">
																	<table>
																		<tr>
																			<td><input type="radio" name="" style="margin: 0 2px 0 0; position: relative; top: 2px;">Opt Blue</td>
																			<td style="width: 50px; text-align: right;"><input type="text" style="background: #fff;" class="form-control md-input" name=""> </td>
																			<td style="width: 8px; padding: 0;">%</td>
																			<td style="font-size: 9px; line-height: 9px; width: 37px">Discount Rate</td>
																		</tr>
																	</table>
																</td>
															</tr>
															<tr>
																<td style="background: #eff0f5; padding: 3px 3px;">
																	<table>
																		<tr>
																			<td><input type="radio" name="" style="margin: 0 2px 0 0; position: relative; top: 2px;"> Amex ESA
																				<label style="width: 100%; font-size: 11px; display: block;">SE # (required): <input type="text" class="form-control" style="width: 45px;  background: #fff; height: 17px; margin: 3px 0 0;" name=""></label>
																			</td>
																			<td>
																				<span>$</span>
																			</td>
																			<td style="width: 50px;  text-align: right;"><input type="text" class="form-control md-input" style="background: #fff;" name=""> </td>
																			<td style="width: 8px; padding: 0;">%</td>
																			<td style="font-size: 9px; line-height: 9px; width: 30px">Transaction Fee</td>
																		</tr>
																	</table>
																</td>
															</tr>
															<tr>
																<td>
																	<p style="margin: 3px 0 0; height: auto; color: #bdbdbd; font-size: 10px; line-height: 10px;max-width:100%;">
																		Where Flat Rate Pricing is selected (Option C), Flat-Rate Pricing program bills the discount rate outlined above for all card types listed above no matter the type or volume of card transactions. Flat-Rate Pricing is only applicable to the card types outlined above and does not include nonbankcard cards, Pin based debit transactions, EBT transactions, Fuel cards and gift cards. Pricing includes all Card Organization costs (including interchange fees, dues and assessments, and debit network fees). Pricing does NOT include other service, account, compliance costs and does not include costs associated with non-bankcards; these fees are billed separately as outlined in this agreement and/or the terms and conditions.
																	</p>
																</td>
															</tr>
														</table>
													</td>
												</tr>
												<tr>
													<td colspan="3">
														<p style="margin: 8px 0 0; padding-top:10px;width: 100%; color: #bdbdbd; font-size: 10px; line-height: 10px;max-width:600px;">
															The following is applicable to all pricing options above: Please review the Merchant Processing Agreement at www.TekCardPayments.com/terms-and-conditions for additional information on which interchange programs apply. “AMEX Cost" includes all Interchange/Discount, Dues, Assessments, surcharges, plus an AMEX 0.15% Fee Surcharge applicable for AMEX transactions. For more information on interchange rates visit www.visa.com, www.mastercard.com or www.americanexpress.com. The following surcharges also apply to American Express transactions when applicable: Card Not Present Fee of 0.30% and Cross Border Transaction Fee of 0.40%. Fees or charges may be added or changed by an amendment to the Merchant Processing Agreement with 30 days notice.
														</p>
													</td>
												</tr>
											</table>
										</td>
									</tr>
								</table>
							</td>

						</tr>
					</table>
				</div>
			</div>
			<!-- /.Section -->
			<!-- Section -->
			<div class="section">
				<div class="heading" style="margin: 0 0 5px;">5. Enhanced Merchant Data Security and Chargeback Insurance</div>
				<div class="section-table pad-0">
					<table cellpadding="0" cellspacing="0" class="table">
						<tr>
							<td>
								<table cellpadding="0" cellspacing="0" class="table">
									<tr>
										<td style="width: 40%">

											<table cellpadding="0" cellspacing="0" class="table">
												<tr width="100%">
													<td style="min-width:10%" width="10%">
														<img src="images/cross.png" width="100%" style="min-width:100%;margin: 0 5px 0 0; position: relative; top: 3px"> 
													</td>
													<td style="min-width: 30%;" width="30%">
													Chargeback & Breach Advantage Program
													</td>
													<td style="min-width: 10%;" width="10%">
														<label><input type="checkbox" name="" style="margin: 5px 0 0;width:100%" checked> Opt-Out </label>
													</td>
													<td style="vertical-align: middle;min-width:10%;max-width:5%" width="5%"> 
														$ 
													</td>
													<td style="min-width: 10%; padding: 10px 0" width="10%" valign="middle">
														<input type="text" class="form-control" style="width: 100%; " name="">
													</td>
													<td style="min-width:15%; line-height: 10px; font-size: 9px;max-width:15%" width="15%">
														Monthly Fee (Per MID)
													</td>
												</tr>
											</table>
										</td>
										<td style="width: 30%">
											<p style="margin: 3px 0 7px; width: 100%; color: #bdbdbd; font-size: 10px; line-height: 10px;">
												The following benefit is provided to you by Processor and not Bank, and this Summary of Benefits outlines some of the terms and conditions of the PCI Compliance, LLC Enhanced Merchant Data Security and Chargeback Insurance program administered in partnership with Payment Insurance Network, LLC. This Summary of Benefits is not an insurance policy or a certificate of insurance. Subject to certain terms and conditions, Merchants are entitled to the benefits described below. Should you have any questions regarding the program, wish to view a complete copy of the terms and conditions or wish to opt-out of the benefits provided by the policy, please email info@tekcardpayments.com BENEFIT: The program provides benefits exclusively to Merchant who have a contractual relationship for Transaction processing or associated services with an ISO Insured under the program. The program does not cover deliberate acts of a Merchant’s employees and does not cover Data Security Costs incurred by a Processor.
											</p>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<!-- /.Section -->
			<!-- Section -->
			<div class="section">
				<div class="heading" style="margin: 0 0 5px;">6. Merchant Club</div>
				<div class="section-table pad-0">
					<table cellpadding="0" cellspacing="0" class="table">
						<tr>
							<td>
								<table cellpadding="0" cellspacing="0" class="table">
									<tr>
										<td style="width: 40%">

											<table cellpadding="0" cellspacing="0" class="table">
												<tr>
													<td style="">
														<label>
															<input type="checkbox" name="" style="margin: 5px 0 0;" checked>
															Opt in to Merchant Club Paper & Supplies Program
														</label>
													</td>
													<td style="width: 50px; padding: 10px 0" valign="middle">
														$ 14.95
													</td>
													<td style="width: 45px; line-height: 10px; font-size: 9px; ">
														Monthly Fee (Per MID)
													</td>
												</tr>
											</table>
										</td>
										<td style="width: 60%">
											<p style="margin: 3px 0 7px; width: 100%; color: #bdbdbd; font-size: 10px; line-height: 10px;">
												Paper & Supplies Program (Merchant Club) may apply certain rules and restrictions. Visit www.tekcardpayments.com/terms-and-conditions for more info. Merchant Club billed monthly per Merchant ID. Merchant club covers terminal paper and standalone terminals. Does NOT include paper for any large thermal printer/POS systems. Paper quantity sent at TekCard’s discretion based upon merchant’s monthly transaction volume. Can be canceled at any time. This program is provided to you by Processor and not Bank. Bank is not a party, as it applies to the TekCard Merchant Club Program, and Bank is not liable to you in any with respect to such services.
											</p>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<!-- /.Section -->
			<!-- Section -->
			<div class="section">
				<table cellpadding="0" cellspacing="0" class="table">
					<tr>
						<td style="width: 33.33333%;  padding: 0 10px;" valign="top">
							<div class="section" style="margin: 10px 0; border: 1px solid #e2e3e8;">
								<div class="heading">7A. Credit & EBT Authorization Fees</div>
								<table border="0">
									<tbody>
										<tr>
											<td style="padding: 10px 5px">
												<img src="images/cards-01.png" alt="" style="height: 15px;">
											</td>
										</tr>
										<tr>
											<td style="background: #eff0f5; padding: 3px 3px;">
												<table width="100%">
													<tbody width="100%">
														<tr width="100%">
															<td style="max-width: 50%;min-width:50%;" width="50%">Visa, Mastercard, Discover</td>
															<td style="max-width: 10%;min-width:10%;" width="10%">
																<span>$</span>
															</td>
															<td style="min-width:20%;text-align: right;" width="20%">
																<input type="text" style="width: 100%; background: #fff;" class="form-control" name="" />
															</td>
															<td style="min-width: 20%;max-width:20%;font-size: 9px; line-height: 9px;" width="20%">Per Item</td>
														</tr>
													</tbody>
												</table>
											</td>
										</tr>
										<tr>
											<td style="background: #eff0f5; padding: 3px 3px;">
												<table width="100%">
													<tbody width="100%">
														<tr width="100%">
															<td width="50%" style="min-width: 50%;">American Express</td>
															<td width="10%" style="min-width: 10%;">
																<span>$</span>
															</td>
															<td style="min-width:20%; text-align: right;" width="20%">
																<input type="text" style="width: 100%; background: #fff;" class="form-control" name="">
															</td>
															<td style="min-width:20%;font-size: 9px;line-height: 9px;">Per Item</td>
														</tr>
													</tbody>
												</table>
											</td>
										</tr>
										<tr>
											<td style="background: #eff0f5; padding: 3px 3px;">
												<table width="100%" style="vertical-align: middle;">
													<tbody width="100%" style="vertical-align: middle;">
														<tr width="100%" style="vertical-align: middle;">
															<td style="min-width: 50%;max-width:50%;vertical-align:middle" width="50%">EBT (FNS ID: <input type="text" style="width: auto; background: #fff;" class="form-control" name="">)
																<label style="width: auto; font-size: 13px;">(Cash benefits only)</label>
															</td>
															<td style="min-width: 10%;vertical-align:middle" width="10%">
																<span>$</span>
															</td>
															<td style="min-width: 20%;max-width:20%; text-align: right;vertical-align:middle" valign="top" width="20%">
																<input type="text" style="width: 100%; background: #fff;" class="form-control" name="">
															</td>
															<td style="min-width: 20%;font-size: 9px; line-height: 9px; padding: 5px 0 0;vertical-align:middle" valign="top" width="20%"> Per Item</td>
														</tr>
													</tbody> 
												</table>
											</td>
										</tr>
										<tr>
											<td style="background: #eff0f5; padding: 0 3px 0;">
												<table>
													<tr>
														<td>eWIC (FNS ID <input type="text" style="width: 75px; background: #fff;" class="form-control" name="">)
															<label style="width: 100%; font-size: 13px;">(Cash benefits only)</label>
														</td>
														<td style="width: 50px; text-align: right;" valign="top">
															&nbsp;
														</td>
														<td style="width: 17px; font-size: 9px; line-height: 9px; padding: 5px 0 0;" valign="top">
															&nbsp;
														</td>
													</tr>
												</table>
											</td>
										</tr>
										<tr>
											<td style="background: #eff0f5; padding: 0 3px 0;">
												<table>
													<tbody>
														<tr>
															<td>State
																<input type="text" style="width: 75px; background: #fff;" class="form-control" name="" value="Florida">
															</td>
															<td style="width: 50px; text-align: right;" valign="top">
																&nbsp;
															</td>
															<td style="width: 17px; font-size: 9px; line-height: 9px; padding: 5px 0 0;" valign="top">
																&nbsp;
															</td>
														</tr>
													</tbody>
												</table>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</td>
						<td style="width: 33.33333%;  padding: 0 10px;" valign="top">
							<div class="section" style="margin: 10px 0; border: 1px solid #e2e3e8;">
								<div class="heading">7B. Pin Based Debit Network Fee</div>
								<table border="0">
									<tbody>
										<tr>
											<td style="padding: 10px 5px">
												<img src="images/cards-02.png" alt="" style="height: 15px;">
											</td>
										</tr>
										<tr>
											<td style="background: #eff0f5; padding: 7px 3px;">
												<table>
													<tbody>
														<tr>
															<td>Debit Network Fees</td>
															<td style="width: 50px; text-align: right;">
																Pass Thru
															</td>
														</tr>
													</tbody>
												</table>
											</td>
										</tr>
										<tr>
											<td style="background: #eff0f5; padding: 7px 3px;">
												<table>
													<tbody>
														<tr>
															<td>Switch Fees</td>
															<td style="width: 50px; text-align: right;">
																Pass Thru
															</td>
														</tr>
													</tbody>
												</table>
											</td>
										</tr>
										<tr>
											<td style="background: #eff0f5; padding: 5px 3px;">
												<table>
													<tbody>
														<tr>
															<td>Item Rate</td>
															<td>
																<span>
																$
																</span>
															</td>
															<td style="width: 50px; text-align: right;">
																<input type="text" style="width: 40px; background: #fff;" class="form-control" name="">
															</td>
															<td style="width: 17px; font-size: 9px; line-height: 9px;">Per Item</td>
														</tr>
													</tbody>
												</table>
											</td>
										</tr>
										<tr>
											<td style="background: #eff0f5; padding: 5px 3px;">
												<table>
													<tbody>
														<tr>
															<td>Volume Percentage</td>
															<td style="width: 65px; text-align: right;">
																<input type="text" style="width: 40px; background: #fff;display:inline-block" class="form-control" name=""> 
															</td>
															<td width="14px">%</td>
															<td style="width: 25px; font-size: 9px; line-height: 9px;">Discount Rate</td>
														</tr>
													</tbody>
												</table>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</td>
						<td style="width: 33.33333%;  padding: 0 10px;" valign="top">
							<div class="section" style="margin: 10px 0; border: 1px solid #e2e3e8;">
								<div class="heading">7B. Pin Based Debit Network Fee</div>
								<table border="0">
									<tbody>
										<tr>
											<td style="padding: 10px 5px">
												<img src="images/cards-03.png" alt="" style="height: 15px;">
											</td>
										</tr>
										<tr>
											<td style="background: #eff0f5; padding: 10px 3px;">
												<table>
													<tbody>
														<tr>
															<td style="width: 30px">WEX Inc.</td>
															<td>
																<span>
																$ 
																</span>
															</td>
															<td style="width: 50px; text-align: right;">
																<input type="text" style="width: 30px; background: #fff;" class="form-control" name="">
															</td>
															<td style="width: 17px; font-size: 9px; line-height: 9px;">Per Item</td>
															<td>
																<span>$ </span>
															</td>
															<td style="width: 30px; text-align: right;">
																<input type="text" style="width: 30px; background: #fff;" class="form-control" name="">
															</td>
															<td style="width: 17px; font-size: 9px; line-height: 9px;">Discount Rate</td>
														</tr>
													</tbody>
												</table>
											</td>
										</tr>
										<tr>
											<td style="background: #eff0f5; padding: 10px 3px;">
												<table style="vertical-align:middle;">
													<tbody style="vertical-align:middle;">
														<tr style="vertical-align:middle;">
															<td style="width: 30px" style="vertical-align:middle;">Voyager</td>
															<td>
																<span>$</span>
															</td>
															<td style="width: 50px; text-align: right;" style="vertical-align:middle;">
																 <input type="text" style="width: 30px; background: #fff;" class="form-control" name="">
															</td>
															<td style="width: 17px; font-size: 9px; line-height: 9px;" style="vertical-align:middle;">Per Item</td>
															<td>
																<span> 
																$
																</span>
															</td>
															<td style="width: 30px; text-align: right;" style="vertical-align:middle;">
																 <input type="text" style="width: 30px; background: #fff;" class="form-control" name="">
															</td>
															<td style="width: 17px;  font-size: 9px; line-height: 9px;">Discount Rate</td>
														</tr>
													</tbody>
												</table>
											</td>
										</tr>
										<tr>
											<td style="background: #eff0f5; padding: 10px 3px;">
												<table>
													<tbody>
														<tr>
															<td style="width: 80px">Fuelman</td>
															<td>
																<span >$</span>
															</td>
															<td style="width: 50px; text-align: right;">
																<input type="text" style="width: 30px; background: #fff;" class="form-control" name="">
															</td>
															<td style="width: 17px; font-size: 9px; line-height: 9px;">Per Item</td>
															<td style="width: 30px; text-align: center; font-size: 9px; line-height: 9px;">
																(Discount rate billed directly via Fuelman)
															</td>
														</tr>
													</tbody>
												</table>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</td>
					</tr>
				</table>
			</div>
			<!-- service charges -->
			<div class="section">
				<div class="heading" style="margin: 0;font-family:Arial, Helvetica, sans-serif">8. Service Charges</div>
				<div class="section-table pad-0">
					<table cellpadding="0" cellspacing="0" class="table">
						<tr width="100%">
							<td style="min-width:50%;padding: 0 10px;" valign="top" width="50%">
								<div class="section" style="margin: 10px 0; border: 1px solid #e2e3e8;">
									<table border="0" style="font: 11px 'Calibri'; line-height: 5px; ">
										<tbody>
											<tr>
												<td style="background: #eff0f5; padding: 2px 3px;">
													<table>
														<tbody>
															<tr width="100%">
																<td style="min-width:50%;max-width:50%;text-align:left;" width="50%">Batch</td>
																<td style="min-width:10%;max-width:10%;text-align:left;" width="10%"><b class="doller">$</b></td>
																<td style="min-width:20%;max-width:20%;" valign="middle" width="20%">
																	<input type="text" style="width: 100%; background: #fff; margin: 0" class="form-control" name="">
																</td>
																<td style="min-width:20%;max-width:20%;padding: 0; margin: 0; font-size: 8px; line-height: 9px;text-align: left;" width="20%">Per Batch</td>
															</tr>
														</tbody>
													</table>
												</td>
											</tr>
											<tr>
												<td style="background: #eff0f5; padding: 2px 3px;">
													<table>
														<tbody>
															<tr>
																<td style="min-width:50%;max-width:50%;text-align:left;" width="50%">Bi-Annual PCI Fee</td>
																<td style="min-width:10%;max-width:10%;text-align:left;padding: 0;" valign="center" width="10%">
																	<b class="doller">$</b>
																</td>
																<td style="min-width: 20%;max-width:20%;text-align:left" width="20%">49.95</td>
																<td style="min-width: 20%; padding: 0; margin: 0; font-size: 8px; line-height: 9px;" width="20%">&nbsp;</td>
															</tr>
															<tr>
																<table style="border: none;" style="margin:0;padding:0;" cellspacing="0" width="100%">
																		<tr width="100%">
																			<td width="50%" style="max-width:50%;min-width:50%">
																				<span style="display:inline;font-size: 8px; line-height: 8px; margin: 0; color: #999; font-weight: bold;text-align:left">(Billed Bi-Annually in June and December)</span>
																			</td>
																		</tr>
																</table>
															</tr>
														</tbody>
													</table>
												</td>
											</tr>
											<tr>
												<td style="background: #eff0f5; padding: 2px 3px;">
													<table>
														<tbody>
															<tr>
																<td style="min-width:50%;max-width:50%;text-align:left;" width="50%">Monthly Minimum</td>
																<td style="min-width:10%;max-width:10%;text-align:left" width="10%">
																	<b class="doller">$</b> 
																</td>
																<td style="min-width:20%;max-width:20%;text-align: left;" valign="middle" width="20%">
																	<input type="text" style="background: #fff; margin: 0" class="form-control" name="">
																</td>
																<td style="min-width:20%;max-width:20%;padding: 0; margin: 0; font-size: 8px; line-height: 9px;text-align:left">&nbsp;</td>
															</tr>
														</tbody>
													</table>
												</td>
											</tr>
											<tr>
												<td style="background: #eff0f5; padding: 2px 3px;">
													<table>
														<tbody>
															<tr>
																<td style="min-width:50%;max-width:50%" width="50%">Monthly MC Location Fee
																</td>
																<td style="min-width:10%;text-align:left;" valign="center" width="10%">
																	<b class="doller">$</b>
																</td>
																<td style="min-width: 20%;max-width:20%;text-align:left" width="20%">2.50</td>
																<td style="min-width: 20%;max-width:20%;padding: 0; margin: 0; font-size: 9px; line-height: 9px;" width="20%">&nbsp;</td>
															</tr>
															<tr>
																<td>
																	<span style="font-size: 10px; line-height: 10px; margin: 0; color: #999; font-weight: bold;">(Per Mid, Per Month)</span>
																</td>
															</tr>
														</tbody>
													</table>
												</td>
											</tr>
											<tr>
												<td style="background: #eff0f5; padding: 2px 3px;">
													<table>
														<tbody>
															<tr>
																<td style="min-width:50%;max-width:50%;text-align:left" width="50%">Chargeback Fee</td>
																<td style="min-width:10%;max-width:10%;text-align:left;" width="10%"><b class="doller">$</b></td>
																<td style="min-width:20%;max-width:20%;text-align:left;" valign="center" width="20%">
																	<input type="text" style="background: #fff; margin: 0" class="form-control" name="">
																</td>
																<td style="min-width:20%;max-width:20%;padding: 0; margin: 0; font-size: 8px; line-height: 9px;text-align:left">Per
																	Item</td>
															</tr>
														</tbody>
													</table>
												</td>
											</tr>
											<tr>
												<td style="background: #eff0f5; padding: 10px 3px;">
													<table>
														<tbody>
															<tr>
																<td style="min-width:50%;max-width:50%;text-align:left" width="50%">Voice Authorization
																</td>
																<td style="min-width:10%;text-align:right;max-width:10%;text-align:left;" valign="middle">
																	<b class="doller">$</b>
																</td>
																<td style="min-width: 20%;max-width:20%;text-align:left;" width="20%">1.75</td>
																<td style="min-width: 20%;max-width:20%;padding: 0; margin: 0; font-size: 9px; line-height: 9px;text-align:left;" width="20%">Per Item</td>
															</tr>
														</tbody>
													</table>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</td>
							<td style="min-width:50%;padding: 0 10px 0 0;" valign="top" width="50%">
								<div class="section" style="margin: 10px 0; border: 1px solid #e2e3e8;">
									<table border="0" style="font-size: 12px">
										<tbody>
											<tr>
												<td style="background: #eff0f5; padding: 2px 3px;">
													<table>
														<tbody>
															<tr>
																<td style="min-width:50%;max-width:50%;text-align:left" width="50%">ACH Reject Fee</td>
																<td style="min-width:10%;max-width:10%;text-align:left;" valign="middle" width="10%">
																	<b class="doller">$</b> 
																</td>
																<td style="min-width:20%;max-width:20%;" width="20%"><input type="text" style="background: #fff; margin: 0" class="form-control" name=""></td>
																<td style="min-width:20%;max-width:20%;padding: 0; margin: 0; font-size: 8px; line-height: 9px;text-align:left" width="20%">Per Reject</td>
															</tr>
														</tbody>
													</table>
												</td>
											</tr>
											<tr>
												<td style="background: #eff0f5; padding: 5px 3px;">
													<table>
														<tbody>
															<tr>
																<td style="min-width:50%;max-width:50%;text-align:left;" width="50%">Monthly Service & Support Fee</td>
																<td style="min-width: 10%;max-width:10%" width="10%"></td>
																<td style="min-width: 20%;max-width:20%" width="20%"></td>
																<td style="min-width:20%; font-weight:bold;text-align:left;" valign="middle">Fee</td>
															</tr>
														</tbody>
													</table>
												</td>
											</tr>
											<tr>
												<td style="background: #eff0f5; padding: 2px 3px;">
													<table>
														<tbody>
															<tr>
																<td style="min-width:50%;max-width:50%;text-align:left" width="50%">Debit Access Fee</td>
																<td style="min-width: 10%;max-width:10%" width="10%"><b class="doller">$</b></td>
																<td style="min-width:20%;max-width:20%;text-align: left;" valign="middle" width="20%">
																	<input type="text" style="width: 36px; background: #fff; margin: 0" class="form-control" name="">
																</td>
																<td style="min-width:20%;max-width:20%;padding: 0; margin: 0; font-size: 8px; line-height: 9px;text-align:left" width="20%">Per Month</td>
															</tr>
														</tbody>
													</table>
												</td>
											</tr>
											<tr>
												<td style="background: #eff0f5; padding: 2px 3px;">
													<table>
														<tbody>
															<tr>
																<td style="min-width:50%;max-width:50%;text-align:left;" width="50%">Statement Fee</td>
																<td style="min-width: 10%;max-width:10%;text-align:left" width="10%"><b class="doller">$</b> </td>
																<td style="min-width:20%;max-width:20%;text-align: left;margin:0;" valign="middle">
																	<input type="text" style="background: #fff; margin: 0;width:36px;margin:0;" class="form-control" name="">
																</td>
																<td style="min-width:20%;max-width:20%;padding: 0; margin: 0; font-size: 8px; line-height: 9px;" width="20%">Per Batch</td>
															</tr>
														</tbody>
													</table>
												</td>
											</tr>
											<tr>
												<td style="background: #eff0f5; padding: 2px 3px;">
													<table>
														<tbody>
															<tr>
																<td style="min-width:50%;max-width:50%;text-align:left;" width="50%">Retrieval Request</td>
																<td style="min-width:10%;max-width:10%;text-align:left;" width="10%"><b class="doller">$</b> </td>
																<td style="min-width:20%;max-width:20%;text-align:left;" width="20%" valign="center">
																	<input type="text" style="width: 36px; background: #fff; margin: 0" class="form-control" name="">
																</td>
																<td style="min-width:20%;max-width:20%;padding: 0; margin: 0; font-size: 8px; line-height: 9px;" width="20%">&nbsp;</td>
															</tr>
														</tbody>
													</table>
												</td>
											</tr>
											<tr>
												<td style="background: #eff0f5; padding: 10px 3px;">
													<table>
														<tbody>
															<tr>
																<td style="min-width:50%;max-width:50%;" width="50%">Online Account Reporting</td>
																<td style="min-width: 10%;max-width:10%" width="10%"></td>
																<td style="min-width: 10%;max-width:10%" width="20%"></td>
																<td style="min-width: 10%;max-width:10%;font-weight: bold;" valign="center" width="20%">
																	Fee
																</td>
															</tr>
														</tbody>
													</table>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</td>
						</tr>
						<tr width="100%"> 
							<td style="min-width:50%;padding: 0 10px;" valign="top" width="50%">
								<div class="section" style="margin: 10px 0; border: 1px solid #e2e3e8;">
									<table border="0" style="font-size: 12px">
										<tbody>
											<tr>
												<td style="background: #eff0f5; padding: 2px 3px;">
													<table>
														<tbody>
															<tr>
																<td style="min-width:50%;max-width:50%;text-align:left;" width="50%">IRS Regulatory Product Fee</td>
																<td style="min-width: 10%;max-width:10%;text-align:left;" width="10%"><b class="doller">$</b></td>
																<td style="min-width:20%;max-width:20%;text-align:left;" valign="center" width="20%">
																	<input type="text" style="width: 36px; background: #fff; margin: 0" class="form-control" name="">
																</td>
																<td style="min-width:20%;max-width:20%;padding: 0; margin: 0; font-size: 8px; line-height: 9px;text-align:left;" width="20%">Per Month</td>
															</tr>
														</tbody>
													</table>
												</td>
											</tr>
											<tr>
												<td style="background: #eff0f5; padding: 2px 3px;">
													<table>
														<tbody>
															<tr>
																<td style="min-width:50%;max-width:50%;text-align: left;" width="50%">V / MC / D / AX Other Item Rate</td>
																<td style="min-width: 10%;max-width:10%;text-align: left;" width="10%"><b class="doller">$</b></td>
																<td style="min-width: 20%;max-width:20%;text-align: left;" valign="middle">
																	<input type="text" style="width: 36px; background: #fff; margin: 0" class="form-control" name="">
																</td>
																<td style="min-width: 20%;max-width:20%;padding: 0; margin: 0; font-size: 8px; line-height: 9px;text-align: left;" width="20%">Per Item</td>
															</tr>
														</tbody>
													</table>
												</td>
											</tr>
											<tr>
												<td style="background: #eff0f5; padding: 2px 3px;">
													<table>
														<tbody>
															<tr>
																<td style="min-width:50%;max-width:50%;text-align:left;" width="50%">Other Volume Rate</td>
																<td style="min-width: 10%;max-width:10%" wisdth="10%"></td>
																<td style="min-width:20%;max-width:20%;text-align: left;" valign="center" width="20%">
																	<input type="text" style="width: 36px; background: #fff; margin: 0" class="form-control" name="">
																</td>
																<td style="min-width:20%;max-width:20%;padding: 0; margin: 0; font-size: 8px; line-height: 9px;text-align-left;" width="20%">%</td>
															</tr>
														</tbody>
													</table>
												</td>
											</tr>
											<tr>
												<td style="background: #eff0f5; padding: 2px 3px;">
													<table>
														<tbody>
															<tr>
																<td style="min-width: 50%;max-width:50%;text-align:left;" width="50%">Return Transaction Fee</td>
																<td style="min-width: 10%;max-width:10%;text-align:left;" width="10%"><b class="doller">$</b></td>
																<td style="min-width: 20%;max-width:20%;text-align:left;" width="20%" valign="center">
																	<input type="text" style="width: 36px; background: #fff; margin: 0" class="form-control" name="">
																</td>
																<td style="min-width: 20%;max-width:20%;padding:0;margin:0; font-size: 8px; line-height: 9px;" width="20%">Per Item</td>
															</tr>
														</tbody>
													</table>
												</td>
											</tr>
											<tr>
												<td style="background: #eff0f5; padding: 2px 3px;">
													<table>
														<tbody>
															<tr>
																<td style="min-width:50%;max-width:50%;text-align:left;" width="50%">Misc. Use</td>
																<td style="min-width:10%;max-width:10%;text-align:left;" width="10%"><b class="doller">$</b></td>
																<td style="min-width:20%;max-width:20%;text-align:left;" width="20%" valign="center">
																	<input type="text" style="width: 36px; background: #fff; margin: 0" class="form-control" name="">
																</td>
																<td style="min-width:20%;max-width:20%;padding: 0;margin: 0;font-size: 8px; line-height: 9px;text-align:left" width="20%">Per <br>
																	_ _ _ _ _</td>
															</tr>
														</tbody>
													</table>
												</td>
											</tr>
											<tr>
												<td style="background: #eff0f5; padding: 2px 3px;">
													<table>
														<tbody>
															<tr>
																<td style="width: 40px;padding:0;margin:0">
																	<table style="width:100%;border:none;margin:0;padding:0;">
																		<tr>
																			<td>Request Manual Printer</td>
																		</tr>
																		<tr>
																			<td>
																				<span style="font-size: 10px; line-height: 10px; margin: 0; font-weight: bold; color: #999;font-size: 6px;margin: 0;">(Imprinter Plate Included)</span>
																			</td>
																		</tr>
																	</table>
																</td>
																<td style="width: 30px; padding: 0" valign="center">
																	<table style="border: none;">
																		<tr>
																			<td>
																				<label style=" text-align: left; display: inline-block;"><input type="radio" style="position: relative; top: 2px; margin: 0 2px 0 0; font-size: 12px; display:inline" name="" checked> No</label>
																				<label style=" text-align: left; display: inline-block;margin-left:5px;"><input type="radio" style="position: relative; top: 2px; margin: 0 2px 0 0; font-size: 12px; display:inline" name=""> Yes</label>
																			</td>
																		</tr>
																		<tr>
																			<span style="width: auto; padding: 0; margin: 0; font-size: 9px; line-height: 9px; color: #999;font-size: 6px;margin: 0;">If yes, a one time fee of $25.00 willbe assessed.</span>
																		</tr>
																	</table>
																</td>
															</tr>
														</tbody>
													</table>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</td>
							<td style="min-width:50%;padding: 0 10px 0 0;border-left: 1px solid #ddd;" valign="top" width="50%">
								<div class="section" style="margin: 10px 0; border: 1px solid #e2e3e8;">
									<h5 style="padding: 0 5px; margin: 5px 0 0; font-size: 13px;">Wireless Terminal Fees (If Applicable):</h5>
									<p style="padding: 0 5px; margin: 0; font-size: 11px">Devices utilizing any connection method outside of any WiFi and Ethernet / IP.</p>
									<table border="0" style="font-size: 12px">
										<tbody>
											<tr>
												<td style="background: #eff0f5; padding: 10px 3px;">
													<table style="width: 100%;" width="100%">
														<tr width="100%">
															<td style="min-width:50%;max-width:50%;text-align:left;" width="50%">Voice Authorization</td>
															<td style="min-width:10%;max-width:10%;text-align:left;" width="10%"><b class="doller">$</b></td>
															<td style="min-width:20%;max-width:20%;text-align:left;" width="20%" valign="middle">25.00</td>
															<td style="min-width:20%;max-width:20%;text-align:left;padding: 0; margin: 0; font-size: 9px; line-height: 9px;" width="20%">Per Item</td>
														</tr>
													</table>
												</td>
											</tr>
											<tr>
												<td style="background: #eff0f5; padding: 10px 3px;">
													<table>
														<tr width="100%">
															<td style="min-width:50%;max-width:50%;text-align:left;" width="50%">Voice Authorization</td>
															<td style="min-width:10%;max-width:10%;text-align:left;" width="10%"><b class="doller">$</b></td>
															<td style="min-width:20%;max-width:20%;text-align:left;" width="20%" valign="middle">25.00</td>
															<td style="min-width:20%;max-width:20%;text-align:left;padding: 0; margin: 0; font-size: 9px; line-height: 9px;" width="20%">Per Item</td>
														</tr>
													</table>
												</td>
											</tr>
											<tr>
												<td style="background: #eff0f5; padding: 10px 3px;">
													<table>
														<tr>
															<td style="min-width:50%;max-width:50%;text-align:left;" width="50%">Voice Authorization</td>
															<td style="min-width:10%;max-width:10%;text-align:left;" width="10%"><b class="doller">$</b></td>
															<td style="min-width:20%;max-width:20%;text-align:left;" width="20%" valign="middle">
																<input type="text" style="width: 36px; background: #fff; margin: 0" class="form-control" name="">
															</td>
															<td style="min-width:20%;max-width:20%;text-align:left;padding: 0; margin: 0; font-size: 9px; line-height: 9px;" width="20%">Per Item</td>
														</tr>
													</table>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<!-- /.Section -->
			<style type="text/css">
				.equip-table td {
					border-bottom: 1px solid #ddd;
					font-size: 11px;
				}
			</style>
		<div class="section page-br-b" style="width: 100%;">			
			<div class="heading" style="margin: 0;font-family:Arial, Helvetica, sans-serif">9. Gateways, Equipment and Processing Methods All gateway fee only apply when applicable, in addition to processing fees and discount rates. </div>			
			<div class="section-table pad-0" style="width:100%"> 
				<table width="100% " cellpadding="0" cellspacing="0" class="table" style="max-width: 100%;"> 
					<tr width="100%"> 
						<td colspan="2" style="padding: 0">
							<table cellpadding="0" cellspacing="0" class="table"> 
								<tr> 
									<td width="33%" style="border-right: 1px solid #ddd; padding: 0; width:calc(100% /3);" valign="top">
										<table class="equip-table" cellpadding="0" cellspacing="0">
											<tr>
												<td colspan="2" align="center" style="padding: 5px 0;">
													<img src="images/eqi-logo-01.png" style="height: 20px;">
												</td>
											</tr>
											<tr>
												<td colspan="2" style="padding: 3px 5px 7px;">
												<label style="display:inline">
													<input type="radio" name="equipment-brand" style="margin: 5px 5px; position: relative; top: 2px;display:inline-block" checked>Authorize.net
												</label>
												</td>
											</tr>
											<tr>
												<td style="font-size: 10px; padding: 0 0 0 5px; height: 22px; line-height: 9px;">Setup Fee</td>
												<td align="right" style="font-weight: bold; padding: 0 5px 0 0;font-size: 10px; ">$20.00</td>
											</tr>
											<tr>
												<td style="font-size: 10px; padding: 0 0 0 5px; height: 22px; line-height: 9px;">Monthly Fee (Per MID)</td>
												<td align="right" style="font-weight: bold; padding: 0 5px 0 0;font-size: 10px; ">$20.00</td>
											</tr>
											<tr>
												<td style="font-size: 10px; padding: 0 0 0 5px; height: 22px; line-height: 9px;">Transaction Fee</td>
												<td align="right" style="font-weight: bold; padding: 0 5px 0 0;font-size: 10px; ">$0.00</td>
											</tr>
											<tr>
												<td style="font-size: 10px; border: 0; padding: 0 0 0 5px; height: 22px; line-height: 9px;">Batch Fee</td>
												<td align="right" style="font-weight: bold; border: 0; padding: 0 5px 0 0;font-size: 10px; ">$0.00</td>
											</tr>
										</table>
									</td>
									<td width="33%" style="border-right: 1px solid #ddd; padding: 0; width: 16.666%" valign="top">
										<table class="equip-table" cellpadding="0" cellspacing="0">
											<tr>
												<td colspan="2" align="center" style="padding: 5px 0;">
													<img src="images/eqi-logo-02.png" style="height: 20px;">
												</td>
											</tr>
											<tr>
												<td  colspan="2" style="padding: 3px 5px 7px;">
													<label style="display: inline;">
														<input type="radio" name="equipment-brand" style="margin: 5px 5px; position: relative; top: 2px;display:inline-block;">Datacap NETePay
													</label>
												</td>
											</tr>
											<tr>
												<td style="font-size: 10px; padding: 0 0 0 5px; height: 22px; line-height: 9px;w">Setup Fee</td>
												<td align="right" style="font-weight:bold;padding: 0 5px 0 0;font-size: 10px; ">$25.00</td>
											</tr>
											<tr>
												<td style="font-size: 10px; padding: 0 0 0 5px; height: 22px; line-height: 9px;">Monthly Fee (Per MID)</td>
												<td align="right" style="font-weight: bold; padding: 0 5px 0 0;font-size: 10px; ">$19.00 
													<table style="border: none;">
														<tr style="border: none;">
															<td style="font-size: 8px; color: #999; font-size: 6px; margin: 0; line-height: 8px;border:none;text-align:right;">First lane</td> 
														</tr>
													</table>
												</td>
											</tr>
											<tr>
												<td style="font-size: 10px; padding: 0 0 0 5px; height: 22px; line-height: 9px;">Additional Lane</td>
												<td align="right" style="font-weight: bold; padding: 0 5px 0 0;font-size: 10px; ">$5.00/per</td>
											</tr>
										</table>
									</td>
									<td width="33%" style="border-right: 1px solid #ddd; padding: 0; width: 16.666%" valign="top">
										<table class="equip-table" cellpadding="0" cellspacing="0">
											<tr>
												<td colspan="2" align="center" style="padding: 5px 0;">
													<img src="images/eqi-logo-03.png" style="height: 20px;">
												</td>
											</tr>
											<tr>
												<td  colspan="2" style="padding: 3px 5px 7px;">
													<label style="display: inline;">
														<input type="radio" name="equipment-brand" style="margin: 5px 5px; position: relative; top: 2px;display:inline-block">USAEPay
													</label>
												</td>
											</tr>
											<tr>
												<td style="font-size: 10px; padding: 0 0 0 5px; height: 22px; line-height: 9px;">Setup Fee</td>
												<td align="right" style="font-weight: bold; padding: 0 5px 0 0;font-size: 10px; ">$20.00</td>
											</tr>
											<tr>
												<td style="font-size: 10px; padding: 0 0 0 5px; height: 22px; line-height: 9px; width: 56px">Monthly Fee (Per MID)</td>
												<td align="right" style="font-weight: bold; padding: 0 5px 0 0; width: 10px;">$12.50</td>
											</tr>
											<tr>
												<td style="font-size: 10px; padding: 0 0 0 5px; height: 22px; line-height: 9px;">Transaction Fee</td>
												<td width="100%" align="right" style="font-weight: bold; padding: 0 5px 0 0;font-size: 10px; ">
												First 5,000 FREE	
												<table style="border: none;">
														<tr width="100%" style="border: none;">
															<td style="font-size: 8px; color: #999; font-size: 5px; margin: 10px 0 0 0;text-align:right;border:none;">$10.00 each additional 5,000</td>
														</tr>
													</table>
												</td>
											</tr>
										</table>
									</td>
								<!-- <tr>
									<td colspan="3">
										<span style="height: 1px;width:100%;background-color:#ddd">

										</span>
									</td>
								</tr> -->
									<td style="border-right: 1px solid #ddd; padding: 0; width: 16.666%" valign="top" width="33%">
										<table class="equip-table" cellpadding="0" cellspacing="0">
											<tr>
												<td colspan="2" align="center" style="padding: 5px 0;">
													<img src="images/eqi-logo-04.png" style="height: 20px;">
												</td>
											</tr>
											<tr>
												<td colspan="2" style="padding: 3px 5px 7px;">
													<label style="display: inline;">
														<input type="radio" name="equipment-brand" style="margin: 5px 5px; position: relative; top: 2px;display:inline-block">NMI
													</label>
												</td>
											</tr>
											<tr>
												<td style="font-size: 10px; padding: 0 0 0 5px; height: 22px; line-height: 9px;">Setup Fee</td>
												<td align="right" style="font-weight: bold; padding: 0 5px 0 0;font-size: 10px; ">$15.00</td>
											</tr>
											<tr>
												<td style="font-size: 10px; padding: 0 0 0 5px; height: 22px; line-height: 9px; width: 56px;">Monthly Fee (Per MID)</td>
												<td align="right" style="font-weight: bold; padding: 0 5px 0 0;font-size: 10px; ">$7.00</td>
											</tr>
											<tr>
												<td style="font-size: 10px; padding: 0 0 0 5px; height: 22px; line-height: 9px;">Transaction Fee</td>
												<td align="right" style="font-weight: bold; padding: 0 5px 0 0;font-size: 10px; ">$0.10
													<table style="border: none;">
														<tr style="border: none;">
															<td style="font-size: 8px; color: #999; font-size: 6px; margin: 0;text-align:right;border:none;">For invoicing and ACH, price is subject to change.</td>
														</tr>
													</table>
												</td>
											</tr>
										</table>
									</td>
									<td style="border-right: 1px solid #ddd; padding: 0; width: 16.666%" valign="top" width="33%">
										<table class="equip-table" cellpadding="0" cellspacing="0">
											<tr>
												<td colspan="2" align="center" style="padding: 5px 0;">
													<img src="images/eqi-logo-05.png" style="height: 20px;">
												</td>
											</tr>
											<tr>
												<td colspan="2" style="padding: 3px 5px 7px;">
													<label for="" style="display: inline;">
														<input type="radio" name="equipment-brand" style="margin: 5px 5px; position: relative; top: 2px;display:inline-block">Charge Anywhere
													</label>
												</td>
											</tr>
											<tr>
												<td style="font-size: 10px; padding: 0 0 0 5px; height: 22px; line-height: 9px;">Setup Fee</td>
												<td align="right" style="font-weight: bold; padding: 0 5px 0 0;font-size: 10px; ">$15.00</td>
											</tr>
											<tr>
												<td style="font-size: 10px; padding: 0 0 0 5px; height: 22px; line-height: 9px;">Monthly Fee (Per MID)</td>
												<td align="right" style="font-weight: bold; padding: 0 5px 0 0;font-size: 10px; ">$10.00</td>
											</tr>
											<tr>
												<td style="font-size: 10px; padding: 0 0 0 5px; height: 22px; line-height: 9px;">Transaction Fee</td>
												<td align="right" style="font-weight: bold; padding: 0 5px 0 0;font-size: 10px; ">$0.00</td>
											</tr>
										</table>
									</td>
									<td style=" padding: 0; width: 16.666%;" valign="top" width="33%">
										<table class="equip-table" cellpadding="0" cellspacing="0" >
											<tr>
												<td colspan="2" align="center" style="padding: 5px 0;">
													<img src="images/eqi-logo-06.png" style="height: 20px;">
												</td>
											</tr>
											<tr>
												<td colspan="2" style="padding: 3px 0px 7px 5px;">
													<label for="" style="display: inline;">
														<input type="radio" name="equipment-brand" style="margin: 5px 5px; position: relative; top: 2px;display:inline-block">QuickBooks Integration
													</label>
												</td>
											</tr>
											<tr>
												<td style="font-size: 10px; padding: 0 0 0 5px; height: 22px; line-height: 9px;">Setup Fee</td>
												<td align="right" style="font-weight: bold; padding: 0 5px 0 0;font-size: 10px; ">$75.00
													<table style="border: none;">
														<tr style="border: none;">
															<td style="font-size: 6px; color: #999; margin: 0;border:none;text-align:right;">One time license Fee</td>
														</tr>
													</table>
												</td>
												</td>
											</tr>
											<tr>
												<td style="font-size: 10px; padding: 0 0 0 5px; height: 22px; line-height: 9px;">Monthly Fee (Per MID)</td>
												<td align="right" style="font-weight: bold; padding: 0 5px 0 0;font-size: 10px; ">$15.00</td>
											</tr>
											<tr>
												<td style="font-size: 10px; padding: 0 0 0 5px; height: 22px; line-height: 9px;">Transaction Fee</td>
												<td align="right" style="font-weight: bold; padding: 0 5px 0 0;font-size: 10px; ">$0.05</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr> 
						<td colspan="2" style="padding: 0; border-top: 1px solid #ddd;">
							<table cellpadding="0" cellspacing="0" class="table"> 
								<tr width="100%" style="min-width: 100%;max-width:100%;">
									<td style="padding: 0 5px; width: 15%" valign="top" width="25%">
										<table cellpadding="0" cellspacing="0">
											<tr>
												<td style="padding: 5px 10px 0 0 ;" valign="top">
													<img src="images/icon-01.png" style="height: 30px;">
												</td>
												<td style="padding: 5px 0 0;">
													<table cellpadding="0" cellspacing="0">
														<tr>
															<td style="padding: 5px 10px; background: #eee;">
																Terminal (Countertop)	
															</td>
														</tr>
														<tr>
															<td style="padding: 5px 0;">
																							<table style="border: none;">
																	<tr style="border: none;">
																		<td style="border: none;"><label style="display: inline;"><input type="radio" name="" style="display: inline;" checked> New</label></td>
																		<td style="border: none;"><label style="display: inline;"><input type="radio" name="" style="display: inline;"> Existing</label></td>
																	</tr>
																</table>
															</td>
														</tr>
														<tr>
															<td style="padding: 5px 0 0;">
																<table cellpadding="0" cellspacing="0">
																	<tr>
																		<td style="padding: 5px 0;">
																			Make
																		</td>
																		<td>
																			<input type="text" class="form-control" style="height: 20px;width:100%;">
																		</td>
																	</tr>
																	<tr>
																		<td style="padding: 5px 0;">
																			Model
																		</td>
																		<td>
																			<input type="text" class="form-control" style="height: 20px;width:100%;">
																		</td>
																	</tr>
																	<tr>
																		<td style="padding: 5px 0 0;">
																			QTY
																		</td>
																		<td>
																			<input type="text" class="form-control" style="height: 20px;width:100%;" value="23">
																		</td>
																	</tr>
																</table>
															</td>
														</tr>
													</table>
												</td>
											</tr>
										</table>
									</td>	
									<td style="padding: 0 5px; width: 15%" valign="top" width="25%">
										<table cellpadding="0" cellspacing="0">
											<tr>
												<td style="padding: 5px 10px 0 0 ;" valign="top">
													<img src="images/icon-02.png" style="height: 30px;">
												</td>
												<td style="padding: 5px 0 0;">
													<table cellpadding="0" cellspacing="0">
														<tr>
															<td style="padding: 5px 10px; background: #eee;">
																Terminal (Countertop)	
															</td>
														</tr>
														<tr>
															<td style="padding: 5px 0;">
																							<table style="border: none;">
																	<tr style="border: none;">
																		<td style="border: none;"><label style="display: inline;"><input type="radio" name="" style="display: inline;"> New</label></td>
																		<td style="border: none;"><label style="display: inline;"><input type="radio" name="" style="display: inline;"> Existing</label></td>
																	</tr>
																</table>
															</td>
														</tr>
														<tr>
															<td style="padding: 5px 0 0;">
																<table cellpadding="0" cellspacing="0">
																	<tr>
																		<td style="padding: 5px 0;">
																			Make
																		</td>
																		<td>
																			<input type="text" class="form-control" style="height: 20px;width:100%;">
																		</td>
																	</tr>
																	<tr>
																		<td style="padding: 5px 0;">
																			Model
																		</td>
																		<td>
																			<input type="text" class="form-control" style="height: 20px;width:100%;">
																		</td>
																	</tr>
																	<tr>
																		<td style="padding: 5px 0 0;">
																			QTY
																		</td>
																		<td>
																			<input type="text" class="form-control" style="height: 20px;width:100%;" value="23">
																		</td>
																	</tr>
																</table>
															</td>
														</tr>
													</table>
												</td>
											</tr>
										</table>
									</td>	
									<td style="padding: 0 5px; width: 15%" valign="top" width="25%">
										<table cellpadding="0" cellspacing="0">
											<tr>
												<td style="padding: 5px 10px 0 0 ;" valign="top">
													<img src="images/icon-03.png" style="height: 30px;">
												</td>
												<td style="padding: 5px 0 0;">
													<table cellpadding="0" cellspacing="0">
														<tr>
															<td style="padding: 5px 10px; background: #eee;">
																Terminal (Countertop)	
															</td>
														</tr>
														<tr>
															<td style="padding: 5px 0;">
																							<table style="border: none;">
																	<tr style="border: none;">
																		<td style="border: none;"><label style="display: inline;"><input type="radio" name="" style="display: inline;"> New</label></td>
																		<td style="border: none;"><label style="display: inline;"><input type="radio" name="" style="display: inline;"> Existing</label></td>	
																	</tr>
																</table>
															</td>
														</tr>
														<tr>
															<td style="padding: 5px 0 0;">
																<table cellpadding="0" cellspacing="0">
																	<tr>
																		<td style="padding: 5px 0;">
																			Make
																		</td>
																		<td>
																			<input type="text" class="form-control" style="height: 20px;width:100%;">
																		</td>
																	</tr>
																	<tr>
																		<td style="padding: 5px 0;">
																			Model
																		</td>
																		<td>
																			<input type="text" class="form-control" style="height: 20px;width:100%;">
																		</td>
																	</tr>
																	<tr>
																		<td style="padding: 5px 0 0;">
																			QTY
																		</td>
																		<td>
																			<input type="text" class="form-control" style="height: 20px;width:100%;" value="235">
																		</td>
																	</tr>
																</table>
															</td>
														</tr>
													</table>
												</td>
											</tr>
										</table>
									</td>	
									<td style="padding: 0 5px; width: 15%" valign="top" width="25%">
										<table cellpadding="0" cellspacing="0">
											<tr>
												<td style="padding: 5px 10px 0 0 ;" valign="top">
													<img src="images/icon-04.png" style="height: 30px;">
												</td>
												<td style="padding: 5px 0 0;">
													<table cellpadding="0" cellspacing="0">
														<tr>
															<td style="padding: 5px 10px; background: #eee;">
																Terminal (Countertop)	
															</td>
														</tr>
														<tr>
															<td style="padding: 5px 0;">
																							<table style="border: none;">
																	<tr style="border: none;">
																		<td style="border: none;"><label style="display: inline;"><input type="radio" name="" style="display: inline;"> New</label></td>
																		<td style="border: none;"><label style="display: inline;"><input type="radio" name="" style="display: inline;"> Existing</label></td>
																	</tr>
																</table>
															</td>
														</tr>
														<tr>
															<td style="padding: 5px 0 0;">
																<table cellpadding="0" cellspacing="0">
																	<tr>
																		<td style="padding: 5px 0;">
																			Make
																		</td>
																		<td>
																			<input type="text" class="form-control" style="height: 20px;width:100%;">
																		</td>
																	</tr>
																	<tr>
																		<td style="padding: 5px 0;">
																			Model
																		</td>
																		<td>
																			<input type="text" class="form-control" style="height: 20px;width:100%;">
																		</td>
																	</tr>
																	<tr>
																		<td style="padding: 5px 0 0;">
																			QTY
																		</td>
																		<td>
																			<input type="text" class="form-control" style="height: 20px;width:100%;" value="65">
																		</td>
																	</tr>
																</table>
															</td>
														</tr>
													</table>
												</td>
											</tr>
										</table>
									</td>	
								</tr>
								<tr>
									<table cellpadding="0" cellspacing="0">
										<tr>
											<td style="width: 20%;">
												Print Legal Name	
											</td>
											<td style="padding: 5px 7px 0 5px">
												<input type="text" class="form-control" style="height: 22px;width:100%;" name="">	
											</td>
										</tr>
									</table>
								</tr>
							</table>
						</td>
					</tr>
					<style type="text/css">
						.bg-table{
							border-collapse:seperate;
						}
						.bg-table *{
							border: none;
						}
						.bg-table td { border: 2px solid #fff; background: #f5f5f5; color: #5f6061; font-weight: bold; }
					</style>
				</table>
				<table width="100% " cellpadding="0" cellspacing="0" class="table" style="max-width: 100%;"> 
					<tr width="100%">
						<td width="100%"> 
							<table cellpadding="0" cellspacing="0" width="100%" class="table">
								<tr width="100%">
									<td width="100%" style="padding: 5px 8px; border-top: 1px solid #ddd; border-right: 1px solid #ddd; font-weight: bold;">
										<table cellpadding="0" cellspacing="0" width="400" class="table">
											<tr>
												<td style="padding: 0 5px 3px 0;">
													Network (Front End):
												</td>
											</tr>
											<tr>
												<td style="padding: 0">
													<table cellpadding="0" cellspacing="0" class="table">
														<tr>
															<td style="background: #f5f5f5; padding: 5px 8px;">
																<table style="border: none;">
																	<tr style="border: none;">
																		<td style="margin: 0 5px 0 0;font-size:8px;"> 
																			<input type="radio" style="margin: 0; position: relative; top: 2px;display:inline;" name="network"> Omaha 
																		</td>
																		<td style="font-size: 8px;">
																			<input type="radio" style="margin: 0; position: relative; top: 2px;display:inline;" name="network" checked> North (Cardnet)
																		</td>
																		<td style="font-size: 8px;">
																			<input type="radio" style="margin: 0; position: relative; top: 2px;display:inline;" name="network"> Nashville </label>
																		</td>
																		<td style="font-size:8px;">
																			<input type="radio" style="margin: 0; position: relative; top: 2px;display:inline;" name="network"> Buypass </label>
																		</td>
																	</tr>
																</table>
															</td>
														</tr>
													</table>
												</td>
											</tr>
											<tr>
												<td style="padding: 10px 5px 3px 0;">
													Encryption Keys:
												</td>
											</tr>
											<tr>
												<td style="padding: 0 0 2px">
													<table cellpadding="0" cellspacing="0" class="table">
														<tr>
															<td style="background: #f5f5f5; padding: 5px 8px;">
																<label style="margin: 0 15px 0 0;display:inline"> 
																	<input type="radio" style="margin: 0 5px 0 0; position: relative; top: 2px;display:inline;" name="network" checked> TD1073 
																</label>
															</td>
														</tr>
													</table>
												</td>
											</tr>
										</table>	
									</td>
									<!-- chk -->
								</tr>
								<tr width="100%">
										<td width="50%" style="padding: 5px 8px; border-top: 1px solid #ddd; font-weight: bold;" valign="top">
										<table cellpadding="0" cellspacing="0" class="table" width="100%">
											<tr style="vertical-align: middle;">
												<td colspan="2" style="padding: 0 5px 3px 0;vertical-align:middle">
													Tokenization Data Security 
												</td>
											</tr>
											<tr>												
												<td width="50%">
													<table cellpadding="0" cellspacing="0" class="table">
														<tr>
															<td style="padding: 0;">
																<table cellpadding="0" cellspacing="0" class="table">
																	<tr>
																		<td style="padding: 0;"> 
																			<table cellpadding="0" cellspacing="0" class="table" style="background: #f5f5f5; font-size: 10px; line-height: 10px; color: #333; font-weight: bold;padding: 0 10px;">
																				<tr style="vertical-align: middle;">
																					<td style="vertical-align: middle;"> 
																						<label style="margin: 0 0 0 10px;"> 
																							<input type="radio" style="margin: 0; position: relative; top: -7px;" name="network"> 
																						</label>	
																					</td>
																					<td style="padding: 7px 2px" style="vertical-align: middle;">
																						<img src="images/transarmor.png" alt="" style="height: 15px;" >
																					</td>
																					<td style="padding: 5px 0;" style="vertical-align: middle;">
																						Clover Security Plus Fee
																					</td>
																					<td style="vertical-align: middle;">
																						$20.00/MO
																					</td>
																				</tr>
																			</table>
																		</td>
																	</tr>
																</table>
															</td>															
														</tr>
														<tr>
															<td>
																<tr>
																	<td colspan="2" style="padding: 0 5px 8px 10px; background: #f5f5f5; color: #777;">
																		*Required when using Datacap NETePay
																	</td>
																</tr>
															</td>
														</tr>

													</table>
												</td>
												<td width="50%">
													<table cellpadding="0" cellspacing="0" class="table">
														<tr>
															<td style="padding: 0;">
																<table cellpadding="0" cellspacing="0" class="table">
																	<tr>
																		<td style="padding: 0;"> 
																			<table cellpadding="0" cellspacing="0" class="table" style="background: #f5f5f5; font-size: 10px; line-height: 10px; color: #333; font-weight: bold;padding:0 10px;">
																				<tr>
																					<td> 
																						<label style="margin: 0 0 0 10px;"> 
																							<input type="radio" style="margin: 0 0 0 0; position: relative; top:-6px;" name="network"> 
																						</label>	
																					</td>
																					<td style="padding: 7px 2px;vertical-align:middle;">
																						<img src="images/clover.png" alt="" style="height: 15px;" >
																					</td>
																					<td style="padding: 5px 0;vertical-align:middle">
																						Clover Security Plus Fee
																					</td>
																					<td>
																						$20.00/MO
																					</td>
																				</tr>
																			</table>
																		</td>
																	</tr>
																</table>
															</td>															
														</tr>

														<tr>
															<td>
																<tr>
																	<td colspan="2" style="padding: 0 5px 8px 10px; background: #f5f5f5; color: #777;">
																		*Required when using any Clover device. 
																	</td>
																</tr>
															</td>
														</tr>

													</table>
												</td>
											</tr>
										</table>	
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
				<table width="100% " cellpadding="0" cellspacing="0" class="table" style="min-width: 100%;max-width:100%;"> 
					<tr width="100%" style="min-width: 100%;max-width:100%;"> 
						<td width="100%" style="min-width: 100%;max-width:100%;">
							<table cellpadding="0" cellspacing="0" class="table" width="100%" style="min-width: 100%;max-width:100%;">
								<tr width="100%" style="min-width: 100%;max-width:100%;">
									<td  width="50%" style="padding: 0; border-top: 1px solid #ddd; border-right: 1px solid #ddd; min-width:50%;max-width:50%">
										<p style="text-align: center; padding: 0 10px; margin: 0; color: #999; font-size: 9px; font: bold 10px 'Arial';">TekPOS Software Pricing Choose One</p>
										<table cellpadding="0" cellspacing="0" class="table bg-table" style="font: bold 9px 'Arial';font-family:Arial, Helvetica, sans-serif;min-width:100%;max-width:100%;margin:0;padding:0">
											<tr width="100%" style="min-width: 100%;max-width:100%;">
												<td style="padding: 5px; background: #fff;min-width:20%;max-width:20%;" width="20%">
													<img src="images/tekpos-logo.png">
												</td>
												<td style="padding: 5px; background: #ebebec; text-align: center;min-width:20%;max-width:20%;" width="20%">
													QTY<span style="font-size: 6px; color: #999; display: block; margin: 0">(Per iPad)</span>
												</td>
												<td style="padding: 5px; background: #ebebec;min-width:20%;max-width:20%;"  width="20%">
													<input type="radio" name="">
													Monthly Rate<span style="font-size: 6px; color: #999; display: block; margin: 0;">(Per Register)</span>
												</td>
												<td style="padding: 5px; background: #ebebec;min-width:20%;max-width:20%;"  width="20%">
													<input type="radio" name="" checked>
													Annual Rate<span style="font-size: 6px; color: #999; display: block; margin: 0;">(Per Register)</span>
												</td>
												<td style="padding: 5px; background: #ebebec; text-align: center;min-width:20%;max-width:20%;"  width="20%">
													Activatation Date
												</td>
											</tr>

											<style type="text/css">
												.qty-field { height: 24px; width: 100%; max-width: 50px; border: none; border-radius: 0; background: #ebeef8; margin: 0; display: block; text-align: center; font-size: 12px }
												input:focus { outline: 0; }
											</style>
											<tr width="100%" style="min-width: 50%;max-width:100%">
												<td style="padding: 5px;min-width:20%;max-width:20%;"  width="20%">
													<input type="radio" name="" style="margin: 0; position: relative; top: 2px" checked> Annual Rate
												</td>
												<td style="padding: 0; background: #ebeef8; width: 50px;" width="13.3%">
													<input type="text" class="qty-field">
												</td>
												<td style="padding: 5px; text-align: center;">													
													FREE
													<span style="font-size: 6px; color: #999; display: block; margin: 0;">1 Register / 1 User Only</span>
												</td>
												<td style="padding: 5px; text-align: center;">													
													FREE
													<span style="font-size: 6px; color: #999; display: block; margin: 0;">1 Register / 1 User Only</span>
												</td>
												<td style="padding: 0; background: #ebeef8; width: 50px;">
													<input type="text" class="qty-field" style="width: 100%; max-width: 100%">
												</td>
											</tr>
											<tr>
												<td style="padding: 5px;">
													<input type="radio" name="" style="margin: 0; position: relative; top: 2px"> Basic
												</td>
												<td style="padding: 0; background: #ebeef8; width: 50px;">
													<input type="text" class="qty-field">
												</td>
												<td style="padding: 5px; text-align: center;">													
													$39.99/MO
													<span style="font-size: 6px; color: #999; display: block; margin: 0;">(Per Register)</span>
												</td>
												<td style="padding: 5px; text-align: center;">													
													$19.99/MO
													<span style="font-size: 6px; color: #999; display: block; margin: 0;">(Per Register)</span>
												</td>
												<td style="padding: 0; background: #ebeef8; width: 50px;">
													<input type="text" class="qty-field" style="width: 100%; max-width: 100%">
												</td>
											</tr>	
											<tr>
												<td style="padding: 5px;">
													<input type="radio" name="" style="margin: 0; position: relative; top: 2px"> Pro
												</td>
												<td style="padding: 0; background: #ebeef8; width: 50px;">
													<input type="text" class="qty-field">
												</td>
												<td style="padding: 5px; text-align: center;">													
													$69.99/MO
													<span style="font-size: 6px; color: #999; display: block; margin: 0;">(Per Register)</span>
												</td>
												<td style="padding: 5px; text-align: center;">													
													$49.99/MO
													<span style="font-size: 6px; color: #999; display: block; margin: 0;">(Per Register)</span>
												</td>
												<td style="padding: 0; background: #ebeef8; width: 50px;">
													<input type="text" class="qty-field" style="width: 100%; max-width: 100%">
												</td>
											</tr>
											<tr>
												<td style="padding: 5px;">
													<input type="radio" name="" style="margin: 0; position: relative; top: 2px"> Enterprise
												</td>
												<td style="padding: 0; background: #ebeef8; width: 50px;">
													<input type="text" class="qty-field">
												</td>
												<td style="padding: 5px; text-align: center;">													
													$109.99/MO
													<span style="font-size: 6px; color: #999; display: block; margin: 0;">(Per Register)</span>
												</td>
												<td style="padding: 5px; text-align: center;">													
													$89.99/MO
													<span style="font-size: 6px; color: #999; display: block; margin: 0;">(Per Register)</span>
												</td>
												<td style="padding: 0; background: #ebeef8; width: 50px;">
													<input type="text" class="qty-field" style="width: 100%; max-width: 100%">
												</td>
											</tr>	
										</table>
									</td>
									<td width="70%" style="padding: 0; border-top: 1px solid #ddd; border-right: 1px solid #ddd; min-width: 50%;max-width:50%;">
										<table cellpadding="0" cellspacing="0" class="table bg-table" style="font: bold 9px 'Arial';">
											<tr width="100%">
												<td style="padding: 5px; background: #fff;min-width:20%;max-width:20%" width="20%">
													<img src="images/clover-lg-logo.png">
												</td>
												<td style="padding: 5px; background: #ebebec; text-align: center;" >
													QTY
												</td>
												<td style="padding: 5px; background: #ebebec; text-align: center;" >
													Device Price
												</td>
												<td style="padding: 5px; background: #ebebec; text-align: center;" >
													Clover Fees
												</td>
												<td style="padding: 5px; background: #ebebec; text-align: center;" >
													Deployment Fee
												</td>
												<td style="padding: 5px; background: #ebebec; text-align: center;" width="16%"> 
													Monthly Software Fee
												</td>
												<td style="padding: 0; height: 33px;  background: #ebebec; text-align: center;">
													Protection Program
													<span style="font-size: 5px; color: #999; display: block; margin: 0;">
													Only applicable to new devices. Priced per device.</span>
												</td>
											</tr>
											<tr>
												<td style="padding: 5px 0 5px 5px;">
													<input type="radio" name="" style="margin: 0; position: relative; top: 2px" checked>
													Clover Station												
												</td>
												<td style="padding: 0; background: #ebeef8; width: 35px;">
													<input type="text" class="qty-field" style="width: 100%; max-width: 100%">
												</td>												
												<td style="padding: 0; background: #ebeef8; width: 40px;">
													<input type="text" class="qty-field" style="width: 100%; max-width: 100%">
												</td>
												<td style="padding: 0; background: #ebeef8; width: 50px;">
													<input type="text" class="qty-field" style="width: 100%; max-width: 100%">
												</td>
												<td style="padding: 0; background: #ebeef8; width: 50px;">
													<input type="text" class="qty-field" style="width: 100%; max-width: 100%">
												</td>
												<td style="padding: 0; background: #ebeef8; width: 50px;">
													<input type="text" class="qty-field" style="width: 100%; max-width: 100%">
												</td>
												<td style="padding: 5px; text-align: center;">													
													$150.00
													<span style="font-size: 6px; color: #999; display: block; margin: 0;">(3 Year Coverage)</span>
												</td>
											</tr>
											<tr>
												<td style="padding: 5px;">
													<input type="radio" name="" style="margin: 0; position: relative; top: 2px"> Clover Mini
												</td>
												<td style="padding: 0; background: #ebeef8; width: 35px;">
													<input type="text" class="qty-field" style="width: 100%; max-width: 100%">
												</td>												
												<td style="padding: 0; background: #ebeef8; width: 40px;">
													<input type="text" class="qty-field" style="width: 100%; max-width: 100%">
												</td>
												<td style="padding: 0; background: #ebeef8; width: 50px;">
													<input type="text" class="qty-field" style="width: 100%; max-width: 100%">
												</td>
												<td style="padding: 0; background: #ebeef8; width: 50px;">
													<input type="text" class="qty-field" style="width: 100%; max-width: 100%">
												</td>
												<td style="padding: 0; background: #ebeef8; width: 50px;">
													<input type="text" class="qty-field" style="width: 100%; max-width: 100%">
												</td>
												<td style="padding: 5px; text-align: center;">													
													$110.00
													<span style="font-size: 6px; color: #999; display: block; margin: 0;">(3 Year Coverage)</span>
												</td>
											</tr>
											<tr>
												<td style="padding: 5px;">
													<input type="radio" name="" style="margin: 0; position: relative; top: 2px"> Clover Flex
												</td>
												<td style="padding: 0; background: #ebeef8; width: 35px;">
													<input type="text" class="qty-field" style="width: 100%; max-width: 100%">
												</td>												
												<td style="padding: 0; background: #ebeef8; width: 40px;">
													<input type="text" class="qty-field" style="width: 100%; max-width: 100%">
												</td>
												<td style="padding: 0; background: #ebeef8; width: 50px;">
													<input type="text" class="qty-field" style="width: 100%; max-width: 100%">
												</td>
												<td style="padding: 0; background: #ebeef8; width: 50px;">
													<input type="text" class="qty-field" style="width: 100%; max-width: 100%">
												</td>
												<td style="padding: 0; background: #ebeef8; width: 50px;">
													<input type="text" class="qty-field" style="width: 100%; max-width: 100%">
												</td>
												<td style="padding: 5px; text-align: center;">													
													$90.00
													<span style="font-size: 6px; color: #999; display: block; margin: 0;">(3 Year Coverage)</span>
												</td>
											</tr>
											<tr>
												<td style="padding: 0 5px; height: 33px">
													<input type="radio" name="" style="margin: 0; position: relative; top: 2px"> 
													Clover Go
													<span style="font-size: 6px; color: #999; display: block; margin: 0 0 0 15px;">(Mobile)</span>
												</td>
												<td style="padding: 0; background: #ebeef8; width: 35px;">
													<input type="text" class="qty-field" style="width: 100%; max-width: 100%">
												</td>												
												<td style="padding: 0; background: #ebeef8; width: 40px;">
													<input type="text" class="qty-field" style="width: 100%; max-width: 100%">
												</td>
												<td style="padding: 0; background: #ebeef8; width: 50px;">
													<input type="text" class="qty-field" style="width: 100%; max-width: 100%">
												</td>
												<td style="padding: 0; background: #ebeef8; width: 50px;">
													<input type="text" class="qty-field" style="width: 100%; max-width: 100%">
												</td>
												<td style="padding: 0; background: #ebeef8; width: 50px;">
													<input type="text" class="qty-field" style="width: 100%; max-width: 100%">
												</td>
												<td style="padding: 5px; text-align: center;">													
													N/A
												</td>
											</tr>
										</table>
									</td>
								</tr>	
							</table>
						</td>						
					</tr> 
				</table>
			</div>
		</div>
			<!-- /.Section -->
			<!-- Section -->
			<div class="section page-br-b">
				<div class="heading" style="margin: 0 0 5px;font-family:Arial, Helvetica, sans-serif">
					10A. Certification of Beneficial Owner(s) <span style="font-size: 10px; font-weight: normal; margin: 0 0 0 10px;">Persons opening an account or maintaining a business relationship on behalf of the legal entity must provide the following information</span>
				</div>
				<p style="padding: 0 10px; margin: 0; color: #999; font-size: 10px; font: bold 10px 'Arial';">A copy of a valid Drivers License and/or Government Issued ID number is required for each beneficiary owner in the Beneficiary Owner Addendum Form.
				</p>
				<div class="sub-heading" style="margin: 5px 0 5px; background: #ebebec; padding: 3px 10px; color: #3e4042; font: bold 10px 'Arial';">
					I. Person opening or requesting maintenance on account (Required)
				</div>
				<div class="section-table ">

					<table cellpadding="0" cellspacing="0" class="table">
						<tr>
							<td style="width: 33%;">
								First Name
								<input type="text" class="form-control" name="" value="Frank">
							</td>
							<td style="width: 33%">
								Last Name
								<input type="text" class="form-control" name="" value=" Ronan">
							</td>
							<td style="width: 33%">
								Title
								<input type="text" class="form-control" name="">
							</td>
						</tr>
					</table>
				</div>
				<div class="sub-heading" style="margin: 5px 0 5px; background: #ebebec; padding: 3px 10px; color: #3e4042; font: bold 10px 'Arial';">I. Person opening or requesting maintenance on account (Required)
				</div>
				<div class="section-table pad">

					<div class="ownership-info" style="position: relative;">
						<div class="" style="background: #3ab54a; color: #fff; position: absolute; margin: 38px 0 -24px -6px; width: 126px; height: 17px; left: -72px; transform: rotate(-90deg); top: 14px; text-align: center;">Beneficiary#1</div>
						<table cellpadding="0" cellspacing="0" class="table">
							<tr>
								<td>
									<table cellpadding="0" cellspacing="0" class="table">
										<tr width="100%">
											<td>
												First Name
												<input type="text" class="form-control" name="" value="Frank">
											</td>
											<td>
												Last Name
												<input type="text" class="form-control" name="" value=" Ronan">
											</td>
											<td>
												M.I.
												<input type="text" class="form-control" name="">
											</td>
										</tr>
										<tr width="100%">
											<td>
												D.O.B.
												<input type="text" class="form-control" name="" value="11-21-1976">
											</td>
											<td>
												Ownership %
												<input type="text" class="form-control" name="" value="50">
											</td>
											<td>
												SSN (US Persons)
												<input type="text" class="form-control" name="" value="078-05-1120">
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td>
									<table cellpadding="0" cellspacing="0" class="table">
										<tr>
											<td style="width:33%">
												Address (No P.O. Box)
												<input type="text" class="form-control" name="" value="Chapman 711-2880 Nulla St. Mankato Mississippi">
											</td>
											<td style="width: 33%">
												City
												<input type="text" class="form-control" name="" value="Miami">
											</td>
											<td style="width: 33%">
												State
												<input type="text" class="form-control" name="" value="Florida">
											</td>
										</tr>
										<tr>
											<td colspan="3" style="margin:0;padding:0">
												<table>
													<tr>
														<td style="width: 50%">
													Zip Code
													<input type="text" class="form-control" name="" value="40170">
												</td>
												<td style="width: 50%">
													Phone Number
													<input type="text" class="form-control" name="" value="+1-541-754-3010">
												</td>
													</tr>
												</table>
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td>
									<table cellpadding="0" cellspacing="0" class="table">
										<tr>
											<td style="width:33%">
												Email Address
												<input type="text" class="form-control" name="" value="testemail@test.com">
											</td>
											<td style="width:33%">
												ID Type
												<input type="text" class="form-control" name="" value="Passport">
											</td>
											<td style="width:33%">
												ID #
												<input type="text" class="form-control" name="">
											</td>
										</tr>
										<tr>
											<td colspan="3" style="margin:0;padding:0">
												<table>
													<tr>
														<td style="width:50%">
													Issuing State
													<input type="text" class="form-control" name="" value="New York">
												</td>
												<td style="width:50%">
													Passport # (Non-Use Citizens)
													<input type="text" class="form-control" name="">
												</td>
													</tr>
												</table>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</div>


					<div class="ownership-info" style="position: relative;">
						<div class="" style="background: #3ab54a; color: #fff; position: absolute; margin: 38px 0 -24px -6px; width: 126px; height: 17px; left: -72px; transform: rotate(-90deg); top: 19px; text-align: center;">Beneficiary#2</div>
						<table cellpadding="0" cellspacing="0" class="table" style="position: relative;">
							<tr>
								<td>
									<table cellpadding="0" cellspacing="0" class="table">
										<tr>
											<td style="width:33%">
												First Name
												<input type="text" class="form-control" name="" value="Frank">
											</td>
											<td style="width:33%">
												Last Name
												<input type="text" class="form-control" name="" value=" Ronan">
											</td>
											<td style="width:33%">
												M.I.
												<input type="text" class="form-control" name="">
											</td>
										</tr>
										<tr>
											<td style="width: 33%">
												D.O.B.
												<input type="text" class="form-control" name="" value="11-21-1976">
											</td>
											<td style="width: 33%">
												Ownership %
												<input type="text" class="form-control" name="" value="90">
											</td>
											<td style="width: 33%">
												SSN (US Persons)
												<input type="text" class="form-control" name="" value="078-05-1120">
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td>
									<table cellpadding="0" cellspacing="0" class="table">
										<tr>
											<td style="width:33%">
												Address (No P.O. Box)
												<input type="text" class="form-control" name="" value="Chapman 711-2880 Nulla St. Mankato Mississippi">
											</td>
											<td style="width:33%">
												City
												<input type="text" class="form-control" name="" value="New York">
											</td>
											<td style="width:33%">
												State
												<input type="text" class="form-control" name="" value="New York">
											</td>
										</tr>
										<tr>
										<td colspan="3" width="100%" style="margin:0;padding:0">
											<table cellpadding="0" cellspacing="0" width="100% ">
												<tr width="100%">
													<td style="width:50%">
													Zip Code
													<input type="text" class="form-control" name="" value="40170">
												</td>
												<td style="width:50%">
													Phone Number
													<input type="text" class="form-control" name="" value="+1-541-754-3010">
												</td>
												</tr>
											</table>
										</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr style="margin:0">
								<td style="margin: 0;padding:0">
									<table cellpadding="0" cellspacing="0" class="table">
										<tr>
											<td style="width: 33%">
												Email Address
												<input type="text" class="form-control" name="" value="testemail@test.com">
											</td>
											<td style="width: 33%">
												ID Type
												<input type="text" class="form-control" name="" value="Passport">
											</td>
											<td style="width: 33%">
												ID #
												<input type="text" class="form-control" name="">
											</td>
										</tr>
										<tr>
										<td colspan="3" style="margin: 0;padding:0;border:none" width="100%">
											<table cellpadding="0" cellspacing="0" style="margin:0;padding:0;border:none" width="100%">
												<tr style="margin: 0;padding:0;border:none" width="100%">
													<td style="width:50%">
													Issuing State
													<input type="text" class="form-control" name="" value="New York">
												</td>
												<td style="width:50%">
													Passport # (Non-Use Citizens)
													<input type="text" class="form-control" name="">
												</td>
												</tr>
											</table>
										</td>	
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</div>
				</div>

				<div class="sub-heading" style="margin: 5px 0 5px; background: #ebebec; padding: 3px 10px; color: #888; font: bold 9px 'Arial';">*For any additional owners who have 25% or more ownership, separate Beneficiary Addendum must be signed and submitted
				</div>

			</div>
			<!-- /.Section -->
			<!-- Section -->
			<div class="section ">
				<div class="heading" style="margin: 0 0 5px;font-family:Arial, Helvetica, sans-serif">
					10B. Managing / Directing Responsibility (Required)
				</div>
				<p style="padding: 0 10px; margin: 0; color: #999; font-size: 10px; font: bold 10px 'Arial';">Provide information below for one individual with significant responsibility for managing or directing the legal entity previously listed on this form, such as, an executive officer or senior manager (e.g. Chief Executive Officer, Chief Financial Officer, Chief Operating Officer, Managing Member, General Partner, President, Vice President, Treasurer); or Any other individual who regularly performs similar functions. If appropriate, an individual listed in C: BENEFICIAL OWNERSHIP INFORMATION (above) may be listed in this section. Individual with significant control:
				</p>
				<div class="sub-heading" style="margin: 5px 0 5px; background: #ebebec; padding: 3px 10px 6px; color: #3e4042; font: bold 10px 'Arial';">
					<table style="border: none;vertical-align:middle;" cellpadding="0" cellspacing="0" class="table">
						<tr style="vertical-align: middle;">
							<td style="vertical-align:middle">
								<table style="vertical-align: middle;" cellpadding="0" cellspacing="0" class="table">
									<tr style="vertical-align: middle;">
										<td style="vertical-align: middle;">
											<input type="radio" name="" style="margin-bottom: 20px;margin-right:5px;">
										</td>
										<td style="vertical-align: middle;margin-left:5px;">
											<span style="font-size: 8px;margin-left:5px;">Check here if same as 10A. If so, specify which Beneficiary Owner:</span>
										</td>
									</tr>
								</table>
							</td>
							<td style="vertical-align:middle">
								<table style="border: none;" cellpadding="0" cellspacing="0" class="table">
									<td style="vertical-align:middle">
										<input type="radio" name="" style="margin-bottom: 20px;margin-right:5px;"> 
									</td>
									<td style="vertical-align: middle;margin-left:5px;">
										<span style="font-size: 8px;margin-left:5px;">Beneficiary #1:</span>
									</td>
								</table>
							</td>
							<td style="vertical-align:middle">
								<table style="border: none;" cellpadding="0" cellspacing="0" class="table">
									<tr style="vertical-align: middle;">
										<td style="vertical-align:middle">
											<input type="radio" name="" style="margin-bottom: 20px;margin-right:5px;"> 
										</td>
										<td style="vertical-align: middle;margin-left:5px;">
											<span style="font-size: 8px;margin-left:5px;">Beneficiary #2</span>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr style="vertical-align: middle;">
						<td style="vertical-align:middle">
								<table style="border: none;vertical-align:middle" cellpadding="0" cellspacing="0" class="table">
									<tr style="vertical-align:middle">
										<td style="vertical-align:middle;margin:0;">
											<input type="radio" name="" style="margin-bottom: 20px;margin-right:5px;"> 
										</td>
										<td style="vertical-align: middle;margin:0;">
											<span style="font-size: 8px;margin:0;">Beneficiary #3</span>
										</td>
									</tr>
								</table>
						</td>
							<td style="vertical-align:middle">
								<table style="vertical-align: middle;border:none" cellpadding="0" cellspacing="0" class="table">
									<tr style="border: none;vertical-align:middle">
										<td style="vertical-align: middle;">
											<input type="radio" name="" style="margin-bottom: 20px;margin-right:5px;"> 
										</td>
										<td style="vertical-align: middle;border:none">
											<span style="font-size: 8px;margin-left:5px;">Beneficiary #4</span>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</div>
				<div class="section-table ">

					<table cellpadding="0" cellspacing="0" class="table">
						<tbody>
							<tr>
								<td>
									<table cellpadding="0" cellspacing="0" class="table">
										<tbody>
											<tr>
												<td style="width:33%">
													First Name
													<input type="text" class="form-control" name="" value="Frank">
												</td>
												<td style="width:33%">
													Last Name
													<input type="text" class="form-control" name="" value="Ronan">
												</td>
												<td style="width:33%">
													M.I.
													<input type="text" class="form-control" name="">
												</td>
											</tr>
											<tr>
												<td style="width:33%">
													D.O.B.
													<input type="text" class="form-control" name="" value="11-21-1976">
												</td>
												<td style="width:33%">
													Ownership %
													<input type="text" class="form-control" name="" value="80">
												</td>
												<td style="width:33%">
													SSN (US Persons)
													<input type="text" class="form-control" name="" value="078-05-1120">
												</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
							<tr>
								<td>
									<table cellpadding="0" cellspacing="0" class="table">
										<tbody>
											<tr>
												<td style="width: 33%">
													Address (No P.O. Box)
													<input type="text" class="form-control" name="" value="Chapman 711-2880 Nulla St. Mankato Mississippi">
												</td>
												<td style="width: 33%">
													City
													<input type="text" class="form-control" name="" value="Miami">
												</td>
												<td style="width: 33%">
													State
													<input type="text" class="form-control" name="" value="Florida">
												</td>
											</tr>
											<tr>
												<td colspan="3" style="margin:0;padding:0">
													<table>
														<tr>
														<td style="width: 60px">
													Zip Code
													<input type="text" class="form-control" name="" value="40170">
												</td>
												<td style="width: 90px">
													Phone Number
													<input type="text" class="form-control" name="" value="+1-541-754-3010">
												</td>
														</tr>
													</table>
												</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
							<tr>
								<td>
									<table cellpadding="0" cellspacing="0" class="table">
										<tbody>
											<tr>
												<td style="width: 160px">
													Email Address
													<input type="text" class="form-control" name="" value="testemail@test.com">
												</td>
												<td style="width: 140px">
													ID Type
													<input type="text" class="form-control" name="" value="Passport">
												</td>
												<td style="width: 80px">
													ID #
													<input type="text" class="form-control" name="" value="HG83249HJ24384324N">
												</td>
											</tr>
											<tr>
												<td colspan="3" style="margin:0;padding:0">
													<table>
														<tr>
														<td style="width: 65px">
													Issuing State
													<input type="text" class="form-control" name="" value="New York">
												</td>
												<td style="width: 110px">
													Passport # (Non-Use Citizens)
													<input type="text" class="form-control" name="">
												</td>
														</tr>
													</table>
												</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>

							<tr>
								<td>
									<table cellpadding="0" cellspacing="0" class="table" style="padding: 10px 0;">
										<tr>
											<td>Goverment Issued ID (Provide copy)</td>
										</tr>
										<tr>
											<td>
												<table style="border: none;vertical-align:middle">
													<tr style="border: none;vertical-align:middle">
														<td style="margin: 0 0 0 10px;">
															<input type="radio" name="" style="position: relative; top: 2px; margin: 0 5px 0 0;">
														</td>
														<td>
															<span>Unexpired Driver’s License</span>
														</td>
													</tr>
												</table>
											</td>
											<td>
												<table style="border: none;vertical-align:middle">
														<tr style="vertical-align: middle;border:none">
															<td style="margin: 0 0 0 10px;">
																<input type="radio" name="" style="position: relative; top: 2px; margin: 0 5px 0 0;" />
															</td>
															<td>
																<span>Government Issued Identification Card</span>
															</td>
														</tr>
													</table>
											</td>
											<td>
												<table>
													<tr>
														<td>
															<input type="radio" name="" style="position: relative; top: 2px; margin: 0 5px 0 0;" checked>
														</td>
														<td>
															<span>Military Identification</span>
														</td>
													</tr>
												</table>
											</td>
											<td>
												<table>
													<tr>
														<td style="margin: 0 0 0 10px;">
															<input type="radio" name="" style="position: relative; top: 2px; margin: 0 5px 0 0;">
														</td>
														<td>
															<span>Passport</span>
														</td>
													</tr>
												</table>
											</td>
										</tr>
									</table>
								</td>
							</tr>

						</tbody>
					</table>
				</div>
			</div>
			<!-- /.Section -->
			<!-- Section -->
			<div class="section">
				<div class="heading" style="margin: 0 0 5px;font-family:Arial, Helvetica, sans-serif">
					11. General Information
				</div>
				<p style="padding: 0 10px; margin: 0; color: #999; font: bold 9px 'Arial';font-family:Arial, Helvetica, sans-serif">
					To help the government fight financial crime, Federal regulation requires certain financial institutions to obtain, verify, and record information about the beneficial owners of legal entity customers. Legal entities can be abused to disguise involvement in terrorist financing, money laundering, tax evasion, corruption, fraud, and other financial crimes. Requiring the disclosure of key individuals who own or control a legal entity (i.e., the beneficial owners) helps law enforcement investigate and prosecute these crimes.
				</p>
				<h3 style="padding: 0 10px; margin: 10px 0 0; color: #555; font: bold 10px 'Arial';font-family:Arial, Helvetica, sans-serif">Who has to complete this form?</h3>
				<p style="padding: 0 10px; margin: 0; color: #999; font-size: 10px; font: bold 9px 'Arial';font-family:Arial, Helvetica, sans-serif">
					This form must be completed by the person opening a new account or requesting maintenance on an existing account on behalf of a legal entity. For the purposes of this form, a legal entity includes a Corporation, Limited Liability Company, other entity that is
					created by a filing of a public document with a Secretary of State or similar office, a general partnership, and similar business entity formed in the United States or foreign country.)
				</p>
				<h3 style="padding: 0 10px; margin: 10px 0 0; color: #555; font: bold 10px 'Arial';font-family:Arial, Helvetica, sans-serif">What information do I have to provide? </h3>
				<p style="padding: 0 10px; margin: 0 0 5px; color: #999;  font: bold 9px 'Arial';font-family:Arial, Helvetica, sans-serif">
					This form requires you to provide the name, address, date of birth, and social security number (or passport number or other similar information, in the case of non-US Persons) for the following individuals (beneficial owners): (i) Each individual, if any, who owns directly or indirectly, 25 percent or more of the equity interests of the legal entity customer (e.g., each natural person that owns 25 percent or more of the shares of a corporation); and (ii) An individual with significant responsibility for managing the legal entity customer (e.g., a Chief Executive Officer, Chief Financial Officer, Chief Operating Officer, Managing Member, General Partner, President, Vice President, or Treasurer)
				</p>
				<h3 style="padding: 0 10px; margin: 5px 0 10px; color: #555; font: bold 10px 'Arial';font-family:Arial, Helvetica, sans-serif">A verified or copy of a valid driver’s license or other government issued identifying document for each beneficial owner on this form is required.</h3>
			</div>
			<!-- /.Section -->
			<!-- Section -->
			<div class="section ">
				<div class="heading" style="margin: 0 0 5px;font-family:Arial, Helvetica, sans-serif">
					12. Visa Disclosure
				</div>
				<table cellpadding="0" cellspacing="0" class="table" style="margin: 0 0 10px;">
					<tr>
						<td valign="top" style="padding: 0 10px; color: #555; font: bold 10px 'Arial'; width: 26%;font-family:Arial, Helvetica, sans-serif">
							<p style="margin: 0; font: bold 7px 'Arial';font-family:Arial, Helvetica, sans-serif">MEMBER BANK (ACQUIRER) INFORMATION</p>
							WESTAMERICA BANK<br>
							3750 Westwind Blvd.<br>
							Suite #210<br>
							Santa Rosa, CA 95403<br>
							P: 800-939-9942<br>
							E: acquirer@westamerica.com
						</td>
						<td valign="top" style="padding: 0 10px;">
							<p style="color: #666; font: bold 10px 'Arial'; margin: 0;font-family:Arial, Helvetica, sans-serif">IMPORTANT MEMBER BANK (ACQUIRER) RESPONSIBILITIES</p>
							<ol style="padding: 0 0 0 8px; margin: 0;  color: #999; font: bold 9px 'Arial';font-family:Arial, Helvetica, sans-serif">
								<li>A Visa Member is the only entity approved to extend acceptance of Visa products directly to a Merchant.</li>
								<li>A Visa Member must be a principal (signer) to the Merchant Agreement</li>
								<li>A Visa Member is responsible for educating Merchants on pertinent Visa Rules with which Merchants must comply.</li>
								<li>A Visa Member is responsible for and must provide settlement funds to the Merchant.</li>
								<li>A Visa Member is responsible for all funds held in reserve that are derived from settlement.</li>
							</ol>
						</td>
						<td valign="top">
							<p style="color: #666; font: bold 10px 'Arial'; margin: 0;font-family:Arial, Helvetica, sans-serif">IMPORTANT MERCHANT RESPONSIBILITIES</p>
							<ol style="padding: 0 0 0 8px; margin: 0;  color: #999; font: bold 9px 'Arial';font-family:Arial, Helvetica, sans-serif">
								<li>Ensure compliance with cardholder data security and storage requirements.</li>
								<li>Maintain fraud and disputes below thresholds.</li>
								<li>Review and understand the terms of the Merchant Agreement.</li>
								<li>Comply with Visa Rules.</li>
							</ol>
						</td>
					</tr>
				</table>
			</div>
			<!-- /.Section -->
			<!-- Section -->
			<div class="section">
				<div class="heading" style="margin: 0 0 5px;font-family:Arial, Helvetica, sans-serif">
					13. Merchant Compliance
				</div>
				<p style="padding: 0 10px; margin: 0 0 10px; color: #999; font: bold 9px 'Arial';font-family:Arial, Helvetica, sans-serif">
					A bi-annual annual $49.95 compliance fee will be charged to Merchant each June and December of the year, unless 30 days’ notice is provided for a change in billing date. Merchant represents and warrants that as of the date of signing this Agreement and throughout any term of this Merchant Processing Agreement that it is Payment Card Industry ("PCI") Data Security Standard ("DSS") compliant, and that any hardware or software that Merchant uses during the term of this Agreement to process electronic transactions is Payment Application ("PA") DSS compliant. Merchant further represents and warrants that it will provide assistance as requested from TekCard Payments to remain compliant with the requirements of Internal Revenue Code Section 6050W and any other applicable federal or state law as it relates to the reporting and processing of electronic transactions. TekCard Payments reserves the right to impose future fees or withhold payments to Merchant as set forth in the Merchant Processing Agreement and as required by law. Additional Fees may be added or changed by an amendment to the Merchant Processing Agreement with 30 day’s notice
				</p>
			</div>
			<!-- /.Section -->
			<!-- Section -->
			<div class="section">
				<div class="heading" style="margin: 0 0 5px;font-family:Arial, Helvetica, sans-serif">
					14. Funds Transfer Authorization
				</div>
				<p style="padding: 0 10px; margin: 0 0 10px; color: #999; font: bold 9px 'Arial';font-family:Arial, Helvetica, sans-serif">
					BANK and Company are authorized to perform such functions under the Merchant Processing Agreement, the Gateway Services Agreement, and the POS System Service Agreement Terms and Conditions, as applicable, for the purposes set forth in the applicable agreement.
				</p>
			</div>
			<!-- /.Section -->
			<!-- Section -->
			<div class="section">
				<div class="heading" style="margin: 0 0 5px;font-family:Arial, Helvetica, sans-serif">
					15. Personal Guaranty (No titles)
				</div>
				<table cellpadding="0" cellspacing="0" class="table" style="margin: 0 0 10px;">
					<tr>
						<td valign="top" style="padding: 0; color: #555; font: bold 10px 'Arial'; width: 45%;">
							<p style="padding: 0 10px; margin: 0 0 10px; color: #999; font: bold 8px 'Arial'; line-height: 10px">
								This general, absolute, and unconditional continuing Guaranty (“GUARANTY”) by the undersigned
								(collectively “GUARANTOR“ or "my" or "I" or “me”), is for the benefit of WestAmerica Bank and TekCard
								Payments. (Each a "Guaranty Party" and collectively the "Guaranty Parties"). For value received, and in
								consideration of the mutual undertakings contained in the Merchant Processing Agreement and allied
								agreements (“AGREEMENT”) between any Guaranty Party and MERCHANT as set forth below, I absolutely
								and unconditionally guarantee the full performance of all MERCHANT's obligations to any Guaranty Party,
								together with all costs, expenses, and attorneys' fees incurred by any Guaranty Party in connection with any
								actions, inactions, or defaults of MERCHANT. I waive any right to require any Guaranty Party to proceed
								against other entities or MERCHANT. There are no conditions attached to the enforcement of this
								GUARANTY. I authorize the Guaranty Parties and their respective agents or assigns to make from time to
								time any personal credit or other inquiries and agree to provide, at the Guaranty Parties' request, financial
								statements and/or tax returns. This is a continuing GUARANTY and shall remain in effect until one hundred
								eighty (180) days after receipt by The Guaranty Parties of written notice by me terminating or modifying the
								same. The termination of the AGREEMENT or GUARANTY shall not release me from liability with respect to
								any obligations incurred before the effective date of termination. No termination of this GUARANTY shall
								be effected by any change in my legal status or any change in the relationship between MERCHANT and
								me. This GUARANTY shall bind and inure to the benefit of the personal representatives, heirs,
								administrators, successors and assigns of GUARANTOR and TekCard Payments
							</p>
						</td>
						<td valign="top" style="padding: 0 10px; width: 55%">
							<table cellpadding="0" cellspacing="0" class="table">
								<tr>
									<td style="width: 50%; padding: 5px 5px 15px;">
										<table cellpadding="0" cellspacing="0" class="table">
											<tr style="position: relative;">
												<td style="padding: 0; width: 25px;height:31px;"><span style="width: 25px; height: 38px; background: #3ab54a; color: #fff; border-radius: 3px 0 0 3px; border: 1px solid #d6d9e4; border-right: 0; line-height: 30px; text-align: center; display: inline-block; ">X</span></td>
												<td style="padding: 0;height:31px;position:relative">
													<input type="text" class="form-control" style="height: 30px; margin: 0; border-radius: 0 3px 3px 0" name="">
													<img src="images/sign.png" alt="" height="26px" width="100%" style="position: absolute;left:26px;top:2px;bottom:2px;right:5px;margin:2px 0;max-width:100px;">
												</td>
											</tr>
										</table>
										Principal #1 From Application - Signature
									</td>
									<td style="width: 50%; padding: 5px 5px 15px;">
										<table cellpadding="0" cellspacing="0" class="table">
											<tr>
												<td style="padding: 0; width: 25px;"><span style="width: 25px; height: 38px; background: #3ab54a; color: #fff; border-radius: 3px 0 0 3px; border: 1px solid transparent; border-right: none; line-height: 30px; text-align: center; display: inline-block; ">X</span></td>
												<td style="padding: 0;position:relative">
													<input type="text" class="form-control" style="height: 30px; margin: 0; border-radius: 0 3px 3px 0" name="">
													<img src="images/sign.png" alt="" height="26px" width="100%" style="position: absolute;left:26px;top:2px;bottom:2px;right:5px;margin:2px 0;max-width:100px;">
												</td>
											</tr>
										</table>
										Principal #2 From Application - Signature
									</td>
								</tr>
								<tr>
									<td style="width: 50%; padding: 5px 5px 15px;">
										<input type="text" class="form-control" style="height: 30px;" name="">
										Print Name
									</td>
									<td style="width: 50%; padding: 5px 5px 15px;">
										<input type="text" class="form-control" style="height: 30px;" name="">
										Print Name
									</td>
								</tr>
								<tr>
									<td style="width: 50%; padding: 5px 5px 15px;">
										<input type="text" class="form-control" style="height: 30px;" name="" value="11-21-2002">
										Date
									</td>
									<td style="width: 50%; padding: 5px 5px 15px;">
										<input type="text" class="form-control" style="height: 30px;" name="" value="11-21-2002">
										Date
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</div>
			<!-- /.Section -->
			<!-- Section -->
			<div class="section ">
				<div class="heading" style="margin: 0 0 5px;">
					16. Beneficial Owner(s) Certification Agreed To (Required)
				</div>
				<table cellpadding="0" cellspacing="0" class="table" style="margin: 0 0 5px;">
					<tr>
						<td valign="top" style="padding: 0; color: #555; font: bold 10px 'Arial'; width: 45%;">
							<p style="padding: 0 10px; margin: 0 0 10px; color: #999; font: bold 10px 'Arial'; line-height: 12px">
								I, (print name) ___________________________________________________ , hereby certify, to the
								best of my knowledge, that the information provided on this form is complete and
								correct for all accounts. It is further agreed that TekCard Inc., and Westamerica Bank
								will be immediately notified by the legal entity of any change in such information
								provided on this form.
							</p>
						</td>
						<td valign="top" style="padding: 0 10px; width: 55%">
							<table cellpadding="0" cellspacing="0" class="table">
								<tr>
									<td style="width: 30%; padding: 5px 5px 15px;">
										<table cellpadding="0" cellspacing="0" class="table">
											<tr>
												<td style="padding: 0; width: 25px;"><span style="width: 25px; height: 38px; background: #3ab54a; color: #fff; border-radius: 3px 0 0 3px; border: 1px solid #d6d9e4; border-right: 0; line-height: 30px; text-align: center; display: inline-block; ">X</span></td>
												<td style="padding: 0;position:relative">
													<input type="text" class="form-control" style="height: 30px; margin: 0; border-radius: 0 3px 3px 0" name="">
													<img src="images/sign.png" alt="" height="26px" width="100%" style="position: absolute;left:5px;top:2px;bottom:2px;right:5px;margin:2px 0;max-width:80px;">
												</td>
											</tr>
										</table>
										Signature
									</td>
									<td style="width: 30%; padding: 5px 5px 15px;">
										<input type="text" class="form-control" style="height: 30px;" name="">
										Printed Name
									</td>
									<td style="width: 30%; padding: 5px 5px 15px;">
										<input type="text" class="form-control" style="height: 30px;" name="" value="11-21-2002">
										Date
									</td>
								</tr>

							</table>
						</td>
					</tr>
				</table>
			</div>
			<!-- /.Section -->
			<!-- Section -->
			<div class="section">
				<div class="heading" style="margin: 0 0 5px;">
					16. Beneficial Owner(s) Certification Agreed To (Required)
				</div>
				<table cellpadding="0" cellspacing="0" class="table" style="margin: 0 0 5px;">
					<tr>
						<td valign="top" style="padding: 0 10px;">
							<p style="margin: 0 0 5px; color: #666; font: bold 9px 'Arial'; line-height: 12px">
								By its signature below, Client acknowledges that it has received the Merchant Processing Application and the Program Terms and Conditions available at www.tekcardpayments.com/terms-and-conditions as in effect at any given time.
							</p>
							<p style="padding: 5px 0 0; margin: 0 0 10px; color: #999; font: bold 8px 'Arial'; border-top: 1px solid #ddd; line-height: 10px">
								This general, absolute, and unconditional continuing Guaranty (“GUARANTY”) by the undersigned
								(collectively “GUARANTOR“ or "my" or "I" or “me”), is for the benefit of WestAmerica Bank and TekCard
								Payments. (Each a "Guaranty Party" and collectively the "Guaranty Parties"). For value received, and in
								consideration of the mutual undertakings contained in the Merchant Processing Agreement and allied
								agreements (“AGREEMENT”) between any Guaranty Party and MERCHANT as set forth below, I absolutely
								and unconditionally guarantee the full performance of all MERCHANT's obligations to any Guaranty Party,
								together with all costs, expenses, and attorneys' fees incurred by any Guaranty Party in connection with any
								actions, inactions, or defaults of MERCHANT. I waive any right to require any Guaranty Party to proceed
								against other entities or MERCHANT. There are no conditions attached to the enforcement of this
								GUARANTY. I authorize the Guaranty Parties and their respective agents or assigns to make from time to
								time any personal credit or other inquiries and agree to provide, at the Guaranty Parties' request, financial
								statements and/or tax returns. This is a continuing GUARANTY and shall remain in effect until one hundred
								eighty (180) days after receipt by The Guaranty Parties of written notice by me terminating or modifying the
								same. The termination of the AGREEMENT or GUARANTY shall not release me from liability with respect to
								any obligations incurred before the effective date of termination. No termination of this GUARANTY shall
								be effected by any change in my legal status or any change in the relationship between MERCHANT and
								me. This GUARANTY shall bind and inure to the benefit of the personal representatives, heirs,
								administrators, successors and assigns of GUARANTOR and TekCard Payments
							</p>
							<p style="padding: 5px 0 0; margin: 0 0 10px; color: #999; font: bold 8px 'Arial'; border-top: 1px solid #ddd; line-height: 10px">
								By signing this Merchant Agreement, each of the undersigned authorizes us and our Affiliates and our third party subcontractors and/or agents to verify the information contained in the this application and to request and obtain from any consumer reporting agency and other sources, including bank reference , personal and business consumer reports and other information and to disclose such information amongst each other for any purposes permitted by law. If the Application is approved, each of the undersigned also authorizes us and our Affiliates and our third party subcontractors and/or agents to obtain subsequent consumer reports in connection with the maintenance, updating, renewal or extension of the Agreement or for any other purpose permitted. Each of the undersigned furthermore agrees that all references, including banks and consumer reporting agencies, may release any and all personal and business credit financial information to us and our Affiliates and our third party subcontractors and/or agents. Each of the undersigned authorizes us and our Affiliates and our third party subcontractors and/or agents to provide amongst each other the information contained in this Merchant Agreement and any information received subsequent thereto from all reference, including banks and consumer reporting agencies for any purpose permitted by law. It is our policy to obtain certain information in order to verify your identity while processing your account application.
								<br>
								As part of our approval, processing services, continuing fraud prevention and account review processes, the undersigned consents to the use of information gathered online or that you submit to us, and/ or automated electronic computer security screening, by us on our third party vendors.
							</p>
							<p style="padding: 5px 0 0; margin: 0 0 10px; color: #999; font: bold 8px 'Arial'; border-top: 1px solid #ddd; line-height: 10px">
								ANNUAL FEE: Merchant shall pay an annual subscription fee of $89.00 per Merchant Account per year, and this fee will be assessed to Merchant in the month of August every year unless a 30 days’ notice is provided for a change in billing date by TekCard Payments. ACCOUNT CLOSURE FEE: If the Merchant terminates this Agreement prior to the end of the INITIAL TERM (3 years) or any RENEWAL TERM (2 years) for any reason, MERCHANT agrees to pay TekCard Payments an Account Closure Fee as set forth in Section 5.2 of two hundred and ninety five dollars ($295) or sixty dollars ($60) multiplied by the number of months remaining in the merchant agreement (whichever is greater) per Merchant Identification Number ("MID"). MERCHANT agrees that this fee is not a penalty, but rather a reasonable estimation of the actual damages TekCard Payments would suffer if TekCard Payments were to fail to receive the processing business for the then current term. MERCHANT agrees that the Account Closure Fee shall also be due if MERCHANT discontinues submitting SALES for processing during the INITIAL TERM or any RENEWAL TERM of the Agreement. Not withstanding the foregoing, the Account Closure Fee will not exceed the maximum amount set forth by applicable law. Paragraph references and capitalized terms not defined at http://www.tekcardpayments.com/terms-and-conditions. Designated Cancellation Forms must be faxed to TekCard Payments
							</p>
							<p style="padding: 5px 0 0; margin: 0 0 10px; color: #999; font: bold 8px 'Arial'; border-top: 1px solid #ddd; line-height: 10px">
								MERCHANT AND COMPANY WAIVE THEIR RIGHTS TO SUE BEFORE A JUDGE OR JURY AND PARTICIPATE IN A CLASS ACTION AND AGREE TO RESOLVE ALL CLAIMS AND DISPUTES THROUGH BINDING INDIVIDUAL ARBITRATION. SEE ARTICLE VII AT <a href="www.tekcardpayments.com/terms-and-conditions" target="_blank"> www.tekcardpayments.com/terms-and-conditions.</a>
							</p>
							<p style="padding: 5px 0 0; margin: 0 0 10px; color: #999; font: bold 8px 'Arial'; border-top: 1px solid #ddd; line-height: 10px">
								In witness whereof the parties hereto have caused this Agreement to be executed by their duly authorized representatives effective on the date signed or approved by BANK
							</p>
							<p style="padding: 5px 0 0; margin: 0 0 10px; color: #999; font: bold 8px 'Arial'; border-top: 1px solid #ddd; line-height: 10px">
								If applicable, MERCHANT agrees by its signature below to the TMS American Express Opt Blue Program Agreement. For details, please see www.tekcardpayments.com/terms/americanexpress.
							</p>
							<p style="padding: 5px 0 0; margin: 0 0 10px; color: #999; font: bold 8px 'Arial'; border-top: 1px solid #ddd; line-height: 10px">
								I further acknowledge and agree that I will not use my merchant account and/or the Services for illegal transactions, for example, those prohibited by the Unlawful Internet Gambling Enforcement Act, 31 U.S.C. Section 5361 et seq, as may be amended from time to time, or processing and acceptance of transaction in cretin jurisdictions pursuant to 31 CFR Part 500 et seq. and other laws enforced by the Office of Foreign Assets Control (OFAC)
								<br>
								Merchant certifies, under penalties of perjury, that the federal taxpayer identification number and corresponding filing name provide herein are correct.
							</p>
						</td>
					</tr>

					<tr>
						<td valign="top" style="padding: 0 10px; width: 55%;">

							<table cellpadding="0" cellspacing="0" class="table">
								<tr>
									<td style="width: 55%;" valign="top">
										<table cellpadding="0" cellspacing="0" class="table">
											<tr>

												<td style="width: 30%; padding: 5px 0;">
													<table cellpadding="0" cellspacing="0" class="table">
														<tr>
															<td style="padding: 5px 0 0;">
																<table cellpadding="0" cellspacing="0" class="table">
																	<tr>
																		<td style="padding: 0; width: 25px;"><span style="width: 25px; height: 38px; background: #3ab54a; color: #fff; border-radius: 3px 0 0 3px; border: 1px solid #d6d9e4; border-right: 0; line-height: 30px; text-align: center; display: inline-block; ">X</span></td>
																		<td style="padding: 0;position:relative">
																			<input type="text" class="form-control" style="height: 30px; margin: 0; border-radius: 0 3px 3px 0" name="">
																			<img src="images/sign.png" alt="" height="26px" width="100%" style="position: absolute;left:26px;top:2px;bottom:2px;right:5px;margin:2px 0;max-width:100px;">
																		</td>
																	</tr>
																</table>
																Principal #1 From Application - Signature
															</td>
														</tr>
														<tr>
															<td style="padding: 5px 0 0;">
																<input type="text" class="form-control" style="height: 30px;" name="">
																Print Legal Name
															</td>
														</tr>
														<tr>
															<td style="padding: 5px 0 15px ;">
																<table cellpadding="0" cellspacing="0" class="table">
																	<tr>
																		<td style="padding: 5px 0 0; width: 35%;">
																			<input type="text" class="form-control" name="">
																			Date
																		</td>
																		<td style="padding: 5px 0 0 10px;">
																			<input type="text" class="form-control" name="">
																			Title
																		</td>
																	</tr>
																</table>
															</td>
														</tr>
													</table>
												</td>

												<td style="width: 30%; padding: 5px 0 0 15px;">
													<table cellpadding="0" cellspacing="0" class="table">
														<tr>
															<td style="padding: 5px 0 0;">
																<table cellpadding="0" cellspacing="0" class="table">
																	<tr>
																		<td style="padding: 0; width: 25px;"><span style="width: 25px; height: 38px; background: #3ab54a; color: #fff; border-radius: 3px 0 0 3px; border: 1px solid #d6d9e4; border-right: none; line-height: 30px; text-align: center; display: inline-block; ">X</span></td>
																		<td style="padding: 0;position:relative">
																			<input type="text" class="form-control" style="height: 30px; margin: 0; border-radius: 0 3px 3px 0" name="">
																			<img src="images/sign.png" alt="" height="26px" width="100%" style="position: absolute;left:26px;top:2px;bottom:2px;right:5px;margin:2px 0;max-width:100px;">
																		</td>
																	</tr>
																</table>
																Principal #1 From Application - Signature
															</td>
														</tr>
														<tr>
															<td style="padding: 5px 0 0;">
																<input type="text" class="form-control" style="height: 30px;" name="">
																Print Legal Name
															</td>
														</tr>
														<tr>
															<td style="padding: 5px 0 15px ;">
																<table cellpadding="0" cellspacing="0" class="table">
																	<tr>
																		<td style="padding: 5px 0 0; width: 35%;">
																			<input type="text" class="form-control" name="" value="11-21-2002">
																			Date
																		</td>
																		<td style="padding: 5px 0 0 10px;">
																			<input type="text" class="form-control" name="">
																			Title
																		</td>
																	</tr>
																</table>
															</td>
														</tr>
													</table>
												</td>

											</tr>
										</table>
									</td>
									<td style="width: 45%; padding: 0 0 0 35px" valign="top">
										<table cellpadding="0" cellspacing="0" class="table">
											<tr>
												<td style="padding: 5px 0 0;" valign="top">
													<div style="background: #000; color: #fff; padding: 3px; text-align: center; margin: 5px 0 0; display: block; ">Office Use Only</div>
													<table cellpadding="0" cellspacing="0" class="table">
														<tr>
															<td style="padding: 0; width: 25px; background: #d6d9e4;"><span style="width: 25px;height: 29px;background: #d6d9e4;color: #555;border-radius: 3px 0 0 3px;border: 0;border-right: none;line-height: 30px;text-align: center;display: inline-block;">X</span></td>
															<td style="padding: 0;position:relative">
																<input type="text" class="form-control" style="height: 30px; margin: 0; border-radius: 0 3px 3px 0" name="">
																<img src="images/sign.png" alt="" height="26px" width="100%" style="position: absolute;left:26px;top:2px;bottom:2px;right:5px;margin:2px 0;max-width:100px;">
															</td>
														</tr>
													</table>
													Principal #1 From Application - Signature
												</td>
											</tr>
											<tr>
												<td style="padding: 5px 0 0;">
													<input type="text" class="form-control" style="height: 30px;" name="">
													Print Legal Name
												</td>
											</tr>

										</table>
									</td>
								</tr>



							</table>



						</td>
					</tr>




				</table>
			</div>
			<!-- /.Section -->

			<style type="text/css">
				.value-box {
					width: 100%;
					padding: 7px 5px;
					border: 1px solid #d6d9e4;
					margin: 2px 0 0;
					background: #f1f4ff;
					border-radius: 3px;
					font: bold 10px 'Arial';
					box-sizing: border-box;
				}
			</style>

			<!-- Section -->
			<div class="section " style="border: 0;">
				<div class="heading" style="margin: 0 0 5px; text-align: center;">
					CONFIRMATION PAGE
				</div>
				<div class="sub-heading" style="margin: 5px 0 5px; background: #ebebec; padding: 3px 10px; color: #3e4042; font: bold 10px 'Arial';">Processor Information
				</div>
				<table cellpadding="0" cellspacing="0" class="table" style="margin: 0 0 5px;padding:0 10px;">
					<tr>
						<td style="width: 15%; padding: 5px 5px 5px 0;">
							Name
							<div style="" class="value-box">Tekcard, Inc</div>
						</td>
						<td style="width: 35%; padding: 5px 5px 5px;">
							Address
							<div style="" class="value-box">160 Chubb Avenue, Ste. 203, Lyndhurst, NJ 07071</div>
						</td>
						<td style="width: 25%; padding: 5px 5px 5px;">
							URL
							<div style="" class="value-box">https://tekcardpayments.com/contact</div>
						</td>
						<td style="width: 25%; padding: 5px 0 5px 5px;">
							Customer Service Number
							<div style="" class="value-box">1-844-TEK-PYMT</div>
						</td>

					</tr>
				</table>
				<hr>
				<div class="sub-heading" style="display:block;background: #ebebec; padding: 8px 10px; width:100%;">
					<span style="max-width:500px;color: #666; font: bold 9px 'Arial'; line-height: 9px;word-break:break-word;"> 
						read the Program Guide in its entirety. It describes the terms under which we will provide merchant processing Services to you. From time to time you may have questions regarding the contents of your Agreement with Bank and/or Processor or the contents of your Agreement with TeleCheck. The following terms and conditions are provided to you by Processor and not Bank. Bank is not a party to this Agreement, as it applies to the TeleCheck Transactions, and Bank is not liable to you in any with respect to such services.
					</span>	
				</div>

				<p style=" color: #666; font: bold 9px 'Arial'; line-height: 9px;padding:0 10px">
					The following information summarizes portions of your Agreement in order to assist you in answering some of the questions we are most commonly asked
				</p>

				<table cellpadding="0" cellspacing="0" class="table" style="margin: 0 0 5px;padding:10px;">
					<tr>
						<td style="width: 33.3333%; padding: 5px 5px 5px 0;">
							<p style="padding: 5px 0 0; margin: 0 0 5px; color: #999; font: bold 8px 'Arial'; line-height: 10px">
								<span style="width: 10px; display: inline-block;">1</span>
								<b style="color: #333;">Your Discount Rates are assessed</b>on transactions that qualify for certain reduced interchange rates imposed by MasterCard, Visa, Discover and PayPal. Any transactions that fail to qualify for these reduced rates will be charged an additional fee (see Section 25 of the Program Guide).
							</p>

							<p style="padding: 5px 0 0; margin: 0 0 5px; color: #999; font: bold 8px 'Arial'; line-height: 10px">
								<span style="width: 10px; display: inline-block;">2</span> <b style="color: #333;">We may debit your bank accoun</b>
								(also referred to as your Settlement Account) from time to time for amounts owed to us under the Agreement.
							</p>

							<p style="padding: 5px 0 0; margin: 0 0 5px; color: #999; font: bold 8px 'Arial'; line-height: 10px">
								<span style="width: 10px; display: inline-block;">3</span>
								<b style="color: #333;">There are many reasons why a Chargeback may occurr.</b>
								When they occur we will debit your settlement funds or Settlement Account. For a more detailed discussion regarding Chargebacks see Section 14 of the Your Payments Acceptance Guide or see the applicable provisions of the TeleCheck Solutions Agreement
							</p>
							<p style="padding: 5px 0 0; margin: 0 0 5px; color: #999; font: bold 8px 'Arial'; line-height: 10px">
								<span style="width: 10px; display: inline-block;">4</span>
								<b style="color: #333;">If you dispute any charge or funding,</b>
								you must notify us within 60 days of the date of the statement where the charge or funding appears for Card Processing or within 30 days of the date of a TeleCheck transaction
							</p>
							<p style="padding: 5px 0 0; margin: 0 0 5px; color: #999; font: bold 8px 'Arial'; line-height: 10px">
								<span style="width: 10px; display: inline-block;">5</span>
								<b style="color: #333;">The Agreement limits our liability to you</b>
								For a detailed description of the limitation of liability see Section 27, 37.3, and 39.10 of the Card General Terms; or Section 17 of the TeleCheck Solutions Agreement
							</p>
						</td>
						<td style="width: 33.3333%; padding: 5px 5px 5px 0;" valign="top">
							<p style="padding: 5px 0 0; margin: 0 0 5px; color: #999; font: bold 8px 'Arial'; line-height: 10px">
								<span style="width: 10px; display: inline-block;">6</span>
								<b style="color: #333;">We have assumed certain risks</b> by agreeing to provide you with Card processing or check services. Accordingly, we may take certain actions to mitigate our risk, including termination of the Agreement, and/or hold monies otherwise payable to you (see Card Processing General Terms in Section 30, Term; Events of Default and Section 31, Reserve Account; Security Interest), (see TeleCheck Solutions Agreement in Section 7), under certain circumstances.
							</p>

							<p style="padding: 5px 0 0; margin: 0 0 5px; color: #999; font: bold 8px 'Arial'; line-height: 10px">
								<span style="width: 10px; display: inline-block;">7</span>
								<b style="color: #333;">By executing this Agreement with us</b>
								you are authorizing us and our Affiliates to obtain financial and credit information regarding your business and the signers and guarantors of the Agreement until all your obligations to us and our Affiliates are satisfied.
							</p>

							<p style="padding: 5px 0 0; margin: 0 0 5px; color: #999; font: bold 8px 'Arial'; line-height: 10px">
								<span style="width: 10px; display: inline-block;">8</span>
								<b style="color: #333;">The Agreement contains a provision</b>
								that in the event you terminate the Agreement prior to the expiration of your initial three (3) year term, you will be responsible for the payment of an early termination fee as set forth in Part IV, A.3 under “Additional Fee Information” and Section 16.2 of the TeleCheck Solutions Agreement.
							</p>
						</td>
						<td style="width: 33.3333%; padding: 5px 5px 5px 0;" valign="top">
							<p style="padding: 5px 0 0; margin: 0 0 5px; color: #999; font: bold 8px 'Arial'; line-height: 10px">
								<span style="width: 10px; display: inline-block;">9</span>
								<b style="color: #333;">If you lease equipment from Processor, </b> it is important that you review Section 1 in Third Party Agreements. Bank is not a party to this Agreement
							</p>
							<p style="padding: 5px 0 0; margin: 0 0 5px; color: #999; font: bold 8px 'Arial'; line-height: 10px">
								<span style="width: 10px; display: inline-block;">9</span>
								<b style="color: #333;">Card Organization Disclosure Visa and MasterCard Member The Bank’s mailing address is:</b>
							</p>
							<p style="padding: 5px 0 0 20px; margin: 0 0 5px; color: #333; font: bold 12px 'Arial'; line-height: 14px">
								<span style="font-size: 8px;">MEMBER BANK (ACQUIRER) INFORMATION </span><br>
								WESTAMERICA BANK<br>
								3750 Westwind Blvd.<br>
								Suite #210<br>
								Santa Rosa, CA 95403<br>
								P: 800-939-9942<br>
								E: acquirer@westamerica.com
							</p>
						</td>

					</tr>
				</table>

				<table cellpadding="0" cellspacing="0" class="table" style="margin: 0 0 5px;padding:10px">
					<tr>

						<td style="width: 40%; padding: 5px 20px 5px 0;" valign="top">
							<span style="font-size: 15px; background: #eee; color: #333; display: inline-block; width: 100%; padding: 8px 15px; box-sizing: border-box;">Important Member Bank Responsibilities:</span>
							<p style="padding: 5px 0 0; margin: 0 0 5px; color: #999; font: bold 8px 'Arial'; line-height: 10px">
								<span style="width: 15px; font-weight: bold; font-size: 9px; color: #333; display: inline-block;">a)</span>
								The Bank must be a principal (signer) to the Agreement.
							</p>
							<p style="padding: 5px 0 0; margin: 0 0 5px; color: #999; font: bold 8px 'Arial'; line-height: 10px">
								<span style="width: 15px; font-weight: bold; font-size: 9px; color: #333; display: inline-block;">b)</span>
								The Bank is responsible for educating merchants on pertinent Visa and MasterCard rules with which merchants must comply; but this information may be provided to you by Processor.
							</p>
							<p style="padding: 5px 0 0; margin: 0 0 5px; color: #999; font: bold 8px 'Arial'; line-height: 10px">
								<span style="width: 15px; font-weight: bold; font-size: 9px; color: #333; display: inline-block;">c)</span>
								The Bank is responsible for and must provide settlement funds to the merchant
							</p>
							<p style="padding: 5px 0 0; margin: 0 0 5px; color: #999; font: bold 8px 'Arial'; line-height: 10px">
								<span style="width: 15px; font-weight: bold; font-size: 9px; color: #333; display: inline-block;">d)</span>
								The Bank is responsible for all funds held in reserve that are derived from settlement.
							</p>
							<p style="padding: 5px 0 0; margin: 0 0 5px; color: #999; font: bold 8px 'Arial'; line-height: 10px">
								<span style="width: 15px; font-weight: bold; font-size: 9px; color: #333; display: inline-block;">e)</span>
								The Bank is the ultimate authority should a merchant have any problems with Visa or MasterCard products (however, Processor also will assist you with any such problems).
							</p>
						</td>

						<td style="width: 60%; padding: 5px 5px 5px 0;" valign="top">
							<span style="font-size: 15px; background: #eee; color: #333; display: inline-block; width: 100%; padding: 8px 15px; box-sizing: border-box;">Important Merchant Responsibilities: </span>
							<p style="padding: 5px 0 0; margin: 0 0 5px; color: #999; font: bold 8px 'Arial'; line-height: 10px">
								<span style="width: 10px; display: inline-block; color: #333;">a)</span>
								Maintain fraud and Chargebacks below Card Organization thresholds
							</p>
							<p style="padding: 5px 0 0; margin: 0 0 5px; color: #999; font: bold 8px 'Arial'; line-height: 10px">
								<span style="width: 10px; display: inline-block; color: #333;">b)</span>
								Review and understand the terms of the Merchant Agreement.
							</p>
							<p style="padding: 5px 0 0; margin: 0 0 5px; color: #999; font: bold 8px 'Arial'; line-height: 10px">
								<span style="width: 10px; display: inline-block;">c)</span>
								Retain a signed copy of this Disclosure Page
							</p>
							<p style="padding: 5px 0 0; margin: 0 0 5px; color: #999; font: bold 8px 'Arial'; line-height: 10px">
								<span style="width: 10px; display: inline-block; color: #333;">e)</span>
								You may download “Visa Regulations” from Visa’s website at: https://usa.visa.com/support/merchant.html.
							</p>
							<p style="padding: 5px 0 0; margin: 0 0 5px; color: #999; font: bold 8px 'Arial'; line-height: 10px">
								<span style="width: 10px; display: inline-block; color: #333;">f)</span>
								You may download “Visa Regulations” from Visa’s website at: https://usa.visa.com/support/merchant.html.
							</p>
							<p style="padding: 5px 0 0; margin: 0 0 5px; color: #999; font: bold 8px 'Arial'; line-height: 10px">
								<span style="width: 10px; display: inline-block; color: #333;">g)</span>
								You may download “MasterCard Regulations” from MasterCard’s website at: http://www.mastercard.com/us/merchant/support/rules.html
							</p>
							<p style="padding: 5px 0 0; margin: 0 0 5px; color: #999; font: bold 8px 'Arial'; line-height: 10px">
								<span style="width: 10px; display: inline-block; color: #333;">h)</span>
								You may download “American Express Merchant Operating Guide” from American Express’ website at: www.americanexpress.com/merchantopguide.
							</p>
						</td>
					</tr>
				</table>
			</div>
			<!-- /.Section -->
			<!-- Section -->
			<div class="section">
				<div class="heading" style="margin: 0 0 5px;">
					18. Special Instructions (If any)
				</div>

				<div class="section-table ">
					<table cellpadding="0" cellspacing="0" class="table">
						<tr>
							<td style="padding: 5px 3px">
								<input type="text" class="form-control" name="">
							</td>
						</tr>
						<tr>
							<td style="padding: 0 3px 3px">
								<input type="text" class="form-control" name="">
							</td>
						</tr>
					</table>
				</div>
			</div>
			<!-- /.Section -->
			<!-- Section -->
			<div class="section" style="border: 0; padding: 0;border:none;">
				<div class="section-table" style="padding: 0;">
					<table cellpadding="0" cellspacing="0" class="table" style="border: none;">
						<tr>
							<td style="padding: 5px 0; font-size: 15px; color: #333;">
								Print Client’s Business Legal Name
							</td>
						</tr>
						<tr>
							<td style="padding: 5px 0">
								<table cellpadding="0" cellspacing="0" class="table">
									<tr>
										<td style="padding: 0; width: 25px;"><span style="width: 25px; height: 38px; background: #3ab54a; color: #fff; border-radius: 3px 0 0 3px; margin: 0; border: 1px solid #d6d9e4; border-right: none; line-height: 30px; text-align: center; display: inline-block; ">X</span></td>
										<td style="padding: 0;"><input type="text" class="form-control" style="height: 30px; width: 300px; margin: 0; border-radius: 0 3px 3px 0;line-height:20px;" name="" value="Real Estate Properties"></td>
									</tr>
									<tr>
										<td colspan="2" style="padding: 3px 0 0; font-weight: bold; font-size: 12px; letter-spacing: 0.01em;">Print Client’s Business Legal Name</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td style="padding: 5px 0;">
								<p style="color: #333">
									By its signature below, Client acknowledges that it has received the Merchant Processing Application, Program Terms and Conditions as posted on www.tekcardpayments.com/terms-and-conditions, consisting of 48 pages [including this Confirmation Page and the applicable Third-Party Agreement(s)].
								</p>
								<p style="color: #333">
									Client further acknowledges reading and agreeing to all terms in the Program Terms and Conditions. Upon receipt of a signed facsimile or original of this Confirmation Page by us, Client’s Application will be processed. Client understands a copy of the Program Guide is also available for downloading from the Internet at: www.tekcardpayments.com/terms-and-conditions
								</p>
								<p style="color: #333">
									NO ALTERATIONS OR STRIKE-OUTS TO THE PROGRAM TERMS AND CONDITIONS WILL BE ACCEPTED
								</p>
							</td>
						</tr>
						<tr>
							<td style="padding: 50px 0 10px">
								<table cellpadding="0" cellspacing="0" class="table">
									<tr>
										<td style="padding: 0; width: 25px;border:none;"><span style="width: 25px; height: 38px; background: #3ab54a; color: #fff; border-radius: 3px 0 0 3px; margin: 0; border: 1px solid #d6d9e4; border-right: none; line-height: 30px; text-align: center; display: inline-block; ">X</span></td>
										<td style="padding: 0;"><input type="text" class="form-control" style="height: 30px; width: 300px; margin: 0; border-radius: 0 3px 3px 0;" name=""></td>
									</tr>
									<tr>
										<td colspan="2" style="padding: 3px 0 0; font-weight: bold; font-size: 12px; letter-spacing: 0.01em;">Principal #1 From Application - Print Name</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td style="padding:0">
								<table cellpadding="0" cellspacing="0" class="table">
									<tr style="margin:0 0 0 10px">
										<td style="padding: 0;margin:0 0 0 -10px;position:relative;" colspan="2">
												<span style="width: 25px; height: 38px; background: #3ab54a; color: #fff; border-radius: 3px 0 0 3px; margin: 0; border: 1px solid #d6d9e4; border-right: none; line-height: 30px; text-align: center; display:inline-block;">X</span>
												<input type="text" class="form-control" style="height: 30px; width: 300px; margin: 0 0 10px -2px; border-radius: 0 3px 3px 0;display:inline-block" name="">
												<img src="images/sign.png" alt="" height="26px" width="300px" style="position: absolute;left:26px;top:2px;bottom:2px;right:0;margin:2px 0;">
											<!-- <a class="trigger_popup_fricc">Click here to show the popup</a> -->
											<!-- Signature Form -->
											<!-- <div class="hover_bkgr_fricc">
												<span class="helper"></span>
												<div>
													<form method="POST" action="">
														<canvas class="pad"></canvas>
														<fieldset>
															<input type="reset" value="clear" />
														</fieldset>
													</form>
												</div>
											</div> -->
											<!-- Signature Form -->
										</td>
									</tr>
									<tr>
										<td colspan="2" style="padding: 3px 0 0; font-weight: bold; font-size: 12px; letter-spacing: 0.01em;">Signature</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td style="padding: 5px 0">
								<table cellpadding="0" cellspacing="0" class="table" style="width: 325px;border:none;">
									<tr>
										<td colspan="2" style="padding: 3px 0 0; font-weight: bold; font-size: 12px; letter-spacing: 0.01em;">
											<table cellpadding="0" cellspacing="0" class="table">
												<tr>
													<td style="padding: 5px 0 0; width: 35%; font-weight: bold; font-size: 12px; letter-spacing: 0.01em;">
														<input type="text" class="form-control" name="" style="height: 30px;line-height:20px;" value="12-30-2020">
														Date
													</td>
													<td style="padding: 5px 0 0 10px; font-weight: bold; font-size: 12px; letter-spacing: 0.01em;">
														<input type="text" class="form-control" name="" style="height: 30px;display:block;line-height:20px;" value="sample title">
														Title
													</td>
												</tr>
											</table>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<!-- /.Section -->
		</div>
	</div>
</body>
</html>