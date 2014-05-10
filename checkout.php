<?php

require_once 'config.php';
require_once 'functions.php';
require_once 'opensession.php';
require_once 'sql.php';
require_once 'transaction.php';
require_once 'itemdata.php';

StripWWW();
OpenSession();
ProcessLogin();
PrintHeader( 'RXGMART', 'none' ); 

?>
<center><img src="pokemart.png" ></center>
<h1>CHECKOUT</h1>

<?php

function checkout() {
	if( !$_SESSION['loggedin'] ) {
		echo '<p>Your session expired.</p>';
		return;
	}
	global $item_data,$item_data2;
	try {
		$x = 0;
		
		if( !isset($_GET['restore']) ) {
			$x = new Transaction;
			
			$x->steamid = $_SESSION['steamid'];
			foreach( $_GET as $key =>$value ) {
				
				if( isset( $item_data2[$key] ) ) {
					if( $value <= 0 ) continue;
					$x->items->AddItem( $item_data2[$key]['id'], $value );
				}
			}
		} else {
			$x=$_SESSION['transaction'];
			
		}
		if( $x->items->totalcount() == 0 ) {
			echo "<p>You didn't put any pixels in your shopping cart...!</p>";
			return;
		}
		$credit = GetStoreCredit( $_SESSION['accountid'] );
		
		$x->Compute( );
		
		//if( $x->subtotal < $GLOBALS['min_transaction'] ) {
		//	echo "<p>you need to purchase at least ".FormatPrice( $min_transaction,true )." of goods!</p>";
		//	return;
		//}
		
		if( !TryRemoveItemStockList( $x->items->items, true ) ) {
			echo "<p>One or more items desired are out of stock.</p>";
			return;
		}
		
		echo '<p>Please confirm your order:</p>';
		echo '<table border="1">';
		
		
		foreach( $x->items->items as $i ) {
			echo '<tr><td style="min-width:40px">' . $i->amount . '</td><td>' . $item_data2[$i->id][ $i->amount == 1 ? 'name':'plural'] . '</td><td style="text-align: right">'. FormatPrice($item_data2[$i->id]['price'] * $i->amount,true) . '</td></tr>';
			
		}
		
		echo '<tr><td></td><td>subtotal</td><td style="text-align: right">'.FormatPrice($x->subtotal,true).'</td></tr>';
		echo '<tr><td></td><td>shipping & handling</td><td style="text-align: right">'.FormatPrice($x->shipping,true).'</td></tr>';
		
		echo '<tr><td></td><td>total</td><td style="text-align: right">'.FormatPrice($x->total,true).'</td></tr>';
		
		$payment_due = $x->total - $credit;
		if( $payment_due < 0 ) $payment_due = 0;
		echo '<tr><td></td><td>CASH</td><td style="text-align: right">'.FormatPrice($credit,true).'</td></tr>';
		if( $payment_due > 0 ){
			echo '<tr style="color:red; text-decoration: underline; font-weight: bold;"><td></td><td>payment due</td><td style="text-align: right">'.FormatPrice($payment_due,true).'</td></tr>';
		}
		////if( $x->payment_due > 0 ) {
		//	echo '<tr style="color:red; text-decoration: underline; font-weight: bold;"><td></td><td>payment due</td><td>'.FormatPrice($x->payment_due,true).'</td></tr>';
		//}
		echo '</table>';
		
		
		$_SESSION['transaction'] = $x;
		
		
		if( $payment_due > 0 ) {
			echo '<form action="addfunds.php">';
			echo '<img src="shoppingcart.png" style="vertical-align:bottom">';
			echo '<input type="submit" value="Add Funds">';
			
			echo '</form>';
			
		} else {
			echo '<form action="buy.php">';
			echo '<img src="shoppingcart.png" style="vertical-align:bottom">';
			echo '<input type="submit" value="Complete Purchase">';
			echo '</form>';
		}
			 
		
		
	} catch( Exception $e ) {
		echo "an error happened, try again later.";
	}
}
checkout();


PrintFooter();

?>
