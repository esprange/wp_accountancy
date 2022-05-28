/**
 * WPACC javascript functions for forms.
 *
 * @author Eric Sprangers.
 * @since  1.0.0
 * @package WP_Accounting
 */

/* global wp, wpaccData, wpacc_i18n */

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
			let table = $table.DataTable(
				{
					columnDefs: [ {
						visible: false,
						searchable: false,
						targets: 0
					} ],
					dom: 'Bfrtip'
				}
			);
			if ( $table.data( 'create' ) ) {
				table.button().add( 0, {
					text: wpacc_i18n.create,
					action: function () {
						doAjaxForm([{wpacc_action: 'create'}])
					}
				} );
			}
			if ( $table.data( 'addrow' ) ) {
				$table.append( '<button class="dt-button" id="wpacc_add_row">Add row</button>' );
			}
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
					$( '#wpacc-main' ).html( response.data );
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
				$( '#wpacc-main' ).html( response.data );
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
			$( '[data-menu]' ).on(
				'click',
				function() {
					doAjaxMenu( $( this ).data( 'menu' ) );
				}
			)
			$( '#wpacc-menu-dropdown' ).on(
				'click',
				function( e ) {
					$( '#wpacc-menu ul' ).slideToggle( 500 );
					e.preventDefault();
				}
			)
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
				'a',
				function( e ) {
					doAjaxForm( [ { wpacc_action: 'read' }, { id: $( this ).data( 'id' ) } ] );
					e.preventDefault();
				}
			)
			/**
			 * Selection of an item in a select table.
			 */
			.on(
				'select.dt',
				'table.wpacc-select',
				function( e, dt, type, index ) {
					if ( 'row' === type ) {
						const table = $( this ).DataTable();
						let id      = table.rows( index ).data()[0][1];
						doAjaxForm( [ { wpacc_action:'select' }, { id: id } ] );
					}
				}
			)
			/**
			 * Show the currently selected item in a select table.
			 */
			.on(
				'draw.dt',
				'table.wpacc-select',
				function() {
					const table = $( this ).DataTable();
					let id      = $( this ).data( 'selected' );
					if ( 'undefined' !== id ) {
						table.rows().every(
							function ( rowIdx, tableLoop, rowLoop) {
								if ( + this.data()[1] === id ) {
									this.select();
								}
							}
						);
					}
				}
			)
			.on(
				'click',
				'button.wpacc-add-row',
				function( e ) {
					const $last_row = $( '.wpacc-table tbody tr:last' ),
						$new_row    = $last_row.clone();
					const table     = $( '.wpacc-table' ).DataTable();
					$new_row.each(
						function () {
							$( this ).val( '' );
						}
					)
					$new_row.appendTo( $last_row );
					table.draw();
					e.preventDefault();
				}
			);
		}
	)
}
)( jQuery );
