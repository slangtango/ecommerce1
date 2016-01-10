<?
include ("variable.php");

$email = $_POST['email'];
$nextpage = $_POST['nextpage'];
$casual = $_POST['casual'];
$craft = $_POST['craft'];
$seam = $_POST['seam'];
$small = $_POST['small'];
$large = $_POST['large'];
$man = $_POST['man'];
$other = $_POST['other'];
$discount = $_POST['discount'];
$wholesale = $_POST['wholesale'];
$sender = $_POST['sender'];
$enter = $_POST['enter'];
$mailing = $_POST['mailing'];

$today = date("Y-m-d");
$visit_time = date("H:i:s");

/*
echo "
email = $email<br>
nextpage = $nextpage<br>
casual = $casual<br>
craft = $craft<br>
seam = $seam<br>
small = $small<br>
large = $large<br>
man = $man<br>
other = $other<br>
discount = $discount<br>
wholesale = $wholesale<br>
sender = $sender<br>
enter = $enter<br>
mailing = $mailing

";
die();
*/

if(!ereg("^.+@.+\\..+$", $email))
	{
	$erroremail = 1;
	redo_page($email,$nextpage,$sender,$enter,$casual,$craft,$seam,$small,$large,$man,$other,$discount,$wholesale,$mailing);
	exit();
	}
	
if ($_POST)
	{
	if (empty($_POST['email']))
		{
		$erroremail = 1;
		redo_page($email,$nextpage,$sender,$enter,$casual,$craft,$seam,$small,$large,$man,$other,$discount,$wholesale,$mailing);
		exit();
		}
	else
		{
		$email = $_POST['email'];
		$email = preg_replace('{ [^ \w \. - _  @ ] }x', '', $email );

    		// check if e-mail address syntax is valid
		if(!ereg("^.+@.+\\..+$", $email))
			{
			$erroremail = 1;
			redo_page($email,$nextpage,$sender,$enter,$casual,$craft,$seam,$small,$large,$man,$other,$discount,$wholesale,$mailing);
			exit();
			}
		//echo "$email";	
		}
	//echo "$email - stage 3. printing out good.";
	//die();

	}		



//	header("Location: $nextpage");
//	die();

//$db = 'sewing'; // mySQL database name
//$user = 'jhittlesr'; // mySQL username
//$pw = 'blessing'; // mySQL password

$s = ($SERVER_PROTOCOL == "HTTP/1.0") ? "Pragma: no-cache" : "Cache-Control: no-cache, must-revalidate";
 header($s);



if ($mailing == 1)
	{
//echo "Yes";
//	die();

	/* Connect to DB */
	$cid = mysql_connect($host,$user,$pw);
	if (!$cid) { echo("ERROR: " . mysql_error() . "\n");	}
	mysql_select_db ($db);


		// This query will pull the type of member, last date logged in,  and the user id on login.
		$SQL = "SELECT Visitor_email FROM visitor_log WHERE Visitor_email = '$email'";

		#execute SQL statement
		$rsGetEmailCheck = mysql_query("$SQL",$cid) or die ("Couldn't connect to the MySQL database!email check");

		$GetEmailCheck = mysql_fetch_row($rsGetEmailCheck);
		if (mysql_num_rows($rsGetEmailCheck))
			{
			header("Location: $nextpage");
			die();
			}

		else
			{
			# setup SQL statement
			$SQL = " INSERT INTO visitor_log ";
			$SQL = $SQL . " (Visitor_date,Visitor_time,Visitor_enter,Visitor_email,Visitor_casual,Visitor_craft,Visitor_seam,Visitor_small,Visitor_large,Visitor_man,Visitor_other,Visitor_discount,Visitor_wholesale) VALUES ";
			$SQL = $SQL . " ('$today','$visit_time','$sender','$email','$casual','$craft','$seam','$small','$large','$man','$other','$discount','$wholesale') ";

			//echo "$SQL<p>";

			#execute SQL statement
			$result = mysql_query("$SQL",$cid) or die ("Could not create record. Please notify support at support@jhittlesewing.funoverload.com");


			header("Location: $nextpage");
			die();
			}
	}
