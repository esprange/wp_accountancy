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

use WP_Accountancy\Includes\Business;
/**
 * The Forms class.
 */
class Field {

	/**
	 * Settings for fields.
	 *
	 * @var object
	 */
	private object $business;

	/**
	 * Assign a unique field number to a field
	 *
	 * @var int $field_id Unique field number.
	 */
	private static int $field_id = 0;

	/**
	 * Contructor
	 *
	 * @param Business $business The business settings.
	 */
	public function __construct( Business $business ) {
		$this->business = $business;
	}

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
			'type'     => 'text',
			'label'    => '',
			'value'    => null,
			'list'     => [],
			'optgroup' => false,
			'lstgroup' => false,
			'static'   => false,
			'zoom'     => false,
			'total'    => false,
			'class'    => '',
			'table'    => '',
			'unit'     => '',
		];
		$args           = (object) wp_parse_args( $args, $default );
		$args->required = $args->required ? 'required' : '';
		$args->readonly = wp_readonly( $args->total, true, false );
		$args->class   .= " wpacc-type-$args->type";
		$args->unit     = $args->unit ? "<span>$args->unit</span>" : '';
		$args->static   = $args->static ?: $args->zoom;
		$args->field_id = self::$field_id;
		self::$field_id++;
		return ( $args->label ? "<label >$args->label</label>" : '' ) .
		match ( $args->type ) {
			'float'     => $this->render_float( $args ),
			'currency'  => $this->render_currency( $args ),
			'number',
			'date',
			'email',
			'hidden',
			'text'      => $this->render_input( $args ),
			'select'    => $this->render_select( $args ),
			'textarea'  => $this->render_textarea( $args ),
			'radio'     => $this->render_radio( $args ),
			'checkbox'  => $this->render_check( $args ),
			'image'     => $this->render_image( $args ),
		};
	}

	/**
	 * Render an static element
	 *
	 * @param object $args Field definition.
	 *
	 * @return string
	 */
	private function render_static( object $args ) : string {
		if ( $args->zoom ) {
			if ( $args->lstgroup ) {
				return <<<EOT
				<div class="wpacc-field">
					$args->unit
					<strong><a class="$args->class wpacc-zoom" >$args->value</a></strong>
				</div>
				EOT;
			}
			return <<<EOT
				<div class="wpacc-field">
					$args->unit
					&nbsp;&nbsp;<a class="$args->class wpacc-zoom" >$args->value</a>
				</div>
				EOT;
		}
		return <<<EOT
		<div class="wpacc-field">
			$args->unit
			$args->value
		</div>
		EOT;
	}

	/**
	 * Render an input element
	 *
	 * @param object $args Field definition.
	 *
	 * @return string
	 */
	private function render_input( object $args ) : string {
		if ( $args->static ) {
			return $this->render_static( $args );
		}
		return <<<EOT
		<div class="wpacc-field">
			$args->unit
			<input name="$args->name$args->table" id="wpacc-$args->field_id" class="$args->class" type="$args->type" value="$args->value" $args->required $args->readonly >
		</div>
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
		if ( $args->static ) {
			$args->value = number_format( (float) $args->value, $this->business->decimals, $this->business->decimalsep, $this->business->thousandsep );
			return $this->render_static( $args );
		}
		return <<<EOT
		<div class="wpacc-field">
			$args->unit
			<input name="$args->name$args->table" id="wpacc-$args->field_id" class="$args->class" type="text" value="$args->value" inputmode="numeric" $args->required $args->readonly >
		</div>
		EOT;
	}

	/**
	 * Render an input currency element
	 *
	 * @param object $args Field definition.
	 *
	 * @return string
	 */
	private function render_currency( object $args ) : string {
//		$args->value = number_format( (float) $args->value, $this->business->decimals, $this->business->decimalsep, $this->business->thousandsep );
		$args->unit  = $this->business->currency;
		return $this->render_float( $args );
	}

	/**
	 * Render a select element
	 *
	 * @param object $args Field definition.
	 *
	 * @return string
	 */
	private function render_select( object $args ) : string {
		$optgroup    = '';
		$firstoption = $args->required ? '' : '<option value="">&nbsp;</option>';
		$html        = <<<EOT
		<div class="wpacc-field">
			<select name="$args->name$args->table" id="wpacc-$args->field_id" class="$args->class" $args->required $args->readonly >
			$firstoption
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
		</div>
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
		<textarea name="$args->name$args->table" id="wpacc-$args->field_id" class="$args->class" $args->required $args->readonly >$args->value</textarea>
		EOT;
	}

	/**
	 * Render a radio element
	 *
	 * @param object $args Field definition.
	 *
	 * @return string
	 */
	private function render_radio( object $args ) : string {
		$value   = strtok( $args->value, '|' );
		$checked = checked( strtok( '|' ), true, false );
		return <<<EOT
		<input name="$args->name" id="wpacc-$args->field_id" type="$args->type" class="$args->class" value="$value" $checked $args->required $args->readonly >
		EOT;
	}

	/**
	 * Render a checkbox element
	 *
	 * @param object $args Field definition.
	 *
	 * @return string
	 */
	private function render_check( object $args ) : string {
		$checked = checked( $args->value, true, false );
		return <<<EOT
		<input name="$args->name$args->table" id="wpacc-$args->field_id" type="$args->type" class="$args->class" $checked $args->required $args->readonly >
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
		$img    = $args->value ?: plugin_dir_url( __FILE__ ) . '/../images/1x1-transparant.png';
		$img_id = 'wpacc_img_' . wp_rand();
		return <<<EOT
		<input name="$args->name$args->table" id="wpacc-$args->field_id" type="file" class="$args->class" accept="image/png, image/jpeg" $args->required $args->readonly >
		<img src="$img" id="{$img_id}img" width="100" height="100" alt=""/>
		EOT;
	}
}
