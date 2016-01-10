//////////////////////////////////////
//										 		//
// 		AUTOCOMPLETE.JS				//
//												//
//////////////////////////////////////


//This file sends requests to PHP files to generate autocomplete lists for search elements when data is entered

//document.ready prevents any code from running until page is fully loaded, to make sure all dependencies are in place
$(document).ready(function() {						

//Set requestType varaiable
var requestType;

// PRODUCT SEARCH // 
//When a keyboard key is released while entering data into the #productSearch input
	$("#productSearch").on("keyup", function(event) {

//Set Request Type to 1 for Product Request
	requestType = 1;

//reset query variables and list content
	var productQuery = "";
	document.getElementById("product_autocomplete_list").innerHTML = "";
	this.style.display = "block";

//check the length of the string in the input							
		productQuery = $("#productSearch").val();	
			if (productQuery.length > 2) {
//If longer than 2 characters, launch AJAX request to get product list		
				var xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
						document.getElementById("product_autocomplete_list").innerHTML = xmlhttp.responseText;
		
					}
				}
			xmlhttp.open("GET", "autocomplete.php?requestType=" + requestType + "&productQuery=" + productQuery, true);	
			xmlhttp.send();			
			}

		});
	//Display the list!
	$("#product_autocomplete_list").show();



// MERCHANT SEARCH //
//When a keyboard key is released while entering data into the #merchantSearch input
	$("#merchantSearch").on("keyup", function(event) {

//Set Request Type to 2 for Merchant Request
	requestType = 2;

//reset query variables and list content 
	var merchantQuery = "";
	document.getElementById("merchant_autocomplete_list").innerHTML = "";
	this.style.display = "block";	

//check the length of the string in the input							
		merchantQuery = $("#merchantSearch").val();	
			if (merchantQuery.length > 0) {
//If longer than 0 characters, launch AJAX request to get product list	

//Launch AJAX request										
				var xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
						document.getElementById("merchant_autocomplete_list").innerHTML = xmlhttp.responseText;
			
					}
				}
			xmlhttp.open("GET", "autocomplete.php?requestType=" + requestType + "&merchantQuery=" + merchantQuery, true);	
			xmlhttp.send();			
			}

	});

//Display the list!
	$("#merchant_autocomplete_list").show();


// QUICK PRODUCT ADD// 
//When a keyboard key is released while entering data into the #productSearch input
	$(document).on("keyup", "#quick_add_desc", function(event) {

//Set Request Type to 1 for Product Request
	requestType = 3;

//reset query variables and list content
	var productQuery = "";
	document.getElementById("quickAddAClist").innerHTML = "";
	//this.style.display = "block";

//check the length of the string in the input							
		productQuery = $("#quick_add_desc").val();	
			if (productQuery.length > 3) {
//If longer than 3 characters, launch AJAX request to get product list		
				var xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
						document.getElementById("quickAddAClist").innerHTML = xmlhttp.responseText;
							console.log("showing list");
					}
				}
			xmlhttp.open("GET", "autocomplete.php?requestType=" + requestType + "&productQuery=" + productQuery, true);	
			xmlhttp.send();			
			}

		});
	//Display the list!
	$("#quickAddAClist").show();

//Set quick add ID and Desc when clicked
$(document).on('click', '.quick_add_ac_link', function(event) {
	var $rawinfo = this.id;
	var $infoArray = $rawinfo.split("and", 2);
	
	console.log($infoArray[0]);
	console.log($infoArray[1]);
	$("#qa_item_id").val($infoArray[0]);
	$("#quick_add_desc").val($infoArray[1]);

});

//Set searchbox value to clicked link when clicked
	//detect click on autocomplete list links
	$(document).on('click', '.autocomp_link', function(event) {
		var $linkname = this.id;	

		// Rewrite the text in the appropriate input box and clear the other 
		if (requestType == 2) {   
			$("#merchantSearch").val($linkname);
			$("#productSearch").val("");
		} else if (requestType == 1) {   
			$("#productSearch").val($linkname);
			$("#merchantSearch").val("");
		} 

		});



//Hide lists when document is clicked
$(document).on("click", function(event) {
		$(".autocomplete_list").hide();	
	});

});

				
