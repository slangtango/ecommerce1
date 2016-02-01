///////////////////////
//							//
// DISPLAY PRODUCTS  //
//							//
/////////////////////// 


// This little JS file is the miscellaneous script file
// It runs a few different page functions

// 1: the animation of the accordion menu on the side of the page
// 2: handles changes to the "display X products per page" drop down selector
// 3: handles hiding and showing the category list in mobile display
// 4: handles the "Back to top" button
// 5: handles the display and hiding of tooltips

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

//if the accordion is not displayed, show it
	if (!$('#accordion').is(":visible")) {
		$('#accordion').show();
	//add #results to the links, so that user is automatically scrolled down
	// far enough to see the product when the page reloads
		$('#accordion a').each(function() {
			this.href += '/#results';
		
		});
	//if it was already displayed, hide it
	} else {
		$('#accordion').hide();

	}

});


/// BACK TO TOP BUTTON ////


    //Listen for the button click event
    $('.to-top-btn').on('click', function(e) {
        $('body, html').stop().animate({scrollTop: 0}, 'slow', 'swing');
        e.preventDefault();
    });
 
    //Show the button when the page scrolls to about 1000 pixels
    //change the value to your desired offset
    $(window).scroll(function() {
        if($(window).scrollTop() > 1000){
            //show the button when scroll offset is greater than 400 pixels
            $('.to-top-btn').fadeIn('slow');
        }else{
            //hide the button if scroll offset is less than 400 pixels
            $('.to-top-btn').fadeOut('slow');
        }
    });


/// TOOL TIPS ///

    //Listen for the tooltip click event
    $(document).on('click', '.tooltip_title', function(e) {
		//if it's not visible, show it, and vice-versa
		if (!$('.tooltip').is(":visible")) {
    	$('.tooltip').show();   
			} else {
    	$('.tooltip').hide();    
			}
        });





