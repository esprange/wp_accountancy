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
	public function get_title() : string {
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
				'query' => 'summary',
			]
		) )->get_results();
		$assets        = $this->list( Account::ASSETS_ITEM, __( 'Assets', 'wpacc' ) );
		$liabilities   = $this->list( Account::LIABILITY_ITEM, __( 'Liabilities', 'wpacc' ) );
		$equity        = $this->list( Account::EQUITY_ITEM, __( 'Equity', 'wpacc' ) );
		$income        = $this->list( Account::INCOME_ITEM, __( 'Income', 'wpacc' ) );
		$expense       = $this->list( Account::EXPENSE_ITEM, __( 'Expenses', 'wpacc' ) );
		$html          = <<<EOT
		<div class="wpacc-split">
			<div style="grid-column:1;" >
				$assets
				$liabilities
				$equity
			</div>
			<div style="grid-column:2;" >
				$income
				$expense
			</div>
		</div>
		EOT;
		return $html . $this->form( $this->button->action( 'change', __( 'Change', 'wpacc' ) ) );
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
		$account = new Account( $this->business, intval( filter_input( INPUT_GET, 'id' ) ?? 0 ) );
		$list    = ( new ChartOfAccountsQuery(
			$this->business,
			[
				'from'       => $this->from,
				'until'      => $this->until,
				'account_id' => $account->id,
			]
		) )->get_results();
		return $this->table->render(
			[
				'fields' => [
					[
						'name' => 'transaction_id',
						'type' => 'hidden',
					],
					[
						'name'   => 'date',
						'type'   => 'date',
						'static' => true,
						'label'  => __( 'Date', 'wpacc' ),
					],
					[
						'name'   => 'transaction',
						'type'   => 'text',
						'static' => true,
						'label'  => __( 'Transaction', 'wpacc' ),

					],
					[
						'name'   => 'actor',
						'type'   => Account::EXPENSE_ITEM === $account->type || Account::INCOME_ITEM === $account->type ? 'text' : 'hidden',
						'static' => true,
						'label'  => Account::EXPENSE_ITEM === $account->type ? __( 'Supplier', 'wpacc' ) : __( 'Customer', 'wpacc' ),
					],
					[
						'name'   => 'description',
						'type'   => 'text',
						'static' => true,
						'label'  => __( 'Description', 'wpacc' ),
					],
					[
						'name'   => 'debit',
						'type'   => 'currency',
						'static' => true,
						'label'  => __( 'Debit', 'wpacc' ),
					],
					[
						'name'   => 'credit',
						'type'   => 'currency',
						'static' => true,
						'label'  => __( 'Credit', 'wpacc' ),
					],
				],
				'items'  => $list,
			]
		);
	}

	/**
	 * Show the account list
	 *
	 * @param string $type  Type of accounts to show.
	 * @param string $title The contents of the header.
	 */
	private function list( string $type, string $title ) : string {
		$list = array_filter(
			$this->summary,
			function( $list_item ) use ( $type ) {
				return $type === $list_item->type;
			}
		);
		$sum  = 0.0;
		$html = <<<EOT
		<span style="font-size: large">$title</span>
		<ul style="list-style-type: none;">
		EOT;
		foreach ( $list as $list_item ) {
			$sum  += $list_item->value ?? 0.0;
			$value = number_format_i18n( $list_item->value ?? 0, 2 );
			$html .= <<<EOT
			<li>$list_item->name
				<span style="float:right;"><a href="?wpacc_action=read&id=$list_item->account_id">$value</a></span>
			</li>
			EOT;
		}
		$total_label = __( 'Total', 'wpacc' );
		$total_value = number_format_i18n( $sum, 2 );
		$html       .= <<<EOT
			<li>
				<strong>$total_label
				<span style="float:right;">$total_value</span>
				</strong>
			</li>
		</ul>
		EOT;
		return $html;
	}
}
