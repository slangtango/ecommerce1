<?php 
/////////////////////////////
//									//
//	 CATEGORY NAVIGATION		//
//									//
/////////////////////////////

// This file creates the category accordion menu. It is added to the index.php by means of an include.



//Include database access name, username, and password as $db, $user, and $pw
include ("connection.php");


//Compose SQL query to select by category

	$SQL = "SELECT Cat_ID, Cat_Name FROM ht_catagory ORDER BY Cat_Name ASC";

// Query database  - no need to sanitize as this is not user accessible
	$categories = $database->query($SQL);

//Begin category list
		echo "<ul>";

//Echo out each category name
	foreach ($categories as $category) {
	
//Remove underscores
			$categoryname = $category['Cat_Name'];
			$categoryname = str_replace("_", " ", $categoryname); 
//echo category name
			echo "<li><h3>". $categoryname ."</h3>";

//Within each category, find merchants and create the sub-list

			// Write SQL statement to select Merchant IDs from selected category		
			$SQL2 = "SELECT DISTINCT Mer_ID FROM ht_product_type WHERE Cat_ID = ".$category['Cat_ID'];		
				
			//Select and assign merchants in that category to an array				
			$merchants_by_id = $database->query($SQL2);
		
			//Create an array for merchant names
			$merchants_by_name = array();
		
			// now loop through merchants_by_id and add their names and ids to the by_name array
			foreach ($merchants_by_id as $merchant) {
				$merchant_id = $merchant['Mer_ID'];			
				$merchantinfo = $database->query("SELECT Mer_Name, Mer_ID FROM ht_merchant WHERE Mer_ID=".$merchant_id);									

					foreach ($merchantinfo as $info); {			
					array_push($merchants_by_name, $info);
					};	 	
			}

			// $merchants_by_name now includes both names and ids!
			
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

//Close Category list item
		echo "</li>";
	
//Close category foreach loop	
	}

//Close entire Category list
		echo "</ul>";
	
?>


