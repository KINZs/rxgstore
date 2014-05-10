<?php


require_once 'config.php'; 
require_once 'sql.php';
require_once 'functions.php';
require_once 'opensession.php'; 

StripWWW();
OpenSession();
AdminCheckpoint( ADMFLAG_RECEIPTQUERY ); 

PrintHeader( "admin => stats" );

ShowStats();

function PrintMonthStats( $offset ) {
	
	$period_start = strftime( "%Y-%m-%d",mktime( 0,0,0,date("n")+$offset,1 ));
	$period_end = strftime( "%Y-%m-%d",mktime( 0,0,0,date("n")+$offset+1,1 ));
	
	echo "<h2>$period_start thru $period_end ";
	if( $offset == 0 ) echo '(this month)';
	else if( $offset == -1 ) echo '(last month)';
	else if( $offset < -1 ) echo '('.(-$offset).' months ago)';
	echo "</h2>";
	$sql = GetSQL();
	$result = $sql->safequery( "SELECT SUM(TOTAL), SUM(SETTLE)  FROM RECEIPTS WHERE DATE >= '$period_start' AND DATE < '$period_end' AND STATE='OKAY'" );
	$row = $result->fetch_array();
	
	echo '<table class="stats">'; 
	echo '<tr><td>total CASH spent</td><td>$'.FormatPrice($row['SUM(TOTAL)']).'</td></tr>';
	echo '<tr><td>total PAYPAL income</td><td>$'.FormatPrice($row['SUM(SETTLE)']).'</td></tr>';
	echo '</table>';
	
	
}

function ShowStats() {

	for( $i = 0; $i > -4; $i-- ) {
		PrintMonthStats( $i );
	}
	
	echo '<hr><br>';
	echo "<h2>PAYPAL income chart (days) ";
	echo '<img border="2" src="stats_graph.php">';
	
}

PrintFooter();

?>
