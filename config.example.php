<?php
 
$domain = $_SERVER['HTTP_HOST']; 
$apath = rtrim(dirname($_SERVER['PHP_SELF']), '/\\').'/';  
$gpath = "http://$domain$apath"; 
$stripwww = true; 
 
$steamapikey = ""; // enter STEAM API key

$sql_addr = ''; // address of SQL server, eg 'yoursite.com';
$sql_user = ''; // username for SQL database eg 'you'
$sql_password = ''; // password for SQL login eg 'roawm(#*)H93wmth9vu0e49a3c'
$sql_database = ''; // name of SQL database eg 'yoursite_store';

$min_transaction = 5;
$shipping_fee = 5;

$session_timeout = 60*20;

//------------------------------------------------------------------
//
// ADMIN PERMISSIONS
//
//------------------------------------------------------------------
// adjust store stock
// add or remove items to shelf
//
define("ADMFLAG_CHGSTOCK",0x1); 
//------------------------------------------------------------------
// change user attributes, 
//   - inventory items 
//   - store credit/CASH
//   - pokeman nicknames
//
define("ADMFLAG_USER",0x2); 
//------------------------------------------------------------------
// refund purchases
//
define("ADMFLAG_REFUND",0x4);
define("ADMFLAG_RECEIPTQUERY",0x8);
//------------------------------------------------------------------
// fuck shit up
//
define("ADMFLAG_FSU",0x8);
//------------------------------------------------------------------
// master admin rank
//
define("ADMFLAG_ALL",ADMFLAG_CHGSTOCK|ADMFLAG_USER|ADMFLAG_REFUND|ADMFLAG_FSU|ADMFLAG_RECEIPTQUERY);

$admins = array( 
	array (
		"name" => "pray and spray",  // name of admin
		"id" => "76561198069264171", // 64-bit steam ID
		"flags" => ADMFLAG_ALL       // access flags
	)
); 

?>