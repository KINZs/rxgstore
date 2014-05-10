
<html>
<body> 

<pre style="background-color:#558; font-size:24px; color:white; padding:8px; border: 2px dashed #eee">

<?php

require_once 'config.php'; 
	
function CreateSQLTables() {
	echo "creating SQL tables...\r\n";
	
	echo "connecting to database...\r\n";
	global $sql_addr,$sql_user,$sql_password,$sql_database;
	$con = mysqli_connect( $sql_addr,$sql_user,$sql_password,$sql_database);
	
	if( mysqli_connect_errno($con) ) {
		echo "error: could not connect to database\r\n";
		return FALSE;
	}
	
	echo "creating table \"INFO\"\r\n";
	//$result = mysqli_query( $con, "DROP TABLE INFO" );
	//$result = mysqli_query( $con, "CREATE TABLE IF NOT EXISTS INFO ( ACCOUNTID INT PRIMARY KEY, TYPE VARCHAR(64), ELEMENT VARCHAR(64), LENGTH SMALLINT, WEIGHT MEDIUMINT, HABITAT VARCHAR(64), DESCRIPTION TEXT );" );
	//echo "result: $result\r\n";
	//if( !$result ) {
	//	echo "error: " . mysqli_error($con) . "\r\n";
	//}
	
	echo "creating table \"CAPTURES\"\r\n";
	//$result = mysqli_query( $con, "CREATE TABLE IF NOT EXISTS CAPTURES ( ID BIGINT UNSIGNED PRIMARY KEY, ACCOUNTID INT, TARGET INT, TIME BIGINT UNSIGNED, NICKNAME VARCHAR(64) );" );
	//echo "result: $result\r\n";
	//if( !$result ) {
	//	echo "error: " . mysqli_error($con) . "\r\n";
	//}
	
	mysqli_close( $con );
	
}
function Test1() {
	echo "setting info for PRAY&SPRAY\n";
	global $sql_addr,$sql_user,$sql_password,$sql_database;
	$con = mysqli_connect( $sql_addr,$sql_user,$sql_password,$sql_database);
	
	if( mysqli_connect_errno($con) ) {
		echo "error: could not connect to database\r\n";
		return FALSE;
	}
	echo "creating table \"CAPTURES\"\r\n";
	$result = mysqli_query( $con, 
		"REPLACE INTO INFO VALUES( 108998443, \"ACEVENTURA\" , \"Potassium\", 26, 72, \"GULF COAST\",\"This rare pokeman is often found spending his free time coding invasive server plugins such as this one that allows players to capture each other with pokeballs\" )" );
	echo "result: $result\r\n";
	if( !$result ) {
		echo "error: " . mysqli_error($con) . "\r\n";
	}
	
	mysqli_close( $con );
	
}
	
if( $_GET['op'] == "create tables" ) {
	CreateSQLTables();
} else if( $_GET['op'] == "test1" ) {
	Test1();
}

?>

</pre>

<form method="get">
<input type="submit" name="op" value="create tables">
<input type="submit" name="op" value="test1">
</form>
</body>
</html>