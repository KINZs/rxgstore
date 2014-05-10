<?php

require_once 'config.php';
require_once 'functions.php';
require_once 'opensession.php';

StripWWW();
OpenSession();
ProcessLogin();
PrintHeader( 'pokemandex' ); 

?>
<script src="dex.js"></script>
<center><img src="dexter.png" ></center>
<h1>pokemandex</h1>
<p>here you can look up information about a pokeman</p>
<p>enter a profile url or a steam id<?php if( $_SESSION['loggedin']==1 ) echo ' or "me"'; ?> to perform a lookup</p>
<?php if( $_SESSION['loggedin']==1 ) echo '<p>you can also edit your own information if you look up yourself</p>'; ?>
<p>in the steam browser you can right click a person's profile page and press Copy Page URL, and you can paste that here </p>

<form action="javascript:LookupPokeman()">
<input class="pokemanlookup" id="pokeman" type="text" value="" name="id" placeholder="http://steamcommunity.com/id/example">
</form><br/>

<div id="contenthook"></div>

<?php 

	
?>