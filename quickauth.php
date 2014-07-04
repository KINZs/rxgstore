<?php

require_once 'config.php';
require_once 'functions.php';
require_once 'opensession.php';
require_once 'sql.php';

OpenSession();

if( isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == 1 ) {
	TryAuth(false);
}
else PrintHeader( 'RXGMART',"",true,true,
	'<script type="text/javascript" src="tinybox.min.js"></script>
	<script type="text/javascript" src="sprintf.min.js"></script>
	<link rel="stylesheet" href="tbstyle.css" />'); 
?>

<center><img src="http://cdn.bulbagarden.net/upload/thumb/6/6c/Unova_Pokemon_Center.png/200px-Unova_Pokemon_Center.png"></center>
<h1>RXG Center</h1>

<h3>Logging you in securely... please wait.</h3>

<?php

PrintFooter();

TryAuth();

function TryAuth( $forceLogin = true ) {
	
	$sql = GetSQL();
	$id = $sql->real_escape_string( $_GET['id'] );
	
	if( $forceLogin ) {
		$token = $sql->real_escape_string( $_GET['token'] );
		$result = $sql->safequery( "SELECT ACCOUNT FROM QUICKAUTH WHERE ID = $id AND TOKEN = $token" );
		$row = $result->fetch_array();
		$account = (int)$row[0];
		LogInUser( SteamID64FromAccountID($account) );
	}
	
	$sql->safequery( "DELETE FROM QUICKAUTH WHERE ID = $id" );
	RedirectUser( "index.php" );
}

?>