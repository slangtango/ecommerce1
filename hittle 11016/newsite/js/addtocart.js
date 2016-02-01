//////////////////////////////////////
//										 		//
// 		ADD TO CART SCRIPT (JS)		//
//												//
//////////////////////////////////////

//This file handles events for all things related to the shopping cart, with the exception
// of the Quick Add autocomplete list, which is handled by autocomplete.js. It sends information 
// to the server-side addtocart.php, reloads the shopping cart without a page refresh at the end
// of most actions, and turns display on and off for button and form elements as needed.

// Contents //

	// 1 - ADD TO CART
	// 2 - REMOVE FROM CART
	// 3 - CHANGE ITEM QUANTITY

	// 4 - ADDING AND VALIDATING BILLING ADDRESS (auto runs for first time visitors)
	// 5 - DISPLAY BILLING ADDRESS CHANGE FORM (launches script 4 for repeat visitors seeking to change info)
	// 6 - CANCEL BILLING ADDRESS CHANGE
	// 7 - AUTO POPULATING THE SHIPPING FORM FROM BILLING
	// 8 - VALIDATING THE SHIPPING ADDRESS BEFORE SUBMIT

	// 9 - ADJUST QUICK ADD AND CART TABLES FOR MOBILE DISPLAY


///////////////////////////////////////////////////////////////////////////////////////////////////////////////

// 1 - ADDING PRODUCTS TO CART
///////

// This event triggers when a form with the class "atc_form" is submitted. Each catalog
// listing with an Add to Cart button has one of these forms, and the Quick Add also has
// the atc_form class.
$.ajaxSetup({ cache: false }); //prevents the AJAX cache. This line is required for compatibility with Edge


$(document).on('submit', '.atc_form', function(event) {


//Check to see if the form has an ID. This will be used to see if it is the Quick Add.
	var id = $(this).attr('id');
//Save this form and quantity input as variables for later use
	var $thisQty = $(this).find(".quantity");
	//prevent adds of quantity 0
	$qty = $thisQty.val();
	//must be a nonzero integer
		console.log($qty);
	if ((parseInt($qty) != $qty) || ($qty == 0)) {
		console.log("empty qty");
		return false;
	}

	var $thisForm = $(this);


//if the form submitted was the Quick Add, get the user id and order id from the hidden form inputs 
	if (id == 'qa') {
			console.log("id captured");
			$user_id = $('#qa_user_id').val();
			$order_id = $('#qa_order_id').val();
	}


//Prevent the form submit from reloading the page
	event.preventDefault();

//Serialize data from the form, and generate a target URl by appending the serial data as a query string
	var $serial = $(this).serialize();
	var targetUrl = "addtocart.php?add=1&" + $serial; //the add=1 will direct addtocart.php  to the add functions

	console.log(targetUrl);



//The console.log statement prints the target URL to the browser console for debugging checks - this line can be removed
	// console.log(targetUrl);



//Send request to addtocart.php to trigger the actual database update
					
//Set up an AJAX request
		$.ajax({ 
			cache: false,
			url: targetUrl, 								// <- url = target url, where the submit will go
			success: function(result) {				// when the AJAX action is complete, run the following code
				
				//If the action was from the Quick Add form, refresh the results div with the updated cart info
				if (id == 'qa') {

				//load new content
					$('#results').load("shoppingcart.php?reload=1&CustID="+ $user_id + "&Order_ID=" + $order_id);
	
				//if mobile			
				if ($( window ).width() < 769) {		
				//Resize cart table if mobile
				resize();	
				location.reload();
				}
				
				//If the action was from a catalog Add to Cart button, show the "Added to Cart" tip for
				} else {
				 $thisForm.append("<div class='addTip'>Added to Cart</div>");  //add the new div to the page
				$('.addTip').delay(1000).fadeOut(1000, function() {				//wait 1 second, then fade out for 1 second
						
						//When the "Added to Cart" tip has disappeared, set the Quantity box to empty again
						$thisQty.val("");
					});

				}
			}
		});
	});
// End of ADD PRODUCT SCRIPT


/////////////////////////////////////////////////////////////////////////////////////////////////////////



