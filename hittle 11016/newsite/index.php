<?php 
//////////////////////////
// 							//
//			INDEX.PHP		//
//								//
//////////////////////////

//	This is the main page for Hittle Sewing, by Owen Graham, 2015
//	It performs several different functions, as described below:

// 0 - SESSION START and DATABASE CONNECTION

// 1 - USER IDENTIFICATION
// 	Checks to see if the user is registered and saves their ID in SESSION. 
// If there is a new user, it logs that information to the visitor log and customer email tables. 

// 2 - ORDER INFORMATION
// 	Once the user is identified, this page also checks for their open orders so that the correct
//		information will be displayed when they go to the cart. If there are multiple open orders (a 
//		situation which should never occur, but this is a backup) it sets the most recent order as the open
//		order.

// 3 - PAGINATION
//		Checks to see how many results this user prefers to see at a time, and what page number
//		should be displayed

// 4- HEADER AND NAVIGATION
// 	The page displays a custom version of the basic header with active search boxes, imports the Category list
//    via an include statement, and sets up the nav links at the top of the main results div

// 5 - RESULTS DIV
// 	 Depending on the user's actions, the page displays product search results, cart info, or "about us" 
//		 content in the main results div. The default content is Featured Products

// 6 - FOOTER
//		 The page ends with the basic footer, as well as all script imports

//////////////////////////////////////////////////////////////////////////////

// 	SECTION 0
 
//////////////////////////////////////////////////////////////////////////////

session_start();

include ("connection.php");


//Check to see if we're viewing the cart, or order history, or about page (will apply in section 5)
	$showCart = $_GET['showCart'];
	$history = $_GET['history'];
	$about = $_GET['about'];

///////////////////////////////////////////////////////////////////////////////

// 	SECTION 1: USER IDENTIFICATION 

///////////////////////////////////////////////////////////////////////////////

// Start by checking for input from POST from landing page
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

	$enter = $_POST['enter'];
	$sender =  $_POST['sender']; 
	$mailing = $_POST['mailing'];
	
	$today = date("Y-m-d");
	$visit_time = date("H:i:s");
	


// Next, check to see if the cookie is already set
// First case, if the cookie is present, and no new user email has been entered
if (($newUserEmail == null) && (isset($_COOKIE["loginEmail"]))) { 

		//Set $login to the existing cookie value
		$login = $_COOKIE["loginEmail"];			

//	Second case, new user email info is present
	} else if ($newUserEmail !== null) { 

	// We start by setting the cookie, as this needs to happen whether it is a new user or returning user without a cookie
	   //set a new Cookie loginEmail to the email that was entered and set to last one year
		setCookie(loginEmail, $newUserEmail, time() + (86400 * 365), "/"); // 86400 * 365 = 1 year

		//Set login to the userEmail so we can use it right away, instead of checking for cookie again
		$login = $newUserEmail;	

		// Next, check to see if the user exists before logging their info to the table
		$SQL = "SELECT * FROM ht_customer_email WHERE Cust_Email = :email OR Cust_Primary = :email";
		$stmt = $database->prepare($SQL);
		$stmt->bindParam(':email', $login, PDO::PARAM_STR, 100);	
		
		$stmt->execute();
		
		// count the results
		$results = $stmt->rowCount();
		
		// if they do not exist, write to table
		if ($results == 0) {


		//	Write the new user to the ht_customer_email table
		// setting this email to both the main record and primary contact info
		// Use bindParam to prevent SQL injection!

		$SQL = "INSERT INTO ht_customer_email (Cust_Email, Cust_Primary, Cust_Date) VALUES (:useremail, :userprimary, :userdate)";
		$stmt = $database->prepare($SQL); 
		$stmt->bindParam(':useremail', $login, PDO::PARAM_STR, 100);
		$stmt->bindParam(':userprimary', $login, PDO::PARAM_STR, 100);
		
		// also log the date the new user joined
		$date = date('Y-m-d');
		$stmt->bindParam(':userdate', $date);

		$stmt->execute();

		}

		// Now, write this new user to the visitor_log table as well
		
		//Check to see if this visitor has already been logged
		$SQL = "SELECT * FROM visitor_log WHERE Visitor_email = :email";
		$stmt = $database->prepare($SQL);
		$stmt->bindParam(':email', $login, PDO::PARAM_STR, 100);	
		
		$stmt->execute();		

		$email_log = $stmt->fetchColumn();

		//If they have not been logged, write to table
		if ($email_log == 0)	{
			// setup SQL statement
		$SQL = " INSERT INTO visitor_log ";
		$SQL .= " (Visitor_date,Visitor_time,Visitor_enter,Visitor_email,Visitor_casual,Visitor_craft,Visitor_seam,Visitor_small,Visitor_large,Visitor_man,Visitor_other,Visitor_discount,Visitor_wholesale) VALUES ";
		$SQL .= " (:today, :visit_time, :sender, :email, :casual, :craft, :seam, :small, :large, :man, :other, :discount, :wholesale) ";
			
			$stmt = $database->prepare($SQL);
			$stmt->bindParam(':today', $today);
			$stmt->bindParam(':visit_time', $visit_time);
			$stmt->bindParam(':sender', $sender);
			$stmt->bindParam(':email', $login);
			$stmt->bindParam(':casual', $casual);
			$stmt->bindParam(':craft', $craft);
			$stmt->bindParam(':seam', $seam);
			$stmt->bindParam(':small', $small);
			$stmt->bindParam(':large', $large);
			$stmt->bindParam(':man', $man);
			$stmt->bindParam(':other', $other);
			$stmt->bindParam(':discount', $discount);
			$stmt->bindParam(':wholesale', $wholesale);

			//write to database
			$stmt->execute();

	
			}		
		} //end of new user actions 


