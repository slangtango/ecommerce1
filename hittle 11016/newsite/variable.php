<?
if (1 == 2)
	{
	echo "<center><b><font size=+1>";
	echo "We are currently down for maintainence";
	echo "</font></b></center>";
	exit ();
	}
$db = 'chff123_devtest-15'; // mySQL database name
$user = 'chff123_devadmin'; // mySQL username
$pw = 'Sew1ngadmin'; // mySQL password
$mysql_access = mysql_connect("localhost", $user, $pw);
?>
