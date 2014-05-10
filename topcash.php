<?php
// notes 
//steamid base =76561197960265728
//

require_once 'config.php'; 
require_once 'sql.php';
require_once 'functions.php';
require_once 'opensession.php'; 

StripWWW();
OpenSession(); 
ProcessLogin();
PrintHeader( 'topcash' ); 

?>
<h1>who spent the most cents?</h1>

<?php

$sql = GetSQL();

$result = $sql->safequery( "SELECT STEAM,SUM(TOTAL) AS SUMTOTAL FROM RECEIPTS WHERE `TYPE`='ITEMS' AND `STATE`='OKAY' GROUP BY STEAM ORDER BY SUMTOTAL DESC LIMIT 50" );

$list = array();
while( $a = $result->fetch_array() ) {
	$list[] = $a;
}

$steamid_list="";
foreach( $list as $p ) {
	$steamid_list = $steamid_list .  $p['STEAM']  . ",";
}
$steamid_list = substr($steamid_list,0, strlen($steamid_list)-1); // remove trailing comma
$species_data = GetMultiUserData( $steamid_list );


$names = array();
$av = array();

foreach( $species_data as $p ) { 
	$names[$p->steamid] = $p->personaname;
	$av[$p->steamid] = $p->avatar;
}

echo '<table class="topcash">';
$position=0;
foreach( $list as $a ) {
	$id =  $a['STEAM'] ;
	$position++;
	echo '<tr'.($position <= 10 ?' class="topcash_top10"':'').'><td class="topcash_position">'.$position.'</td><td class="topcash_avatar"><img src="'.$av[$id].'"</td><td>&nbsp;<a href="http://steamcommunity.com/profiles/'.$id.'">'.$names[$id].'</a></td><td>&nbsp;' . FormatPrice($a['SUMTOTAL'],true) . '</td></tr>';
}
echo '</table>';

?>
<?php	 
PrintFooter();

?>
 
