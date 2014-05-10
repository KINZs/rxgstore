<?php

require_once 'openid.php';
require_once 'sql.php';

function RedirectUser( $page ) {
	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	
	header("Location: http://$host$uri/$page");
	exit;
}

//---------------------------------------------------------------------------------------------
function PrintHeader( $title="pokeman center", $backpage="", $jquery=true, $keepalive=true, $extra="" ) {
	echo '
		<!DOCTYPE html>
		<html>
		<head>
		<meta http-equiv="Content-type" content="text/html;charset=UTF-8">
		<title>';
		
	echo $title;
	
	echo '</title>

		<link href="http://fonts.googleapis.com/css?family=Roboto:400,300,700,400italic" rel="stylesheet" type="text/css">
		<link href="http://fonts.googleapis.com/css?family=PT+Mono" rel="stylesheet" type="text/css">
		<link rel="stylesheet" type="text/css" href="theme.css">';
		
	$css = basename($_SERVER['PHP_SELF']);
	$css = substr( $css, 0, strpos($css,".") ) . ".css";
	if( file_exists($css) ) echo '<link rel="stylesheet" type="text/css" href="'.$css.'">';
		
	if( $jquery ) {
		echo '<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>';
	}
	
	echo $extra;
	
	global $session_timeout;
	if( $keepalive ) {
		echo '
			<script type="text/javascript">

				function keep_alive() {
				/*
					$.ajax({
					   url: \'pulse.php\',
					   cache: false,
					   complete: function () {}
					});*/
					
					var xmlhttp = new XMLHttpRequest();
					xmlhttp.open("GET","pulse.php",true);
					xmlhttp.send();
				};
				
				setInterval( keep_alive,'.(($session_timeout -60)*1000).' );   
				 
			</script>';
	}
	
	echo '
		</head>
		 
		<body>
		<div id="background">&nbsp;
		</div>
		<br>


		<div class="header">

		<table class="headert"><tr>
		<td class="header_left">';
		
	if( $backpage == "" ) $backpage = "./";
	if( $backpage != "none" ) {
	//	$backpage = "";
	//	if( $page == 'freepokeball' ) $backpage = 'store';
	//	if( $page == 'editpokeman' ) $backpage = 'trainers';
	////	if( $page == 'admin_restock' || $page == 'admin_restock2' ) $backpage = 'admin';
	//	if( $page == 'buy'  ) $backpage = 'store';
		echo '<a href="'.$backpage.'">go back</a>';
	}
	
	echo '
		</td>
		<td class="header_right">
		
		';
	if( $_SESSION['loggedin'] != 1 ) {

		echo '	<form action="?login" method="post">
					<input class="normal" type="image" src="http://cdn.steamcommunity.com/public/images/signinthroughsteam/sits_small.png" alt="Sign in through Steam">
					<input type="checkbox" name="rememberme" id="rememberme"><label for="rememberme">remember me</lable>
				</form>';
	} else {
		echo 'Logged in as <b>' . $_SESSION['loggedin_name'] . '</b> <a href="trainers.php?id=me"><img src="'.$_SESSION['avatar'].'"></a>';
		echo '<a class="logout" href="?logout">log out</a>';
	}
	
	echo '
 
		</td></tr></table>

		</div>
		<div class="content">';
}

function PrintFooter() {
	echo '</div>';
	echo '<div class="foot">';
	echo '<a href="http://reflex-gamers.com">reflex gamers</a> | <a href="http://steampowered.com">Powered by Steam</a>';
	if( UserAdmin() ) {
		echo '<br>administrator mode => ' . $GLOBALS['admins'][$_SESSION['adminid']-1]['name'];
	}
	echo '</div>';
	echo '</body></html>';
	
}

function StripWWW() {
	if( !$GLOBALS['stripwww'] ) return;
	if( substr($_SERVER['HTTP_HOST'],0,4) == "www." ) {
		
		header( "Location: $gpath"  );
		exit;
	}
}

