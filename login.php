	<?php session_start();
	include 'include/head.php'; ?>
	
	<body class="wrap">
	<div id="side_shadow">
		<?php include 'include/nav_bar.php'; ?>
		
		<h3 class="center">Log In</h3>
		<?php
		$login_email = "";
		$login_password = "";
		
		$error_section = "";
		
		if(isset($_POST['submit_login']))
		{
			$login_email = mysql_real_escape_string($_POST['login_email']);
			$login_password = mysql_real_escape_string($_POST['login_password']);
			
			if(isset($_POST['remember_me']))
				$remember_me = mysql_real_escape_string($_POST['remember_me']);
			else
				$remember_me = "";

			if(!$login_email||!$login_password)
			{
				if(!$login_email)
					$errors[] = "Please enter an email.";
				if(!$login_password)
					$errors[] = "Please enter a password.";
			}
			else
			{
				$query = $db_con->prepare("SELECT user_id, email, password FROM users WHERE email=:email");
				$query->execute(array(':email'=>$login_email));
				
				$numrows=$query->rowCount();
				
				if($numrows==0)
					$errors[] = "Invalid login information.";
				else
				{
					while ($row = $query->fetch(PDO::FETCH_ASSOC))
					{
						$dbpassword = $row['password'];
						$dbuser_id = $row['user_id'];
						
						if(md5($login_password)!=$dbpassword)
							$errors[] = "Invalid login information.";
					}
				}
			}
			if(empty($errors))
			{
				$_SESSION['user_id']=$dbuser_id;
				
				if($remember_me=='yes')
				{
					setcookie("user_id", md5($_SESSION['user_id']), time()+7200);
					setcookie("email", md5($login_email), time()+7200);
				}
				
				$active_check_query = $db_con->prepare("SELECT subscription FROM users WHERE user_id=:user_id");
				$active_check_query->bindParam('user_id', $dbuser_id);
				$active_check_query->execute();
				
				while($active_check_row = $active_check_query->fetch(PDO::FETCH_ASSOC))
				{
					$subscription_check = $active_check_row['subscription'];
				}
				if($subscription_check=='active' || $subscription_check=='admin' || $subscription_check=='paused')
				{
					$URL="member.php";
				}
				elseif($subscription_check=='inactive' || $subscription_check=='delinquent')
				{
					$URL="join.php";
				}
				
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
		<div id="login">
			<div id="login_content">
				<form method="POST" action="">
					<p>Email:</p>
					<input type="text" name="login_email" value="<?php  ?>">
					<p>Password:</p>
					<input type="password" name="login_password" value="<?php ?>">
					<input type="submit" value="Log In" name="submit_login"><br />
					<div id="remember_me">
						<input type="checkbox" name="remember_me"><p>Rememer Me</p>
					</div>
				</form>
			</div>
		</div>
		
		<?php include 'include/footer.php'; ?>
	</div>
	</body>

</html>