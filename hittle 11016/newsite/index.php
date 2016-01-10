<?php 
//This is the main page for Hittle Sewing, by Owen Graham, 2015
//Access is via a landing page which collects user email and basic data, 
// and which should redirect here if the cookie is present.
session_start();
///////////////////
//DATABASE CONNECTION
//////////////////

include ("connection.php");


/////////////////////////////////////////////////
// USER IDENTIFICATION STUFF
//////////////////////////////////////////


//Check to see that this username is collected, and set cookie to remember user


// Get input from POST from landing page, only if they entered a new email
	$newUserEmail = $_POST["useremail"];	
 //type of visit info 
	$casual = $_POST['casual'];
	$craft = $_POST['craft'];
	$seam = $_POST['seam'];
	$small = $_POST['small'];
	$large = $_POST['large'];
	$man = $_POST['man'];
	$other = $_POST['other'];
	$discount = $_POST['discount'];
	$wholesale = $_POST['wholesale'];
	$sender =  "JHS"; // not currently collecting $_POST['sender'];

	$today = date("Y-m-d");
	$visit_time = date("H:i:s");




//Check to see if we're viewing the cart, or order history
	$showCart = $_GET['showCart'];
	$history = $_GET['history'];

//	$addingToCart = $_GET['addingToCart'];	

//The if-else statement below checks to see if the user has the cookie set, which will allow 
//repeat visitors to link directly to index.php if they want. Landing.php should redirect here
//anyway but this is just to be safe


if (($newUserEmail == null) && (isset($_COOKIE["loginEmail"]))) { //if the cookie is present and no new email provided 

		//Set $login to the existing cookie value, so we get the user's email
		$login = $_COOKIE["loginEmail"];			

//second case, just arrived from landing page having entered email - they want a new account
	} else if ($newUserEmail !== null) { //if no cookie, but userEmail entered
	
	   //set a new Cookie loginEmail to the email that was entered and set to last one year
		setCookie(loginEmail, $newUserEmail, time() + (86400 * 365), "/"); // 86400 * 365 = 1 year

		//Set login to the userEmail so we can use it right away, instead of checking for cookie again
		$login = $newUserEmail;	

		//Write the new user to the ht_customer_email table, setting this email to both the main record and primary contact info
		$SQL = "INSERT INTO ht_customer_email (Cust_Email, Cust_Primary) VALUES (:useremail, :userprimary)";
		$stmt = $database->prepare($SQL); //Sanitize statement to prevent SQL injection	
		$stmt->bindParam(':useremail', $login, PDO::PARAM_STR, 100);
		$stmt->bindParam(':userprimary', $login, PDO::PARAM_STR, 100);

		//Write the new user to the visitor log

			

			//// this code was adapted from the old log.php ///
		


		//check to see if this visitor has already been logged
		//$SQL = "SELECT Visitor_email FROM visitor_log WHERE Visitor_email = :email";
		// $stmt = $database->prepare($SQL);
		//$stmt->bindParam(':email', $login, PDO::PARAM_STR, 100);	
		
		//$GetEmailCheck = $stmt->fetch();

			//if they have not been logged, write to visitor log table
		//if ($GetEmailCheck == null)
		//	{
			# setup SQL statement
		//	$SQL = " INSERT INTO visitor_log ";
		//	$SQL = $SQL . " (Visitor_date,Visitor_time,Visitor_enter,Visitor_email,Visitor_casual,Visitor_craft,Visitor_seam,Visitor_small,Visitor_large,Visitor_man,Visitor_other,Visitor_discount,Visitor_wholesale) VALUES ";
		//	$SQL = $SQL . " (:today, :visit_time, :sender, :email, :casual, :craft, :seam, :small, :large, :man, :other, :discount, :wholesale) ";
			
		//	$stmt = $database->prepare($SQL);
		//	$stmt->bindParam(':today', $today);
		//	$stmt->bindParam(':visit_time', $visit_time);
		//	$stmt->bindParam(':sender', $sender);
		//	$stmt->bindParam(':email', $login);
		//	$stmt->bindParam(':casual', $casual);
		//	$stmt->bindParam(':craft', $craft);
		//	$stmt->bindParam(':seam', $seam);
		//	$stmt->bindParam(':small', $small);
		//	$stmt->bindParam(':large', $large);
		//	$stmt->bindParam(':man', $man);
		//	$stmt->bindParam(':other', $other);
		//	$stmt->bindParam(':discount', $discount);
		//	$stmt->bindParam(':wholesale', $wholesale);

			//write to database
		//	$stmt->execute();

	
			//}
		//// end section taken from the old log.php /////		


		if ($stmt->execute()) {
				echo "Added new user ".$login;
			}
		
		} 

//Get the user's ID using their email
		$SQL = "SELECT Cust_ID FROM ht_customer_email WHERE Cust_Email = :useremail";
		$stmt = $database->prepare($SQL); //Sanitize statement to prevent SQL injection	
		$stmt->bindParam(':useremail', $login, PDO::PARAM_STR, 100);
		
		//run it and set session user id if succesful	
		$stmt->execute(); 
		$_SESSION['CustID'] = $stmt->fetchColumn();



			
