<?php
/**
 * The chart of accounts display handler.
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountancy/Public
 */

namespace WP_Accountancy\Public;

use WP_Accountancy\Includes\Account;
use WP_Accountancy\Includes\AccountQuery;
use WP_Accountancy\Includes\Business;
use WP_Accountancy\Includes\TaxCodeQuery;

/**
 * The Public filters.
 */
class CoaDisplay extends Display {

	/**
	 * The balance account types and their labels.
	 *
	 * @var array
	 */
	private array $balance_items;

	/**
	 * The profit loss account types and their labels.
	 *
	 * @var array
	 */
	private array $profitloss_items;

	/**
	 * Constructor
	 *
	 * @param Business $business The active business.
	 */
	public function __construct( Business $business ) {
		parent::__construct( $business );
		$this->balance_items    = [
			Account::ASSETS_ITEM    => (object) [ 'name' => __( 'Assets', 'wpacc' ) ],
			Account::LIABILITY_ITEM => (object) [ 'name' => __( 'Liability', 'wpacc' ) ],
			Account::EQUITY_ITEM    => (object) [ 'name' => __( 'Equity', 'wpacc' ) ],
		];
		$this->profitloss_items = [
			Account::INCOME_ITEM  => (object) [ 'name' => __( 'Income', 'wpacc' ) ],
			Account::EXPENSE_ITEM => (object) [ 'name' => __( 'Expense', 'wpacc' ) ],
		];
	}


	/**
	 * Provide the top title
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Chart of Accounts', 'wpacc' );
	}

	/**
	 * Create a balance account.
	 *
	 * @return string
	 */
	public function create_balance_account() : string {
		return $this->read( $this->balance_items );
	}

	/**
	 * Create a profit loss account.
	 *
	 * @return string
	 */
	public function create_profitloss_account() : string {
		return $this->read( $this->profitloss_items );
	}

	/**
	 * Update the account.
	 *
	 * @return string
	 */
	public function update() : string {
		$input                  = filter_input_array( INPUT_POST );
		$account                = new Account( $this->business, intval( $input['id'] ?? 0 ) );
		$account->name          = sanitize_text_field( $input['name'] ?? '' );
		$account->taxcode_id    = intval( $input['taxcode_id'] ?? false ) ?: null;
		$account->group_id      = intval( $input['group_id'] ?? false ) ?: null;
		$account->type          = sanitize_text_field( $input['type'] ?? '' );
		$account->initial_value = 0.0;
		$account->active        = boolval( $input['active'] ?? true );
		$account->update();
		return $this->notify( 1, __( 'Account saved', 'wpacc' ) );
	}

	/**
	 * Delete the account.
	 *
	 * @return string
	 */
	public function delete() : string {
		$account_id = filter_input( INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT );
		if ( $account_id ) {
			$account = new Account( $this->business, intval( $account_id ) );
			if ( $account->delete() ) {
				return $this->notify( - 1, __( 'Account removed', 'wpacc' ) );
			}
			return $this->notify( 0, __( 'Remove not allowed', 'wpacc' ) );
		}
		return $this->notify( 0, __( 'Internal error' ) );
	}

	/**
	 * Display the form
	 *
	 * @param array $types Allowed types if it is a new account, otherwise the allowed types should belong to either balance or profit and loss.
	 * @return string
	 */
	public function read( array $types = [] ) : string {
		$account = new Account( $this->business, intval( filter_input( INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT ) ) );
		if ( $account->id ) {
			$types = in_array( $account->type, array_keys( $this->balance_items ), true ) ? $this->balance_items : $this->profitloss_items;
		}
		return $this->form(
			$this->field->render(
				[
					'name'     => 'name',
					'type'     => 'text',
					'value'    => $account->name,
					'label'    => __( 'Name', 'wpacc' ),
					'required' => true,
				]
			) .
			$this->field->render(
				[
					'name'  => 'type',
					'type'  => 'select',
					'value' => $account->type,
					'label' => 'Group',
					'list'  => $types,
				]
			) .
			$this->field->render(
				[
					'name'  => 'taxcode_id',
					'type'  => 'select',
					'list'  => ( new TaxCodeQuery( $this->business ) )->get_results(),
					'label' => __( 'Taxcode', 'wpacc' ),
				]
			) .
			$this->field->render(
				[
					'name'  => 'active',
					'type'  => 'checkbox',
					'value' => $account->active,
					'label' => __( 'Active', 'wpacc' ),
				]
			) .
			$this->field->render(
				[
					'name'  => 'id',
					'type'  => 'hidden',
					'value' => $account->id,
				]
			) .
			$this->button->save( __( 'Save', 'wpacc' ) ) .
			( $account->id ? $this->button->delete( __( 'Delete', 'wpacc' ) ) : '' )
		);
	}

	/**
	 * Render the existing purchases
	 *
	 * @return string
	 */
	public function overview() : string {
		$balance    = $this->form(
			$this->button->action( 'create_balance_account', __( 'New account', 'wpacc' ) ) .
			$this->button->action( 'create_balance_group', __( 'New group', 'wpacc' ) ) .
			$this->section( $this->balance_items )
		);
		$profitloss = $this->form(
			$this->button->action( 'create_profitloss_account', __( 'New account', 'wpacc' ) ) .
			$this->button->action( 'create_profitloss_group', __( 'New group', 'wpacc' ) ) .
			$this->button->action( 'create_total_group', __( 'New total', 'wpacc' ) ) .
			$this->section( $this->profitloss_items )
		);
		return <<<EOT
		<div class="wpacc-split">
			<div style="grid-column: 1">
				$balance
			</div>
			<div style="grid-column: 2">
				$profitloss
			</div>
		</div>
		EOT;
	}

	/**
	 * Render the sections
	 *
	 * @param  array $sections The sections to render.
	 * @return string
	 */
	private function section( array $sections ) : string {
		$html = '';
		foreach ( $sections as $section => $label ) {
			$groups = (
			new AccountQuery(
				$this->business,
				[
					'type'   => $section,
					'groups' => true,
				]
			)
			)->get_results();
			$items  = [];
			foreach ( $groups as $group ) {
				$items += [ "$group->account_id" => $group ];
				foreach (
					(
					new AccountQuery(
						$this->business,
						[
							'type'     => $section,
							'group_id' => $group->account_id,
						]
					)
					)->get_results() as $account_id => $account
				) {
					$items += [ $account_id => $account ];
				}
			}
			$html .= $this->section_table( $label->name, $items );
		}
		return $html;
	}

	/**
	 * Render the tabel for the section
	 *
	 * @param string $label The section label.
	 * @param array  $items The items of the section.
	 *
	 * @return string
	 */
	private function section_table( string $label, array $items ) : string {
		return "<span style='font-style: italic'><strong>$label</strong></span>" .
			$this->table->render(
				[
					'fields' => [
						[
							'name'  => 'account_id',
							'label' => 'id',
							'type'  => 'static',
						],
						[
							'name'  => 'name',
							'label' => __( 'Name', 'wpacc' ),
							'type'  => 'zoom',
						],
					],
					'items'  => $items,
				]
			);
	}

}