//---------------------------------------------------------------------------------------------
function LogInUser( $stid, $save ) {
	
	$userdata = GetUserData( $stid );
	if( $userdata == FALSE ) {
		echo 'error logging in.';
	} else {
		$_SESSION['loggedin'] = 1;
		$_SESSION['steamid'] = $stid;
		$_SESSION['accountid'] = AccountIDFromSteamID64($stid);
		$_SESSION['loggedin_name'] = $userdata->personaname;
		$_SESSION['avatar'] = $userdata->avatar;
		
		if( $save ) {
			SaveUserLogin( $_SESSION['accountid'] );
		}
		
		foreach( $GLOBALS['admins'] as $key => $admin ) {
			if( $stid == $admin['id'] ) {
				$_SESSION['adminid'] = $key+1;
				$_SESSION['admin'] = $admin['flags'];
			}
		}
	}
}

//---------------------------------------------------------------------------------------------
function SaveUserLogin( $account ) {
	$sql = GetSQL();
	 
	$code = mt_rand(1,999999);
	$expire =time()+60*60*24*30;
	$remoteaddr = $sql->real_escape_string($_SERVER['REMOTE_ADDR']);
	$account = (int)$account;
	$sql->safequery( "INSERT INTO SAVEDLOGIN (CODE,EXPIRES,REMOTEADDR,ACCOUNT) VALUES ($code,$expire,'$remoteaddr',$account)" );
	$result = $sql->safequery( "SELECT LAST_INSERT_ID()" );
	$row = $result->fetch_array();
	$id = $row['LAST_INSERT_ID()'];
	 
	global $apath;
	setcookie( "savedlogin", sprintf( "%016d%016d", $id, $code ), $expire, $apath);
}

//---------------------------------------------------------------------------------------------
function TryLoginSaved(){ 
	
	if( !isset($_COOKIE['savedlogin']) ) return FALSE;
	$c = $_COOKIE['savedlogin'];
	if( $c == "" || strlen($c) != 32 ) return FALSE;
	$id = substr( $c, 0, 16 );
	$code = substr( $c, 16, 16 );
	
	$sql = GetSQL();
	$time = time();
	
	$remoteaddr = $sql->real_escape_string($_SERVER['REMOTE_ADDR']);
	$id = (int)$id; // cast to int, otherwise client can exploit cookie value
	$code = (int)$code;
	
	$result = $sql->safequery( "SELECT ACCOUNT FROM SAVEDLOGIN WHERE ID=$id AND CODE=$code AND REMOTEADDR='$remoteaddr' AND EXPIRES>$time" );
	
	$row = $result->fetch_array();
	global $apath;
	if( !$row ) {
		// run cleanup function
		$sql->safequery( "DELETE FROM SAVEDLOGIN WHERE EXPIRES<=".$time ); 
		
		// erase client cookie
		setcookie( "savedlogin", "", 0, $apath );
		return FALSE;
	} else {
		
		$expire =time()+60*60*24*30;
		$sql->safequery( "UPDATE SAVEDLOGIN SET EXPIRES=$expire WHERE ID=$id" );
		
		setcookie( "savedlogin", $c, $expire, $apath);
		LogInUser( SteamID64FromAccountID( $row['ACCOUNT'] ), false );
		return TRUE;
	}
	
}

//---------------------------------------------------------------------------------------------
function ProcessLogin() {
	
	if( isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == 1 ) return;
	
	if( TryLoginSaved() ) return;
	
	try {
		# Change 'localhost' to your domain name.
		$openid = new LightOpenID($GLOBALS['domain']);
		if(!$openid->mode) {
			if(isset($_GET['login'])) {
				if( isset( $_POST['rememberme'] ) ) {
					$_SESSION['rememberme'] = 1;
				}
				$openid->identity = 'http://steamcommunity.com/openid';
				header('Location: ' . $openid->authUrl());
			}
			
			
		} elseif($openid->mode == 'cancel') {
			//echo 'User has canceled authentication!';
		} else {
			//echo 'User ' . ($openid->validate() ? $openid->identity . ' has ' : 'has not ') . 'logged in.<br>';
			
			if( $openid->validate() ) {
				$stid = SteamIdFromOpenId( $openid->identity );
				LogInUser( $stid, isset($_SESSION['rememberme']) );
			}
		}
	} catch(ErrorException $e) {
		echo 'auth error: ' . $e->getMessage();
	}
}

//---------------------------------------------------------------------------------------------
function LogError( $text, $e=null, $simple=false ) {

	if( !$simple ) {
		$text = "time=".strftime( "%c" ) . "\n" . $text;
		
	}
	if( $e ) {
		$text = $text . "\n" . "exception=".$e->getMessage()."\n";
	}
	if( !$simple ) {
		$text = $text .
			"SESSION=" . print_r( $_SESSION, true );
		
	}
	$text = "\n-------------------------------\n" . $text;
	file_put_contents( "data/error.log", $text,	FILE_APPEND );
}

