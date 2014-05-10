<?php

	require_once 'config.php';
	require_once 'functions.php';
	require_once 'opensession.php';
	require_once 'sql.php';
	
	StripWWW();
	OpenSession();
	ProcessLogin();
	PrintHeader( 'trainers'  ); 
?>

<center><img src="trainer.png" ></center>
<h1>trainers</h1>

<p>enter a profile url or a steam id<?php if( $_SESSION['loggedin']==1 ) echo ' or "me"'; ?> to perform a lookup</p>
<p>in the steam browser you can right click a person's profile page and press Copy Page URL, and you can paste that here </p>

<form>

<input type="hidden" name="page" value="trainers">
<input class="pokemanlookup" type="text" value="" name="id" placeholder="http://steamcommunity.com/id/example">
</form><br/>

<script src="trainers.js"></script>

<?php
$id = isset($_GET['id'])?$_GET['id']:"";

if( empty($id) ) {
	
	$toptrainers = GetTopTrainers();
	if( $toptrainers != FALSE ) {
		echo '<center><h2>TOP POKEMAN TRAINERS</h2></center>';
		
		echo '<table class="toptrainers">';
		echo '<tr><th>trainer</th><th>pokemans</th></tr>';
		foreach( $toptrainers as $i ) {
			echo '<tr><td>' . $i['name'] . '</td>' .
				 '<td class="ttcount">' . $i['captures'] . '</td>'.
				 '</tr>';
			
		}
		echo '</table>';
	}
	
} else {

	do { 
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
		
		$steamid64 = $indexes['STEAMID64'];
		if( is_null($steamid64) ) {
			echo "<p><b>error: invalid ID or steam is unavailable</b></p>";
			break;
		}
		$steamid64 = $values[ $steamid64[0] ]['value'];
		$name = $values[ $indexes['STEAMID'][0] ]['value'];
		$avatar = $values[ $indexes['AVATARMEDIUM'][0] ]['value'];
		$account = AccountIDFromSteamID64($steamid64);
		$total = GetTotalPokemans($account);
		
		
		echo '<table><tr><td><img src="'.$avatar.'" id="deximg2"></td><td class="pmname2">'.$name.'</td></tr></table>';
		echo "<p>Total pokemans captured: $total ";
		if( $total == 0 ) echo "(what a newb)";
		echo "</p>";
		
		if( $total != 0 ) {
			
			
			$pokemans = GetPokemansCaptured( $account, 0, 50 );
			
			$steamid_list="";
			foreach( $pokemans as $p ) {
				$steamid_list = $steamid_list . SteamID64FromAccountID($p['TARGET']) . ",";
			}
			$steamid_list = substr($steamid_list,0, strlen($steamid_list)-1); // remove trailing comma
			$species_data = GetMultiUserData( $steamid_list );
			 
			
			if( $species_data != FALSE ) {
				
				$index = 0;
				foreach( $species_data as $player ) {
					$aid = AccountIDFromSteamID64( $player->steamid );
		
					foreach( $pokemans as &$p  ) {
						if( $p['TARGET'] == $aid ) {
		
							$p['SPECIES'] = "<a href=\"?page=dex&id=http://steamcommunity.com/profiles/" . SteamID64FromAccountID($p['TARGET']) . "\"><img src=\"" . $player->avatar . "\"></a> ".$player->personaname;
						}
					}
					unset( $p );
				}
			}
			 
			echo "<table class=\"pokemanlisting\"><tr><th>nickname</th><th>date captured</th><th>species</th></tr>";
			
			$owner = UserIsSteamID( $steamid64 ) || UserIsAdmin(); 
			
			foreach( $pokemans as $p   ) {
				echo "<tr>";
				
				if( $owner ) {
					echo '<td><span id="pmn'.$p['ID'].'">'.$p['NICKNAME'].'</span> <span style="color:blue;cursor:pointer" onclick="EditNickname(this,'.$p['ID'].' ); return false">[edit]</span></td>';
					//echo "<td><a href=\"?page=editpokeman&id=". $p['ID'] . "\">" . $p['NICKNAME'] . "</a></td>";
				} else {
					echo "<td>" . $p['NICKNAME'] . "</td>";
				}
				echo "<td>" . strftime( "%c", $p['TIME'] ) . "</td>";
							
				if( !isset( $p['SPECIES'] ) ) {
					echo "<td><a href=\"?page=dex&id=http://steamcommunity.com/profiles/" . SteamID64FromAccountID($p['TARGET']) . "\">????</a></td>";
				} else {
					echo "<td>".$p['SPECIES']."</td>";
				}
				echo "</tr>";
			}
			
			echo "</table>";
			
		}
			
	} while(false);
}

PrintFooter();

?>

