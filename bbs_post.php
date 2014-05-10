<?php

require_once 'config.php';
require_once 'sql.php';
require_once 'functions.php';
require_once 'opensession.php';

StripWWW();
OpenSession();
ProcessLogin();

if( !$_SESSION['loggedin'] ) die("NO");
if( !isset( $_POST['content'] ) ) die( "NO" );

PostBBS();

function PostBBS() {
	$sql = GetSQL();
	
	$userdata = GetUserData( $_SESSION['steamid'] );
	if( !$userdata->personaname ) die( "NO" );
	$name = $sql->real_escape_string($userdata->personaname);
	
	$content = $_POST['content'];	
	$content = substr( $content, 0, 256 );
	$account = $_SESSION['accountid'];
	$date = time();
	
	$content = $sql->real_escape_string($content);
	
	
	$result = $sql->safequery( "INSERT INTO BBS (DATE,NAME,ACCOUNT,CONTENT) VALUES ($date, '$name', $account, '$content')" );
	if( !$result ) die("NO");
	$sql->close();
}

?>