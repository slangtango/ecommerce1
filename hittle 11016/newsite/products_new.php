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
// products to the results-display area. It is included inside index.php, and can access variables
// from that page.

// All product listings except specials and features are created by making a query to the database
// and calling the "make_listing" function on them. Specials and Features have their own custom function
// in Section 6. Products with Colors and Size charts have their own special display script in Section 4.

// There is also a short "make header" function that creates the listing header.

// In the query string, the prodReqType variable determines which section is run, type 1 calling section 1, 
// type 2 section 2, and so on. If no value is supplied for this variable, the script defaults to 
// type 6 and displays Featured Products as the default.

// Pagination works by creating an array of pages, and gradually building each page as a long HTML
// string, occupying one spot in the array. 
// The pagination sub-function in Make_Listing (as well as the Specials and Features) then
// displays content by echoing the string.

// The Make_listing function also checks to see if the user is logged in, and displays "More Info" buttons
// in place of purchase buttons if they are not. For this purpose, a $nextPage variable will be generated
// so the site knows where to return the user after login.


// CONTENTS

// SECTION 0: DATABASE CONNECTION

// SECTION 0.x: FUNCTIONS AND REQUEST TYPE CHECK

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

// For each listing, we will query the database for additional data, set the $nextPage variable in case
// they are not logged in, and add the product listing to the page, $thisPage. When $thisPage has reached a 
// certain number of listings, it is added to $resultPages array. 

// Then the correct page is displayed, and last, the pagination links are set up and displayed.

