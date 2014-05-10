<?php

require_once 'config.php';
require_once 'functions.php';
require_once 'sql.php';
require_once 'opensession.php';

StripWWW();
OpenSession();

header('Content-type: text/html');

if( !isset($_GET['id']) || empty($_GET['id']) ) {
	echo '<div id="dexborder"><div id="dexdisplay"><p>invalid input</p></div></div>';
	exit;
}
$id = $_GET['id'];

	
echo '<div id="dexborder"><div id="dexdisplay">';
	do {
		echo "<p style=\"font-size:12px\">RESULT FOR \"$id\"</p>";
	
		$idurl = ParseId( $id );
		if( $idurl === FALSE ) {
			echo "<p><b>error: invalid ID!</b></p>";
			break;
		}
		
		$data = GetContents( $idurl . "?xml=1" );
		
		$parser = xml_parser_create('');
		$values = array();
		$indexes = array();
		xml_parse_into_struct( $parser, $data, $values, $indexes );
		xml_parser_free($parser);
		
		
		if( !isset($indexes['STEAMID64']) ) {
			echo "<p><b>error: invalid ID or steam is unavailable</b></p>";
			break;
		}
		$steamid64 = $indexes['STEAMID64'];
		$steamid64 = $values[ $steamid64[0] ]['value'];
		$name = $values[ $indexes['STEAMID'][0] ]['value'];
		$avatar = $values[ $indexes['AVATARMEDIUM'][0] ]['value'];
		
		echo '<table><tr><td><img src="'.$avatar.'" id="deximg"></td><td class="pmname">'.$name.'</td></tr></table>';
		
		$pokeman = GetPokemanInfo( AccountIDFromSteamID64( $steamid64 ) );
		echo "<p>" . AnFunction($pokeman['TYPE']) . " \"" . $pokeman['TYPE'] . "\" pokeman<br>";
		echo "ELEMENT: " . $pokeman['ELEMENT'] . "<br>";
		echo "LENGTH: " . FormatLength($pokeman['LENGTH']) . "<br>";
		echo "WEIGHT: " . FormatWeight($pokeman['WEIGHT']) . "<br>";
		echo "HABITAT: " . $pokeman['HABITAT'] . "<br>";
		echo "CAPTURED: " . GetPokemanCaptureCount(AccountIDFromSteamID64($steamid64));
		echo "</p>";
		echo "<p id=\"pokemandesc\">".  $pokeman['DESCRIPTION'] ."</p>";
		if( UserIsAdmin() || UserIsSteamID( $steamid64 ) ) {
			$_SESSION['EDITPM'] = $pokeman;
			$_SESSION['EDITPMNAME'] = $name;
			echo "<p><a href=\"editpm.php\">[edit]</a></p>";
		}
	} while(false);
echo '</div>pokemandex v1.0.2</div>';