// Get the user's ID# using the login variable, and set to SESSION['CustID'] so we can get it from any page

		$SQL = "SELECT Cust_ID FROM ht_customer_email WHERE Cust_Email = :useremail OR Cust_Primary = :useremail";
		$stmt = $database->prepare($SQL); //Sanitize statement to prevent SQL injection	
		$stmt->bindParam(':useremail', $login, PDO::PARAM_STR, 100);	
		$stmt->execute(); 

		$_SESSION['CustID'] = $stmt->fetchColumn();



			
/////////////////////////////////////////////////////////////////////////////////

//			 SECTION 2:  ORDER IDENTIFICATION AND STATUS

////////////////////////////////////////////////////////////////////////////////



// If no user login is present, do not do anything with orders.... user will continue through site anonymously
if ($login == null) {
	$_SESSION['Order_ID'] = null;
	$_SESSION['CustID'] = null;

// Else check to see where the user came from, and if they have open orders
} else {

// Option 1 - If they returned from the checkout page by clicking "Back to Cart"
	if ($_GET['keepShopping'] == 1) {

	//reset order status to 0 = open, and order ID to the Session-stored Order Id
			$status = 0;			
			$order_id = $_SESSION['Order_ID'];

			$SQL = "UPDATE ht_orders SET Orders_Status = :status WHERE Orders_ID= :id";
			$stmt = $database->prepare($SQL); //Sanitize statement to prevent SQL injection	
			$stmt->bindParam(':id', $order_id);
			$stmt->bindParam(':status', $status);
			$stmt->execute();
	}

// Option 2 - The user arrived from somewhere else. We will check for open (unpaid) orders, and determine which one 
// is current, then set the SESSION [Order ID] accordingly.

//There are 3 possibilities: no open orders, 1 open order, or multiple open orders


//Select all open or unpaid orders for this customer
	// status 0 = current open order
	// status 1 = unpaid but user has attempted to connect to PayPal
			$SQL = "SELECT Orders_ID FROM ht_orders WHERE Orders_Cust_ID= :cust_id AND Orders_Status < 2";
			$stmt = $database->prepare($SQL); //Sanitize statement to prevent SQL injection	
			$stmt->bindParam(':cust_id', $_SESSION['CustID']);
			$stmt->execute();

			$open_orders = $stmt->fetchColumn();	

//Count the number of open orders -- this should never be other than one or zero
			$count_orders = count($open_orders);
		//convert null to 0 just in case
			if ($open_orders == null) {
				$count_orders = 0;
			}

// OPTION 1 - ONLY ONE OPEN ORDER
// set Session Order ID to the existing order ID
		if ($count_orders == 1) {
			
			$_SESSION['Order_ID'] = $open_orders;

			$order_id = $_SESSION['Order_ID'];
			$status = 0;

	//reset this order status to 0 = open - again, just in case
			$SQL = "UPDATE ht_orders SET Orders_Status = :status WHERE Orders_ID= :id";
			$stmt = $database->prepare($SQL); //Sanitize statement to prevent SQL injection	
			$stmt->bindParam(':id', $order_id);
			$stmt->bindParam(':status', $status);
			$stmt->execute();


		}
			
// OPTION 2 - MULTIPLE OPEN ORDERS
// Delete all but the most recent 
// - NOTE: this should not occur unless there are bugs in the program, this code is a just-in-case fix
// 			since multiple open orders could be a real pain to deal with

		if ($count_orders > 1) {
			
	//Select the most recent record and save it
			$SQL = "SELECT MAX(Orders_ID) FROM ht_orders WHERE Orders_Cust_ID= :cust_id AND Orders_Status < 2";
			$stmt = $database->prepare($SQL); //Sanitize statement to prevent SQL injection	
			$stmt->bindParam(':cust_id', $_SESSION['CustID']);
			$stmt->execute();
			
		//Get the ID, and set Session equal to it
			$open_orders = $stmt->fetchColumn();
			$_SESSION['Order_ID'] = $open_orders;

			$order_id = $_SESSION['Order_ID'];
			$status = 0;

		//set this order status to 0 = open
			$SQL = "UPDATE ht_orders SET Orders_Status = :status WHERE Orders_ID= :id";
			$stmt = $database->prepare($SQL); //Sanitize statement to prevent SQL injection	
			$stmt->bindParam(':id', $order_id);
			$stmt->bindParam(':status', $status);
			$stmt->execute();
	

		// Delete all other open orders for this user
			$SQL = "DELETE FROM ht_orders WHERE Orders_ID <> :good_id AND Orders_Cust_ID= :cust_id AND Orders_Status = 0";
			$stmt = $database->prepare($SQL); //Sanitize statement to prevent SQL injection	
			$stmt->bindParam(':cust_id', $_SESSION['CustID']);
			$stmt->bindParam(':good_id', $_SESSION['Order_ID']);
			$stmt->execute();
			}


// OPTION 3 - NO OPEN ORDERS
// Create a new one!
		if ($count_orders == 0) {

	//Write the new record to the database
			$status = 0;
			$SQL = "INSERT INTO ht_orders(Orders_Cust_ID, Orders_Status) VALUES(:cust_id, :status)";
			$stmt = $database->prepare($SQL); //Sanitize statement to prevent SQL injection	
			$stmt->bindParam(':cust_id', $_SESSION['CustID']);
			$stmt->bindParam(':status', $status);
			$stmt->execute();

	//Select the new record by its ID and set that ID to the Session Order ID
			$SQL = "SELECT Orders_ID FROM ht_orders WHERE Orders_Cust_ID= :cust_id AND Orders_Status = 0";
			$stmt = $database->prepare($SQL); //Sanitize statement to prevent SQL injection	
			$stmt->bindParam(':cust_id', $_SESSION['CustID']);
			$stmt->execute();

			$open_orders = $stmt->fetchColumn();
			$_SESSION['Order_ID'] = $open_orders;
		}

	}

