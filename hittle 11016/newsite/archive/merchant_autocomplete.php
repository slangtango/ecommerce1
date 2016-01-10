<?php 
//AUTOCOMPLETES

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

//Get the merchant name or partial name from the GEt request
	$merchantQuery = $_GET["merchantQuery"];

//Compose SQL query statement selecting Merchant names and IDs where name matches partially
	$SQL = "SELECT Mer_ID, Mer_Name FROM ht_merchant WHERE Mer_Name LIKE '" .$merchantQuery . "%'";

// Query database using the 'query' method for PDOs
	$autocomp_merchants = $database->query($SQL);

//Begin list for autocomplete
	 echo "<ul class='autocomplete_list'>";

//Echo out each merchant name as an option
	 foreach ($autocomp_merchants as $merchant) {
		$merch_id = $merchant["Mer_ID"];	 
		$link = "./merchant_search.php?merchantQuery=" .$merch_id;
		 echo "<li><a class='category_nav_link' href='$link'>".$merchant['Mer_Name']."</a></li>";
 }

//close list
	echo "</ul>";

?>
