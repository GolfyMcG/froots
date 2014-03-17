<!DOCTYPE html>

<!-- =================================== -->
<!-- ========== www.froots.co ========== -->
<!-- ======== Author:Alex Villa ======== -->
<!-- =================================== -->

<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="icon" href="images/favicon.png" type="image/png" />
		<link rel="stylesheet" href="froots.css">
		<link rel="shortcut icon" href="favicon.ico" />
		<title>Fresh Fruit, Delivered | Froots & Co.</title>
		
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js" type="text/javascript"></script>
		<!--[if lt IE 9]>
			<script src="http://ie7-js.googlecode.com/svn/version/2.1(beta4)/IE9.js"></script>
		<![endif]-->
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
		<script type="text/javascript" src="include/js/jquery.scrollTo-1.4.3.1-min.js"></script>
		
		<!-- GOOGLE ANALYTICS -->
		<script type="text/javascript">

		  var _gaq = _gaq || [];
		  _gaq.push(['_setAccount', '']);
		  _gaq.push(['_trackPageview']);

		  (function() {
			var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'stats.g.doubleclick.net/dc.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		  })();

		</script>
		
		<script type="text/javascript">
			$(function() {
				
				var fixadent = $("#nav_bar"), pos = fixadent.offset();
				var placeholder = $("#nav_placeholder");
			   
				$(window).scroll(function()
				{
					if($(this).scrollTop() > pos.top && fixadent.css('position') == 'relative') 
					{
						placeholder.removeClass('fixed');
						fixadent.addClass('fixed');
					} 
					else if($(this).scrollTop() <= pos.top && fixadent.hasClass('fixed'))
					{
						placeholder.addClass('fixed');
						fixadent.removeClass('fixed');
					}
				}
				)
				});
		</script>
		<script type="text/javascript">
			function show(div, bg_div){
				$(div).fadeIn('fast');
				$(bg_div).fadeIn('fast');
				
				var scrollPosition = [
					self.pageXOffset || document.documentElement.scrollLeft || document.body.scrollLeft,
					self.pageYOffset || document.documentElement.scrollTop  || document.body.scrollTop
				];
				
				var html = jQuery('html'); // it would make more sense to apply this to body, but IE7 won't have that
				html.data('scroll-position', scrollPosition);
				html.data('previous-overflow', html.css('overflow'));
				html.css('overflow', 'hidden');
				return;
			}
			
			function hide(div, bg_div){
				$(div).fadeOut('fast');
				$(bg_div).fadeOut('fast');
				
				var html = jQuery('html');
				var scrollPosition = html.data('scroll-position');
				html.css('overflow', html.data('previous-overflow'));
				return;
			}
			function forgotSwitch()
			{
				//if the forgot password is showing, do the first one
				if(document.getElementById("log_in_row").style.display == "none")
				{
					$(forgot_password_row).fadeOut('fast', function() {
						$(log_in_row).fadeIn('fast');
					});
					$(forgot_log_text).text('Forgot password?');
				}
				else
				{
					$(log_in_row).fadeOut('fast', function() {
						$(forgot_password_row).fadeIn('fast');
					});
					$(forgot_log_text).text('Log in');
				}
			}
		</script>
		<script type="text/javascript">
			 $(document).ready(function(){
				//values pop-down
				$(".extra_value").toggle("fast");
				
				$(".extra_value_selector").click(function(event){
					$(".extra_value").toggle("fast");
				});
				
				//faq pop-down
				$(".extra_faq").toggle("fast");
				
				$(".extra_faq_selector").click(function(event){
					$(".extra_faq").toggle("fast");
				});
				
				//corporate form pop-down
				$(".corporate_form_selector").click(function(event){
					$(".corporate_form").toggle("fast");
				});
				
				$('a[href*=#]:not([href=#])').click(function() {
					if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') 
						|| location.hostname == this.hostname) {

						var target = $(this.hash);
						target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
						   if (target.length) {
							 $('html,body').animate({
								 scrollTop: target.offset().top
							}, 1000);
							return false;
						}
					}
				});
				
			 });
			 
			 jQuery(document).ready(function($) {
 
				$(".scroll").click(function(event){		
					event.preventDefault();
					$('html,body').mouseover.animate({scrollTop:$(this.hash).offset().top}, 500);
				});
			});
		</script>
		
		<?php 
		include("include/connect.php"); 
		date_default_timezone_set('America/New_York');
		
		
		if(isset($_COOKIE['user_id']))
		{
			$cookie_id = mysql_real_escape_string($_COOKIE['user_id']);
			$cookie_email = mysql_real_escape_string($_COOKIE['email']);
			
			$cookie_query = $db_con->prepare("SELECT user_id, email FROM users WHERE user_id=:user_id && email=:email");
			$cookie_query->bindParam('user_id', $cookie_id);
			$cookie_query->bindParam('email', $cookie_email);
			$cookie_query->execute();
			
			$cookie_numrows=$cookie_query->rowCount();
			if($cookie_numrows!=0)
			{
				$_SESSION['user_id']==$cookie_id;
			}
			
		}
		
		//open the admin_settings on submission
		if(isset($_POST['admin_settings_submit']))
		{
			$admin_settings_message = "";
			$admin_settings_display = "style='display: block;'";
			$popup_background = "style='display: block;'";
		}
		else
		{
			$admin_settings_message = "";
			$admin_settings_display = "style='display: none;'";
		}
		?>
		
	</head>