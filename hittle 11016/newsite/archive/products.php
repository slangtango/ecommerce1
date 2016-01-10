<?
include ("variable.php");

/////////////////////////////////////////
//                                     //
// this is jhs products script.        //
//                                     //
/////////////////////////////////////////



	$host = "localhost";
	# connect to database
	$cid = mysql_connect($host,$user,$pw);
	if (!$cid) { echo("ERROR: " . mysql_error() . "\n");}

$selectby = $_GET['selectby'];
$merchant = $_GET['merchant'];
$productsearch = $_GET['productsearch'];
$group = $_GET['group'];
$merch = $_GET['merch'];
$product = $_GET['product'];
$start = $_GET['start'];
$productname = $_GET['productname'];
$listitem = $_GET['listitem'];
$sub = $_GET['sub'];
$searchman = $_GET['searchman'];
$searchprod = $_GET['searchprod'];

$keywordsearch = $_POST['keywordsearch'];
$searchcatalog = $_POST['searchcatalog'];


if ($_POST)
	{
	if ($_POST['keywordsearch'])
		{
		$keywordsearch = preg_replace('{ [^ \w \. - _  @ ] }x', '', $keywordsearch );
		}
		//echo "$keywordsearch<p>";
	}		



echo "
<html><body link=blue alink=blue vlink=blue>
";

if ($productsearch == 1)
	{
	echo "

	<center><font size=+2><b>Choose an item type to continue</b></font></center><p>
	<table width=90% align=center valign=top>";

	$tablerow = 0;
	$SQL = "SELECT Cat_ID, Cat_Name FROM ht_catagory ORDER BY Cat_Name ASC";

	#execute SQL statement
	$result = mysql_db_query($db,"$SQL",$cid) or die ("Couldn't connect to the MySQL database! 1");
	// Loop through the database and collect all entries.
	while ($prodloop = mysql_fetch_array($result))
		{
		$catid = $prodloop[0];
		$name = $prodloop[1];

		if ($tablerow == 0)
			{
			echo "<tr>";
			}

		echo "<td align=left><a href=\"./products.php?selectby=product&product=$catid&prodname=$name\"><b>$name</b></a></td>";

		$tablerow = $tablerow + 1;

		if ($tablerow == 3)
			{
			echo "</tr>";
			$tablerow = 0;
			}
		}
	if ($tablerow == 1)
		{
		echo "<td></td><td></td></tr>";
		}
	elseif ($tablerow == 2)
		{
		echo "<td></td></tr>";
		}
	echo "</table>";
	}



if ($selectby == product) // This will make a drop down of the product selected by manufacturers.
	{
	echo "
	<center><font size=+2><b>$prodname</b></font><p>
	The following merchants supply $prodname.<p>
	Click the merchant name for a list of their products.<p>
	";

	$a = 0;
	// This first query will get the total number of referrals for this member.
	$SQL = "SELECT DISTINCT Mer_ID FROM ht_product_type WHERE Cat_ID = '$product'";

	#execute SQL statement
	$result = mysql_db_query($db,"$SQL",$cid) or die ("Couldn't connect to the MySQL database!");
	// Loop through the database and collect all entries.
	while ($ploop = mysql_fetch_row($result))
		{
		$pcm[$a] = "$ploop[0]";
		$a = $a + 1;
		}

	for ($b = 0; $b < $a; $b++)
		{
		// This first query will get the total number of referrals for this member.
		$SQL = "SELECT Mer_Name FROM ht_merchant WHERE Mer_ID = '$pcm[$b]'";

		#execute SQL statement
		$result = mysql_db_query($db,"$SQL",$cid) or die ("Couldn't connect to the MySQL database!");
		// Loop through the database and collect all entries.
		while ($mloop = mysql_fetch_row($result))
			{
			$pmerch[$a] = "$mloop[0]";

			echo "
			<a href='./products.php?group=1&merch=$pcm[$b]&product=$product&start=0' target='mainframe'>$pmerch[$a]</a><p>
			";
			}
		}
	echo "</center>";
	}

