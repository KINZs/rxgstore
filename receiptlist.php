<?php

// receipt query script
//
// inputs: (GET)
//   page - which page of results to return, 0 based
//   paypalid - filter by paypalid
//   saleid - filter by rxgmart sale id
//   after - filter after this date
//   before - filter before this date
//   state - filter by payment state, currently either "OKAY" or "REFUNDED"
//   raw - return raw data in JSON format



require_once 'opensession.php';
require_once 'config.php'; 
require_once 'sql.php'; 
require_once 'functions.php';

OpenSession();
if( !UserIsAdmin() ) exit;

$data = DoQuery();
CloseSQL();

if( $data === FALSE ) {
	echo "[error!]";
	exit;
}

if( isset( $_GET['raw'] ) ) {
	header('Content-type: application/json');
	echo json_encode($data);
	exit;
}

header('Content-type: text/html');

if( $data['of'] == 0 ) {
	echo '<p>no results.</p>';
	exit;
}

$page = $data['page'];
$pages = $data['of'];
echo '<p>page '. ($page +1). ' of ' . $pages . '</p>';
echo '<table class="receiptlist">';
echo '<tr><td>sale#</td><td>date</td><td>type</td><td>state</td><td>steamid</td><td>paypal#</td><td>total</td><td>paypal</td><td>fees</td><td>settle</td><td>cash</td></tr>';
$index=1;
foreach( $data['results'] as $result ) {
	//echo "test=".$result['DATE']."<br>";
	//echo "test=".strtotime($result['DATE'])."<br>";
	$time = strftime("%m/%d/%y %H:%M:%S",strtotime($result['DATE']));
	
	
	echo '<tr id="result'.($index++).'"class="receiptentry" onclick="ReceiptClicked(this,'.$result['ID'].')">'.
		'<td>'.$result['ID'].'</td>'.
		'<td>'.$time.'</td>'.
		'<td>'.$result['TYPE'].'</td>'.
		'<td>'.$result['STATE'].'</td>'.
		'<td><a target="_blank" href="http://steamcommunity.com/profiles/'.$result['STEAM'].'">'.$result['STEAM'].'</a></td>'.
		'<td>'.$result['PPSALEID'].'</td>';
		
	if( $result['TYPE'] == 'CASH' ) {
		echo '<td> </td>'.
			'<td>'.FormatPrice($result['PAYPAL']).'</td>'.
			'<td>'.FormatPrice($result['FEES']).'</td>'.
			'<td>'.FormatPrice($result['SETTLE']).'</td>'.
			'<td>'.FormatPrice($result['CASH']).'</td>';
	} else {	
		echo '<td>'.FormatPrice($result['TOTAL']).'</td>'.
			'<td> </td>'.
			'<td> </td>'.
			'<td> </td>'.
			'<td> </td>';
	}
		
	echo '</tr>';
}
echo '</table>';

if( $page+1 > 1 ) {
	echo '<button id="prevpage" onclick="javascript:PrevPage()">prev</button>';
}
if( $page+1 < $pages ) {
	echo '<button id="nextpage" onclick="javascript:NextPage()">next</button>';
}

function DoQuery() {
	$resultsperpage =10;

	try {
		$sql = GetSQL();
		
		$query = "SELECT SQL_CALC_FOUND_ROWS ID,DATE,TYPE,STATE,STEAM,TOTAL,PPSALEID,PAYPAL,FEES,SETTLE,CASH FROM RECEIPTS WHERE";
		if( isset( $_GET['paypalid'] ) ) {
			$query .= " PPSALEID='" . $sql->real_escape_string( $_GET['paypalid'] ) . "' AND";
		}
		if( isset( $_GET['saleid'] ) ) {
			$query .= " ID=" . (int)$_GET['saleid'] . " AND";
		}
		if( isset( $_GET['after'] ) ) {
			$time = (int)strtotime( $_GET['after'] ) or die();
			$query .= " DATE>'" .  $sql->real_escape_string($_GET['after']) . "' AND";
		}
		if( isset( $_GET['before'] ) ) {
			
			
			$query .= " DATE<'" .  $sql->real_escape_string($_GET['before']) . "' AND";
		}
		if( isset( $_GET['type'] ) ) {
			$query .= " TYPE='" .  $sql->real_escape_string($_GET['type']) . "' AND";
		}
		if( isset( $_GET['state'] ) ) {
			$query .= " STATE='" . $sql->real_escape_string( $_GET['state'] ) . "' AND";
		}
		if( isset( $_GET['steamid'] ) ) {
			$query .= " STEAMID='" . $sql->real_escape_string( $_GET['steamid'] ) . "' AND";
		}
		
		// remove last word (trailing AND, or WHERE if no conditions set)
		$query = substr( $query, 0, strrpos($query,' ') );
		$query .= " ORDER BY DATE DESC";
		$page = isset( $_GET['page'] ) ? (int)$_GET['page']:0;
		if( $page == 0 ) {
			$query .= " LIMIT 10";
		} else {
			$query .= " LIMIT ".  ($page * $resultsperpage) . ",10";
		}
		//echo '<span style="font-family:courier new,monospace; font-size: 10px">'.$query.'</span>';
		$result = $sql->safequery( $query );
		
		$data = array();
		while( $row = $result->fetch_array() ) {
			$data[] = $row;
				
		}
		$result = $sql->safequery( "SELECT FOUND_ROWS()" );
		$row = $result->fetch_array();
		
		$rv = array (
			"page" => $page,
			"of" => (int)floor(($row['FOUND_ROWS()']+$resultsperpage-1)/$resultsperpage),
			"results" => $data
			);
		return $rv;
		//echo json_encode( $rv );
		
	} catch( Exception $e ) {
		echo $e->getMessage();
		return FALSE;
	}	
}

?>