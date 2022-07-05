/**
 * WPACC javascript functions.
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
		$( 'input[class^=wpacc-total-]' ).trigger( 'change' );
	}

	/**
	 * Perform Ajax form call.
	 *
	 * @param {array} params
	 */
	function doForm( params = [] ) {
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
				dataType:    'json',
				beforeSend:
					function() {
						$( document ).css( 'cursor', 'wait' );
					},
				success:
					function ( response ) {
						if ('' !== response.data) {
							$( '#wpacc-main' ).html( response.data.main );
							$( '#wpacc-business' ).html( response.data.business );
						}
					},
				error:
					function( jqXHR ) {
						$( '#wpacc-main' ).html( '<span class="wpacc-error">' + jqXHR.statusText + '</span>' );
					},
				complete:
					function() {
						$( document ).css( 'cursor', 'default' );
					}
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
			onload();

			/**
			 * Main menu event.
			 */
			$( 'a[data-menu]' ).on(
				'click',
				function() {
					$( '.wpacc-menu a' ).removeClass( 'wpacc-menu-selected' );
					$( this ).addClass( 'wpacc-menu-selected' );
					$.get(
						wpaccData.ajaxurl,
						{
							'action': 'wpacc_menuhandler',
							'menu': $( this ).data( 'menu' ),
						},
						function (response) {
							$( '#wpacc-main' ).html( response.data.main );
							$( '#wpacc-business' ).html( response.data.business );
						},
					);
				}
			);

			/**
			 * Business select menu
			 */
			$( '#wpacc-menu-dropdown' ).on(
				'click',
				function( e ) {
					$( '#wpacc-menu ul' ).slideToggle( 500 );
					e.preventDefault();
				}
			);

			/**
			 * Make the main menu responsive
			 */
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
					doForm( [ { wpacc_action: $( this ).val() } ] );
					e.preventDefault();
				}
			)
			/**
			 * A zoom anchor is clicked
			 */
			.on(
				'click',
				'a.wpacc-zoom',
				function( e ) {
					let id = $( this ). closest( 'tr' ). children( 'td:first' ).text();
					doForm( [ { wpacc_action: 'read' }, { id: id } ] );
					e.preventDefault();
				}
			)
			/**
			 * An add row button is clicked
			 */
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
			/**
			 * An image is selected, show the preview
			 */
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
			)
			.on(
				'change',
				'input[class^=wpacc-total-]',
				function() {
					let sum  = 0.0,
						name = this.name;
					$( "[name='" + name + "']" ).each(
						function() {
							sum += parseFloat( $( this ).val() );
						}
					);
					$( '.wpacc-sum-' + name.replace( '[]', '' ) ).html( sum );
				}
			);
		}
	)
}
)( jQuery );