if ($group == 1)
	{


	$SQL = "SELECT Mer_Name FROM ht_merchant WHERE Mer_ID = '$merch'";

	#execute SQL statement
	$result = mysql_db_query($db,"$SQL",$cid) or die ("Couldn't connect to the MySQL database!");
	$mername = mysql_fetch_row($result);

// ****************************************************** //
// FOLLOWING LINES EDITED BY OWEN //
//old code	
//	echo "<center><b><font size=+1>$mername[0]</font></b></center><p>";

//new code
	
	echo "<h1>$mername[0]</h1>";

//end new code 
//*********************************************************//

	$a = 0;
	// This first query will get the total number of referrals for this member.
	$SQL = "SELECT Prod_ID, Prod_Name, Prod_Image_yn, Prod_Header_yn, Img_ID FROM ht_product_type WHERE Mer_ID = '$merch' AND Cat_ID = '$product' ORDER BY Prod_Name ASC";

	#execute SQL statement
	$result = mysql_db_query($db,"$SQL",$cid) or die ("Couldn't connect to the MySQL database! prod type1");
	// Loop through the database and collect all entries.
	while ($gloop = mysql_fetch_row($result))
		{
		$groupid[$a] = "$gloop[0]";
		$groupname[$a] = "$gloop[1]";
		$groupimgyn[$a] = "$gloop[2]";
		$groupheadyn[$a] = "$gloop[3]";
		$groupimgid[$a] = "$gloop[4]";

		$a = $a + 1;
		}


// Do a loop through the products that come from the database.
// count the a var to see how many items are in the select.
// If there are more than ten do a var with link to see next 10.


//	for ($g = $start; $g < $a; $g++)
	for ($g = 0; $g < $a; $g++)
		{

//		if ($g < ($start + 10))
//			{

// ****************************************************** //
// FOLLOWING LINES EDITED BY OWEN //


	echo "<div class='product_listing'>";

		if ($groupimgyn[$g] == 2)
			{
			echo "
			<img src='http://funoverload.com/sewing/images/$groupimgid[$g]'>
			";
			}
		else
			{
			echo "
			<p>No image</p>
			";
			}

		if ($groupheadyn[$g] == 2)
			{
			$SQL = "SELECT Head_Text FROM ht_header WHERE Head_ID = '$groupid[$g]'";

			#execute SQL statement
			$result = mysql_db_query($db,"$SQL",$cid) or die ("Couldn't connect to the MySQL database! header table");
			$headertext = mysql_fetch_row($result);

			echo "
			<p>$headertext[0]</p>
			";
			}
		else
			{
			echo "
			<p>No header text</p>
			";
			}



		echo "<a class='add_to_cart_button' href='#'>Add to Cart</a>";
		echo "</div>";

// END SECTION OWEN IS EDITING NOW //

//			}
		}

/*
	if ($a > ($start + 10))
		{
		$nextsearch = $start + 10;
		echo "
		<p><center>
		<a href='./products.php?group=1&merch=$merch&product=$product&start=$nextsearch'>Next 10</a>
		</center>
		";
		}
*/


	}


