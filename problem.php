	<?php include 'include/head.php'; ?>
	
	<body class="wrap">
	<div id="side_shadow">
		<?php include 'include/nav_bar.php'; ?>
		
		<h3 class='center'>Is there a problem?</h3>
		<p style='padding: 0 60px;'>Despite our best efforts, sometimes things go wrong. We're here to make things right. If you've experienced a problem with your purchase, get our attention here. We'll get back to you ASAP.</p>
		
		<?php
		//Form processing for problem
		if(isset($_POST['submit_problem']))
		{
			echo "<div id='errors'>";
			include("include/connect.php");
			
			$first_name = mysql_real_escape_string($_POST['first_name']);
			$last_name = mysql_real_escape_string($_POST['last_name']);
			$email = mysql_real_escape_string($_POST['email']);
			$confirm_email = mysql_real_escape_string($_POST['confirm_email']);
			$problem = mysql_real_escape_string($_POST['problem']);
			
			if(!$first_name||!$last_name||!$email||!$confirm_email||!$problem)
				$errors[] = "Please fill in <b>all</b> fields.";
			if($email!=$confirm_email)
				$errors[] = "Your emails don't match.";
			if(!preg_match("/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/i", $email))
				$errors[] = "You must enter a valid email address.";
			if(empty($errors))
			{
				$query = $db_con->prepare("INSERT INTO suggestions VALUES (:id, :date, :first_name, :last_name, :email, :problem)");
				$id = '';
				$date = date('Y-m-d G:i:s');
				$query->bindParam(':id', $id);
				$query->bindParam(':date', $date);
				$query->bindParam(':first_name', $first_name);
				$query->bindParam(':last_name', $last_name);
				$query->bindParam(':email', $email);
				$query->bindParam(':problem', $problem);
				
				$query->execute();
				
				$to = $email;
				$subject = "FROOTS & Co. Complaint Form: $first_name $last_name";
				$from = "The FROOTS & Co. Team <admin@froots.co>";

include 'include/email_top.php';
include 'include/email_bottom.php';
				
				$body = "
Hello $first_name,<br/><br/>

You had the following problem: <br/>
<hr />
$problem <br/><br/>
<hr />
This is just a confirmation that we recieved your message and want you to know we're working on it. If you have any further concerns or want to follow up with us, please contact us at admin@froots.co. <br/><br/>

Stay fresh,<br/>
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
				
				echo "<p>We'll be back to you soon, but for now, we've sent you our confirmation of your submission.</p>";
			}
			else
			{
				echo "<ul>";
				foreach($errors as $error) 
				{
				  echo "<li>".$error."</li>";
				}
				echo "</ul>";
			}
			echo "</div>";
		}
		else
		{
			$first_name = "";
			$last_name = "";
			$email = "";
			$confirm_email = "";
			$problem = "";
		}
		?>
		
		<form class="elevated" action="problem.php" method="POST">
			<table id="contact" style='margin: 0 auto 50px auto'>
				<tr>
					<td><p>First Name:</p></td>
					<td><input type="text" name="first_name" size="61" value="<?php echo $first_name; ?>"></td>
				</tr>
				<tr>
					<td><p>Last Name:</p></td>
					<td><input type="text" name="last_name" size="61" value="<?php echo $last_name; ?>"></td>
				</tr>
				<tr>
					<td><p>Email Address:</p></td>
					<td><input type="text" name="email" size="61" value="<?php echo $email; ?>"></td>
				</tr>
				<tr>
					<td><p>Confirm Email:</p></td>
					<td><input type="text" name="confirm_email" size="61" value="<?php echo $confirm_email; ?>"></td>
				</tr>
				<tr>
					<td><p>Problem:</p></td>
					<td><textarea name="problem" rows="3" cols="61" placeholder="What's on your mind?"><?php echo $problem; ?></textarea></td>
				</tr>
			</table>
			<div class="center"><input type="submit" value="Submit" name="submit_problem"></div>
		</form>
		
		<?php include 'include/footer.php'; ?>
	</div>
	</body>

</html>