<?php 

//Merchant Search

// This is a temporary file to confirm that the manufacturer search is connecting to database.
// It should eventually be integrated with products.php


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

// Get merchant id from Merchant search box
	$merch_id = $_GET["merchantQuery"]; 


//Compose SQL query statement selecting product names and IDs
	$SQL = "SELECT Prod_Name FROM ht_product_type WHERE Mer_ID = '".$merch_id."'";


// Query database using the 'query' method for PDOs
	$merchantproducts = $database->query($SQL);


//Begin product list
	echo "<ul>";


// Populate list with products
	foreach ($merchantproducts as $product) {
		echo "<li>" . $product["Prod_Name"] . "</li>";
	} 

//End product list
		echo "</ul>";

?>
