<?php 
//ORDER HISTORY
//this file puts together and display's the user's order history

//connect to database
 include "connection.php";

//identify the user
$user_id = $_SESSION['CustID'];

// if unknown user, direct to login
if ($user_id == null) {
	echo "You are not logged in! Please click <a href='landing.php'>here</a> to log in.";
}

//else, go to database and get user's order history
else {

	//select the user's orders
		$SQL = "SELECT * FROM ht_orders WHERE Orders_Cust_ID = '".$user_id."'"; 
		$result = $database->query($SQL);
		$order_array = $result->fetchAll();

	//then, loop through each order, get the contents, and display


foreach ($order_array as $order) {
	//get the basic variables
		$order_id = $order[0];
		$order_status = $order[3];
		$order_date = $order[5];

	//echo order header
	echo "<h2>Order #".$order_id." placed on ".$order_date."</h2>";


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

				//get each item description from ht_cart_ac
				$SQL = "SELECT Cart_Description FROM ht_cart_ac WHERE Cart_Item_Number = '".$cart_item['Cart_Order_Item_ID']."'";
				$result = $database->query($SQL);
				$item_desc = $result->fetchColumn();

				//if no description
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
			echo "<hr>";
}

}	



?>
