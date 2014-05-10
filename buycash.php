<?php


require_once 'config.php';
require_once 'sql.php'; 
require_once 'functions.php';
require_once 'opensession.php';
require_once 'paypal.php';

StripWWW();
OpenSession();
StartBuyCash();

function StartBuyCash() {
	try {
		
		if( !$_SESSION['loggedin'] ) {
			RedirectUser( "buyerror.php?error=LOGIN" );
		}
		 
		if( !isset( $_GET['option'] ) ) die( "error" );
		
		$option = $_GET['option'];
		if( $option == 1 ) {
			$amount = 100;
			$price = 100;
		} else if( $option == 2 ) {
			$amount = 255;
			$price = 250;
		} else if( $option == 3 ) {
			$amount = 515; 
			$price = 500;
		} else if( $option == 4 ) {
			$amount = 1050;
			$price = 1000;
		} else if( $option == 5 ) {
			$amount = 2750;
			$price = 2500;
		} else if( $option == 6 ) {
			if( !isset( $_GET['amount'] ) ) die( "error" );
			$price = $_GET['amount'];//ceil( $amount * 0.9 );
			if( $price < 2500 ) die( "error" );
			$amount = ceil( $price  *1.1 );
		}
		 
		$_SESSION['buycash'] = array(
			'challenge' => mt_rand(),
			'amount' => $amount,
			'price' => $price,
			'account' => $_SESSION['accountid']
			);
		
		$token = Paypal_GetAccessToken();
		$payment = Paypal_CreatePayment( $token->access_token, "buycash_return.php?challenge=" . $_SESSION['buycash']['challenge'] , "buycash_cancel.php", $price ); 
		$approval_url = PayPal_FindApprovalUrl($payment); 
		if( $approval_url == "" ) throw new Exception( "paypal error" );
		$_SESSION['buycash']['payment'] = $payment;
		session_write_close();
		
		// redirect to paypal
		header( "Location: $approval_url" );
		exit;
		
	} catch (Exception $e) {
		unset( $_SESSION['buycash'] );
		RedirectUser( "buyerror.php?error=UNKNOWN" );
		
		//echo ' an error occurred... try again later :)';
		//echo 'Caught exception: ',  $e->getMessage(), "\n";
	}
}

?>