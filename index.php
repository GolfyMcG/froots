	<?php include 'include/head.php'; 
	
	if(isset($_POST['survey_submit']))
	{
		$other_answers = "";
		$survey_answers = "";
		
		if(!empty($_POST['survey_answers']))
			$survey_answers = $_POST['survey_answers'];
		if(isset($_POST['other_answers']))
			$other_answers = mysql_real_escape_string($_POST['other_answers']);
		
		$survey_user_id = mysql_real_escape_string($_POST['survey_user_id']);
		$survey_first_name = mysql_real_escape_string($_POST['survey_first_name']);
		$survey_last_name = mysql_real_escape_string($_POST['survey_last_name']);
		$survey_subscription = mysql_real_escape_string($_POST['survey_subscription']);
		$date = date('Y-m-d G:i:s');
		
		foreach($survey_answers AS $survey_answer)
		{
			$survey_answers_checked[] = mysql_real_escape_string($survey_answer);
		}
		
		$survey_answers_checked[] = $other_answers;
		$survey_results = implode(",", $survey_answers_checked);
		
		$survey_query = $db_con->prepare("INSERT INTO survey_results VALUES (:survey_user_id, :survey_first_name, :survey_last_name, :survey_subscription, :date, :comments)");
		$survey_query->bindParam(':survey_user_id', $survey_user_id);
		$survey_query->bindParam(':survey_first_name', $survey_first_name);
		$survey_query->bindParam(':survey_last_name', $survey_last_name);
		$survey_query->bindParam(':survey_subscription', $survey_subscription);
		$survey_query->bindParam(':date', $date);
		$survey_query->bindParam(':comments', $survey_results);
		$survey_query->execute();
		
		$thank_you = "Thank you for submitting your feedback.";
	}
	?>
	<body class="wrap">
	<div id="side_shadow">
		<div id="cover_photo">
			<p><a href="join.php">Sign Up</a> or <a href="login.php">Sign In</a></p>
			<div id="delivery_counter">
				<?php 
					// Create fruit servings counter:
					$delivery_counter_query = $db_con->prepare("SELECT SUM(activity_value) As deliverySum FROM activity WHERE activity_description='routine delivery'");
					$delivery_counter_query->execute();
					
					while($delivery_counter_row = $delivery_counter_query->fetch(PDO::FETCH_ASSOC))
					{
						$delivery_count = $delivery_counter_row['deliverySum'];
					}
					
					$original_boxes = 80;
					$total_boxes = $delivery_count + $original_boxes;
					
					$promotional_fruit = 840;
					$total_servings = number_format($total_boxes*8 + $promotional_fruit, 0, 0, ',');
					
					$servings_array = str_split($total_servings);

					/*echo "<table><tr>";
						foreach($servings_array AS $servings_number)
						{
							if($servings_number !=',')
								echo "<td class='counter_number'><h3 class='counter_number'>".$servings_number."</h3></td>";
							else
								echo "<td class='counter_number' style='height: 50%;'><h3 class='counter_number'>".$servings_number."</h3></td>";
						}
						echo "<td class='counter_end'><h3 class='counter_end'> fruits delivered</h3></td>";
					echo "</tr></table>";*/
				?>
			</div>
		</div>
		<div id="nav_placeholder" class="fixed"></div>
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
			$icon_href = "href='#cover_photo' class='scroll'";
		}
		?>
		<ul id="nav_bar">
			<li class="blank">&nbsp;</li>
			<a <?php echo $icon_href; ?> ><li <?php echo $profile_class; ?>>&nbsp;</li></a>
			<a href="#values" class="scroll" ><li <?php echo $logout_class; ?>>Our Company</li></a>
			<a href="#fruits" class="scroll" ><li <?php echo $logout_class; ?>>Our Fruits</li></a>
			<a href="#faq" class="scroll" ><li <?php echo $logout_class; ?>>FAQ's</li></a>
			<a href="#corporate" class="scroll" ><li <?php echo $logout_class; ?>>Our Partnerships</li></a>
			<?php 
			if(isset($_SESSION['user_id']))
			{
				echo "<a href='logout.php'><li class='logout'>Logout</li></a>";
			}
			?>
		</ul>
		
		<div id="passion">
			<div id="passion_text">
				<h3>Our passion</h3>
				<p>Froots and Company is a student run venture devoted to making healthy snacks convenient and affordable.</p>
				<p>Fruit does not stay fresh forever and not everyone has the luxury to go grocery shopping every single week. 
				We are passionate about health and we believe in convenience; so now we deliver market fresh fruit straight to your door.</p>
			</div>
			<img src="images/passion.png">
		</div>
		
		<div id="deserves">
			<div id="deserves_content">
				<div id="deserves_title">
					<h3>Give your body what it deserves</h3>
				</div>
				<div id="deserves_text">
					<p>A good diet shouldn't be hard to maintain and we find convenience to be a major factor.  Join our weekly fruit subscription program to instantly balance your diet.</p>
					<p>The <b>Baltimore Farmer's Market</b> has been growing year after year driving our Local & Green food movement. It's time to skip out on excesive sodium and saturated fats and time to feel better.</p>
				</div>
			</div>
		</div>
		
		<div id="values">
			<h3 class="center">We value your values</h3>
			
			<?php
			$titles = array(
			"Feel better and stay happy",
			"Your time is valuable",
			"Get market fresh fruit",
			"Fruit is nature's gift",
			"No hidden fees or secret costs",
			"Great service is everything",
			"Community giveback",
			"The right priorities"
			);
			
			$messages = array(
			"Packed with natural sugars and vitamins, fruits naturally increase satisfaction and productivity.",
			"We'll deliver the fruit directly to your door or mailbox - saving you time and effort.",
			"Your fruit is hand-selected each week at the peak of their season.",
			"Get the vitamins and minerals you need to live long and strong.",
			"You can pause or cancel your delivery at any time!",
			"If you're not happy with the quality of an item in your box, contact us, and we'll make it right. You are are our priority.",
			"Through a collaboration with Balitmore's Soup Kitchen: <b>Our Daily Bread</b>, we help to provide fruit for everyone in our community.",
			"Remember, we don't choose fruits for looks, we buy for flavor. You may see blemishes or growth marks, but hey, it's inside that matters.",
			);
			
			$colors = array(
			"#ffca4b",
			"#5a6a8e",
			"#9ae070",
			"#dc5151",
			"#dc5151",
			"#9ae070",
			"#5a6a8e",
			"#ffca4b"
			);
			
			$images = array(
			"smiley.png",
			"parachute.png",
			"mini_tree.png",
			"heart.png",
			"",
			"",
			"",
			"",
			);
			
			foreach($titles AS $key => $title)
			{
				$color = $colors[$key];
				$image = $images[$key];
				if($image == "")
				{
					$image_display = "<h2>".($key+1)."</h2>";
				}
				else
					$image_display = "<img src='images/$image' />";
					
				if(($key+2)%2==0)
					$float= "float: left;";
				else
					$float= "float: right;";
				
				if($key>3)
					$extra = "class='extra_value'";
				else
					$extra = "";
				if($key==4)
					echo "<p class='center extra_value_selector'>(more reasons to be healthy with us)</p>";
				
				echo "<div id='value_item' style='$float' $extra>";
				
					echo "<div id='value_item_image'>";
						echo $image_display;
					echo "</div>";
					
					echo "<div id='value_item_text'>";
						echo "<h5 style='color:$color;'>".$title."</h5>";
						echo "<p>".$messages[$key]."</p>";
					echo "</div>";
					
				echo "</div>";
			}
			?>
		</div>
		<div id="testimonials">
			<div id="testimonial_content">
				<div id="testimonial_title">
					<h3>Testimonials</h3>
				</div>
				<div id="testimonial_text">
					<div class="left_testimonial">
						<p>"Froots & Co. made it easier for me... I don't have an excuse anymore... it naturally replaces any junk food that I may snack on!"</p>
						<p class="testimonial_name">-Alex H.</p>
					</div>
					<div class="right_testimonial">
						<p>"I received [my first box] yesterday, and almost all the fruit is gone already!"</p>
						<p class="testimonial_name">-Anna B.</p>
					</div>
				</div>
			</div>
		</div>
		<div id="notification">
			<div id="notification_content">
				<form method="GET" action="join.php">
					<p>Full Name:</p>
					<input type="text" name="name" value="">
					<p>Email:</p>
					<input type="text" name="email" value="">
					<input type="submit" value="Get Started!" name="submit_notification">
				</form>
			</div>
		</div>
		<div id="healthy">
			<h3 class="center" >Get Healthy, Stay Fresh.</h3>
			<a href="join.php"><img src="images/package_options2.png" /></a>
		</div>
		<h2 style="margin: 10px 0 -10px 0;" class="center">***</h2>
		<div id="fruits">
			<h3 class="center" >The Fruits</h3>
			<p class="center" >Why yes! We deliver more than the most common fruits. We have all sorts of berries, kiwis, and even pomegranates. And don't worry, you can opt out of those you don't want. <br />
			(Though we recommend that you give them all a shot)</p>
			<?php
			$fruits = array(
			"#e25d42" => "Strawberries",
			"#ed1c24" => "Red Seedless Grapes",
			"#9e0039" => "Raspberries",
			"#f09635" => "Navel Oranges",
			"#c69c6d" => "Danjou Pears",
			"#fff200" => "Bananas",
			"#9ae070" => "Granny Smith Apples",
			"#f69679" => "Honeycrisp Apples",
			"#f9a847" => "Sunburst Tangerines",
			"#0054a6" => "Blueberries",
			"#ed145b" => "Pomegranate",
			"#a2e27c" => "Asian Pear",
			"#662d91" => "Blackberries",
			"#f7941d" => "Clementines",
			"#ffdf61" => "Golden Delicious Apples",
			"#39b54a" => "Avocado",
			"#aad6a4" => "Fuji Apples",
			"#7cc576" => "Kiwifruit",
			"#f89c39" => "Florida Oranges",
			"#a67c52" => "Bosc Pears",
			"#ce5d0f" => "Gala Apples",
			"#ffca4b" => "Mangos"
			);
			echo "<ul>";
				foreach($fruits AS $color => $fruit)
				{
					echo "<li style='color:$color;' >".$fruit."</li>";
				}
			echo "</ul>";
			?>
		</div>
		<div style="margin-top: 50px;" id="notification">
			<div id="notification_content">
				<form method="GET" action="join.php">
					<p>Full Name:</p>
					<input type="text" name="name" value="">
					<p>Email:</p>
					<input type="text" name="email" value="">
					<input type="submit" value="Get Started!" name="submit_notification">
				</form>
			</div>
		</div>
		<div id="faq">
			<h3 class="center" >Frequently Asked Questions</h3>
			
			<div id="faq_item">
				<div id="faq_number">
					<h2>1</h2>
				</div>
				<div id="faq_content">
					<h4>How do I get my fruit?</h4>
					<p>Every Monday morning you will wake up to a new box placed right at your door. For the first week it will come in an oak box which you can hold onto.
					After the first week, we will bring cardboard boxes which you can fold back the flaps and simply insert into the wooden box. That's it!</p>
				</div>
			</div>
			
			<div id="faq_item">
				<div id="faq_number">
					<h2>2</h2>
				</div>
				<div id="faq_content">
					<h4>Do I have to sign up for a long term contract?</h4>
					<p>No one likes being locked into something they don't want.
					You can pause or cancel at any time, no questions asked.</p>
				</div>
			</div>
			
			<div id="faq_item">
				<div id="faq_number">
					<h2>3</h2>
				</div>
				<div id="faq_content">
					<h4>Where can I get my Froots delivered?</h4>
					<p>Froots & Co. now delivers to everyone within 2 miles of the JHMI campus, Homewood Campus, and Loyola. Even offices and dorms!</p>
				</div>
			</div>
			
			<div id="faq_item">
				<div id="faq_number">
					<h2>4</h2>
				</div>
				<div id="faq_content">
					<h4>How much will my subscription cost?</h4>
					<p>The cost of your subscription begins at $9.97/wk for delivery (delivery cost included). If you order more than one box on a single account, the cost goes down automatically.
					If you are doing a group purchase, as more of your coworkers or friends sign up, the price per box will go down and your rebate will be processed within a week of ordering. See our <a href="pricing.php">pricing page</a> for more information.</p>
				</div>
			</div>
			
			<div id="faq_item">
				<div id="faq_number">
					<h2>5</h2>
				</div>
				<div id="faq_content">
					<h4>Will you pause my deliveries for school holidays?</h4>
					<p>Yes! We monitor your school's schedule and whether it's Spring Break or Christmas Break, we will automatically pause your subscription for you.</p>
				</div>
			</div>
			
			<p class='center extra_faq_selector'>(more frequently asked questions)</p>
			
			<div id="faq_item" class="extra_faq">
				<div id="faq_number">
					<h2>6</h2>
				</div>
				<div id="faq_content">
					<h4>Is the fruit organic?</h4>
					<p>The cost difference for organically grown fruit is such that your membership will double in price! 
					We strive to work with small, family owned farms whenever possible. 
					We want to support growing in a sustainable, environmentally friendly way. Want organic fruit? <a href="suggestions.php">Let us know!</a></p>
				</div>
			</div>
			
			<div id="faq_item" class="extra_faq">
				<div id="faq_number">
					<h2>7</h2>
				</div>
				<div id="faq_content">
					<h4>I'm having a problem placing my order.</h4>
					<p>Any issues with check-out or order fulfillment can be resolved by <a href="mailto:alex@froots.co">alex@froots.co</a>. He'll respond within 24 hours, but honestly, it's more like 12 minutes.</p>
				</div>
			</div>
			
			<div id="faq_item" class="extra_faq">
				<div id="faq_number">
					<h2>8</h2>
				</div>
				<div id="faq_content">
					<h4>Is the fruit pre-washed?</h4>
					<p>Everything we deliver is "au naturel" so we suggest a quick rinse before you enjoy.</p>
				</div>
			</div>
			
			<div id="faq_item" class="extra_faq">
				<div id="faq_number">
					<h2>9</h2>
				</div>
				<div id="faq_content">
					<h4>What if I get a piece of fruit that's gone bad?</h4>
					<p>We promise 100% satisfaction. If you're not happy with the quality of anything, contact us and <b>we'll make it right.</b></p>
				</div>
			</div>
			
			<div id="faq_item" class="extra_faq">
				<div id="faq_number">
					<h2>10</h2>
				</div>
				<div id="faq_content">
					<h4>How will I know when it's been delivered?</h4>
					<p>As soon as your box is delivered, we will send an email or text message telling you that their fruit is ready to be eaten!</p>
				</div>
			</div>
		</div>
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
		<h2 style="margin: 10px 0 -10px 0;" class="center">***</h2>
		<div id="price_comparison" class='center'>
			<h3>Share with Everyone</h3>
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
					echo "<tr style='cursor:pointer;' class='nutrional_benefits_row_selector'><td colspan='6' style='border: 0;'><h4>Nutritional Benefits</h4></td></tr>";
						
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
					
					//blank row
					$counter = 0;
					
					echo "<tr>";
						echo "<td class='title_column'></td>";
						while($counter<5)
						{
							echo "<td></td>";
							$counter++;
						}
					echo "</tr>";
				?>
			</table>
		</div>
		
		<span id="corporate_return"></span>
		<div id="corporate">
			<h3 class="center" >Corporate Partnerships</h3>
			<p class="center" >So you've got an office space or student group, and now you want us to freshen it up? Awesome! To get your group involved, fill out our <span class='corporate_form_selector'>corporate partnership form</span> and we'll get things rolling.</p>
		</div>
		<?php
		$c_full_name = "";
		$c_company_name = "";
		$c_office_name = "";
		$c_email = "";
		$c_phone_number = "";
		
		if(isset($_POST['submit_partnerships']))
		{
			echo "<div id='errors'>";
			
			$c_full_name = mysql_real_escape_string($_POST['c_full_name']);
			$c_company_name = mysql_real_escape_string($_POST['c_company_name']);
			$c_office_name = mysql_real_escape_string($_POST['c_office_name']);
			$c_email = mysql_real_escape_string($_POST['c_email']);
			$c_phone_number = mysql_real_escape_string($_POST['c_phone_number']);
			$c_phone_number = preg_replace("/[^0-9]/","",$c_phone_number );
			
			if(!$c_full_name||!$c_company_name||!$c_office_name||!$c_email||!$c_phone_number)
				$errors[] = "Please fill in <b>all</b> fields.";
			if(!preg_match("/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/i", $c_email))
				$errors[] = "You must enter a valid email address.";
			if(strlen($c_phone_number)<10)
				$errors[] = "Phone number must have at least 10 digits.";
				
			if(empty($errors))
			{
				$query = $db_con->prepare("INSERT INTO partnerships VALUES (:id, :date, :full_name, :company_name, :office_name, :email, :phone_number)");
				
				$query = $db_con->prepare("
				INSERT INTO partnerships (id, date, full_name, company_name, office_name, email, phone_number) VALUES (:id, :date, :full_name, :company_name, :office_name, :email, :phone_number)
				");
				
				$id = '';
				$date = date('Y-m-d G:i:s');
				$query->bindParam(':id', $id);
				$query->bindParam(':date', $date);
				$query->bindParam(':full_name', $c_full_name);
				$query->bindParam(':company_name', $c_company_name);
				$query->bindParam(':office_name', $c_office_name);
				$query->bindParam(':email', $c_email);
				$query->bindParam(':phone_number', $c_phone_number);
				
				$query->execute();
				
				$to = $c_email;
				$subject = "FROOTS & Co. Contact: $c_company_name/$c_office_name";
				$from = "The FROOTS & Co. Team <admin@froots.co>";

include 'include/email_top.php';
include 'include/email_bottom.php';
					
					$body = "
Hello $c_full_name,<br/><br/>

We're excited that you want to work on a corporate partnership with us. Below is a summary of your information.<br/><br/>

Full Name: $c_full_name<br/>
Company Name: $c_company_name<br/>
Office/Department Name: $c_office_name<br/>
Email: $c_email<br/>
Phone Number: $c_phone_number<br/><br/>

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
				
				echo "<ul><li>We'll be back to you soon, but for now, we've sent you our confirmation of your contact.</li></ul>";
				
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
			?>
			<script type="text/javascript">
				$(document).ready(function(){
					//corporate form pop-down
					$(".corporate_form").toggle("fast");
					});
			</script>
			<?php
		}
		?>
		<div class="corporate_form" .>
			<form class="elevated" action="#corporate_return" method="POST">
				<table id="contact" style='margin: 0 auto 50px auto'>
					<tr>
						<td><p>Full Name:</p></td>
						<td><input type="text" name="c_full_name" size="61" value="<?php echo $c_full_name ; ?>"></td>
					</tr>
					<tr>
						<td><p>Company Name:</p></td>
						<td><input type="text" name="c_company_name" size="61" value="<?php echo $c_company_name ; ?>"></td>
					</tr>
					<tr>
						<td><p>Office/Department:</p></td>
						<td><input type="text" name="c_office_name" size="61" value="<?php echo $c_office_name ; ?>"></td>
					</tr>
					<tr>
						<td><p>Email:</p></td>
						<td><input type="text" name="c_email" size="61" value="<?php echo $c_email ; ?>"></td>
					</tr>
					<tr>
						<td><p>Phone Number:</p></td>
						<td><input type="text" name="c_phone_number" size="61" value="<?php echo $c_phone_number ; ?>"></td>
					</tr>
				</table>
				<div class="center"><input type="submit" value="Submit" name="submit_partnerships"></div>
			</form>
		</div>
		<div id="footer">
			<table style="display: inline;">
				<tr>
					<td>
						<ul>
							<li><a href="index.php" class='team'>Home</a></li>
							<li><a href="pricing.php" class='pricing'>Pricing</a></li>
							<li><a href="#faq" class='faq scroll'>FAQs</a></li>
						</ul>
					</td>
					<td>
						<ul>
							<li><a href="story.php" class='story'>Our Team</a></li>
							<li><a href="suppliers.php" class='suppliers'>Supplier Origins</a></li>
							<li><a href="http://www.facebook.com/froots.co" target="_blank">Facebook</a></li>
						</ul>
					</td>
					<td>
						<ul>
							<li><a href="suggestions.php" class='suggestions'>Suggestions</a></li>
							<li><a href="problem.php" class='problem'>Report Problem</a></li>
							<li><a href="privacy.php" class='privacy'>Privacy Policy</a></li>
						</ul>
					</td>
				</tr>
			</table>
			<iframe src="//www.facebook.com/plugins/like.php?href=http%3A%2F%2Fwww.facebook.com%2Ffroots.co&amp;send=false&amp;layout=box_count&amp;width=100&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font&amp;height=90&amp;appId=251373481641636" style="overflow:hidden; border:none; overflow:hidden; width:70px; height:65px; position: relative; top:30px; left: 290px;"></iframe>
		</div>
		
	</div>
	</body>

</html>