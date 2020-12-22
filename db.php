<?php
$servername = "127.0.0.1";
$username = "attribution";
$password = "osidfosdi@#ew}{we290do";
$dbname = "3sh_powellandsons_api";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

//$web_root = '/Users/reddingm/Code/localdev/crisp-attr-hm';

?>