$make_listing = function($results) {

// Bring in the global variables from outside the function

//database connection, raw database return, merchant name and id, user login cookie, results per page, and page number selected
	global $database, $results, $mername, $merch_id, $login, $rpp, $pageNum;




//Pagination
	// create the empty array of pages
	$resultPages = array();
	$resultPages[] = ""; //assign empty string for page 0 - this way page 1 will correspond to spot 1


  // start the counter.... this keeps track of how many products have been added, so we know when to
  // start a new page
	$counter = 0;

// Begin the loop. $results will be the results of the product search, and each $product is a particular
// product from the database - each time through the loop adds one product listing.
	foreach ($results as $product) {

// If we're at the beginning of a page, start a new $thisPage, insert the 'Results Per Page' selector, 
// and start a new <ul>
	if ($counter == 0) {
	 	$thisPage = "<div id='rpp'><form>";
		$thisPage.= "<label for='rpp_select'>Showing ".$rpp." Results Per Page. Change:</label><select name='rpp_select' id='rpp_select'>";
		$thisPage.= "<option class='rpp_opt' value='' selected></option>";
		$thisPage.= "<option class='rpp_opt' value='12'>12</option>";	
		$thisPage.= "<option class='rpp_opt' value='36'>36</option>";	
		$thisPage.= "<option class='rpp_opt' value='72'>72</option></select>";	
		$thisPage.= "</form></div>";

		$thisPage .= "<ul>";		
	} 

//Assign available data to variables
		$product_id = $product['Prod_ID'];
		$product_img = $product['Img_ID']; 
		$product_has_chart = $product['Item_Color_Chart_yn'];

//Get the product's description from the autocomplete table
		$SQL = "SELECT PAC_Description from ht_product_ac WHERE PAC_Prod_Type='$product_id'"; //compose statement
		$description_result = $database->query($SQL); //get results as PDO object
		$description = $description_result->fetch();  //convert results to Array


//Begin adding the product listing to $thisPage - 
			$thisPage .= "<li class='product_listing'>";
			$thisPage .= "<img src='http://funoverload.com/sewing/images/".$product_img."' alt='Product image'>";
			$thisPage .= "<div class='listing_text'>";
			$thisPage .= "<p>Type ID: ".$product_id."</p>";
			$thisPage .= "<p>Manufacturer: ".$mername[0]."</p>";
			$thisPage .= "<p>".$description[0]."</p>";
			$thisPage .= "</div>";

//Next, we create the $nextPage variable to use in case the user is not logged in and has to be redirected.
// To do this correctly, we also have to check if it has a colors / sizes options page.		

//First case: if it has colors and sizes
	if ($product_has_chart == 1) {
				$nextPage = "index.php?prodReqType=4&merch=".$merch_id."&prodID=".$product_id;
				$nextPage = urlencode($nextPage);

//Second case, if it does not have colors and sizes
				} else { 

				//go to prodReqType 5, display single item
				$nextPage = "index.php?prodReqType=5&prodID=".$product_id;
				$nextPage = urlencode($nextPage);
				}

//Now that we have set $nextPage, we check to see if the user is logged in


//First case: user is not logged in
if (!isset($login)) {
 		//show the More Info button and link, display "More Info..." so they must log in before proceeding
		// pass the $nextPage through the query string so landing page knows where to send us back to
			$thisPage .= "<div class='listing_form'>";
			$thisPage .= "<a class='add_to_cart_button' href='landing.php?nextpage=".$nextPage."'>More Info...</a></div>";
			$thisPage .= "</li>";

//Second case: user is logged in. Proceed with normal listing display. Listings with colors and sizes
// will have a link to a page with options for those, while listings without will have the 'add to cart' button.
	} else {
			// this div contains the button and/or qty field
			$thisPage .= "<div class='listing_form'>";

	//Display Select Colors/Sizes if the product has a color chart	
			if ($product_has_chart == 1) {
				$thisPage .= "<div class='form_placeholder'>";
				$thisPage .= "<a class='select_colors_button category_nav_link' href='index.php?prodReqType=4&merch=".$merch_id."&prodID=".$product_id."'>Colors, Sizes & Prices</a>";
				$thisPage .= "</div>";
	//If product does not have color chart, display "Add to Cart" button	
				} else {

			//pull in the user id and order id variables - these are set at the top of index.php, and will be
			// available to us because this page is included within that page

			global $user_id, $order_id;
			//get Item ID, so we know what exactly is being added to the cart
			$SQL = "SELECT Item_ID, List_Price FROM ht_item WHERE Item_Prod_ID = '$product_id'";
			$results = $database->query($SQL);
			$item_info = $results->fetch();
			$product_item_id = $item_info['Item_ID'];
			$price = $item_info['List_Price'];
		
			$thisPage .= "<p>Price: $".$price."</p>";
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
	// end this listing
			$thisPage .= "</li>";			
	}		

// Before ending the foreach loop and proceeding to the next product, we need to check and see if we have
// reached the end of the page. This depends on the number of results per page preferred.

 		if ($counter == ($rpp - 1)) { // -1 accounts for arrays starting at 0
		//End list for this page
		$thisPage .= "</ul>";

		//add this page to the array
		$resultPages[] = $thisPage;

		//reset counter, so the loop knows to start a new page next time
		$counter = 0;
		} else {
		//increment counter, so we count up 1 more product
		$counter = $counter + 1;
		}

//end the foreach loop. Code will now proceed to run loop again with next product.
	} 


//Add last partial page of results to resultPages array
	if ($counter != 0) {
		$resultPages[] = $thisPage;
	}

// Now that the results page array is complete, we need to determine which page to display, display it,
// and add the page selectors at the bottom

// The next few lines correct a bug - if user changes Results Per Page so that their current page does not exist,
// they are directed to the last page

// the $pageNum variable is set at the top of index.php
	$numPages = count($resultPages);
	if ($pageNum > ($numPages - 1)) {
		//set to last page
		$pageNum = ($numPages - 1);	
	}

// Display selected page!
	echo $resultPages[$pageNum];

// Last, we set up page number links. To start with, we need a basic link that includes the query
// parameters for these results
	$link = "index.php?" .$_SERVER['QUERY_STRING'];

	// We have to strip any &page= from the string, otherwise it will reset to same page every time
	$newLink = strstr($link, "&page=", true); //strips everything after &page=

	if ($newLink != false) { //&page string was not found
	$link = $newLink;
	}


// Start displaying page number link navigation

	echo "<div id='page_numbers'>";
	echo "Showing page ".$pageNum." of ".($numPages - 1);


//Create page links


if ($numPages > 2) {	// really means one page, since page 0 contains nothing

 echo " <div id='page_links'>";

//start with 'First' and 'Back' links
	// First and Back should not be clickable if we are on the first page
	if ($pageNum != 1) { 
	echo "<a href=".$link."&page=1>First</a>";
	} else {
	echo "First";
	}
	
	$prev = ($pageNum -1);
	if ($prev != 0) { //if not first page, give back option
 	echo "<a href=".$link."&page=".$prev.">Back</a>";
	} 

// If less than 10 pages, just show all the links
	
	if ($numPages < 10) {
	$start = 2;
	$end = ($numPages - 1);
	}

// If there are more than 10 pages, show five forward and five back
 	else {
	$start = ($pageNum - 5);
		// make sure there are no links less than page 1
		if ($start < 1) {
			$start = 1;
		}

	$end = ($pageNum + 6);
		// make sure no links for page numbers higher than exist
		if ($end > ($numPages -1)) {
			$end = ($numPages -1);
		} 
	}

// Loop through the selected range of pages, and display a clickable link for all but the current page
		for ($page=$start; $page < $end; $page++) {
			
			if ($page == $pageNum) {
			echo "<span class='currentPage'>".$page."</span>";			
			} else {
			echo "<a href=".$link."&page=".$page.">".$page."</a>";
			}

		}
	


// Next and Last links at the end of the page nav
	$next = ($pageNum + 1);
	// check to see if we are at last page, if not give Next and Last as options
	if ($pageNum != ($numPages - 1)) { 
	  echo "<a href=".$link."&page=".$next.">Next</a>";	
		$last = ($numPages - 1);
		echo 	"<a href=".$link."&page=".$last.">Last</a>";
	} else {
		echo "Last";
	}
		
	echo "</div>";


}

	echo "</div>";
};



//////////////////////////////////////////////////////////////////////////
//
// WHAT TYPE OF REQUEST IS THIS?
//
//
/////////////////////////////////////////////////////////////////////////

//Check to see if prodReqType is already set
$prodReqType = $_GET["prodReqType"];

//if no request type, default to Featured Products, type 6
if ($prodReqType == null) {
	$prodReqType = 6;
}

// Check to see if anything was entered in the search boxes
$merchant_name = $_GET["searchingMerchant"];
$searchingProducts = $_GET["searchingProduct"];

//If user searched from the Merchant search box, get merch ID and then go to section 1, Req By Merchant
 if ($merchant_name == true) {

//add underscores back in to names so they match database content
	$merchant_name = str_replace(" ", "_", $merchant_name);
	
	//get ID from database
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


//If user entered data through product Search, prompt them to use the autocomplete links instead
} else if ($searchingProducts == true) {
	echo "Please select a valid product option from the drop down. If no drop down is showing, please try again.";
	
}






////////////////////////////////////////////////////////////////////////////////
//
// SECTION 1: LIST OF ALL PRODUCTS BY A MANUFACTURER - prodReqType 1
//
///////////////////////////////////////////////////////////////////////////////

// This code should run when links in Search By Manufacturer Autocomplete are clicked

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

// Make the page header
$make_header($merch_id);

//Next, let's get the list of products this manufacturer makes in this category

//First match Merch_ID to a list of Product Types and associated images
	$SQL = "SELECT Prod_ID, Img_ID, Mer_ID, Item_Color_Chart_yn FROM ht_product_type WHERE Mer_ID = '$merch_id'";
	$results = $database->query($SQL);
	


//Run the make listing function to output the results as a series of result pages with nav 
$make_listing($results);


}
//END SECTION 1


