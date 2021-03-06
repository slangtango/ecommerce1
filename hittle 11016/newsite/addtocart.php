<?php
//////////////////////////////////////
//										 		//
// 		ADD TO CART SCRIPT (PHP)	//
//												//
//////////////////////////////////////

// This script handles the action of adding a line item to the ht_cart_order table,
// the table used to display line by line information about items in the cart, and to keep track
// of what items and quantities are associated with particular order numbers.


// This script is called by the functions in addtocart.js, which are themselves triggered
// by user actions such as clicking a button. It does not interact with the browser directly.

// It checks to see if the action is a product add, product remove, or a change of quantity, 
// and performs the appropriate action. 

//Step 1: Connect to database
include 'connection.php';


//Step 2: Get variables. All variables are submitted to this script by GET query strings,
// which are generated by the addtocart.js script. Since they are not seen in the browser,
// they should not be susceptible to user modification.
 
				$order_id = $_GET['order_id']; 	//order id #
				$user_id = $_GET['user_id']; 		// user id #
				$item_id = $_GET['id'];    		// item id #
				$entry_id = $_GET['entryID'];		// entry ID - the key for this particular line item entry in ht_cart_order, NOT order id OR item id
				$quantity = $_GET['quantity']; 	// quantity being added or changed (NA for removes)

				$remove = $_GET['remove'];			// signals a remove action		
				$update = $_GET['update'];			// signals a change qty action
				$add = $_GET['add'];					// signals an add, generates a new record in ht_cart_order


//Step 3: Proceed according to the type of action


// Removing an item
				if ($remove == 1) {
				
				//delete the line item with the corresponding entry id
				$SQL = "DELETE FROM ht_cart_order WHERE Cart_Order_Entry_ID = '".$entry_id."'";	
				$database->exec($SQL);
				}

//Change the qty on an existing line item
 				if ($update == 1) {
				
				//make the change, setting Cart_Order_Qty to the new value supplied by $quantity, on the line item with this entry id
				$SQL = "UPDATE ht_cart_order SET Cart_Order_Qty = '".$quantity."' WHERE Cart_Order_Entry_ID = '".$entry_id."'";
				$database->exec($SQL);
				}

//Adding a new line item
				else if ($add == 1) {
			
				//check to see if same item already exists in this order
				$SQL = "SELECT * FROM ht_cart_order WHERE Cart_Order_Item_ID = '".$item_id."' AND Cart_Order_ID = '".$order_id."'"; 
				$result = $database->query($SQL);

					//if it exists, do an update, adding the new qty rather than a new line item
					if ($result->rowCount() > 0) {
						$line_item = $result->fetch();
						$line_ID = $line_item['Cart_Order_Entry_ID'];				
						$prev_qty = $line_item['Cart_Order_Qty'];
						$add_qty = $quantity;
						$new_qty = $prev_qty + $add_qty;
					
					$SQL = "UPDATE ht_cart_order SET Cart_Order_Qty = '".$new_qty."' WHERE Cart_Order_Entry_ID = '".$line_ID."'";
					$database->exec($SQL);

					} else {

					//If there are no existing instances of this item in cart, make a new entry

					//check to see if it is a regular catalog item or special item
					$first = substr($item_id, 0, 1);

					//if it's a special
						if (($first == 's') OR ($first == 'f')) {

						$SQL = "SELECT Specials_List_Price FROM ht_specials WHERE Specials_Item_ID = '".$item_id."'";
						$result = $database->query($SQL);
						$result_arr = $result->fetch();

						$price = $result_arr[0];
				
						//Write new item to the cart, assigning the correct order id, user id, item id, qunatity, and price
						$SQL = "INSERT INTO ht_cart_order";
						$SQL .= "(Cart_Order_ID, Cart_Cust_ID, Cart_Order_Item_ID, Cart_Order_Qty, Cart_Order_List_Price)"; 
						$SQL .=" VALUES (:order_id, :cust_id, :item_id, :qty, :price)";
						$stmt = $database->prepare($SQL); //Sanitize statement to prevent SQL injection	
						$stmt->bindParam(':order_id', $order_id);
						$stmt->bindParam(':cust_id', $user_id);
						$stmt->bindParam(':item_id', $item_id);
						$stmt->bindParam(':qty', $quantity);
						$stmt->bindParam(':price', $price);

						$stmt->execute();


						}
	
					//else for regular catalog items
						else {
						//Start by requesting price information for this item_id from database
						$SQL = "SELECT List_Price FROM ht_item WHERE Item_ID = '".$item_id."'";
						$result = $database->query($SQL);
						$result_arr = $result->fetch();
						$price = $result_arr[0];


						//Write new item to the cart, assigning the correct order id, user id, item id, qunatity, and price
						$SQL = "INSERT INTO ht_cart_order";
						$SQL .= "(Cart_Order_ID, Cart_Cust_ID, Cart_Order_Item_ID, Cart_Order_Qty, Cart_Order_List_Price)"; 
						$SQL .=" VALUES (:order_id, :cust_id, :item_id, :qty, :price)";
						$stmt = $database->prepare($SQL); //Sanitize statement to prevent SQL injection	
						$stmt->bindParam(':order_id', $order_id);
						$stmt->bindParam(':cust_id', $user_id);
						$stmt->bindParam(':item_id', $item_id);
						$stmt->bindParam(':qty', $quantity);
						$stmt->bindParam(':price', $price);

						$stmt->execute();
		
						}
				}
			}


	//if bad info for some reason, prevent any code from running
				else {die();}
?>
