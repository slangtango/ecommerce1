<?php 

/////////////////////////////////////////
//                                     //
// THIS IS THE NEW J HITTLE SEWING     //
//  PRODUCT DISPLAY SCRIPT             //   
//                      					//
//	ADAPTED FROM THE ORIGINAL 				//
//	by John Hittle 							//
//													//	
// 2015 Owen Graham							//
/////////////////////////////////////////

// This script takes GET request inputs from hyperlinks and returns a selection of
// products to the results-display area. 

// CONTENTS

// SECTION 0: DATABASE CONNECTION, FUNCTIONS AND VARIABLES

// SECTION 1: LIST ALL PRODUCTS BY A MANUFACTURER

// SECTION 2: LIST ALL PRODUCTS IN A CATEGORY BY MANUFACTURER

// SECTION 3: DISPLAY AN INDIVIDUAL PRODUCT FROM PRODUCT SEARCH

// SECTION 4: DISPLAY COLORS AND SIZES OF A PARTICULAR PRODUCT

// SECTION 5: DISPLAY A SINGLE PRODUCT

// SECTION 6: DISPLAY SPECIALS AND FEATURE ITEMS

////////////////////////////////////////////
//
//SECTION 0: DATABASE CONNECTION
//
/////////////////////////////////////////////

include ("connection.php");



///////////////////////////////////////////////////
//
// SECTION 0.1: OTHER VARIABLES
//
///////////////////////////////////////////////////

$merch_id = "";


///////////////////////////////////////////////////
//
// SECTION 0.2: FUNCTIONS 			
//
///////////////////////////////////////////////////

//// MAKE HEADER FUNCTION /////
// This function gets the merchant name and makes a header

$make_header = function($merch_id) {
	
	

	global $mername;
		//remove underscores from mer name
		$mername = str_replace("_", " ", $mername);

// Echo the merchant name as a title for the displayed results	
		echo "<h1>".$mername[0]."</h1>";
};







//////////////////////////////
//								   ///
// MAKE LISTING FUNCTION   ///
//									///
//////////////////////////////

// This function takes the result of a database select and generates
// an HTML list of products with the appropriate data, and paginates results

