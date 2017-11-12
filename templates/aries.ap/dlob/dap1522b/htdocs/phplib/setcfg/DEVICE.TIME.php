<?
/* setcfg is used to move the validated session data to the configuration database.
 * The variable, 'SETCFG_prefix',  will indicate the path of the session data. */
include "/htdocs/phplib/trace.php";
include "/htdocs/phplib/xnode.php";

anchor($SETCFG_prefix."/device/time");
set("/device/time/ntp/enable",	query("ntp/enable"));
set("/runtime/device/ntp/state", "RUNNING");
set("/device/time/ntp/period",	query("ntp/period"));
set("/device/time/ntp/server",	query("ntp/server"));
set("/device/time/timezone",	query("timezone"));
set("/device/time/dst",			query("dst"));
anchor($SETCFG_prefix."/device/time/DaylightSaving");
set("/device/time/DaylightSaving/startdate/month",			query("startdate/month"));
set("/device/time/DaylightSaving/startdate/week",			query("startdate/week"));
set("/device/time/DaylightSaving/startdate/dayofweek",			query("startdate/dayofweek"));
set("/device/time/DaylightSaving/startdate/time",			query("startdate/time"));
set("/device/time/DaylightSaving/enddate/month",			query("enddate/month"));
set("/device/time/DaylightSaving/enddate/week",			query("enddate/week"));
set("/device/time/DaylightSaving/enddate/dayofweek",			query("enddate/dayofweek"));
set("/device/time/DaylightSaving/enddate/time",			query("enddate/time"));
?>
