<?php
	
require_once 'config.php';
require_once 'sql.php';
require_once 'itemdata.php';
require_once 'functions.php';
require_once 'opensession.php';

StripWWW();
OpenSession();

AdminCheckpoint(ADMFLAG_CHGSTOCK);

PrintHeader( "admin center => restock", 'admin.php' );

echo '<center><img src="bob.png" ></center>';
echo '<h1>pixel warehouse</h1>';
echo '<br>adding items...';
foreach( $_GET as $key => $value ) {
	if( substr( $key, 0, 5 ) == "item_" ) {
		$key = substr( $key, 5 );
		if( isset( $item_data2[$key] ) ) {
			if( $value == 0 ) continue;
			AddItemStock( $key, $value );
			echo $value .' '.$item_data2[$key][$value == 1?'name':'plural'];
		}
	}
}
echo '<br><br>done!';


PrintFooter();


?>
