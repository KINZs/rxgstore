<?php 
	
	require_once "itemdata.php";
	require_once "functions.php";
	
	if( !isset($_GET['id']) ) die();
	
	$id = $_GET['id'];
	if( !isset( $item_data2[$id] ) ) die();
	$data = $item_data2[$id];
	if( !$data['buyable'] ) die();
	
	
	echo '<div class="iteminfo">';
	echo '<img src="'.$data['usage'].'.png">';
	echo '<p>Name: "' . $data['name'] . '"</p>';
	echo '<p>Price: $' . FormatPrice( $data['price'] ) . '</p>';
	echo '<p>Ingame Usage: ' . ($data['usage']==""?"N/A":('"'.$data['usage'].'"')) . '</p>';
	echo '<p>Usable On: ' . ($data['servers']) . '</p>';

	$file = "itemdesc/".$data['usage'].".txt";
	if( file_exists( $file ) ) {
		echo '<p>Description:</p>' . file_get_contents( $file );
	} else {
		echo '<p>Description: N/A</p>';
	}
	echo '</div>';

?>