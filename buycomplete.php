
<?php

require_once 'config.php';
require_once 'sql.php';
require_once 'transaction.php';
require_once 'functions.php';
require_once 'opensession.php';
require_once 'itemdata.php';
require_once 'paypal.php';

StripWWW();
OpenSession();
ProcessLogin();

function buycomplete() {
	global $item_data2;
	$aid = $_SESSION['accountid'];
	try {
		if( !buyloggedincheck() ) return FALSE;
		if( !isset($_SESSION['transaction']) ) {
			ClearPaymentCache();
			echo '<p>oops, an error occurred</p>';
			return FALSE;
		}
		$transaction = &$_SESSION['transaction'];
//		if( ($transaction->payment) && !isset($_GET['PayerID']) ) {
	//		ClearPaymentCache();
//			echo '<p>something went wrong</p>';
//			return FALSE;
//		}
//		if( (!$transaction->payment) && ($transaction->payment_due > 0) ) {
//			ClearPaymentCache();
//			echo '<p>something went wrong</p>';
//			return FALSE;
//		}
//		if( ($transaction->payment_due>0) && !isset($_GET['PayerID']) ) {
//			ClearPaymentCache();
//			echo '<p>something went wrong</p>';
//			return FALSE;
//		}
		if( !isset($_GET['challenge']) || $_GET['challenge'] != $transaction->challenge ) {
			// this shouldn't happen without user interference
			ClearPaymentCache();
			echo '<p>something went wrong</p>';
			return FALSE;
		}
		
		$items_given = false;
		$items_unstocked=false;
		$storecredit_charged=false;
		
		if( $transaction->total > 0 ) {
			if( !ChargeStoreCredit( $aid, $transaction->total ) ) {
				echo "<p>it seems you don't have enough store credit to complete this transaction :(</p>";
				return FALSE;
			}
			$storecredit_charged = true;
		}
		 
		if( !TryRemoveItemStockList( $transaction->items->items ) ) {
			echo "<p>oops! the items you wanted were bought already or removed, your transaction has been cancelled</p>";
			if( $storecredit_charged ) {
				GrantStoreCredit( $aid, $transaction->total );
			}
			return FALSE;
		}
		$items_unstocked = true;

		foreach( $transaction->items->items as $item ) {
			AddUserItem( $aid, $item->id, $item->amount );
		}
		$items_given = true;
		   
	//	if( $transaction->payment_due > 0 ) {
	//	
	//		$token = Paypal_GetAccessToken();
	//		$response = PayPal_ExecutePayment( $token->access_token,$transaction->payment,$_GET['PayerID'] ) ;
	//		if( $response->state == "approved" ) {
	//			 
	//			$transaction->payment = $response;
	//			
	//			$transaction->paypalsaleid = $response->transactions[0]->related_resources[0]->sale->id;
		//	} else {
	//			throw new Exception( "paypal execution error" );
	//		}
	//	}
		
		// payment confirmed
		$sale = RecordItemTransaction( $transaction );
		echo "<h1>confirmation</h1>";
		echo "<p>enjoy your new pixels, here is your receipt lol</p>";
		
		PrintOutReceipt( $transaction );
 
		ClearPaymentCache();
		 
	} catch( Exception $e ) {
	
		LogError( "***PURCHASE EXECUTION ERROR***\n", $e );
		
		if( $items_given ) {
			// take that shit back
			try {
				foreach( $transaction->items->items as $item ) {
					RemoveUserItem( $aid, $item->id, $item->amount );
				}
			} catch (Exception $e) {
				LogError( "couldn't REMOVE items after transaction failure!", $e, true );
			}
		}
		if( $items_unstocked ) {
			try {
				AddItemStockList( $transaction->items->items );
			} catch (Exception $e) {
				LogError( "couldn't RESTOCK items after transaction failure!", $e, true );
			}
		}
		if( $storecredit_charged ) {
			try {
				GrantStoreCredit( $aid, $transaction->storecredit );
			} catch (Exception $e) {
				LogError( "couldn't RESTORE STORE CREDIT after transaction failure!", $e, true );
			}
		}
		
		
		ClearPaymentCache();
			
		echo '<p>an error happened! please notify an administrator</p>';
		
		return FALSE;
	}
}

PrintHeader( "RXGMART", 'store.php' );

echo '<center><img src="pokemart.png" ></center>';

buycomplete();

PrintFooter();


?>