// 2 - REMOVING A PRODUCT FROM CART


//The following code runs when the user clicks on an element with the class "delete_item" - i.e, Remove buttons in cart
	$(document).on('click', '.delete_item', function(event) {
		event.preventDefault();
		console.log('removed');
// Start by getting the line item Entry ID, the key for any listing in ht_cart_order. It is stored as the "value" attribute
// of the Remove button
			var entryID = $(this).val();
		console.log(entryID);

// Get user and order ID from the hidden inputs (located up in the Quick Add)
	userID = $('#qa_user_id').val();
	orderID = $('#qa_order_id').val();

// Remove the product from the actual database record

//Set up an AJAX request to send the correct data to addtocart.php
		var targetUrl = "addtocart.php?remove=1&entryID=" + entryID + "&order_id=" + orderID
		console.log(targetUrl);		

		$.ajax({ 
			cache: false,
			url: targetUrl, 
			success: function(result) {
				console.log('done');
			//When the AJAX is done, refresh the contents of the results div to display the updated cart
				$('#results').load("shoppingcart.php?reload=1&CustID="+ userID + "&Order_ID=" + orderID);

			//if mobile			
				if ($( window ).width() < 769) {		
				//Resize cart table if mobile
				resize();	
				location.reload();
				}
			}
		});
});
// END OF REMOVE PRODUCTS



/////////////////////////////////////////////////////////////////////////////////////////////////////////




// 3 -CHANGING QUANTITY OF AN ITEM
// This section includes two functions. The first one displays the text input in the QTY field, 
// changes the button text to "Done", and gets the Entry ID so we know which record to change. The second function
// collects the user's new quantity and updates the database, then changes the display back to the normal setup.


//Section 3.1: this runs when a click event occurs on an element with the class "change_qty" - Change Qty button in cart
	$(document).on('click', '.change_qty', function(event) {

	// Get the line item Entry ID, the key for any listing in ht_cart_order. It is stored as the "value" attribute
	// of the button
		var id = $(this).val();
	//Change button text to "Done"
		$(this).parent().html("<button class='chg_done cart_button' value='"+id+"'>Done</button>");
	//Show the input text box in QTY, targeting that table cell by using the ID value and #qty
		$('#qty' + id).html("<input type='text' class='qty' id='input"+id+"'>");

	});



// Section 3.2 -- write to database and reset, launched when the user clicks the "Done" button
	$(document).on('click', '.chg_done', function(event) {
		console.log("changed");

//Grab the entryID again, as well as user and order ids from the hidden inputs in the Quick Add
			var id = $(this).val();
			var userID = $('#qa_user_id').val();
			var orderID = $('#qa_order_id').val();

//Capture the new quantity from the input text box and display as plain text
		var newqty = $('#input' + id).val();
	// prevent 0 or non-integer entries
		if ((parseInt(newqty) != newqty) || (newqty == 0)) {
		console.log("empty qty");
		return false;
		}


		$('#qty' + id).html(newqty);



//Change the button from "Done" back to "Change Quantity"
		$(this).parent().html("<button class='change_qty' value='"+id+"'>Change Qty</button>");



//Submit change to database
		var targetUrl = "addtocart.php?update=1&entryID=" + id + "&order_id=" + orderID + "&quantity=" + newqty
		
		$.ajax({ 
			url: targetUrl, 
			success: function(result) {
				//console.log("change qty function succesful");
			//Refresh the results div to display the updated shopping cart
				$('#results').load("shoppingcart.php?reload=1&CustID="+ userID + "&Order_ID=" + orderID);

				//if mobile			
				if ($( window ).width() < 769) {		
				//Resize cart table if mobile
				resize();	
				location.reload();
				}
			}
		});
	});

// END OF CHANGE QTY



/////////////////////////////////////////////////////////////////////////////////////////////////////////

// 4 - ADDING AND VALIDATING BILLING ADDRESS INFORMATION


//This script adds or updates billing address information for a user, and runs whenver the 
// form with the id "addy_form" submits. This form displays automatically for users with no
// address on file, or can be made to display by clicking the Change Billing Address button (next script)

