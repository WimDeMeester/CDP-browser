<?php
// The file with all the database settings
require_once "../lib/setup/databaseInfo.php";

// The library to work with the mysql database
require_once "../lib/database.php";

$objDatabase=new Database();

echo "Make REFTYPE a MULTILIST (was a LIST before).\n";
$sql = "UPDATE metadata SET valueType=\"MULTILIST\" WHERE id=\"REFTYPE\"";
$objDatabase->execSQL($sql);
?>
