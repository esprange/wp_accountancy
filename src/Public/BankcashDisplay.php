<?php
/**
 * The bankcash display handler.
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountancy/Public
 */

namespace WP_Accountancy\Public;

use WP_Accountancy\Includes\Account;
use WP_Accountancy\Includes\Detail;
use WP_Accountancy\Includes\DetailQuery;
use WP_Accountancy\Includes\Transaction;
use WP_Accountancy\Includes\AssetQuery;
use WP_Accountancy\Includes\TransactionQuery;
use function WP_Accountancy\Includes\notify;

/**
 * The Public filters.
 */
class BankcashDisplay extends Display {
	/**
	 * Create the bank or cash.
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
		$input                = filter_input_array(
			INPUT_POST,
			[
				'id'     => FILTER_SANITIZE_NUMBER_INT,
				'name'   => FILTER_UNSAFE_RAW,
				'type'   => FILTER_UNSAFE_RAW,
				'start'  => FILTER_SANITIZE_NUMBER_FLOAT,
				'active' => FILTER_SANITIZE_NUMBER_INT,
			]
		);
		$account              = new Account( $input['id'] );
		$account->name        = $input['name'];
		$account->type        = $input['type'];
		$account->active      = $input['active'];
		$account->business_id = $wpacc_business->id;
		$account->update();
		$this->start_balance( $account->id, $input['start'] );
		return notify( -1, 'bank' === $account->type ? __( 'Bank saved', 'wpacc' ) : __( 'Cash saved', 'wpacc' ) );
	}

	/**
	 * Delete the bank/cash account
	 *
	 * @return string
	 */
	public function delete() : string {
		$account_id = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT );
		if ( $account_id ) {
			$account = new Account( $account_id );
			if ( $account->delete() ) {
				return notify( - 1, 'bank' === $account->type ? __( 'Bank removed', 'wpacc' ) : __( 'Cash removed', 'wpacc' ) );
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
		$account = new Account( intval( filter_input( INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT ) ) );
		ob_start();
		?>
		<label for="wpacc_name"><?php esc_html_e( 'Name', 'wpacc' ); ?>
			<input name="name" id="wpacc_name" value="<?php echo esc_attr( $account->name ); ?>" >
		</label>
		<label for="wpacc_start"><?php esc_html_e( 'Starting Balance', 'wpacc' ); ?>
			<input name="start" type="number" id="wpacc_start" value="<?php echo esc_attr( $account->start ); ?>" >
		</label>
		<label for="wpacc_active"><?php esc_html_e( 'Active', 'wpacc' ); ?>
			<input name="active" id="wpacc_active" type="checkbox" <?php checked( $account->active ); ?>" >
		</label>
		<input type="hidden" name="id" value="<?php echo esc_attr( $account->id ); ?>" />
		<?php
		return $this->form( ob_get_clean() . $this->action_button( $account->id ? 'update' : 'create', __( 'Save', 'wpacc' ) ) );
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
		return ob_get_clean() . $this->form( $this->action_button( 'change', __( 'Change', 'wpacc' ) ) );
	}

	/**
	 * Create or update the start amount of this bank or cash position
	 *
	 * @param int   $account_id The account id of the bank or cash definition.
	 * @param float $start      The starting balance.
	 *
	 * @return void
	 */
	private function set_start_balance( int $account_id, float $start ) {
		$transactions      = ( new TransactionQuery( [ 'id' => $account_id ] ) )->get_results();
		$transaction       = Transaction::START_BALANCE === $transactions[0]->type ? $transactions[0] : new Transaction();
		$transaction->type = Transaction::START_BALANCE;
		$transaction->update();
		$details            = ( new DetailQuery( [ 'transaction_id' => $transaction->id ] ) )->get_results();
		$detail             = $details ? $details[0] : new Detail( $transaction->id );
		$detail->account_id = $account_id;
		$detail->amount     = 1;
		$detail->unitprice  = $start;
		$detail->update();
	}

	private function get_start_balance( int $account_id ) : float {
		$transactions = ( new TransactionQuery( [ 'id' => $account_id ] ) )->get_results();

	}
}
