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
		const $table      = $( 'table.wpacc' ),
			$table_select = $( 'table.wpacc-select' ),
			$datepicker   = $( 'input.wpacc-date' );
		if ( ! $.fn.DataTable.isDataTable( 'table.wpacc' ) ) {
			$table.DataTable();
		}
		if ( ! $.fn.DataTable.isDataTable( 'table.wpacc-select' ) ) {
			$table_select.DataTable(
				{
					columnDefs: [{
						orderable: false,
						className: 'select-checkbox',
						targets: 0
					}, {
						visible: false,
						searchable: false,
						targets: 1
					}],
					deferRender: true,
					dom: 'Bfrtip',
					select: {
						style: 'single',
						selector: 'td:first-child'
					}
				}
			);
		}
		if ( $datepicker[0] && ! $datepicker.hasClass( 'hasDatepicker' ) ) {
			$datepicker.datepicker();
		}
	}

	/**
	 * Perform Ajax form call.
	 *
	 * @param {array} params
	 */
	function doAjaxForm( params = [] ) {
		const formData = new FormData( document.getElementById( 'wpacc-form' ) );
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
					$( '#wpacc' ).html( response.data );
				}
			}
		).fail(
			function( jqXHR ) {
				$( '#wpacc' ).html( jqXHR.responseJSON.message );
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
				$( '#wpacc' ).html( response.data );
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
			$( 'nav.wpacc-menu a' ).on(
				'click',
				function() {
					doAjaxMenu( $( this ).data( 'menu' ) );
				}
			)

			$( '#wpacc' ).on(
				'click',
				'button[name=wpacc_action]',
				function() {
					doAjaxForm( [ { wpacc_action: $( this ).val() } ] );
				}
			).on(
				'select.dt',
				'table.wpacc-select',
				function( e, dt, type, index ) {
					if ( 'row' === type ) {
						const table = $( this ).DataTable();
						let id      = table.rows( index ).data()[0][1];
						doAjaxForm( [ { wpacc_action:'select' }, { id: id } ] );
					}
				}
			).on(
				'draw.dt',
				'table.wpacc-select',
				function( e, dt, type, index ) {
					const table = $( this ).DataTable();
					let id      = $( this ).data( 'selected' );
					if ( 'undefined' !== id ) {
						table.rows().every(
							function ( rowIdx, tableLoop, rowLoop) {
								if (  this.data()[1] === + id ) {
									this.select();
								}
							}
						);
					}
				}
			);
		}
	)
}
)( jQuery );