//---------------------------------------------------------------------------------------------
function GetContents($url) {
	
	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);

	$data = curl_exec($ch); 
	
	curl_close($ch);

	return $data;
}

//---------------------------------------------------------------------------------------------
function UserIsAdmin() {
	if( !isset($_SESSION['loggedin']) || !isset($_SESSION['admin']) ) return 0;
	return $_SESSION['admin'];
	//global $admins;
	//if( !$_SESSION['loggedin'] ) return FALSE;
	//return in_array( $_SESSION['steamid'], $admins );
}

function AdminCheckpoint( $flags=0 ) {
	if( !isset($_SESSION['loggedin']) || !isset($_SESSION['admin']) ) die( "not logged in or not admin" );
	
	if( ($_SESSION['admin'] & $flags) != $flags ) {
		die( "insufficient privileges" );
	}
}

//---------------------------------------------------------------------------------------------
function UserAdmin() {
	return UserIsAdmin();
}


//---------------------------------------------------------------------------------------------
function UserIsSteamID( $steamid ) {
	if( !$_SESSION['loggedin'] ) return FALSE;
	return $_SESSION['steamid'] == $steamid;
}

//---------------------------------------------------------------------------------------------
function GetUserData( $steamid ) {
	global $steamapikey;
	$data = GetContents( "http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=$steamapikey&steamids=$steamid" );
	
	$list =json_decode( $data )->response->players;
	if( !isset( $list[0] ) ) return FALSE;
	return $list[0];
}

//---------------------------------------------------------------------------------------------
function GetMultiUserData( $steamids ) {
	global $steamapikey;
	$data = GetContents( "http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=$steamapikey&steamids=$steamids" );
	
	$list =json_decode( $data )->response->players;
 
	if( !isset( $list[0] ) ) return FALSE;
	return $list;
}

//---------------------------------------------------------------------------------------------
function ParseId( $str ) {
	// parses ID and returns valid steam community url
	$str = trim($str);
	
	if( $str == 'me' ) {
		if( $_SESSION['loggedin'] == 1 ) {
			return 'http://steamcommunity.com/profiles/' . $_SESSION['steamid'];
		}
					
		return FALSE;
	}
	
	if( preg_match( '/^(http:\/\/)*(www.)*steamcommunity.com\/profiles\/(?P<idmatch>[0-9]+)\/?$/', $str, $matches ) == 1 ) {
		// community ID match
		
		return 'http://steamcommunity.com/profiles/' . $matches['idmatch'];
	}
	
	if( preg_match( '/^765(?P<idmatch>[0-9]+)\/?$/', $str, $matches ) == 1 ) {
		// short community ID match
		return 'http://steamcommunity.com/profiles/765' . $matches['idmatch'];
	}
	
	if( preg_match( '/^((http:\/\/)*(www.)*steamcommunity.com\/id\/)*(?P<idmatch>[a-zA-Z0-9_]+)\/?$/', $str, $matches ) == 1 ) {
		// custom URL match
		
		return 'http://steamcommunity.com/id/' . $matches['idmatch'];
	}
	
	if( preg_match( '/^STEAM_[0-1]:(?P<Y>[0-1]):(?P<Z>[0-9]+)$/', $str, $matches ) ) {
		// Steam ID match
		
		$b = $matches['Z'];
		$b = bcmul( $b, 2 );
		$b = bcadd( $b, $matches['Y'] );
		$b = bcadd( $b, "76561197960265728" );
		return 'http://steamcommunity.com/profiles/' . $b;
	}
	
	return FALSE;
	
}


//---------------------------------------------------------------------------------------------

function AnFunction( $str ) {
	$a = strtolower(substr($str,0,1));
	if( $a == "a" || $a == "e" || $a == "i" || $a == "o" || $a == "u" ) return "An";
	return "A";
}

//---------------------------------------------------------------------------------------------
function SteamIdFromOpenId( $openid ) {
	return substr( $openid, strrpos($openid, "/")+1 );
}