/////////////////////////////////////////////////////////////////////////////////////
//
// SECTION 2: LIST PRODUCTS BY A MANUFACTURER WITHIN A CATEGORY (prodReqType  2)
//
///////////////////////////////////////////////////////////////////////////////////////

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

// Make the page header
$make_header($merch_id);

//Next, let's get the list of products this manufacturer makes in this category

//First match the Category ID and Merch_ID to a list of Product Types and associated images
	 $SQL = "SELECT Prod_ID, Img_ID, Item_Color_Chart_yn from ht_product_type WHERE Cat_ID = '$product_cat'AND Mer_ID = '$merch_id'";

	$results = $database->query($SQL);

//Run the make listing function to output results in a paginated list
$make_listing($results);

}

// END SECTION 2



/////////////////////////////////////////////////////////////////////////////////////////
//
//SECTION 3: DISPLAY AN INDIVIDUAL PRODUCT TYPE FROM THE PRODUCT SEARCH BOX (prodReqType 3)
//
/////////////////////////////////////////////////////////////////////////////////////////

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

// make the page header
$make_header($merch_id);

//Next, let's get the product

//First match the Product ID to a a product type and associated image
	$SQL = "SELECT Prod_ID, Img_ID, Item_Color_Chart_yn from ht_product_type WHERE Prod_ID = '$product_id'";
	$results = $database->query($SQL);
	
$make_listing($results);

}


//END SECTION 3


/////////////////////////////////////////////////////////////////////////////////////
//
// SECTION 4: DISPLAY COLORS AND SIZES OF A PRODUCT - prodReqType 4
//
/////////////////////////////////////////////////////////////////////////////////////

