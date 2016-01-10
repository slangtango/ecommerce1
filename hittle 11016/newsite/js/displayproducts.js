// DISPLAY PRODUCTS  // 


// This little JS file operates the animation of the accordion menu on the side of the page, and handles
// changes to the "display X products per page" drop down selector


//// ACCORDION  ///
$(document).ready(function(){
	// when the accordion menu categories are clicked
	$("#accordion h3").click(function(){

		//Start by sliding up all the link lists
		$("#accordion ul ul").slideUp();

		//slide down the link list below the h3 clicked - only if its closed
		if(!$(this).next().is(":visible"))
		{
			$(this).next().slideDown();
		}
	})
})


//// RESULTS PER PAGE ////

//When the rpp select value is changed
	$(document).on('change', '#rpp_select', function (event) {
	
	var $rpp = $(this).val();
	var $location = document.URL;

	//remove everything after $rpp= from previous query strings, to prevent rpp queries from doubling up
	$location = $location.split('&rpp=', 1).pop();

	$url = $location + "&rpp=" + $rpp;

//Reload the page with the new rpp value attached to the query string
	window.open($url, "_self", true);
});



/// SHOW CATEGORIES IN MOBILE ///

// When the Category heading is clicked in mobile
$(document).on('click', '#clicktoshow', function (event) {
	console.log('tapped');
	if (!$('#accordion').is(":visible")) {
		$('#accordion').show();

		$('#accordion a').each(function() {
			this.href += '/#results';
			console.log('added');		
		});

	} else {
		$('#accordion').hide();

	}

});




