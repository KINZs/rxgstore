<?php

require_once 'config.php';
require_once 'functions.php';
require_once 'opensession.php';

StripWWW();
OpenSession();
ProcessLogin();
PrintHeader( 'pokemandex => edit', 'dex.php' ); 
?>

<center><img src="dexter.png" ></center>
<h1>pokeman info</h1>

<?php
	if( !isset( $_SESSION['EDITPM'] ) ) {
		echo 'an error occurred';
	} else {
		$pokeman = $_SESSION['EDITPM'];
		
		$cancelreturn = 'onkeydown="if (event.keyCode == 13) {return false;}"';
		echo '<form action="setpm.php">';
		//echo '<input type="hidden" name="page" value="setpm">';
		echo '<p>you can edit your pokeman data here, the guidelines are not rules.</p>';
		echo '<p style="background-color:#fff">current name: '.$_SESSION['EDITPMNAME'].'</p>';
		echo '<p><b>type:</b> try to describe yourself with one word.... examples: RAGING, SWIMMING, RECLUSIVE, EXCELLENT</p>';
		echo '<input type="text" value="'. $pokeman['TYPE'] .'" name="TYPE" '.$cancelreturn.'>';
		echo '<p><b>element:</b> what is the primary element of your pokeman, see <a href="http://en.wikipedia.org/wiki/Periodic_table">http://en.wikipedia.org/wiki/Periodic_table</a>. examples: lithium, silicon, water, cardboard</p>';
		echo '<input type="text" value="'. $pokeman['ELEMENT'] .'" name="ELEMENT" '.$cancelreturn.'>';
		echo '<p><b>length</b> in inches; how LONG your pokeman is</p>';
		echo '<input type="text" value="'. $pokeman['LENGTH'] .'" name="LENGTH" '.$cancelreturn.'>';
		echo '<p><b>weight</b> in pounds</p>';
		echo '<input type="text" value="'. $pokeman['WEIGHT'] .'" name="WEIGHT" '.$cancelreturn.'>';
		echo '<p><b>habitat:</b> where you can be found, such as a server name, your home address, a planet, soil type, or something else ridiculous</p>';
		echo '<input type="text" value="'. $pokeman['HABITAT'] .'" name="HABITAT" '.$cancelreturn.'>';
		echo '<p><b>description:</b> a short or long description of your pokeman\'s behavior, try to keep it short though.</p>';
		echo '<textarea style="width:80%; height:240px;" name="DESCRIPTION">'. $pokeman['DESCRIPTION'] .'</textarea>';
		echo '<br/><br/><input type="submit" value="press here to commit">';
		echo '</form>';
	}
?>

<?php

PrintFooter();

?>
