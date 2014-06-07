<?php

require_once 'config.php';
require_once 'functions.php';
require_once 'opensession.php';
require_once 'sql.php';
require_once 'itemdata.php';

StripWWW();
OpenSession();
ProcessLogin();
PrintHeader( 'RXGMART',"",true,true,
	'<script type="text/javascript" src="tinybox.min.js"></script>
	<script type="text/javascript" src="sprintf.min.js"></script>
	<link rel="stylesheet" href="tbstyle.css" />'); 

?>

<script>

function showitem( id ) {
	TINY.box.show({url:'iteminfo.php?id=' + id,get:'id=4',width:600,height:400,opacity:20});
}

function showsnh( id ) {
	TINY.box.show({html:'it costs money to send all of those pixels!!',height:100,opacity:20});
}

</script>

<center><img src="pokemart.png" ></center>
<h1>RXGMART</h1>

<?php 

if( $_SESSION['loggedin'] != 1 ) {
	echo '<p>You need to sign in.</p>';
} else {
	//echo '<p><span style="font-weight:bold; font-size:20px; color:#f00">PROMOTION</span>: you can claim a free pokeball every day by clicking <a href="freepokeball.php">here</a></p>';
	echo '<p>You currently have:<br>';
	
	
	try {
		$inv = GetInventory( $_SESSION['accountid'] );

		if( !empty($inv) ) {
			echo '<table class="inventory">';
			echo '<tr>';
			$column = 0;
			foreach( $inv as $item ) {
				$id = $item['itemid'];
				$amt = $item['amount'];
				//echo $amt . " " . $GLOBALS['item_data2'][$id][ $amt == 1 ? 'name' : 'plural' ];
				if( $column == 5 ) {
					$column = 0;
					
					echo '</tr><tr>';
				}
				echo '<td><div class="item_pic" title="'. $GLOBALS['item_data2'][$id]['name'].'"><img src="'. $GLOBALS['item_data2'][$id]['usage'] . '.png"><div class="item_amt">' . $amt . '</div></div></td>';//<div class="name">' . $GLOBALS['item_data2'][$id]['name']. '</div></td>';
				$column++;
			}
			while( $column < 5 ) {
				$column++;
				echo '<td></td>';
			}
			echo '</tr></table>';
		} else {
			echo '<b>Nothing!</b>';
		}
		
	} catch( Exception $e ) {
		echo htmlspecialchars("<error!>");
	}
	
	echo '</p>';
	echo '<p style="font-size:24px">CASH: '.FormatPrice(GetStoreCredit($_SESSION['accountid']),true).'</p>';
	echo '<form action="addfunds.php"><input type="submit" value="Add Funds"></form>';
	echo '<hr>';
	echo '<p>You can buy stuff here with your CASH. Earn CASH by playing in RXG servers and picking it up!</p>';
	
	echo '<p>Click on an item to read more about it.</p>';
	
	//echo '<p>DEBUG FEATURE: purchase items with paypal account gaben@gmail.com password gabenewell</p>';
	try {
		$item_stock = GetItemStock();
		
		echo '<form action="checkout.php">'; 
		
		echo '<table class="buylist">';
		echo '<tr style="text-align:center"><td></td><td style="text-align:left">item</td><td>price</td><td>quantity</td>';
		foreach( $GLOBALS['item_data2'] as $item ) {
			if( !$item['buyable'] ) continue;
			$stock = isset($item_stock[$item['id']])?$item_stock[$item['id']]:0;
			echo '<tr>';
			echo '<td class="image"><img src="'.$item['usage'].'.png" onclick="showitem(' .$item['id'] .')"></td>';
			echo '<td class="desc" onclick="showitem(' .$item['id'] .')">'.$item['name'].' '.ItemStockString($stock).'</td>';
			echo '<td class="prices">'.ItemPrice($item).'</td>';
			
			if( $stock ) {
				echo '<td class="itemamount"><input data-price="'.$item['price'].'" class="itemamount addup" autocomplete="off" type="text" name="'.$item['id'].'" value="" ></td>';
			} else {
				echo '<td class="itemamount"><input class="itemamount" type="text" value="N/A" disabled></td>';
			}
			echo '</tr>';
			//echo '<img src="'.$item['image'].'"> '.$item['name'].' - <span class="prices">'.ItemPrice($item).'</span> x ';
			//echo '<input type="text" name="'.$key.'" value="" placeholder="quantity">';
		}
		echo '<tr><td></td><td>Subtotal</td><td class="prices" id="cart_subtotal">--</td><td></td></tr>';
		echo '<tr><td></td><td>Shipping & Handling <img src="help.png" onclick="showsnh()"></td><td class="prices">'.FormatPrice($shipping_fee,true).'</td><td></td></tr>';
		echo '<tr><td></td><td>Total</td><td class="prices" id="cart_total">--</td><td></td></tr>';
		echo '</table>';
		//echo '<p>add '.FormatPrice($shipping_fee,true).' for shipping and handling!</p>';
		echo '<img src="shoppingcart.png" style="vertical-align:bottom"><input type="submit" value="Checkout" >';
		
		echo '</form>';
		
		?>
		
		<script>
		
		$(".addup").keyup( function() {
			update_totals();
		});
		
		function FormatPrice( cents ) {
			if( cents < 100 ) {
				return cents + "&cent;";
			} else {
				return sprintf( "$%d.%02d", Math.floor(cents/100), cents%100 );
			}
		}
		
		function update_totals() {
		
			var subtotal = 0.0;
			
			$(".addup").each( function( index ) {
				var a = parseInt( $(this).val() );
				if( isNaN(a)   || a == 0 ) return;
				console.log( "poop " + a );
				subtotal += a * $(this).attr( 'data-price' );
			});
			var total = subtotal + <?php echo $shipping_fee ?>;
			
			if( subtotal == 0 ) {
				$("#cart_subtotal").text( "--" );
				$("#cart_total").text( "--" );
			} else {
				$("#cart_subtotal").html( FormatPrice( subtotal ) );
				$("#cart_total").html( FormatPrice( total ) );
			}
		}
		
		</script>
		<?php
	} catch ( Exception $e ) {
		echo "<p>error: couldn't connect to store</p>";
	}
	
} 



PrintFooter();

?>
