<?php
/**
 * The purchase display handler.
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountancy/Public
 */

namespace WP_Accountancy\Public;

use WP_Accountancy\Includes\Account;
use WP_Accountancy\Includes\ChartOfAccounts;
use WP_Accountancy\Includes\ChartOfAccountsQuery;
use WP_Accountancy\Includes\DetailQuery;
use function WP_Accountancy\Includes\notify;
use function WP_Accountancy\Includes\business;

/**
 * The Public filters.
 */
class PurchaseDisplay extends Display {

	/**
	 * Ordered list of accounts.
	 *
	 * @var array $summary list.
	 */
	private array $summary;

	/**
	 * Start date of summary
	 *
	 * @var string $from Start date of summary.
	 */
	private string $from  = '2000-01-01'; // Allow the past, if one likes historical data.

	/**
	 * End date of summary
	 *
	 * @var string $until End date of summary.
	 */
	private string $until = '2100-01-01'; // Not to expect that WordPress still exists by the time :-).

	/**
	 * Render the existing business
	 *
	 * @return string
	 */
	public function overview() : string {
		$coa     = ( new ChartOfAccounts( business()->id ) )->get_results();
		$details = ( new ChartOfAccountsQuery(
			business()->id,
			[
				'from'  => $this->from,
				'until' => $this->until,
			]
		) )->get_results();

//		foreach ( Account::VALID_ITEMS as $item ) {
//			$this->summary[ $item ]['account'] = array_filter(
//				$coa,
//				function ( $account ) use ( $item ) {
//					return $item === $account->type;
//				}
//			);
//			$this->summary[ $item ]['value']   = array_sum(
//				array_map(
//					function ( $detail ) {
//						return $detail->unitprice * $detail->quantity;
//					},
//					array_filter(
//						$details,
//						function ( $detail ) use ( $item ) {
//							return $item === $detail->$this->summary[ $item ]['account']->type;
//						}
//					)
//				)
//			);
//		}
		foreach ( Account::VALID_ITEMS as $item ) {
			usort(
				$this->summary[ $item ],
				function( $left, $right ) {
					return $left['account']->order_number <=> $right['account']->order_number;
				}
			);
		}
		ob_start();
		?>
		<div style="float:left;width:50%;" >
			<?php $this->list( Account::ASSETS_ITEM, __( 'Assets', 'wpacc' ) ); ?>
			<?php $this->list( Account::LIABILITY_ITEM, __( 'Liabilities', 'wpacc' ) ); ?>
			<?php $this->list( Account::EQUITY_ITEM, __( 'Equity', 'wpacc' ) ); ?>
		</div>
		<div style="float:right; width:50%;" >
			<?php $this->list( Account::INCOME_ITEM, __( 'Assets', 'wpacc' ) ); ?>
			<?php $this->list( Account::EXPENSE_ITEM, __( 'Expenses', 'wpacc' ) ); ?>
		</div>
		<?php
		return ob_get_clean() . $this->form( $this->action_button( 'change', __( 'Change', 'wpacc' ) ) );
	}

	public function change() : string {
		ob_start();
		?>
		<label for="wpacc_start"></label>

		<?php
		return ob_get_clean();
	}

	/**
	 * Show the account list
	 *
	 * @param string $type  Type of accounts to show.
	 * @param string $title The contents of the header.
	 */
	private function list( string $type, string $title ) {
		?>
		<h2><?php echo esc_html( $title ); ?></h2>
		<ul style="list-style-type: none;">
		<?php foreach ( $this->summary[ $type ] as $account ) : ?>
			<li><?php echo esc_html( $account->name ); ?><span style="text-align:right;"><?php echo esc_html( $this->summary['value'] ); ?></span></li>
		<?php endforeach; ?>
			<li>
				<strong><?php esc_html_e( 'Total', 'wpacc' ); ?></strong>
			</li>
		</ul>
		<?php
	}
}
