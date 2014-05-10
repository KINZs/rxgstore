<?php

require_once 'config.php';
require_once 'functions.php';
require_once 'opensession.php';


StripWWW();
OpenSession();

PrintHeader( "RXGMART", 'store.php' );

if( isset($_SESSION['buycash']) ) {
	unset($_SESSION['buycash']);
	echo "<p>your transaction has been cancelled!</p>";
} else {
	echo "<p></p>";
}
PrintFooter();

?>
