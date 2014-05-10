<?php

require_once 'config.php';
require_once 'sql.php';
require_once 'functions.php';
require_once 'opensession.php';
require_once 'itemdata.php';
require_once 'paypal.php';

StripWWW();
OpenSession();
ProcessLogin();


function buycashcomplete() {
	if( $_SESSION['loggedin'] != 1 ) {
		if( isset( $_SESSION['buycash'] ) ) {
			echo '<p>oops, an error occurred, your PAYPAL has not been charged.</p>';
			unset( $_SESSION['buycash'] );
		} else {
			echo '<p>oops, an error occurred</p>';
		}
		return FALSE;
	}
	
	if( !isset($_SESSION['buycash']) ) {
		echo '<p>oops, an error occurred</p>';
		return FALSE;
	}
	
	if( !isset($_GET['challenge']) || $_GET['challenge'] != $_SESSION['buycash']['challenge'] || !isset($_GET['PayerID']) ) {
		unset( $_SESSION['buycash'] );
		die( "error" );
	}
	
	$aid = $_SESSION['buycash']['account'];
	
	try {
		GrantStoreCredit( $aid, $_SESSION['buycash']['amount'] );
	} catch (Exception $e) {
		LogError( "couldn't GRANT STORE CREDIT", $e  );
		echo '<p>oops, an error occurred, your PAYPAL has not been charged.</p>';
		return FALSE;
	}
	
	$token = Paypal_GetAccessToken();
	$response = PayPal_ExecutePayment( $token->access_token, $_SESSION['buycash']['payment'],$_GET['PayerID'] ) ;
	if( $response->state == "approved" ) {
 
		RecordCASHPurchase( $response );
		
		echo "<h1>confirmation</h1>";
		echo "<p><span style='color:#008800;size:20px;font-weight:bold;'>".FormatPrice( $_SESSION['buycash']['amount'],true )."</span> has been added to your CASH.</p>";
		//PrintOutReceipt( $transaction );
		
		if( isset( $_SESSION['transaction'] ) ) {
			echo '<p><a href="checkout.php?restore">Continue Checkout</a></p>';
		}
		
		unset( $_SESSION['buycash'] );
	} else {
		LogError( "***ADDFUNDS PURCHASE EXECUTION ERROR***", $e );
		if( !ChargeStoreCredit( $aid, $_SESSION['buycash']['amount'] ) ) {
			LogError( "couldn't REMOVE CASH after paypal failure!", $e, true );
			return FALSE;
		}
		echo '<p>An error occurred, please contact an administrator!</p>';
	}
}

PrintHeader( "RXGMART", 'store.php' );
echo '<center><img src="pokemart.png" ></center>';

buycashcomplete();

PrintFooter();


?>