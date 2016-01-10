<?php
// DATABASE TESTING FILE
// Use for discovering database structure

//Include database access name, username, and password as $db, $user, and $pw
include ("connection.php");

//Add columns by uncommenting this script and running it once
	//$count = 1;
	//while ($count < 2) {
		//$SQL = "ALTER TABLE ht_cart_order MODIFY COLUMN Cart_Order_Item_ID varchar(20)"; //compose statement
		//$database->query($SQL);
		//$count = 2;
//}
echo date('Y-m-d');
//Get description from table
		// $SQL = "SHOW TABLES";		
		//$SQL = "DELETE FROM ht_orders WHERE Orders_Status < 2"; //compose statement
   	//$SQL = "INSERT INTO ht_specials (Specials_Active, Specials_Priority, Specials_Group_ID,";
		//$SQL .= " Specials_Layout, Specials_Item_ID, Specials_Title, Specials_Group_Description,";
		//$SQL .= " Specials_Overflow, Specials_Item_Description, Specials_Img_ID, Specials_Special_Price)";
		//$SQL .= " VALUES (1, 2, 'plastic_hardware', 3, 's1.qrb_52k', 'Plastic Hardware', ' ',";
		//$SQL .= " ' ', 'Quick Release Buckles, 2 per package. Plastic, made in China.', 'qrb_5pk.jpg', '.49 per pack')"; 
		//$database->query($SQL);
		
		//$SQL = "DESCRIBE ht_cart_order";
	$SQL = "SELECT * FROM ht_specials WHERE Specials_Title LIKE '%Olfa%'";
		//$SQL = "UPDATE ht_specials SET Specials_Layout = 1 WHERE Specials_Layout <> 3";
		$test_result = $database->query($SQL);
		$test_results_array = $test_result->fetchAll();  //convert results to Array

	//echo "<pre>";
	var_dump($test_results_array);
	//echo "</pre>";

	//echo "<p>emails</p>";

		//$SQL = "SELECT * FROM ht_customer_email WHERE Cust_ID > 63";
		//$test_result = $database->query($SQL);
		//$test_results_array = $test_result->fetchAll();  //convert results to Array

	echo "<pre>";
	//print_r($test_results_array);
	echo "</pre>";


?>