/////////////////////////////////////////////////////////////////////////////

//  	SECTION 3: PAGINATION

//////////////////////////////////////////////////////////////////////////////

//This is a quick little check to see if the user has changed their preferred # results per page

//Check for $rpp variable
	$rpp = $_GET['rpp'];

	//if user changed the selector, i.e. $rpp has been set in the query string
	if ($rpp != null) {
		//reset results per page cookie
		setCookie(rpp, $rpp, time() + (86400 * 365), "/"); // 86400 * 365 = 1 year

	//if no change was submitted
	} else if ($rpp == null) {

		// Check for pre-exsting results cookie value and use that
		if (isset($_COOKIE['rpp'])) {
			$rpp = $_COOKIE['rpp'];

		// If no cookie has been set, default to 12 results per page
		} else {
			$rpp = 12;
		}
		
	}


// Next determine page number and default to 1 if not set

// Check for a page number value in query string, and set $pageNum
	$pageNum = $_GET['page'];
	if ($pageNum == null) {
		$pageNum = 1;
	} 


///////////////////////////////////////////////////////////////////////////////////

// 		SECTION 4: HEADER AND NAVIGATION

///////////////////////////////////////////////////////////////////////////////////

// The next section contains the HTML for the page header, navigation, and the PHP 
// include statement to bring in the Category list

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
	 <link type="text/css" href="css/custom.css" rel="stylesheet">



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
		</form>
		<?php
		//if not logged in, show log in link, else log out
		if ($login == null) {
			echo "<a id='log_link' href='landing.php'>Log In</a>";
		} else {
			echo "<a id='log_link' href='landing.php?logout=1'>Log Out</a>";
		}

		?>
		</div>
	</div> 
	<!-- end of search bar -->
	
	<!-- MAIN CONTENT AREA -->
	<div id="main_content">

		<div class="category_list">
			<h1 id='clicktoshow'>Categories</h1>
				<div id="accordion">				
				<?php include "category_nav.php"; ?> 
				<!-- This PHP file generates the left-hand nav accordion menu content -->		
				</div>
		</div>	

		<div class="nav_and_results">
			<nav>
				<ul>
					<li><a href="index.php">Home</a></li>
					<li><a href="index.php?showCart=1">Cart</a></li>
					<li><a href="index.php?prodReqType=6&special=1">Specials</a></li>
					<li><a href="index.php?about=1">About Us</a></li>
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
///////////////////////////////////////////////////////////////////////////////////