//---------------------------------------------------------------------------------------------
function AccountIDFromSteamID64( $steamid64 ) {
	$steamid64 = bcsub( $steamid64, "76561197960265728" );
	if( bccomp( $steamid64, "2147483648" ) >= 0 ) {
		$steamid64 = bcsub( $steamid64, "4294967296" );
	}
	return (int)$steamid64;
}

//---------------------------------------------------------------------------------------------
function SteamID64FromAccountID( $accountid ) {
	$num = (string)$accountid;
	if( $accountid < 0 ) {
		$num = bcadd( $num, "4294967296" );
	}
	$num = bcadd( $num, "76561197960265728" );
	//$accountid += 0x0110000100000000 ;
	return $num;
}


//---------------------------------------------------------------------------------------------
function GetPokemanInfo( $accountid ) {
	$sql = GetSQL();
	
	$accountid = (int)$accountid;
	$result = $sql->safequery( "SELECT * FROM INFO WHERE ACCOUNTID=$accountid" );
	$row = $result->fetch_array();
	
	if( is_null($row) ) {
		$row = array();
		$row['ACCOUNTID'] = $accountid;
		$row['TYPE'] = "UNKNOWN";
		$row['ELEMENT'] = "???";
		$row['LENGTH'] = 0;
		$row['WEIGHT'] = 0;
		$row['HABITAT'] = "???";
		$row['DESCRIPTION'] = "??????????????????????????????";
		
	}
	 
	return $row;
}

function SetPokemanInfo( $accountid, $type, $element, $length, $weight, $habitat, $description ) {
 
	$sql = GetSQL();
	
	$accountid = (int)$accountid;
	$type = $sql->real_escape_string( trim($type) );
	$element = $sql->real_escape_string( trim($element) );
	$length = (int)$length;
	if( $length < 1 ) $length = 1;
	$weight = (int)$weight;
	if( $weight < 1 ) $weight = 1;
	$habitat = $sql->real_escape_string( trim($habitat) );
	$description = $sql->real_escape_string( trim($description) );
	
	$result = $sql->safequery( 
		"REPLACE INTO INFO VALUES( $accountid, '$type' , '$element', $length, $weight, '$habitat', '$description' )" );
}

//---------------------------------------------------------------------------------------------
function GetTotalPokemans( $accountid ) {
	$sql = GetSQL();
	
	$accountid = (int)$accountid;
	$result = $sql->safequery( "SELECT SUM(1) AS TOTALCOUNT FROM CAPTURES WHERE ACCOUNTID=$accountid" );
	$row = $result->fetch_array();
	
	if( is_null($row) ) return 0;
	if( !isset($row['TOTALCOUNT']) ) return 0;
	
	return $row['TOTALCOUNT'];
}

//---------------------------------------------------------------------------------------------
function GetPokemansCaptured( $accountid, $start, $howmany ) {
	$sql = GetSQL();
	
	$accountid = (int)$accountid;
	$result = $sql->safequery( "SELECT * FROM CAPTURES WHERE ACCOUNTID=$accountid ORDER BY TIME DESC LIMIT $start,$howmany;" );
	
	$list = array();
	for( $i = 0; $i < $howmany; $i++ ) {
		
		$row = $result->fetch_array();
		 
		if( is_null($row) ) break;
		$list[] = $row;
	} 
	return $list;
}

//---------------------------------------------------------------------------------------------
function GetCapture( $id ) {
	$id = (int)$id;
	$sql = GetSQL();
	$result = $sql->safequery( "SELECT * FROM CAPTURES WHERE ID=$id;" );
	$row = $result->fetch_array();
	if( is_null($row) ) return FALSE;
	return $row;
}

//---------------------------------------------------------------------------------------------
function SaveCapture( $info ) {
	$id = (int)$info['ID'];
	$sql = GetSQL();
	$nick = $info['NICKNAME'];
	$nick = $sql->real_escape_string( $nick );
	
	$result = $sql->safequery( "UPDATE CAPTURES SET NICKNAME='$nick' WHERE ID=$id;" );
}

//---------------------------------------------------------------------------------------------
function GetPokemanCaptureCount( $accountid ) {
	$accountid = (int)$accountid;
	$sql = GetSQL();
	
	$result = $sql->safequery( "SELECT SUM(1) AS COUNT FROM CAPTURES WHERE TARGET=$accountid" );
	$row = $result->fetch_array();
	
	if( is_null($row) || $row['COUNT'] == 0 ) return "NEVER";

	return $row['COUNT'] . " time" . (($row['COUNT'] == 1)?"":"s");
}

