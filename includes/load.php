<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
global $db, $p_db;

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/product.php";
require_once __DIR__ . "/functions.php";

$db = new DB(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 
$db->createTables();
$p_db = new ProductDB($db);
?>