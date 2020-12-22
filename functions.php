<?php

function truncateimportdata($yesterday){
	global $conn;
 
  $truncate_sql = "TRUNCATE TABLE import_tmp";
  $result = $conn->query($truncate_sql);
  
  insert_task_completion('truncated_import_table', '', $yesterday);
  
}
  
function loaddatafromfile($file_loc, $file_date) {
	global $conn;

	$file = 'real_estate_leads-'.$file_date.'_'.$file_date.'.csv';
	$full_file_path = $file_loc.$file;
	//echo $full_file_path."\n";
	//die();
	$sql = "LOAD DATA LOCAL INFILE '".$full_file_path."' INTO TABLE import_tmp FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' LINES TERMINATED BY '\n' IGNORE 1 LINES ";
	$result = $conn->query($sql);

	insert_task_completion('imported_csv', $conn->affected_rows, $file_date);
	//die();
}  
  
function insert_task_completion($action,$record_count,$query_date){
	global $conn;

	$sql = "insert into tasks set";
	$sql .= " `action` = '".$action."',";
	$sql .= " `record_count` = '".$record_count."',";
	$sql .= " `query_date` = '".$query_date."'";
	//echo $sql;
	//die();  

	$result = $conn->query($sql);
}

function remove_pii_fromleads($query_date){
	global $conn;
		
	$sql = "update import_tmp set  First_Name = '', Last_Name = '', Address = '', Primary_Phone = '', Email = ''";
		
	$result = $conn->query($sql);

	insert_task_completion('cleared_lead_pii', '', $query_date);
}

function migrate_tmp_to_main($import_table,$main_table,$query_date){
  global $conn;

  //move to prod table
	$source_fields = "'".$query_date."',  
	`Transaction Date`,
  `Partner ID`,  `Partner Name`,  `Company Name`,  `Price`,  `Lead Cost`,  `Times Sold`,  `Lead ID`,  `Lead Type ID`,  `Date Created`,
  `Refunds`,  `Lead Refund Approval Date`,  `Lead Refund Reason`,  `Lead Refund Remarks`,  `Lead Refund Comments`,  `Referrer`,  `loci`,
  `locp`,  `Match_With_Partner_ID`,  `Test_Lead`,  `Return_Best_Price`,  `IP_Address`,  `SRC`,  `Landing_Page`,
  `Sub_ID`,  `Pub_ID`,  `Google_Click_ID`,  `Optout`,  `Unique_Identifier`,  `User_Agent`,
  `Masked_Trusted_Form_URL`,  `TCPA_Consent`,  `TCPA_Language`,
  `Trusted_Form_URL`,  `LeadiD_Token`,  `First_Name`,  `Last_Name`,  `Address`,  `City`,
  `State`,  `Zip`,  `Primary_Phone`,  `Secondary_Phone`,  `Email`,  `Home_Type`,
  `Home_Price`,  `Mortgage_Approval`,  `Buying_And_Selling`,
  `Type_of_Lead`,  `Keyword`,  `Incoming_Project`,  `Project`,  `timing`,  `location`,  `Homeowner`,  `Credit_Rating`,
  `Comments`,  `Sold by Cherry Picker`,  `Filter Set ID`,  `Filter Set Name`,  `Lead Price Total`,  `Lead Status`";
  
  
  $dest_fields = "  `query_date`,
  `transaction_date`,  `partner_id`,
  `partner_name`,  `company_name`,  `price`,
  `lead_cost`,  `times_sold`,  `lead_id`,
  `lead_type_id`,  `date_created`,  `refunds`,
  `lead_refund_approval_date`,  `lead_refund_reason`,  `lead_refund_remarks`,
  `lead_refund_comments`,  `referrer`,  `loci`,
  `locp`,  `match_with_partner_id`,  `test_lead`,  `return_best_price`,  `ip_address`,
  `src`,  `landing_page`,  `sub_id`,  `pub_id`,  `google_click_id`,  `optout`,  `unique_identifier`,  `user_agent`,
  `masked_trusted_form_url`,  `tcpa_consent`,
  `tcpa_language`,  `trusted_form_url`,  `leadid_token`,
  `first_name`,  `last_name`,  `address`,
  `city`,  `state`,  `zip`,  `primary_phone` ,
  `secondary_phone`,  `email`,  `home_type`,  `home_price`,
  `mortgage_approval`,  `buying_and_selling`,  `type_of_lead`,
  `keyword`,  `incoming_project`,  `project`,
  `timing`,  `location`,  `homeowner`,  `credit_rating`,  `comments`,
  `sold_by_cherry_picker`,  `filter_set_id`,
  `filter_set_name`,  `lead_price_total`,  `lead_status`  ";


  //$sql_migrate = "INSERT INTO ".$table." SELECT * FROM ".$tmp_table;
  //$migrate_fields = "action_date,action_id,promo_code,Subid1,Subid2,Subid3,Sharedid,Campaign,action_tracker,Status,status_detail,sale_amount,Payout,Tax,Referring_URL,query_date,timestamp";
  $sql_migrate = "INSERT INTO ".$main_table." (".$dest_fields.") SELECT ".$source_fields." FROM ".$import_table;

  $result_migrate  = $conn->query($sql_migrate);
  
  insert_task_completion('migrated_to_main', $conn->affected_rows, $query_date);
  
  //echo $sql_migrate."\n";

}

function getRawDataforPlatformExportsDate($query_date){
	global $conn;

	$sql = "select * from leads where query_date = '".$query_date."' and lead_status = 'Matched' and google_click_id != ''";
		
	$result = $conn->query($sql);	
	//echo $sql."\n\n";
	//die();
	return $result;
	
}


function build_ga_file_for_ga($query_date,$dbdata){
		//matched for GA
		if ($dbdata->num_rows > 0) {
		
			header("Content-type: text/csv");
			header("Content-disposition: attachment; filename=ga-yesterday.csv");
			echo "Parameters:TimeZone=America/New_York;\n";
			echo "\"Google Click ID\",\"Conversion Name\",\"Conversion Value\",\"Conversion Time\",\"Conversion Currency\"\n";
		

			while($row = $dbdata->fetch_assoc()) {
				$thisdate = $row["date_created"];
				$format = "Y-m-d H:i:s";
				$modified_date = date($format, strtotime("$thisdate + 3 hours"));
				
				echo "\"".strip_gclid($row["google_click_id"])."\",\"".determine_conversion_name($row["company_name"])."\",\"".$modified_date."\",\"".$row["price"]."\",\"\"\n";

			}

		}else{
			header("Content-type: text/csv");
			header("Content-disposition: attachment; filename=ga-yesterday.csv");
			echo "Parameters:TimeZone=America/New_York;\n";
			echo "\"Google Click ID\",\"Conversion Name\",\"Conversion Value\",\"Conversion Time\",\"Conversion Currency\"\n";
		}
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

	
}

function strip_gclid($gclid){

	$prefixes = array("g_1_", "g_2_", "g_3_", "g_4_", "b_1_", "b_2_", "b_3_", "b_4_", "g_");
	$cleaned_gclid = str_replace($prefixes, "", $gclid);

	return $cleaned_gclid;
}

function determine_conversion_name($partner_name){
	global $conn;
	
	$sql = "select conversion from conversion_lookups where company_name = '".$partner_name."' LIMIT 1";
	//echo $sql;
	//die();
	$result = $conn->query($sql);
	
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			return $row["conversion"];
		}
	}


}

?>