<?php

require_once "sql.php";

$item_data2 = array();

LoadItemData();

function LoadItemData() {
	$sql =GetSQL();
	global $item_data2;
	$result = $sql->safequery( "SELECT * FROM ITEMINFO" );
	if( !$result ) die();
	
	while( $a = $result->fetch_array() ) {
		$item_data2[$a['ID']] = array(
			"id"		=> $a['ID'],
			"name"		=> $a['NAME'],
			"plural"	=> $a['PLURAL'],
			"usage"		=> $a['ITEM'],
			"buyable"	=> $a['BUYABLE'],
			"price"		=> $a['PRICE'],
			"servers"	=> $a['SERVERS']
		);
	}
	
	
}

/*
$item_data = array(
	"item_pokeball" => array(	
		"id" 		=> 1,
		"name" 		=> "pokeball",
		"plural" 	=> "pokeballs",
		"usage"		=> "pokeball",
		"buyable" 	=> TRUE,
		"price" 	=> 0.10,
		"image" 	=> "pokeball.png"
		
	),
	"item_radio" => array (
		"id" 		=> 2,
		"name"		=> "disposable radio",
		"plural"	=> "disposable radios",
		"usage"		=> "radio",
		"buyable"	=> TRUE,
		"price"		=> 0.10,
		"image"		=> "radio.png"
	),
	"item_negev" => array (
		"id"		=> 3,
		"name"		=> "negev",
		"plural"	=> "negevs",
		"usage"		=> "negev",
		"buyable"	=> TRUE,
		"price"		=> 0.10,
		"image"		=> "negev.png"
	),
	"item_cookie" => array (
		"id"		=> 4,
		"name"		=> "cookie",
		"plural"	=> "cookies",
		"usage"		=> "cookie",
		"buyable"	=> TRUE,
		"price"		=> 0.15,
		"image"		=> "cookie.png"
	),
	"item_nuke" => array (
		"id"		=> 5,
		"name"		=> "nuke",
		"plural"	=> "nukes",
		"usage"		=> "nuke",
		"buyable"	=> TRUE,
		"price"		=> 100.0,
		"image"		=> "nuke.png"
	)
);

$item_data2 = array(); // indexed by ID
foreach( $item_data as $item ) {
	$item_data2[$item['id']] = $item;
}

*/

?>