//---------------------------------------------------------------------------------------------
function GetTopTrainers() {
	$sql = GetSQL();
	
	$result = $sql->safequery( "SELECT ACCOUNT,SUM(1) AS COUNT FROM CAPTURES GROUP BY ACCOUNT ORDER BY COUNT DESC LIMIT 10"  );
	
	$data = array();
	
	$steamid_list="";
	
	while( !is_null($row = $result->fetch_array()) ) {
		 
		$entry = array();
		$entry['account'] = $row['ACCOUNT'];
		$entry['captures'] = $row['COUNT'];
		$entry['name'] = "<a href=\"?page=trainers&id=http://steamcommunity.com/profiles/" . SteamID64FromAccountID($entry['account']) . "\">".SteamID64FromAccountID($entry['account'])."</a>";
		$data[] = $entry;
		
		$steamid_list = $steamid_list . SteamID64FromAccountID($entry['account']) . ",";
	}
	
	$steamid_list = substr($steamid_list,0, strlen($steamid_list)-1); // remove trailing comma
	$species_data = GetMultiUserData( $steamid_list );
	if( $species_data != FALSE ) {
				
		foreach( $species_data as $player ) {
			$aid = AccountIDFromSteamID64( $player->steamid );

			foreach( $data as &$p  ) {
				if( $p['account'] == $aid ) {
					$p['name'] = "<a href=\"?page=trainers&id=http://steamcommunity.com/profiles/" . SteamID64FromAccountID($p['account']) . "\"><img src=\"" . $player->avatar . "\"></a> <span class=\"ttname\">".$player->personaname."</span>";
				}
			}
			unset( $p );
		}
	}
				
	return $data;
}

//---------------------------------------------------------------------------------------------
function GetInventory( $accountid ) {
	$sql = GetSQL();
	
	$accountid = (int)$accountid;
	$result = $sql->safequery( "SELECT * FROM INVENTORY WHERE ACCOUNT=$accountid AND AMOUNT <> 0"  );
	
	$inv = array();
	while( $row = $result->fetch_array() ) {
		$inv[] = array(
				'itemid' => $row['ITEMID'],
				'amount' => $row['AMOUNT']
				);
	}
	
	return $inv;
}

//---------------------------------------------------------------------------------------------
function AddUserItem( $accountid, $itemid, $amount ) {
	$acccountid = (int)$accountid;
	$itemid = (int)$itemid;
	$amount = (int)$amount;
	
	$sql = GetSQL();
	$result = $sql->safequery( 
		"INSERT INTO INVENTORY VALUES( $accountid, $itemid, $amount ) ".
		"ON DUPLICATE KEY UPDATE AMOUNT=AMOUNT+$amount;" );
}

//---------------------------------------------------------------------------------------------
function RecordItemTransaction( &$transaction  ) {

	$transaction->saleid = "<error>";
	try {
		
		
		$sql = GetSQL();
		
		//$sale2 = $sql->real_escape_string( $transaction->paypalsaleid );
		$steam = $sql->real_escape_string( $transaction->steamid );
		$total = ((float)$transaction->total);
	//	$paypal = ((float)$transaction->payment_due);
	//	$fees = isset($transaction->payment->transactions[0]->amount->details->fee) ? (float)($transaction->payment->transactions[0]->amount->details->fee) : 0.00;
	//	$settle = $paypal - $fees;
	//	$credit = (int)$transaction->total*100;
		$dump_string = $sql->real_escape_string( serialize($transaction) );
		
		$result = $sql->safequery( "INSERT INTO RECEIPTS (TYPE,STEAM,TOTAL,TRANSACTION) ".
								"VALUES( 'ITEMS',$steam,$total,'$dump_string' )" );
		$result = $sql->safequery( "SELECT LAST_INSERT_ID()" );
		
		$row = $result->fetch_array();
		$transaction->saleid = $row['LAST_INSERT_ID()'];
		
	} catch (Exception $e) {
		file_put_contents( "data/error.log", 
			"-------------------------------------------------\n".
			"***COULDN'T SAVE RECEIPT!!!***\n".
			"exception=" .$e->getMessage() . "\n" .
			"time=".strftime( "%c" ) . "\n" .
			"SESSION=" . print_r( $_SESSION, true ) . "\n".
			"xaction=" . print_r( $transaction, true ) . "\n"
			,FILE_APPEND );
		return;
	}

	return $transaction->saleid;
}

