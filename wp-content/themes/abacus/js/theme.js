(function ( $ ) {
	$( '.main-navigation, .top-navigation' )
	   .find( '.menu-item-has-children > a' ).after( '<i class="fa fa-caret-down"></i>' )
	   .end()
	   .find( '.menu-item-has-children i' ).on( 'click', function () {
	   var $el = $( this ),
	   	$parent = $el.parent();

	   $el.parent().parent().find( 'li' ).not( $parent ).removeClass( 'menu-open' );
	   $el.parent().toggleClass( 'menu-open' );
	} );

	$( '.nav-open-top-menu' ).click( function () {
	   $( 'body' ).toggleClass( 'top-menu-open' );
	} );

	$( '.nav-search i' ).click( function () {
	   $( 'body' ).addClass( 'search-open' );
	   $( '.search-bar' ).find( '.search-field' ).focus();
	} );

	$( '#page' ).on( 'click', '.close-search', function () {
	   $( 'body' ).removeClass( 'search-open' );
	} );

	var header_height = $( '#masthead' ).outerHeight(),
	   $body = $( 'body' );

	if ( 'fixed' === $( '#masthead' ).css( 'position' ) ) {
	   $body.css( 'padding-top', header_height );
	   header_height = ( $body.hasClass( 'admin-bar' ) ) ? header_height + 32 : header_height;
	   header_height = ( $body.hasClass( 'home' ) ) ? 380 + header_height : header_height;
	   $( window ).bind( 'scroll', function () {
	   	if ( $( window ).scrollTop() > ( header_height - 66 ) ) {
	   		$body.addClass( 'shrink-nav' );
	   	} else {
	   		$body.removeClass( 'shrink-nav' );
	   	}
	   } ).scroll();
	}

	var $products = $( '.products .product' );
	$products.each( function ( e ) {
	   setTimeout( function () {
	   	$products.eq( e ).addClass( 'post-loaded animate' );
	   }, 200 * ( e + 1 ) );
	} );

	if ( $( '#back-to-top' ).length ) {
	   var scrollTrigger = 100, // px
	   	backToTop = function () {
	   		var scrollTop = $( window ).scrollTop();
	   		if ( scrollTop > scrollTrigger ) {
	   			$( '#back-to-top' ).addClass( 'show' );
	   		} else {
	   			$( '#back-to-top' ).removeClass( 'show' );
	   		}
	   	};
	   backToTop();
	   $( window ).on( 'scroll', function () {
	   	backToTop();
	   } );
	   $( '#back-to-top' ).on( 'click', function ( e ) {
	   	e.preventDefault();
	   	$( 'html,body' ).animate( {
	   		scrollTop : 0
	   	}, 700 );
	   } );
	}
})( jQuery );