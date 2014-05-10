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

ProcessBuy();
function ProcessBuy() {
	try {
	
		if( !$_SESSION['loggedin'] ) {
			RedirectUser( "buyerror.php?error=LOGIN" );
		}
		
		if( !isset($_SESSION['transaction']) ) {
			RedirectUser( "buyerror.php?error=UNKNOWN" );
		}
	
		$x = &$_SESSION['transaction'];
		
		if( !TryRemoveItemStockList( $x->items->items, true ) ) {
			RedirectUser( "buyerror.php?error=OUTOFSTOCK" );
		}
		
		if( $x->payment_due > 0 ) {
			RedirectUser( "buyerror.php?error=UNKNOWN" );
			
			/*
			$token = Paypal_GetAccessToken();
			$payment = Paypal_CreatePayment( $token->access_token, $x );
		
			$approval_url = PayPal_FindApprovalUrl($payment);
			$x->payment = $payment;
			 
			if( $approval_url == "" ) throw new Exception( "paypal error" );
			
			session_write_close();
			
			// redirect to paypal
			header( "Location: $approval_url" );
			exit;
			*/
		} else {
			// store credit transaction
			RedirectUser( "buycomplete.php?challenge=".$x->challenge );
			//header( "Location: ".$gpath."?page=buycomplete&challenge=".$x->challenge );
		}
		
	} catch (Exception $e) {	
	
		RedirectUser( "buyerror.php?error=UNKNOWN" );
		
		//echo ' an error occurred... try again later :)';
		//echo 'Caught exception: ',  $e->getMessage(), "\n";
	}
}