// It validates that the form is complete, submits the data as a query string to the new_address.php script, 
// and updates the cart's address display

$(document).on('submit', '#addy_form', function(event) {

//Prevent page reload
	event.preventDefault();
	console.log('adding_address');



/// FORM VALIDATION BYPASS FOR SAFARI AND IOS ///
	// set incomplete status to 0
	var $incomplete = 0;
	//select all elements in this form with the attribute '[required]'
$('#addy_form').find('[required]').each(function() {

	//if the input is empty, set incomplete to 1 and add class 'empty' which turns the outline red
	if ($(this).val() == "") {
		$incomplete = 1;
		$(this).addClass('empty');
	} else {
		//reset any which have had data added, in case the user has to be prompted more than once
		$(this).removeClass('empty');
	}

	
});
//if any input is incomplete, stop any further action
if ($incomplete == 1) {
	console.log('incomplete form');
	return;
}

//If everything was OK, keep rolling and send off the request
//Start by serializing the data from the form
	var $serial = $(this).serialize();

//Get user and order id from the hidden inputs in the Quick Add
		$userID = $('#qa_user_id').val();
		$orderID = $('#qa_order_id').val();

//Create a new AJAX request and write to the database
		var targetUrl = "new_address.php?" + $serial;
		
		$.ajax({ 
			url: targetUrl, 
			success: function(result) {
				// console.log("added address");  <-- these two lines are useful for debugging purposes, uncomment if needed


				//If the AJAX call was successful, display the billing address div and cart buttons, and hide the form
				$('#billing_div').show();
				$('#cart_buttons').show();
				$('#chg_addy').show();
				$('#addy_cxl').hide();
				$('#addy_form_div').hide();

			// Reload the shopping cart to display updated information
				$('#results').load("shoppingcart.php?reload=1&CustID="+ $userID + "&Order_ID=" + $orderID);

			//if mobile			
				if ($( window ).width() < 769) {		
				//Resize cart table if mobile
				resize();	
				location.reload();
				}
			}
		});
	});
//End of ADDRESS ADD



/////////////////////////////////////////////////////////////////////////////////////////////////////////


// 5 - CHANGING ADDRESS INFORMATION
// This script displays the billing address update form when a user clicks "Change Billing Address", so they
// to update their billing information


//When the user clicks on the change addy button
	$(document).on('click', '#chg_addy', function (event) {

//Hide the billing address display, and show the address input
		$('#addy_form_div').removeClass("hidden_div");
		$('#addy_cxl').css("display", "inline-block");
		$('#billing_div').hide();
		$('#chg_addy').hide();
		$('#checkout_button').hide(); //prevent user from trying to checkout with incomplete information

//assign existing billing address values to the inputs so user doesn't have to retype everything

	// First. access these values by the "text" property of span elements placed in the document for this purpose
		var email = $('#email_wrap').text();
		var fname = $('#fname_wrap').text();
		var lname = $('#lname_wrap').text();	
		var line1 = $('#addy1_wrap').text();	
		var line2 = $('#addy2_wrap').text();	
		var city = $('#addy_city_wrap').text();	
		var state = $('#addy_state_wrap').text();	
		var zip = $('#addy_zip_wrap').text();
		var phone = $('#addy_phone_wrap').text();		

	// Next, assign the values to the form inputs
		$('#addy_email').val(email);
		$('#addy_fname').val(fname);
		$('#addy_lname').val(lname);
		$('#addy1').val(line1);
		$('#addy2').val(line2);
		$('#addy_city').val(city);
		$('#addy_state').val(state);
		$('#addy_zip').val(zip);
		$('#addy_phone').val(phone);
});
//end CHANGING ADDRESS INFORMATION



/////////////////////////////////////////////////////////////////////////////////////////////////////////



// 6 -  CANCEL ADDRESS CHANGE

// This script hides the billing address update form without updating the database, if a user decides to cancel the change
// It is triggered by a click on the button with class "addy_cxl"
	$(document).on('click', '#addy_cxl', function (event) {

		//prevent form from interpreting the button click as a submit and refreshing the whole page
		event.preventDefault();

		//hide the address form, show the billing div, change and checkout buttons again
		$('#addy_form_div').addClass("hidden_div");
		$('#addy_cxl').hide();
		$('#billing_div').show();
		$('#chg_addy').show();
		$('#checkout_button').show();
});




