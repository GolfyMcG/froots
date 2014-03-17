		<?php
		$logout_class = "";
		if(isset($_SESSION['user_id']))
		{
			$logout_class = "class='logout'";
			$profile_class = "class='profile_pic'";
			$icon_href = "href='member.php'";
		}
		else
		{
			$profile_class = "class='logo'";
			$icon_href = "href='index.php#cover_photo'";
		}
		?>
		<ul id="nav_bar">
			<li class="blank">&nbsp;</li>
			<a <?php echo $icon_href; ?> ><li <?php echo $profile_class; ?>>&nbsp;</li></a>
			<a href="index.php#values" ><li <?php echo $logout_class; ?>>Our Company</li></a>
			<a href="index.php#fruits" ><li <?php echo $logout_class; ?>>Our Fruits</li></a>
			<a href="index.php#faq" ><li <?php echo $logout_class; ?>>FAQ's</li></a>
			<a href="index.php#corporate" ><li <?php echo $logout_class; ?>>Our Partnerships</li></a>
			<?php 
			if(isset($_SESSION['user_id']))
			{
				echo "<a href='logout.php'><li class='logout'>Logout</li></a>";
			}
			?>
		</ul>