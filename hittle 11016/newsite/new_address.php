<?php
////////////////////////////////
//										//
//			NEW ADDRESS				//
//										//
////////////////////////////////

// This script is called by section 4 of addtocart.js. It handles adding and updating
// address information for users. It updates ht_address, and also ht_customer_email.


// Take note that all these fields receive user input data and MUST be santized vs SQL injection

//connect to database
include "connection.php";

//collect new address information from GET
	$user_id = $_GET['addy_user_id'];
	$prim_email = $_GET['addy_email'];
	$addy_fname = $_GET['addy_fname'];
	$addy_lname = $_GET['addy_lname'];
	$addy1 = $_GET['addy1'];
	$addy2 = $_GET['addy2'];
	$addy_city = $_GET['addy_city'];
	$addy_state = $_GET['addy_state'];
	$addy_zip = $_GET['addy_zip'];
	$addy_phone = $_GET['addy_phone'];


// Update primary email to input value
	$SQL = "UPDATE ht_customer_email SET Cust_Primary = :email WHERE Cust_ID = :user_id";
	$stmt = $database->prepare($SQL);
	$stmt->bindParam(':email', $prim_email);
	$stmt->bindParam(':user_id', $user_id);

	$stmt->execute();


//Check to see if this user already had a record in ht_address
	$SQL = "SELECT * FROM ht_address WHERE Address_Cust_ID = :user_id";
	$stmt = $database->prepare($SQL);
	$stmt->bindParam(':user_id', $user_id);

	$stmt->execute();
	$result = $stmt->fetchAll();


//if new address (none on file)
if ($result == null) {
	//update address table
	$SQL = "INSERT INTO ht_address (Address_Billing_Fname, Address_Billing_Lname, Address_Billing, Address_Billing_2, Address_Billing_City, Address_Billing_State, Address_Billing_Zip, Address_Billing_Phone, Address_Cust_ID)";
	$SQL .= "VALUES (:addy_fname, :addy_lname, :addy1, :addy2, :addy_city, :addy_state, :addy_zip, :addy_phone, :user_id)";
	$stmt = $database->prepare($SQL);
	$stmt->bindParam(':addy_fname', $addy_fname);
	$stmt->bindParam(':addy_lname', $addy_lname);
	$stmt->bindParam(':addy1', $addy1);
	$stmt->bindParam(':addy2', $addy2);
	$stmt->bindParam(':addy_city', $addy_city);
	$stmt->bindParam(':addy_state', $addy_state);
	$stmt->bindParam(':addy_zip', $addy_zip);
	$stmt->bindParam(':addy_phone', $addy_phone);
	$stmt->bindParam(':user_id', $user_id);

		$stmt->execute();
	
} else {

//if existing address on file, update
	$SQL = "UPDATE ht_address SET Address_Billing_Fname = :addy_fname, Address_Billing_Lname = :addy_lname, Address_Billing = :addy1, Address_Billing_2 = :addy2, Address_Billing_City = :addy_city,";
	$SQL .= " Address_Billing_State = :addy_state, Address_Billing_Zip = :addy_zip, Address_Billing_Phone = :addy_phone WHERE Address_Cust_ID = :user_id";
	$stmt = $database->prepare($SQL);
	$stmt->bindParam(':addy_fname', $addy_fname);
	$stmt->bindParam(':addy_lname', $addy_lname);
	$stmt->bindParam(':addy1', $addy1);
	$stmt->bindParam(':addy2', $addy2);
	$stmt->bindParam(':addy_city', $addy_city);
	$stmt->bindParam(':addy_state', $addy_state);
	$stmt->bindParam(':addy_zip', $addy_zip);
	$stmt->bindParam(':addy_phone', $addy_phone);
	$stmt->bindParam(':user_id', $user_id);

	$stmt->execute();
}

?>
