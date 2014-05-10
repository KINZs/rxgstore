<?php


require_once 'config.php'; 
require_once 'sql.php';
require_once 'functions.php';

main();

function TimeDifferenceString($date) {

	$now = time();
	$t = $now - $date;
	
	if( $t < 0 ) {
		return "???";
	} else if( $t < 60 ) {
		return "$t second".($t==1?"":"s");
	} else if( $t < 60*60 ) {
		$t = floor($t / 60);
		return "$t minute".($t==1?"":"s");
	} else if( $t < 60*60*24 ) {
		$t = floor($t / (60*60));
		return "$t hour".($t==1?"":"s");
	} else {
		$t = floor($t / (60*60*24));
		return "$t day".($t==1?"":"s");
	}
}

function main() {
	$time = time();
	$start = $time - 60*60*24*14;
	$sql = GetSQL();
	$result = $sql->safequery( "SELECT * FROM (SELECT * FROM BBS WHERE DATE>$start ORDER BY DATE DESC LIMIT 10) T ORDER BY DATE ASC" );
	 
	 echo '<table>';
	while( $row = $result->fetch_array() ) {
		 
		echo '<tr><td>'.TimeDifferenceString($row['DATE']).' ago</td><td>'.$row['NAME'].': '.$row['CONTENT'].'</td></tr>';
	}
	echo '</table>';
}

?>