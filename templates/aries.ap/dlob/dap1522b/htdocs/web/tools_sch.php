HTTP/1.1 200 OK

<?
/* The variables are used in js and body both, so define them here. */
$SCH_MAX_COUNT = query("/schedule/max");
if ($SCH_MAX_COUNT == "") $SCH_MAX_COUNT = 10;

/* necessary and basic definition */
$TEMP_MYNAME	= "tools_sch";
$TEMP_MYGROUP	= "maintenance";
$TEMP_STYLE		= "complex";
include "/htdocs/webinc/templates.php";
?>