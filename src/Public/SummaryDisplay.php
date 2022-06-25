<?php
/**
 * The summary display handler.
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountancy/Public
 */

namespace WP_Accountancy\Public;

use WP_Accountancy\Includes\Account;
use WP_Accountancy\Includes\ChartOfAccountsQuery;

/**
 * The Public filters.
 */
class SummaryDisplay extends Display {

	/**
	 * Provide the top title
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Summary', 'wpacc' );
	}

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
	private string $from = '2000-01-01'; // Allow the past, if one likes historical data.

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
		$this->summary = ( new ChartOfAccountsQuery(
			$this->business,
			[
				'from'  => $this->from,
				'until' => $this->until,
			]
		) )->get_results();

		ob_start();
		?>
		<div style="float:left;width:50%;padding-right: 10px" >
			<?php $this->list( Account::ASSETS_ITEM, __( 'Assets', 'wpacc' ) ); ?>
			<?php $this->list( Account::LIABILITY_ITEM, __( 'Liabilities', 'wpacc' ) ); ?>
			<?php $this->list( Account::EQUITY_ITEM, __( 'Equity', 'wpacc' ) ); ?>
		</div>
		<div style="float:right; width:50%;" >
			<?php $this->list( Account::INCOME_ITEM, __( 'Assets', 'wpacc' ) ); ?>
			<?php $this->list( Account::EXPENSE_ITEM, __( 'Expenses', 'wpacc' ) ); ?>
		</div>
		<?php
		return ob_get_clean() . $this->form( $this->button->action( 'change', __( 'Change', 'wpacc' ) ) );
	}

	/**
	 * Change function
	 *
	 * @return string
	 */
	public function change() : string {
		ob_start();
		?>
		<label for="wpacc_start"></label>

		<?php
		return ob_get_clean();
	}

	/**
	 * Show the specifics of a single account.
	 *
	 * @return string
	 */
	public function read() : string {
		ob_start();
		?>
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
		$list = array_filter(
			$this->summary,
			function( $list_item ) use ( $type ) {
				return $type === $list_item->type;
			}
		);
		?>
		<span style="font-size: large"><?php echo esc_html( $title ); ?></span>
		<ul style="list-style-type: none;">
		<?php foreach ( $list as $list_item ) : ?>
			<li>
				<?php echo esc_html( $list_item->name ); ?>
				<span style="float:right;"><a href="<?php echo esc_url( sprintf( '?wpacc_action=read&id=%d', $list_item->id ) ); ?>"><?php echo esc_html( number_format_i18n( $list_item->value ?? 0, 2 ) ); ?></a></span>
			</li>
		<?php endforeach; ?>
			<li>
				<strong><?php esc_html_e( 'Total', 'wpacc' ); ?>
				<span style="float:right;"><?php echo esc_html( number_format_i18n( $list_item->value ?? 0, 2 ) ); ?></span>
				</strong>
			</li>
		</ul>
		<?php
	}
}
