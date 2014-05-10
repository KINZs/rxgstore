<?php

require_once 'config.php';
require_once 'functions.php';
require_once 'opensession.php';

StripWWW();
OpenSession();
ProcessLogin();
PrintHeader( 'BBS' ); 

?>
<script src="dex.js"></script>
<center><img src="bbs.png" ></center>
<h1>Bulletin Board</h1>

<div class="bbs_content" id="bbs_content">
	(loading)
<?php

?>
</div>
<?php
if( $_SESSION['loggedin'] ) {

?>
	<form action="javascript:PostBBS()">
		<table style="width:100%"><tr><td><input type="text" name="content" id="bbs_post" class="bbs_input" style="width:100%"></td><td style="width:64px"><input type="submit" value="Post" id="bbs_submit" style="width:100%"></td></tr></table>
	</form>
	
<?php

}

?>

<script>

function RefreshContent() {
	$.get( "bbs_content.php" )
		.done( function(data) {
			$("#bbs_content").html( data );
		});
}

function PostBBS() {
	var post_content = $("#bbs_post").val();
	post_content = post_content.trim();
	
	if( post_content == "" ) return;
	
	if( post_content.length > 256 ) {
		alert( "too many words." )
		return;
	}
	
	$.post( "bbs_post.php", {content: post_content} )
		.done( function(data) {
			data = data.trim();
			if( data == 'NO' ) {
				alert( "Unable to post." );
			} else {
				//alert( "posted1 to post."+data );
				RefreshContent();
			}
			
		})
		.fail( function() {
			alert( "Unable to post." );
		})
		.always( function() {
			$("#bbs_submit").removeAttr( "disabled" );
		});
	
	$("#bbs_submit").attr("disabled","disabled");
	$("#bbs_post").val("");
}

$().ready( function() { 
	RefreshContent();
});


</script>

<?php
PrintFooter();
?>