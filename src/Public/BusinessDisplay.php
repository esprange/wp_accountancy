<?php
/**
 * The business display handler.
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountancy/Public
 */

namespace WP_Accountancy\Public;

use WP_Accountancy\Includes\Business;
use WP_Accountancy\Includes\BusinessQuery;
use function WP_Accountancy\Includes\notify;

/**
 * The Public filters.
 */
class BusinessDisplay extends Display {

	/**
	 * The business shown in the form.
	 *
	 * @var Business The business.
	 */
	private Business $business;

	/**
	 * The constructor
	 */
	public function __construct() {
		$this->business = new Business();
	}

	/**
	 * Create the business.
	 *
	 * @return string
	 */
	public function create() : string {
		return $this->update();
	}

	/**
	 * Display the form
	 *
	 * @return string
	 */
	public function read() : string {
		$business_id = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT );
		if ( $business_id ) {
			$this->business = new Business( $business_id );
		}
		ob_start();
		?>
			<label for="wpacc_name"><?php esc_html_e( 'Name', 'wpacc' ); ?>
				<input name="name" id="wpacc_name" value="<?php echo esc_attr( $this->business->name ); ?>" >
			</label>
			<label for="wpacc_country"><?php esc_html_e( 'Country', 'wpacc' ); ?>
				<input name="country" id="wpacc_country" value="<?php echo esc_attr( $this->business->country ); ?>" >
			</label>
			<label for="wpacc_address"><?php esc_html_e( 'Address', 'wpacc' ); ?>
				<textarea name="address" id="wpacc_address" ><?php echo esc_attr( $this->business->address ); ?></textarea>
			</label>
			<label for="wpacc_logo"><?php esc_html_e( 'Logo', 'wpacc' ); ?>
				<input name="logo" id="wpacc_logo" value="<?php echo esc_attr( $this->business->logo ); ?>" >
			</label>
			<input type="hidden" name="id" value="<?php echo esc_attr( $this->business->id ); ?>" />
		<?php
		return $this->container( $this->form( ob_get_clean() . $this->action_button( $this->business->id ? 'update' : 'create', __( 'Save', 'wpacc' ) ) ) );
	}

	/**
	 * Render the existing business
	 *
	 * @return string
	 */
	public function overview() : string {
		$businesses = new BusinessQuery();
		ob_start();
		?>
		<table>
			<thead>
			<tr>
				<th><?php esc_html_e( 'Name', 'wpacc' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ( $businesses->get_results() as $business ) : ?>
				<tr>
					<td><a href="<?php echo esc_url( sprintf( '?wpacc_action=read&id=%d', $business->id ) ); ?>"><?php echo esc_html( $business->name ); ?></a></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php
		return $this->container( ob_get_clean() . $this->form( $this->action_button( 'read', __( 'Create', 'wpacc' ) ) ) );
	}

	/**
	 * Update the business.
	 *
	 * @return string
	 */
	public function update() : string {
		$input = filter_input_array(
			INPUT_POST,
			[
				'id'      => FILTER_SANITIZE_NUMBER_INT,
				'name'    => FILTER_SANITIZE_STRING,
				'address' => FILTER_SANITIZE_STRING,
				'country' => FILTER_SANITIZE_STRING,
				'logo'    => FILTER_SANITIZE_STRING,
			]
		);
		if ( $input['id'] ) {
			$this->business = new Business( $input['id'] );
		}
		$this->business->name    = $input['name'];
		$this->business->address = $input['address'];
		$this->business->country = $input['country'];
		$this->business->logo    = $input['logo'];
		$this->business->update();
		return notify( -1, __( 'Business saved', 'wpacc' ) );
	}

	/**
	 * Delete the business
	 *
	 * @return string
	 */
	public function delete() : string {
		$business_id = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT );
		if ( $business_id ) {
			$this->business = new Business( $business_id );
			if ( $this->business->delete() ) {
				return notify( - 1, __( 'Business removed', 'wpacc' ) );
			}
			return notify( 1, __( 'Remove not allowed', 'wpacc' ) );
		}
		return notify( 1, __( 'Internal error' ) );
	}
}
