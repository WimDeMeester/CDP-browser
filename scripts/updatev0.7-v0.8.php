<?php
// The file with all the database settings
require_once "../lib/setup/databaseInfo.php";

// The library to work with the mysql database
require_once "../lib/database.php";

$objDatabase=new Database();

echo "Make the possible values of the metadata 64 character long.\n";

$sql = "ALTER TABLE metadata MODIFY value VARCHAR(64)";
$objDatabase->execSQL($sql);
?>