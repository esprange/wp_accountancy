<?php

/**
 * Class for rendering the configuration functions.
 *
 * @package WP-Accountancy
 * @subpackage WP-Accountancy/Admin
 * @noinspection PhpUnnecessaryCurlyVarSyntaxInspection
 */

namespace WP_Accountancy\Admin;

use function WP_Accountancy\Includes\setup as current_setup;
use function WP_Accountancy\Includes\options as current_options;

/**
 * Admin display class
 */
class ConfigDisplay {

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
			$this->text_parameters( 'options', [] );
			$this->numeric_parameters(
				'options',
				[
					'dummy option' => [
						'min'   => 1,
						'max'   => 99,
						'label' => 'Dummy option',
					],
				]
			);
			$this->switch_parameters(
				'options',
				[
					'multibusiness' => __( 'Allow accounting for multiple bsuinesses', 'wpacc' ),
				]
			);
			$this->list_parameters(
				'options',
				[
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
				]
			);
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
			<?php
				$this->switch_parameters( 'setup', [] );
				$this->text_parameters( 'setup', [] );
				$this->list_parameters( 'setup', [] );
			?>
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
	 * @param string $destination setup or options.
	 * @param array  $parameters  the parameters.
	 * @return void
	 */
	private function text_parameters( string $destination, array $parameters ) : void {
		$current = "current_$destination";
		foreach ( $parameters as $id => $parameter ) {
			?>
			<tr >
				<th scope="row"><label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $parameter ); ?></label></th>
				<td>
					<input type="text" name="<?php echo esc_attr( "wpacc-$destination[$id]" ); ?>" id="<?php echo esc_attr( $id ); ?>" class="regular-text"
						value="<?php echo esc_attr( $this->$current[ $id ] ); ?>" />
				</td>
			</tr>
			<?php
		}
	}

	/**
	 * Show the list parameters
	 *
	 * @param string $destination setup or options.
	 * @param array  $parameters  the parameters.
	 * @return void
	 */
	private function list_parameters( string $destination, array $parameters ) : void {
		$current = "current_$destination";
		foreach ( $parameters as $id => $parameter ) {
			$json_fields = wp_json_encode( $parameter['fields'] );
			if ( is_string( $json_fields ) ) {
				?>
	<tr><th scope="row"><?php echo esc_html( $parameter['title'] ); ?></th><td>
		<table class="form-table" id="<?php echo esc_attr( "wpacc_list_$id" ); ?>">
			<thead>
				<tr>
					<th scope="row"><?php esc_html_e( 'Name', 'wpacc' ); ?></th>
					<?php foreach ( $parameter['fields'] as $field ) : ?>
						<th scope="row"><?php echo esc_html( $field['title'] ); ?></th>
					<?php endforeach; ?>
				</tr>g
			</thead>
			<tbody>
				<?php
				foreach ( $this->$current[ $id ] ?? [] as $index => $option ) :
					?>
				<tr>
					<td><!--suppress HtmlFormInputWithoutLabel -->
						<input type="text" class="regular-text" name="<?php echo esc_attr( "wpacc-$destination[$id][$index][name]" ); ?>" value="<?php echo esc_attr( $option['name'] ); ?>" /></td>
					<?php foreach ( $parameter['fields'] as $field ) : ?>
					<td><!--suppress HtmlFormInputWithoutLabel -->
						<input <?php echo $field['field']; // phpcs:ignore ?>  class="small-text <?php echo esc_attr( $field['class'] ?? '' ); ?>" name="<?php echo esc_attr( "wpacc-opties[$id][$index][{$field['name']}]" ); ?>" value="<?php echo esc_attr( $option[ $field['name'] ] ); ?>" /></td>
					<?php endforeach; ?>
					<td><span id="wpacc_remove_<?php echo esc_attr( $id . '_' . $index ); ?>" class="dashicons dashicons-trash" style="cursor: pointer;"></span></td>
				</tr>
					<?php
			endforeach;
				?>
			</tbody>
			<tfoot>
				<tr>
					<th scope="row"><?php echo esc_html( $parameter['title'] ); ?><?php esc_html_e( 'add', 'wpacc' ); ?></th>
					<td>
						<button id="wpacc_add_<?php echo esc_attr( $id ); ?>" type="button" class="list_add" data-key="<?php echo esc_attr( $id ); ?>" data-parameters='<?php echo $json_fields; // phpcs:ignore ?>'>
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
	 * @param string $destination setup or options.
	 * @param array  $parameters  the parametsrs.
	 * @return void
	 */
	private function switch_parameters( string $destination, array $parameters ) : void {
		$current = "current_$destination";
		foreach ( $parameters as $id => $parameter ) {
			?>
			<tr>
				<th scope="row"><?php echo esc_html( $parameter ); ?></th>
				<td>
					<label>
						<input type="radio" name="<?php echo esc_attr( "wpacc-{$destination}[$id]" ); ?>"
							value="0" <?php checked( 0, $this->$current[ $id ] ); ?>/><?php esc_html_e( 'No', 'wpacc' ); ?>
					</label>
					<label style="margin-left: 50px">
						<input type="radio" name="<?php echo esc_attr( "wpacc-{$destination}[$id]" ); ?>"
							value="1" <?php checked( 1, $this->$current[ $id ] ); ?>/><?php esc_html_e( 'Yes', 'wpacc' ); ?>
					</label>
				</td>
			</tr>
			<?php
		}
	}

	/**
	 * Toon de parameters
	 *
	 * @param string $destination setup or options.
	 * @param array  $parameters  the parameters.
	 * @return void
	 */
	private function numeric_parameters( string $destination, array $parameters ) : void {
		$current = "current_$destination";
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
						max="<?php echo esc_attr( $parameter['max'] ); ?>" name="<?php echo esc_attr( "wpacc-{$destination}[$id]" ); ?>" id="<?php echo esc_attr( $id ); ?>" class="small-text"
						value="<?php echo esc_attr( $this->$current[ $id ] ); ?>" />
				</td>
			</tr>
			<?php
		endforeach;
	}
}
