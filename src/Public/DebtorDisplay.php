<?php
/**
 * The debtor display handler.
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountancy/Public
 */

namespace WP_Accountancy\Public;

use WP_Accountancy\Includes\Debtor;
use WP_Accountancy\Includes\DebtorQuery;
use function WP_Accountancy\Includes\notify;

/**
 * The Debtor display class.
 */
class DebtorDisplay extends Display {

	/**
	 * Debtor
	 *
	 * @var Debtor $debtor The debtor.
	 */
	private Debtor $debtor;

	/**
	 * Create the debtor.
	 *
	 * @return string
	 */
	public function create() : string {
		return $this->update();
	}

	/**
	 * Update the debtor.
	 *
	 * @return string
	 */
	public function update() : string {
		$input = filter_input_array(
			INPUT_POST,
			[
				'id'            => FILTER_SANITIZE_NUMBER_INT,
				'name'          => FILTER_SANITIZE_STRING,
				'address'       => FILTER_SANITIZE_STRING,
				'email_address' => FILTER_SANITIZE_STRING,
				'active'        => FILTER_SANITIZE_NUMBER_INT,
			]
		);
		if ( $input['id'] ) {
			$this->debtor = new Debtor( $input['id'] );
		}
		$this->debtor->name          = $input['name'];
		$this->debtor->address       = $input['address'];
		$this->debtor->email_address = $input['email_address'];
		$this->debtor->active        = $input['active'];
		$this->debtor->business_id   = $this->business->id;
		$this->debtor->update();
		do_action( 'wpacc_debtor_select', $this->debtor->id );
		return notify( -1, __( 'Customer saved', 'wpacc' ) );
	}

	/**
	 * Delete the debtor
	 *
	 * @return string
	 */
	public function delete() : string {
		$debtor_id = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT );
		if ( $debtor_id ) {
			$this->debtor = new Debtor( $debtor_id );
			if ( $this->debtor->delete() ) {
				return notify( - 1, __( 'Customer removed', 'wpacc' ) );
			}
			return notify( 1, __( 'Remove not allowed', 'wpacc' ) );
		}
		return notify( 1, __( 'Internal error' ) );
	}

	/**
	 * Display the form
	 *
	 * @return string
	 */
	public function read() : string {
		$debtor_id = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT );
		if ( $debtor_id ) {
			$this->debtor = new Debtor( $debtor_id );
		}
		ob_start();
		?>
		<label for="wpacc_name"><?php esc_html_e( 'Name', 'wpacc' ); ?>
			<input name="name" id="wpacc_name" value="<?php echo esc_attr( $this->debtor->name ); ?>" >
		</label>
		<label for="wpacc_address"><?php esc_html_e( 'Address', 'wpacc' ); ?>
			<textarea name="address" id="wpacc_address" ><?php echo esc_attr( $this->debtor->address ); ?></textarea>
		</label>
		<label for="wpacc_email_address"><?php esc_html_e( 'EMail', 'wpacc' ); ?>
			<input name="email_address" id="wpacc_email_address" value="<?php echo esc_attr( $this->debtor->email_address ); ?>" >
		</label>
		<label for="wpacc_active"><?php esc_html_e( 'Active', 'wpacc' ); ?>
			<input name="active" id="wpacc_active" type="checkbox" <?php checked( $this->debtor->active ); ?>" >
		</label>
		<input type="hidden" name="id" value="<?php echo esc_attr( $this->debtor->id ); ?>" />
		<?php
		return $this->form( ob_get_clean() . $this->action_button( $this->debtor->id ? 'update' : 'create', __( 'Save', 'wpacc' ) ) );
	}

	/**
	 * Render the existing debtor
	 *
	 * @return string
	 */
	public function overview() : string {
		$debtors = new DebtorQuery( $this->business->id );
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
			<?php foreach ( $debtors->get_results() as $debtor ) : ?>
				<tr>
					<td></td>
					<td><?php echo esc_html( $debtor->id ); ?></td>
					<td><a href="<?php echo esc_url( sprintf( '?wpacc_action=read&id=%d', $debtor->id ) ); ?>"><?php echo esc_html( $debtor->name ); ?></a></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		}
		?>
		<?php
		return ob_get_clean() . $this->form( $this->action_button( 'change', __( 'Change', 'wpacc' ) ) );
	}


}