//			SECTION 5: RESULTS DIV

//////////////////////////////////////////////////////////////////////////////////

// In this section of the page, we select the correct content to display in the center
// of the page. Options include cart, order history, about us, or product displays,
// selected by variables which appear in the query string

//get basic variables ready	
		$order_id = $_SESSION['Order_ID'];
		$user_id = $_SESSION['CustID'];


//Option 1: Display the cart
		if ($showCart == 1) {
			include "shoppingcart.php";
		} 

//Option 2: order history
		else if ($history == 1) {
			include "orderhistory.php";
		}

// Option 3: About Us page
		else if ($about == 1) {
			include "about.php";
		}

//Option 4: Display products (can include search results, or Specials and Features - this is the default)
	 	else  {
			include "products_new.php";				 
		}

////////////////////////////////////////////////////////////////////////////////////////////

// 			SECTION 6: FOOTER

//////////////////////////////////////////////////////////////////////////////////////////

// The code below closes out the results div, adds the "Back to Top" button, and brings in the footer,
// as well as all JavaScript imports. 

?>
			</div>		

<!-- End of "results" div -->
<!-- floating back to top button -->
<a href="#" class="to-top-btn">Back to Top ^</a>
	
		</div>
	</div>


	<div class="clear"></div>
	<!-- FOOTER AREA WITH COMPANY INFO AND CONTACT INFO -->

	<?php include "footer.php"; ?> <!-- This PHP file generates the page footer -->


	<!-- ADDITIONAL SCRIPT IMPORTS -->
    <!-- jQuery -->
	 <!-- The following file imports the JQuery library. It must be included or the other scripts will not work -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>

	<!-- Script which handles accordion menu and results per page -->
	<script src="js/displayproducts.js"></script>

	<!-- Script for autocomplete in search boxes -->
	 <script src="js/autocomplete.js"></script>

	<!-- Script for shopping cart actions -->
	 <script src="js/addtocart.js"></script>


  </body>
</html>
