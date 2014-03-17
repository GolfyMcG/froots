	<?php session_start();
	include 'include/head.php'; ?>
	
	<body class="wrap">
	<?php
	if(isset($_SESSION['user_id']))
	{
		//initialize user information process
		$user_id = mysql_real_escape_string($_SESSION['user_id']);
		$user_info_query = $db_con->prepare("SELECT * FROM users WHERE user_id=:user_id");
		$user_info_query->bindParam('user_id', $user_id);
		$user_info_query->execute();
		while($user_info_row = $user_info_query->fetch(PDO::FETCH_ASSOC))
		{
			$user_info = array(
			'user_id' => $user_id,
			'first_name' => $user_info_row['first_name'],
			'last_name' => $user_info_row['last_name'],
			'email' => $user_info_row['email'],
			'subscription' => $user_info_row['subscription'],
			'phone_number' => $user_info_row['phone_number'],
			'phone_carrier' => $user_info_row['phone_carrier'],
			'address' => $user_info_row['address'],
			'address2' => $user_info_row['address2'],
			'credits' => $user_info_row['credits'],
			'referrals' => $user_info_row['referrals'],
			'selected_plan' => $user_info_row['selected_plan'],
			'subscription_size' => $user_info_row['subscription_size'],
			'stripe_id' => $user_info_row['stripe_id']
			);
		}
		if($user_info['subscription']=="inactive" || $user_info['subscription']=="delinquent")
		{
			$URL = 'join.php';
			echo "<meta http-equiv='refresh' content='0;url=$URL'>";
		}
	}
	else
	{
		$URL = 'join.php';
		echo "<meta http-equiv='refresh' content='0;url=$URL'>";
	}
	?>
	
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
		
			function updateInput(ish){
					if(document.getElementById(ish).value == 'yes')
					{
						document.getElementById(ish).value = 'no';
						document.getElementById("word_"+ish).style.cssText = 'color: #A63C45; cursor:pointer;';
					}
					else
					{
						document.getElementById(ish).value = 'yes';
						document.getElementById("word_"+ish).style.cssText = 'color: #87BE77; cursor:pointer;';
					}
				}
			
			function updateReceived(item_number)
			{
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
						document.getElementById("received"+item_number).innerHTML=xmlhttp.responseText;
					}
				}
				xmlhttp.open("GET","include/update_received.php?received_id="+item_number,true);
				xmlhttp.send();
			}
			function adminForm(edit_user_id)
			{
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
						document.getElementById("admin_settings_form").innerHTML=xmlhttp.responseText;
					}
				}
				xmlhttp.open("GET","include/admin_settings_update.php?edit_user_id="+edit_user_id,true);
				xmlhttp.send();
			}
		</script>
	
	<div id="side_shadow">
		<?php include 'include/nav_bar.php'; ?>
		<h3 class="center" style="margin-bottom: 0px;">Member Page</h3>
		<?php
		//********************************
		//MEMBER OPTIONS UPDATE PROCESSING
		//********************************
		
		if(isset($_POST['submit_updates']))
		{
			//******************************
			//USER SETTINGS ERROR PROCESSING
			//******************************
			$new_phone_number = mysql_real_escape_string($_POST['phone_number']);
			$new_phone_carrier = mysql_real_escape_string($_POST['phone_carrier']);
			$new_comments = mysql_real_escape_string($_POST['comments']);
			$new_address = mysql_real_escape_string($_POST['address']);
			$new_address2 = mysql_real_escape_string($_POST['address2']);
			$new_comments = mysql_real_escape_string($_POST['comments']);
			$new_subscription = mysql_real_escape_string($_POST['subscription']);
			$new_selected_plan = mysql_real_escape_string($_POST['selected_plan']);			
			
			if($new_address == "" || $new_phone_number == "" || $new_phone_carrier == "" || $new_subscription == "" || $new_selected_plan == "")
				$errors[] = "Please fill out all of the required fields.";
			if(!preg_match("/^([1]-)?[0-9]{3}-[0-9]{3}-[0-9]{4}$/i", $new_phone_number))
				$errors[] = 'Please enter a valid phone number';
			
			//****************************
			//FRUIT LIKES ERROR PROCESSING
			//****************************
			$fruit_options = $_POST['fruit_options'];
			foreach($fruit_options as $fruit_option) 
			{
			  if($fruit_option!="")
				$fruit_liked[] = mysql_real_escape_string($fruit_option);
			}
			
			if(count($fruit_liked)<3)
				$errors[] = "You must like at least 3 types of fruit each week";
			
			if(empty($errors))
			{
				$update_delivery_query = $db_con->prepare("
				UPDATE users 
				SET phone_number=:phone_number, phone_carrier=:phone_carrier, address= :address, address2=:address2, subscription=:subscription, selected_plan=:selected_plan  WHERE user_id=:user_id");
				$update_delivery_query->bindParam('phone_number', $new_phone_number);
				$update_delivery_query->bindParam('phone_carrier', $new_phone_carrier);
				$update_delivery_query->bindParam('address', $new_address);
				$update_delivery_query->bindParam('address2', $new_address2);
				$update_delivery_query->bindParam('subscription', $new_subscription);
				$update_delivery_query->bindParam('selected_plan', $new_selected_plan);
				$update_delivery_query->bindParam('user_id', $user_id);
				$update_delivery_query->execute();
				
				if($new_subscription !=$user_info['subscription'])
				{
					if($new_subscription == 'paused')
					{
						echo "<div style='margin: 10px 0;'id='errors'>";
							echo "<p>You have paused your deliveries. Please allow for 2 days notice for a pause to take effect. Contact us at <a href='mailto:admin@froots.co'>admin@froots.co</a> if you need to pause for a sooner delivery.";
						echo "</div>";
						
						//record the activity
						$date = date('Y-m-d G:i:s');
						$audit_name = 'pausing deliveries';
						$audit_description = 'routine pause';
						$audit_quantity = '1';
						$audit_activity_query = $db_con->prepare("INSERT INTO activity VALUES ('', :date, :user_id, :audit_name, :audit_description, :audit_quantity)");
						$audit_activity_query->bindParam(':date', $date);
						$audit_activity_query->bindParam(':user_id', $user_id);
						$audit_activity_query->bindParam(':audit_name', $audit_name);
						$audit_activity_query->bindParam(':audit_description', $audit_description);
						$audit_activity_query->bindParam(':audit_quantity', $audit_quantity);
						$audit_activity_query->execute();
						
						$to = $user_info['email'];
						$subject = "FROOTS & Co. Delivery Paused";
						$from = "The FROOTS & Co. Team <admin@froots.co>";

include 'include/email_top.php';
include 'include/email_bottom.php';
				
						$body = "
Hello ".$user_info['first_name'].",<br/><br/>

This is just a brief notification of your service being paused. If this wasn't intended then please visit your <a href='www.froots.co/login.php'>profile</a> to make the desired changes.<br/><br/>

If you believe this was fraudulent then please contact us immediately at <a href='mailto:admin@froots.co'>admin@froots.co</a>. <br/><br/>

Stay fresh.<br/>
The FROOTS Team
";


						//function to send email
						
						include_once('include/ses.php');
						$ses = new SimpleEmailService('', '');
						$ses->listVerifiedEmailAddresses();
						
						$m = new SimpleEmailServiceMessage();
						$m->addTo($to);
						$m->addBCC('admin@froots.co');
						$m->setFrom($from);
						$m->setSubject($subject);
						$m->setMessageFromString(null, $bodyhead.$body.$bodyfoot);

						$ses->sendEmail($m);
						
					}
					elseif($new_subscription != 'paused')
					{
						echo "<div style='margin: 10px 0;'id='errors'>";
							echo "<p>Your account is active again and you will continue to receive deliveries!";
						echo "</div>";
						
						//record the activity
						$date = date('Y-m-d G:i:s');
						$audit_name = 'unpausing deliveries';
						$audit_description = 'routine unpause';
						$audit_quantity = '1';
						$audit_activity_query = $db_con->prepare("INSERT INTO activity VALUES ('', :date, :user_id, :audit_name, :audit_description, :audit_quantity)");
						$audit_activity_query->bindParam(':date', $date);
						$audit_activity_query->bindParam(':user_id', $user_id);
						$audit_activity_query->bindParam(':audit_name', $audit_name);
						$audit_activity_query->bindParam(':audit_description', $audit_description);
						$audit_activity_query->bindParam(':audit_quantity', $audit_quantity);
						$audit_activity_query->execute();
						
						$to = $user_info['email'];
						$subject = "FROOTS & Co. Delivery Unpaused";
						$from = "The FROOTS & Co. Team <admin@froots.co>";

include 'include/email_top.php';
include 'include/email_bottom.php';
				
						$body = "
Hello ".$user_info['first_name'].",<br/><br/>

This is just a brief notification of your service being unpaused. If this wasn't intended then please visit your <a href='www.froots.co/login.php'>profile</a> to make the desired changes.<br/><br/>

If you believe this was fraudulent then please contact us immediately at <a href='mailto:admin@froots.co'>admin@froots.co</a>. <br/><br/>

Stay fresh.<br/>
The FROOTS Team
";


						//function to send email
						
						include_once('include/ses.php');
						$ses = new SimpleEmailService('', '');
						$ses->listVerifiedEmailAddresses();
						
						$m = new SimpleEmailServiceMessage();
						$m->addTo($to);
						$m->addBCC('admin@froots.co');
						$m->setFrom($from);
						$m->setSubject($subject);
						$m->setMessageFromString(null, $bodyhead.$body.$bodyfoot);

						$ses->sendEmail($m);
						
					}
				}
				
				if($new_selected_plan !=$user_info['selected_plan'])
				{
					if($new_selected_plan == 'cancel')
					{
						echo "<div style='margin: 10px 0;'id='errors'>";
							echo "<p>You have cancelled your recurring billing. This does not mean you are refunded your current credits. If you would like to discuss a refund then please contact us at <a href='mailto:admin@froots.co'>admin@froots.co</a>.";
						echo "</div>";
						
						//record the activity
						$date = date('Y-m-d G:i:s');
						$audit_name = 'cancel billing';
						$audit_description = 'routine cancel';
						$audit_quantity = '1';
						$audit_activity_query = $db_con->prepare("INSERT INTO activity VALUES ('', :date, :user_id, :audit_name, :audit_description, :audit_quantity)");
						$audit_activity_query->bindParam(':date', $date);
						$audit_activity_query->bindParam(':user_id', $user_id);
						$audit_activity_query->bindParam(':audit_name', $audit_name);
						$audit_activity_query->bindParam(':audit_description', $audit_description);
						$audit_activity_query->bindParam(':audit_quantity', $audit_quantity);
						$audit_activity_query->execute();
						
						$to = $user_info['email'];
						$subject = "FROOTS & Co. Delivery Cancelled";
						$from = "The FROOTS & Co. Team <admin@froots.co>";

include 'include/email_top.php';
include 'include/email_bottom.php';
				
						$body = "
Hello ".$user_info['first_name'].",<br/><br/>

We are sorry you were not satisfied with our fresh fruit service.<br /><br />

This is just a brief confirmation that auto-renewal has been cancelled. Your service will continue until your account has run out of credits, and account will NOT be renewed.<br /><br />

You currently have enough credits for the next ".$user_info['credits']." weeks.<br /><br /> 

We appreciate the business and the weeks you spent with us.<br /><br />

Stay fresh.<br/>
The FROOTS Team
";


						//function to send email
						
						include_once('include/ses.php');
						$ses = new SimpleEmailService('', '');
						$ses->listVerifiedEmailAddresses();
						
						$m = new SimpleEmailServiceMessage();
						$m->addTo($to);
						$m->addBCC('admin@froots.co');
						$m->setFrom($from);
						$m->setSubject($subject);
						$m->setMessageFromString(null, $bodyhead.$body.$bodyfoot);

						$ses->sendEmail($m);
						
					}
					if($new_selected_plan != 'cancel')
					{
						echo "<div style='margin: 10px 0;'id='errors'>";
							echo "<p>You have reactivated your recurring billing and should expect uninterrupted service!";
						echo "</div>";
						
						//record the activity
						$date = date('Y-m-d G:i:s');
						$audit_name = 'uncancel billing';
						$audit_description = 'routine uncancel';
						$audit_quantity = '1';
						$audit_activity_query = $db_con->prepare("INSERT INTO activity VALUES ('', :date, :user_id, :audit_name, :audit_description, :audit_quantity)");
						$audit_activity_query->bindParam(':date', $date);
						$audit_activity_query->bindParam(':user_id', $user_id);
						$audit_activity_query->bindParam(':audit_name', $audit_name);
						$audit_activity_query->bindParam(':audit_description', $audit_description);
						$audit_activity_query->bindParam(':audit_quantity', $audit_quantity);
						$audit_activity_query->execute();
						
						$to = $user_info['email'];
						$subject = "FROOTS & Co. Delivery Uncancelled";
						$from = "The FROOTS & Co. Team <admin@froots.co>";

include 'include/email_top.php';
include 'include/email_bottom.php';
				
						$body = "
Hello ".$user_info['first_name'].",<br/><br/>

This is just a brief notification of your service being uncancelled. If this wasn't intended then please visit your <a href='www.froots.co/login.php'>profile</a> to make the desired changes.<br/><br/>

This means your service will be automatically billed when your account runs out of credits.<br /><br />

If you believe this was fraudulent then please contact us immediately at <a href='mailto:admin@froots.co'>admin@froots.co</a>. <br/><br/>

Stay fresh.<br/>
The FROOTS Team
";


						//function to send email
						
						include_once('include/ses.php');
						$ses = new SimpleEmailService('', '');
						$ses->listVerifiedEmailAddresses();
						
						$m = new SimpleEmailServiceMessage();
						$m->addTo($to);
						$m->addBCC('admin@froots.co');
						$m->setFrom($from);
						$m->setSubject($subject);
						$m->setMessageFromString(null, $bodyhead.$body.$bodyfoot);

						$ses->sendEmail($m);						
						
					}
				}
				
				//REFRESH USER SETTINGS FOR DISPLAY
				$user_info['phone_number'] = $new_phone_number;
				$user_info['phone_carrier'] = $new_phone_carrier;
				$user_info['address'] = $new_address;
				$user_info['address2'] = $new_address2;
				$user_info['subscription'] = $new_subscription;
				$user_info['selected_plan'] = $new_selected_plan;
				
				//UPDATE COMMMENT HISTORY
				if($new_comments!="")
				{
					$add_user_comment_query = $db_con->prepare("INSERT INTO comments VALUES('', :user_id, :new_comment)");
					$add_user_comment_query->bindParam(':user_id', $user_info['user_id']);
					$add_user_comment_query->bindParam(':new_comment', $new_comments);
					$add_user_comment_query->execute();
				}
				
				//UPDATE FRUIT PREFERENCES
				$fruit_pref_query = $db_con->prepare("SELECT * FROM fruit_preference WHERE user_id=:user_id");
				$fruit_pref_query->bindParam(':user_id', $user_info['user_id']);
				$fruit_pref_query->execute();
				
				$fruit_prefs[] = "";
				
				while($fruit_pref_row = $fruit_pref_query->fetch(PDO::FETCH_ASSOC))
				{
					$fruit_prefs[] .= ucfirst($fruit_pref_row['fruit_name']);
				}
				foreach($fruit_options as $fruit_option_name => $fruit_option_preference)
				{
					if(in_array($fruit_option_name, $fruit_prefs))
					{
						//this section is for updating
						$fruit_preference_update_query = $db_con->prepare("UPDATE fruit_preference SET preference=:fruit_option_preference WHERE user_id=:user_id && fruit_name = :fruit_option_name");
						$fruit_preference_update_query->bindParam(':user_id',$user_info['user_id']);
						$fruit_preference_update_query->bindParam(':fruit_option_name',$fruit_option_name);
						$fruit_preference_update_query->bindParam(':fruit_option_preference',$fruit_option_preference);
						$fruit_preference_update_query->execute();
					}
					else
					{
						//this section is for inserting
						$fruit_preference_insert_query = $db_con->prepare("INSERT INTO fruit_preference VALUES('', :user_id, :fruit_option_name, :fruit_option_preference)");
						$fruit_preference_insert_query->bindParam(':user_id',$user_info['user_id']);
						$fruit_preference_insert_query->bindParam(':fruit_option_name',$fruit_option_name);
						$fruit_preference_insert_query->bindParam(':fruit_option_preference',$fruit_option_preference);
						$fruit_preference_insert_query->execute();
					}
				}
			}
			else 
			{
				echo "<div style='margin: 10px 0;'id='errors'>";
					echo "<ul style='padding-left: 40px;'>";
					foreach($errors as $error) 
					{
					  echo "<li>".$error."</li>";
					}
					echo "</ul>";
				echo "</div>";
			}
		}
		?>
		<?php
		if($user_info['subscription']=='admin')
		{
		?>
			<script type="text/javascript">
				$(function() {
					$("table").tablesorter({debug: true})
					$("a.append").click(appendData);
				});
				
				function showAdmin(){
					$('#show_admin_button').fadeOut('fast');
					$('#hide_admin_button').fadeIn('fast');
					$('#admin_panel').fadeIn('fast');
				}
				function hideAdmin(){
					$('#hide_admin_button').fadeOut('fast');
					$('#show_admin_button').fadeIn('fast');
					$('#admin_panel').fadeOut('fast');
				}
			</script>
			<script type="text/javascript" src="include/js/jquery.tablesorter.js"></script>
			<?php
			//**********************
			//ADMIN PANEL PROCESSING
			//**********************
			$display_admin_panel = 'display:none;';
			$display_toggle = 'display:block;';
			//Database Search Processing
			$search_results_echo = "";
			$fruit_pref_column = "";
			$comment_column = "";
			
			if(isset($_GET['submit_search']))
			{
				$display_admin_panel = 'display:block;';
				$display_toggle = 'display:none;';
				if(isset($_GET['column']))
					$column = mysql_real_escape_string($_GET['column']);
				if(isset($_GET['column_value']))
					$column_value = mysql_real_escape_string($_GET['column_value']);
				if(isset($_GET['column2']))
					$column2 = mysql_real_escape_string($_GET['column2']);
				else
					$column2 = "";
				if(isset($_GET['column_value2']))
					$column_value2 = mysql_real_escape_string($_GET['column_value2']);
				else
					$column_value2 = "";
				if(empty($_GET['display_column']))
				{
					$display_column[]='first_name';
					$display_column[]='last_name';
					$display_column[]='phone_number';
					$display_column[]='address';
					$display_column[]='address2';
					$display_column[]='credits';
					$display_column[]='subscription_size';
					$display_column[]='user_id';
					
					$fruit_pref_column = true;
					$comment_column = true;
				}
				else
					$display_column = $_GET['display_column'];
				
				if($column_value2!="" && $column2!="")
				{
					$search_criterion = $column." = "."'".$column_value."' && ".$column2." = '".$column_value2."'";
					$admin_user_query = $db_con->prepare("SELECT * FROM users WHERE ".$column." = :column_value && ".$column2." = :column_value2");
					$admin_user_query->bindParam(':column_value2', $column_value2);
				}
				else
				{
					$search_criterion = $column." = '".$column_value."'";
					$admin_user_query = $db_con->prepare("SELECT * FROM users WHERE ".$column." = :column_value");
				}
				
				$admin_user_query->bindParam(':column_value', $column_value);
				
				$admin_user_query->execute();
				$search_results_echo .= "<p>Results for: ".$search_criterion."</p>";
				$search_results_echo .= "<table class='tablesorter'>";
				$search_results_echo .= "<thead>
					<tr>
						";
						$column_list = "";
						$column_count = 0;
						foreach ($display_column AS $display_name)
						{
							$search_results_echo .= "<th>".str_replace("_"," ",$display_name)."</th>";
							if($column_count==0)
								$column_list .="$display_name";
							else
								$column_list .=", $display_name";
							$column_count++;
						}
						if(!empty($_GET['fruit_preference']) || $fruit_pref_column == true)
						{
							$fruit_pref_column = true;
							$search_results_echo .= "<th>preferred fruits</th>";
						}
						else
							$fruit_pref_column = false;
						if(!empty($_GET['comments']) || $comment_column == true)
						{
							$comment_column = true;
							$search_results_echo .= "<th>comments</th>";
						}
						else
							$comment_column = false;
							
				$search_results_echo .= "
					<th>R?</th>
					</tr>
				</thead>";
				while($admin_user_row = $admin_user_query->fetch(PDO::FETCH_ASSOC))
				{
					$admin_user_id = $admin_user_row['user_id'];
					$admin_user_credits = $admin_user_row['credits'];
					$search_results_echo .= "<tr>";
					foreach ($display_column AS $display_name)
					{
						$search_results_echo .= "<td>".$admin_user_row[$display_name]."</td>";
					}
					
					if(!empty($_GET['fruit_preference']) || $fruit_pref_column == true)
					{
						$fruit_preference_display_query = $db_con->prepare("SELECT fruit_name FROM fruit_preference WHERE user_id=:admin_user_id && preference='yes' ORDER BY fruit_name");
						$fruit_preference_display_query->execute(array(':admin_user_id'=>$admin_user_id));
						
						$search_results_echo .= "<td>";
							while($fruit_preference_display_row = $fruit_preference_display_query->fetch(PDO::FETCH_ASSOC))
							{
								$search_results_echo .= $fruit_preference_display_row['fruit_name'].", ";
							}
						$search_results_echo .= "</td>";
					}
					if(!empty($_GET['comments']) || $comment_column == true)
					{
						$comment_display_query = $db_con->prepare("SELECT comment FROM comments WHERE user_id=:admin_user_id ORDER BY comment");
						$comment_display_query->execute(array(':admin_user_id'=>$admin_user_id));
						
						$search_results_echo .= "<td>";
							while($comment_display_row = $comment_display_query->fetch(PDO::FETCH_ASSOC))
							{
								$search_results_echo .= $comment_display_row['comment'].", ";
							}
						$search_results_echo .= "</td>";
					}
					$edit_on_click = 'show("#admin_settings_container", "#admin_background"); adminForm("'.$admin_user_id.'"); ';
					
					$search_results_echo .= "
						<td>
							<div id='received".$admin_user_id."'>
								<button class='received_button' onClick='updateReceived(".$admin_user_id.")'>".$admin_user_credits."</button>
							</div>
						</td>
						<td>
							<center><a style='vertical-align: middle;' href='#' onClick='".$edit_on_click."'>edit</a></center>
						</td>
					</tr>
					";
				}
				$search_results_echo .= "</table>";
				$search_results_echo .= "
				<a href='download.php?download_info=download_info&&column_list=".$column_list."&&column=".$column."&&column_value=".$column_value."&&column2=".$column2."&&column_value2=".$column_value2."&&fruit_pref_column=".$fruit_pref_column."&&comment_column=".$comment_column."' target='_blank'><button>download</button></a>
				<h5 style='margin-top:15px;margin-bottom:5px;'>New Search: </h5>
				";
			}
			
			if(isset($_GET['submit_reminder']))
			{
				$display_admin_panel = 'display:block;';
				$display_toggle = 'display:none;';
				
				if(empty($errors))
				{
					$email_reminder_query = $db_con->prepare("SELECT first_name, email FROM users WHERE subscription = 'active'");
					$email_reminder_query->execute();
					
					$email_subject = "Froots & Co. Delivery Reminder";
					$from = "The FROOTS & Co. Team <admin@froots.co>";
					
include_once('include/ses.php');
include 'include/email_top.php';
include 'include/email_bottom.php';
				$recipients = array();
				while($email_reminder_row = $email_reminder_query->fetch(PDO::FETCH_ASSOC))
				{
				
					$delivery_reminder_name = $email_reminder_row['first_name'];
					$delivery_reminder_email = $email_reminder_row['email'];
					$recipients[] .= $delivery_reminder_email;
					
					$body = "
Hey $delivery_reminder_name,<br/><br/>

You have a delicious Froot delivery coming tomorrow morning!<br /><br />

If you won't be there, you can send a message from your profile <a href='https://www.froots.co'>here</a>, otherwise sit back, relax, and we'll see you tomorrow!<br /><br />

Stay fresh,<br/>
The Froots & Co. Team
";
						//function to send email
						$ses = new SimpleEmailService('', '');
						$ses->listVerifiedEmailAddresses();
						
						$m = new SimpleEmailServiceMessage();
						$m->addTo($delivery_reminder_email);
						$m->setFrom($from);
						$m->setSubject($email_subject);
						$m->setMessageFromString(null, $bodyhead.$body.$bodyfoot);

						$ses->sendEmail($m);
					}
					
					echo "<div id='errors' style='margin: 20px 0;'>";
						echo "<ul>";
						echo "<li>email sent to:</li>";
						foreach($recipients as $recipient) 
						{
						  echo "<li>".$recipient."</li>";
						}
						echo "</ul>";
					echo "</div>";
					
				}
				else 
				{
					echo "<div id='errors' style='margin: 20px 0;'>";
						echo "<ul>";
						foreach($errors as $error) 
						{
						  echo "<li>".$error."</li>";
						}
						echo "</ul>";
					echo "</div>";
				}
			}
			
			if(isset($_GET['submit_pref_update']))
			{
				$fruit_active_query = $db_con->prepare("
								SELECT DISTINCT(p.fruit_profile_name)
								FROM fruit_profiles AS p
								INNER JOIN fruit_types AS t
								ON p.fruit_profile_id = t.fruit_type_profile_id
								WHERE t.fruit_type_active='yes'
								");
				$fruit_active_query->execute();
				//Loop through the active distinct fruit names in the DB
				while($fruit_active_query_row = $fruit_active_query->fetch(PDO::FETCH_ASSOC))
				{
					$fruit_check_query = $db_con->prepare("SELECT user_id FROM fruit_preference WHERE fruit_name=:fruit_name");
					$fruit_check_query->bindParam(':fruit_name', $fruit_active_query_row['fruit_profile_name']);
					$fruit_check_query->execute();
					
					//Create an array of users who have expressed an opinion (positive or negative) on each fruit
					while($fruit_check_query_row = $fruit_check_query->fetch(PDO::FETCH_ASSOC))
					{
						$fruit_pref_user_array[] = $fruit_check_query_row['user_id'];
					}
					//Search through a list of everyone that has finished registering
					$user_query = $db_con->prepare("SELECT user_id FROM users WHERE subscription='active' OR subscription='delinquent'");
					$user_query->execute();
					
					while($user_query_row = $user_query->fetch(PDO::FETCH_ASSOC))
					{
						//Check if each user is in each fruit's list of users
						if(!in_array($user_query_row['user_id'],$fruit_pref_user_array))
						{
							$add_fruit_pref_query = $db_con->prepare("INSERT INTO fruit_preference(preference_id, user_id, fruit_name, preference) VALUES ('',:user_id,:fruit_name,'yes')");
							$add_fruit_pref_query->bindParam(':user_id', $user_query_row['user_id']);
							$add_fruit_pref_query->bindParam(':fruit_name', $fruit_active_query_row['fruit_profile_name']);
							$add_fruit_pref_query->execute();
						}
					}
					unset($fruit_pref_user_array);
				}
			}
			
			//Email form processing
			$email_body = "";
			$email_subject = "";
			$column_value = "";
			$column_value2 = "";
			if(isset($_GET['submit_email']))
			{
				$display_admin_panel = 'display:block;';
				$display_toggle = 'display:none;';
				//check email body and subject
				if(isset($_GET['email_subject']))
				{
					if($_GET['email_subject']!="")
						$email_subject = $_GET['email_subject'];
					else
						$errors[] = "you must provide an email subject";
				}
				if(isset($_GET['email_body']))
				{
					if($_GET['email_body']!="")
						$email_body = $_GET['email_body'];
					else
						$errors[] = "you must provide an email body";
				}
				if(empty($errors))
				{
					//determine the sender
					$sender_query = $db_con->prepare("SELECT email FROM users WHERE user_id=".$user_id);
					$sender_query->execute();
					while($sender_query_row = $sender_query->fetch(PDO::FETCH_ASSOC))
					{
						$from = $sender_query_row['email'];
					}
					
					//determine the mailing list
					if(isset($_GET['column']))
						$column = mysql_real_escape_string($_GET['column']);
					else
						$column = "";
						
					if(isset($_GET['column2']))
						$column2 = mysql_real_escape_string($_GET['column2']);
					else
						$column2 = "";
					
					if(isset($_GET['column_value']))
						$column_value = mysql_real_escape_string($_GET['column_value']);
					else
						$column_value = "";
					
					if(isset($_GET['column_value2']))
						$column_value2 = mysql_real_escape_string($_GET['column_value2']);
					else
						$column_value2 = "";
					
					include_once('include/ses.php');
					include 'include/email_top.php';
					include 'include/email_bottom.php';
					
					
					if($column=="custom_email")
					{
						$recipient_emails = explode(" ", $column_value);

						foreach($recipient_emails AS $recipient_email)
						{
							$errors[] = $recipient_email;
							$body = "
	Hello,<br/><br/>

	$email_body <br/><br/>

	Stay fresh,<br/>
	The Froots & Co. Team
	";
							//function to send email
							$ses = new SimpleEmailService('', '');
							$ses->listVerifiedEmailAddresses();
							
							$m = new SimpleEmailServiceMessage();
							$m->addTo($recipient_email);
							$m->setFrom($from);
							$m->setSubject($email_subject);
							$m->setMessageFromString(null, $bodyhead.$body.$bodyfoot);

							$ses->sendEmail($m);
						}
					}
					else
					{
						
						if($column_value2!="" && $column2!="")
						{
							$search_criterion = $column." = "."'".$column_value."' && ".$column2." = '".$column_value2."'";
							$admin_email_query = $db_con->prepare("SELECT * FROM users WHERE ".$column." = :column_value && ".$column2." = :column_value2 && unsubscribe != 'unsubscribe'");
							$admin_email_query->bindParam(':column_value2', $column_value2);
						}
						else
						{
							$search_criterion = $column." = '".$column_value."'";
							$admin_email_query = $db_con->prepare("SELECT * FROM users WHERE ".$column." = :column_value && unsubscribe != 'unsubscribe'");
						}
						$admin_email_query->bindParam(':column_value', $column_value);
						$admin_email_query->execute();
						
						while ($admin_user_row = $admin_email_query->fetch(PDO::FETCH_ASSOC))
						{
							$e_user_id = $admin_user_row['user_id'];
							$e_first_name = $admin_user_row['first_name'];
							$e_last_name = $admin_user_row['last_name'];
							$e_email = $admin_user_row['email'];
							$e_phone_number = $admin_user_row['phone_number'];
							$e_address = $admin_user_row['address'];
							$e_address2 = $admin_user_row['address2'];
							$e_subscription = $admin_user_row['subscription'];
							$e_subscription_size = $admin_user_row['subscription_size'];
							$e_subscription_package = $admin_user_row['subscription_package'];
							$e_renewal_link_hash = md5(md5('renew_now').md5($admin_user_row['user_id']));
							
							$invoice = "
							<div id='invoice'>
								<table style='position: relative;left: 50% !important;width: 600px !important;margin-left: -300px !important;margin-top: 20px'>
									<tr>
										<td colspan='4' class='invoice_title' style='font-family: &quot;Gotham_Bold&quot;, Arial, Sans-Serif !important;font-size: 22px !important;margin-bottom: 20px;text-align: center;color: #2D2D2D !important;margin: 0 !important;background-color: #EBEBEB !important'>Payment Summary:</td>
									</tr>
									<tr>
										<th style='font-size: 14px !important;letter-spacing: -0.07em;font-family: &quot;Gotham_Book&quot;, Arial, Sans-Serif;line-height: 1.4em;margin-bottom: 20px;color: #777777;background-color: #F9F9F9;text-align: left'>item</th>
										<th style='font-size: 14px !important;letter-spacing: -0.07em;font-family: &quot;Gotham_Book&quot;, Arial, Sans-Serif;line-height: 1.4em;margin-bottom: 20px;color: #777777;background-color: #F9F9F9;text-align: left'>quantity</th>
										<th style='font-size: 14px !important;letter-spacing: -0.07em;font-family: &quot;Gotham_Book&quot;, Arial, Sans-Serif;line-height: 1.4em;margin-bottom: 20px;color: #777777;background-color: #F9F9F9;text-align: left'>unit price</th>
										<th class='right' style='text-align: right !important;font-size: 14px !important;letter-spacing: -0.07em;font-family: &quot;Gotham_Book&quot;, Arial, Sans-Serif;line-height: 1.4em;margin-bottom: 20px;color: #777777;background-color: #F9F9F9'>total</th>
									</tr>
									<tr>
										<td class='invoice' style='font-family: &quot;Gotham_Book&quot;, Arial, Sans-Serif;font-size: 13px !important;margin-bottom: 20px;text-align: left;color: #515151'>monthly subscription</td>
										<td class='invoice' style='font-family: &quot;Gotham_Book&quot;, Arial, Sans-Serif;font-size: 13px !important;margin-bottom: 20px;text-align: left;color: #515151'>4</td>
										<td class='invoice' style='font-family: &quot;Gotham_Book&quot;, Arial, Sans-Serif;font-size: 13px !important;margin-bottom: 20px;text-align: left;color: #515151'>9.97</td>
										<td class='right invoice' style='font-family: &quot;Gotham_Book&quot;, Arial, Sans-Serif;font-size: 13px !important;margin-bottom: 20px;text-align: right !important;color: #515151'>39.98</td>
									</tr>
									<tr>
										<td colspan='3' class='right invoice' style='font-family: &quot;Gotham_Book&quot;, Arial, Sans-Serif;font-size: 13px !important;margin-bottom: 20px;text-align: right !important;color: #515151'>Subtotal:</td>
										<td class='right invoice' style='font-family: &quot;Gotham_Book&quot;, Arial, Sans-Serif;font-size: 13px !important;margin-bottom: 20px;text-align: right !important;color: #515151'>$39.88</td>
									</tr>
									<tr>
										<td colspan='3' class='right invoice' style='font-family: &quot;Gotham_Book&quot;, Arial, Sans-Serif;font-size: 13px !important;margin-bottom: 20px;text-align: right !important;color: #515151'>Tax:</td>
										<td class='right invoice' style='font-family: &quot;Gotham_Book&quot;, Arial, Sans-Serif;font-size: 13px !important;margin-bottom: 20px;text-align: right !important;color: #515151'>$2.39</td>
									</tr>
									<tr>
										<td colspan='3' class='right' style='font-family: &quot;Gotham_Book&quot;, Arial, Sans-Serif;font-size: 15px;margin-bottom: 20px;text-align: right !important;color: #515151'>Total:</td>
										<td class='right' style='font-family: &quot;Gotham_Book&quot;, Arial, Sans-Serif;font-size: 15px;margin-bottom: 20px;text-align: right !important;color: #515151'>$42.27</td>
									</tr>
								</table>
							</div>
							";
							
							$e_renewal_link = $invoice."<center><a class='renew_button' href='https://www.froots.co/join.php?renew_now=$e_renewal_link_hash' style='display: block;height: 40px;width: 220px;background-color: #9AE070;border: 1px solid #FFF;text-align: center;padding-top: 16px;text-decoration: none;margin-top: 20px'>Click to Renew Subscription</a><p style='text-align: center;font-family: &quot;Gotham_Book&quot;, Arial, Sans-Serif;font-size: 15px;line-height: 1.4em;margin-bottom: 20px;color: #515151'>or</p><a href='https://www.froots.co/join.php'>Click for more options</a></center>";
							
							$survey_form = "
							<table>
								<form method='POST' action='https://www.froots.co'>
									<tr>
										<td><input type='checkbox' name='survey_answers[]' value='too_much_fruit' style='margin-right: 5px;'>There is too much fruit</td>
									</tr>
									<tr>
										<td><input type='checkbox' name='survey_answers[]' value='too_little_fruit' style='margin-right: 5px;'>There isn't enough fruit</td>
									</tr>
									<tr>
										<td><input type='checkbox' name='survey_answers[]' value='not_enough_variety' style='margin-right: 5px;'>There isn't enough variety</td>
									</tr>
									<tr>
										<td><input type='checkbox' name='survey_answers[]' value='did_not_need' style='margin-right: 5px;'>I don't need this service</td>
									</tr>
									<tr>
										<td><input type='checkbox' name='survey_answers[]' value='poor_quality_fruit' style='margin-right: 5px;'>I don't like the fruit</td>
									</tr>
									<tr>
										<td><input type='checkbox' name='survey_answers[]' value='costs_too_much' style='margin-right: 5px;'>It costs too much</td>
									</tr>
									<tr>
										<td><input type='checkbox' name='survey_answers[]' value='inconvenient_delivery' style='margin-right: 5px;'>Deliveries are inconvenient</td>
									</tr>
									<tr>
										<td>Other: <input type='text' name='other_answers' style='margin-left: 5px;'></td>
									</tr>
									<input type='hidden' name='survey_user_id' value='$e_user_id'>
									<input type='hidden' name='survey_first_name' value='$e_first_name'>
									<input type='hidden' name='survey_last_name' value='$e_last_name'>
									<input type='hidden' name='survey_subscription' value='$e_subscription'>
									<input type='submit' name='survey_submit' value='Submit'>
								</form>
							</table>
							";
							
							$e_survey_form = "Please select all that apply<br/>".$survey_form;
							
							$errors[] = $e_email;
							$body = "
	Hello $e_first_name,<br/><br/>

	$email_body <br/><br/>

	Stay fresh,<br/>
	The Froots & Co. Team
	";
							
							//inject user specific variables
							$body = str_replace("%first_name%", $e_first_name, $body);
							$body = str_replace("%last_name%", $e_last_name, $body);
							$body = str_replace("%email%", $e_email, $body);
							$body = str_replace("%phone_number%", $e_phone_number, $body);
							$body = str_replace("%address%", $e_address, $body);
							$body = str_replace("%address2%", $e_address2, $body);
							$body = str_replace("%subscription_size%", $e_subscription_size, $body);
							$body = str_replace("%subscription_package%", $e_subscription_package, $body);
							$body = str_replace("%renewal_link%", $e_renewal_link, $body);
							$body = str_replace("%survey_form%", $e_survey_form, $body);
							
							//function to send email
							$ses = new SimpleEmailService('', '');
							$ses->listVerifiedEmailAddresses();
							
							$m = new SimpleEmailServiceMessage();
							$m->addTo($e_email);
							$m->setFrom($from);
							$m->setSubject($email_subject);
							$m->setMessageFromString(null, $bodyhead.$body.$bodyfoot);

							$ses->sendEmail($m);
						}
					}
					
					echo "<div id='errors' style='margin: 20px 0;'>";
						echo "sent emails to: ";
						$i= 0;
						foreach($errors as $error) 
						{
						  echo $error." -- ";
						  if($i%4==0)
							echo "<br />";
						  $i++;
						}
					echo "</div>";
					
				}
				else 
				{
					echo "<div id='errors' style='margin: 20px 0;'>";
						echo "<ul>";
						foreach($errors as $error) 
						{
						  echo "<li>".$error."</li>";
						}
						echo "</ul>";
					echo "</div>";
				}
			}
			if(isset($_POST['update_fruit_actives']))
			{
				$display_admin_panel = 'display:block;';
				$display_toggle = 'display:none;';
				echo "<div id='errors' style='margin: 20px 0;'>";
				
				$fruit_type_actives = $_POST['fruit_type_actives'];
				
				if(count($fruit_type_actives)<1)
				{
					echo "you must select at least one fruit type";
				}
				else
				{
					$fruit_active_check_query = $db_con->prepare("UPDATE fruit_types SET fruit_type_active='no'");
					$fruit_active_check_query->execute();
					
					foreach($fruit_type_actives as $fruit_type_active)
					{
						$fruit_active_check_query = $db_con->prepare("UPDATE fruit_types SET fruit_type_active='yes' WHERE fruit_type_id=:fruit_type_id");
						$fruit_active_check_query->execute(array(':fruit_type_id'=>$fruit_type_active));
					}
					echo "successfully updated fruit profiles";
				}
				echo "</div>";
			}
			if(isset($_POST['new_fruit_type_submit']))
			{
				$display_admin_panel = 'display:block;';
				$display_toggle = 'display:none;';
				echo "<div id='errors' style='margin: 20px 0;'>";
				$new_fruit_type_profile_id = "";
				$new_fruit_type_name = "";
				$new_fruit_type_description = "";
				
				if(isset($_POST['new_fruit_type_profile_id']))
					$new_fruit_type_profile_id = $_POST['new_fruit_type_profile_id'];
				if(isset($_POST['new_fruit_type_name']))
					$new_fruit_type_name = $_POST['new_fruit_type_name'];
				if(isset($_POST['new_fruit_type_description']))
					$new_fruit_type_description = $_POST['new_fruit_type_description'];
				
				if($new_fruit_type_profile_id=="na" || $new_fruit_type_name=="" || $new_fruit_type_description=="" )
				{
					echo "please fill out all of the fields";
				}
				else
				{
					$add_fruit_type_query = $db_con->prepare("INSERT INTO fruit_types VALUES('', :new_fruit_type_profile_id, :new_fruit_type_name, '', :new_fruit_type_description, 'yes')");
					$add_fruit_type_query->bindParam(':new_fruit_type_profile_id', $new_fruit_type_profile_id);
					$add_fruit_type_query->bindParam(':new_fruit_type_name', $new_fruit_type_name);
					$add_fruit_type_query->bindParam(':new_fruit_type_description', $new_fruit_type_description);
					$add_fruit_type_query->execute();
					
					echo "successfully added fruit type";
				}
				echo "</div>";
			}
			?>
			<h5 class="center" style=" <?php echo $display_toggle; ?>cursor:pointer;" id="show_admin_button" onClick="showAdmin();">Show Admin Panel</h5>
			<h5 class="center" style="<?php echo $display_admin_panel; ?>; cursor:pointer;" id="hide_admin_button" onClick="hideAdmin();" >Hide Admin Panel</h5>
			<div id="admin_panel" style="<?php echo $display_admin_panel; ?>">
				<h5 style="margin-bottom: 5px;">Delivery Reminder:</h5>
				<form method="GET" action="#admin_panel">
					<input type='submit' value='Send Reminder' name='submit_reminder'>
				</form>
				<h5 style="margin-bottom: 5px;">Search Database:</h5>
				<?php
				echo $search_results_echo;
				?>
				<form method="GET" action="#admin_panel">
					<p style="display:inline;">WHERE:</p>
					<select style="display:inline;" name='column'>
						<option value="first_name">first name</option>
						<option value="last_name">last name</option>
						<option value="address">address</option>
						<option value="subscription">subscription</option>
					</select>
					<p style="display:inline;">=</p>
					<input type="text" name="column_value">
					<br />
					<p style="display:inline;">&& WHERE:</p>
					<select style="display:inline;" name='column2'>
						<option value="first_name">first name</option>
						<option selected value="last_name">last name</option>
						<option value="address">address</option>
						<option value="subscription">subscription</option>
					</select>
					<p style="display:inline;">=</p>
					<input type="text" name="column_value2">
					<p style="margin-bottom: 5px; font-weight:bold;">Options:</p>
					<table style="width:100%;">
						<tr>
							<td class="center"><p style="display:inline;">User ID</p><input type="checkbox" name="display_column[]" value="user_id"></td>
							<td class="center"><p style="display:inline;">First Name</p><input type="checkbox" name="display_column[]" value="first_name"></td>
							<td class="center"><p style="display:inline;">Last Name</p><input type="checkbox" name="display_column[]" value="last_name"></td>
						</tr>
						<tr>
							<td class="center"><p style="display:inline;">Email</p><input type="checkbox" name="display_column[]" value="email"></td>
							<td class="center"><p style="display:inline;">Subscription</p><input type="checkbox" name="display_column[]" value="subscription"></td>
							<td class="center"><p style="display:inline;">Phone Number</p><input type="checkbox" name="display_column[]" value="phone_number"></td>
						</tr>
						<tr>
							<td class="center"><p style="display:inline;">Address</p><input type="checkbox" name="display_column[]" value="address"></td>
							<td class="center"><p style="display:inline;">Address 2</p><input type="checkbox" name="display_column[]" value="address2"></p></td>
							<td class="center"><p style="display:inline;">Subscription Size</p><input type="checkbox" name="display_column[]" value="subscription_size"></td>
						</tr>
						<tr>
							<td class="center" ><p style="display:inline;">Subscription Packaging</p><input type="checkbox" name="display_column[]" value="subscription_package"></td>
							<td class="center"><p style="display:inline;">Late Crates</p><input type="checkbox" name="display_column[]" value="late_crates"></p></td>
							<td class="center"><p style="display:inline;">Fruit Preference</p><input type="checkbox" name="fruit_preference[]" value="fruit_preference"></td>
						</tr>
						<tr>
							<td class="center"><p style="display:inline;">Comments</p><input type="checkbox" name="comments[]" value="comments"></td>
						</tr>
					</table>
					<input type="submit" name="submit_search" value="search">
				</form>
				<h5 style="margin-top: 15px; margin-bottom: 5px;">Send HTML Email:</h5>
				<form method="GET" action="#nav_bar">
					<p style="display:inline;">WHERE:</p>
					<select style="display:inline;" name='column'>
						<option value="first_name">first name</option>
						<option value="last_name">last name</option>
						<option value="address">address</option>
						<option value="email">email</option>
						<option value="custom_email">CUSTOM email</option>
						<option value="subscription">subscription</option>
					</select>
					<p style="display:inline;">=</p>
					<input type="text" name="column_value" value="<?php echo $column_value; ?>">
					<br />
					<p style="display:inline;">&& WHERE:</p>
					<select style="display:inline;" name='column2'>
						<option value="first_name">first name</option>
						<option value="last_name">last name</option>
						<option value="address">address</option>
						<option value="email">email</option>
						<option value="subscription">subscription</option>
					</select>
					<p style="display:inline;">=</p>
					<input type="text" name="column_value2" value="<?php echo $column_value2; ?>">
					
					<p>VARIABLES: (note - CANNOT be used with CUSTOM email. Will only work with regular queries to database)</p>
					<table style="width:100%;">
						<tr>
							<td>%first_name%</td>
							<td>%last_name%</td>
							<td>%email%</td>
						</tr>
						<tr>
							<td>%phone_number%</td>
							<td>%address%</td>
							<td>%address2%</td>
						</tr>
						<tr>
							<td>%subscription_size%</td>
							<td>%subscription_package%</td>
							<td>%renewal_link%</td>
						</tr>
						<tr>
							<td>%survey_form%</td>
						</tr>
					</table>
					<p>Subject:</p>
					<input type="text" name="email_subject" value="<?php echo $email_subject; ?>">
					<p>Body:</p>
					<textarea rows="4" cols="50" name="email_body"><?php echo $email_body; ?></textarea>
					<br />
					<input type="submit" name="submit_email" value="send">
				</form>
				<h5 style="margin-top: 15px; margin-bottom: 5px;">Active Fruit Types:</h5>
				<form method="POST" action="#nav_bar">
					<?php
					$fruit_active_selection_query = $db_con->prepare("SELECT * FROM fruit_types ORDER BY fruit_type_name");
					$fruit_active_selection_query->execute();
					$row_num = 0;
					echo "<table><tr><td>";
					while($fruit_active_selection_row = $fruit_active_selection_query->fetch(PDO::FETCH_ASSOC))
					{
						$fruit_type_id = $fruit_active_selection_row['fruit_type_id'];
						$fruit_type_profile_id = $fruit_active_selection_row['fruit_type_profile_id'];
						$fruit_type_name = $fruit_active_selection_row['fruit_type_name'];
						$fruit_type_active = $fruit_active_selection_row['fruit_type_active'];
						if($fruit_type_active=='yes')
							$checked_fruit_type = 'checked';
						else
							$checked_fruit_type = '';
						?>
						<input type="checkbox" <?php echo $checked_fruit_type; ?> name="fruit_type_actives[]" value="<?php echo $fruit_type_id ; ?>"><p style="margin:0 0 0 15px; display:inline;"><?php echo $fruit_type_name; ?></p>
						<?php
						$row_num++;
						if($row_num%3 == 0)
							echo "</td></tr><tr><td>";
						else
							echo "</td><td>";
					}
					echo "</td></tr></table>";
					?>
					<input type="submit" name="update_fruit_actives" value="update">
				</form>
				<h5 style="margin-top: 15px; margin-bottom: 5px;">Add Fruit Type:</h5>
				<?php
				?>
				<form method="POST" action="#nav_bar">
					<table>
						<tr>
							<td><p>fruit profile:</p></td>
							<td>
								<select name="new_fruit_type_profile_id">
									<option value="na">select</option>
									<?php
									$fruit_type_profile_id_query = $db_con->prepare("SELECT * FROM fruit_profiles ORDER BY fruit_profile_name");
									$fruit_type_profile_id_query->execute();
									
									while($fruit_type_profile_id_row = $fruit_type_profile_id_query->fetch(PDO::FETCH_ASSOC))
									{
										$fruit_profile_id = $fruit_type_profile_id_row['fruit_profile_id'];
										$fruit_profile_name = $fruit_type_profile_id_row['fruit_profile_name'];
										echo "<option value='$fruit_profile_id'>$fruit_profile_name</option>";
									}
									?>
								</select>
							</td>
						</tr>
						<tr>
							<td><p>fruit type name:</p></td>
							<td><input type="text" name="new_fruit_type_name"></td>
						</tr>
						<tr>
							<td><p>fruit description:</p></td>
							<td><textarea name="new_fruit_type_description"></textarea></td>
						</tr>
					</table>
					<input type="submit" name="new_fruit_type_submit" value="add fruit type">
				</form>
			</div>
		<?php
		}
		?>
		
		<div id="member_options">
			<form method="POST" action="#nav_bar">
				<div id="member_likes">
					<center><h4 style="padding: 0px; margin-bottom: 25px;">The Fruits:</h4><br />
							<?php
							$fruit_active_query = $db_con->prepare("
								SELECT DISTINCT(p.fruit_profile_name)
								FROM fruit_profiles AS p
								INNER JOIN fruit_types AS t
								ON p.fruit_profile_id = t.fruit_type_profile_id
								WHERE t.fruit_type_active='yes'
								");
							$fruit_active_query->execute();
							
							$fruit_dislikes_query = $db_con->prepare("SELECT * FROM fruit_preference WHERE user_id=:user_id && preference='no'");
							$fruit_dislikes_query->bindParam(':user_id', $user_info['user_id']);
							$fruit_dislikes_query->execute();
							
							$fruit_dislikes[] = "";
							
							while($fruit_dislikes_row = $fruit_dislikes_query->fetch(PDO::FETCH_ASSOC))
							{
								$fruit_dislikes[] .= ucfirst($fruit_dislikes_row['fruit_name']);
							}
							
							$i = 1;
							while($fruit_option_row = $fruit_active_query->fetch(PDO::FETCH_ASSOC))
							{
								$attribute_name = 'this.attributes["name"].value';
								if(in_array($fruit_option_row['fruit_profile_name'], $fruit_dislikes))
								{
									$color = 'color: #A63C45';
									$input_value = 'no';
								}
								else
								{
									$color = 'color: #87BE77';
									$input_value = 'yes';
								}
								echo "<span style='$color; cursor:pointer;' id='word_".$fruit_option_row['fruit_profile_name']."' name='".$fruit_option_row['fruit_profile_name']."' onClick='updateInput($attribute_name)'>".$fruit_option_row['fruit_profile_name']."</span>";

								echo"<input type='hidden' value='".$input_value."' name='fruit_options[".$fruit_option_row['fruit_profile_name']."]' id='".$fruit_option_row['fruit_profile_name']."'><br />";
								$i++;
							}
							?>
							<center><p style="display: inline;"><span class="green_block"></span>do like <span class="blue_block"></span>don't like</p></center>
					</center>
				</div>
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
				<h4>Phone Number:</h4> <input type="text" id="phone_input" onkeypress="return isNumberKey(event, this.value);" placeholder="###-###-####" name="phone_number" value="<?php echo $user_info['phone_number'];?>" ><br />
				<h4>Phone Carrier:</h4> 
					<select name="phone_carrier">
						<option value="">Select Carrier</option>
						<option value="verizon" <?php if($user_info['phone_carrier']=='verizon'){echo "selected";} ?>>Verizon Wireless</option>
						<option value="att" <?php if($user_info['phone_carrier']=='att'){echo "selected";} ?>>AT&amp;T </option>
						<option value="sprint" <?php if($user_info['phone_carrier']=='sprint'){echo "selected";} ?>>Sprint Nextel</option>
						<option value="tmobile" <?php if($user_info['phone_carrier']=='tmobile'){echo "selected";} ?>>T-Mobile USA</option>
						<option value="other" <?php if($user_info['phone_carrier']=='other'){echo "selected";} ?>>Other</option>
					</select>
					<br />
				<h4 style="margin-bottom:0;">Address:</h4> <input type="text" name="address" value="<?php echo $user_info['address'];?>" ><br />
				<h4 style="margin-top:0;">Address 2:</h4> <input type="text" style="margin-top:0;" name="address2" value="<?php echo $user_info['address2'];?>" ><br />
				
				<h4 style="vertical-align:top;">Comments:</h4> <textarea name="comments" placeholder="What's on your mind?"></textarea><br />
				
				<h4 style="vertical-align:top;">Delivery Status:</h4>
					<p>Active</p><input type="radio" name="subscription" value="active" <?php if($user_info['subscription']!="paused"){echo "checked";}?>>
					<p>Paused</p><input type="radio" name="subscription" value="paused" <?php if($user_info['subscription']=="paused"){echo "checked";}?>><br />
				
				<h4 style="vertical-align:top; margin-bottom: 0;">Billing Status:</h4>
					<p>Active</p><input type="radio" name="selected_plan" value="register_plan<?php echo $user_info['subscription_size']; ?>" <?php if($user_info['selected_plan']!="cancel"){echo "checked";}?>>
					<p>Cancelled</p><input type="radio" name="selected_plan" value="cancel" <?php if($user_info['selected_plan']=="cancel"){echo "checked";}?>><br />
				
				<h4 style="vertical-align:top; margin-top:0;">Current Balance:</h4><p><?php echo $user_info['credits'];?> weeks</p> <br /><br />
				
				<center><input type="submit" name="submit_updates" value="update settings"></center>
				
				<!--REFERRAL LINK! -->
				<center>
					<h4>Want free weeks? Share this with your friends:</h4><br />
					<p id="pre">http://www.froots.co/join.php?refer=<?php echo md5(md5("refer").md5($user_id));?></p><br />
					<p><a href="http://www.facebook.com/sharer.php?u=http://www.froots.co/join.php?refer=<?php echo md5(md5("refer").md5($user_id));?>">Share to Facebook!</a></p>
				</center>
			</form>
		</div>
		<?php include 'include/footer.php'; ?>
	</div>
	
	<?php
	if($user_info['subscription']=='admin')
	{
		//*********************************
		//ADMIN SETTING PROCESSING AND POPUP
		//*********************************
		?>
		<div id="admin_settings_container" <?php echo $admin_settings_display; ?>>
			<p style="position:absolute; top:1px; right:6px; cursor:pointer; margin: 0; font-weight: bold;" onClick="hide('#admin_settings_container', '#admin_background')">X</p>
			<h5>Admin Settings Update</h5>
			<?php echo "<p>".$admin_settings_message."</p>"; ?>
			<div id="admin_settings_form">
				<?php
				if(isset($_POST['admin_settings_submit']))
				{
					echo "<div id='errors' style='width:92.5%; right:13px;'>";
					$edit_user_id = $_POST['edit_user_id'];
					
					if(isset($_POST['first_name']))
						$new_admin_first_name = mysql_real_escape_string($_POST['first_name']);
					if(isset($_POST['last_name']))
						$new_admin_last_name = mysql_real_escape_string($_POST['last_name']);
					if(isset($_POST['email']))
						$new_admin_email = mysql_real_escape_string($_POST['email']);
					if(isset($_POST['phone_number']))
						$new_admin_phone_number = mysql_real_escape_string($_POST['phone_number']);
					if(isset($_POST['address']))
						$new_admin_address = mysql_real_escape_string($_POST['address']);
					if(isset($_POST['address2']))
						$new_admin_address2 = mysql_real_escape_string($_POST['address2']);
					if(isset($_POST['comment']))
						$new_admin_comment = mysql_real_escape_string($_POST['comment']);
					if(isset($_POST['receiving_email']))
						$new_receiving_email = mysql_real_escape_string($_POST['receiving_email']);
						
					if(isset($_POST['selected_plan']))
						$new_selected_plan = mysql_real_escape_string($_POST['selected_plan']);
					if(isset($_POST['subscription']))
						$new_subscription = mysql_real_escape_string($_POST['subscription']);
						
					if(isset($_POST['admin_subscription_renew']))
						$admin_subscription_renew = mysql_real_escape_string($_POST['admin_subscription_renew']);
					else
						$admin_subscription_renew = "";
					
					$admin_update_query = $db_con->prepare("
					UPDATE users
					SET first_name = :first_name, last_name = :last_name, email = :email, phone_number = :phone_number , address = :address, address2 = :address2, unsubscribe = :receiving_email, selected_plan = :selected_plan, subscription =:subscription  WHERE user_id=".$edit_user_id
					);
					
					$admin_update_query->bindParam(':first_name', $new_admin_first_name);
					$admin_update_query->bindParam(':last_name', $new_admin_last_name);
					$admin_update_query->bindParam(':email', $new_admin_email);
					$admin_update_query->bindParam(':phone_number', $new_admin_phone_number);
					$admin_update_query->bindParam(':address', $new_admin_address);
					$admin_update_query->bindParam(':address2', $new_admin_address2);
					$admin_update_query->bindParam(':receiving_email', $new_receiving_email);
					$admin_update_query->bindParam(':selected_plan', $new_selected_plan);
					$admin_update_query->bindParam(':subscription', $new_subscription);
					
					$admin_update_query->execute();
					
					if(isset($_POST['comment']) && $_POST['comment']!="")
					{
						$comments_query = $db_con->prepare("INSERT INTO comments VALUES ('', '".$edit_user_id."', :comments)");
						$comments_query->execute(array(':comments'=>$new_admin_comment));
					}
					$admin_info_query = $db_con->prepare("SELECT * FROM users WHERE user_id = :user_id ");
					$admin_info_query->execute(array(":user_id"=>$edit_user_id));
					
					if(isset($_POST['audit_confirm']) && $_POST['audit_confirm']!="na")
					{
						$audit_confirm = mysql_real_escape_string($_POST['audit_confirm']);
						if(isset($_POST['audit_quantity']))
							$audit_quantity = mysql_real_escape_string($_POST['audit_quantity']);
						if(isset($_POST['audit_type']))
							$audit_type = mysql_real_escape_string($_POST['audit_type']);
						if(isset($_POST['audit_description']))
							$audit_description = mysql_real_escape_string($_POST['audit_description']);
						else
							$audit_description = "";
						
						if($audit_quantity==0 || $audit_type=='na' || $audit_description=="")
						{
							echo "please fill out the entire audit form";
						}
						else
						{
							if($audit_type=='referrals')
							{
								echo "Referral audits aren't live yet";
							}
							elseif($audit_type=='credits' || $audit_type=='late_crates')
							{
								$audit_name = $audit_confirm."_".$audit_type;
								if($audit_confirm=='add')
									$audit_sign = '+';
								elseif($audit_confirm=='subtract')
									$audit_sign = '-';

								//perform the action
								$credit_audit_query = $db_con->prepare("UPDATE users SET ".$audit_type." = ".$audit_type." ".$audit_sign." :audit_quantity WHERE user_id = :edit_user_id");
								$credit_audit_query->bindParam(':audit_quantity', $audit_quantity);
								$credit_audit_query->bindParam(':edit_user_id', $edit_user_id);
								$credit_audit_query->execute();
								
								//record the activity
								$date = date('Y-m-d G:i:s');
								$audit_activity_query = $db_con->prepare("INSERT INTO activity VALUES ('', :date, :edit_user_id, :audit_name, :audit_description, :audit_quantity)");
								$audit_activity_query->bindParam(':date', $date);
								$audit_activity_query->bindParam(':edit_user_id', $edit_user_id);
								$audit_activity_query->bindParam(':audit_name', $audit_name);
								$audit_activity_query->bindParam(':audit_description', $audit_description);
								$audit_activity_query->bindParam(':audit_quantity', $audit_quantity);
								$audit_activity_query->execute();
								
								echo "successfuly audited: $audit_type = $audit_type $audit_sign $audit_quantity WHERE $edit_user_id";
							}
						}
					}
					if($admin_subscription_renew != "" || $admin_subscription_renew != "na")
					{
						require 'include/stripe-php/lib/Stripe.php'; 	
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
						
						//PAYMENT DETAILS
						$selected_plan = $admin_subscription_renew;
						$renew_now_package = 'oak';
						$renew_now_duration = 4;
						$renew_now_price = $pricing_array[$selected_plan];
						
						$renew_now_size = substr($selected_plan, -2);
							
						$renew_now_subtotal = $renew_now_price*$renew_now_duration;
						$renew_now_price_display = "$".$renew_now_subtotal/100;
						
						$renew_now_tax = round($renew_now_subtotal*0.06);
						$renew_now_tax_display = "$".$renew_now_tax/100;
						
						$renew_now_total = $renew_now_subtotal+$renew_now_tax;
						$renew_now_total_display = "$".$renew_now_total/100;
						
						Stripe::setApiKey("");
						
						$stripe_id_edit_query = $db_con->prepare("SELECT stripe_id, email, first_name, last_name FROM users WHERE user_id=:user_id");
						$stripe_id_edit_query->bindParam(':user_id',$edit_user_id);
						$stripe_id_edit_query->execute();
						
						
						while($stripe_id_edit_row = $stripe_id_edit_query->fetch(PDO::FETCH_ASSOC))
						{
							$renew_now_stripe_id = $stripe_id_edit_row['stripe_id'];
							$renew_now_email = $stripe_id_edit_row['email'];
							$renew_now_first_name = $stripe_id_edit_row['first_name'];
							$renew_now_last_name = $stripe_id_edit_row['last_name'];
						}
						
						$customer = Stripe_Customer::retrieve("$renew_now_stripe_id");
						
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
								SET subscription = 'active', credits = :weeks_purchased, subscription_size = :subscription_size, subscription_package = :subscription_package, selected_plan = :selected_plan WHERE user_id=:user_id"
								);
							$renew_now_query->bindParam(':weeks_purchased', $renew_now_duration);
							$renew_now_query->bindParam(':subscription_size', $renew_now_size);
							$renew_now_query->bindParam(':subscription_package', $renew_now_package);
							$renew_now_query->bindParam(':selected_plan', $selected_plan);
							$renew_now_query->bindParam(':user_id', $edit_user_id);
							$renew_now_query->execute();
							
							//record payment activity
							$date = date('Y-m-d G:i:s');
							$purchase_activity_query = $db_con->prepare("INSERT INTO activity VALUES ('', :date, :user_id, 'add_credits', 'admin panel renew', :credits)");
							$purchase_activity_query->bindParam(':date', $date);
							$purchase_activity_query->bindParam(':user_id', $edit_user_id);
							$purchase_activity_query->bindParam(':credits', $renew_now_duration);
							$purchase_activity_query->execute();
							
							//email payment confirmation
							$to = $renew_now_email;
							$subject = "FROOTS & Co. Renewal: $renew_now_first_name $renew_now_last_name";
							$from = "The FROOTS & Co. Team <admin@froots.co>";

include 'include/email_top.php';
include 'include/email_bottom.php';
		
							$body = "
Hello $renew_now_first_name,<br/><br/>

This is a simple confirmation that you've renewed your account with FROOTS & Co.<br/><br/>

Total Weeks: $renew_now_duration <br />
Order Size: $renew_now_size <br />
Subtotal: $renew_now_price_display <br />
Tax: $renew_now_tax_display <br />
Total: $renew_now_total_display <br /><br />

As usual, feel free to update your delivery information and leave us comments via your profile.<br /><br />

Stay fresh,<br/>
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
							echo "account renewed";
						}	
					}
					
					while($admin_info_row = $admin_info_query->fetch(PDO::FETCH_ASSOC))
					{
						$admin_first_name = $admin_info_row['first_name'];
						$admin_last_name = $admin_info_row['last_name'];
						$admin_email = $admin_info_row['email'];
						$admin_subscription = $admin_info_row['subscription'];
						$admin_current_address = $admin_info_row['address'];
						$admin_current_address2 = $admin_info_row['address2'];
						$admin_current_phone_number = $admin_info_row['phone_number'];
						$admin_current_subscribe = $admin_info_row['unsubscribe'];
					}
					echo "</div>";
					?>
					<form method="POST">
						<input type="hidden" name="edit_user_id" value="<?php echo $edit_user_id; ?>">
						<table>
							<tr>
								<td><p>First Name:</p></td>
								<td><input type="text" name="first_name" value="<?php echo $admin_first_name; ?>"></td>
								<td><p>Last Name:</p></td>
								<td><input type="text" name="last_name" value="<?php echo $admin_last_name; ?>"></td>
							</tr>
							<tr>
								<td><p>Email:</p></td>
								<td colspan="3"><input size="40" type="text" name="email" value="<?php echo $admin_email; ?>"></td>
							</tr>
							<tr>
								<td>subscribe<input <?php if($admin_current_subscribe!='unsubscribe'){echo "checked";} ?> type='radio' name='receiving_email' value=''></td>
								<td>unsubscribe<input <?php if($admin_current_subscribe=='unsubscribe'){echo "checked";} ?> type='radio' name='receiving_email' value='unsubscribe'></td>
							</tr>
							<tr>
								<td><p>Phone Number:</p></td>
								<td><input type="text" name="phone_number" value="<?php echo $admin_current_phone_number ; ?>"></td>
							</tr>
							<?php
							if($admin_subscription == 'delinquent')
							{
							?>
								<tr>
									<td><p>Renew Subscription:</p></td>
									<td>
										<select name="admin_subscription_renew">
											<option value="na">Select Plan</option>
											<option value="register_plan01">1</option>
											<option value="register_plan02">2</option>
											<option value="register_plan04">4</option>
											<option value="register_plan08">8</option>
											<option value="register_plan12">12</option>
										</select>
									</td>
								</tr>
							<?php
							}
							?>
							<tr>
								<td><p>Address 1:</p></td>
								<td colspan="3"><input type="text" size="40" name="address" value="<?php echo $admin_current_address ; ?>"></td>
							</tr>
							<tr>
								<td><p>Address 2:</p></td>
								<td colspan="3"><input type="text" size="40" name="address2" value="<?php echo $admin_current_address2 ; ?>"></td>
							</tr>
							<tr>
								<td><p>Comments:</p></td>
								<td colspan="3"><textarea name="comment" ></textarea></td>
							</tr>
							<tr>
								<td>
									<select name='audit_confirm'>
										<option value='na'>Null</option>
										<option value='add'>Add</option>
										<option value='subtract'>Subtract</option>
									</select>
								</td>
								<td>
									<select name='audit_quantity'>
										<?php
											$i = 0;
											while($i <=10)
											{
												echo "<option value='$i'>$i</option>";
												$i++;
											}
										?>
									</select>
									<select name='audit_type'>
										<option value='na'>Type?</option>
										<option value='credits'>Credits</option>
										<option value='referrals'>Referrals</option>
									</select>
								</td>
								<td colspan="2">
									<input type="text" name="audit_description" value="">
								</td>
							</tr>
						</table>
						<input type="submit" name="admin_settings_submit" value="update">
					</form>
				<?php
				}
				?>
			</div>
		</div>
	<?php
	}
	?>
	
	</body>

</html>