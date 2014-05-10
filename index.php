<?php
// notes 
//steamid base =76561197960265728
//

require_once 'config.php'; 
require_once 'functions.php';
require_once 'opensession.php'; 

StripWWW();
OpenSession();

ProcessLogin();
PrintHeader( 'RXG Center','none' ); 
 
?>

<center><img src="http://cdn.bulbagarden.net/upload/thumb/6/6c/Unova_Pokemon_Center.png/200px-Unova_Pokemon_Center.png"></center>
<h1>RXG Center</h1>

<p><center>What is your business today?</center></p>
<?php

//<a style="text-decoration:none" href="dex.php"><div class="business">pokeman query</div></a>
//<br>
//<a style="text-decoration:none" href="trainers.php"><div class="business">trainers</div></a>
//<br>
?>

<a style="text-decoration:none" href="store.php"><div class="business">Store</div></a><br>
<a style="text-decoration:none" href="usingitems.php"><div class="business">How To Use Items</div></a><br>
<a style="text-decoration:none" href="bbs.php"><div class="business">Bulletin Board</div></a><br>
<a style="text-decoration:none" href="topcash.php"><div class="business">Biggest Spenders</div></a><br>
<a style="text-decoration:none" href="http://reflex-gamers.com/forums/forum.php"><div class="business">RXG Forums</div></a>

<?php 
if( UserIsAdmin() ) {
	echo '<br><a style="text-decoration:none" href="admin.php"><div class="business">admins</div></a>';
}
?>
 
<br>

<?php	 

PrintFooter();

?>
 
