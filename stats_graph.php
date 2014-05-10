<?php

require_once 'config.php'; 

require_once 'config.php'; 
require_once 'sql.php';
require_once 'functions.php';
require_once 'opensession.php';
OpenSession(); 
AdminCheckpoint( ADMFLAG_RECEIPTQUERY ); 

//header("Content-type: text/html");
header("Content-type: image/png");

$graph = 0;
$graph_range = 0.0;
	$lookup = 30;

function QueryData() {
	$sql = GetSQL();
	
	global $lookup;
	$startdate = mktime( 0, 0, 0, date("n"),date("j")-$lookup+1 );
	$startdatef = strftime( "%Y-%m-%d",$startdate); 
	
	$result = $sql->safequery( "SELECT DATE,SETTLE FROM RECEIPTS WHERE DATE >= '$startdatef' AND STATE='OKAY' AND TYPE='CASH'" );
	
	$data =  array();
	for( $i = 0; $i < $lookup; $i++ ) {
		$data[$i] = 0.0;
	}
	
	while( $row = $result->fetch_array() ) {
		
		$day = floor((strtotime($row['DATE']) - $startdate) / (24*60*60)); 
		$data[$day] += $row['SETTLE'];
	}
	
	return $data;
}

function CreateGraph() {
	
	$data = QueryData();
	$datacount = count($data);

	$rangemin = 0;
	$rangemax = 0;
	foreach( $data as $d ) {
		if( $d > $rangemax ) {
			$rangemax = $d;
		}
	}
	
	$rangemax = $rangemax * 1.25;
	

	$graphheight = 128;
	$barwidth = 16;
	$graphwidth = $datacount*$barwidth;

	//$offset = $_GET['offset'];
	//echo 'test ' . ($length*5) . ',. '.$graphheight;
	$img     = imagecreatetruecolor($graphwidth,$graphheight);

	$white = imagecolorallocate( $img, 253,253,253 );
	$barcolor = imagecolorallocate($img, 59,100,104);
	$xp = imagecolorallocatealpha ( $img , 0,0,0,127 );

	$grid = imagecolorallocate( $img , 177,212,205  );
	imagefill( $img, 0, 0, $white );

	imagesetstyle( $img,array( $grid, $grid, $xp) );
	// draw grid
	for( $i = 1; $i < 10; $i++ ) {
		$y = $graphheight / 10 * $i;
		imageline( $img, 0,$y,$graphwidth, $y, IMG_COLOR_STYLED );
	}
	for( $i = 1; $i < $datacount/2; $i++ ) {
		$x = $graphwidth / ($datacount/2) * $i;
		imageline( $img, $x,0,$x, $graphheight, IMG_COLOR_STYLED );
	}
	// draw bars
	foreach( $data as $index => $d ) {
		$top = round($graphheight - ($d*$graphheight/$rangemax));
		$left = $index * $barwidth;

		imagefilledrectangle( $img,   $left,   $top,   $left + $barwidth-2,   $graphheight-1, $barcolor );
	}
	
	global $graph,$graph_range;
	$graph = $img;
	$graph_range = $rangemax;
}

CreateGraph();
$shell = imagecreatetruecolor( imagesx($graph) + 40, imagesy($graph)+32 );
$bg = imagecolorallocate( $shell, 230,230,235 );
$black = imagecolorallocate( $shell, 33,33,33 );
imagefill( $shell, 0, 0, $bg );

for( $i = 0; $i < 11; $i++ ) {
	imagestring( $shell,1,3,5 + $i * 12.6, FormatPrice( $graph_range * (10-$i) / 11),$black );
}

for( $i = 0; $i < $lookup; $i++ ) {
	$day = strftime( "%d",mktime( 0, 0, 0, date("n"),date("j")-$i ));
	
	imagestring( $shell,2,imagesx($shell)-24-$i*16,137, $day,$black );
}
//$enddate = strftime( "%d",mktime( 0, 0, 0, date("n"),date("j")));

//imagestring( $shell,5,30,140, $startdate,$black );
//imagestring( $shell,5,imagesx($shell)-100,140, $enddate,$black );

imagerectangle( $shell, 30-1,8-1,30+imagesx($graph), 8+imagesy($graph), $black );
imagecopy( $shell, $graph, 30, 8, 0, 0, imagesx($graph), imagesy($graph) );
//imagecopyresampled ( $shell , $graph , 0,0,0,0,256,64,imagesx($graph),imagesy($graph) );


// output result as PNG

//header("Content-type: image/png");
imagepng($shell);

// delete resource
imagedestroy($shell);
imagedestroy($graph);

?>