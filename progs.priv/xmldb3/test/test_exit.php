PHP: testing exit
========================================
<?
function test_exit()
{
	echo "PASS !!\n";
	exit;
	echo " FAIL !\n";
}

echo "PASS !\n";
test_exit();
echo "FAIL !!\n";

?>
