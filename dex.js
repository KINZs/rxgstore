
var current_ajax;

function LookupPokeman() {
	var id = $("#pokeman").val();
	$("#contenthook").html(
		'<div id="dexborder"><div id="dexdisplay"><p><img src="ajax-loader.gif"> LOOKING UP "' + id + '"...</p></div>pokemandex v1.0.2</div>' );
	
	if( current_ajax != null ) current_ajax.abort();
	
	current_ajax = $.get( "dexquery.php", { id: id } )
		.done( function(data) {
			$("#contenthook").html(data);
			current_ajax = null;
		})
		.fail( function(xhr, text_status, error_thrown) {
			if (text_status == "abort") return;
			$("#contenthook").html('<div id="dexborder"><div id="dexdisplay"><p>couldn\'t retrieve data.</p></div>pokemandex v1.0.2</div>');
			current_ajax = null;
		});
	
}
