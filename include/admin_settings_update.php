<?php
include("connect.php");

$edit_user_id = $_GET['edit_user_id'];

$admin_info_query = $db_con->prepare("SELECT * FROM users WHERE user_id = :user_id ");
$admin_info_query->execute(array(":user_id"=>$edit_user_id));

while($admin_info_row = $admin_info_query->fetch(PDO::FETCH_ASSOC))
{
	$admin_first_name = $admin_info_row['first_name'];
	$admin_last_name = $admin_info_row['last_name'];
	$admin_email = $admin_info_row['email'];
	$admin_subscription = $admin_info_row['subscription'];
	$admin_current_selected_plan = $admin_info_row['selected_plan'];
	$admin_current_subscription_size = $admin_info_row['subscription_size'];
	$admin_current_address = $admin_info_row['address'];
	$admin_current_address2 = $admin_info_row['address2'];
	$admin_current_phone_number = $admin_info_row['phone_number'];
	$admin_current_phone_carrier = $admin_info_row['phone_carrier'];
	$admin_current_subscribe = $admin_info_row['unsubscribe'];
}
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
			<td><p>Carrier:</p></td>
			<td>
				<select name="phone_carrier">
					<option value="">Select Carrier</option>
					<option value="verizon" <?php if($admin_current_phone_carrier=='verizon'){echo "selected";} ?>>Verizon Wireless</option>
					<option value="att" <?php if($admin_current_phone_carrier=='att'){echo "selected";} ?>>AT&amp;T </option>
					<option value="sprint" <?php if($admin_current_phone_carrier=='sprint'){echo "selected";} ?>>Sprint Nextel</option>
					<option value="tmobile" <?php if($admin_current_phone_carrier=='tmobile'){echo "selected";} ?>>T-Mobile USA</option>
					<option value="other" <?php if($admin_current_phone_carrier=='other'){echo "selected";} ?>>Other</option>
				</select>
			</td>
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
			<td>Billing:</td>
			<td>
				Active<input type="radio" name="selected_plan" value="register_plan<?php echo $admin_current_subscription_size; ?>" <?php if($admin_current_selected_plan!="cancel"){echo "checked";}?>>
				Cancelled<input type="radio" name="selected_plan" value="cancel" <?php if($admin_current_selected_plan=="cancel"){echo "checked";}?>>
			</td>
		</tr>
		<tr>
			<td>Delivery:</td>
			<td>
				Active<input type="radio" name="subscription" value="active" <?php if($admin_subscription!="paused"){echo "checked";}?>>
				Paused<input type="radio" name="subscription" value="paused" <?php if($admin_subscription=="paused"){echo "checked";}?>>
			</td>
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
					<option value='late_crates'>Late Crates</option>
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