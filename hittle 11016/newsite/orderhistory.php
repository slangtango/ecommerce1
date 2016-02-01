<?php 
/////////////////////////////////
//										 //
//			ORDER HISTORY			 //
//										 //
/////////////////////////////////

// This file puts together and displays the user's order history. It selects all orders
// corresponding to a certain user, loops through the list and displays each order's info,
// including order status, calculating small order charge, and tax

//connect to database
 include "connection.php";

//identify the user
$user_id = $_SESSION['CustID'];

// if unknown user, direct to landing page for login
if ($user_id == null) {
	echo "You are not logged in! Please click <a href='landing.php'>here</a> to log in.";
}

//else, go to database and get user's order history
else {

	//select the user's orders (exclude open orders)
		$SQL = "SELECT * FROM ht_orders WHERE Orders_Cust_ID = '".$user_id."' AND Orders_Status > 0 ORDER BY Orders_ID DESC"; 
		$result = $database->query($SQL);
		$order_array = $result->fetchAll();

	//then, loop through each order, get the contents, and display


foreach ($order_array as $order) {
	//get the basic variables
		$order_id = $order[0];
		$order_status = $order[3];
		$order_date = $order[5];

		if ($order_date == null) {
			$order_date = date('d-m-Y');
		}

		//generate a display id for the order (add date)
		$date_array = explode("-", $order_date);
		$new_order_date = $date_array[1].$date_array[2]; //get month and day only
		
		$display_id = $order_id."-".$new_order_date;

		//mktime creates a UNIX timestamp from the date array, and date() reformats it to text for display
		$display_date = date('F j, Y', mktime(0,0,0, $date_array[1], $date_array[2], $date_array[0]));

	//echo order header
	echo "<h2>Order #".$display_id." placed on ".$display_date."</h2>";


	//echo status
	if ($order_status == 2) {
		echo "Status: Paid, shipping soon";
	} else if ($order_status == 0) {
		echo "This order is your current open order";
	} else if ($order_status == 1) {
		echo "Status: Awaiting payment verification from PayPal";
	} else if ($order_status == 9) {
		echo "Status: Incomplete: payment could not be processed. Please call J Hittle for assistance.";
	} 


	//select order contents

		$SQL = "SELECT Cart_Order_Entry_ID, Cart_Order_Item_ID, Cart_Order_Qty, Cart_Order_List_Price FROM ht_cart_order"; 
		$SQL .= " WHERE Cart_Order_ID = '".$order_id."'";
		$result = $database->query($SQL);
		$cart_array = $result->fetchAll();
	

//loop over cart array and display each item // direct copy from shopping cart

	//create table title row and set order total to zero
		echo "<table class='responsive_table' id='cart_table'>";
		echo "<thead><tr><th id='cart_desc_col'>Description</th><th class='qty_col'>Qty</th><th>Price</th><th>Subtotal</th></tr></thead><tbody>";
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
				

		//if it's a special
		if (($first == 's') OR ($first == 'f')) {
			$SQL = "SELECT Specials_Item_Description, Specials_Title FROM ht_specials WHERE Specials_Item_ID = '".$cart_item['Cart_Order_Item_ID']."'";
			$result = $database->query($SQL);
			$descriptions = $result->fetch();
	
		//display text for specials composed of title and item_desc	
			$item_desc = "Special: ".$descriptions['Specials_Title']." ".$descriptions['Specials_Item_Description'];

		} else { //regular catalog items
	
		//get each item description from ht_cart_ac
			$SQL = "SELECT Cart_Description FROM ht_cart_ac WHERE Cart_Item_Number = '".$cart_item['Cart_Order_Item_ID']."'";
			$result = $database->query($SQL);
			$item_desc = $result->fetchColumn();
			}

		//if no description is available
			if ($item_desc == null) {
					$item_desc = "No description available";
				}

		//use this variable to give all cart order entries unique ids
			$entry = $cart_item['Cart_Order_Entry_ID'];


			echo "<tr><td>".$item_desc."</td>";
			echo "<td id='qty".$entry."'>".$cart_item['Cart_Order_Qty']."</td>";
			echo "<td id='price".$entry."'>".$price."</td><td id='total".$entry."'>".$totalprice."</td>";															
			}


			echo "</tbody></table>";

		// the next div includes the total information, and adds tax and small order charge								
		
		echo '<div id="total_area">';
				
		//check for user address for KY sales tax
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
				$orderprice = number_format($orderprice, 2); //two decimal places for prices
				echo "Subtotal: $".$orderprice."<br>";
				}
	

				//check for small order
				if (($orderprice < 125) && ($orderprice != 0)) {
				$orderprice = number_format($orderprice, 2); //set to 2 decimal places 
				
				echo "Small order charge: $6.70 <br>";
				$orderprice = $orderprice + 6.70;
				}

				
				//check for tax
				if ($has_tax == 1) {
				$orderprice = number_format($orderprice, 2);
				$tax = ($orderprice * 0.06);
		
				$orderprice = ($orderprice + $tax);
				$tax = number_format($tax, 2);
				echo "Kentucky State Sales Tax (KY Residents Only): $".$tax."<br>";
				}



				

				//large orders				
				$orderprice = number_format($orderprice, 2);
				echo "Total: $".$orderprice."<br>";
			
			echo "</div>";	
			echo "<hr>"; // the horizontal rule divides each order from the next one
}

}	



?>
