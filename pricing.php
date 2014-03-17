	<?php include 'include/head.php'; ?>
	
	<body class="wrap">
	<div id="side_shadow">
		<?php include 'include/nav_bar.php'; ?>
		
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
		
		<?php include 'include/footer.php'; ?>
	</div>
	</body>

</html>