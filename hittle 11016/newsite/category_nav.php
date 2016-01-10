<?php 
//CATEGORY NAVIGATION
// This file, when included, populates the div with a category navigation



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


//Compose SQL query statement
	$SQL = "SELECT Cat_ID, Cat_Name FROM ht_catagory ORDER BY Cat_Name ASC";



// Query database using the 'query' method for PDOs
	$categories = $database->query($SQL);

//Begin echoing category list to screen
		echo "<ul>";

//Echo out each category name
	foreach ($categories as $category) {
	
//Remove underscores
			$categoryname = $category['Cat_Name'];
			$categoryname = str_replace("_", " ", $categoryname); 
//echo category name
			echo "<li><h3>". $categoryname ."</h3>";

	//Within each category name, find merchants
			// Write SQL statement to select Merchant IDs from selected category		
			$SQL2 = "SELECT DISTINCT Mer_ID FROM ht_product_type WHERE Cat_ID = ".$category['Cat_ID'];		
				
			//Select and assign merchants in that category to an array	
			
			$merchants_by_id = $database->query($SQL2);
		
			//Get names from ht_merchant table and match to IDs

			$merchants_by_name = array();
		
			foreach ($merchants_by_id as $merchant) {
				$merchant_id = $merchant['Mer_ID'];			
				$merchantinfo = $database->query("SELECT Mer_Name, Mer_ID FROM ht_merchant WHERE Mer_ID=".$merchant_id);									

					foreach ($merchantinfo as $info); {			
					array_push($merchants_by_name, $info);
					};	 	
			}

			// $merchants_by_name now includes both names and ids
			
	//Alphabetize merchant list		
			sort($merchants_by_name);
		
	//Go through $merchants_by_name and begin echoing to the screen		
			//Begin merchant list
			echo "<ul>";

			foreach($merchants_by_name as $merchant_name) {
					//create a hyperlink to connect to products.php
					$merch_id = $merchant_name['Mer_ID'];
					$merch_name = $merchant_name['Mer_Name'];

					//remove underscores from mer name
					$merch_name = str_replace("_", " ", $merch_name);

					$product_cat = $category['Cat_ID'];
					$link = "./index.php?prodReqType=2&merch=".$merch_id."&product=".$product_cat."";					
					echo "<li><a class='category_nav_link' href='".$link."'>".$merch_name . "</a></li>";	
				}
			
			//Close merchant list
			echo "</ul>";

//Close Category item
		echo "</li>";
	
//Close category foreach loop	
	}

//Close entire Category list
		echo "</ul>";
	
?>


