<?php 
//AUTOCOMPLETE
//This file connects to the database and returns an HTML list of links based on user entered text

//DATABASE CONNECTION AND REQUEST TYPE SETUP //

$requestType = 0;

//Include database access name, username, and password as $db, $user, and $pw
include "connection.php";

//Find out whether we are doing a Merchant Search Autocomplete or a Product Search Autocomplete
$requestType = $_GET["requestType"];


// ******************** //
// FOR PRODUCT SEARCHES //


if ($requestType == 1) {

// get the name that the user typed into the input box
$productQuery = $_GET["productQuery"];

//Add % signs for SQL LIKE query
$productQuery = "%".$productQuery."%";

//Compose SQL query statement selecting product names and IDs
	$SQL = "SELECT PAC_ID, PAC_Prod_Type, PAC_Merchant, PAC_Description FROM ht_product_ac WHERE PAC_Description LIKE :productQuery" ;
	$stmt = $database->prepare($SQL); //santize input to prevent SQL injection
	$stmt->bindParam(':productQuery', $productQuery, PDO::PARAM_STR, 30);
	$stmt->execute();
	
// get query results as an array using fetchAll
	$autocomp_products = $stmt->fetchAll();

//Begin list
	 echo "<ul class='autocomplete_list'>";

//Echo out each product name as an option
	$count = 1;	 
	foreach ($autocomp_products as $product) {
		$merch_id = $product['PAC_Merchant'];
		$product_id = $product['PAC_Prod_Type'];

		$prod_desc = $product['PAC_Description'];

		$link = "./index.php?prodReqType=3&merchantQuery=".$merch_id."&productQuery=" .$product_id;   
		
		echo "<a  class='category_nav_link autocomp_link' id='$prod_desc' href='$link'><li>" . $prod_desc . "</li></a>";
		
		//make sure autocomplete list is not longer than 20 items
		if ($count==20) {		
		break;
		} else {
		$count = $count+1;		
		} 

}

//close datalist
	 echo "</ul>";
}



// ******************** //
//FOR MERCHANT SEARCHES //

if ($requestType == 2) {

//Get the merchant name or partial name from the GET request
	$merchantQuery = $_GET["merchantQuery"];

//Add % signs for the SQL LIKE query
	$merchantQuery = "%".$merchantQuery."%";

//Compose SQL query statement selecting Merchant names and IDs where name matches partially
	$SQL = "SELECT Mer_ID, Mer_Name FROM ht_merchant WHERE Mer_Name LIKE :merchantQuery";
	$stmt = $database->prepare($SQL); //santize input to prevent SQL injection
	$stmt->bindParam(':merchantQuery', $merchantQuery, PDO::PARAM_STR, 30);
	$stmt->execute();
	
// Get query results as an array using fetchAll
	$autocomp_merchants = $stmt->fetchAll();


//Begin list for autocomplete
	 echo "<ul class='autocomplete_list'>";

//Echo out each merchant name as an option
	 foreach ($autocomp_merchants as $merchant) {
		$merch_id = $merchant["Mer_ID"];	 
		$merch_name = $merchant["Mer_Name"];
//Remove underscores from merch name
		$merch_name = str_replace("_", " ", $merch_name); 

		$link = "./index.php?prodReqType=1&merchantQuery=" .$merch_id;   
			//prodReqType directs to the correct part of products_new.php
		 echo "<a class='category_nav_link autocomp_link' id='$merch_name' href='$link'><li>".$merch_name."</li></a>";
 }

//close list
	echo "</ul>";


}


// ******************** //
//FOR QUICK ADD AUTOCOMPLETE SEARCHES //

if ($requestType == 3) {

//Get the merchant name or partial name from the GET request
	$productQuery = $_GET["productQuery"];

//Add % signs for the SQL LIKE query
	$productQuery = "%".$productQuery."%";

//Compose SQL query statement selecting Merchant names and IDs where name matches partially
	$SQL = "SELECT Cart_Item_Number, Cart_Description FROM ht_cart_ac WHERE Cart_Description LIKE :productQuery";
	$stmt = $database->prepare($SQL); //santize input to prevent SQL injection
	$stmt->bindParam(':productQuery', $productQuery, PDO::PARAM_STR, 30);
	$stmt->execute();
	
// Get query results as an array using fetchAll
	$autocomp_quick = $stmt->fetchAll();

	$count = 0;
//Begin list for autocomplete
	 echo "<ul id='qa_ac_list' class='autocomplete_list'>";

//Echo out each merchant name as an option
	 foreach ($autocomp_quick as $item) {

		 echo "<a class='quick_add_ac_link' id='".$item['Cart_Item_Number']."and".$item['Cart_Description']."'><li>";
		 echo $item['Cart_Description']."</li></a>";
 
		//make sure autocomplete list is not longer than 20 items
		if ($count==20) {		
		break;
		} else {
		$count = $count+1;		
		} 


}

//close list
	echo "</ul>";


}

?>