if ($searchcatalog == 1)
	{

	$SQL = "SELECT Head_ID FROM ht_header WHERE Head_Text LIKE '%$keywordsearch%'";

	#execute SQL statement
//echo "$SQL<p>";
	$rsGetKeywordList = mysql_db_query($db,"$SQL",$cid) or die ("Couldn't connect to the MySQL database! header table");

	if (mysql_num_rows($rsGetKeywordList))
		{
		$listcounter = 0;
		while($GetKeywordList = mysql_fetch_row($rsGetKeywordList))
			{
			// build array for where clause

			if ($listcounter == 0)
				{
				$listingfound = "Prod_ID = '$GetKeywordList[0]'";
				$listcounter = $listcounter + 1;
				}
			else
				{
				$listingfound = $listingfound . " OR Prod_ID = '$GetKeywordList[0]'";

				$listcounter = $listcounter + 1;
				}
			}

/*
		echo "<center><b><font size=+1>$keywordsearch</font></b></center><p>
		listcounter = $listcounter<br>
		listingfound = $listingfound<p>
		";
*/


		$a = 0;
		// This first query will get the total number of referrals for this member.
		$SQL = "SELECT Prod_ID, Prod_Name, Prod_Image_yn, Prod_Header_yn, Img_ID FROM ht_product_type WHERE $listingfound ORDER BY Prod_Name ASC";
//echo "$SQL<p>";
		#execute SQL statement
		$result = mysql_db_query($db,"$SQL",$cid) or die ("Couldn't connect to the MySQL database! prod type1");
		// Loop through the database and collect all entries.
		while ($gloop = mysql_fetch_row($result))
			{
			$groupid[$a] = "$gloop[0]";
			$groupname[$a] = "$gloop[1]";
			$groupimgyn[$a] = "$gloop[2]";
			$groupheadyn[$a] = "$gloop[3]";
			$groupimgid[$a] = "$gloop[4]";

			$a = $a + 1;
			}


		for ($g = 0; $g < $a; $g++)
			{


			echo "
			<table width=90% align=center border=1>
			<tr>
			<td>$groupname[$g]</td>

			<td width=100>
			<a href='./products.php?listitem=1&merch=$merch&sub=$groupid[$g]'>Order Here<br><br><font size=-1>Click For</font><br>Styles, Sizes, Colors</a></td>
			</tr>
			";

			if ($groupheadyn[$g] == 2)
				{
				$SQL = "SELECT Head_Text FROM ht_header WHERE Head_ID = '$groupid[$g]'";

				#execute SQL statement
				$result = mysql_db_query($db,"$SQL",$cid) or die ("Couldn't connect to the MySQL database! header table");
				$headertext = mysql_fetch_row($result);

				echo "
				<tr><td>$headertext[0]</td>
				";
				}
			else
				{
				echo "
				<tr><td></td>
				";
				}


			if ($groupimgyn[$g] == 2)
				{
				echo "
				<td><img src='http://funoverload.com/sewing/images/$groupimgid[$g]'></td>
				";
				}
			else
				{
				echo "
				<td></td></tr>
				";
				}

			}
		echo "
		</table><p>
		";
		}
	else
		{
		echo " We could not find any listings with those keyword.<p> Pease try another search term.";
		}

	}














