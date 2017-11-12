test namespace
--------------
<?

$_GLOBALS["TEST_GLOBALS"] = "TEST_GLOBALS";
$_SERVER["TEST_SERVER"] = "TEST_SERVER";
$_GET["TEST_GET"] = "TEST_GET";
$_POST["TEST_POST"] = "TEST_POST";
$_ENV["TEST_ENV"] = "TEST_ENV";


if ($TEST_GLOBALS == "TEST_GLOBALS")			echo "Pass 1\n";	else echo "Fail 1\n";
if ($TEST_SERVER == "")							echo "Pass 2\n";	else echo "Fail 2\n";
if ($_SERVER["TEST_SERVER"] == "TEST_SERVER")	echo "Pass 3\n";	else echo "Fail 3\n";
if ($TEST_GET == "")							echo "Pass 4\n";	else echo "Fail 4\n";
if ($_GET["TEST_GET"] == "TEST_GET")			echo "Pass 5\n";	else echo "Fail 5\n";
if ($TEST_POST == "")							echo "Pass 6\n";	else echo "Fail 6\n";
if ($_POST["TEST_POST"] == "TEST_POST")			echo "Pass 7\n";	else echo "Fail 7\n";
if ($TEST_ENV == "")							echo "Pass 8\n";	else echo "Fail 8\n";
if ($_ENV["TEST_ENV"] == "TEST_ENV")			echo "Pass 9\n";	else echo "Fail 9\n";

echo "Please execute './xmldbc -S ./testxml_usock -P test/test_namespace.php -V _SERVER_XXXX=xxxx -V _POST_XXXX=xxxx'\n";

if ($_SERVER["XXXX"] == "xxxx")	echo "Pass10\n";    else echo "Fail10\n";
if ($_POST["XXXX"] == "xxxx")	echo "Pass11\n";    else echo "Fail11\n";


?>