$make_listing = function($results) {

// Bring in the global variables from outside the function

//database connection, raw database return, merchant name and id, user login cookie, results per page, and page number selected
	global $database, $results, $mername, $merch_id, $login, $rpp, $pageNum;




//Pagination
	$resultPages = array();
	$resultPages[] = ""; //assign empty string for page 0



	$counter = 0;
//Go through the results
	foreach ($results as $product) {

	if ($counter == 0) {

	//start new list for new page
//add pagination drop down option

	 	$thisPage = "<div id='rpp'><form>";
		$thisPage.= "<label for='rpp_select'>Showing ".$rpp." Results Per Page. Change:</label><select name='rpp_select' id='rpp_select'>";
		$thisPage.= "<option class='rpp_opt' value='' selected></option>";
		$thisPage.= "<option class='rpp_opt' value='12'>12</option>";	
		$thisPage.= "<option class='rpp_opt' value='36'>36</option>";	
		$thisPage.= "<option class='rpp_opt' value='72'>72</option></select>";	
		$thisPage.= "</form></div>";

		$thisPage .= "<ul>";
		
	} 

//Assign types and images to variables	
		$product_id = $product['Prod_ID'];
		$product_img = $product['Img_ID']; 
		$product_has_chart = $product['Item_Color_Chart_yn'];

//Get description from ac table
		$SQL = "SELECT PAC_Description from ht_product_ac WHERE PAC_Prod_Type='$product_id'"; //compose statement
		$description_result = $database->query($SQL); //get results as PDO object
		$description = $description_result->fetch();  //convert results to Array


//Echo information about this product as a complete product listing in HTML
			$thisPage .= "<li class='product_listing'>";
			$thisPage .= "<img src='http://funoverload.com/sewing/images/".$product_img."' alt='Product image'>";
			$thisPage .= "<div class='listing_text'>";
			$thisPage .= "<p>Type ID: ".$product_id."</p>";
			$thisPage .= "<p>Manufacturer: ".$mername[0]."</p>";
			$thisPage .= "<p>".$description[0]."</p>";
			$thisPage .= "</div>";

//Next, check if we are headed for Colors/Sizes or a specific product listing,
// setting the nextpage variable so landing.php will know where to take the user next		

//First case: if it has color chart		
	if ($product_has_chart == 2) {
				$nextPage = "index.php?prodReqType=4&merch=".$merch_id."&prodID=".$product_id;
				$nextPage = urlencode($nextPage);

//Second case, if it does not have a color chart
				} else { 

				//go to prodReqType 5, display single item
	
				$nextPage = "index.php?prodReqType=5&prodID=".$product_id;
				$nextPage = urlencode($nextPage);
				}

//Now that we have set $nextPage, we check to see if the user is logged in


//First case: user is not logged in, no cookie detected
if (!isset($login)) {
 		//show the More Info button and link, display "More Info..." so they must log in before proceeding
			$thisPage .= "<div class='listing_form'>";
			$thisPage .= "<a class='add_to_cart_button' href='landing.php?nextpage=".$nextPage."'>More Info...</a></div>";
			$thisPage .= "</li>";

//Second case: user is logged in. Proceed with normal listing display.
	} else {
			$thisPage .= "<div class='listing_form'>";

	//Display Select Colors/Sizes if the product has a color chart	
			if ($product_has_chart == 2) {
				$thisPage .= "<div class='form_placeholder'>";
				$thisPage .= "<a class='select_colors_button category_nav_link' href='index.php?prodReqType=4&merch=".$merch_id."&prodID=".$product_id."'>Colors / Sizes</a>";
				$thisPage .= "</div>";
	//If product does not have color chart, display "Add to Cart" button	
				} else {

			global $user_id, $order_id;
			//get Item ID
			$SQL = "SELECT Item_ID FROM ht_item WHERE Item_Prod_ID = '$product_id'";
			$results = $database->query($SQL);
			$product_item_id = $results->fetchColumn();

			$thisPage .= "<form class='atc_form' autocomplete='off'>";			
			$thisPage .= "QTY:";			
			$thisPage .= "<input type='text' name='quantity' class='quantity'>";				
			$thisPage .= "<input type='hidden' name='id' value='".$product_item_id."'>";	
			$thisPage .= "<input type='hidden' name='user_id' value='".$user_id."'>";	
			$thisPage .= "<input type='hidden' name='order_id' value='".$order_id."'>";			
			$thisPage .= "<input type='submit' class='add_to_cart add_to_cart_button' value='Add to Cart'>";	
			$thisPage .= "</form>";
			$thisPage .= "</div>";
			}
	
			$thisPage .= "</li>";			
	}		


 		if ($counter == ($rpp - 1)) {
		//End list for this page
		$thisPage .= "</ul>";
		//add this page to the array

		$resultPages[] = $thisPage;

		//reset counter
		$counter = 0;
		} else {
		//increment counter
		$counter = $counter + 1;
		}
	}

//Add last partial page of results to resultPages array
	if ($counter != 0) {
		$resultPages[] = $thisPage;
	}

//make sure we don't overshoot (if someone changes to show 72 results from last page, could get page number higher than exists)
	$numPages = count($resultPages);
	if ($pageNum > ($numPages - 1)) {
		//set to last page
		$pageNum = ($numPages - 1);	
	}

//Display selected page

	echo $resultPages[$pageNum];

	//query string gets the parameters for these search results
	$link = "index.php?" .$_SERVER['QUERY_STRING'];

	//remove previous value of page from string

	$newLink = strstr($link, "&page=", true);//strips everything after &page=
	//$newLink = strstr($newLink, "&rpp=", true);//strip page number setting
	if ($newLink != false) {//&page string was not found
	$link = $newLink;
	}
	//echo $link;



//start displaying page number nav

	echo "<div id='page_numbers'>";
	echo "Showing page ".$pageNum." of ".($numPages - 1);



//Create page links
if ($numPages > 2) {	

 echo " <div id='page_links'>";
//start with first and back
	if ($pageNum != 1) { 
	echo "<a href=".$link."&page=1>First</a>";
	} else {
	echo "First";
	}
	
	$prev = ($pageNum -1);
	if ($prev != 0) { //if not first page, give back option
 	echo "<a href=".$link."&page=".$prev.">Back</a>";
	} 

//short list
	
	if ($numPages < 10) {
	$start = 2;
	$end = ($numPages - 1);
	}
//long list
 	else {
	$start = ($pageNum - 5);
		if ($start < 1) {
			$start = 1;
		}

	$end = ($pageNum + 6);
		if ($end > ($numPages -1)) {
			$end = ($numPages -1);
		} 
	}

//do the middle
		for ($page=$start; $page < $end; $page++) {
			//if we're on the current page			
			if ($page == $pageNum) {
			echo "<span class='currentPage'>".$page."</span>";			
			} else {
			echo "<a href=".$link."&page=".$page.">".$page."</a>";
			}

		}
	


//ending
	$next = ($pageNum + 1);
	if ($pageNum != ($numPages - 1)) { //if not last page, give next and option
	  echo "<a href=".$link."&page=".$next.">Next</a>";	
		$last = ($numPages - 1);
		echo 	"<a href=".$link."&page=".$last.">Last</a>";
	} else {
		echo "Last";
	}
		
	echo "</div>";

	echo "</div>";
}

};



