<?php

require_once 'config.php';
require_once 'functions.php';
require_once 'opensession.php';
require_once 'sql.php';
require_once 'itemdata.php';
require_once 'transaction.php';

StripWWW();
OpenSession(); 
ProcessLogin();
PrintHeader( 'RECENT PURCHASES' );

?>
<h1>Recent Purchases</h1>

<?php

$sql = GetSQL();
$query = "SELECT ID,DATE,STEAM,TOTAL,TRANSACTION FROM RECEIPTS WHERE TYPE = 'ITEMS' AND STATE = 'OKAY' ORDER BY DATE DESC LIMIT 10";
$result = $sql->safequery( $query );

$data = array();
$steamid_list = "";

while( $row = $result->fetch_array() ) {
	$data[] = $row;
	$steamid_list .= $row['STEAM'] . ",";
}

$steamid_list = substr($steamid_list,0, strlen($steamid_list)-1); // remove trailing comma
$users = GetMultiUserData( $steamid_list );

$names = array();
$av = array();

foreach( $users as $user ) { 
	$names[$user->steamid] = $user->personaname;
	$av[$user->steamid] = $user->avatar;
}

//print_r($data);

$position = 0;
foreach( $data as $row ) {
	$id = $row['STEAM'];
	$position++;
	
	//echo '<div class="recent_heading"><img src="'.$av[$id].'"><span class="recent_subheading"><a href="http://steamcommunity.com/profiles/'.$id.'">'.$names[$id].'</a> purchased these items for ' . FormatPrice($row['TOTAL'],true) . '</span></div>';
	echo '<div class="recent_heading"><span class="recent_date">'.GetRelativeTime(strtotime($row['DATE'])).'</span><img src="'.$av[$id].'"><span class="recent_subheading"><a href="http://steamcommunity.com/profiles/'.$id.'">'.$names[$id].'</a></span></div>';
	
	$items = unserialize($row['TRANSACTION'])->items->items;
	
	//print_r($items);
	
	echo '<table class="inventory">';
	echo '<tr>';
	$column = 0;
	foreach( $items as $item ) {
		$id = $item->id;
		$amt = $item->amount;
		if( $column == 7 ) {
			$column = 0;
			echo '</tr><tr>';
		}
		echo '<td><div class="item_pic" title="'. $GLOBALS['item_data2'][$id]['name'].'"><img src="'. $GLOBALS['item_data2'][$id]['usage'] . '.png"><div class="item_amt">' . $amt . '</div></div></td>';
		$column++;
	}
	echo '</tr></table>';
}


PrintFooter();

?>