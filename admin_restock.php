<?php
	
require_once 'config.php';
require_once 'sql.php';
require_once 'itemdata.php';
require_once 'functions.php';
require_once 'opensession.php';

StripWWW();
OpenSession();

AdminCheckpoint(ADMFLAG_CHGSTOCK);

PrintHeader( "admin center => restock", 'admin.php' );

echo '<center><img src="bob.png" ></center>';
echo '<h1>pixel warehouse</h1>';
echo '<p>put more pixels for sale</p>';

$item_stock = GetItemStock();


echo '<form action="admin_restock2.php">';
echo '<table class="buylist">';
echo '<tr style="text-align:center"><td></td><td style="text-align:left">item</td><td>stocked</td><td>quantity</td>';
//echo '<input type="hidden" name="page" value="admin_restock2">';
foreach( $GLOBALS['item_data2'] as $item ) {
	if( !$item['buyable'] ) continue;
	
	$stock = isset($item_stock[$item['id']]) ? $item_stock[$item['id']]:0;
	echo '<tr>';
	echo '<td class="image"><img src="'.$item['usage'].'.png"></td>';
	echo '<td class="desc">'.$item['name'].'</td>';
	echo '<td >'.$stock.'</td>';
	echo '<td class="itemamount"><input class="itemamount" type="text" name="item_'.$item['id'].'" value="" ></td>';
	echo '</tr>';
}
echo '</table>';
echo '<input type="submit" value="put on shelf" >';
echo '</form>';
	
PrintFooter();

?>
