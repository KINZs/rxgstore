<?php

require_once 'config.php';
require_once 'functions.php';
require_once 'opensession.php';


StripWWW();
OpenSession();

AdminCheckpoint();

PrintHeader( "ADMIN CENTER" );

echo '<center><img src="bob.png" ></center>';
echo '<h1>admin center</h1>';
 
echo '<p>here you can do cool stuff</p>';
echo '<a href="admin_receipts.php">RECEIPT QUERY</a> <br>';
echo '<a href="admin_stats.php">STATISTICS</a><br>';
echo '<a href="admin_restock.php">RESTOCK GOODS</a> <br> ';
echo '<a href="admin_createtables.php">DATABASE CONSTRUCTION (do not enter)</a> <br> ';
echo '<a href="topcash.php">most CENTS</a> <br> ';
	
PrintFooter();

?>
