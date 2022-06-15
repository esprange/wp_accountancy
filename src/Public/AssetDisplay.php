<?php
/**
 * The asset display handler.
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountancy/Public
 */

namespace WP_Accountancy\Public;

use WP_Accountancy\Includes\Asset;
use WP_Accountancy\Includes\AssetQuery;

/**
 * The Public filters.
 */
class AssetDisplay extends Display {

	/**
	 * Provide the top title
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Assets', 'wpacc' );
	}

	/**
	 * Create the asset.
	 *
	 * @return string
	 */
	public function create() : string {
		return $this->update();
	}

	/**
	 * Update the asset.
	 *
	 * @return string
	 */
	public function update() : string {
		global $wpacc_business;
		$input              = filter_input_array(
			INPUT_POST,
			[
				'id'          => FILTER_SANITIZE_NUMBER_INT,
				'name'        => FILTER_UNSAFE_RAW,
				'description' => FILTER_UNSAFE_RAW,
				'rate'        => FILTER_SANITIZE_NUMBER_FLOAT,
				'cost'        => FILTER_SANITIZE_NUMBER_FLOAT,
				'provision'   => FILTER_SANITIZE_NUMBER_FLOAT,
				'active'      => FILTER_SANITIZE_NUMBER_INT,
			]
		);
		$asset              = new Asset( $input['id'] ?? 0 );
		$asset->name        = $input['name'];
		$asset->description = $input['description'];
		$asset->rate        = $input['rate'];
		$asset->business_id = $wpacc_business->id;
		$asset->update();
		return $this->notify( 1, __( 'Asset saved', 'wpacc' ) );
	}

	/**
	 * Delete the asset
	 *
	 * @return string
	 */
	public function delete() : string {
		$asset_id = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT );
		if ( $asset_id ) {
			$asset = new Asset( $asset_id );
			if ( $asset->delete() ) {
				return $this->$this->notify( - 1, __( 'Asset removed', 'wpacc' ) );
			}
			return $this->notify( 0, __( 'Remove not allowed', 'wpacc' ) );
		}
		return $this->notify( 0, __( 'Internal error' ) );
	}

	/**
	 * Display the form
	 *
	 * @return string
	 */
	public function read() : string {
		$asset = new Asset( intval( filter_input( INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT ) ) );
		ob_start();
		?>
		<label for="wpacc_name"><?php esc_html_e( 'Name', 'wpacc' ); ?>
			<input name="name" id="wpacc_name" value="<?php echo esc_attr( $asset->name ); ?>" >
		</label>
		<label for="wpacc_description"><?php esc_html_e( 'Description', 'wpacc' ); ?>
			<textarea name="description" id="wpacc_description" ><?php echo esc_attr( $asset->description ); ?></textarea>
		</label>
		<label for="wpacc_rate"><?php esc_html_e( 'Depreciation rate', 'wpacc' ); ?>
			<input name="rate" type="number" id="wpacc_rate" value="<?php echo esc_attr( $asset->rate ); ?>" >
		</label>
		<label for="wpacc_cost"><?php esc_html_e( 'Acquisition cost', 'wpacc' ); ?>
			<input name="cost" type="number" id="wpacc_cost" value="<?php echo esc_attr( $asset->cost ); ?>" >
		</label>
		<label for="wpacc_provision"><?php esc_html_e( 'Accumulated depreciation', 'wpacc' ); ?>
			<input name="provision" type="number" id="wpacc_provision" value="<?php echo esc_attr( $asset->provision ); ?>" >
		</label>
		<label for="wpacc_active"><?php esc_html_e( 'Active', 'wpacc' ); ?>
			<input name="active" id="wpacc_active" type="checkbox" <?php checked( $asset->active ); ?>" >
		</label>
		<input type="hidden" name="id" value="<?php echo esc_attr( $asset->id ); ?>" />
		<?php
		return $this->form( ob_get_clean() . $this->button->action( $asset->id ? 'update' : 'create', __( 'Save', 'wpacc' ) ) );
	}

	/**
	 * Render the existing assets
	 *
	 * @return string
	 */
	public function overview() : string {
		$assets = new AssetQuery();
		?>
		<table class="wpacc display" >
			<thead>
			<tr>
				<th></th>
				<th></th>
				<th><?php esc_html_e( 'Name', 'wpacc' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ( $assets->get_results() as $asset ) : ?>
				<tr>
					<td></td>
					<td><?php echo esc_html( $asset->id ); ?></td>
					<td><a href="<?php echo esc_url( sprintf( '?wpacc_action=read&id=%d', $asset->id ) ); ?>"><?php echo esc_html( $asset->name ); ?></a></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		}
		?>
		<?php
		return ob_get_clean() . $this->form( $this->button->action( 'change', __( 'Change', 'wpacc' ) ) );
	}

}
