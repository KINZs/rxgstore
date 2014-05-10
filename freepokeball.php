
<?php

require_once 'config.php';
require_once 'sql.php';
require_once 'itemdata.php';
require_once 'functions.php';
require_once 'opensession.php';


StripWWW();
OpenSession();

PrintHeader( "RXGMART", 'store.php' );

echo '<center><img src="pokemart.png" ></center>';
if( $_SESSION['loggedin'] != 1 ) {
	echo '<p>you need to sign in</p>';
} else {
	
	try {
		$result = CheckFreePokeball( $_SESSION['accountid'] );
		if( $result == 0 ) {
			echo '<p>you have been given a pokeball!</p>';
		} else {
			echo '<p>you need to wait another '. FormatDuration($result). ' before you can claim another pokeball. :)</p>';
		}
	} catch (Exception $e) {
		echo '<p>an error happened...!</p>';
	} 
} 


PrintFooter();

?>

