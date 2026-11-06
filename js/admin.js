jQuery( document ).ready( function( $ ) {
	$( "#clicky-tabs" ).find( "a" ).click( function() {
		$( "#clicky-tabs" ).find( "a" ).removeClass( "nav-tab-active" );
		$( ".clicky_tab" ).removeClass( "active" );

		var id = $( this ).attr( "id" ).replace( "-tab", "" );
		$( "#" + id ).addClass( "active" );
		$( this ).addClass( "nav-tab-active" );
		$( "#return_tab" ).val( id );
	} );

	// Init.
	var activeTab = window.location.hash.replace( "#top#", "" );

	// Default to first tab.
	if ( activeTab === "" || activeTab === "#_=_" ) {
		activeTab = $( ".clicky_tab" ).attr( "id" );
	}

	$( "#" + activeTab ).addClass( "active" );
	$( "#" + activeTab + "-tab" ).addClass( "nav-tab-active" ).click();
} );