//////////////////////////////////////////
//
// WHAT TYPE OF REQUEST IS THIS?
//
//
//////////////////////////////////////////

//Check to see if prodReqType is already set
$prodReqType = $_GET["prodReqType"];

//if no request type, default to Featured Produts, type 6
if ($prodReqType == null) {
	$prodReqType = 6;
}


$merchant_name = $_GET["searchingMerchant"];
$searchingProducts = $_GET["searchingProduct"];

//If user searched from the Merchant search box, get merch ID and then go to section 1
 if ($merchant_name == true) {

//add underscores back in to names
	$merchant_name = str_replace(" ", "_", $merchant_name);
	
	//get ID
	$SQL = "SELECT Mer_ID FROM ht_merchant WHERE Mer_Name = :merchantName";
	$stmt = $database->prepare($SQL); //santize input to prevent SQL injection
	$stmt->bindParam(':merchantName', $merchant_name, PDO::PARAM_STR, 100);
	$stmt->execute();

	$merch_id = $stmt->fetchColumn();

	if ($merch_id == NULL) {

		echo "No results found for that manufacturer";
	}

//Direct to section 1
	$prodReqType = 1;


//If user entered data through product Search, temporarily leave alone -- nov 30 2015
} else if ($searchingProducts == true) {
	echo "Please select a valid product option from the drop down. If no drop down is showing, please try again.";
	
}

//If user searched from the Product Search box, redirect to section 5
// if (searchingProducts == true) {
//	$prodReqType = 5;
// }



////////////////////////////////////////////////////
//
// SECTION 1: LIST OF ALL PRODUCTS BY A MANUFACTURER - prodReqType 1
//
/////////////////////////////////////////////////////

// This code should run when links in Search By Manufacturer are clicked

if ($prodReqType == 1) {


// Get merchant id from Merchant search box
	if ($merch_id == "") {
		$merch_id = $_GET["merchantQuery"]; 
	} 

//Let's get the actual merchant name
// Compose SQL query statement selecting merchant name for the appropriate merchant ID
	$SQL = "SELECT Mer_Name FROM ht_merchant WHERE Mer_ID = '$merch_id'";

//Execute the statement and query the database
	$name_search_result = $database->query($SQL);
	
//Grab the name from the database result		
	$mername = $name_search_result->fetch();


$make_header($merch_id);

//Next, let's get the list of products this manufacturer makes in this category

//First match Merch_ID to a list of Product Types and associated images
	$SQL = "SELECT Prod_ID, Img_ID, Mer_ID, Item_Color_Chart_yn FROM ht_product_type WHERE Mer_ID = '$merch_id'";
	$results = $database->query($SQL);
	


//Run the make listing function to output the results as HTML list 
$make_listing($results);


}
//END SECTION 1


/////////////////////////////////////////////////////////////
//
//SECTION 2: LIST PRODUCTS BY A MANUFACTURER WITHIN A CATEGORY (prodReqType  2)
//
///////////////////////////////////////////////////////////////

//This section should be called for links in the left-hand category navigation element

if ($prodReqType == 2) {

//The GET request supplies $merch_id, (the Mer_ID), and $product_cat (the product category or Cat_ID)
	$merch_id = $_GET["merch"];
	$product_cat = $_GET["product"];

//Start by geting the actual merchant name
// Compose SQL query statement selecting merchant name for the appropriate merchant ID
	$SQL = "SELECT Mer_Name FROM ht_merchant WHERE Mer_ID = '$merch_id'";

//Execute the statement and query the database
	$name_search_result = $database->query($SQL);
	
//Grab the name from the database result and set to global variable $mername		
		$mername = $name_search_result->fetch();


$make_header($merch_id);

//Next, let's get the list of products this manufacturer makes in this category

//First match the Category ID and Merch_ID to a list of Product Types and associated images
	 $SQL = "SELECT Prod_ID, Img_ID, Item_Color_Chart_yn from ht_product_type WHERE Cat_ID = '$product_cat'AND Mer_ID = '$merch_id'";

	$results = $database->query($SQL);

//Run the make listing function to output results as HTML list
$make_listing($results);

}

