<?php
/**
 * The forms handler.
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountancy/Public
 */

namespace WP_Accountancy\Public;

/**
 * The Forms class.
 */
class Forms {

	/**
	 * Build a form submit button
	 *
	 * @param string $action The button action.
	 * @param string $text   The button label.
	 * @param bool   $left   Horizontal position in form.
	 *
	 * @return string
	 */
	public function action_button( string $action, string $text, bool $left = true ) : string {
		$position = $left ? 'left' : 'right';
		return <<<EOT
		<button name="wpacc_action" type="button" style="float: $position;" value="$action" >$text</button>
		EOT;
	}

	/**
	 * A form input field
	 *
	 * @param array $args Arguments.
	 *
	 * @return string
	 */
	public function form_field( array $args ) : string {
		$default = [
			'name'     => '',
			'required' => false,
			'type'     => 'text',
			'label'    => '',
			'value'    => null,
			'list'     => [],
		];
		return $this->render_fields( $args, $default,true );
	}

	/**
	 * Display the table
	 *
	 * @param array $fields The table field names.
	 * @param array $items  The items to be displayed.
	 *
	 * @return string
	 */
	public function table( array $fields, array $items ) : string {
		return $this->render_table(
			$fields,
			function() use ( $fields, $items ) : string {
				$html = '';
				foreach ( $items as $item ) {
					$html .= "<tr><td>$item->id</td>";
					foreach ( array_keys( $fields ) as $field ) {
						$html .= match ( $field ) {
							'id'   => '',
							'name' => "<td><a data-id='$item->id'>$item->name</a></td>",
							default => "<td>$item->$field</td>",
						};
					}
					$html .= '</tr>';
				}
				return $html;
			},
			[ 'create' ]
		);
	}

	/**
	 * Display the forms table
	 *
	 * @param array $fields      The table field names.
	 * @param array $form_fields The form field definitions.
	 * @param array $items       The items to be displayed.
	 *
	 * @return string
	 */
	public function forms_table( array $fields, array $form_fields, array $items ) : string {
		return $this->render_table(
			$fields,
			function() use ( $form_fields, $items ) : string {
				$default = [
					'name'     => '',
					'required' => false,
					'type'     => 'text',
					'list'     => [],
					'value'    => '',
				];
				$html    = '';
				$index   = 0;
				while ( $index < count( $items ) + 1 ) {
					$value = isset( $items[ $index ] ) ? $items[ $index ]->field : '';
					foreach( $form_fields as $key => $form_field ) {
						$html .= '<td>' . $this->render_fields(
								[
									'name'  => "{$key}[]",
									'type'  => $form_field['type'] ?? 'text',
									'value' => $value,
									'list'  => $form_field['list'] ?? [],
								],
								$default,
								false
							) . '</td>';
					}
					$html .= '</tr>';
					$index++;
				}
				return $html;
			},
			[ 'static', 'addrow' ]
		);
	}

	/**
	 * Render the form input field
	 *
	 * @param array $args    Arguments.
	 * @param array $default Defaults.
	 * @param bool $labels   If labels should be applied.
	 *
	 * @return string
	 */
	private function render_fields( array $args, array $default, bool $labels = true ) : string {
		$args = (object) wp_parse_args( $args, $default );
		$required = $args->required ? 'required' : '';
		$label_start = $labels ? "<label for='wpacc_$args->name' >$args->label" : '';
		$label_end   = $labels ? '</label>' : '';
		if ( in_array( $args->type , [ 'text', 'email', 'number', 'date' ] ) ) {
			$html = <<<EOT
			$label_start
			<input name="$args->name" type="$args->type" id="wpacc_$args->name" value="$args->value" $required >
			$label_end
		EOT;
		} elseif ( 'textarea' === $args->type ) {
			$html = <<<EOT
			$label_start
			<textarea name="$args->name" id="wpacc_$args->name" $required >$args->value</textarea>
			$label_end
		EOT;
		} elseif ( 'currency' === $args->type ) {
			$html = <<<EOT
			$label_start
			<input name="$args->name" id="wpacc_$args->name" value="$args->value" type="number" step="0.01" $required >
			$label_end
		EOT;
		} elseif ( 'readonly' === $args->type ) {
			$html = <<<EOT
			$label_start
			<input name="$args->name" value="$args->value" readonly >
			$label_end
		EOT;
		} elseif ( in_array( $args->type, [ 'radio', 'checkbox' ] ) ) {
			$checked = checked( $args->value, true, false );
			$html    = <<<EOT
			$label_start
			<input name="$args->name" id="wpacc_$args->name" type="$args->type" value="$args->value" $checked $required >
			$label_end
		EOT;
		} elseif ( 'select' === $args->type ) {
			$html = <<<EOT
			$label_start
			<select name="$args->name" id="wpacc_$args->name">
		EOT;
			foreach( $args->list as $item ) {
				$selected = selected( $args->value, $item['id'], false );
				$html .= <<<EOT
				<option value="{$item['id']}" $selected >{$item['name']}</option>
			EOT;
			}
			$html .= <<<EOT
			</select>
		EOT;
		} elseif ( 'hidden' === $args->type ) {
			$html = <<< EOT
			<input name="$args->name" id="wpacc_$args->name" type="$args->type" value="$args->value" >
		EOT;
		} else {
			$html = '';
		}
		return $html;
	}

	/**
	 * Render the data rows of the table.
	 *
	 * @param array    $fields  The table field names.
	 * @param callable $rows    The function to render the row.
	 * @param array    $options Table options.
	 *
	 * @return string
	 */
	private function render_table( array $fields, callable $rows, array $options = [] ) {
		error_log( var_export( $options, true ));
		$create = in_array( 'create', $options, true ) ? 'data-create="true"' : '';
		$static = in_array( 'static', $options, true ) ? 'data-paging="false" data-info="false"' : '';
		$search = in_array( 'search', $options, true ) ? 'data-searching="true"' : 'data-searching="false"';
		$addrow = in_array( 'addrow', $options, true ) ? 'data-addrow="true"' : '';
		$html = <<<EOT
			<table class="wpacc display" $create $static $search $addrow >
			<thead>
			<tr>
		EOT;
		error_log( $html);
		foreach( $fields as $fieldname ) {
			$html .= <<<EOT
				<th>$fieldname</th>
			EOT;
		}
		$html .= <<<EOT
			</tr>
			</thead>
			<tbody>
		EOT;
		$html .= $rows();
		$html .= <<<EOT
			</tbody>
			</table>
		EOT;
		return $html;
	}



}
