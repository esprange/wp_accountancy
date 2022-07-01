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
			'optgroup' => false,
			'lstgroup' => false,
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
			'select'    => $this->render_select( $args ),
			'textarea'  => $this->render_textarea( $args ),
			'radio',
			'checkbox'  => $this->render_check( $args ),
			'zoom'      => $this->render_zoom( $args ),
			'image'     => $this->render_image( $args ),
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
		return $args->lstgroup ? "<strong>$args->value</strong>" : "&nbsp;&nbsp;$args->value";
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
		$optgroup = '';
		$html     = <<<EOT
		<select name="$args->name" $args->required $args->readonly >
		EOT;
		foreach ( $args->list as $option_id => $option ) {
			$selected = selected( $args->value, $option_id, false );
			$name     = $option->name;
			if ( $args->optgroup ) {
				$group = strtok( $option_id, '|' );
				$name  = strtok( '|' );
				if ( $group !== $optgroup ) {
					if ( $optgroup ) {
						$html .= <<<EOT
			</optgroup>
		EOT;
					}
					$optgroup = $group;
					$html    .= <<<EOT
			<optgroup label = "$group">
		EOT;
				}
			}
			$html .= <<<EOT
			<option value="$option_id" $selected >$name</option>
		EOT;
		}
		if ( $args->optgroup ) {
			$html .= <<<EOT
			</optgroup>
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
		$value   = strtok( $args->value, '|' );
		$checked = checked( strtok( '|' ), true, false );
		return <<<EOT
		<input name="$args->name" type="$args->type" value="$value" $checked $args->required $args->readonly >
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
		return $args->lstgroup ?
		<<<EOT
		<strong><a class="wpacc-zoom" >$args->value</a></strong>
		EOT
		:
		<<<EOT
		&nbsp;&nbsp;<a class="wpacc-zoom" >$args->value</a>
		EOT;
	}

	/**
	 * Render an image element
	 *
	 * @param object $args Field definition.
	 *
	 * @return string
	 */
	private function render_image( object $args ) : string {
		$alt    = __( 'Your image', 'wpacc' );
		$img    = $args->value ?: plugin_dir_url( __FILE__ ) . '/../images/1x1-transparant.png';
		$img_id = 'wpacc_img_' . wp_rand();
		return <<<EOT
		<input name="$args->name" type="file" class="wpacc-image" accept="image/png, image/jpeg" $args->required $args->readonly >
		<img src="$img" id="{$img_id}img" alt="$alt" width="100" height="100" />
		EOT;
	}
}
