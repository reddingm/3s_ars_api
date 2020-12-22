<?php
//$serverpath =  '..';
$serverpath =  '/var/web/hosts/home_agentconnection';

require_once($serverpath."/db.php");
require_once($serverpath."/functions.php");

$query_date = date("Y-m-d", time() - 60 * 60 * 24);

$dbdata = getRawDataforPlatformExportsDate($query_date);
//die();

build_ga_file_for_ga($query_date, $dbdata);
?>

<?php
/*

Google template attached, with example data
	All lead records for prior day where Lead Status = "Matched"
	Fields for output file:

Google Click ID = "Paid_Click_ID" from Boberdoo data, with first 2 characters removed ("g_")
Conversion Name = Based on "Company Name" lookup, which we currently only have 1 partner for:
Company Name = BoomTown
Conversion Name = Lead - BT
Conversion Time = "Date Created" from Boberdoo data + 3 hours
Conversion Value = "Price" from Boberdoo data
Conversion Currency = (blank)
If the "Paid_Click_ID" field is blank, you can ignore that lead record


*/
?>