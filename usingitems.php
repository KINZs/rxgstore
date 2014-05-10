<?php

require_once 'config.php';
require_once 'functions.php';
require_once 'opensession.php';  

StripWWW();
OpenSession();
ProcessLogin();
PrintHeader( 'Using Items'); 

?> 
<h1>How To Use Items</h1>

<p>To get an item to use, you need to first buy it in the store. Click on the store tab on the main page to access your inventory and things to buy. You need CASH to buy things. Pick up CASH in game by pressing E on it. See how much cash you have with the !cash command.</p>

<p>Type in a quantity of what you want and checkout, you will be prompted with a checkout page to confirm your purchase.</p>

<p>Your ingame inventory will be updated automatically after a short period after buying items. Using the /items command ingame will list your items.</p>

<p>To use an item, use the /useitem command. This will bring up a menu of your items that you can use. You can also do /useitem "itemname" to use an item instantly.</p>

<p>You can make a certain key use a certain item by typing in console:<br><center>bind [KEY] "useitem [ITEM]"</center><br> Item names can be found with the /items command or by clicking the item icon in the RXG Store to reveal more information.</p>
<?php
PrintFooter();

?>
