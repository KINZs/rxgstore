<?php

header('Content-type: text/html');

if( !isset( $_GET['id'] ) ) exit;

require_once 'config.php';
require_once 'opensession.php';
require_once 'functions.php';
require_once 'transaction.php';
require_once 'sql.php';
 
OpenSession();
if( !UserIsAdmin() ) exit;

FormatReceipt();
function FormatReceipt() {
	$sql = GetSQL();
	
	$result = $sql->safequery( "SELECT * FROM RECEIPTS WHERE ID=".(int)$_GET['id'] );
	$row = $result->fetch_array();
	
	if( $row['NOTE'] != "" ) {
		echo '<p style="text-decoration:underline"><b>ADMIN NOTE ATTACHED:</b> ' . $row['NOTE'] . '</p>';
	}
	if( $row['STATE'] == 'REFUNDED' )  {
		echo '<p><span style="color:#008800; font-weight:bold">THIS TRANSACTION WAS REFUNDED</span></p>';
	}
	
	if( $row['TYPE'] == 'ITEMS' ) {
		$x = unserialize( $row['TRANSACTION'] );
		$x->saleid = $row['ID'];
		PrintOutReceipt( $x );
	} else if( $row['TYPE'] == 'CASH' ) {
		
	
		echo '<p>CASH transaction</p>';
		echo '<table>';
		echo '<tr><td>PAYMENT AMOUNT</td><td>$'.FormatPrice($row['PAYPAL']).'</td></tr>';
		echo '<tr><td>PAYPAL FEES</td><td>$'.FormatPrice($row['FEES']).'</td></tr>';
		echo '<tr><td>AMOUNT MINUS FEES</td><td>$'.FormatPrice($row['SETTLE']).'</td></tr>';
		echo '<tr><td>CASH RECEIVED BY PLAYER</td><td>$'.FormatPrice($row['CASH']).'</td></tr>';
	} else {
		echo 'UNKNOWN RECEIPT TYPE!';
	}
	
	
	
	
	 
	
} 


?>