//////////////////////////////////////
// ORDER IDENTIFICATION AND STATUS STUFF
////////////////////////////////////////

///if no user login is present, do not do anything with orders.... user will continue through site anonymously
if ($login == null) {
	$_SESSION['Order_ID'] = null;
	$_SESSION['CustID'] = null;

	//else check to see where the user came from, and if they have open orders
} else {

	//start by using $login to get an actual Customer ID, and set the session variables that will be used for future actions
 //Get the user's ID using their email
		$SQL = "SELECT Cust_ID FROM ht_customer_email WHERE Cust_Email = :useremail";
		$stmt = $database->prepare($SQL); //Sanitize statement to prevent SQL injection	
		$stmt->bindParam(':useremail', $login, PDO::PARAM_STR, 100);
		
		//run it and set session user id if succesful	
		$stmt->execute(); 
		$_SESSION['CustID'] = $stmt->fetchColumn();


	$order_id = $_SESSION['Order_ID'];

// check to see if the customer returned from the checkout page
	if ($_GET['keepShopping'] == 1) {
			$status = 0;
//reset order status to 0 = open
			$SQL = "UPDATE ht_orders SET Orders_Status = :status WHERE Orders_ID= :id";
			$stmt = $database->prepare($SQL); //Sanitize statement to prevent SQL injection	
			$stmt->bindParam(':id', $order_id);
			$stmt->bindParam(':status', $status);
			$stmt->execute();
	}


//Check for open orders under this customer ID (open order = status 0)
//Select all open orders for this customer (status 0 open, or status 1 confirmed but unpaid)
			$SQL = "SELECT Orders_ID FROM ht_orders WHERE Orders_Cust_ID= :cust_id AND Orders_Status < 2";
			$stmt = $database->prepare($SQL); //Sanitize statement to prevent SQL injection	
			$stmt->bindParam(':cust_id', $_SESSION['CustID']);
			$stmt->execute();

			$open_orders = $stmt->fetchColumn();	

//Count the number of open orders -- this should never be other than one or zero
			$count_orders = count($open_orders);
			if ($open_orders == null) {
			
				$count_orders = 0;
			}
//If there is only one open order -- set Session Order ID to the existing order ID in column 0
		if ($count_orders == 1) {
			
			$_SESSION['Order_ID'] = $open_orders;

			$order_id = $_SESSION['Order_ID'];
			$status = 0;
//reset this order status to 0 = open - just in case
			$SQL = "UPDATE ht_orders SET Orders_Status = :status WHERE Orders_ID= :id";
			$stmt = $database->prepare($SQL); //Sanitize statement to prevent SQL injection	
			$stmt->bindParam(':id', $order_id);
			$stmt->bindParam(':status', $status);
			$stmt->execute();


		}
			
//What if there are multiple open orders? -- delete all but the most recent (this is just in case, should not occur)
		if ($count_orders > 1) {
			
	//get the good record and save it
			$SQL = "SELECT MAX(Orders_ID) FROM ht_orders WHERE Orders_Cust_ID= :cust_id AND Orders_Status < 2";
			$stmt = $database->prepare($SQL); //Sanitize statement to prevent SQL injection	
			$stmt->bindParam(':cust_id', $_SESSION['CustID']);
			$stmt->execute();
			
			
			$open_orders = $stmt->fetchColumn();
			$_SESSION['Order_ID'] = $open_orders;

			$order_id = $_SESSION['Order_ID'];
			$status = 0;
//reset this order status to 0 = open
			$SQL = "UPDATE ht_orders SET Orders_Status = :status WHERE Orders_ID= :id";
			$stmt = $database->prepare($SQL); //Sanitize statement to prevent SQL injection	
			$stmt->bindParam(':id', $order_id);
			$stmt->bindParam(':status', $status);
			$stmt->execute();
	

	//delete the others -- <> is the SQL equiv of !=
			$SQL = "DELETE FROM ht_orders WHERE Orders_ID <> :good_id AND Orders_Cust_ID= :cust_id AND Orders_Status = 0";
			$stmt = $database->prepare($SQL); //Sanitize statement to prevent SQL injection	
			$stmt->bindParam(':cust_id', $_SESSION['CustID']);
			$stmt->bindParam(':good_id', $_SESSION['Order_ID']);
			$stmt->execute();
			}

//What if there are no open orders? Create a new one
		if ($count_orders == 0) {
	//Write the new record to the database
			$status = 0;
			$SQL = "INSERT INTO ht_orders(Orders_Cust_ID, Orders_Status) VALUES(:cust_id, :status)";
			$stmt = $database->prepare($SQL); //Sanitize statement to prevent SQL injection	
			$stmt->bindParam(':cust_id', $_SESSION['CustID']);
			$stmt->bindParam(':status', $status);
			$stmt->execute();

	//Select the new record by its ID
			$SQL = "SELECT Orders_ID FROM ht_orders WHERE Orders_Cust_ID= :cust_id AND Orders_Status = 0";
			$stmt = $database->prepare($SQL); //Sanitize statement to prevent SQL injection	
			$stmt->bindParam(':cust_id', $_SESSION['CustID']);
			$stmt->execute();

			$open_orders = $stmt->fetchColumn();
			$_SESSION['Order_ID'] = $open_orders;
		}

	}