/////////////////////////////////////////////////////////////////////////////////////////////////////////


// 7 -  USE BILLING AS SHIPPING ADDRESS 

// This script auto populates the shipping address form with the values from the billing address
// It is toggled on and off by the "change" event on the checkbox with the id "same_as_bill"

 $(document).on('change', '#same_as_bill', function(event) {

 //If box is checked ,set values to billing values
	if (this.checked) {

	//Assign text from span elements in the billing address display to variables
		var fname = $('#fname_wrap').text();
		var lname = $('#lname_wrap').text();	
		var line1 = $('#addy1_wrap').text();	
		var line2 = $('#addy2_wrap').text();	
		var city = $('#addy_city_wrap').text();	
		var state = $('#addy_state_wrap').text();	
		var zip = $('#addy_zip_wrap').text();		

	//Assign variables to shipping inputs

		$('#shipping_fname').val(fname);
		$('#shipping_lname').val(lname);
		$('#shipping1').val(line1);
		$('#shipping2').val(line2);
		$('#shipping_city').val(city);
		$('#shipping_state').val(state);
		$('#shipping_zip').val(zip);
	}
		//If box was unchecked, set back to empty
		else {
		$('#shipping_fname').val("");
		$('#shipping_lname').val("");
		$('#shipping1').val("");
		$('#shipping2').val("");
		$('#shipping_city').val("");
		$('#shipping_state').val("");
		$('#shipping_zip').val("");
		}
});




/////////////////////////////////////////////////////////////////////////////////////////////////////////


// 8 - SHIPPING FORM VALIDATION
// This script prevents the checkout form from submitting to the confirmation page if the shipping
// address form is incomplete

//run script when the checkout form submits
$(document).on('submit', '#checkout_form', function(event) {

/// FORM VALIDATION BYPASS FOR SAFARI AND IOS ///
	//set incomplete status to 0
	var $incomplete = 0;
	//find all elements in this form with the attribute '[required]' and loop through
$('#checkout_form').find('[required]').each(function() {

	//if they are empty, set incomplete to 1, and add the empty class, which gives a red border
	if ($(this).val() == "") {
		$incomplete = 1;
		$(this).addClass('empty');
	} else {
		//this resets any that have had data added, in case the user has to be prompted more than once
		$(this).removeClass('empty');
	}

	
});
//if any required input is incomplete, stop any further action
if ($incomplete == 1) {
	console.log('incomplete form');
	return false;
}

});


////////////////////////////////////////////////////////////////////////////////////////////////////////


/// 9 - MOBILE TABLE DISPLAY

//This script recreates the cart table in a mobile friendly layout when small viewports are detected
//To run it, call the function 'resize' - you will need to reload the page for results to present
//since this only changes classes for CSS purposes

var resize = function() {
  $width = $( window ).width();
	if ($width < 769) {
		console.log("resizing table");

		var head = $('.responsive_table thead');
		head.hide();

		var cells = $('.responsive_table td');
		if	( cells.parent().is('tr')) {
			cells.unwrap();
			cells.addClass('mobile_cell');

			$(".mobile_cell:nth-child(6n+2)").addClass('qty_cell');
			$(".mobile_cell:nth-child(6n+3)").addClass('price_cell');
			$(".mobile_cell:nth-child(6n+4)").addClass('sub_cell');


			//take it back from quick add cells
			$('#qa_table td:nth-child(2)').removeClass('qty_cell');
			$('#qa_table td:nth-child(3)').removeClass('price_cell');

			cells.wrap('<tr></tr>');		
		}
	} else {
	 	//	$(".mobile_cell:nth-child(6n+2)").removeClass('qty_cell');
		//	$(".mobile_cell:nth-child(6n+3)").removeClass('price_cell');
		//	$(".mobile_cell:nth-child(6n+4)").removeClass('sub_cell');
		
		//	cells.removeClass('mobile_cell');
		//	cells.unwrap();
	}
};



$(window).load(resize);



