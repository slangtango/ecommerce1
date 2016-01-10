<?php
//Payment Test Page
session_start();
$_SESSION['orderStatus'] = "atCheckout";
///////////////////
//DATABASE CONNECTION
//////////////////

include ("connection.php");

/////////////////////////////////////////////////

//page header
include 'header.php';

//if we came from paypal redirect
//get order id
	$order_id = $_GET['order_id'];
	$return = $_GET['return'];



if ($return != 1) {
//If we came from the shopping cart page
	//get order total from shopping cart page for use
	$order_id = $_POST['order_id'];
 	$order_total = $_POST['order_total'];

	//also get shipping address
	$fname = $_POST['shipping_fname'];
	$lname = $_POST['shipping_lname'];
	$ship1 = $_POST['shipping1'];
	$ship2 = $_POST['shipping2'];
	$city = $_POST['shipping_city'];
	$state = $_POST['shipping_state'];
	$zip = $_POST['shipping_zip'];


} 

//get customer info
	$order_cust_id = $_SESSION['CustID'];  
	$user_email = $_COOKIE['loginEmail'];


//make page header
	echo "<h2>Confirmation: Order #".$order_id."</h2>";


//Check to see if we came back from paypal
	if ($return == 1) {

	//delay execution  2 seconds so database has time to update

	sleep(2);
	
	//check database for order status
	$SQL = "SELECT Orders_Status FROM ht_orders WHERE Orders_ID = :id";
	$stmt = $database->prepare($SQL); //Sanitize statement to prevent SQL injection	
	$stmt->bindParam(':id', $order_id);
	$stmt->execute();

	$result = $stmt->fetchColumn(0);


//order confirmed properly
		if ($result == 2) {
		echo "<h2>Thank you for your order</h2>";
		echo "<p>Your order ID is:".$_SESSION['Order_ID']."</p>";

		echo "<p><a href='index.php'>Start a new order</a></p>";

//order set to status 9, failure to complete payment transaction
		} else if ($result == 9) {
		echo "<h1>Error</h1>";
		echo "<p>Something went wrong with your order and payment could not be completed.</p>"; 
		echo "Please call J Hittle Sewing at 937-1503</p>";
		echo "<p>Your order ID is:".$_SESSION['Order_ID']."</p>";
		echo "<p><a href='index.php'>Return to main page</a></p>";

//order status did not update in the database yet
		} else if ($result == 1) {
			echo "<h2>Almost Done!</h2>";
			echo "<p>Oops! There was a delay in confirming your order. Please refresh the page.</p>"; 
			echo "<p>If the problem persists, please call J Hittle Sewing at 502-937-1503 and we'll sort it out for you. Thanks!</p>";
		}

		
	}