// If the user clicks a Colors / Sizes button, this code runs to display those options. 
// We start by getting basic product info from the header table and product type
// table, then request specific item listings from the ht_item table. These will be echoed out in a table
// format along with their Add to Cart forms.

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
// Make the page header
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
	echo "<p>Click <a href='index.php?about=1'>here</a> to view our returns policy.</p>";

//Checking for product specific header text from the header table here
	if ($groupheadyn == 2)
			{
			$SQL = "SELECT Head_Text FROM ht_header WHERE Head_ID = '$groupid'";

			//execute SQL statement
			$result = $database->query($SQL);
			$headertext = $result->fetchColumn();

			echo "<p class='old_header'>".$headertext."</p>";
		}

// Check for product image
	if ($groupimgyn == 2) {
			echo "<img src='http://funoverload.com/sewing/images/$groupimgid'>";
		}


// Now that this is complete, begin list of specific items within the product ID - 
// these are the individual color and size options
// First get the items from that database

	$SQL = "SELECT Item_ID, Item_Number, List_Price, Description_yn, PP_Desc FROM ht_item WHERE Item_Prod_ID = '$product_id' ORDER BY Item_Number ASC";

	$result = $database->query($SQL);
	$items_array = $result->fetchAll();

//Begin the table
		echo "<table>";
		echo "<tr><th>Item Description</th><th>List Price</th></tr>";

	foreach ($items_array as $item) {
		$item_id = $item[0];
		$item_no = $item[1];
		$item_list_price = $item[2];

		//get descriptions for each product
		$SQL = "SELECT Desc_Text FROM ht_item_description WHERE Desc_Item_ID = ".$item_id;
		$result = $database->query($SQL);
		$item_description = $result->fetchColumn();		
		
			echo "<tr>";
			echo "<td>Item No: $item_no<br>$item_description</td>";
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

}

// END SECTION 4

//////////////////////////////////////////////////////////////////////////////////////
//
// SECTION 5: DISPLAY A SINGLE PRODUCT - prodReqType 5
//
//////////////////////////////////////////////////////////////////////////////////////

// This code only runs in the instance where a user who is not logged in clicks the "More Info"
// button on a product without color or size options. After they have been routed through landing.php
// and logged in, this displays the listing for the single product they wanted to see.

if ($prodReqType == 5) {
//Let's get the product
	$product_id = $_GET["prodID"];


//First match the Product ID to a a product type and associated image
	$SQL = "SELECT Prod_ID, Img_ID, Item_Color_Chart_yn from ht_product_type WHERE Prod_ID = '".$product_id."'";
	$results = $database->query($SQL);

	
$make_listing($results);

}

////////////////////////////////////////////////////////////////////////////////////////////
//
// SECTION 6: DISPLAY SPECIALS AND FEATURES - prodReqType 6
//
/////////////////////////////////////////////////////////////////////////////////////////////

// This code handles the selection, display and pagination of results for Special and Featured products.
// It is sufficiently different from the other styles that it incorporates its own, modified version of the 
// make_listing and pagination code - therefore it is quite a bit longer and more complicated. 

// This code proceeds by making the correct query for Specials or Features based on the $special variable, 
// which will be set further up in index.php. It then sets up pagination and begins to loop through the results
// by distinct Group IDs

// Each group ID will have its products displayed as a part of a single listing with the blue headline bar at
// the top. There are 5 distinct listing styles. Some items may have price/size subgroups, which will have
// their own sublistings (small boxes with red Add to Cart buttons) and dropdown menus for options within each 
// price/size subgroup. 

// Style 1 handles simple listings
// Style 2 handles simple listings with overflow text
// Style 3 handles listings where each product in the group has a separate picture and option box 
// Style 4 handles listings with option boxes displayed beneath the description text rather than beside it
// Style 5 allows the admin to display non-product info, such as shipping and return policies, in the stream
//        of specials and features.

// Each option box has an add to cart button and may include a drop-down menu if many colors are available.
// We also have to have $nextPage and 'More Info' buttons for users who are not logged in 

// Pagination is a bit different here - listings are grouped into pages based on the Priority attribute
// in the database. 0-99 form page 1, 100-199 page 2, etc. They will be displayed in order of their priority
// level.

