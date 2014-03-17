	<?php session_start();
	include 'include/head.php'; ?>
	
	<?php	
		require 'include/stripe-php/lib/Stripe.php'; 	  
		
		if(isset($_GET['refer']))
		{
			$referral_code = mysql_real_escape_string(strip_tags($_GET['refer']));

			$refer_code_query = $db_con->prepare("SELECT user_id, first_name FROM users WHERE subscription='active' OR subscription='paused' ORDER BY user_id");
			$refer_code_query->execute();
			
			while($refer_code_row = $refer_code_query->fetch(PDO::FETCH_ASSOC))
			{
				$refer_code_row_user_id = $refer_code_row['user_id'];
				$test_refer_id = md5(md5("refer").md5($refer_code_row_user_id));
				if($test_refer_id==$referral_code)
				{
					setcookie("referral_code", $referral_code, time()+172800);
					break;
				}
			}
		}
		
		if(isset($_GET['renew_now']))
		{
			$renew_now_hash = mysql_real_escape_string($_GET['renew_now']);
			
			$renew_now_hash_query = $db_con->prepare("SELECT user_id, subscription, first_name, last_name, email, stripe_id FROM users");
			$renew_now_hash_query->execute();
			
			while($renew_now_hash_row = $renew_now_hash_query->fetch(PDO::FETCH_ASSOC))
			{
				$renew_now_row_user_id = $renew_now_hash_row['user_id'];
				$renew_now_row_subscription = $renew_now_hash_row['subscription'];
				$renew_now_row_stripe_id = $renew_now_hash_row['stripe_id'];
				$renew_now_row_first_name = $renew_now_hash_row['first_name'];
				$renew_now_row_last_name = $renew_now_hash_row['last_name'];
				$renew_now_row_email = $renew_now_hash_row['email'];
				
				$renew_now_hash_id = md5(md5("renew_now").md5($renew_now_row_user_id));
				if($renew_now_hash_id==$renew_now_hash)
				{
					if($renew_now_row_subscription == "delinquent")
					{
						//log the user in
						$_SESSION['user_id'] = $renew_now_row_user_id;
								
						if($renew_now_row_stripe_id != "")
						{
							//PAYMENT DETAILS
							$selected_plan="beta_nov insta-renew";
							$renew_now_package = 'oak';
							$renew_now_size = 01;
							$renew_now_size_display = 8*$renew_now_size;
							$renew_now_duration = 4;
							
							$renew_now_price = 997*$renew_now_duration;
							$renew_now_price_display = "$".$renew_now_price/100;
							
							$renew_now_tax = round($renew_now_price*0.06);
							$renew_now_tax_display = "$".$renew_now_tax/100;
							
							$renew_now_total = $renew_now_price+$renew_now_tax;
							$renew_now_total_display = "$".$renew_now_total/100;
							
							Stripe::setApiKey("");
							
							$customer = Stripe_Customer::retrieve("$renew_now_row_stripe_id");
							
							// charge the Customer instead of the card
							$new_charge = Stripe_Charge::create(array(
							  "amount" => $renew_now_total, # amount in cents, again
							  "currency" => "usd",
							  "customer" => $customer->id)
							);
							
							if($new_charge->failure_message == null)
							{
								//update account information to reflect purchase
								$renew_now_query = $db_con->prepare("
									UPDATE users
									SET subscription = 'active', credits = :weeks_purchased, subscription_size = :subscription_size, subscription_package = :subscription_package, selected_plan='register_plan01' WHERE user_id=:user_id"
									);
								
								$renew_now_query->bindParam(':weeks_purchased', $renew_now_duration);
								$renew_now_query->bindParam(':subscription_size', $renew_now_size);
								$renew_now_query->bindParam(':subscription_package', $renew_now_package);
								$renew_now_query->bindParam(':user_id', $renew_now_row_user_id);
								$renew_now_query->execute();
								
								//record payment activity
								$date = date('Y-m-d G:i:s');
								$purchase_activity_query = $db_con->prepare("INSERT INTO activity VALUES ('', :date, :user_id, 'add_credits', :description, :credits)");
								$purchase_activity_query->bindParam(':date', $date);
								$purchase_activity_query->bindParam(':user_id', $renew_now_row_user_id);
								$purchase_activity_query->bindParam(':credits', $renew_now_duration);
								$purchase_activity_query->bindParam(':description', $selected_plan);
								$purchase_activity_query->execute();
								
								//email payment confirmation
								$to = $renew_now_row_email;
								$subject = "FROOTS & Co. Renewal: $renew_now_row_first_name $renew_now_row_last_name";
								$from = "The FROOTS & Co. Team <admin@froots.co>";

include 'include/email_top.php';
include 'include/email_bottom.php';
			
								$body = "
Hello $renew_now_row_first_name,<br/><br/>

This is a simple confirmation that you've renewed your account with FROOTS & Co.<br/><br/>

Total Weeks: $renew_now_duration <br />
Order Size: $renew_now_size_display <br />
Subtotal: $renew_now_price_display <br />
Tax: $renew_now_tax_display <br />
Total: $renew_now_total_display <br /><br />

As usual, feel free to update your delivery information and leave us comments via your profile.<br /><br />

Thank you,<br/>
The FROOTS Team
";

								//function to send email
								include_once('include/ses.php');
								$ses = new SimpleEmailService('', '');
								$ses->listVerifiedEmailAddresses();
								
								$m = new SimpleEmailServiceMessage();
								$m->addTo($to);
								$m->setFrom($from);
								$m->setSubject($subject);
								$m->setMessageFromString(null, $bodyhead.$body.$bodyfoot);

								$ses->sendEmail($m);
								
								//redirect to member page
								$URL="member.php";
								echo "<meta http-equiv='refresh' content='0;url=$URL'>";
							}
							else
							{
								//redirect to member page
								$URL="join.php";
								echo "<meta http-equiv='refresh' content='0;url=$URL'>";
							}
						}
					}
				}
			}
		}
		?>
	
	<body class="wrap">
		<script type="text/javascript" src="https://js.stripe.com/v1/"></script>
		<script type="text/javascript">
			// this identifies your website in the createToken call below
			Stripe.setPublishableKey('');
			
			function stripeResponseHandler(status, response) {
				if (response.error) {
					// re-enable the submit button
					$('.submit-button').removeAttr("disabled");
					// show the errors on the form
					$(".payment-errors").html(response.error.message);
				} else {
					var form$ = $("#payment-form");
					// token contains id, last4, and card type
					var token = response['id'];
					// insert the token into the form so it gets submitted to the server
					form$.append("<input type='hidden' name='stripeToken' value='" + token + "' />");
					// and submit
					form$.get(0).submit();
				}
			}

			$(document).ready(function() {
				$("#payment-form").submit(function(event) {
					// disable the submit button to prevent repeated clicks
					$('.submit-button').attr("disabled", "disabled");

					// createToken returns immediately - the supplied callback submits the form if there are no errors
					Stripe.createToken({
						number: $('.card-number').val(),
						cvc: $('.card-cvc').val(),
						exp_month: $('.card-expiry-month').val(),
						exp_year: $('.card-expiry-year').val()
					}, stripeResponseHandler);
					return false; // submit from callback
				});
			});
		</script>
		<script type="text/javascript">
			function updateInput(ish){
				if(document.getElementById(ish).value == ish)
				{
					document.getElementById(ish).value = '';
					document.getElementById("word_"+ish).style.cssText = 'color: #A63C45;cursor:pointer;';
				}
				else
				{
					document.getElementById(ish).value = ish;
					document.getElementById("word_"+ish).style.cssText = 'color: #87BE77;cursor:pointer;';
				}
			}
			
			function changePlan(clicked_plan)
			{
				document.getElementById('selected_plan').value = clicked_plan;
				
				document.getElementById('register_plan01').style.cssText = '';
				document.getElementById('register_plan02').style.cssText = '';
				document.getElementById('register_plan04').style.cssText = '';
				document.getElementById('register_plan08').style.cssText = '';
				document.getElementById('register_plan12').style.cssText = '';
					
				document.getElementById(clicked_plan).style.cssText = 'background-color: #212121 !important;color: #F9F9F9;';
				
				var selected_plan = clicked_plan;
				var coupon_code = document.getElementById('coupon_code').value;
				
				if (window.XMLHttpRequest)
				{// code for IE7+, Firefox, Chrome, Opera, Safari
					xmlhttp=new XMLHttpRequest();
				}
				else
				{// code for IE6, IE5
					xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
				}

				xmlhttp.onreadystatechange=function()
				{
					if (xmlhttp.readyState==4 && xmlhttp.status==200)
					{
						document.getElementById('invoice').innerHTML=xmlhttp.responseText;
					}
				}
				xmlhttp.open("GET","include/update_price.php?coupon="+coupon_code+"&&selected_plan="+selected_plan,true);
				xmlhttp.send();
				
			}
			
			function updatePrice()
			{
				var selected_plan = document.getElementById('selected_plan').value;
				var coupon_code = document.getElementById('coupon_code').value;
				
				if (window.XMLHttpRequest)
				{// code for IE7+, Firefox, Chrome, Opera, Safari
					xmlhttp=new XMLHttpRequest();
				}
				else
				{// code for IE6, IE5
					xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
				}

				xmlhttp.onreadystatechange=function()
				{
					if (xmlhttp.readyState==4 && xmlhttp.status==200)
					{
						document.getElementById('invoice').innerHTML=xmlhttp.responseText;
					}
				}
				xmlhttp.open("GET","include/update_price.php?coupon="+coupon_code+"&&selected_plan="+selected_plan,true);
				xmlhttp.send();
			}

		</script>
	<div id="side_shadow">
		<?php
		$step_one = "";
		$step_two = "";
		$step_three = "";
		
		$class_one = "";
		$class_two = "";
		$class_three = "";
		
		if(!isset($_SESSION['user_id']))
		{
			$step_one = 'on';
		}
		else
		{
			$user_id = $_SESSION['user_id'];
			
			$check_address_query = $db_con->prepare("SELECT phone_number, address, address2 FROM users WHERE user_id=:user_id");
			$check_address_query->bindParam(':user_id',$user_id);
			$check_address_query->execute();
			while($check_address_row = $check_address_query->fetch(PDO::FETCH_ASSOC))
			{
				$db_phone_number = $check_address_row['phone_number'];
				$db_address = $check_address_row['address'];
				$db_address2 = $check_address_row['address2'];
			}
			if($db_phone_number == '' || $db_address == 'none' || $db_address2 == '0' )
			{
				$step_two = 'on';
			}
			else
			{
				$check_active_query = $db_con->prepare("SELECT subscription FROM users WHERE user_id=:user_id");
				$check_active_query->bindParam('user_id', $user_id);
				$check_active_query->execute();
				
				while($check_active_row = $check_active_query->fetch(PDO::FETCH_ASSOC))
				{
					$subscription_status = $check_active_row['subscription'];
				}
				if($subscription_status !='active')
				{
					$step_three = 'on';
				}
				elseif($subscription_status =='active' || $subscription_status =='admin')
				{
				$URL = "member.php";
				echo "<meta http-equiv='refresh' content='0;url=$URL'>";
				}
			}
		}

		if($step_one == 'on')
		{
			$class_one = "join_current";
		}
		if($step_two == 'on')
		{
			$class_one = "join_complete";
			$class_two = "join_current";
		}
		if($step_three == 'on')
		{
			$class_one = "join_complete";
			$class_two = "join_complete";
			$class_three = "join_current";
		}
		
		?>
		<?php include 'include/nav_bar.php'; ?>
		
		<table id="join_nav">
			<tr>
				<td class="<?php echo $class_one;?>">1. create your account</td>
				<td class="<?php echo $class_two;?>">2. schedule your delivery</td>
				<td class="<?php echo $class_three;?>">3. pay securely</td>
			</tr>
		</table>
		
		<?php
		if(!isset($_SESSION['user_id']))
		{
			// *******************************
			//1st FORM PROCESSING WILL GO HERE
			// *******************************
			$first_name = "";
			$last_name = "";
			$email = "";
			$password = "";
			$legal_agreement = "";
			
			if(isset($_GET['submit_notification']))
			{
				$full_name = mysql_real_escape_string($_GET['name']);
				$name_array = explode(" ", $full_name);
				
				if(count($name_array)==1)
					$first_name = $name_array[0];
				elseif(count($name_array)==2)
				{
					$first_name = $name_array[0];
					$last_name = $name_array[1];
				}
				elseif(count($name_array)>2)
				{
					$first_name = $name_array[0];
					$last_name = end($name_array);
				}
				
				$email = mysql_real_escape_string($_GET['email']);
			}
			
			if(isset($_POST['submit_one']))
			{
				$first_name = mysql_real_escape_string($_POST['first_name']);
				$last_name = mysql_real_escape_string($_POST['last_name']);
				$email = mysql_real_escape_string($_POST['email']);
				$password = mysql_real_escape_string($_POST['password']);
				if(isset($_POST['legal_agreement']))
					$legal_agreement = mysql_real_escape_string($_POST['legal_agreement']);
				
				$email_check = $db_con->prepare("SELECT email FROM users WHERE email=:email");
				$email_check->execute(array(':email'=>$email));
				
				$email_count = $email_check->rowCount();
				
				if($first_name==""||$last_name==""||$email==""||$password=="")
					$errors[] = "Please fill out all of the fields.";
				if(!preg_match("/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/i", $email))
					$errors[] = "You must enter a valid email address.";
				if(strlen($password)>25||strlen($password)<6)
					$errors[] =  "Password must be between 6 and 25 characters.";
				if($email_count!=0)
					$errors[] = "Email is already in use";
				if($legal_agreement=="")
					$errors[] = "You must accept the privacy policy to continue";
				
				if(empty($errors))
				{
					$date = date('Y-m-d G:i:s');
					$encrypt_password=md5($password);
					$query = $db_con->prepare("
						INSERT INTO users VALUES ('', :date, :first_name, :last_name, :email, :encrypt_password, 'inactive', '', '', 'none', 'none', 'none', 0, 0, 0, '', '', '', '', '', '')
						");
					$query->bindParam(':date', $date);
					$query->bindParam(':first_name', $first_name);
					$query->bindParam(':last_name', $last_name);
					$query->bindParam(':email', $email);
					$query->bindParam(':encrypt_password', $encrypt_password);
					
					$query->execute();
					
					$lastid = $db_con->lastInsertId('user_id');
					
					$to = $email;
					$subject = "FROOTS & Co. Registration: $first_name $last_name";
					$from = "The FROOTS & Co. Team <admin@froots.co>";

include 'include/email_top.php';
include 'include/email_bottom.php';
	
	$body = "
Hello $first_name,<br/><br/>

Thanks so much for creating an account with Froots & Co.! I can't describe how much it means to us!<br /><br />

If you'd like, we can schedule a call for any of your questions, suggestions, and feedback, and we can share some best practices with you.  You can always send us an email if you have additional questions before, during, or after signing up for a subscription.<br /><br />

Also, make sure to check out our <a href='rewards.php'>Rewards Program</a>. You can get literally get an unlimited amount of fruit FREE.<br /><br />

Thanks again for your support! I can't wait to talk to you soon!<br /><br />

Stay fresh.<br/>
The FROOTS Team
";

					//function to send email
					include_once('include/ses.php');
					$ses = new SimpleEmailService('', '');
					$ses->listVerifiedEmailAddresses();
					
					$m = new SimpleEmailServiceMessage();
					$m->addTo($to);
					$m->setFrom($from);
					$m->setSubject($subject);
					$m->setMessageFromString(null, $bodyhead.$body.$bodyfoot);

					$ses->sendEmail($m);
					
					$_SESSION['user_id'] = $lastid;
										
					$URL = "join.php#nav_bar";
					echo "<meta http-equiv='refresh' content='0;url=$URL'>";
					
				}
				else 
				{
					echo "<div id='errors'>";
						echo "<ul>";
						foreach($errors as $error) 
						{
						  echo "<li>".$error."</li>";
						}
						echo "</ul>";
					echo "</div>";
				}
				
			}
		?>
			<!-- STEP ONE -->
			<div id="step_one">
				<h3 class="center">Step 1: Getting Started</h3>
				<p class="center">Don't worry, we won't give anyone your email.</p>
				<form method="POST" action="#nav_bar">
					<table>
						<tr>
							<td>First Name</td>
							<td class="right"><input type='text' name="first_name" value="<?php echo $first_name; ?>"></td>
						</tr>
						<tr>
							<td>Last Name</td>
							<td class="right"><input type='text' name="last_name" value="<?php echo $last_name; ?>"></td>
						</tr>
						<tr>
							<td>Email</td>
							<td class="right"><input type='text' name="email" value="<?php echo $email; ?>"></td>
						</tr>
						<tr>
							<td>Password</td>
							<td class="right"><input type='password' name="password"></td>
						</tr>
						<tr>
							<td colspan="2" style="font-size: 14px;"><input <?php if($legal_agreement=="yes"){echo "checked";}?> type="checkbox" name="legal_agreement" value="yes">By clicking here, I agree that I have read and agree with the <a target="_blank" href="privacy.php">privacy policy</a>.</td>
						</tr>
					</table>
					<input type="submit" name="submit_one" value="next" class="next_button">
				</form>
			</div>
		<?php
		}
		else
		{
			$user_id = $_SESSION['user_id'];

			$check_address_query = $db_con->prepare("SELECT phone_number, phone_carrier, address, address2 FROM users WHERE user_id=:user_id");
			$check_address_query->bindParam(':user_id',$user_id);
			$check_address_query->execute();
			while($check_address_row = $check_address_query->fetch(PDO::FETCH_ASSOC))
			{
				$db_phone_number = $check_address_row['phone_number'];
				$db_address = $check_address_row['address'];
				$db_address2 = $check_address_row['address2'];
			}
			if($db_phone_number == '' || $db_address == 'none')
			{
				$address = "";
				$address2 = "";
				$zip_code = "";
				$phone_number = "";
				$phone_carrier = "";
				
				if(isset($_POST['submit_two']))
				{
					$address = mysql_real_escape_string($_POST['address']);
					$address2 = mysql_real_escape_string($_POST['address2']);
					$zip_code = mysql_real_escape_string($_POST['zip_code']);
					$phone_number = mysql_real_escape_string($_POST['phone_number']);
					$phone_carrier = mysql_real_escape_string($_POST['phone_carrier']);
					
					if($address == "" || $zip_code == "" || $phone_number == "" || $phone_carrier == "")
						$errors[] = "Please fill out all of the required fields.";
					if(!preg_match("/^([1]-)?[0-9]{3}-[0-9]{3}-[0-9]{4}$/i", $phone_number))
						$errors[] = 'Please enter a valid phone number';
					
					if(empty($errors))
					{
						$update_delivery_query = $db_con->prepare("
						UPDATE users 
						SET phone_number=:phone_number, phone_carrier=:phone_carrier, address= :address, address2=:address2 WHERE user_id=:user_id");
						$update_delivery_query->bindParam('phone_number', $phone_number);
						$update_delivery_query->bindParam('phone_carrier', $phone_carrier);
						$update_delivery_query->bindParam('address', $address);
						$update_delivery_query->bindParam('address2', $address2);
						$update_delivery_query->bindParam('user_id', $user_id);
						
						$update_delivery_query->execute();
						
						$URL = "join.php#nav_bar";
						echo "<meta http-equiv='refresh' content='0;url=$URL'>";
						
					}
					else 
					{
						echo "<div id='errors'>";
							echo "<ul>";
							foreach($errors as $error) 
							{
							  echo "<li>".$error."</li>";
							}
							echo "</ul>";
						echo "</div>";
					}
				}
			
		?>
				<!-- STEP TWO -->
				<div id="step_two">
					<h3 class="center">Step 2: Almost done</h3>
					<p class="center">We know you're busy. We'll come right to your door. <br />Please provide your <b>ROOM</b> address, not your mailing address.<br />(Yes, that means dorms or offices too!></p>
					<form method="POST" action="#nav_bar">
						<table>
							<tr>
								<td>Address</td>
								<td class="right"><input type='text' name="address" value="<?php echo $address; ?>"></td>
							</tr>
							<tr>
								<td>Address 2</td>
								<td class="right"><input type='text' name="address2" value="<?php echo $address2; ?>"></td>
							</tr>
							<tr>
								<td>Zip Code</td>
								<td class="right"><input type='text' name="zip_code" value="<?php echo $zip_code; ?>"></td>
							</tr>
							<script type="text/javascript">
								function isNumberKey(evt, previous_value)
								{
									var charCode = (evt.which) ? evt.which : event.keyCode
									
									if (charCode > 31 && (charCode < 48 || charCode > 57))
										return false;
										
									var last_value = String.fromCharCode(charCode);
									var current_value = previous_value+last_value;
									if (current_value.length>12)
										return false;
									
									if(current_value.length==4 || current_value.length==8)
										document.getElementById('phone_input').value = previous_value+"-";
									
									return true;
								}
							</script>
							<tr>
								<td>Phone Number</td>
								<td class="right"><input type='text' name="phone_number" id="phone_input" onkeypress="return isNumberKey(event, this.value);" placeholder="###-###-####" value="<?php echo $phone_number; ?>"></td>
							</tr>
							<tr>
								<td>Phone Carrier</td>
								<td class="right">
									<select name="phone_carrier">
										<option value="">Select Carrier</option>
										<option value="verizon">Verizon Wireless</option>
										<option value="att">AT&amp;T </option>
										<option value="sprint">Sprint Nextel</option>
										<option value="tmobile">T-Mobile USA</option>
										<option value="other">Other</option>
									</select>
								</td>
							</tr>
						</table>
						<input style='margin-top: 20px;' type="submit" name="submit_two" value="next" class="next_button">
					</form>
				</div>
			<?php
			}
			else
			{
				$referral_accurate = "";
				if(isset($_COOKIE['referral_code']))
				{
					$referral_code = $_COOKIE['referral_code'];
					
					$refer_code_query = $db_con->prepare("SELECT user_id, first_name FROM users WHERE subscription='active' OR subscription='paused' ORDER BY user_id");
					$refer_code_query->execute();
					
					while($refer_code_row = $refer_code_query->fetch(PDO::FETCH_ASSOC))
					{
						$refer_code_row_user_id = $refer_code_row['user_id'];
						$refer_code_row_first_name = $refer_code_row['first_name'];
						$test_refer_id = md5(md5("refer").md5($refer_code_row_user_id));
						if($test_refer_id==$referral_code)
						{
							$referral_accurate = 'yes';
							break;
						}
					}
				}
				
				$check_active_query = $db_con->prepare("SELECT subscription FROM users WHERE user_id=:user_id");
				$check_active_query->bindParam('user_id', $user_id);
				$check_active_query->execute();
				
				while($check_active_row = $check_active_query->fetch(PDO::FETCH_ASSOC))
				{
					$subscription_status = $check_active_row['subscription'];
				}
				if($subscription_status !='active')
				{
					if($subscription_status =='admin')
					{
						$URL = "member.php";
						echo "<meta http-equiv='refresh' content='0;url=$URL'>";
					}
					
					if($subscription_status =='paused')
					{
						$URL = "member.php";
						echo "<meta http-equiv='refresh' content='0;url=$URL'>";
					}
					
					$error = "";
					$success = "";
					if(isset($_POST['submit_three']))
					{
						Stripe::setApiKey("");
						try
						{
							if (!isset($_POST['stripeToken']))
							  throw new Exception("The Stripe Token was not generated correctly");
							
							// get the credit card details submitted by the form
							$token = $_POST['stripeToken'];
							$using_discount =false;
							
							if(isset($_POST['coupon_code']) && strlen(trim($_POST['coupon_code'])) > 0) 
							{
								$referral_coupon_query = $db_con->prepare("SELECT user_id, first_name FROM users WHERE subscription='active' OR subscription='paused' ORDER BY user_id");
								$referral_coupon_query->execute();
								
								while($referral_coupon_row = $referral_coupon_query->fetch(PDO::FETCH_ASSOC))
								{
									$referral_coupon_row_user_id = $referral_coupon_row['user_id'];
									$referral_coupon_row_first_name = strtoupper($referral_coupon_row['first_name']);
									
									$test_referral_coupon = $referral_coupon_row_first_name.$referral_coupon_row_user_id;
									if($test_referral_coupon==strtoupper(trim($_POST['coupon_code'])))
									{
										$referral_coupon_status = "referral_coupon";
										break;
									}
								}
								
								if($referral_coupon_status != "referral_coupon")
								{
									$using_discount = true;
									// we have a discount code, now check that it is valid			
									try 
									{
										$coupon = Stripe_Coupon::retrieve( trim( $_POST['coupon_code'] ) );
										// if we got here, the coupon is valid
									} 
									catch (Exception $e) 
									{
										// an exception was caught, so the code is invalid
										throw new Exception('The coupon code you entered is invalid. Please click back and enter a valid code, or leave it blank for no discount.');
									}
								}
							}
							
							$selected_plan = mysql_real_escape_string($_POST['selected_plan']);
							$selected_size = substr($selected_plan, -2);

							$counter = 0;
							$unit_price = 997;
							$bulk_discount = 0;
							$boxes = array('01', '02', '04', '08', '12');

							$pricing_array = array(
													'register_plan01' => 0,
													'register_plan02' => 0,
													'register_plan04' => 0,
													'register_plan08' => 0,
													'register_plan12' => 0);
							while($counter<5)
							{
								$final_price = round(($unit_price*$boxes[$counter])*(1-$bulk_discount),2);
								$pricing_array['register_plan'.($boxes[$counter])] .= $final_price;
								$bulk_discount = $bulk_discount+0.05;
								$counter++;
							}
							
							$current_price = $pricing_array[$selected_plan];
							$weeks_purchased = 4;
							if($referral_coupon_status == "referral_coupon")
								$bonus_weeks = 1;
							else
								$bonus_weeks = 0;
							
							$subscription_package = 'cardboard';
							$subscription_size = $selected_size;
							
							if(isset($_COOKIE['referral_code']))
							{
								$referral_code = $_COOKIE['referral_code'];
								
								$refer_code_query = $db_con->prepare("SELECT user_id, first_name FROM users WHERE subscription='active' OR subscription='paused' ORDER BY user_id");
								$refer_code_query->execute();
								
								while($refer_code_row = $refer_code_query->fetch(PDO::FETCH_ASSOC))
								{
									$refer_code_row_user_id = $refer_code_row['user_id'];
									$refer_code_row_first_name = $refer_code_row['first_name'];
									$test_refer_id = md5(md5("refer").md5($refer_code_row_user_id));
									if($test_refer_id==$referral_code)
									{
										$referral_accurate = 'yes';
										$referral_user_id = $refer_code_row_user_id;
										$bonus_weeks = 1;
										//$current_price = $current_price*0.9;
										break;
									}
								}
							}
							
							$amount = $current_price*$weeks_purchased;
							if($using_discount !== false) 
							{
								// calculate the discounted price
								$amount = round($amount-($amount*($coupon->percent_off/100)));
							}
							$tax = round($amount*0.06);
							$total = round($amount+ $tax);
							
							$stripe_id_query = $db_con->prepare("SELECT stripe_id FROM users WHERE user_id=".$user_id);
							$stripe_id_query->execute();
							while($stripe_id_row = $stripe_id_query->fetch(PDO::FETCH_ASSOC))
							{
								$stripe_id = $stripe_id_row['stripe_id'];
							}
							if($stripe_id!='')
							{
								$customer = Stripe_Customer::retrieve("$stripe_id");
								$customer->card = $token;
								$customer->save();
							}
							else
							{
								// create a Customer
								$customer = Stripe_Customer::create(array(
								  "card" => $token,
								  "description" => $user_id)
								);
								$new_stripe_id = $customer->id;

								$stripe_id_update = $db_con->prepare("UPDATE users SET stripe_id=:new_stripe_id WHERE user_id=".$user_id);
								$stripe_id_update->execute(array(':new_stripe_id'=>$new_stripe_id));
							}
							
							// charge the Customer instead of the card
							Stripe_Charge::create(array(
							  "amount" => $total, # amount in cents, again
							  "currency" => "usd",
							  "customer" => $customer->id)
							);
							
							$query = $db_con->prepare("
							UPDATE users
							SET subscription = 'active', credits = :weeks_purchased, subscription_size = :subscription_size, subscription_package = :subscription_package, selected_plan=:selected_plan WHERE user_id=:user_id"
							);
							$total_weeks_purchased = $weeks_purchased+$bonus_weeks;
							$query->bindParam(':weeks_purchased', $total_weeks_purchased);
							$query->bindParam(':subscription_size', $subscription_size);
							$query->bindParam(':subscription_package', $subscription_package);
							$query->bindParam(':selected_plan', $selected_plan);
							$query->bindParam(':user_id', $user_id);
							$query->execute();
							
							$success = '<p>Your payment was successful.</p>';
							
							$date = date('Y-m-d G:i:s');
							$purchase_activity_query = $db_con->prepare("INSERT INTO activity VALUES ('', :date, :user_id, 'add_credits', :description, :credits)");
							$purchase_activity_query->bindParam(':date', $date);
							$purchase_activity_query->bindParam(':user_id', $user_id);
							$purchase_activity_query->bindParam(':credits', $total_weeks_purchased);
							$purchase_activity_query->bindParam(':description', $selected_plan);
							$purchase_activity_query->execute();
							
							$query_info = $db_con->prepare("SELECT first_name, email, address, address2 FROM users WHERE user_id=:user_id");
							$query_info->execute(array(':user_id'=>$user_id));
							
							while($row= $query_info->fetch(PDO::FETCH_ASSOC))
							{
								$first_name = $row['first_name'];
								$address_string = $row['address']." ".$row['address2'];
								$email = $row['email'];
							}
								$to = $email;
								$subject = "FROOTS & Co. Subscription";
								$from = "The FROOTS & Co. Team <admin@froots.co>";
							
							$amount = round($amount,2)/100;
							$tax = $tax/100;
							$total = $total/100;
							
							if(isset($_COOKIE['referral_code'])&&$referral_accurate=='yes')
							{
								$referer = mysql_real_escape_string($referral_user_id);
								
								if($referral_coupon_status != "referral_coupon")
								{
									$add_referral_query = $db_con->prepare("UPDATE users SET credits=credits + 1 WHERE user_id=:referer");
									$add_referral_query->execute(array(':referer'=>$referer));
									
									$referral_activity_query = $db_con->prepare("INSERT INTO activity VALUES ('', :date, :user_id, 'add_credits', 'referral link credits', '1')");
									$referral_activity_query->bindParam(':date', $date);
									$referral_activity_query->bindParam(':user_id', $referer);
									$referral_activity_query->execute();
								}
								else
								{
									$referral_activity_query = $db_con->prepare("INSERT INTO activity VALUES ('', :date, :user_id, 'add_credits', 'referral link credits', '0')");
									$referral_activity_query->bindParam(':date', $date);
									$referral_activity_query->bindParam(':user_id', $referer);
									$referral_activity_query->execute();
								}
								/*$referral_check_query = $db_con->prepare("SELECT referrals FROM users WHERE user_id=:referer");
								$referral_check_query->execute(array(':referer'=>$referer));
								
								while($referral_check_row = $referral_check_query->fetch(PDO::FETCH_ASSOC))
								{
									$referrals_check = $referral_check_row['referrals'];
									if($referrals_check % 2 == 0)
									{
										$add_credit_query = $db_con->prepare("UPDATE users SET credits=credits + 1 WHERE user_id=:referer");
										$add_credit_query->execute(array(':referer'=>$referer));
										
										$date = date('Y-m-d G:i:s');
										
										$referral_credit_activity_query = $db_con->prepare("INSERT INTO activity VALUES ('', :date, :user_id, 'add_credits', 'referral credits', '1')");
										$referral_credit_activity_query->bindParam(':date', $date);
										$referral_credit_activity_query->bindParam(':user_id', $referer);
										$referral_credit_activity_query->execute();
									}
								}*/
							}
							
							
							if($referral_coupon_status == "referral_coupon")
							{
								$add_credit_query = $db_con->prepare("UPDATE users SET credits=credits + 1 WHERE user_id=:referer");
								$add_credit_query->execute(array(':referer'=>$referral_coupon_row_user_id));
								
								$date = date('Y-m-d G:i:s');
								
								$referral_credit_activity_query = $db_con->prepare("INSERT INTO activity VALUES ('', :date, :user_id, 'add_credits', 'referral coupon credits', '1')");
								$referral_credit_activity_query->bindParam(':date', $date);
								$referral_credit_activity_query->bindParam(':user_id', $referral_coupon_row_user_id);
								$referral_credit_activity_query->execute();
							}
							
include 'include/email_top.php';
include 'include/email_bottom.php';

$body = "
Hello $first_name,<br/><br/>

This is a confirmation email of your purchase. You have subscribed to our fruit delivery service. Your payment and delivery details are below:<br /><br />

Address: $address_string <br />
Price: $$amount (+$tax tax) = $$total<br /><br />

If you need to change any of these details, please reply to this email with your concerns.<br /><br />

Thank you,<br/>
The Froots & Co. Team
";

								//function to send email
								
								include_once('include/ses.php');
								$ses = new SimpleEmailService('', '');
								$ses->listVerifiedEmailAddresses();
								
								$m = new SimpleEmailServiceMessage();
								$m->addTo($to);
								$m->setFrom($from);
								$m->setSubject($subject);
								$m->setMessageFromString(null, $bodyhead.$body.$bodyfoot);

								$ses->sendEmail($m);
								
								$URL = "member.php";
								echo "<meta http-equiv='refresh' content='0;url=$URL'>";
								
						}
						catch (Exception $e) 
						{
							$error = $e->getMessage();
						}
					}
			?>
					<div id="error-section"></div>
					<span class="payment-errors" ><?= $error ?></span>
					<span class="payment-success"><?= $success ?></span>
					<!-- STEP THREE -->
					<div id="step_three">
						<h3 style="text-align: center; margin-bottom: 0;">Step 3: Choose your subscription</h3>
						<p style="text-align: center; margin-top: 0; margin-bottom: 40px">Our members can cancel their <b style="color: #dc5151;">recurring bill</b> at any time, no questions asked!</p>
						<script type="text/javascript">
							 $(document).ready(function(){
								$(".nutrional_benefits_rows").toggle("fast");
								
								$(".nutrional_benefits_row_selector").click(function(event){
									$(".nutrional_benefits_rows").toggle("fast");
								});
								
								$(".fruit_selection_rows").toggle("fast");
								
								$(".fruit_selection_row_selector").click(function(event){
									$(".fruit_selection_rows").toggle("fast");
								});
								
							 });
						</script>
						<div id="price_comparison">
							<table>
								<?php
								//cost row
								$counter = 0;
								$unit_price = 9.97;
								$display_price = 9.97;
								$discount = 0;
								$boxes = array(1, 2, 4, 8, 12);
								echo "<tr>";
									echo "<td class='title_column' >Cost/wk</td>";
									while($counter<5)
									{
										$display_price = number_format(round(($unit_price*$boxes[$counter])*(1-$discount),2), 2);
										echo "<td>$$display_price</td>";
										$discount = $discount+0.05;
										$counter++;
									}
								echo "</tr>";
								
								//people row
								$counter = 0;
								$people = array(1, 2, 4, 7, 10);
								$range = "";
								echo "<tr>";
									echo "<td class='title_column price_comparison_rows' >People</td>";
									while($counter<5)
									{
										if($people[$counter]>1)
											$range = "-".($people[$counter]+$counter);
										if($range=="-10")
											$range="-9";
										echo "<td class='price_comparison_rows'>$people[$counter]$range</td>";
										$counter++;
									}
								echo "</tr>";
								
								//fruit row
								$counter = 0;
								$fruit = array(8, 16, 32, 64, 96);
								echo "<tr>";
									echo "<td class='title_column price_comparison_rows'>Servings</td>";
									while($counter<5)
									{
										echo "<td class='price_comparison_rows'>".$fruit[$counter]."</td>";
										$counter++;
									}
								echo "</tr>";
								
								//discount row
								$counter = 0;
								$discount = 0;
								echo "<tr>";
									echo "<td class='title_column price_comparison_rows' >Discount</td>";
									while($counter<5)
									{
										echo "<td class='price_comparison_rows'>$discount%</td>";
										$counter++;

											$discount = $discount+5;
									}
								echo "</tr>";
								
								//unit cost row
								$counter = 0;
								$discount = 0;
								
								echo "<tr>";
									echo "<td class='title_column price_comparison_rows' >Cost/Box</td>";
									while($counter<5)
									{
										$unit_cost = number_format(((100-$discount)/100)*(9.97),2);
										echo "<td class='price_comparison_rows'>$$unit_cost</td>";
										$counter++;

											$discount = $discount+5;
									}
								echo "</tr>";
								
								//delivery row
								$counter = 0;
								echo "<tr>";
									echo "<td class='title_column'>Free Delivery</td>";
									while($counter<5)
									{
										echo "<td><img src='images/join_checks.png' alt='checked off attribute'/></td>";
										$counter++;
									}
								echo "</tr>";
									
								//-----------------------------
								//----NUTRITIONAL BENEFITS-----
								//-----------------------------
								echo "<tr style='cursor:pointer;' class='nutrional_benefits_row_selector'><td colspan='6'><h4>Nutritional Benefits</h4></td></tr>";
									
									//dietary fiber row
									$counter = 0;
									$boxes = array(1, 2, 4, 8, 12);
									$diet_fiber = 6;
									
									echo "<tr class='nutrional_benefits_rows'>";
										echo "<td class='title_column price_comparison_rows'>Dietary Fiber</td>";
										while($counter<5)
										{
											$fruit = 8*($boxes[$counter]);
											$total_df = $fruit*$diet_fiber;
											echo "<td class='price_comparison_rows'>$total_df g</td>";
											$counter++;
										}
									echo "</tr>";
									
									//calories avoided row
									$counter = 0;
									$boxes = array(1, 2, 4, 8, 12);
									$calories = 60;
									
									echo "<tr class='nutrional_benefits_rows'>";
										echo "<td class='title_column'>Calories Avoided</td>";
										while($counter<5)
										{
											$fruit = 8*($boxes[$counter]);
											$total_cal = $fruit*$calories;
											echo "<td>$total_cal cal</td>";
											$counter++;
										}
									echo "</tr>";
								
								//button row
								$counter = 0;
								$boxes = array('01', '02', '04', '08', '12');
								echo "<tr>";
									echo "<td class='title_column'>Select Plan:</td>";
									while($counter<5)
									{
										$styled_selection = "";
										if($counter==0)
											$styled_selection = "background-color: #212121 !important; color: #F9F9F9;";
										?>
										<td class="pointer" id='register_plan<?php echo $boxes[$counter];?>' onClick='changePlan(this.id)' style="<?php echo $styled_selection; ?>">Register</td>
										<?php
										$counter++;
									}
								echo "</tr>";
								?>
							</table>
						</div>
						<form method="POST" action="#nav_bar" id="payment-form" name="form">
							<table>
								<tr>
									<input type="hidden" name="selected_plan" id="selected_plan" value="register_plan01" onChange="updatePrice(this.value)">
								</tr>
								<tr>
									<div class="form-row">
										<td><label>card number:</label></td>
										<td><input type="text" size="20" autocomplete="off" class="card-number" /></td>
									</div>
								</tr>
								<tr>
									<div class="form-row">
										<td><label>security code:</label></td>
										<td><input type="text" size="4" autocomplete="off" class="card-cvc" /></td>
									</div>
								</tr>
								<tr>
									<div class="form-row">
										<td><label>expiration:</label></td>
										<td><input type="text" size="3" class="card-expiry-month" placeholder="MM"/><span style="margin: 0 3px; font-size:20px;">/</span><input type="text" size="5" class="card-expiry-year" placeholder="YYYY"/></td>
									</div>
								</tr>
								<tr>
									<div>
										<td><label>coupon code:</label></td>
										<td><input type="text" size="8" name="coupon_code" id="coupon_code" onKeyUp="updatePrice()"/></td>
									</div>
								</tr>
							</table>
							<div id="invoice">
								<table>
									<tr>
										<td colspan="4" class="invoice_title">Payment Summary:</td>
									</tr>
									<tr>
										<th>item</th>
										<th>quantity</th>
										<th>unit price</th>
										<th class="right">total</th>
									</tr>
									<?php
									$default_rate = 9.97;
									$default_weeks = 4;
									
									if($referral_accurate=='yes')
										$referral_discount = round(9.97*4*-0.1,2);
									else
										$referral_discount = 0;
									
									$subtotal = $default_rate*$default_weeks+$referral_discount;
									$tax = round($subtotal*0.06, 2);
									$total = $subtotal+$tax;
									?>
									<tr>
										<td class="invoice">Number of weeks </td>
										<td class="invoice"><?php if($referral_accurate=='yes'){echo "5";}else{echo "4";} ?></td>
										<td class="invoice">9.97</td>
										<td class="right invoice">39.88</td>
									</tr>
									<tr>
										<td class="invoice">Number of servings </td>
										<td class="invoice">8</td>
										<td class="invoice"></td>
										<td class="right invoice"></td>
									</tr>
									<?php if($referral_accurate=='yes') { ?>
									<tr>
										<td class="invoice" colspan='3'><b>You and <?php echo $refer_code_row_first_name; ?> earned a free week!</b></td>
										<td class="right invoice"></td>
									</tr>
									<?php } ?>
									<tr>
										<td colspan="3" class="right invoice">Subtotal:</td>
										<td class="right invoice"><?php echo $subtotal; ?></td>
									</tr>
									<tr>
										<td colspan="3 invoice" class="right invoice">Tax:</td>
										<td class="right invoice"><?php echo $tax; ?></td>
									</tr>
									<tr>
										<td colspan="3" class="right">Total:</td>
										<td class="right"><?php echo "$".$total; ?></td>
									</tr>
								</table>
							</div>
							<input type="hidden" name="submit_three">
							<p style="text-align: center;">Remember, you can cancel your <b style="color: #dc5151;">recurring bill</b> at any time, straight from your profile!</p>
							<button type="submit" class="next_button" class="submit-button">finish</button>
						</form>
					</div>
		<?php
				}
				else
				{
					$URL = "member.php";
					echo "<meta http-equiv='refresh' content='0;url=$URL'>";
				}
			}
		}
		
		include 'include/footer.php'; 
		?>
	</div>
	</body>

</html>