//---------------------------------------------------------------------------------------------
function RecordCASHPurchase( $payment ) { 
	try {
		
		
		$sql = GetSQL();
		
		$sale2 = $sql->real_escape_string( $payment->transactions[0]->related_resources[0]->sale->id );
		$steam = $sql->real_escape_string( SteamID64FromAccountID($_SESSION['buycash']['account']) );
		$paypal = $_SESSION['buycash']['price'];
		$fees = isset($payment->transactions[0]->amount->details->fee)?(int)((float)($payment->transactions[0]->amount->details->fee)*100):0;
		$settle = $paypal - $fees;
		$cash = $_SESSION['buycash']['amount'];
		
	//	$paypal = ((float)$transaction->payment_due);
	//	$fees = isset($transaction->payment->transactions[0]->amount->details->fee) ? (float)($transaction->payment->transactions[0]->amount->details->fee) : 0.00;
	//	$settle = $paypal - $fees;
	//	$credit = (int)$transaction->total*100;
	//	$dump_string = $sql->real_escape_string( serialize($transaction) );
		
		$result = $sql->safequery( "INSERT INTO RECEIPTS (TYPE,PPSALEID,STEAM,PAYPAL,FEES,SETTLE,CASH) ".
								"VALUES( 'CASH','$sale2',$steam,$paypal,$fees,$settle,$cash )" );
		//$result = $sql->safequery( "SELECT LAST_INSERT_ID()" );
		
		//$row = $result->fetch_array();
		
	} catch (Exception $e) {
		file_put_contents( "data/error.log", 
			"-------------------------------------------------\n".
			"***COULDN'T SAVE ADDFUNDS RECORD!!!***\n".
			"exception=" .$e->getMessage() . "\n" .
			"time=".strftime( "%c" ) . "\n" .
			"SESSION=" . print_r( $_SESSION, true ) . "\n"
			,FILE_APPEND );
		return;
	}
}

//---------------------------------------------------------------------------------------------
function RemoveUserItem( $accountid, $itemid, $amount ) {
	$acccountid = (int)$accountid;
	$itemid = (int)$itemid;
	$amount = (int)$amount;
	
	$sql = GetSQL();
	$result = $sql->safequery( "UPDATE INVENTORY SET AMOUNT=AMOUNT-$amount WHERE ACCOUNT=$accountid AND ITEMID=$itemid" );
}
  
//---------------------------------------------------------------------------------------------
function CheckFreePokeball( $accountid ) {
	// returns 0 if a free pokeball is added
	// or returns int containing seconds until next free pokeball
	
	$sql = GetSQL();
	
	$sql->safequery( "LOCK TABLE USER WRITE" );
	
	$accountid = (int)$accountid;
	$result = $sql->safequery(  "SELECT * FROM USER WHERE ACCOUNT=$accountid" );
	$row = $result->fetch_array();
	
	$time = time();
	
	if( is_null($row) ) {
		// new user
		$freeballtime = 0;
	} else {
		$freeballtime = $row['FREEBALLTIME'];
	}
	
	if( $time >= $freeballtime ) {
		$freeballtime = time() + 60*60*20;
		$sql->safequery( "INSERT INTO USER (ACCOUNT,FREEBALLTIME) VALUES ($accountid,$freeballtime) ON DUPLICATE KEY UPDATE FREEBALLTIME=$freeballtime" );
		$sql->safequery( "UNLOCK TABLES" );
		
		AddUserItem( $accountid, $GLOBALS['item_data']['item_pokeball']['id'], 1 );
		return 0;
		
	} else {
		
		$sql->safequery( "UNLOCK TABLES" );
		return $freeballtime - $time;
	}
	
}

//---------------------------------------------------------------------------------------------
function GetStoreCredit( $accountid ) {
	$row = GetSQL()->safequery( "SELECT CREDIT FROM USER WHERE ACCOUNT=".(int)$accountid )->fetch_array();
	
	return $row['CREDIT'] ;
	
	//$credit = $row['CREDIT'];
	//$credit *= 100;
	//$credit = floor($credit);
	
	//return $credit/100;
}

