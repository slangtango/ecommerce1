<?php
///////////////////////////////////
//											//
//			LANDING.PHP					//
//											//
///////////////////////////////////

// This page is the gateway for new users. It checks to see if the user was routed here
// from another part of the site, and routes them back to that part after collecting
// their login data. If they were not routed here from a particular product, they will simply
// be directed to the home page.

// If they were routed here by clicking 'Log Out', it deletes the cookie

// It includes a jQuery snippet at the end to fix form validation issues in Safari and iOS,
// preventing users on those browsers from entering the site without an email

// All other browsers recognize the "required" attribute on the email input and will prevent empty submits

// Check to see if the user was logging out
	$logout = $_GET['logout'];

	// if so, set cookie expiration date into the past - no more cookie!
	if ($logout == 1) {
		setCookie("loginEmail", "", time() -3600, "/");
	}



//Check to see if we were redirected from another part of the site
	if (isset($_GET['nextpage'])) {
		$nextPage = $_GET['nextpage'];
	} else {
		$nextPage = "index.php";
	}

// Include the default basic header
include 'header.php';

?>
 

	
	<div>
		<div id="landing_headline_wrap">
		<h1>J. Hittle Sewing</h1>
			<h2>A One-Stop Shop For Sewing, Quilting, Applique, Embroidery, Purse Making, and Crafting Supplies</h2>
			<h3>Large or Small Orders Welcome!</h3>
		</div>
		<div id="user_registration">	
			<p>Due to the sensitivity of wholesale prices we ask that you register with us before viewing our catalog.</p>
			<p>If you have already registered with J Hittle Sewing, please enter the email address you usually use with us.</p>
			<!-- use this line to check nextpage variable <?php echo $nextPage; ?> -->
			<form id="reg_form" action="<?php echo $nextPage; ?>" method="post">
				<input type="hidden" name="enter" value="x">
				<input type="hidden" name="sender" value="JHS">
				<input type="hidden" name="mailing" value="1">
				<div id="user_reg_left">			
					<p>Please enter your email and the type of sewing-related activity you are engaged in:</p>
					<input required id="landing_email" type="text" name="useremail" placeholder="Enter your email..."></input></br>					
					<input type="checkbox" name="casual" value="1">Casual Sewing</input></br>
					<input type="checkbox" name="craft" value="1">Craft or Quilting Group</input></br>
					<input type="checkbox" name="seam" value="1">Seamstress or Tailor</input></br>
					<input type="checkbox" name="small" value="1">Small Retailer</input></br>
					<input type="checkbox" name="large" value="1">Large Retailer</input></br>
					<input type="checkbox" name="man" value="1">Manufacturer of sewn products</input></br>
					<input type="checkbox" name="other" value="1">Other</input>
					<hr>					
					<br>
					<input type="checkbox" name="discount" value="1">I am interested in discount prices</input></br>
					<input type="checkbox" name="wholesale" value="1">I am interested in wholesale prices</input></br>
					<input type="checkbox" name="taxid" value="1">I have a Sales Tax ID number</input></br>
				</div>
	
				<div id="user_reg_right">
					<p>Terms Of Use: I agree that I am engaged in a sewing related activity and have reason to gain access to confidential wholesale pricing.
						By entering this site I agree to receive updates and featured materials through e-mail each week.</p>
						<a href="#privacy">Privacy policy</a>
						<div id='privacy'>
							<div id='privacy_text'>
							<h2>Privacy Policy</h2>

							<p>J Hittle Sewing is committed to protecting your privacy and providing the most secure online shopping experience possible.
							If you have any questions regarding our privacy please contact us.</p>
							<p>Phone Number<br>
							1-877-805-2616 <br> 
							Monday - Friday 9:00am-7:00pm EST</p>
							<p>Mailing Address<br> 
							J Hittle Sewing<br>
							11703 Pierce Way<br>
							Louisville, KY. 40272</p>
							<p>E-Mail Address<br>
							john@happythoughtstoo.com</p>
		
							<h2>Personal Information</h2>
							<p>J Hittle Sewing collects the following information for the purpose of marketing our products only.</p>
		
							<h3>E-Mail Address</h3>
							<p>When you register, your e-mail address will be stored in our secure database.
							This database will not be sold, shared, rented, or made available in any way to third parties.
							You will receive notices from us regarding sales and featured items, but it will only be e-mailed to those that have signed up.
							You may opt out at any time from receiving these e-mails by contacting us.</p>
							<h3>Questionaire</h3>
							<p>This helps us to determine the nature of our customers in the sewing and crafting market.</p>
							<h3>Cookies</h3>
							<p>Cookies are small text files that your web browser stores on your computer's hard drive.  
							The cookies are used to keep track of items you put in your shopping cart and to help us track what ad campaign you may have found us from.  
							J Hittle Sewing cookies are not available to other companies and will not retrieve any personal information 
							such as name, address, or credit card information from your computer.</p>
							<a href='#'>Close</a>
							</div>
						</div>
					<p>By entering this website I agree to the Terms of Use</p>
					<input type="submit" name="entersite" value="Enter Site">
					<span id='invalid_tip'>Please enter your email</span> <!-- CSS set to not display this, only appears if an empty submit is attempted -->
				</div>

			</form>

		</div>
	</div>

	<?php include "footer.php"; ?> <!-- This file generates the page footer -->

	  <!-- jQuery -->
	 <!-- The following file imports the JQuery library. It must be included or the other scripts will not work -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
	
	<script> 
	/// FORM VALIDATION BYPASS FOR SAFARI AND IOS ///
	
	// When the form attempts to submit
	$('#reg_form').on('submit', function() {
		// console.log('submitting'); // confirms that the function is being triggered, for debugging use

		//if nothing is in the landing email, add the class 'empty', which adds a red border, and display
		// the span of text below the button that prompts them to enter an email
	if ($('#landing_email').val() == "") {
		
		$('#landing_email').addClass('empty');
		$('#invalid_tip').css('display', 'block');
		// return false prevents the submit action from continuing
		return false;
	}
	});
	</script>
	</body>
</html>
