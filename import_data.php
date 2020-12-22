<?php

  require_once("db.php");
  require_once("functions.php");
  
  $file_loc = "/home/leadreports/uploads/";
  $yesterday = date("Y-m-d", time() - 60 * 60 * 24);

	//echo $yesterday;
	//die();

  truncateimportdata($yesterday);
  loaddatafromfile($file_loc,$yesterday);
  remove_pii_fromleads($yesterday);
  
  migrate_tmp_to_main('import_tmp','leads',$yesterday);
  
?>