( function($) {
	$( '#customize-theme-controls' ).on( 'click', '#abc-reset-theme-options', function(e) {
		e.preventDefault();

        if ( window.confirm( Abacus_Customizer.confirmText ) ) {
			window.location.href = Abacus_Customizer.customizerURL + '?abc-reset=' + Abacus_Customizer.exportNonce;
		}
	} );
} )(jQuery);