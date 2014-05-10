function EditNickname( owner, id ) {
	
	var currentname = $("#pmn"+id).text();
	var nick=prompt("rename pokeman:",currentname);
	if( nick === null ) return ;
	nick = nick.trim();
	if( nick == "" || nick == currentname ) return ;

	$("#pmn"+id).text( nick );
	
	$.get( "changepokeman.php", { id:id, nick:nick } )
	.done( function(data,text_status,xhr) {
		
		if( data.trim() != "OK" ) 
			alert( "couldn't rename pokeman." );
	})
	.fail( function(xhr, text_status, error_thrown) {
		alert( "NOTICE: couldn't rename pokeman." ); 
	}); 
	
}
