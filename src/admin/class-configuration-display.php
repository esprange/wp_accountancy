<?php
/**
 * Class for rendering the configuration functions.
 *
 * @package WP-Accountancy
 * @subpackage WP-Accountancy/admin
 */

namespace WP_Accountancy\Admin;

use WP_Accountancy\Includes;

/**
 * Admin display class
 */
class Configuration_Display {

	/**
	 * Show the options
	 *
	 * @return void
	 */
	public function options() : void {
		?>
		<!--suppress HtmlUnknownTarget -->
		<form method="POST" action="options.php" >
		<?php settings_fields( 'wpacc-options' ); ?>
		<div style="height: 80vh;overflow-y: scroll;" >
		<table class="form-table" >
		<?php
			$this->options_parameters();
		?>
		</table>
		</div>
		<?php submit_button(); ?>
		<p>&nbsp;</p>
		</form>
		<?php
	}

	/**
	 * Show the setup
	 *
	 * @return void
	 */
	public function setup() : void {
		?>
		<div style="float:left;width:50%;">
		<form method="POST" action="options.php" >
			<?php settings_fields( 'wpacc-setup' ); ?>
			<div style="height: 80vh;overflow-y: scroll;" >
			<table class="form-table">
				<?php $this->setup_switch_parameters(); ?>
				<?php $this->setup_text_parameters(); ?>
			</table>
			</div>
			<?php submit_button(); ?>
			<p>&nbsp;</p>
		</form>
		</div>
		<?php
	}

	/**
	 * Show the text parameters
	 *
	 * @return void
	 */
	private function setup_text_parameters() : void {
		$parameters = [
			'dummy' => 'Dummy test',
		];
		foreach ( $parameters as $id => $naam ) {
			?>
			<tr >
				<th scope="row"><label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $naam ); ?></label></th>
				<td>
					<input type="text" name="wpacc-setup[<?php echo esc_attr( $id ); ?>]" id="<?php echo esc_attr( $id ); ?>" class="regular-text"
						value="<?php echo esc_attr( \WP_Accountancy\Includes\setup()[ $id ] ); ?>" />
				</td>
			</tr>
			<?php
		}
	}

	/**
	 * Show the list parameters
	 *
	 * @return void
	 */
	private function setup_list_parameters() : void {
		$parameters = [
			'dummy_list' => [
				'title'  => 'Dummy list',
				'fields' => [
					[
						'name'  => 'example',
						'title' => 'Example',
						'field' => 'type="number" step="0.01" min="0"',
						'class' => 'wpacc-default',
					],
				],
			],
		];
		foreach ( $parameters as $key => $parameter ) {
			$json_fields = wp_json_encode( $parameter['fields'] );
			if ( is_string( $json_fields ) ) {
				?>
	<tr><th scope="row"><?php echo esc_html( $parameter['title'] ); ?></th><td>
		<table class="form-table" id="<?php echo esc_attr( "wpacc_list_$key" ); ?>">
			<thead>
				<tr>
					<th scope="row"><?php esc_html_e( 'Naam', 'wpacc' ); ?></th>
					<?php foreach ( $parameter['fields'] as $field ) : ?>
						<th scope="row"><?php echo esc_html( $field['title'] ); ?></th>
					<?php endforeach; ?>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ( options()[ $key ] ?? [] as $index => $option ) :
					?>
				<tr>
					<td><!--suppress HtmlFormInputWithoutLabel -->
						<input type="text" class="regular-text" name="<?php echo esc_attr( "wpacc-options[$key][$index][name]" ); ?>" value="<?php echo esc_attr( $option['name'] ); ?>" /></td>
					<?php foreach ( $parameter['fields'] as $field ) : ?>
					<td><!--suppress HtmlFormInputWithoutLabel -->
						<input <?php echo $field['field']; // phpcs:ignore ?>  class="small-text <?php echo esc_attr( $field['class'] ?? '' ); ?>" name="<?php echo esc_attr( "wpacc-opties[$key][$index][{$field['name']}]" ); ?>" value="<?php echo esc_attr( $option[ $field['name'] ] ); ?>" /></td>
					<?php endforeach; ?>
					<td><span id="wpacc_remove_<?php echo esc_attr( $key . '_' . $index ); ?>" class="dashicons dashicons-trash" style="cursor: pointer;"></span></td>
				</tr>
					<?php
			endforeach;
				?>
			</tbody>
			<tfoot>
				<tr>
					<th scope="row"><?php echo esc_html( $parameter['title'] ); ?><?php esc_html_e( 'add', 'wpacc' ); ?></th>
					<td>
						<button id="wpacc_add_<?php echo esc_attr( $key ); ?>" type="button" class="list_add" data-key="<?php echo esc_attr( $key ); ?>" data-parameters='<?php echo $json_fields; // phpcs:ignore ?>'>
							<span class="dashicons dashicons-plus"></span>
						</button>
					</td>
				</tr>
			</tfoot>
		</table>
		</td></tr>
				<?php
			}
		}
	}

	/**
	 * Show switch parameters
	 *
	 * @return void
	 */
	private function setup_switch_parameters() : void {
		$parameters = [
			'dummy_switch' => 'Dummy switch',
		];
		foreach ( $parameters as $id => $name ) {
			?>
			<tr>
				<th scope="row"><?php echo esc_html( $name ); ?></th>
				<td>
					<label>
						<input type="radio" name="wpacc-setup[<?php echo esc_attr( $id ); ?>]"
							value="0" <?php checked( 0, setup()[ $id ] ); ?>/><?php esc_html_e( 'Off', 'wpacc' ); ?>
					</label>
					<label style="margin-left: 50px">
						<input type="radio" name="wpacc-setup[<?php echo esc_attr( $id ); ?>]"
							value="1" <?php checked( 1, setup()[ $id ] ); ?>/><?php esc_html_e( 'On', 'wpacc' ); ?>
					</label>
				</td>
			</tr>
			<?php
		}
	}

	/**
	 * Toon de parameters
	 *
	 * @return void
	 */
	private function options_parameters() : void {
		$parameters = [
			'dummy option' => [
				'min'   => 1,
				'max'   => 99,
				'label' => 'Dummy option',
			],
		];
		foreach ( $parameters as $id => $parameter ) :
			?>
			<tr >
				<th scope="row">
					<label for="<?php echo esc_attr( $id ); ?>">
						<?php echo esc_html( $parameter['label'] ); ?>
					</label>
				</th>
				<td>
					<input type="number" min="<?php echo esc_attr( $parameter['min'] ); ?>"
						max="<?php echo esc_attr( $parameter['max'] ); ?>" name="wpacc-options[<?php echo esc_attr( $id ); ?>]" id="<?php echo esc_attr( $id ); ?>" class="small-text"
						value="<?php echo esc_attr( options()[ $id ] ); ?>" />
				</td>
			</tr>
			<?php
		endforeach;
	}
}
