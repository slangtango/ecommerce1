<?php
//Payment Confirmation
//
//This page receives the IPN (Instant Payment Notifcation) message
// from Paypal, responds to PayPal, and then receives the final
// VALIDATED message from PayPal confirming that the transaction
// is complete. IPN will attempt to send the message for up to
// 4 days until a response is received.

//Once this code has been triggered, we can write to the database
// that the order in question has been paid.


// tell PHP to log errors to ipn_errors.log in this directory
ini_set('log_errors', true);
ini_set('error_log', dirname(__FILE__).'/ipn_errors.log');


//Connect to database

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






// intantiate the IPN listener
include('ipnlistener.php');
$listener = new IpnListener();

// tell the IPN listener to use the PayPal test sandbox
$listener->use_sandbox = true;

//line added to remove bug "cURL 77 error setting certificate verify locations"
$listener->use_curl = false;

// try to process the IPN POST
try {
    $listener->requirePostMethod();
    $verified = $listener->processIpn();
} catch (Exception $e) {
    error_log($e->getMessage());
    exit(0);
}



// Handle IPN Response here
if ($verified) {

//sets email address for error messages
    mail('owenxgraham@gmail.com', 'Valid IPN', $listener->getTextReport());


//Test the IPN message for various fraud issues and / or duplication, then store data if good
		
//Get order ID as key
		$order = $_POST['item_name1'];
//break out into an array on spaces (because item name includes JHS Order # as well as the actual #)
		$order = explode(" ", $order);
//select the element that contains the number
		$order_id = $order[3];

//Get applicable info from database
			$SQL = "SELECT * FROM ht_orders WHERE Orders_ID = :order_id";
			$stmt = $database->prepare($SQL); //Sanitize statement to prevent SQL injection	
			$stmt->bindParam(':order_id', $order_id);
			$stmt->execute();			

			$order_record = $stmt->fetch();
			$order_total =  $order_record[2];
			$order_txn_id = $order_record[4];

//Open error message and perform fraud checks
    $errmsg = '';   // stores errors from fraud checks
    
    // 1. Make sure the payment status is "Completed" 
    if ($_POST['payment_status'] != 'Completed') { 
        // simply ignore any IPN that is not completed
        $errmsg .= "Payment transaction was not completed: "; 
		  $errmsg .= $_POST['payment_status']."\n";
    }

    // 2. Make sure seller email matches your primary account email.
    if ($_POST['receiver_email'] != 'hittletest@test.com') {
        $errmsg .= "'receiver_email' does not match: ";
        $errmsg .= $_POST['receiver_email']."\n";
    }
    
    // 3. Make sure the amount(s) paid match
    if ($_POST['mc_gross'] != $order_total) {
        $errmsg .= "'mc_gross' does not match: ";
		  $errmsg .= "Customer paid:".$_POST['mc_gross']."\n"; 
        $errmsg .= "Should have been: ".$order_total."\n";    
		}


	// 4. Ensure the transaction is not a duplicate.
		//Compare the txn_id to the one in the database

	//get PayPal txn id from POST message
	 if ($order_txn_id != null) {
        $errmsg .= "'Attempt to duplicate payment. Existing id: ".$order_txn_id;
        $errmsg .= $order_total."\n";    
		}

//If an error message has been generated, send Fraud Warning email
    
   if (!empty($errmsg)) {
    
        // manually investigate errors from the fraud checking
        $body = "IPN failed fraud checks: \n$errmsg\n\n";
        $body .= $listener->getTextReport();
       mail('owenxgraham@gmail.com', 'IPN Fraud Warning', $body);

//update order status to 9, failed transaction
			 $order_date = date('Y-m-d');

		$SQL = "UPDATE ht_orders SET Orders_Status = 9, Orders_PP_Txn_ID = :txn, Orders_Date_Submit = :date WHERE Orders_ID = :id";
		$stmt = $database->prepare($SQL); //Sanitize statement to prevent SQL injection	
		$stmt->bindParam(':id', $order_id);
		$stmt->bindParam(':date', $order_date);
		$stmt->bindParam(':txn', $_POST['txn_id']);

		$stmt->execute();


        
    } else {


///////////////////VALID ORDERS//////////////////    
//If no error has been generated, begin writing to database

    $payer_email = $_POST['payer_email'];
    $mc_gross = $_POST['mc_gross'];
	 $order_date = date('Y-m-d');

	//change order status to complete
		$SQL = "UPDATE ht_orders SET Orders_Status = 2, Orders_PP_Txn_ID = :txn, Orders_Date_Submit = :date WHERE Orders_ID = :id";
		$stmt = $database->prepare($SQL); //Sanitize statement to prevent SQL injection	
		$stmt->bindParam(':id', $order_id);
		$stmt->bindParam(':date', $order_date);
		$stmt->bindParam(':txn', $_POST['txn_id']);

		$stmt->execute();

  
    
    // send user an email with a link to their digital download
    $to = filter_var($payer_email, FILTER_SANITIZE_EMAIL);
    mail($to, "Thank you for your order", "Your order payment has been processed");
    
    //




    }
    
} else {
    // manually investigate the invalid IPN
    mail('owenxgraham@gmail.com', 'Invalid IPN', $listener->getTextReport());
}



?>