// END SECTION 2



////////////////////////////////////////////////////////////////
//
//SECTION 3: DISPLAY AN INDIVIDUAL PRODUCT TYPE FROM THE PRODUCT SEARCH BOX (prodReqType 3)
//
///////////////////////////////////////////////////////////////

if ($prodReqType == 3) {
//This section should be called for links which appear in the autocomplete for Product Search

//The GET request supplies the product ID and merchant ID
	$merch_id = $_GET["merchantQuery"];
	$product_id = $_GET["productQuery"];


//Start by geting the actual merchant name
// Compose SQL query statement selecting merchant name for the appropriate merchant ID
	$SQL = "SELECT Mer_Name FROM ht_merchant WHERE Mer_ID = '$merch_id'";

//Execute the statement and query the database
	$name_search_result = $database->query($SQL);
	
//Grab the name from the database result and set to global variable $mername		
		$mername = $name_search_result->fetch();


$make_header($merch_id);

//Next, let's get the product

//First match the Product ID to a a product type and associated image
	$SQL = "SELECT Prod_ID, Img_ID, Item_Color_Chart_yn from ht_product_type WHERE Prod_ID = '$product_id'";
	$results = $database->query($SQL);
	
$make_listing($results);

}


//END SECTION 3


////////////////////////////////////////////////////
//
// SECTION 4: DISPLAY COLORS AND SIZES OF A PRODUCT - prodReqType 4
//
/////////////////////////////////////////////////////

if ($prodReqType == 4) {
	$merch_id = $_GET["merch"];
	$product_id = $_GET["prodID"];

//Let's get the actual merchant name
// Compose SQL query statement selecting merchant name for the appropriate merchant ID
	$SQL = "SELECT Mer_Name FROM ht_merchant WHERE Mer_ID = '$merch_id'";

//Execute the statement and query the database
	$name_search_result = $database->query($SQL);
	
//Grab the name from the database result		
	$mername = $name_search_result->fetch();

	$make_header($merch_id);

//Now to get the product options
	$SQL = "SELECT Prod_ID, Prod_Name, Prod_Image_yn, Prod_Header_yn, Img_ID, Item_Color_Chart_yn FROM ht_product_type WHERE Prod_ID = '$product_id' ORDER BY Prod_Name ASC";
	$result = $database->query($SQL);
	$gloop = $result->fetch(PDO::FETCH_NUM);

		$groupid = "$gloop[0]";
		$groupname = "$gloop[1]";
		$groupimgyn = "$gloop[2]";
		$groupheadyn = "$gloop[3]";
		$groupimgid = "$gloop[4]";
		$groupcolorchartyn = "$gloop[5]";

//Generate header text
	echo "<p>Please allow 10 working days for processing and shipping your order.<p>";
	echo "<p>Our Returns Policy: If the items cost are between \$0.00 and \$25.00  there is a \$10.00 restocking fee.</p><br>";
	echo "<p>To see the charges  for larger amounts, go to the features page.</p>";

//Checking for product specific header text here
	if ($groupheadyn == 2)
			{
			$SQL = "SELECT Head_Text FROM ht_header WHERE Head_ID = '$groupid'";

			//execute SQL statement
			$result = $database->query($SQL);
			$headertext = $result->fetchColumn();

			echo "<p class='old_header'>".$headertext."</p>";
		}

//Check for product image
	if ($groupimgyn == 2) {
			echo "<img src='http://funoverload.com/sewing/images/$groupimgid'>";
		}


//Now that this is complete, begin list of specific items within the product ID
//First get the items from that database

	$SQL = "SELECT Item_ID, List_Price, Description_yn, PP_Desc FROM ht_item WHERE Item_Prod_ID = '$product_id' ORDER BY Item_Number ASC";

	$result = $database->query($SQL);
	$items_array = $result->fetchAll();

//Begin the table
		echo "<table>";
		echo "<tr><th>Item Description</th><th>List Price</th></tr>";

	foreach ($items_array as $item) {
		$item_id = $item[0];
		$item_list_price = $item[1];

		//get descriptions for each product
		$SQL = "SELECT Desc_Text FROM ht_item_description WHERE Desc_Item_ID = ".$item_id;
		$result = $database->query($SQL);
		$item_description = $result->fetchColumn();		
		
			echo "<tr>";
			//echo "<td>$item_id</td>";
			echo "<td>$item_description</td>";
			echo "<td>$".$item_list_price;
			echo "<form class='atc_form' autocomplete='off'>";			
			echo "QTY:";				
			echo "<input type='text' name='quantity' class='quantity'>";				
			echo "<input type='hidden' name='id' value='".$item_id."'>";
			echo "<input type='hidden' name='user_id' value='".$user_id."'>";	
			echo "<input type='hidden' name='order_id' value='".$order_id."'>";			
			echo "<input type='submit' class='add_to_cart' value='Add to Cart'>";
			echo "</form>";			
		
			echo "<tr>";
		}	
		
//End the table
	echo "</table>";

echo "finding colors of ".$product_id." by ".$merch_id;

}

