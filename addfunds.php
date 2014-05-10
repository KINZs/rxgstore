<?php

require_once 'config.php';
require_once 'sql.php'; 
require_once 'functions.php';
require_once 'opensession.php';
require_once 'paypal.php';

StripWWW();
OpenSession();
ProcessLogin();


PrintHeader( "RXGMART", 'store.php'
	);

echo '<center><img src="pokemart.png" ></center>';
echo '<h1>Add Funds</h1>';

if( $_SESSION['loggedin'] != 1 ) {
	echo '<p>You need to sign in.</p>';
	PrintFooter();
	exit;
} else {
	?>

	<center>
	<hr>
	<p>

	<?php

		for( $i = 0; $i < 7; $i++ ) {
			echo '
			<!-- PayPal Logo --> <a href="https://www.paypal.com/webapps/mpp/paypal-popup" 
	title="How PayPal Works" 
	onclick="javascript:window.open(\'https://www.paypal.com/webapps/mpp/paypal-popup\',\'WIPaypal\',\'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=1060, height=700\'); return false;">
	<img src="https://www.paypalobjects.com/webstatic/mktg/logo/pp_cc_mark_37x23.jpg" border="0" alt="PayPal Logo" 
	style="vertical-align:bottom"></a>
	<!-- PayPal Logo -->
			';
			
		}
	?>

	<hr>
	<p>
	Add funds to your rxg CASH with PayPal&trade;.
	</p>
	
	<script src="sprintf.min.js"></script>
	<script>

	function updatemorecash() {
		var amt = $("#morecash_cost").val();
		amt = parseFloat(amt);
		
		if( isNaN(amt) ) {
			$("#morecash_send").val("0");
			$("#morecash_amt").val("0");
			$("#morecash_submit").attr("disabled","yes");
			
		} else if( amt < 25.00 ) {
			$("#morecash_send").val("0");
			$("#morecash_amt").val("must be at least $25.00");
			$("#morecash_submit").attr("disabled","yes");
		} else {
			amt = Math.floor(amt * 100);
			
			$("#morecash_send").val( amt );
			
			amt = Math.ceil(amt * 1.1);
			$("#morecash_amt").val( sprintf( "%d.%02d", amt/100, amt%100 ) );
			$("#morecash_submit").removeAttr("disabled");
		}
	}
	
	function poopdick() {
		$('#poop1').hide(); $('#poop2').show();
		updatemorecash();
	}
	</script>

	<p>Please select an option:</p>
	
	<table class="addfunds"> 
	<?php
	
	function PrintOption( $cents , $bonus, $option , $lel="") {
		echo '<tr>';
		echo '<td>$'.FormatPrice($cents).' REALMONEY for $'.FormatPrice($cents*$bonus).' CASH';
		
		if( $bonus > 1 ) {
			echo ' (+' .(($bonus-1)*100) . '% ' . $lel . ')';
		}
		echo '</td>';
		echo '<td><form action="buycash.php"><input type="hidden" name="option" value="'.$option.'"><input type="submit" value="BUY"></form></td>
		</tr>';
	}
	PrintOption( 100, 1, 1 );
	PrintOption( 250, 1.02, 2, "BONUS!" );
	PrintOption( 500, 1.03, 3, "BONUS!" );
	PrintOption( 1000, 1.05, 4, "BONUS!!" );
	PrintOption( 2500, 1.1, 5, "<span style='font-weight:bold; color:#009900'>BEST VALUE</span>!!!!!" );
	
	/*
	<form action="buycash.php">
	<tr>
		<td>
			
		<input type="submit" value="Buy $1.00 of CASH for $0.99 (save 1%!)"><br>
	</form>
	<form action="buycash.php">
		<input type="hidden" name="option" value="2">
		<input type="submit" value="Buy $2.50 of CASH for $2.45 (save 2%!)"><br>
	</form>
	<form action="buycash.php">
		<input type="hidden" name="option" value="3">
		<input type="submit" value="Buy $5.00 of CASH for $4.85 (save 3%!!)"><br>
	</form>
	<form action="buycash.php">
		<input type="hidden" name="option" value="4">
		<input type="submit" value="Buy $10.00 of CASH for $9.50 (save 5%!!!)"><br>
	</form>
	<form action="buycash.php">
		<input type="hidden" name="option" value="5">
		<input type="submit" value="Buy $25.00 of CASH for $22.50 (save 10%!!!!!)"><br>
	</form>*/
	?>
	</table> 
	
	<div id="poop1">
		<form id="poop1" action="javascript:poopdick();">
			<input type="submit" value="BUY MORE THAN THAT MUCH CASH LOL"><br>
		</form>
	</div>
	
	<div id="poop2" style="border:1px solid #333; padding: 4px; display:none;" ><br>
		<i>Just how much cash do you <b>desire</b>??</i> 
		<form action="buycash.php">
			<input type="hidden" name="option" value="6">
			<input type="hidden" name="amount" value="2500" id="morecash_send">
			<table>
			<tr><td>Spend this much REALMONEY:</td><td>$<input type="text" value="25.00"  id="morecash_cost" onkeyup="updatemorecash()"></td></tr>
			<tr><td>Receive this much CASH:</td><td>$<input type="text" id="morecash_amt" value="" disabled="yes"></td></tr>
			</table>
			<input type="submit" id="morecash_submit" value="Sounds Good!!"><br>
			
		</form>
		<br>
	</div>

	</center>


	<?php
	echo '<hr>';
	echo '<p><i>Mmm... yes... spend that hard earned money on pixels!</i></p>';
		
	echo '<hr>';
	echo "<p>Don't use your mom's or big brother's money without permission because they will find out and get angry at us lmao. If you're a <i>grown up</i> who pays his own bills disregard this.</p>";

	echo "<p>100% of dollar bills obtained is put back into the community; feel confident while giving us your moneys!</p>"; 
}

PrintFooter();


?>
