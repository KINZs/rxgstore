<?php

header('Content-type: text/plain');

require_once 'config.php';
require_once 'functions.php';
require_once 'opensession.php';
require_once 'sql.php';

try {
	OpenSession();
	Main();
} catch (Exception $e) {
		
}

function Main() {

	if( !isset($_SESSION['loggedin']) || !$_SESSION['loggedin'] ) exit;

	if( !isset($_GET['id']) || $_GET['id'] == "" ) exit;
	if( !isset($_GET['nick']) || $_GET['nick'] == "" ) exit;
	$id = $_GET['id'];
	$nick = $_GET['nick'];
	$info = GetCapture( $id );
	if( $info === FALSE ) exit;
	$steamid = SteamID64FromAccountID($info['ACCOUNTID']);
	if( !UserAdmin() && !UserIsSteamID(steamid) ) exit;
	
	$info['NICKNAME'] = $nick;
	SaveCapture( $info );
	
	echo 'OK';
	
}

?>