// END SECTION 4

////////////////////////////////////////////////////
//
// SECTION 5: DISPLAY A SINGLE PRODUCT - prodReqType 5
//
/////////////////////////////////////////////////////

if ($prodReqType == 5) {
//Let's get the product
	$product_id = $_GET["prodID"];


//First match the Product ID to a a product type and associated image
	$SQL = "SELECT Prod_ID, Img_ID, Item_Color_Chart_yn from ht_product_type WHERE Prod_ID = '".$product_id."'";
	$results = $database->query($SQL);

	
$make_listing($results);

}

/////////////////////////////////////////////////////
//
// SECTION 6: DISPLAY SPECIALS AND FEATURES - prodReqType 6
//
/////////////////////////////////////////////////////

if ($prodReqType == 6) {

//test for specials or features
	$special = $_GET['special'];

	if ($special == 1) {
		$selector = 's';
		}
	else {
		$selector = 'f';
	} 

//select the product groups from the specials database, selecting only the active items
	$SQL = "SELECT DISTINCT Specials_Group_ID FROM ht_specials WHERE Specials_Active = 1 AND Specials_Item_ID LIKE '".$selector."%' ORDER BY Specials_Priority ASC";
	$results = $database->query($SQL);
	$groups = $results->fetchAll();

	if ($selector == 's') {
	echo "<h1>Current Special Products</h1>";
	} else {
	echo "<h1>Featured Products!</h1>";
	}

//select each group
	foreach ($groups as $group) {
			$gID = $group[0];	
// get the group's detailed info
			$SQL = "SELECT * FROM ht_specials WHERE Specials_Group_ID = '".$gID."'";
			$results = $database->query($SQL);
			$group_details = $results->fetch(); //get the first line
//check number of items in group
	//if layout is type 1 or 2 (type 2 = type 1 with overflow text)
			if ($group_details['Specials_Layout'] < 3) {

		//check for overflow text
				if ($group_details['Specials_Layout'] == 2) {
					$has_overflow = 1;
					} else {
					$has_overflow = 0;
					}

			$SQL = "SELECT COUNT(*) FROM ht_specials WHERE Specials_Group_ID = '".$gID."'";
			$items = $database->query($SQL); 

		//begin the div
			echo "<div class='special_listing' id='".$gID."'>";
			echo "<h2>".$group_details['Specials_Title'];
			echo " Priority Lvl: ".$group_details['Specials_Priority']."</h2>";
			echo "<div class='special_content'>";
			echo "<div class='special_subsection'>";
			echo "<img src='http://jhittlesewing.funoverload.com/sewing/specials/images/".$group_details['Specials_Img_ID']."'>";
			echo "</div>";			
			echo "<div class='special_subsection'>";				
			echo "<p>Group Description: ".$group_details['Specials_Group_Description']."</p>";
			echo "</div>";
			echo "<div class='special_subsection'>";

//if more than one, do a multi line item result
			if ($items->fetchColumn() > 1 ) {
				echo "multi item result";

	//next see if we have multiple sizes
			$SQL = "SELECT DISTINCT Specials_Size FROM ht_specials WHERE Specials_Group_ID = '".$gID."'";
			$sizes = $database->query($SQL);
			$sizes_array = $sizes->fetchAll(); 
			//var_dump($sizes_array);

	//loop through sizes
		foreach ($sizes_array as $size) {
				$size_id = $size[0]; //need for later in colors selector

			//get full details
 			$SQL = "SELECT * FROM ht_specials WHERE Specials_Group_ID = '".$gID."' AND Specials_Size = '".$size_id."'";
			$size_results = $database->query($SQL);
			$size_details = $size_results->fetchAll();//gets first line only
			//echo sublistings						
				$first = $size_details[0];
				//determine price
						//compare special, retail, wholesale, and net prices
						$price_options = array($first[14], $first[15], $first[16], $first[17]);
				
						arsort($price_options);


						$strip_zeros = function ($var) {
							return ($var > 0);
						};
						$prices = array_filter($price_options, $strip_zeros);
						

						echo "<div class='sublisting'><p>".$first['Specials_Size']." costs $".$first['Specials_Wholesale_Price']."</p>";
				//start add to cart form
						echo "<form class='atc_form' autocomplete='off'>";		
						var_dump($prices);
			
				//if multiple colors, add select element
				if ($size_results->rowCount() > 1 ) {
						echo "<select name='id'>";			
						foreach ($size_details as $color) {
							$color_id = $color['Specials_Item_ID'];
						echo "<option value='".$color_id."'>".$color['Specials_Item_Description']."</option>";
						}
					
						echo "</select>";
					} 

				else {
					echo "<input type='hidden' name='id' value='".$first['Specials_Item_ID']."'>";
					}

				//finish the add to cart form
						echo "QTY:";			
						echo "<input type='text' name='quantity' class='quantity'>";				
	
						echo "<input type='hidden' name='user_id' value='".$user_id."'>";	
						echo "<input type='hidden' name='order_id' value='".$order_id."'>";			
						echo "<input type='submit' class='add_to_cart add_to_cart_button' value='Add to Cart'>";	
						echo "</form>";	
						echo "</div>";		
					
				}
				
			//end the div	
				echo "</div>";		
				echo "</div>";	
				echo "</div>";
				if ($has_overflow == 1) {
					echo "<div class='specials_overflow'>";
					echo $group_details['Specials_Overflow'];
					echo "</div>";
				}



		//single item results run this code
			} else {
				echo "single item result";
				$SQL = "SELECT * FROM ht_specials WHERE Specials_Group_ID = '".$gID."'";
				$item_details = $database->query($SQL);
				$item_array = $item_details->fetch(); 								
						

				//add to cart button
						echo "<form class='atc_form' autocomplete='off'>";			
						echo "QTY:";			
						echo "<input type='text' name='quantity' class='quantity'>";				
						echo "<input type='hidden' name='id' value='".$item_array['Specials_Item_ID']."'>";	
						echo "<input type='hidden' name='user_id' value='".$user_id."'>";	
						echo "<input type='hidden' name='order_id' value='".$order_id."'>";			
						echo "<input type='submit' class='add_to_cart add_to_cart_button' value='Add to Cart'>";	
						echo "</form>";
				echo "</div>";
				echo "</div>";


				echo "</div>";
				if ($has_overflow == 1) {
					echo "<div class='specials_overflow'>";
					echo $group_details['Specials_Overflow'];
					echo "</div>";
				}
			}
	}
	 //end layout style 1
	
	
	if ($group_details['Specials_Layout'] == 3) {

//begin the div
			echo "<div class='special_listing'>";
			echo "<h2>".$group_details['Specials_Title'];
			echo " Priority Lvl: ".$group_details['Specials_Priority']."</h2>";
			echo "<div class='special_content'>";
// select the individual items within
			$SQL = "SELECT * FROM ht_specials WHERE Specials_Group_ID = '".$gID."'";
			$results = $database->query($SQL);

//echo the images
			foreach ($results as $item) {
			echo "<div class='special_subsection'>";
			echo "<p>".$item['Specials_Item_Description']."</p>";
			echo "<img src='http://jhittlesewing.funoverload.com/sewing/specials/images/".$item['Specials_Img_ID']."'>";			
			echo "</div>";
			}
//end the row
			echo "</div>";
//start new row
			echo "<div class='special_content'>";

			$results = $database->query($SQL);

			foreach ($results as $item2) {	
			echo "<div class='special_subsection'>";	
			echo "<div class='sublisting'>";
							//add to cart button
			echo "<form class='atc_form' autocomplete='off'>";			
			echo "QTY:";			
			echo "<input type='text' name='quantity' class='quantity'>";				
			echo "<input type='hidden' name='id' value='".$item2['Specials_Item_ID']."'>";	
			echo "<input type='hidden' name='user_id' value='".$user_id."'>";	
			echo "<input type='hidden' name='order_id' value='".$order_id."'>";			
			echo "<input type='submit' class='add_to_cart add_to_cart_button' value='Add to Cart'>";	
			echo "</form>";
			echo "</div>";
			echo "</div>";
		}
		echo "</div></div>"; //close out content and listing divs
	} //end layout style 3

}

} 


?>