//---------------------------------------------------------------------------------------------
function ChargeStoreCredit( $accountid, $amount ) {
	$amount = (int)$amount;
	$sql = GetSQL();
	
	$sql->safequery( "UPDATE USER SET CREDIT=CREDIT-".$amount." WHERE ACCOUNT=".(int)$accountid." AND CREDIT >= ".$amount );
	$result = $sql->safequery( "SELECT ROW_COUNT()" );
	$row = $result->fetch_array();
	
	return $row['ROW_COUNT()'];
}

//---------------------------------------------------------------------------------------------
function GrantStoreCredit( $accountid, $amount ) {
	$amount = (int)$amount;
	$sql = GetSQL();
	
	$sql->safequery( "INSERT INTO USER (ACCOUNT,CREDIT) VALUES ($accountid,$amount) ".
	"ON DUPLICATE KEY UPDATE CREDIT=CREDIT+$amount" );
}

//---------------------------------------------------------------------------------------------
function FormatPrice( $price , $mode2=false) {
	if( $mode2 && $price < 100 ) return ($price) . '&cent;';
	return sprintf( "%s%d.%02d", $mode2? "$":"",$price/100,$price%100 );
}

//---------------------------------------------------------------------------------------------
function FormatDuration( $sec ) {
	if( $sec >= 60*60 ) {
		$a = round($sec / (60.0*60.0),1);
		return sprintf( $a == (int)$a ? "%.0f hour%s":"%.1f hour%s", $a, $a == 1.0 ? "" : "s" );
	} else if( $sec >= 60 ) {
		$a = round($sec / (60.0),0);
		return sprintf( "%.0f minute%s" , $a, $a == 1.0 ? "" : "s" );
	} else {
		return sprintf( "%d second%s", $sec, $sec == 1.0 ? "" : "s" );
	}
}

//---------------------------------------------------------------------------------------------
function FormatLength( $length ) {
	if( $length == 0 ) return "???";
	$feet = (int)($length / 12);
	$inches = $length - $feet * 12;
	
	return "$feet' $inches\"";
}

//---------------------------------------------------------------------------------------------
function FormatWeight( $pounds ) {
	if( $pounds == 0 ) return "???";
	return "$pounds lb" . ($pounds == 1 ? "" : "s") . ".";
}


function print_rp( $desc, $a ) {
	echo '<pre>
	' . $desc . '
	';
	print_r( $a );
	echo '
	</pre>';
}

function ItemPrice( $item ) {
	return FormatPrice($item['price'],true);
	/*$price = (float)$item['price'];
	if( $price < 1.0 ) {
		return ($price*100) . "&cent;";
	} else {
		return "$" . sprintf( ".2f", $price );
	}*/
}

function buyloggedincheck() {
	if( $_SESSION['loggedin'] != 1 ) {
		echo '<p>oops, an error occurred';
		if( isset( $_SESSION['paypal_payment'] ) ) {
			echo '; your transaction has been cancelled.';
			ClearPaymentCache();
		}
		echo '</p>';
		return FALSE;
	}
	return TRUE;
}

function GetItemStock() {
	$sql = GetSQL();
	$result = $sql->safequery( "SELECT * FROM STOCK" );
	
	$data = array();
	while( $row = $result->fetch_array() ) {
		
		$data[$row['ITEMID']] = $row['AMOUNT'];
	}
	return $data;
}
function AddItemStock( $itemid, $amount ) {
	// admin function
	$sql = GetSQL();
	
	$result = $sql->safequery( "INSERT INTO STOCK VALUES ($itemid,$amount) ON DUPLICATE KEY UPDATE AMOUNT=AMOUNT+$amount" );
}

function AddItemStockList( $items ) {
	
	$sql = GetSQL();
	
	foreach( $items as $item ) {
		if( !$sql->query( "INSERT INTO STOCK VALUES (".$item->id.",".$item->amount.") ON DUPLICATE KEY UPDATE AMOUNT=AMOUNT+".$item->amount ) ) {
			LogError( "AddItemStockList :: error while restocking item :: " . print_r($item,true),null,true );
		}
		
	}
	
}