if ($prodReqType == 6) {

// Check if we are looking for specials or features
	$special = $_GET['special'];

	if ($special == 1) {
		$selector = 's';
		}
	else {
		$selector = 'f';
	} 

// Select the distinct group IDs from the specials database, selecting only the active items
	$SQL = "SELECT DISTINCT Specials_Group_ID FROM ht_specials WHERE Specials_Active = 1 AND Specials_Item_ID LIKE '".$selector."%' ORDER BY Specials_Priority ASC, Specials_Date DESC";
	$results = $database->query($SQL);
	$groups = $results->fetchAll();

// add the correct header for the page
	if ($selector == 's') {
	echo "<h1>Current Special Products</h1>";
	} else {
	echo "<h1>Featured Products!</h1>";
	}


// Begin the empty page array 
	$resultPages = array();
	$resultPages[] = ""; //assign empty string for page 0

// $range_high is used to assign products to pages based on their priority level, included in Specials_Priority 
	$range_high = 99;
// start the first blank page
	$thisPage = "";

//Go through the results, running the code for each distinct group
	foreach ($groups as $group) {
	// start with the group ID for reference
			$gID = $group[0];	
	// get the group's details from the database
			$SQL = "SELECT * FROM ht_specials WHERE Specials_Group_ID = '".$gID."'"; 
			$results = $database->query($SQL);
			$group_details = $results->fetch(); //get the first line

	// If the priority is higher than this page's upper limit, start a new page
			if ($group_details['Specials_Priority'] > $range_high) {

			// add existing page to array
			$resultPages[] = $thisPage;

			//reset thisPage to empty string
			$thisPage = "";

			//raise the range to the next set of 100
			$range_high = $range_high + 100; 
			}

///////////////////////////////////////////////////////////////////////
// FIRST LAYOUT OPTION - TYPES 1 and 2
			if ($group_details['Specials_Layout'] < 3) {

		//check for overflow text
				if ($group_details['Specials_Layout'] == 2) {
					$has_overflow = 1;
					} else {
					$has_overflow = 0;
					}

		// check to see how many items are in the listing, as single and multi item results require
		// different code
			$SQL = "SELECT COUNT(*) FROM ht_specials WHERE Specials_Group_ID = '".$gID."'";
			$items = $database->query($SQL); 

		// begin the listing with headline and image in left hand box
			$thisPage .= "<div class='special_listing' id='".$gID."'>";			
			$thisPage .= "<h2>".$group_details['Specials_Title']."</h2>";
			//$thisPage .= " Priority Lvl: ".$group_details['Specials_Priority']."</h2>"; //comment this line out for production
			$thisPage .= "<div class='special_content'>";
			$thisPage .= "<div class='special_subsection'>";
			$thisPage .= "<img src='http://jhittlesewing.funoverload.com/sewing/specials/images/".$group_details['Specials_Img_ID']."'>";
			$thisPage .= "</div>";	

		// if there is a group description, make a middle box and place it in there
			if 	($group_details['Specials_Group_Description'] != null) {
			$thisPage .= "<div class='special_subsection'>";				
			$thisPage .= "<p>".$group_details['Specials_Group_Description']."</p>";
			$thisPage .= "</div>";
			}
			

		// start the right hand box and add to cart section		
			$thisPage .= "<div class='special_subsection'>";

	// Multiple option products display as follows:
			if ($items->fetchColumn() > 1 ) {


		// Check foe differently-priced options (Specials_Size)
			$SQL = "SELECT DISTINCT Specials_Size FROM ht_specials WHERE Specials_Group_ID = '".$gID."'";
			$sizes = $database->query($SQL);
			$sizes_array = $sizes->fetchAll(); 

		// Loop through options within each price/size, get details, check for login, and display accordingly
		foreach ($sizes_array as $size) {
			$size_id = $size[0]; //need for later in colors selector

			//get full details
 			$SQL = "SELECT * FROM ht_specials WHERE Specials_Group_ID = '".$gID."' AND Specials_Size = '".$size_id."'";
			$size_results = $database->query($SQL);
			$size_details = $size_results->fetchAll();

			// we can get price information from the first line item in the price/size group						
				$first = $size_details[0]; 
				//determine price, and assign variables
					$price = $first['Specials_List_Price'];
					$pricename = $first['Specials_List_Wording'];
				//if it is a markdown, get the strike price too (the price marked down from) 
					$strike_price = $first['Specials_Strike_Price'];
					$strike_name = $first['Specials_Strike_Wording'];
	

				// start sublisting
					$thisPage .= "<div class='sublisting'><p>";
			
			//set $nextPage incase user not logged in
				$nextPage = $_SERVER['REQUEST_URI'].$_SERVER['QUERY_STRING']."#".$gID;
				$nextPage = urlencode($nextPage);

	
			//Now that we have set $nextPage, we check to see if the user is logged in

			//First case: user is not logged in, no cookie detected
			if (!isset($login)) {
		 		//show the More Info button and link, display "More Info..." so they must log in before proceeding			
					$thisPage .= "<div class='listing_form'>";
					$thisPage .= "<a class='add_to_cart_button' href='landing.php?nextpage=".$nextPage."'>More Info...</a></div></div>";
			}

			//Second case: user is logged in. Proceed with normal listing display.
			else {
				// echo the price, and original (strike) price if applicable
				$thisPage .= $first['Specials_Size']."</p><p>";
					if ($strike_price != 0) {
						$thisPage .= $strike_name."</p><p>";
					}
					$thisPage .= $pricename."</p>";

			// Start Add to Cart form
					$thisPage .= "<form class='atc_form' autocomplete='off'>";		
			
				//if multiple colors or options within this price group, add select element
				if ($size_results->rowCount() > 1 ) {
						$thisPage .= "<select name='id'>";	
					// populate the select with options, adding the item id as the value		
						foreach ($size_details as $color) {
							$color_id = $color['Specials_Item_ID'];
						$thisPage .= "<option value='".$color_id."'>".$color['Specials_Item_Description']."</option>";
						}					
						$thisPage .= "</select>";
					} 

				// If there is only one option in the price/size group, just add the one id to a hidden input in the form.
				else {
					$thisPage .= "<input type='hidden' name='id' value='".$first['Specials_Item_ID']."'>";
					}

				// Finish the add to cart form with QTY selector and button
						$thisPage .= "QTY:";			
						$thisPage .= "<input type='text' name='quantity' class='quantity'>";				
	
						$thisPage .= "<input type='hidden' name='user_id' value='".$user_id."'>";	
						$thisPage .= "<input type='hidden' name='order_id' value='".$order_id."'>";			
						$thisPage .= "<input type='submit' class='add_to_cart add_to_cart_button' value='Add to Cart'>";	
						$thisPage .= "</form>";	
						$thisPage .= "</div>";		
					}
				}
				
			//end the divs	
				$thisPage .= "</div>";	//end sublisting	
				$thisPage .= "</div>";	// end subsection (right hand box)
				$thisPage .= "</div>";	// end specials content

			// if it has overflow test - layout type 2 - append here
				if ($has_overflow == 1) {
					$thisPage .= "<div class='specials_overflow'>";
					$thisPage .= $group_details['Specials_Overflow'];
					$thisPage .= "</div>";
				}



	// Single Item results run this code
			} else {
				$SQL = "SELECT * FROM ht_specials WHERE Specials_Group_ID = '".$gID."'";
				$item_details = $database->query($SQL);
				$item_array = $item_details->fetch(); 			
				
					
				// get prices and assign to variables
					$price = $item_array['Specials_List_Price'];
					$pricename = $item_array['Specials_List_Wording'];
				//if it is a markdown, show the strike price
					$strike_price = $item_array['Specials_Strike_Price'];
					$strike_name = $item_array['Specials_Strike_Wording'];
	
				// start the sublisting within the right hand box	
					$thisPage .= "<div class='sublisting'><p>";

		//set $nextPage incase user not logged in	
			$nextPage = $_SERVER['REQUEST_URI'].$_SERVER['QUERY_STRING']."#".$gID;
			$nextPage = urlencode($nextPage);

		//Now that we have set $nextPage, we check to see if the user is logged in
		//First case: user is not logged in, no cookie detected
		if (!isset($login)) {
		 		//show the More Info button and link, display "More Info..." so they must log in before proceeding			
					$thisPage .= "<div class='listing_form'>";
					$thisPage .= "<a class='add_to_cart_button' href='landing.php?nextpage=".$nextPage."'>More Info...</a></div></div>";
		}
		//Second case: user is logged in. Proceed with normal listing display.

		else {
			if ($strike_price != 0) {
				$thisPage .= $strike_name."</p><p>";
			}
				$thisPage .= $pricename."</p>";						

			// Add to Cart form
						$thisPage .= "<form class='atc_form' autocomplete='off'>";			
						$thisPage .= "QTY:";			
						$thisPage .= "<input type='text' name='quantity' class='quantity'>";				
						$thisPage .= "<input type='hidden' name='id' value='".$item_array['Specials_Item_ID']."'>";	
						$thisPage .= "<input type='hidden' name='user_id' value='".$user_id."'>";	
						$thisPage .= "<input type='hidden' name='order_id' value='".$order_id."'>";			
						$thisPage .= "<input type='submit' class='add_to_cart add_to_cart_button' value='Add to Cart'>";	
						$thisPage .= "</form>";
				$thisPage .= "</div>";
				}
				$thisPage .= "</div>"; //end the various divs
				$thisPage .= "</div>";

				
				$thisPage .= "</div>"; // end the listing as a whole
				
			// If there is overflow text, append it here
				if ($has_overflow == 1) {
					$thisPage .= "<div class='specials_overflow'>";
					$thisPage .= $group_details['Specials_Overflow'];
					$thisPage .= "</div>";
				}
			}
		
	}

	//end layout style 1 and 2
///////////////////////////////////////////////////////////////

// LAYOUT STYLE 3 - multiple items with a pic for each
		
	if ($group_details['Specials_Layout'] == 3) {

//begin the div
			$thisPage .= "<div class='special_listing'>";
			$thisPage .= "<h2>".$group_details['Specials_Title']."</h2>";
			// $thisPage .= " Priority Lvl: ".$group_details['Specials_Priority']."</h2>";
			$thisPage .= "<div class='special_content'>";
// select the individual items within
			$SQL = "SELECT * FROM ht_specials WHERE Specials_Group_ID = '".$gID."'";
			$results = $database->query($SQL);

// First special-content div has the images - CSS will display these in a table-style row
			foreach ($results as $item) {
			$thisPage .= "<div class='special_subsection'>";
			$thisPage .= "<p>".$item['Specials_Item_Description']."</p>";
			$thisPage .= "<img src='http://jhittlesewing.funoverload.com/sewing/specials/images/".$item['Specials_Img_ID']."'>";			
			$thisPage .= "</div>";
			}
	//end the row
			$thisPage .= "</div>";

// Next special-content div has the add to cart buttons, again in a table-style row
			$thisPage .= "<div class='special_content'>";
	
	// query the database again
			$results = $database->query($SQL);
	// use item2 here to avoid confusion with code above


			foreach ($results as $item2) {	
			$thisPage .= "<div class='special_subsection'>";	
			$thisPage .= "<div class='sublisting'>";
	
	//set $nextPage incase user not logged in
			$nextPage = $_SERVER['REQUEST_URI'].$_SERVER['QUERY_STRING']."#".$gID;
			$nextPage = urlencode($nextPage);

	//Now that we have set $nextPage, we check to see if the user is logged in
			//First case: user is not logged in, no cookie detected
			if (!isset($login)) {
			 //show the More Info button and link, display "More Info..." 		
				$thisPage .= "<div class='listing_form'>";
				$thisPage .= "<a class='add_to_cart_button' href='landing.php?nextpage=".$nextPage."'>More Info...</a></div></div>";
			}
			
	//Second case: user is logged in. Proceed with normal listing display.
			else {
				// create the Add to Cart button
			$thisPage .= "<form class='atc_form' autocomplete='off'>";			
			$thisPage .= "QTY:";			
			$thisPage .= "<input type='text' name='quantity' class='quantity'>";				
			$thisPage .= "<input type='hidden' name='id' value='".$item2['Specials_Item_ID']."'>";	
			$thisPage .= "<input type='hidden' name='user_id' value='".$user_id."'>";	
			$thisPage .= "<input type='hidden' name='order_id' value='".$order_id."'>";			
			$thisPage .= "<input type='submit' class='add_to_cart add_to_cart_button' value='Add to Cart'>";	
			$thisPage .= "</form>";
			$thisPage .= "</div>";
		}
			$thisPage .= "</div>";
		
		}
		$thisPage .= "</div></div>"; //close out content and listing divs
	} //end layout style 3


///////////////////////////////////////////////////////////////
// LAYOUT STYLE 4 - extra wide top section, add to cart boxes underneath

	if ($group_details['Specials_Layout'] == 4) {

//begin the listing with group details and a special double-wide subsection for description text
			$thisPage .= "<div class='special_listing'>";
			$thisPage .= "<h2>".$group_details['Specials_Title']."</h2>";
			// $thisPage .= " Priority Lvl: ".$group_details['Specials_Priority']."</h2>";
			$thisPage .= "<div class='special_content'>";
			$thisPage .= "<div class='special_subsection'>";
			$thisPage .= "<img src='http://jhittlesewing.funoverload.com/sewing/specials/images/".$group_details['Specials_Img_ID']."'>";
			$thisPage .= "</div>";
			$thisPage .= "<div class='special_subsection dbl_section'>"; // note the class 'dbl_section' here!
			$thisPage .= $group_details['Specials_Group_Description'];
			$thisPage .= "</div>";
			$thisPage .= "</div>"; //end content row 1
			$thisPage .= "</div>"; // ends the main listing div here - the add to cart boxes for this one are 
											// technically not in the listing div. Otherwise CSS display-table would force them
											// to align with the double-wide div -- this was the easiest workaround!

	
// Time to do the Add to Cart boxes - start by selecting specific items within the specials group
			$SQL = "SELECT * FROM ht_specials WHERE Specials_Group_ID = '".$gID."'";
			$results = $database->query($SQL);		
			$subitems = $results->fetchAll();

			$thisPage .= "<div class='specials_overflow'>";

			foreach ($subitems as $subitem) { 
			$thisPage .= "<div class='special_subsection sub_4'>"; // again, special class 'sub_4' - do not change!
			$thisPage .= "<p>".$subitem['Specials_Item_Description']."</p>";
			$thisPage .= "<p>".$subitem['Specials_List_Wording']."</p>";
	
	//set $nextPage incase user not logged in
			$nextPage = $_SERVER['REQUEST_URI'].$_SERVER['QUERY_STRING']."#".$gID;
			$nextPage = urlencode($nextPage);

	//Now that we have set $nextPage, we check to see if the user is logged in
	//First case: user is not logged in, no cookie detected
	if (!isset($login)) {
 		//show the More Info button and link, display "More Info..." 		
			$thisPage .= "<div class='listing_form'>";
			$thisPage .= "<a class='add_to_cart_button' href='landing.php?nextpage=".$nextPage."'>More Info...</a></div></div>";
		}

	//Second case: user is logged in. Proceed with normal listing display.
		else {
			// Create the Add to Cart button
				$thisPage .= "<form class='atc_form' autocomplete='off'>";			
				$thisPage .= "QTY:";			
				$thisPage .= "<input type='text' name='quantity' class='quantity'>";				
				$thisPage .= "<input type='hidden' name='id' value='".$subitem['Specials_Item_ID']."'>";	
				$thisPage .= "<input type='hidden' name='user_id' value='".$user_id."'>";	
				$thisPage .= "<input type='hidden' name='order_id' value='".$order_id."'>";			
				$thisPage .= "<input type='submit' class='add_to_cart add_to_cart_button' value='Add to Cart'>";	
				$thisPage .= "</form>";
				$thisPage .= "</div>";
				}	
			}

			$thisPage .= "</div>"; //close out the divs
				
	} //end layout style 4


//////////////////////////////////////////////////////////////////////
//  LAYOUT STYLE 5 - for non product messages

	//message text should be in the database field 'Specials_Title'

	if ($group_details['Specials_Layout'] == 5) {
//begin the div
			$thisPage .= "<div class='special_listing type_5'>";
			$thisPage .= $group_details['Specials_Title'];
			// $thisPage .= " Priority Lvl: ".$group_details['Specials_Priority'];
			$thisPage .= "</div>";		
	}

} //end the big loop through specs and features by group ID - begins near line 754


// Finish up the pagination
$resultPages[] = $thisPage; //add last page of results to the page array


// This code prevents page numbers from accidentally going higher than what exists 
	$numPages = count($resultPages);
	if ($pageNum > ($numPages - 1)) {
		//set to last page
		$pageNum = ($numPages - 1);	
	}



// Create the links for the page numbers
	//query string gets the parameters for these search results
	$link = "index.php?" .$_SERVER['QUERY_STRING'];

	//remove previous value of page from string
	$newLink = strstr($link, "&page=", true);//strips everything after &page=

	if ($newLink != false) { //&page string was not found
	$link = $newLink;
	}


//start displaying page number nav (this one is simpler because page numbers should remain in single digits)
	$page_nav = "";
	$page_nav .= "<div id='page_numbers'>";
	$page_nav .= "Showing page ".$pageNum." of ".($numPages - 1);


//Create page links
	$page_nav .= "<div id='page_links'>";

	// loop through all pages and display links for all but current page
		for ($page= 1; $page < $numPages; $page++) {
			//if we're on the current page			
			if ($page == $pageNum) {
			$page_nav .= "<span class='currentPage'>".$page."</span>";			
			} else {
			$page_nav .= "<a href=".$link."&page=".$page.">".$page."</a>";
			}
		}
	$page_nav .= "</div>";
		
	$page_nav .= "</div>";

//Display selected page based on $pageNum (this variable was set in index.php)

	echo $page_nav;
	echo $resultPages[$pageNum];
	echo $page_nav;


} // END of Section 6 - SPECIALS AND FEATURES
// end of products_new.php

?>
