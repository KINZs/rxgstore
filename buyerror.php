<?php


require_once 'config.php';
require_once 'sql.php';
require_once 'transaction.php';
require_once 'functions.php';
require_once 'opensession.php';
require_once 'itemdata.php';
require_once 'paypal.php';

StripWWW();
OpenSession();

PrintHeader( "RXGMART", 'store.php' );

$reason = isset($_GET['error'])?$_GET['error']:"";
echo '<h1>error</h1>';

if( $reason == "LOGIN" ) {
	echo '<p>your session expired, please log in again</p>';
} else if( $reason == "OUTOFSTOCK" ) {
	echo '<p>one or more items desired are out of stock.</p>';
} else {
	echo '<p>an error occurred, please try again later</p>';
}

PrintFooter();

?>