//-------------------------------------------------------------------------------------------------
function TryRemoveItemStockList( $items, $checkonly=FALSE ) {
	$sql = GetSQL();
	if( !$checkonly ) $sql->safequery( "LOCK TABLE STOCK WRITE" );
	$result = $sql->safequery( "SELECT * FROM STOCK" );
	
	$stock = array();
	while( $row = $result->fetch_array() ) {
		$stock[$row['ITEMID']] = $row['AMOUNT'];
	}
	
	foreach( $items as $item ) {
		
		if( isset($stock[$item->id]) && ($stock[$item->id] >= $item->amount) ) {
			$stock[$item->id] -= $item->amount;
		} else {
			if( !$checkonly ) $sql->safequery( "UNLOCK TABLES" );
			return FALSE;
		}
	}
	
	if( $checkonly ) {
		return TRUE;
	}
	
	foreach( $items as $item ) {
		$sql->safequery( "UPDATE STOCK SET AMOUNT=". $stock[$item->id] . " WHERE ITEMID=" .$item->id );
	}
	
	$sql->safequery( "UNLOCK TABLES" );
	return TRUE;
}

function ItemStockString($amount) {
	$outofstock = "(OUT OF STOCK)";
	if( is_null($amount) || empty($amount) || $amount <= 0 ) return $outofstock;
	return "(".$amount . " in stock)";
}

function ClearPaymentCache() {
	unset( $_SESSION['transaction'] );
}


function PrintOutReceipt( $x ) {
	echo '<div class="receipt_container">';
		echo '<div class="receipt_top"></div>';
		echo '<div class="receipt">';
			echo '<div class="receipt_contents">';
				echo '<center><img src="rxgmartsmall.png"></center>';
				echo '<table>';
					echo '<tr class="toprow"><td class="qtycol">Qty</td><td class="itemcol">Item</td><td class="right">Price</td></tr>';
					
					end($x->items->items);
					$lastkey = key($x->items->items);
					
					foreach( $x->items->items as $key => $item ) {
						
						echo '<tr class="itemrow'. ($key === $lastkey ? ' border':'').'">'.
								'<td class="qtycol">'.$item->amount.'</td>'.
								'<td class="itemcol">'.$item->name . ($item->amount > 1 ? ('<br>&nbsp;&nbsp;' . FormatPrice($item->price) . '/ea'):'').'</td>'.
								'<td class="right">'.FormatPrice((float)$item->price * (int)$item->amount).'</td></tr>';
					}
					
					$subtotal = FormatPrice($x->subtotal);
					$total = FormatPrice($x->total);
					
					echo '<tr class="itemrow"><td class="itemcol" colspan="2">Subtotal:</td><td class="right">'.$subtotal.'</td></tr>';
					echo '<tr class="itemrow"><td class="itemcol" colspan="2">Shipping:</td><td class="right">'.FormatPrice($x->shipping).'</td></tr>';
					//$tax = $payment->transactions[0]->amount->details->tax;
					//if( (float)$tax > 0.0 ) {
					//	echo '<tr class="itemrow"><td class="itemcol" colspan="2">Tax:</td><td class="right">'.$tax.'</td></tr>';
					//}
					echo '<tr class="itemrow border"><td class="itemcol" colspan="2"><b>Total:</b></td><td class="right"><b>'.$total.'</b></td></tr>';
				//	if( $x->storecredit > 0 ) {
					echo '<tr class="itemrow"><td class="itemcol" colspan="2">CASH:</td><td class="right">'.FormatPrice($x->total ).'</td></tr>';
				//	}
					
				//	if( $x->payment_due > 0 ) {
				//		echo '<tr class="itemrow"><td class="itemcol" colspan="2">PAYPAL:</td><td class="right">'.FormatPrice($x->payment_due).'</td></tr>';
				//	}
					echo '<tr class="itemrow"><td class="itemcol" colspan="2">Change:</td><td class="right">0.00</td></tr>';
				echo '</table>';
				echo '<div class="receipt_footer">';
					echo '<span style="font-size:15px">STEAMID:'.$x->steamid.'</span><br>';
					
					$time = strtotime( $x->date );
					
					echo '<br><span style="font-size:15px">Sale #'.$x->saleid.'</span><br>'.
							'<br>Thank you for your patronage!<br>'.
							
							'<br>' . strftime( "%c", $time ) .
							'<br><br>www.reflex-gamers.com<br><br></center>';
				echo '</div>';
			echo '</div>';
		echo '</div>';
		echo '<div class="receipt_bottom"></div>';
	echo '</div>'; 
}


?>