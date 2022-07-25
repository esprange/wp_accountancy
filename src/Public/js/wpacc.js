/**
 * WPACC javascript functions.
 *
 * @author Eric Sprangers.
 * @since  1.0.0
 * @package WP_Accounting
 */

/* global wpaccData, Cleave */

/**
 * Jquery part
 */
( function( $ ) {
	'use strict';

	/**
	 * Business object.
	 *
	 * @property {object} business
	 * @property {string} business.decimalsep
	 * @property {string} business.thousandsep
	 * @property {number} business.decimals
	 * @property {string} business.dateformat
	 * @property {string} business.timeformat
	 * @property {string} business.name
	 */
	let business;

	let currencies;

	/**
	 * Actions after document ready or ajax ready
	 */
	function onload() {
		currencies = [];
		$( '.wpacc-type-currency' ).each(
			function( index, element ) {
				currencies[ element.id ] = new Cleave(
					element,
					{
						numeral: true,
						delimiter: business.thousandsep,
						numeralDecimalMark: business.decimalsep,
						numeralDecimalScale: business.decimals,
						completeDecimalsOnBlur: true
					}
				);
			}
		);
		$( 'input[class^=wpacc-total-]' ).trigger( 'change' );
		$( '.wpacc-type-date' ).each(
			function() {
				// Dateformat is the PHP dateformat, requires to be split for Cleave.
				let pattern = Array.from( business.dateformat );
				new Cleave(
					this,
					{
						date: true,
						delimiter: pattern[1],
						datePattern: [ pattern[0], pattern[2], pattern[4] ]
					}
				);
			}
		);
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
							business = response.data.business;
							$( '#wpacc-main' ).html( response.data.main );
							$( '#wpacc-business' ).html( business.name );
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
			$( '#wpacc-container' )
			/**
			 * Main menu event.
			 */
			.on(
				'click',
				'a[data-menu]',
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
							business = response.data.business;
							$( '#wpacc-main' ).html( response.data.main );
							$( '#wpacc-business' ).html( business.name );
						},
					);
				}
			)
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
			.on(
				'change ready',
				'.wpacc-type-currency',
				function( e ) {
					let number;
					if ( 'INPUT' === e.nodeName ) {
						number  = parseFloat( e.value );
						e.value = new Intl.NumberFormat( 'en-US', { style: 'currency', currency: 'USD' } ).format( number );
						return;
					}
					number        = parseFloat( e.textContent );
					e.textContent = new Intl.NumberFormat( 'en-US', { style: 'currency', currency: 'USD' } ).format( number );
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
				'input.wpacc-type-image',
				function() {
					let file   = $( this ).get( 0 ).files[0],
						reader = new FileReader(),
						ident  = this.id + 'img';
					if ( file ) {
						reader.onload = function() {
							$( ident ).attr( 'src', String( reader.result ) );
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
							sum += parseFloat( currencies[ this.id ].getRawValue() );
						}
					);
					currencies[ $( '.wpacc-sum-' + name.replace( '[]', '' ) ).attr( 'id' ) ].setRawValue( sum );
				}
			);
		}
	)
}
)( jQuery );