else
	{
//echo "No Mailing For This user";
	header("Location: $nextpage");
	die();
	}

function redo_page($email,$nextpage,$sender,$enter,$casual,$craft,$seam,$small,$large,$man,$other,$discount,$wholesale,$mailing)
	{

	if ($casual == 1)
		{
		$casual = checked;
		}
	if ($craft == 1)
		{
		$craft = checked;
		}
	if ($seam == 1)
		{
		$seam = checked;
		}
	if ($small == 1)
		{
		$small = checked;
		}
	if ($large == 1)
		{
		$large = checked;
		}
	if ($man == 1)
		{
		$man = checked;
		}
	if ($other == 1)
		{
		$other = checked;
		}
	if ($discount == 1)
		{
		$discount = checked;
		}
	if ($wholesale == 1)
		{
		$wholesale = checked;
		}





echo "
<html>
<head><title>Wholesale sewing and quilting supplies</title></head>

<body background=\"./images/mat_gray.gif\">


<table width=75% align=\"center\" border=\"1\" bgcolor=white bordercolor=\"blue\" cellspacing=\"2\" cellpadding=\"2\">
<tr><td bgcolor=\"blue\" colspan=2 align=center><font color=\"white\" size=+2><b>Processing Error</b></font></td></tr>  
<tr>
<td bgcolor=white>
<b>
Please re-enter your e-mail address.<p>
<form method=POST action=\"./log.php\">
<input type=hidden name=nextpage value=$nextpage>
<input type=hidden name=enter value=$enter>
<input type=hidden name=sender value=$sender>
<input type=hidden name=mailing value=1>
Email:<input type=\"text\" name=email size=30 maxlength=80 value=$email>
<p>
<input type=\"checkbox\" name=casual value=1 $casual>casual sewing<br>
<input type=\"checkbox\" name=craft value=1 $craft>craft or quilting group<br>
<input type=\"checkbox\" name=seam value=1 $seam>Seamstress or tailor<br>
<input type=\"checkbox\" name=small value=1 $small>small retailer<br>
<input type=\"checkbox\" name=large value=1 $large>large retailer<br>
<input type=\"checkbox\" name=man value=1 $man>manufacturer of sewn products<br>
<input type=\"checkbox\" name=other value=1 $other>Other<br>
<p>
<input type=\"checkbox\" name=discount value=1 $discount>I am interested in discount prices<br>
<input type=\"checkbox\" name=wholesale value=1 $wholesale>I am interested in wholesale prices<br>
<p>
</td>
<td valign=top>
<b><center><font size=+1>Terms Of Use</font></center><p>
I agree that I am engaged in a sewing related activity 
and have reason to gain access to confidential 
wholesale pricing.


<p>


By entering this site I agree to receive updates and featured materials through e-mail each week.

<p>
</b>
</td>
</tr>
<tr>
<td colspan=2 align=center>
<b>By entering this website I agree to the terms as described on the right.</b><p>
<br>
<INPUT TYPE=submit VALUE=SUBMIT>
</form>

<p>
</td></tr>
</table>
<p><p>
<center>
<!-- BEGIN LINKEXCHANGE CODE --> 
<iframe src=\"http://leader.linkexchange.com/1/X1532953/showiframe?\" width=468 height=60 marginwidth=0 marginheight=0 hspace=0 vspace=0 frameborder=0 scrolling=no>
<a href=\"http://leader.linkexchange.com/1/X1532953/clickle\" target=\"_top\"><img width=468 height=60 border=0 ismap alt=\"\" src=\"http://leader.linkexchange.com/1/X1532953/showle?\"></a></iframe><br><a href=\"http://leader.linkexchange.com/1/X1532953/clicklogo\" target=\"_top\"><img src=\"http://leader.linkexchange.com/1/X1532953/showlogo?\" width=468 height=16 border=0 ismap alt=\"\"></a><br>
<!-- END LINKEXCHANGE CODE -->
</center>
</body>
</html>
";



	}
?>