////RESULTS PER PAGE CHECK////
//check to see if desired number of results per page changed
	$rpp = $_GET['rpp'];

	//if user changed the selector
	if ($rpp != null) {
		//reset cookie
		setCookie(rpp, $rpp, time() + (86400 * 365), "/"); // 86400 * 365 = 1 year

	//if no rpp was submitted
	} else if ($rpp == null) {
		//if the cookie is set, use that
		if (isset($_COOKIE['rpp'])) {
			$rpp = $_COOKIE['rpp'];
		} else {
		
		//default to 12
			$rpp = 12;
		}
		
	}


//////////DETERMINE PAGE NUMBER AND SET TO 1 if not set
//find out what page the user wanted
	$pageNum = $_GET['page'];
	if ($pageNum == null) {
		$pageNum = 1;
	} 

?>
<!--This is the home page for Hittle Sewing, by Owen Graham, 2015. -->

<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Welcome to J. Hittle, Wholesale Sewing Supplier</title>

	<!-- Custom CSS -->
	 <link href="css/custom.css" rel="stylesheet">



  </head>

  <body>
	<!-- HEADER AREA WITH IMAGE AND SEARCH BAR-->
	<div class="header_image">
	</div>
	<div class="search_bar">
	<div id="search_input_wrap">
		<form method="get" name="merchant_search_form" id="merchSearchForm" autocomplete="off" action="index.php"> <!-- Turn off default autocomplete that obscures the list -->
			<div class="search_bar_element">
				 <input type="text" name="searchingMerchant" id="merchantSearch" placeholder="Enter Merchant Name" class="search_input" list="merchants" />
					<div id="merchant_autocomplete_list"></div>
			</div>
		</form>				
			<div class="search_bar_element">
			<!--This div exists as a spacer between the two search bar elements -->			
			</div>
		<form method="get" name="product_search_form" id="prodSearchForm" autocomplete="off" action="index.php">
			<div class="search_bar_element">
				 <input type="text" name="searchingProduct" id="productSearch" placeholder="Enter A Product Number" class="search_input" list="product_list" />

					<div id="product_autocomplete_list"></div>
							
				</div>
				 <!-- <input type="submit" value="Search" />  Temp removed -->	
		</form>
		</div>
	</div> 
	<!-- end of search bar -->
	
	<!-- MAIN CONTENT AREA -->
	<div id="main_content">

		<div class="category_list">
			<h1 id='clicktoshow'>Categories</h1>

			<div id="accordion">
				
			<?php include "category_nav.php"; ?> <!-- This PHP file generates the left-hand nav accordion menu content -->

				
			</div>

		</div>	
		<div class="nav_and_results">
			<nav>
				<ul>
					<li><a href="index.php">Home</a></li>
					<li><a href="index.php?showCart=1">Cart</a></li>
					<li><a href="index.php?prodReqType=6&special=1">Specials</a></li>
					<li><a href="resetcookie.php">FAQ</a></li>
					<li><a href="#">Contact Us</a></li>
					<li><a href="index.php?history=1">My Orders</a></li>
				</ul>
			</nav>
			<div id="headline">
				<h1>Over 10,000 notions and sewing products!</h1>			
			</div>


<!-- PHP Code to display products in the center of the page goes in this div, "results"-->
			<div id="results">

<!-- The folowing code displays if the browser does not support JavaScript or have JS enabled-->
			<noscript><p>Unfortunately, it appears that your browser does not support JavaScript or does not have JavaScript enabled.
						You must have JavaScript enabled to use this site.</p>
				<p>If this is not possible on your computer, please contact J Hittle Sewing at 937-1503</p>
				<p>We apologize for the inconvenience</p>

			</noscript>

			<?php 

			//getvariables ready	
					$order_id = $_SESSION['Order_ID'];
					$user_id = $_SESSION['CustID'];

//Option 1: Display the cart
//

		if ($showCart == 1) {
				include "shoppingcart.php";
		} //end of display cart scripts

//Option 2: order history
		else if ($history == 1) {
			include "orderhistory.php";
		}

//Option 3: Display products (this also includes Specials and Features
	 else if ($showCart !== 1) {
			include "products_new.php";
			 
	}
?>
			</div>		

<!-- End of "results" div -->

	
		</div>

	</div>
	<div class="clear"></div>
	<!-- FOOTER AREA WITH COMPANY INFO AND CONTACT INFO -->

	<?php include "footer.php"; ?> <!-- This PHP file generates the page footer -->


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

<!-- Next chunk of code checks to see if there is predetermined content for results div-->
<!-- It uses jQuery and must appear after the jQuery library include to work -->



  </body>
</html>
