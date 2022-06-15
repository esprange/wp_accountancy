<?php
/**
 * The field renderer.
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
class Field {

	/**
	 * Render a field
	 *
	 * @param array $args Field definition.
	 *
	 * @return string
	 */
	public function render( array $args ) : string {
		$default        = [
			'required' => false,
			'readonly' => false,
			'type'     => 'text',
			'label'    => '',
			'value'    => null,
			'list'     => [],
		];
		$args           = (object) wp_parse_args( $args, $default );
		$args->required = $args->required ? 'required' : '';
		$args->readonly = $args->readonly ? 'readonly' : '';
		$html           = '';
		if ( $args->label ) {
			$html = "<label >$args->label";
		}
		$html .= match ( $args->type ) {
			'static'    => $this->render_static( $args ),
			'float',
			'currency'  => $this->render_float( $args ),
			'number',
			'date',
			'email',
			'hidden',
			'text'      => $this->render_input( $args ),
			'select',   => $this->render_select( $args ),
			'textarea', => $this->render_textarea( $args ),
			'radio',
			'checkbox', => $this->render_check( $args ),
			'zoom',     => $this->render_zoom( $args ),
		};
		$html .= $args->label ? '</label>' : '';
		return $html;
	}

	/**
	 * Render an static element
	 *
	 * @param object $args Field definition.
	 *
	 * @return string
	 */
	private function render_static( object $args ) : string {
		return $args->value;
	}

	/**
	 * Render an input element
	 *
	 * @param object $args Field definition.
	 *
	 * @return string
	 */
	private function render_input( object $args ) : string {
		return <<<EOT
		<input name="$args->name" type="$args->type" value="$args->value" $args->required $args->readonly >
		EOT;
	}

	/**
	 * Render an input element
	 *
	 * @param object $args Field definition.
	 *
	 * @return string
	 */
	private function render_float( object $args ) : string {
		$step = in_array( $args->type, [ 'float', 'currency' ], true ) ? 'step="0.01"' : '';
		return <<<EOT
		<input name="$args->name" type="number" value="$args->value" $step $args->required $args->readonly >
		EOT;
	}

	/**
	 * Render a select element
	 *
	 * @param object $args Field definition.
	 *
	 * @return string
	 */
	private function render_select( object $args ) : string {
		$html = <<<EOT
		<select name="$args->name" $args->required $args->readonly >
		EOT;
		foreach ( $args->list as $option_id => $option ) {
			$selected = selected( $args->value, $option_id, false );
			$html    .= <<<EOT
			<option value="$option_id" $selected >$option->name</option>
		EOT;
		}
		$html .= <<<EOT
		</select>
		EOT;
		return $html;
	}

	/**
	 * Render a textarea element
	 *
	 * @param object $args Field definition.
	 *
	 * @return string
	 */
	private function render_textarea( object $args ) : string {
		return <<<EOT
		<textarea name="$args->name" $args->required $args->readonly >$args->value</textarea>
		EOT;
	}

	/**
	 * Render a radio or checkbox element
	 *
	 * @param object $args Field definition.
	 *
	 * @return string
	 */
	private function render_check( object $args ) : string {
		$checked = checked( $args->value, true, false );
		return <<<EOT
		<input name="$args->name" type="$args->type" value="$args->value" $checked $args->required $args->readonly >
		EOT;
	}

	/**
	 * Render an anchor element
	 *
	 * @param object $args Field definition.
	 *
	 * @return string
	 */
	private function render_zoom( object $args ) : string {
		return <<<EOT
		<a class="wpacc-zoom" >$args->value</a>
		EOT;
	}

}
