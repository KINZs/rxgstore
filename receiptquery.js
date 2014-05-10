var busy=false;
function ReceiptQuery1() {
	$("#page").val(0);
	ReceiptQuery();
}
function ReceiptQuery() {
	if( busy ) {
		alert( "a query is in progress ,please wait" );
		return;
	}
	busy=true;
	
	
	$("#thinker").html('<center><img src="ajax-loader.gif"></center>');
	
	var paypalid = $('#paypalid').val();
	var type = $('#type').val();
	var saleid = $('#saleid').val();
	var after = $('#after').val();
	var before = $('#before').val();
	var state = $('#state').val();
	var page = $('#page').val();
	
	var data = new Object();
	if( paypalid != "" ) data.paypalid = paypalid;
	if( type != "" && type != "NULL" ) data.type = type;
	if( saleid != ""  ) data.saleid = saleid;
	if( after != "" ) data.after = after;
	if( before != "" ) data.before = before;
	if( state != "" && state != 'NULL' ) data.state = state;
	if( page != "" && page != 0 ) data.page = page;
	
	$.get( "receiptlist.php", data )
		.done( OnSuccess )
		.fail( OnFailure );
}

function OnSuccess( data ) {
	
	//location.hash = "receiptlist";
	//data = "<div style=\"font-size:11px\">"
	//	+ data +
	//	"</div>";
	$("#receipt_results").html( data );
	$("#thinker").html('');
	
	busy=false;
	
}

function OnFailure() {
	ShowError();
	busy=false;
}

function ShowError() {
	$("#receipt_results").html('<center>an error happened</center>');
	$("#thinker").html('');
}
	
var displayajax;
var dacell;
function ReceiptClicked( cell, sid ) {
	 
	console.log( this );
	
	//$("#thinker").html('<center><img src="ajax-loader.gif"></center>');
	
	//$("#receipt_display").html('<center><img src="ajax-loader.gif"></center>');
	
	dacell=cell;
	$(cell).attr( "class", "receiptentry loading")
	if( displayajax != null ) displayajax.abort();
	displayajax = $.get( "formatreceipt.php", { id:sid } )
		.done( function(data) {
			for( var i = 1; i < 11; i++ ) {
				$("#result"+i).attr( "class", "receiptentry")
			}
			$(dacell).attr( "class", "receiptentry selected")
			$("#receipt_display").html( data );
			//location.hash = "receipt";
			displayajax=null;
			//$("#thinker").html('');
	
		})
		.fail( function(xhr, text_status, error_thrown) {
			if (text_status == "abort") return;
			$(dacell).attr( "class", "receiptentry")
			$("#receipt_display").html('<center>an error happened</center>');
			displayajax=null;
			//$("#thinker").html('');
		});
	
	
	
}

function PrevPage() {

	var page = $("#page").val();
	page--;
	if( page < 0) page = 0;
	$("#page").val(page);
	ReceiptQuery();
}

function NextPage() {
	var page = $("#page").val();
	page++;
	$("#page").val(page);
	ReceiptQuery();
}