<?php


//Check to see if we were redirected from another part of the site
// and get the intended next page link

//otherwise nextPage is index.php

	if (isset($_GET['nextpage'])) {
		$nextPage = $_GET['nextpage'];
	} else {
		$nextPage = "index.php";
	}

include 'header.php';

?>
 

	
	<div>
		<div id="landing_headline_wrap">
		<h1>J. Hittle Sewing</h1>
			<h2>A One-Stop Shop For Sewing, Quilting, Applique, Embroidery, Purse Making, and Crafting Supplies</h2>
			<h3>Large or Small Orders Welcome!</h3>
		</div>
		<div id="user_registration">	
			<p>Due to the sensitivity of wholesale prices we ask that you register with us before viewing our catalog</p>
			<!-- use this line to check nextpage variable <?php echo $nextPage; ?> -->
			<form id="reg_form" action="<?php echo $nextPage; ?>" method="post">
				<!-- <input type="hidden" name="nextPageLink" value="<?php echo $nextPage; ?>"> -->
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

				</div>
	
				<div id="user_reg_right">
					<p>Terms Of Use: I agree that I am engaged in a sewing related activity and have reason to gain access to confidential wholesale pricing.
						By entering this site I agree to receive updates and featured materials through e-mail each week.</p>
						<a href="#">Privacy policy</a>
					<p>By entering this website I agree to the Terms of Use</p>
					<input type="submit" name="entersite" value="Enter Site">
				</div>

			</form>

		</div>
	</div>

	<?php include "footer.php"; ?> <!-- This file generates the page footer -->

	</body>
</html>
