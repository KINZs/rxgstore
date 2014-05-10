<?php

require_once 'config.php';
require_once 'functions.php';
require_once 'opensession.php';
StripWWW();

OpenSession();
AdminCheckpoint(ADMFLAG_RECEIPTQUERY);


PrintHeader( 'pokeman center | admin => receipts', "admin.php" ); 
 
if( !UserIsAdmin() ) die( "not logged in or not admin" );

echo '<script src="receiptquery.js"></script>';

echo '<center><img src="receipts.png" ></center>';
echo '<h1>receipt query</h1>';

echo '<p>type in details to look up receipts</p>';
echo '<form action="javascript:ReceiptQuery1();" onSubmit="">';
echo '<table>';
echo '<tr><td>SALE#</td><td><input type="text" name="saleid" id="saleid"></td></tr>';
echo '<tr><td>TYPE</td><td><select name="type" id="type"><option value="NULL">***</option><option value="ITEMS">ITEMS</option><option value="CASH">CASH</option></select></td></tr>';
echo '<tr><td>STATE</td><td><select name="state" id="state"><option value="NULL"></option><option value="OKAY">OKAY</option><option value="REFUNDED">REFUNDED</option></select></td></tr>';
echo '<tr><td>AFTER (yyyy/mm/dd)</td><td><input type="text" name="after" id="after"></td></tr>';
echo '<tr><td>BEFORE (yyyy/mm/dd)</td><td><input type="text" name="before" id="before"></td></tr>';
echo '<tr><td>STEAMID</td><td><input type="text" name="steamid" id="steamid"></td></tr>';
echo '<tr><td>PAYPAL SALE ID</td><td><input type="text" name="paypalid" id="paypalid"></td></tr>';

//echo '<tr><td>total at least</td><td><input type="text" name="total" id="total"></td></tr>';

echo '</table>';

echo '<input id="page" type="hidden" name="page" value="0">';
echo '<input id="submit" type="submit" value="search">';
echo '<span id="thinker" style="float:right;"></span>';
echo '</form>';

echo '<a name="receiptlist"></a>';
echo '<div id="receipt_results"></div>';
echo '<a name="receipt"></a>';
echo '<div id="receipt_display"></div>';

PrintFooter();

?>
