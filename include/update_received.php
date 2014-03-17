<?php
include("connect.php");
require 'stripe-php/lib/Stripe.php'; 	

$received_id = $_GET['received_id'];
$date = date('Y-m-d G:i:s');

$deduction_user_query = $db_con->prepare("UPDATE users SET credits = credits - 1 WHERE user_id = :received_id");
$deduction_user_query->execute(array(':received_id'=>$received_id));

$deduction_activity_query = $db_con->prepare("INSERT INTO activity VALUES ('', :date, :received_id, 'subtract_credits', 'routine delivery', '1')");
$deduction_activity_query->bindParam(':date', $date);
$deduction_activity_query->bindParam(':received_id', $received_id);
$deduction_activity_query->execute();

$received_user_query = $db_con->prepare("SELECT * FROM users WHERE user_id = :received_id");
$received_user_query->execute(array(':received_id'=>$received_id));

//Pull user's information
while ($received_user_row = $received_user_query->fetch(PDO::FETCH_ASSOC))
{
	$current_credits = $received_user_row['credits'];
	$selected_plan = $received_user_row['selected_plan'];
	$received_first_name = $received_user_row['first_name'];
	$received_last_name = $received_user_row['last_name'];
	$received_email = $received_user_row['email'];
	$received_carrier = $received_user_row['phone_carrier'];
	$received_phone = preg_replace("/[^0-9]/","", $received_user_row['phone_number']);
	$received_stripe_id = $received_user_row['stripe_id'];
}

$renew_now_size = substr($selected_plan, -2);

//Send text message confirmation
$carrier_array = array(
						'verizon' => 'vtext.com',
						'att' => 'txt.att.net',
						'sprint' => 'messaging.sprintpcs.com',
						'tmobile' => 'tmomail.net');

$carrier_address = $carrier_array[$received_carrier];

if($carrier_address!="")
{
		//text delivery confirmation
		$to = $received_phone."@".$carrier_address;
		$from = "The FROOTS & Co. Team <admin@froots.co>";

		$body = "
Your FROOTS delivery has been dropped off! We hope you enjoy it! ~Stay fresh.
";

		//function to send email
		
		include_once('ses.php');
		$ses = new SimpleEmailService('', '');
		$ses->listVerifiedEmailAddresses();
		
		$m = new SimpleEmailServiceMessage();
		$m->addTo($to);
		$m->setFrom($from);
		$m->setMessageFromString(null, $body);

		$ses->sendEmail($m);
}
else
{
	
}

//Renew account if at 0 credits
if($current_credits==0)
{
	$renew_now_duration = 4;
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
	
	if($selected_plan=='cancel')
	{
		$set_delinquent_query = $db_con->prepare("UPDATE users SET subscription='delinquent' WHERE user_id=:received_id");
		$set_delinquent_query->bindParam(':received_id', $received_id);
		$set_delinquent_query->execute();
		
		//record payment activity
		$date = date('Y-m-d G:i:s');
		$purchase_activity_query = $db_con->prepare("INSERT INTO activity VALUES ('', :date, :user_id, 'cancellation', :description, :credits)");
		$purchase_activity_query->bindParam(':date', $date);
		$purchase_activity_query->bindParam(':user_id', $received_id);
		$purchase_activity_query->bindParam(':credits', $renew_now_duration);
		$purchase_activity_query->bindParam(':description', $selected_plan);
		$purchase_activity_query->execute();
	}
	elseif($pricing_array[$selected_plan]!="")
	{
		//PAYMENT DETAILS
		$renew_now_package = 'oak';
		$selected_size = substr($selected_plan, -2);
		
		$renew_now_price = $pricing_array[$selected_plan]*$renew_now_duration;
		$renew_now_price_display = "$".$renew_now_price/100;
		
		$renew_now_tax = round($renew_now_price*0.06);
		$renew_now_tax_display = "$".$renew_now_tax/100;
		
		$renew_now_total = $renew_now_price+$renew_now_tax;
		$renew_now_total_display = "$".$renew_now_total/100;
		
		Stripe::setApiKey("");
		
		$customer = Stripe_Customer::retrieve("$received_stripe_id");
		
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
				SET subscription = 'active', credits = :weeks_purchased, subscription_size = :subscription_size, subscription_package = :subscription_package, selected_plan=:selected_plan WHERE user_id=:user_id"
				);
			$renew_now_query->bindParam(':weeks_purchased', $renew_now_duration);
			$renew_now_query->bindParam(':subscription_size', $renew_now_size);
			$renew_now_query->bindParam(':subscription_package', $renew_now_package);
			$renew_now_query->bindParam(':user_id', $received_id);
			$renew_now_query->bindParam(':selected_plan', $selected_plan);
			$renew_now_query->execute();
			
			//record payment activity
			$date = date('Y-m-d G:i:s');
			$purchase_activity_query = $db_con->prepare("INSERT INTO activity VALUES ('', :date, :user_id, 'add_credits', :description, :credits)");
			$purchase_activity_query->bindParam(':date', $date);
			$purchase_activity_query->bindParam(':user_id', $received_id);
			$purchase_activity_query->bindParam(':credits', $renew_now_duration);
			$purchase_activity_query->bindParam(':description', $selected_plan);
			$purchase_activity_query->execute();
			
			//email payment confirmation
			$to = $received_email;
			$subject = "FROOTS & Co. Renewal: $received_first_name $received_last_name";
			$from = "The FROOTS & Co. Team <admin@froots.co>";

include 'email_top.php';
include 'email_bottom.php';

			$body = "
Hello $received_first_name,<br/><br/>

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
			/*
			include_once('ses.php');
			$ses = new SimpleEmailService('', '');
			$ses->listVerifiedEmailAddresses();
			
			$m = new SimpleEmailServiceMessage();
			$m->addTo($to);
			$m->setFrom($from);
			$m->setSubject($subject);
			$m->setMessageFromString(null, $bodyhead.$body.$bodyfoot);

			$ses->sendEmail($m);*/
			
			$current_credits = $renew_now_duration;
		}
	}
}

echo "<button style='background-color:#A93C45 !important;' class='received_button'>".$current_credits."</button>";

?>