<?php
/**
 * The table handler.
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountancy/Public
 */

namespace WP_Accountancy\Public;

/**
 * The Table class.
 */
class Table {

	/**
	 * Display a table
	 *
	 * Args is an array with the following elements
	 *     fields: a set of key => array pairs where
	 *          the array contains any of the following
	 *          label:    the field label, displayed in top row
	 *          type:     static text,  a text- date- email- hidden- number- float- or currency input, a textarea, a select list or zoom
	 *          required: if field input is required
	 *          readonly: if the field is readonly
	 *          name:     the name of the form element (by default this equals the key)
	 *     items: zero or more objects. the key of the fields mentioned are the properties.
	 *     options: an array with options to be added to the table
	 *          paging:   add paging to the table
	 *          addrow:   add am add row button below the table, the table starts with an empty row
	 *          create*:  add a button on top to create a new item in the list
	 *          select:   first field is a checkbox that can be used to select a row, if not set, first field is hidden
	 *
	 * @param array $args The table data.
	 * @return string The html text to render the table.
	 */
	public function render( array $args ) : string {
		$args = (object) wp_parse_args(
			$args,
			[
				'fields'  => [],
				'items'   => [],
				'options' => [],
			]
		);
		return $this->render_table_buttons( $args ) .
			$this->render_table_head( $args ) .
			$this->render_table_body( $args ) .
			$this->render_table_foot( $args );
	}

	/**
	 * Render buttons, if exist.
	 *
	 * @param object $args The table data.
	 * @return string
	 */
	private function render_table_buttons( object $args ) : string {
		$html    = '';
		$buttons = array_filter(
			$args->options,
			function( $key ) {
				return str_starts_with( $key, 'button' );
			},
			ARRAY_FILTER_USE_KEY
		);
		foreach ( $buttons as $key => $button ) {
			$action = str_replace( 'button_', '', $key );
			$html  .= <<<EOT
			<button type="button" class="wpacc-btn" name="wpacc_action" value="$action" >$button</button>
			EOT;
		}
		return $html;
	}

	/**
	 * Render the header of the table.
	 *
	 * @param object $args The table data.
	 *
	 * @return string
	 */
	private function render_table_head( object $args ) : string {
		$hide = isset( $args->options['select'] ) ? '' : 'style="visibility:collapse"';
		$cols = count( $args->fields ) - 1;
		$html = <<<EOT
			<table class="wpacc" >
			<colgroup>
				<col $hide>
				<col span="$cols">
			</colgroup>
			<thead>
			<tr>

		EOT;
		foreach ( $args->fields as $field ) {
			$label = $field['label'] ?? $field['name'];
			$html .= <<<EOT
				<th>$label</th>

			EOT;
		}
		$html .= <<<EOT
			</tr>
			</thead>

		EOT;
		return $html;
	}

	/**
	 * Render the data rows of the table.
	 *
	 * @param object $args The table data.
	 *
	 * @return string
	 */
	private function render_table_body( object $args ) : string {
		$html = <<<EOT
			<tbody>
		EOT;
		foreach ( $args->items as $key => $item ) {
			if ( ! $key ) {
				continue;
			}
			$html .= <<<EOT
				<tr>

			EOT;
			foreach ( $args->fields as $field ) {
				$property          = substr( $field['name'], ( strrpos( $field['name'], '-' ) ?: - 1 ) + 1 );
				$field['value']    = $item->$property;
				$field['lstgroup'] = $item->group ?? false;
				$field['label']    = '';
				$field['name']    .= in_array( $field['type'], [ 'radio', 'checkbox' ], true ) ? '' : '[]';
				$html             .= '<td>' . ( new Field() )->render( $field ) . "</td>\n";
			}
			$html .= <<<EOT
				</tr>

			EOT;
		}
		if ( 0 === count( $args->items ) ) {
			$html .= <<<EOT
			<tr>

			EOT;
			foreach ( $args->fields as $field ) {
				$field['value'] = '';
				$field['label'] = '';
				$field['name'] .= in_array( $field['type'], [ 'radio', 'checkbox' ], true ) ? '' : '[]';
				$html          .= '<td>' . ( new Field() )->render( $field ) . "</td>\n";
			}
			$html .= <<<EOT
			</tr>

			EOT;
		}
		$html .= <<<EOT
			</tbody>

		EOT;
		return $html;
	}

	/**
	 * Render the footer of the table.
	 *
	 * @param object $args The table data.
	 *
	 * @return string
	 */
	private function render_table_foot( object $args ) : string {
		$html = '</table>';
		if ( in_array( 'addrow', $args->options, true ) ) {
			$html .= '<button type="button" class="wpacc-btn wpacc-add-row" value="addrow" >+</button><br/>';
		}
		return $html;
	}

}
