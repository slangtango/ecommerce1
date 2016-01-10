// This file operates the accordion nav on the side of the page

$(document).ready(function(){
	$("#accordion h3").click(function(){
		//slide up all the link lists
		$("#accordion ul ul").slideUp();
		//slide down the link list below the h3 clicked - only if its closed
		if(!$(this).next().is(":visible"))
		{
			$(this).next().slideDown();
		}
	})
})


//// RESULTS PER PAGE ////
//when the rpp select value is changed
	$(document).on('change', '#rpp_select', function (event) {
	
	var $rpp = $(this).val();
	var $location = document.URL;

	$url = $location + "&rpp=" + $rpp;

//reload in this window
	window.open($url, "_self", true);
});
