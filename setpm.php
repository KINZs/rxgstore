<?php

	require_once 'config.php';
	require_once 'functions.php';
	require_once 'opensession.php';
	require_once 'sql.php';

		
	StripWWW();
	OpenSession();
	ProcessLogin();
	PrintHeader( 'pokemandex => save', "dex.php" ); 
?>

<center><img src="dexter.png" ></center>

<?php
	if( !isset( $_SESSION['EDITPM'] ) ) {
		echo 'an error occurred...';
	} else {
		$pokeman = $_SESSION['EDITPM'];
		
		try {
		
			SetPokemanInfo( $pokeman['ACCOUNTID'], $_GET['TYPE'], $_GET['ELEMENT'], $_GET['LENGTH'], $_GET['WEIGHT'], $_GET['HABITAT'], $_GET['DESCRIPTION'] );
			echo "<h1>data saved</h1>";
			echo "<p>good job, i think</p>";
			echo "<p><a href=\"dex.php?id=http://steamcommunity.com/profiles/" . SteamID64FromAccountID( $pokeman['ACCOUNTID'] ) . "\">follow me</a></p>";
		} catch( Exception $e ) {
			echo 'an error occurred....';
		}
		
		unset($_SESSION['EDITPM']);
		
 
	}
?>

<?php

PrintFooter();

?>

