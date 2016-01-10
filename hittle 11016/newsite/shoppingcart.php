<?php
//shopping cart

//this file will do the following
 //check to see whether it is being reloaded via ajax (remove, change, and quick add functions)
 //or coming from catalog or confirmation page

  //get user and order id

 //query the database and display order contents along with remove and change buttons
 
 //display the quick add feature

 //check to see is this user has entered shippinh address info, if so display, if not, prompt for entry

//connect to database
 include "connection.php";
	
			if ($_GET['reload'] == 1) {
			//getvariables ready	
					$order_id = $_GET['Order_ID'];
					$user_id = $_GET['CustID'];
			} else {
			//getvariables ready	
					$order_id = $_SESSION['Order_ID'];
					$user_id = $_SESSION['CustID'];
			}
			//make sure user is logged in
			if ($user_id == null) {
				echo "You are not logged in! Follow this link to open an account: <a href='landing.php'>New Account</a>";
			

		//if they are logged in, display the cart
			} else {
// echo quick add feature

		echo "<div id='quick_add'>";

		echo"<h1>Quick Add</h1>";
		echo"<p>Know exactly what you need? Type the name below</p>";

			echo "<form class='atc_form' autocomplete='off' id='qa'>";

			echo "<table class='responsive_table' id='qa_table'><thead>";
			echo "<tr><th class='desc_col'>Item Description</th><th>Qty</th><th></th></tr></thead>";
			echo "<tbody><tr>";				
			echo "<td><input type='text' name='desc' id='quick_add_desc'><div id='quickAddAClist'></div></td>";	
			echo "<td>QTY:<input type='text' name='quantity' class='quantity' id='qa_qty'>";				
			//invisible fields
			echo "<input type='hidden' name='id' id='qa_item_id'>"; //value filled in when user clicks autocomp link
			echo "<input type='hidden' name='user_id' id='qa_user_id' value='".$user_id."'>";	
			echo "<input type='hidden' name='quick' value=1>";
			echo "<input type='hidden' name='order_id' id='qa_order_id' value='".$order_id."'></td>";			
			echo "<td><input type='submit' class='add_to_cart cart_button' value='Update Cart'></td>";
			echo "</tr></tbody>";		
			echo "</table>";

			echo "</form>";


		echo "</div>";

//echo the cart
		echo "<div id='cart'>";
				echo "<h1>Your Order: Order#".$order_id."</h1>";

				//hide these variables in invisible inputs
				echo "<input type='hidden' id='user_id' value='".$user_id."'>";
				echo "<input type='hidden' id='order_id' value='".$order_id."'>";
			
				//display items in cart
				//select this cart from the cart order table based on order id
				$SQL = "SELECT Cart_Order_Entry_ID, Cart_Order_Item_ID, Cart_Order_Qty, Cart_Order_List_Price FROM ht_cart_order"; 
				$SQL .= " WHERE Cart_Order_ID = '".$order_id."'";
				$result = $database->query($SQL);
				$cart_array = $result->fetchAll();

				//loop over cart array and display each item

				//create table title row and set order total to zero
				echo "<table class='responsive_table' id='cart_table'>";
				echo "<thead><tr><th id='cart_desc_col'>Description</th><th class='qty_col'>Qty</th><th>Price</th><th>Subtotal</th><th></th><th></th></tr></thead><tbody>";
				$orderprice = 0;

				foreach ($cart_array as $cart_item) {
				//set item price and standardize to 2 decimal places				
				$price = number_format($cart_item['Cart_Order_List_Price'], 2);

				//total price per item				
				$totalprice = $cart_item['Cart_Order_Qty'] * $price;
				$totalprice = number_format($totalprice, 2); //set to 2 decimal places
	
				//total price for whole order
				$orderprice = $orderprice + $totalprice;

				//get each item description
				//check to see if it is a regular catalog item or special item
				$first = substr($cart_item['Cart_Order_Item_ID'], 0, 1);
				echo $first;

				//if it's a special
				if (($first == 's') OR ($first == 'f')) {
					$SQL = "SELECT Specials_Item_Description, Specials_Title FROM ht_specials WHERE Specials_Item_ID = '".$cart_item['Cart_Order_Item_ID']."'";
					$result = $database->query($SQL);
					$descriptions = $result->fetch();
	
					//if no item desc, check for group
					if ($descriptions['Specials_Item_Description'] == null) {
						$item_desc = "Special: ".$descriptions['Specials_Title'];
					} else {
						$item_desc = "Special: ".$descriptions['Specials_Item_Description'];
					}

				} else { //regular catalog items
	
				$SQL = "SELECT Cart_Description FROM ht_cart_ac WHERE Cart_Item_Number =".$cart_item['Cart_Order_Item_ID'];
				$result = $database->query($SQL);
				$item_desc = $result->fetchColumn();
				}

				//if no description
				if ($item_desc == null) {
					$item_desc = "No description available";
				}

				//use this variable to give all cart order entries unique ids
				$entry = $cart_item['Cart_Order_Entry_ID'];


				echo "<tr><td>".$item_desc."</td>";
				echo "<td id='qty".$entry."'>".$cart_item['Cart_Order_Qty']."</td>";
				echo "<td id='price".$entry."'>".$price."</td><td id='total".$entry."'>".$totalprice."</td>";													
				echo "<td><button class='delete_item cart_button' value='".$entry."'>Remove</button></td>";
				echo "<td><button class='change_qty cart_button' value='".$entry."'>Change Qty</button></td></tr>";		
				}

				echo "</tbody></table>";

		echo "</div>"; //end cart div
		// the following div contains the order total information, adds small order charge								
		
		echo '<div id="total_area">';
				
//check for user address info and prompt entry if not present - we need this at this stage for sales tax, but will also use below for address
//query database
				$SQL = "SELECT * FROM ht_address WHERE Address_Cust_ID ='".$user_id."'";
				$result = $database->query($SQL);
				$address_arr = $result->fetch();

				if ($address_arr['Address_Billing_State'] == 'KY') {
				$has_tax = 1; // = 
				}


				
		
		//add it all up!
				//check for empty cart
				if (count($cart_array) == 0 ) {
					echo "Your cart is empty!<br>";
				//if not empty, start with subtotal				
				}	else {		
				echo "Subtotal: $".$orderprice."<br>";
				}
					
				//check for tax
				if ($has_tax == 1) {
				$orderprice = number_format($orderprice, 2);
				$tax = ($orderprice * 0.06);
		
				$orderprice = ($orderprice + $tax);
				$tax = number_format($tax, 2);
				echo "Kentucky State Sales Tax (KY Residents Only): $".$tax."<br>";
				}


				//check for small order
				if (($orderprice < 125) && ($orderprice != 0)) {
				$orderprice = number_format($orderprice, 2); //set to 2 decimal places 
				
				echo "Small order charge: $6.70 <br>";
				$orderprice = $orderprice + 6.70;
				}
				

				//large orders				
				$orderprice = number_format($orderprice, 2);
				echo "Total: $".$orderprice."<br>";
			
			echo "</div>";		
//end total section
		
//next section is user address details
		echo "<div id='address'>";
		echo "<h1>Address Information</h1>";
		echo "<div id='address_boxes'>";
			if ($address_arr == null) {

				echo "<br>You have not entered any address information with us. Please enter it below before checkout.";
				echo "<div class='addy_box' id='addy_form_div'>";
			//if address on file, make this a hidden div				
			} else {
				echo "<div id='addy_form_div' class='hidden_div addy_box'>";
			}
				
				echo "<form action='new_address.php' id='addy_form' name='addy_form' >";
				echo "<input type='hidden' id='addy_user_id' name='addy_user_id' value='".$user_id."'>";
				echo "<p><label id='email_label' class='field' for='addy_email'>Confirm your email:</label></p><p><input required type='text' id='addy_email' name='addy_email' class='addy_long'></p>";	
				echo "<p>Billing Address</p>";
				echo "<p><label class='field' for='addy_fname'>First Name:</label><input required type='text' id='addy_fname' name='addy_fname' class='addy_long'></p>";
				echo "<p><label class='field' for='addy_lname'>Last Name:</label><input required type='text' id='addy_lname' name='addy_lname' class='addy_long'></p>";				
				echo "<p><label class='field' for='addy1'>Address 1:</label><input required type='text' id='addy1' name='addy1' class='addy_long'></p>";	
				echo "<p><label class='field' for='addy2'>Address 2:</label><input type='text' id='addy2' name='addy2' class='addy_long'></p>";	
				echo "<p><label class='field' for='addy_city'>City:</label><input required type='text' id='addy_city' name='addy_city' class='addy_medium'></p>";	

	//pay careful attention to quotes on these lines for the state selector, they are backwards from my usual style
				echo '<p><label class="field" for="addy_state">State:</label><select required id="addy_state" name="addy_state">';	

					echo '<option value="AL">Alabama</option>';
					echo '<option value="AK">Alaska</option>';
					echo '<option value="AZ">Arizona</option>';
					echo '<option value="AR">Arkansas</option>';
					echo '<option value="CA">California</option>';
					echo '<option value="CO">Colorado</option>';
					echo '<option value="CT">Connecticut</option>';
					echo '<option value="DE">Delaware</option>';
					echo '<option value="DC">District Of Columbia</option>';
					echo '<option value="FL">Florida</option>';
					echo '<option value="GA">Georgia</option>';
					echo '<option value="HI">Hawaii</option>';
					echo '<option value="ID">Idaho</option>';
					echo '<option value="IL">Illinois</option>';
					echo '<option value="IN">Indiana</option>';
					echo '<option value="IA">Iowa</option>';
					echo '<option value="KS">Kansas</option>';
					echo '<option value="KY">Kentucky</option>';
					echo '<option value="LA">Louisiana</option>';
					echo '<option value="ME">Maine</option>';
					echo '<option value="MD">Maryland</option>';
					echo '<option value="MA">Massachusetts</option>';
					echo '<option value="MI">Michigan</option>';
					echo '<option value="MN">Minnesota</option>';
					echo '<option value="MS">Mississippi</option>';
					echo '<option value="MO">Missouri</option>';
					echo '<option value="MT">Montana</option>';
					echo '<option value="NE">Nebraska</option>';
					echo '<option value="NV">Nevada</option>';
					echo '<option value="NH">New Hampshire</option>';
					echo '<option value="NJ">New Jersey</option>';
					echo '<option value="NM">New Mexico</option>';
					echo '<option value="NY">New York</option>';
					echo '<option value="NC">North Carolina</option>';
					echo '<option value="ND">North Dakota</option>';
					echo '<option value="OH">Ohio</option>';
					echo '<option value="OK">Oklahoma</option>';
					echo '<option value="OR">Oregon</option>';
					echo '<option value="PA">Pennsylvania</option>';
					echo '<option value="RI">Rhode Island</option>';
					echo '<option value="SC">South Carolina</option>';
					echo '<option value="SD">South Dakota</option>';
					echo '<option value="TN">Tennessee</option>';
					echo '<option value="TX">Texas</option>';
					echo '<option value="UT">Utah</option>';
					echo '<option value="VT">Vermont</option>';
					echo '<option value="VA">Virginia</option>';
					echo '<option value="WA">Washington</option>';
					echo '<option value="WV">West Virginia</option>';
					echo '<option value="WI">Wisconsin</option>';
					echo '<option value="WY">Wyoming</option>';
				echo"</select></p>";	
				echo "<p><label class='field' for='addy_zip'>ZIP:</label><input required type='text' id='addy_zip' name='addy_zip' class='addy_long'></p>";	
				echo "<p><label class='field' for='addy_phone'>Phone:</label><input required type='text' id='addy_phone' name='addy_phone' class='addy_long'></p>";		
				echo "<p><input type='submit' id='addy_submit' class='cart_button' value='Done' form='addy_form'>";
				echo "<button class='cart_button' id='addy_cxl'>Cancel</button></p>";			
				echo "</form>";

				echo "</div>";			
			
		//If there is an address on file, show it 
			 if ($address_arr != null) {

				echo "<div id='billing_div' class='addy_box'>";
		//confirm primary email
			$SQL = "SELECT Cust_Primary FROM ht_customer_email WHERE Cust_ID ='".$user_id."'";
				$result = $database->query($SQL);
				$prim_email = $result->fetchColumn();

			//walk through address_arr and echo out information
				$addy_fname = $address_arr[2];	
				$addy_lname = $address_arr[3];	
				$addy1 = $address_arr[4];		
				$addy2 = $address_arr[5];	
				$addy_city = $address_arr[6];	
				$addy_state = $address_arr[7];			
				$addy_zip = $address_arr[8];	
				$addy_phone = $address_arr[9];
			
			//echo out info for display. Span elements used to pass info to shipping form
				echo "<h2>Your Primary Email:</h2><span id='email_wrap'>".$prim_email."</span>";
				echo "<h2>Your Billing Address:</h2>";
				echo "<p><span id='fname_wrap'>".$addy_fname."</span> <span id='lname_wrap'>".$addy_lname."</span></p>";				
				echo "<p><span id='addy1_wrap'>".$addy1."</span><span id='addy2_wrap'>".$addy2."</span></p>";
				echo "<p><span id='addy_city_wrap'>".$addy_city."</span>, <span id='addy_state_wrap'>".$addy_state."</span> <span id='addy_zip_wrap'>".$addy_zip."</span></p>";
				echo "<h2>Phone:</h2><p><span id='addy_phone_wrap'>".$addy_phone."</span></p>";
//change billing button
				echo "<button class='cart_button' id='chg_addy'>Change My Billing Information</button>";

				echo "</div>"; //end billing div
				
		

			
			echo "<div id='checkout' class='addy_box' id='shipping_div'>";
				echo "<form action='payment_test.php' id='checkout_form' name='checkout_form' method='post'>";
				echo "<input type='hidden' id='shipping_user_id' name='shipping_user_id' value='".$user_id."'>";
				echo "<p><label id='sab_label' class='field' for='same_as_bill'>Same as billing: </label><input type='checkbox' id='same_as_bill' name='same_as_bill'></p>";	
				echo "<h2>Shipping Address</h2>";
				echo "<p><label class='field' for='shipping_fname'>First Name:</label><input required type='text' id='shipping_fname' name='shipping_fname' class='shipping_long'></p>";	
				echo "<p><label class='field' for='shipping_lname'>Last Name:</label><input required type='text' id='shipping_lname' name='shipping_lname' class='shipping_long'></p>";	
				echo "<p><label class='field' for='shipping1'>Address 1:</label><input required type='text' id='shipping1' name='shipping1' class='shipping_long'></p>";	
				echo "<p><label class='field' for='shipping2'>Address 2:</label><input type='text' id='shipping2' name='shipping2' class='shipping_long'></p>";	
				echo "<p><label class='field' for='shipping_city'>City:</label><input required type='text' id='shipping_city' name='shipping_city' class='shipping_medium'><p>";	

	//pay careful attention to quotes on these lines for the state selector, they are backwards from my usual style
				echo '<p><label class="field" for="shipping_state">State:</label><select required id="shipping_state" name="shipping_state">';	

					echo '<option value="AL">Alabama</option>';
					echo '<option value="AK">Alaska</option>';
					echo '<option value="AZ">Arizona</option>';
					echo '<option value="AR">Arkansas</option>';
					echo '<option value="CA">California</option>';
					echo '<option value="CO">Colorado</option>';
					echo '<option value="CT">Connecticut</option>';
					echo '<option value="DE">Delaware</option>';
					echo '<option value="DC">District Of Columbia</option>';
					echo '<option value="FL">Florida</option>';
					echo '<option value="GA">Georgia</option>';
					echo '<option value="HI">Hawaii</option>';
					echo '<option value="ID">Idaho</option>';
					echo '<option value="IL">Illinois</option>';
					echo '<option value="IN">Indiana</option>';
					echo '<option value="IA">Iowa</option>';
					echo '<option value="KS">Kansas</option>';
					echo '<option value="KY">Kentucky</option>';
					echo '<option value="LA">Louisiana</option>';
					echo '<option value="ME">Maine</option>';
					echo '<option value="MD">Maryland</option>';
					echo '<option value="MA">Massachusetts</option>';
					echo '<option value="MI">Michigan</option>';
					echo '<option value="MN">Minnesota</option>';
					echo '<option value="MS">Mississippi</option>';
					echo '<option value="MO">Missouri</option>';
					echo '<option value="MT">Montana</option>';
					echo '<option value="NE">Nebraska</option>';
					echo '<option value="NV">Nevada</option>';
					echo '<option value="NH">New Hampshire</option>';
					echo '<option value="NJ">New Jersey</option>';
					echo '<option value="NM">New Mexico</option>';
					echo '<option value="NY">New York</option>';
					echo '<option value="NC">North Carolina</option>';
					echo '<option value="ND">North Dakota</option>';
					echo '<option value="OH">Ohio</option>';
					echo '<option value="OK">Oklahoma</option>';
					echo '<option value="OR">Oregon</option>';
					echo '<option value="PA">Pennsylvania</option>';
					echo '<option value="RI">Rhode Island</option>';
					echo '<option value="SC">South Carolina</option>';
					echo '<option value="SD">South Dakota</option>';
					echo '<option value="TN">Tennessee</option>';
					echo '<option value="TX">Texas</option>';
					echo '<option value="UT">Utah</option>';
					echo '<option value="VT">Vermont</option>';
					echo '<option value="VA">Virginia</option>';
					echo '<option value="WA">Washington</option>';
					echo '<option value="WV">West Virginia</option>';
					echo '<option value="WI">Wisconsin</option>';
					echo '<option value="WY">Wyoming</option>';
				echo"</select></p>";	
				echo "<p><label class='field' for='shipping_zip'>ZIP:</label><input required type='text' id='shipping_zip' name='shipping_zip' class='shipping_long'></p>";			

	

			//change addy and email button
				echo "<div class='button_wrap'>";
			//create checkout button with hidden divs covering order total and id

				$checkoutbutton = "<input type='hidden' name='order_total' value=".$orderprice.">";
				$checkoutbutton .= "<input type='hidden' name='order_id' value=".$order_id.">";
				$checkoutbutton .= "<p><input type='submit' class='cart_button' id='checkout_button'value='Go to Checkout'></p></form>";

	
				echo $checkoutbutton;
				echo "</div>";
				echo "</div>"; //end shipping address div
			echo "</div>";
			echo "</div>";
			}
				echo "</div>";	//end shipping address display		
			
		echo "</div>"; //end address area

	}
		 //end of display cart scripts

?>
