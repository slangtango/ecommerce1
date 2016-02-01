<?php
/////////////////////////////
//									//
// SHOPPING CART.PHP			//
//									//
/////////////////////////////

// This file displays the shopping cart and user address form when it is included in index.php.

// It is refreshed dynamically (without overall page reload) by the jquery ajax functions in addtocart.js.

// 1 - It starts by checking the user and order ID.
// 2 - Next, we create the Quick Add feature.
// 3 - Then we echo the user's current order contents in table form, including Remove and Change Qty buttons.
		// A key feature here is the use of the 'id' attribute. The id of quantity and price table cells is set by
		// concatenating "qty" or "price" with the line item entry id, which is the key value in ht_cart_order.
		// Setting it this way allows the addtocart.js file to interact with specific cells in the table, even though
		// they are dynamically created and destroyed by user actions. 

// 4 - Then we total up, checking for small order charge and KY sales tax
// 5 - Lastly, we display any known user address info, and form inputs as needed.
		// Another key feature to understand here: the shipping form also submits the order id, user id, and total
		// to the confirmation.php checkout page. The billing address form is operated by addtocart.js, which submits
		// its requests to new_address.php


////////////////////////////////////////////////////////////////////////////////
// 
//   1 - CONNECT TO DATABASE, CHECK USER AND ORDER ID
 include "connection.php";
	
			if ($_GET['reload'] == 1) {
			//get variables ready	
					$order_id = $_GET['Order_ID'];
					$user_id = $_GET['CustID'];
			} else {
			//getvariables ready	
					$order_id = $_SESSION['Order_ID'];
					$user_id = $_SESSION['CustID'];
			}
			// If no user data is available, prompt them to login
			if ($user_id == null) {
				echo "You are not logged in! <br> You may be a new visitor, or you may have deleted your cookies. <br>";
				echo "Follow this link to open a new or existing account: <a href='landing.php'>New Account</a>";
			

			//if they are logged in, continue with the script
//////////////////////////////////////////////////////////////////////////////
//
//		2 - DISPLAY QUICK ADD FEATURE

			} else {
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
			echo "<input type='hidden' name='id' id='qa_item_id'>"; //value is set when user clicks autocomp link
			echo "<input type='hidden' name='user_id' id='qa_user_id' value='".$user_id."'>";	
			echo "<input type='hidden' name='quick' value=1>";
			echo "<input type='hidden' name='order_id' id='qa_order_id' value='".$order_id."'></td>";			
			echo "<td><input type='submit' class='add_to_cart cart_button' value='Update Cart'></td>";
			echo "</tr></tbody>";		
			echo "</table>";

			echo "</form>";


		echo "</div>";

///////////////////////////////////////////////////////////////////////////////////
//
//		3 - DISPLAY CART
		echo "<div id='cart'>";
		
		// generate display order ID by adding date
				$date = date('md');
						
				$display_id = $order_id.$date;			
				echo "<h1>Your Order Number: ".$display_id."</h1>";

				// stash User and Order ID variables in invisible inputs
				echo "<input type='hidden' id='user_id' value='".$user_id."'>";
				echo "<input type='hidden' id='order_id' value='".$order_id."'>";
			
	
				// Select all items from the cart order table with this order id
				$SQL = "SELECT Cart_Order_Entry_ID, Cart_Order_Item_ID, Cart_Order_Qty, Cart_Order_List_Price FROM ht_cart_order"; 
				$SQL .= " WHERE Cart_Order_ID = '".$order_id."'";
				$result = $database->query($SQL);
				$cart_array = $result->fetchAll();

				// Loop over cart array and display each item

				// Start by creating table title row and set order total to zero
				echo "<table class='responsive_table' id='cart_table'>";
				echo "<thead><tr><th id='cart_desc_col'>Description</th><th class='qty_col'>Qty</th><th>Price</th><th>Subtotal</th><th></th><th></th></tr></thead><tbody>";
				$orderprice = 0;

				// For each item, get prices, descriptions, and add to the table
				foreach ($cart_array as $cart_item) {

					//Get item price and standardize to 2 decimal places				
					$price = number_format($cart_item['Cart_Order_List_Price'], 2);

					// Calculate price per line item (quantity times price)				
					$totalprice = $cart_item['Cart_Order_Qty'] * $price;
					$totalprice = number_format($totalprice, 2); //set to 2 decimal places
	
					// Add this to the order total, $orderprice
					$orderprice = $orderprice + $totalprice;

					// Get each item description
						//Check to see if it is a regular catalog item or special item
						$first = substr($cart_item['Cart_Order_Item_ID'], 0, 1);
				

						//if it's a special
						if (($first == 's') OR ($first == 'f')) {
							$SQL = "SELECT Specials_Item_Description, Specials_Title FROM ht_specials WHERE Specials_Item_ID = '".$cart_item['Cart_Order_Item_ID']."'";
							$result = $database->query($SQL);
							$descriptions = $result->fetch();				

						//display text for specials composed of title and item_desc	
							$item_desc = "Special: ".$descriptions['Specials_Title']." ".$descriptions['Specials_Item_Description'];
					

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

				// Echo the table cells with Description, Qty, Price, Subtotal, and the Remove and Change buttons
					// remember, we append $entry to each 'id' attribute so the JavaScript files know which lines
					// to interact with
					echo "<tr><td>".$item_desc."</td>";
					echo "<td id='qty".$entry."'>".$cart_item['Cart_Order_Qty']."</td>";
					echo "<td id='price".$entry."'>".$price."</td><td id='total".$entry."'>".$totalprice."</td>";													
					echo "<td><button class='delete_item cart_button' value='".$entry."'>Remove</button></td>";
					echo "<td><button class='change_qty cart_button' value='".$entry."'>Change Qty</button></td></tr>";		
					}
				// End the cart table
				echo "</tbody></table>";

		echo "</div>"; //end cart div

////////////////////////////////////////////////////////////////////////////
//
//			4 - TOTAL AREA, SALES TAX AND SMALL ORDER CHARGE							
		
		echo '<div id="total_area">';
				
// Check for user address info and prompt entry if not present - 
// We need this at this stage for sales tax, but will also use below for address

			$SQL = "SELECT * FROM ht_address WHERE Address_Cust_ID ='".$user_id."'";
			$result = $database->query($SQL);
			$address_arr = $result->fetch();

			if ($address_arr['Address_Billing_State'] == 'KY') {
				$has_tax = 1; // will use this to trigger the tax calculation
			}

						
// Add it all up!

			//Check for empty cart
				if (count($cart_array) == 0 ) {
					echo "Your cart is empty!<br>";
	
			// If not empty, start with order subtotal (no tax or small order charge)				
				}	else {		
				$orderprice = number_format($orderprice, 2); //set to 2 decimal places
				echo "Subtotal: $".$orderprice."<br>";
				}
					
			// Check for small order - < $125
				if (($orderprice < 125) && ($orderprice != 0)) {
				$orderprice = number_format($orderprice, 2); //set to 2 decimal places 
				
				echo "Small order charge: $6.70 <span class='tooltip_title'>What's this?</span>";
				// the next line is hidden unless the 'Whats This' tooltip is clicked
				echo "<span class='tooltip'> Small order charge of $6.70 applies to orders under $125</span>";
				$orderprice = $orderprice + 6.70;
				}

			// Check for tax
				if ($has_tax == 1) {
				$orderprice = number_format($orderprice, 2);
				$tax = ($orderprice * 0.06);
		
				$orderprice = ($orderprice + $tax);
				$tax = number_format($tax, 2);
				echo "<br>Kentucky State Sales Tax (KY Residents Only): $".$tax."<br>";
				}

			// Echo the final total			
				$orderprice = number_format($orderprice, 2);
				echo "<br>Total: $".$orderprice."<br>";
			
			echo "</div>";		
//end total section

////////////////////////////////////////////////////////////////////////////////////
//
//			5 - ADDRESS INFORMATION AND FORMS
		
	//Begin the section
		echo "<div id='address'>";
		echo "<h1>Address Information</h1>";
		echo "<div id='address_boxes'>";

	//If no address info is present, prompt for input and begin addy_form
			if ($address_arr == null) {

				echo "<br>You have not entered any address information with us. Please enter it below before checkout.";
				echo "<div class='addy_box' id='addy_form_div'>";

	// If address on file, make this a hidden div to hide the form				
			} else {
				echo "<div id='addy_form_div' class='hidden_div addy_box'>";
			}
	
	// Continue with addy_form			
				echo "<form action='new_address.php' id='addy_form' name='addy_form' >";
				echo "<input type='hidden' id='addy_user_id' name='addy_user_id' value='".$user_id."'>";
				echo "<p><label id='email_label' class='field' for='addy_email'>Confirm your email:</label></p><p><input required type='text' id='addy_email' name='addy_email' class='addy_long'></p>";	
				echo "<p>Billing Address</p>";
				echo "<p><label class='field' for='addy_fname'>First Name:</label><input required type='text' id='addy_fname' name='addy_fname' class='addy_long'></p>";
				echo "<p><label class='field' for='addy_lname'>Last Name:</label><input required type='text' id='addy_lname' name='addy_lname' class='addy_long'></p>";				
				echo "<p><label class='field' for='addy1'>Address 1:</label><input required type='text' id='addy1' name='addy1' class='addy_long'></p>";	
				echo "<p><label class='field' for='addy2'>Address 2:</label><input type='text' id='addy2' name='addy2' class='addy_long'></p>";	
				echo "<p><label class='field' for='addy_city'>City:</label><input required type='text' id='addy_city' name='addy_city' class='addy_medium'></p>";	

		// pay careful attention to quotes on these lines for the state selector, they are backwards from my usual style
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
			
		// If there is an address on file, display it
			 if ($address_arr != null) {

				echo "<div id='billing_div' class='addy_box'>";

		// Get primary email from email table
			$SQL = "SELECT Cust_Primary FROM ht_customer_email WHERE Cust_ID ='".$user_id."'";
				$result = $database->query($SQL);
				$prim_email = $result->fetchColumn();

		// Walk through address_arr and assign values to variables
				$addy_fname = $address_arr[2];	
				$addy_lname = $address_arr[3];	
				$addy1 = $address_arr[4];		
				$addy2 = $address_arr[5];	
				$addy_city = $address_arr[6];	
				$addy_state = $address_arr[7];			
				$addy_zip = $address_arr[8];	
				$addy_phone = $address_arr[9];
			
		// Echo out info for display. 
			//Span elements with IDs are used to pass info to shipping form when "same as billing" is clicked.
				echo "<h2>Your Primary Email:</h2><span id='email_wrap'>".$prim_email."</span>";
				echo "<h2>Your Billing Address:</h2>";
				echo "<p><span id='fname_wrap'>".$addy_fname."</span> <span id='lname_wrap'>".$addy_lname."</span></p>";				
				echo "<p><span id='addy1_wrap'>".$addy1."</span> <span id='addy2_wrap'>".$addy2."</span></p>";
				echo "<p><span id='addy_city_wrap'>".$addy_city."</span>, <span id='addy_state_wrap'>".$addy_state."</span> <span id='addy_zip_wrap'>".$addy_zip."</span></p>";
				echo "<h2>Phone:</h2><p><span id='addy_phone_wrap'>".$addy_phone."</span></p>";

		// Add the 'Change my Billing Address' button
				echo "<button class='cart_button' id='chg_addy'>Change My Billing Information</button>";

				echo "</div>"; //end billing div
				
		

		// Next, echo the shipping address form. Though this appears to only collect shipping info, it also submits
		// the order total to the checkout page. Hence the name 'checkout'.			
			echo "<div id='checkout' class='addy_box' id='shipping_div'>";
				echo "<form action='confirmation.php' id='checkout_form' name='checkout_form' method='post'>";
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

	
				echo "<div class='button_wrap'>";

			// Create the checkout button and add hidden inputs to carry total and order id.

				$checkoutbutton = "<input type='hidden' name='order_total' value=".$orderprice.">";
				$checkoutbutton .= "<input type='hidden' name='order_id' value=".$order_id.">";
				$checkoutbutton .= "<p><input type='submit' class='cart_button' id='checkout_button'value='Go to Checkout'></p></form>";

			// echo the checkout button	
				echo $checkoutbutton;

				echo "</div>"; //end button wrap
				echo "</div>"; //end shipping address div
			echo "</div>";
			echo "</div>";
			}
			echo "</div>";			
			
		echo "</div>"; //end address area

	}
		 // End of Shopping Cart script

?>
