<?php
/**
 * The creditor handler.
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountancy/Public
 */

namespace WP_Accountancy\Public;

use WP_Accountancy\Includes\Creditor;
use WP_Accountancy\Includes\CreditorQuery;
use function WP_Accountancy\Includes\notify;

/**
 * The Creditor display class.
 */
class CreditorDisplay extends Display {

	/**
	 * Creditor
	 *
	 * @var Creditor $creditor The creditor.
	 */
	private Creditor $creditor;

	/**
	 * Create the creditor.
	 *
	 * @return string
	 */
	public function create() : string {
		return $this->update();
	}

	/**
	 * Update the creditor.
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
			$this->creditor = new Creditor( $input['id'] );
		}
		$this->creditor->name          = $input['name'];
		$this->creditor->address       = $input['address'];
		$this->creditor->email_address = $input['email_address'];
		$this->creditor->active        = $input['active'];
		$this->creditor->business_id   = $this->business->id;
		$this->creditor->update();
		do_action( 'wpacc_creditor_select', $this->creditor->id );
		return notify( -1, __( 'Supplier saved', 'wpacc' ) );
	}

	/**
	 * Delete the creditor
	 *
	 * @return string
	 */
	public function delete() : string {
		$creditor_id = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT );
		if ( $creditor_id ) {
			$this->creditor = new Creditor( $creditor_id );
			if ( $this->creditor->delete() ) {
				return notify( - 1, __( 'Creditor removed', 'wpacc' ) );
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
		$creditor_id = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT );
		if ( $creditor_id ) {
			$this->creditor = new Creditor( $creditor_id );
		}
		ob_start();
		?>
		<label for="wpacc_name"><?php esc_html_e( 'Name', 'wpacc' ); ?>
			<input name="name" id="wpacc_name" value="<?php echo esc_attr( $this->creditor->name ); ?>" >
		</label>
		<label for="wpacc_address"><?php esc_html_e( 'Address', 'wpacc' ); ?>
			<textarea name="address" id="wpacc_address" ><?php echo esc_attr( $this->creditor->address ); ?></textarea>
		</label>
		<label for="wpacc_email_address"><?php esc_html_e( 'EMail', 'wpacc' ); ?>
			<input name="email_address" id="wpacc_email_address" value="<?php echo esc_attr( $this->creditor->email_address ); ?>" >
		</label>
		<label for="wpacc_active"><?php esc_html_e( 'Active', 'wpacc' ); ?>
			<input name="active" id="wpacc_active" type="checkbox" <?php checked( $this->creditor->active ); ?>" >
		</label>
		<input type="hidden" name="id" value="<?php echo esc_attr( $this->creditor->id ); ?>" />
		<?php
		return $this->form( ob_get_clean() . $this->action_button( $this->creditor->id ? 'update' : 'create', __( 'Save', 'wpacc' ) ) );
	}

	/**
	 * Render the existing creditor
	 *
	 * @return string
	 */
	public function overview() : string {
		$creditors = new CreditorQuery( $this->business->id );
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
			<?php foreach ( $creditors->get_results() as $creditor ) : ?>
				<tr>
					<td></td>
					<td><?php echo esc_html( $creditor->id ); ?></td>
					<td><a href="<?php echo esc_url( sprintf( '?wpacc_action=read&id=%d', $creditor->id ) ); ?>"><?php echo esc_html( $creditor->name ); ?></a></td>
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
