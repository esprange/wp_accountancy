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
use WP_Accountancy\Includes\ChartOfAccounts;
use function WP_Accountancy\Includes\notify;

/**
 * The Public filters.
 */
class BusinessDisplay extends Display {

	const COUNTRIES = [
		'United Kingdom' => [
			'language' => 'English',
			'template' => 'english.json',
		],
		'United States'  => [
			'language' => 'English',
			'template' => 'english.json',
		],
		'Nederland'      => [
			'language' => 'Nederlands',
			'template' => 'dutch.json',
		],
		'Belgium'        => [
			'language' => 'Nederlands',
			'template' => 'dutch.json',
		],
	];

	/**
	 * Provide the top title
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Businesses', 'wpacc' );
	}

	/**
	 * Create the business.
	 *
	 * @return string
	 */
	public function create() : string {
		global $wpacc_business;
		$result = $this->update();
		$coa    = new ChartOfAccounts();
		$coa->import( WPACC_PLUGIN_PATH . 'templates\\' . self::COUNTRIES[ $wpacc_business->country ]['template'] );
		return $result;
	}

	/**
	 * Display the form
	 *
	 * @return string
	 */
	public function read() : string {
		$business_id   = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT );
		$business      = new Business( $business_id ?? 0 );
		$country_names = array_keys( self::COUNTRIES );
		sort( $country_names );
		ob_start();
		?>
			<label for="wpacc_name"><?php esc_html_e( 'Name', 'wpacc' ); ?>
				<input name="name" id="wpacc_name" value="<?php echo esc_attr( $business->name ); ?>" >
			</label>
			<label for="wpacc_country"><?php esc_html_e( 'Country', 'wpacc' ); ?>
				<select name="country" id="wpacc_country">
					<?php foreach ( $country_names as $country_name ) : ?>
					<option value="<?php echo esc_attr( $country_name ); ?>" ><?php echo esc_html( $country_name ); // phpcs:ignore ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<label for="wpacc_address"><?php esc_html_e( 'Address', 'wpacc' ); ?>
				<textarea name="address" id="wpacc_address" ><?php echo esc_attr( $business->address ); ?></textarea>
			</label>
			<label for="wpacc_logo"><?php esc_html_e( 'Logo', 'wpacc' ); ?>
				<input name="logo" id="wpacc_logo" value="<?php echo esc_attr( $business->logo ); ?>" >
			</label>
			<input type="hidden" name="id" value="<?php echo esc_attr( $business->id ); ?>" />
		<?php
		return $this->form( ob_get_clean() . $this->action_button( $business->id ? 'update' : 'create', __( 'Save', 'wpacc' ) ) );
	}

	/**
	 * Render the existing business
	 *
	 * @return string
	 */
	public function overview() : string {
		global $wpacc_business;
		$businesses = new BusinessQuery();
		ob_start();
		?>
		<table class="wpacc-select display" data-selected="<?php echo esc_attr( $wpacc_business->id ); ?>">
			<thead>
			<tr>
				<th></th>
				<th></th>
				<th><?php esc_html_e( 'Name', 'wpacc' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ( $businesses->get_results() as $business ) : ?>
				<tr>
					<td></td>
					<td><?php echo esc_html( $business->id ); ?></td>
					<td><a href="<?php echo esc_url( sprintf( '?wpacc_action=read&id=%d', $business->id ) ); ?>"><?php echo esc_html( $business->name ); ?></a></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php
		return $this->form( ob_get_clean() . $this->action_button( 'read', __( 'Create', 'wpacc' ) ) );
	}

	/**
	 * Update the business.
	 *
	 * @return string
	 */
	public function update() : string {
		$input             = filter_input_array(
			INPUT_POST,
			[
				'id'      => FILTER_SANITIZE_NUMBER_INT,
				'name'    => FILTER_UNSAFE_RAW,
				'address' => FILTER_UNSAFE_RAW,
				'country' => FILTER_UNSAFE_RAW,
				'logo'    => FILTER_UNSAFE_RAW,
			]
		);
		$business          = new Business( $input['id'] ?? 0 );
		$business->name    = $input['name'];
		$business->address = $input['address'];
		$business->country = $input['country'];
		$business->logo    = $input['logo'];
		$business->update();
		do_action( 'wpacc_business_select', $business->id );
		return notify( -1, __( 'Business saved', 'wpacc' ) );
	}

	/**
	 * Delete the business
	 *
	 * @return string
	 */
	public function delete() : string {
		global $wpacc_business;
		$business_id = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT );
		if ( $business_id ) {
			$business = new Business( $business_id );
			if ( $business->delete() ) {
				if ( $wpacc_business->id === $business_id ) {
					do_action( 'wpacc_business_select', 0 );
				}
				return notify( - 1, __( 'Business removed', 'wpacc' ) );
			}
			return notify( 1, __( 'Remove not allowed', 'wpacc' ) );
		}
		return notify( 1, __( 'Internal error' ) );
	}

	/**
	 * Select the business
	 *
	 * @return string
	 */
	public function select() : string {
		$business_id = filter_input( INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT );
		do_action( 'wpacc_business_select', intval( $business_id ) );
		return '';
	}

}
