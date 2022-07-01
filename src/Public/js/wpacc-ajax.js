/**
 * WPACC javascript functions for forms.
 *
 * @author Eric Sprangers.
 * @since  1.0.0
 * @package WP_Accounting
 */

/* global wpaccData */

/**
 * Jquery part
 */
( function( $ ) {
	'use strict';

	/**
	 * Actions after document ready or ajax ready
	 */
	function onload() {
	}

	/**
	 * Perform Ajax form call.
	 *
	 * @param {array} params
	 */
	function doAjaxForm( params = [] ) {
		const form     = document.getElementById( 'wpacc-form' );
		const formData = new FormData( form );
		/**
		 * Validate the input data
		 */
		if ( ! form.reportValidity() ) {
			return false;
		}
		/**
		 * Add form handling data to the input
		 */
		formData.append( 'action', 'wpacc_formhandler' );
		formData.append( '_ajax_nonce', $( 'form[data-wpacc_nonce]' ).data( 'wpacc_nonce' ) );
		formData.append( 'display', $( 'form[data-wpacc_display]' ).data( 'wpacc_display' ) );
		$.each(
			params,
			function() {
				let key = Object.keys( this )[0];
				formData.append( key, this[ key ] )
			}
		);
		/**
		 * Execute the Ajax request.
		 *
		 * @param {{ajaxurl:string}} ajaxurl
		 */
		$.ajax(
			{
				data:        formData,
				type:        'POST',
				url:         wpaccData.ajaxurl,
				processData: false,
				contentType: false,
			}
		).done(
			function ( response ) {
				if ( '' !== response.data ) {
					$( '#wpacc-main' ).html( response.data.main );
					$( '#wpacc-business' ).html( response.data.business );
				}
			}
		).fail(
			function( jqXHR ) {
				$( '#wpacc-main' ).html( jqXHR.responseJSON.message );
			}
		);
	}

	/**
	 * Perform Ajax menu call.
	 *
	 * @param {string} menu
	 */
	function doAjaxMenu( menu ) {
		$.ajax(
			{
				data: {
					'action':       'wpacc_menuhandler',
					'menu':         menu,
				},
				type:        'GET',
				url:         wpaccData.ajaxurl,
			}
		).done(
			function ( response ) {
				$( '#wpacc-main' ).html( response.data.main );
				$( '#wpacc-business' ).html( response.data.business );
			}
		);
	}

	/**
	 * Ajax ready
	 */
	$( document ).ajaxComplete(
		function() {
			onload();
		}
	);

	/**
	 * Document ready
	 */
	$(
		function() {
			/**
			 * Events for the menu sections.
			 */
			$( 'a[data-menu]' ).on(
				'click',
				function() {
					$( '.wpacc-menu a' ).removeClass( 'wpacc-menu-selected' );
					$( this ).addClass( 'wpacc-menu-selected' );
					doAjaxMenu( $( this ).data( 'menu' ) );
				}
			);
			$( '#wpacc-menu-dropdown' ).on(
				'click',
				function( e ) {
					$( '#wpacc-menu ul' ).slideToggle( 500 );
					e.preventDefault();
				}
			);
			$( window ).on(
				'resize',
				function() {
					if ( window.matchMedia( '(min-width: 600px)' ).matches ) {
						$( '#wpacc-menu ul' ).show();
					}
				}
			);

			/**
			 * Events for the content section.
			 */
			$( '#wpacc-main' )
			/**
			 * A button is clicked
			 */
			.on(
				'click',
				'button[name=wpacc_action]',
				function( e ) {
					doAjaxForm( [ { wpacc_action: $( this ).val() } ] );
					e.preventDefault();
				}
			)
			/**
			 * An anchor is clicked
			 */
			.on(
				'click',
				'a.wpacc-zoom',
				function( e ) {
					let id = $( this ). closest( 'tr' ). children( 'td:first' ).text();
					doAjaxForm( [ { wpacc_action: 'read' }, { id: id } ] );
					e.preventDefault();
				}
			)
			.on(
				'click',
				'button.wpacc-add-row',
				function( e ) {
					const $new_row = $( 'table.wpacc tbody tr:last' ).clone();
					$new_row.find( 'td *' ).each(
						function () {
							$( this ).val( '' );
						}
					)
					$( 'table.wpacc tbody' ).append( $new_row );
					e.preventDefault();
				}
			)
			.on(
				'change',
				'input.wpacc-image',
				function() {
					let file   = $( this ).get( 0 ).files[0],
						reader = new FileReader(),
						ident  = this.id + 'img';
					if ( file ) {
						reader.onload = function() {
							$( ident ).attr( 'src', reader.result );
						}
						reader.readAsDataURL( file );
					}
				}
			);
		}
	)
}
)( jQuery );
