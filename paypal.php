<?php

require_once 'config.php';
require_once 'transaction.php';
require_once 'paypal_credentials.php';
//---------------------------------------------------------------------------------------------

function Paypal_GetAccessToken() {
	
	if (file_exists('data/paypaltoken.data')) {
		$data = unserialize(file_get_contents('data/paypaltoken.data'));
		if( time() < $data->timestamp + ($data->expires_in-600) ) {
			// cached token
			return $data;
		}
	}
	$header = array( "Accept: application/json", "Accept-Language: en_US" );
	
	global $paypal_endpoint;
	global $paypal_clientid, $paypal_secret; 
	 
	$ch = curl_init(); 
	curl_setopt( $ch, CURLOPT_URL, "https://$paypal_endpoint/v1/oauth2/token" );        
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );       
	curl_setopt( $ch, CURLOPT_HEADER, false ); 
	curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
	curl_setopt( $ch, CURLOPT_USERPWD, "$paypal_clientid:$paypal_secret" );
	
	curl_setopt( $ch, CURLOPT_POST, 1 );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials" );
	$data = curl_exec($ch); 
	curl_close($ch);
	if( empty($data) ) {
		throw new Exception("paypal error");
	}
	
	$json = json_decode( $data );
	
	$json->timestamp = time();
	file_put_contents('data/paypaltoken.data',serialize($json));
	
	
	return $json;
	
}

function Paypal_CreatePayment( $access, $returnurl, $cancelurl, $amount ) {
	global $paypal_endpoint;
	$header = array(  "Authorization: Bearer $access",'Content-Type: application/json', 'Accept: application/json' );
	$curl = curl_init( "https://$paypal_endpoint/v1/payments/payment" );
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $header );
	global $gpath, $item_data2;
	$pay = array( 
		"intent" => "sale",
		"redirect_urls" => array(
			"return_url" => $gpath.$returnurl,
			"cancel_url" => $gpath.$cancelurl
			),
		"payer" => array(
			"payment_method" => "paypal" 
			),
		"transactions" => array( 
			array( 
				"amount" => array( 
					"total" => FormatPrice( $amount ), 
					"currency" => "USD",
					"details" => array(
						"subtotal" => FormatPrice( $amount ),
						
						)
					),
				 
				"item_list" => array(
					"items" => array()
						
					)
				)
			)
		);
	
	$pay['transactions'][0]['item_list']['items'][] = array (
		'quantity' => 1,
		"name" => "pixels",
		"price" => FormatPrice( $amount ),
		"currency" => "USD"
		);
	 
	
	$postdata = json_encode( $pay );
	
	curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata); 

	$response = curl_exec( $curl );
	
	if (empty($response)) {
		//echo "\n curl error: " . curl_error( $curl) ;
		curl_close($curl);
		throw new Exception("paypal error");
	}
	curl_close($curl);
	$jsonResponse = json_decode($response);

	if( !isset($jsonResponse->state) || $jsonResponse->state != "created" ) throw new Exception( "paypal error" );

	return $jsonResponse;
}


function PayPal_CheckPayment( $access, $payment ) {
	$header = array(  "Authorization: Bearer $access",'Content-Type: application/json', 'Accept: application/json' );
	global $paypal_endpoint;
	$url = "https://$paypal_endpoint/v1/payments/payment/".$payment->id ;
	$curl = curl_init( $url );
	curl_setopt($curl, CURLOPT_POST, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $header );
	$response = curl_exec( $curl );
	if (empty($response)) {
		curl_close($curl);
		throw new Exception("paypal error");
	}
	curl_close($curl);
	$jsonResponse = json_decode($response);
	return $jsonResponse;
}

function PayPal_ExecutePayment( $access, $payment, $payerid ) {
	 
	$header = array(  "Authorization: Bearer $access",'Content-Type: application/json', 'Accept: application/json' );
	
	$url = PayPal_FindExecuteUrl( $payment );
	$curl = curl_init( $url );
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $header );
	
	$postdata = json_encode( array( "payer_id" => $payerid ) );
	curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata); 

	$response = curl_exec( $curl );
	
	if (empty($response)) {
		curl_close($curl);
		throw new Exception("paypal error");
	}
	curl_close($curl);
	$jsonResponse = json_decode($response);
	return $jsonResponse;
}

function PayPal_FindApprovalUrl( $payment ) {
	  
	foreach( $payment->links as $p ) {
		if( $p->rel == "approval_url" ) {
			return $p->href;
		}
	}
	throw new Exception( "paypal error: no approval url" );
}

function PayPal_FindExecuteUrl( $payment ) {
	foreach( $payment->links as $p ) {
		if( $p->rel == "execute" ) {
			return $p->href;
		}
	}
	throw new Exception( "paypal error: no execute url" );
}

function PayPal_FindCheckUrl( $payment ) {
	foreach( $payment->links as $p ) {
		if( $p->rel == "self" ) {
			return $p->href;
		}
	}
	throw new Exception( "paypal error: no self url" );
}


?>
