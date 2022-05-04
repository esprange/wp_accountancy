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
	 * Document ready
	 */
	$(
		function() {

			$( '#wpacc' ).on(
				'click',
				'button[name=wpacc_action]',
				function() {
					const formData = new FormData( document.getElementById( 'wpacc-form' ) );
					formData.append( 'action', 'wpacc_formhandler' );
					formData.append( 'wpacc_action', $( this ).val() );
					formData.append( '_ajax_nonce', $( this ).data( 'nonce' ) );
					formData.append( 'display', $( this ).data( 'display' ) );
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
							$( '#wpacc' ).html( response.data );
						}
					).fail(
						function( jqXHR ) {
							$( '#wpacc' ).html( jqXHR.responseJSON.message );
						}
					);
				}
			);
		}
	)
}
)( jQuery );