if ($listitem == 1)
	{
	$SQL = "SELECT Mer_Name FROM ht_merchant WHERE Mer_ID = '$merch'";

	#execute SQL statement
	$result = mysql_db_query($db,"$SQL",$cid) or die ("Couldn't connect to the MySQL database!");
	$mername = mysql_fetch_row($result);
	echo "<center><b><font size=+1>$mername[0]</font></b></center><p>";


	// This first query will get the total number of referrals for this member.
	$SQL = "SELECT Prod_ID, Prod_Name, Prod_Image_yn, Prod_Header_yn, Img_ID, Item_Color_Chart_yn FROM ht_product_type WHERE Prod_ID = '$sub' ORDER BY Prod_Name ASC";

	#execute SQL statement
	$result = mysql_db_query($db,"$SQL",$cid) or die ("Couldn't connect to the MySQL database! prod type2");
	// Loop through the database and collect all entries.
	$gloop = mysql_fetch_row($result);
		{
		$groupid = "$gloop[0]";
		$groupname = "$gloop[1]";
		$groupimgyn = "$gloop[2]";
		$groupheadyn = "$gloop[3]";
		$groupimgid = "$gloop[4]";
		$groupcolorchartyn = "$gloop[5]";
		}

		echo "
		<table width=90% align=center border=1>
		<tr><td colspan=2>$groupname</td></tr>
		<tr><td colspan=2>
		<b>Please allow 10 working days for processing and shipping your order.<p>
		<font color=blue>Our Returns Policy: If the items cost are between \$0.00 and \$25.00  there is a \$10.00 restocking fee.<br>
		To see the charges  for larger amounts, go to the features page.</font></b>
		</td></tr>
		";
		if ($groupheadyn == 2)
			{
			$SQL = "SELECT Head_Text FROM ht_header WHERE Head_ID = '$groupid'";

			#execute SQL statement
			$result = mysql_db_query($db,"$SQL",$cid) or die ("Couldn't connect to the MySQL database! header table");
			$headertext = mysql_fetch_row($result);

			echo "
			<tr><td width=80%>$headertext[0]</td>
			";
			}
		else
			{
			echo "
			<tr><td></td>
			";
			}


		if ($groupimgyn == 2)
			{
			echo "
			<td align=right><img src='http://funoverload.com/sewing/images/$groupimgid'></td>
			";
			}
		else
			{
			echo "
			<td></td></tr>
			";
			}

		echo "
		</table><p>
		";



// Now we need to get all items in the Items Table to display.



	if ($groupcolorchartyn == 2)
		{
		echo "
		<table width=90% align=center border=1>
		";
		$query_item_count = 0;
		// This first query will get the information from the items list for display.
		$SQL = "SELECT Item_ID, Item_Number, List_Price, Description_yn, PP_Desc FROM ht_item WHERE Item_Prod_ID = '$sub' ORDER BY Item_Number ASC";
		

		#execute SQL statement
		$result = mysql_db_query($db,"$SQL",$cid) or die ("Couldn't connect to the MySQL database! prod type 3");
		// Loop through the database and collect all entries.
		while ($iloop = mysql_fetch_row($result))
			{
			$itemid[$query_item_count] = "$iloop[0]";
			$itemnum[$query_item_count] = "$iloop[1]";
			$list[$query_item_count] = "$iloop[2]";
			$description[$query_item_count] = "$iloop[3]";
			$pp_desc[$query_item_count] = "$iloop[4]";

			// This is where you need to loop and collect data. Then do a for loop to display so that you can
			// connect to the database again.
			$query_item_count = $query_item_count + 1;
			}

		$itemloop = 0;
		for ($display_list = 0;	$display_list < $query_item_count; $display_list++)
			{
			if ($itemloop == 0)
				{
				echo "<tr>";
				}

			if ($description[$display_list] == 2)
				{
				$SQL = "SELECT Desc_Text FROM ht_item_description WHERE Desc_Item_ID = '$itemid[$display_list]'";

				#execute SQL statement
				$result = mysql_db_query($db,"$SQL",$cid) or die ("Couldn't connect to the MySQL database! header table");
				$descripttext = mysql_fetch_row($result);

				echo "
				<td width=50%><table align=left width=100%><tr>
				<td width=25>$itemnum[$display_list]</td>
				<td align=right>$descripttext[0]</td>
				";
				}
			else
				{
				echo "
				<td width=50%><table align=left width=100%><tr>
				<td width=25>$itemnum[$display_list]</td>
				<td></td>
				";
				}

//<input type=\"image\" src=\"https://www.paypal.com/images/sc-but-01.gif\" border=\"0\" name=\"submit\" alt=\"Make payments with PayPal - it's fast, free and secure!\">

			echo "
			<td width=25 align=center>$list[$display_list]</td>
			<td width=25>
<form target=\"paypal\" action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\">
<input type=\"hidden\" name=\"cmd\" value=\"_cart\">
<input type=\"hidden\" name=\"business\" value=\"john@happythoughtstoo.com\">
<input type=\"hidden\" name=\"item_name\" value=\"$pp_desc[$display_list]\">
<input type=\"hidden\" name=\"item_number\" value=\"$itemnum[$display_list]\">
<input type=\"hidden\" name=\"amount\" value=\"$list[$display_list]\">
<input type=\"hidden\" name=\"no_note\" value=\"1\">
<input type=\"hidden\" name=\"currency_code\" value=\"USD\">
<input type=\"image\" src=\"http://jhittlesewing.funoverload.com/sewing/specials/images/sclink_oh.jpg\" border=\"0\" name=\"submit\" alt=\"Make payments with PayPal - it's fast, free and secure! 1\">
<input type=\"hidden\" name=\"add\" value=\"1\">
</form>
</td>
			</tr></table></td>
			";

			$itemloop = $itemloop + 1;

			if ($itemloop == 2)
				{
				echo "</tr>";
				$itemloop = 0;
				}
			}
		if ($itemloop == 1)
			{
			echo "<td width=50%><table align=left width=100%><tr><td width=25></td><td></td><td width=25></td><td width=25></td></tr></table></td></tr>";
			}
		}
	else
		{
		echo "
		<table width=90% align=center border=1>
		";

		$query_item_count = 0;
		// This first query will get the total number of referrals for this member.
		$SQL = "SELECT Item_ID, Item_Number, List_Price, Description_yn, Item_Image_yn, Item_Image_ID, PP_Desc FROM ht_item WHERE Item_Prod_ID = '$sub' ORDER BY Item_Number ASC";

		#execute SQL statement
		$result = mysql_db_query($db,"$SQL",$cid) or die ("Couldn't connect to the MySQL database! prod type 3");
		// Loop through the database and collect all entries.
		while ($iloop = mysql_fetch_row($result))
			{
			$itemid[$query_item_count] = "$iloop[0]";
			$itemnum[$query_item_count] = "$iloop[1]";
			$list[$query_item_count] = "$iloop[2]";
			$description[$query_item_count] = "$iloop[3]";
			$imgyn[$query_item_count] = "$iloop[4]";
			$itemimgid[$query_item_count] = "$iloop[5]";
			$pp_desc[$query_item_count] = "$iloop[6]";
			$query_item_count = $query_item_count + 1;
			}

		for ($display_list = 0;	$display_list < $query_item_count; $display_list++)
			{
			echo "<tr>";

			if (($description[$display_list] == 2) && ($imgyn[$display_list] == 2))
				{
				$SQL = "SELECT Desc_Text FROM ht_item_description WHERE Desc_Item_ID = '$itemid[$display_list]'";

				#execute SQL statement
				$result = mysql_db_query($db,"$SQL",$cid) or die ("Couldn't connect to the MySQL database! header table");
				$descripttext = mysql_fetch_row($result);

				echo "
				<td width=65>$itemnum[$display_list]</td>
				<td width=100><img src=\"http://funoverload.com/sewing/images/$itemimgid[$display_list]\"></td>
				<td>$descripttext[0]</td>
				";
				}
			elseif (($description[$display_list] == 2) && ($imgyn[$display_list] == 1))
				{
				$SQL = "SELECT Desc_Text FROM ht_item_description WHERE Desc_Item_ID = '$itemid[$display_list]'";

				#execute SQL statement
				$result = mysql_db_query($db,"$SQL",$cid) or die ("Couldn't connect to the MySQL database! header table");
				$descripttext = mysql_fetch_row($result);

				echo "
				<td width=65>$itemnum[$display_list]</td>
				<td>$descripttext[0]</td>
				";
				}
			elseif (($description[$display_list] == 1) && ($imgyn[$display_list] == 2))
				{
				echo "
				<td width=65>$itemnum[$display_list]</td>
				<td><img src=\"http://funoverload.com/sewing/images/$itemimgid[$display_list]\"></td>
				";
				}
			else
				{
				echo "
				<td>$itemnum[$display_list]</td>
				";
				}

//<input type=\"image\" src=\"https://www.paypal.com/images/sc-but-01.gif\" border=\"0\" name=\"submit\" alt=\"Make payments with PayPal - it's fast, free and secure!\">


			echo "
			<td width=65 align=center>$list[$display_list]</td>
			<td width=65>

<form target=\"paypal\" action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\">
<input type=\"hidden\" name=\"cmd\" value=\"_cart\">
<input type=\"hidden\" name=\"business\" value=\"john@happythoughtstoo.com\">
<input type=\"hidden\" name=\"item_name\" value=\"$pp_desc[$display_list]\">
<input type=\"hidden\" name=\"item_number\" value=\"$itemnum[$display_list]\">
<input type=\"hidden\" name=\"amount\" value=\"$list[$display_list]\">
<input type=\"hidden\" name=\"no_note\" value=\"1\">
<input type=\"hidden\" name=\"currency_code\" value=\"USD\">
<input type=\"image\" src=\"http://jhittlesewing.funoverload.com/sewing/specials/images/sclink_oh.jpg\" border=\"0\" name=\"submit\" alt=\"Make payments with PayPal - it's fast, free and secure! 2\">
<input type=\"hidden\" name=\"add\" value=\"1\">
</form>
</td>

			</tr>
			";

			}
		}
	echo "
	</table>
	";

	}


mysql_close($cid);
echo "
</body></html>
";
?>