//code to run if customer has come from shopping cart area
	else if ($return != 1) {

//Write to order database

		$SQL = "UPDATE ht_orders SET Orders_Cust_ID = :cust_id, Orders_Gross_Total = :total, Orders_Status = 1 WHERE Orders_ID = :id";
		$stmt = $database->prepare($SQL); //Sanitize statement to prevent SQL injection	
		$stmt->bindParam(':id', $order_id);
		$stmt->bindParam(':cust_id', $order_cust_id);
		$stmt->bindParam(':total', $order_total);

		$stmt->execute();

//write to address database
//Check to see if this user already had address info
	$SQL = "SELECT * FROM ht_address WHERE Address_Cust_ID = :user_id";
	$stmt = $database->prepare($SQL);
	$stmt->bindParam(':user_id', $order_cust_id);

	$stmt->execute();
	$result = $stmt->fetchAll();

if ($result == null) {
	
//if new address (none on file)
	//update address table
	$SQL = "INSERT INTO ht_address (Address_Shipping_Fname, Address_Shipping_Lname, Address_Shipping, Address_Shipping_2, Address_Shipping_City, Address_Shipping_State, Address_Shipping_Zip, Address_Cust_ID)";
	$SQL .= "VALUES (:addy_fname, :addy_lname, :addy1, :addy2, :addy_city, :addy_state, :addy_zip, :user_id)";
	$stmt = $database->prepare($SQL);
	$stmt->bindParam(':addy_fname', $fname);
	$stmt->bindParam(':addy_lname', $lname);
	$stmt->bindParam(':addy1', $ship1);
	$stmt->bindParam(':addy2', $ship2);
	$stmt->bindParam(':addy_city', $city);
	$stmt->bindParam(':addy_state', $state);
	$stmt->bindParam(':addy_zip', $zip);
	$stmt->bindParam(':user_id', $order_cust_id);

		$stmt->execute();
	
} else {

//if existing address on file, update
	$SQL = "UPDATE ht_address SET Address_Shipping_Fname = :addy_fname, Address_Shipping_Lname = :addy_lname, Address_Shipping = :addy1, Address_Shipping_2 = :addy2, Address_Shipping_City = :addy_city,";
	$SQL .= " Address_Shipping_State = :addy_state, Address_Shipping_Zip = :addy_zip WHERE Address_Cust_ID = :user_id";
	$stmt = $database->prepare($SQL);
	$stmt->bindParam(':addy_fname', $fname);
	$stmt->bindParam(':addy_lname', $lname);
	$stmt->bindParam(':addy1', $ship1);
	$stmt->bindParam(':addy2', $ship2);
	$stmt->bindParam(':addy_city', $city);
	$stmt->bindParam(':addy_state', $state);
	$stmt->bindParam(':addy_zip', $zip);
	$stmt->bindParam(':user_id', $order_cust_id);

	$stmt->execute();
}

// see what's in the cart
				$SQL = "SELECT Cart_Order_Item_ID, Cart_Order_Qty, Cart_Order_List_Price FROM ht_cart_order"; 
				$SQL .= " WHERE Cart_Order_ID = '".$order_id."'";
				$result = $database->query($SQL);
				$cart_array = $result->fetchAll();



//get customer address info
	$SQL = "SELECT * FROM ht_address WHERE Address_Cust_ID ='".$order_cust_id."'";
				$result = $database->query($SQL);
				$address_arr = $result->fetch();
			//walk through address_arr assign info to variables
				$fname = $address_arr[10];
				$lname = $address_arr[11];
				$addy1 = $address_arr[12];		
				$addy2 = $address_arr[13];	
				$addy_city = $address_arr[14];	
				$addy_state = $address_arr[15];			
				$addy_zip = $address_arr[16];	

//get customer primary email
	$SQL = "SELECT Cust_Primary FROM ht_customer_email WHERE Cust_ID ='".$order_cust_id."'";
				$result = $database->query($SQL);
				$prim_email = $result->fetchColumn();




//Echo checkout area information

 	echo "<h1>Welcome to Checkout!</h1>";	
	echo "<div class='conf_box'>";
	echo "<h2>Customer Details</h2>";
	echo "<p>Name: ".$fname." ".$lname."</p>";
	echo "<p>Account #: ".$order_cust_id."</p>";
	echo "<p>Primary Email: ".$prim_email."</p>";
	echo "<h2>Shipping Address</h2>";
	echo "<p>".$addy1.$addy2."</p>";
	echo "<p>".$addy_city.", ".$addy_state." ".$addy_zip."</p>";
	echo "</div>";

	echo "<div class='conf_box'>";
	echo "<h2>Order Details</h2>";
//The PHP below displays the order info to the user-->
	echo "<h2>Order ID: #".$_SESSION['Order_ID']."</h2>";

//loop through and generate cart item listings
	echo "<p>Contents:</p>";
	foreach ($cart_array as $cart_item) {
//get each item description from ht_cart_ac
				$SQL = "SELECT Cart_Description FROM ht_cart_ac WHERE Cart_Item_Number = '".$cart_item['Cart_Order_Item_ID']."'";
				$result = $database->query($SQL);
				$item_desc = $result->fetchColumn();
			
				$length = $result->rowCount();
			if ($length == 0) {
				$item_desc = 'Special or Featured Item';
			}

			$subtotal = ($cart_item[1] * $cart_item[2]);
				$subtotal = number_format($subtotal, 2);

	echo "<p>".$item_desc."</p>";
	echo "<p> Qty: ".$cart_item[1]." Price: $".$cart_item[2]." Subtotal: $".$subtotal."</p>";
		}	


	echo "<h2>Order Total: $".$order_total."</h2>"; 
	echo "</div>";



//generate the paypal button
$button = "<form id='conf_form' action='https://www.sandbox.paypal.com/cgi-bin/webscr' method='post' id='checkout'>";
$button .= "<input type='hidden' name='cmd' value='_cart'>";
$button .= "<input type='hidden' name='upload' value='1'>";
$button .= "<input type='hidden' name='business' value='hittletest@test.com'>"; // change this to the real email when you go live
$button .= "<input type='hidden' name='item_name_1' value='JHS Order ID# ".$order_id."'>";
$button .= "<input type='hidden' name='amount_1' value='".$order_total."'>";
//the following lines are to help paypal populate data for new members and can be removed if problematic
$button .= "<input type='hidden' name='first_name' value='".$fname."'>";
$button .= "<input type='hidden' name='last_name' value='".$lname."'>";
$button .= "<input type='hidden' name='address1' value='".$addy1."'>";
$button .= "<input type='hidden' name='address2' value='".$addy2."'>";
$button .= "<input type='hidden' name='city' value='".$addy_city."'>";
$button .= "<input type='hidden' name='state' value='".$addy_state."'>";
$button .= "<input type='hidden' name='zip' value='".$addy_zip."'>";
//back to important stuff
$button .= "<input id='checkout' type='submit' class='cart_button' value='Proceed to Checkout'>";
$button .= "<input type='hidden' name='return' value='http://www.clickhere4fun.com/chffinmot/newsite/payment_test.php?return=1&order_id=".$order_id."'>";
$button .= "</form><br>";

echo $button;



echo "<a href='index.php?keepShopping=1&showCart=1'>Add to this order</a>";
}

?>

	<!-- ADDITIONAL SCRIPT IMPORTS -->
    <!-- jQuery -->
	 <!-- The following file imports the JQuery library. It must be included or the other scripts will not work -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>

<!-- Include the jQuery Mobile library -->
<!-- <script src="http://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js"></script>


	<!-- Script which handles accordion menu and results per page -->
	<script src="js/displayproducts.js"></script>

	<!-- Script for autocomplete in search boxes -->
	 <script src="js/autocomplete.js"></script>

	<!-- Script for shopping cart actions -->
	 <script src="js/addtocart.js"></script>

</body>
