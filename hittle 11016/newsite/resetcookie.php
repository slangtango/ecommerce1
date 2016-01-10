<?php
/////////////////////////////////
//										 //
//  J HITTLE SHOPPING CART 	 //
//										 //
/////////////////////////////////
session_start();
 
//This is the shopping cart page for Hittle Sewing, by Owen Graham, 2015

//In order to fill the cart, we have to do the following:
// 0. Connect to database
// 1. Detect the customer
// 2. Check for open orders on that customer's ID
// 3. Look up the open order ID and find the products already listed under it
// 4. Display those products in a list



/////////////////////////
// SECTION -1: COOKIE RESET FOR TESTING PURPOSES

function deleteCookie() {
	setCookie("loginEmail", "", time() -3600, "/");
	echo "cookie has been deleted";
	$login = "unknown_user";
}

if (isset($_GET['cookiekill'])) {
	deleteCookie();
}

?>

<a class="category_nav_link" href="resetcookie.php?cookiekill=true">Delete Cookie</a>

<a href="index.php#ledz.sl702268">Go to ledz.sl702268 </a>

<?php







////////////////////////////////////////////
//
//SECTION 0: INCLUDES, DATABASE CONNECTION
//
/////////////////////////////////////////////

//get session up and running so we can access current cart
// session_start();

//Include database access name, username, and password as $db, $user, and $pw
include ("./variable.php");

//Connect to database
	$host = "localhost";
	# connect to database
	$cid = mysql_connect($host,$user,$pw);

//Compose database info for PDO
	$dsn = 'mysql:dbname=chff123_devtest-15;host=localhost';

//Connect to database and create new PDO (PHP Database Object)

try {
    $database = new PDO($dsn, $user, $pw); //this replaces mysql_db_query deprecated with PHP 5.3
	
	} catch (PDOException $e) { //this code executes if there is an error 

	    echo 'Connection failed: ' . $e->getMessage(); //outputs the standard error message for the error
	}

// set error mode to throw errors
$database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


///////////////////////////////////////////////

// STEP 1 - DETECT CUSTOMER BY LOOKING FOR COOKIE

//////////////////////////////////////////////

	$login = $_COOKIE["loginEmail"];

//If no cookie is detected, set login to "unknown user"
	if (!isset($_COOKIE["loginEmail"])) {

		$login = "unknown_user"; //use this designation to refuse access to prices etc	
			
		echo "You are not logged in";
	
	} else {
		
		echo "Your user account is ".$login;
			
		}


///////////////////////////////////////////////

// STEP 2 - CHECK FOR OPEN ORDERS UNDER THIS CUSTOMER

//////////////////////////////////////////////
//Check $_SESSION for open unsaved order

	

//Get customer ID from their email
	//If new customer, add to table and go direct to new order

//Search in orders table for open orders where cust ID matches existing one
//If no open orders, create new order in the $_SESSION global
//If open order, select item IDS from that order


////////////////////////////////////////
// TEST CODE FOR TESTING PAYPAL FUNCTION
///////////////////////////////////////
?>
			<form action="payment_test.php" method="post">			
			<p>Total
				<input type="text" name="order_total">	
			</p>
			<p>
				Order ID
				<input type="text" name="order_id" value="<?php echo $_SESSION['Order_ID']; ?>">
			</p>	
			<p><input type="submit" value="Go to Checkout"></p>


