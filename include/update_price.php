<?php
require 'stripe-php/lib/Stripe.php';
include("connect.php");
Stripe::setApiKey("");

$referral_coupon_status = "";

if(isset($_GET['coupon']) && strlen(trim($_GET['coupon'])) > 0)
{
	$referral_coupon_query = $db_con->prepare("SELECT user_id, first_name FROM users WHERE subscription='active' OR subscription='paused' ORDER BY user_id");
	$referral_coupon_query->execute();
	
	while($referral_coupon_row = $referral_coupon_query->fetch(PDO::FETCH_ASSOC))
	{
		$referral_coupon_row_user_id = $referral_coupon_row['user_id'];
		$referral_coupon_row_first_name = strtoupper($referral_coupon_row['first_name']);
		
		$test_referral_coupon = $referral_coupon_row_first_name.$referral_coupon_row_user_id;
		if($test_referral_coupon==strtoupper(trim($_GET['coupon'])))
		{
			$referral_coupon_status = "referral_coupon";
			break;
		}
	}
	
	if($referral_coupon_status != "referral_coupon")
	{
		try
		{
			$coupon = Stripe_Coupon::retrieve( trim( $_GET['coupon'] ) );
			// if we got here, the coupon is valid
		}
		catch (Exception $e) 
		{
			$coupon_exception = "yes";
		}
	}
}

$selected_plan = mysql_real_escape_string($_GET['selected_plan']);
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
				
$referral_accurate = "";
$referral_discount = "";
$referral_savings = "";

$price = $pricing_array[$selected_plan];
$base_price = round($price,2)/100;
$package = 'oak';

$total_weeks = 4;
if($referral_coupon_status == "referral_coupon")
	$bonus_weeks = 1;
else
	$bonus_weeks = 0;
$total_servings = 8*$selected_size;
	
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
			$bonus_weeks = 1;
			//$referral_discount = -1*$price/100*0.1;
			//$referral_savings = round($referral_discount*$total_weeks,2);
			//$price = $price*0.9;
			break;
		}
	}
}
$yearly_price = $price*52;
$price = $price*$total_weeks;
$discount = "";

if(isset($_GET['coupon']) && strlen(trim($_GET['coupon'])) > 0 && $referral_coupon_status != "referral_coupon")
{
	if(!isset($coupon_exception)&& isset($_GET['coupon']))
	{
		$discount = -1*round(($coupon->percent_off)*$price/100)/100;
		$price = round((1-($coupon->percent_off/100))*$price);
	}
}
else
	$price = round($price);

$subtotal = $total_weeks*$base_price + $referral_savings + $discount;
	
$tax = round($price*0.06);
$total = $subtotal+$tax/100;
	
?>
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
		<tr>
			<td class="invoice">Number of weeks </td>
			<td class="invoice"><?php echo $total_weeks+$bonus_weeks; ?></td>
			<td class="invoice"><?php echo number_format($base_price,2); ?></td>
			<td class="right invoice"><?php echo number_format($total_weeks*$base_price,2); ?></td>
		</tr>
		<tr>
			<td class="invoice">Number of servings </td>
			<td class="invoice"><?php echo $total_servings; ?></td>
			<td class="invoice"></td>
			<td class="right invoice"></td>
		</tr>
		<?php if($referral_accurate=='yes') { ?>
		<tr>
			<td class="invoice" colspan="3"><b>You and <?php echo ucfirst(strtolower($refer_code_row_first_name)); ?> earned a free week!</b></td>
			<td class="right invoice"></td>
		</tr>
		<?php } ?>
		<?php if(!isset($coupon_exception)&& isset($_GET['coupon']) && strlen(trim($_GET['coupon'])) > 0 && $referral_coupon_status != "referral_coupon") { ?>
		<tr>
			<td class="invoice">coupon discount</td>
			<td class="invoice" >1</td>
			<td class="invoice"><?php echo $discount; ?></td>
			<td class="right invoice"><?php echo $discount; ?></td>
		</tr>
		<?php } ?>
		<?php if($referral_coupon_status == "referral_coupon" && $referral_accurate != 'yes') { ?>
		<tr>
			<td class="invoice" colspan="3"><b>You and <?php echo ucfirst(strtolower($referral_coupon_row_first_name)); ?> earned a free week!</b></td>
			<td class="right invoice"></td>
		</tr>
		<?php } ?>
		<tr>
			<td colspan="3" class="right invoice">Subtotal:</td>
			<td class="right invoice"><?php echo number_format($subtotal,2); ?></td>
		</tr>
		<tr>
			<td colspan="3 invoice" class="right invoice">Tax:</td>
			<td class="right invoice"><?php echo $tax/100; ?></td>
		</tr>
		<tr>
			<td colspan="3" class="right">Total:</td>
			<td class="right"><?php echo "$".number_format($total,2); ?></td>
		</tr>
	</table>
</div>