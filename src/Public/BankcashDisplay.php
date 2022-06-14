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
use WP_Accountancy\Includes\BankcashQuery;

/**
 * The Bank Cash display.
 */
class BankcashDisplay extends Display {

	/**
	 * Provide the top title
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Bank and Cash accounts', 'wpacc' );
	}

	/**
	 * Create the bank account.
	 *
	 * @return string
	 */
	public function create_bank() : string {
		$account       = new Account();
		$account->type = Account::BANK_ITEM;
		return $this->details( $account );
	}

	/**
	 * Create the cash account.
	 *
	 * @return string
	 */
	public function create_cash() : string {
		$account       = new Account();
		$account->type = Account::CASH_ITEM;
		return $this->details( $account );
	}

	/**
	 * Update the asset.
	 *
	 * @return string
	 */
	public function update() : string {
		global $wpacc_business;
		$input                  = filter_input_array(
			INPUT_POST,
			[
				'bankcash_id'   => FILTER_SANITIZE_NUMBER_INT,
				'name'          => FILTER_UNSAFE_RAW,
				'type'          => FILTER_UNSAFE_RAW,
				'initial_value' => FILTER_SANITIZE_NUMBER_FLOAT,
				'active'        => FILTER_SANITIZE_NUMBER_INT,
			]
		);
		$account                = new Account( $input['bankcash_id'] );
		$account->name          = $input['name'];
		$account->type          = $input['type'];
		$account->active        = $input['active'];
		$account->business_id   = $wpacc_business->id;
		$account->initial_value = $input['initial_value'];
		$account->update();
		return $this->notify( 1, 'bank' === $account->type ? __( 'Bank saved', 'wpacc' ) : __( 'Cash saved', 'wpacc' ) );
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
				return $this->notify( - 1, Account::BANK_ITEM === $account->type ? __( 'Bank removed', 'wpacc' ) : __( 'Cash removed', 'wpacc' ) );
			}
			return $this->notify( 0, __( 'Remove not allowed', 'wpacc' ) );
		}
		return $this->notify( 0, __( 'Internal error' ) );
	}

	/**
	 * Display the existing bank or cash account
	 *
	 * @return string
	 */
	public function read() : string {
		$account_id = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT );
		if ( $account_id ) {
			$account = new Account( $account_id );
			return $this->details( $account );
		}
		return $this->notify( 0, __( 'Internal error' ) );
	}

	/**
	 * Display the details of the account
	 *
	 * @param Account $account The bank or cash account.
	 * @return string
	 */
	private function details( Account $account ) : string {
		return $this->form(
			$this->field->render(
				[
					'name'  => 'name',
					'type'  => 'text',
					'label' => __( 'Name', 'wpacc' ),
					'value' => $account->name,
				]
			) .
			$this->field->render(
				[
					'name'  => 'initial_value',
					'type'  => 'currency',
					'value' => $account->active,
					'label' => __( 'Start balance', 'wpacc' ),
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
					'name'  => 'type',
					'type'  => 'hidden',
					'value' => $account->type,
				]
			) .
			$this->field->render(
				[
					'name'  => 'bankcash_id',
					'type'  => 'hidden',
					'value' => $account->id,
				]
			) .
			$this->button->save( __( 'Save', 'wpacc' ) ) . ( $account->id ? $this->button->delete( __( 'Delete', 'wpacc' ) ) : '' )
		);
	}

	/**
	 * Render the existing bank and cash accounts
	 *
	 * @return string
	 */
	public function overview() : string {
		return $this->form(
			$this->table->render(
				[
					'fields'  => [
						[
							'name'  => 'bankcash_id',
							'type'  => 'static',
							'label' => '',
						],
						[
							'name'  => 'name',
							'type'  => 'static',
							'label' => __( 'Name', 'wpacc' ),
						],
						[
							'name'  => 'actual_balance',
							'type'  => 'zoom',
							'label' => __( 'Balance', 'wpacc' ),
						],
					],
					'items'   => ( new BankcashQuery() )->get_results(),
					'options' => [
						'button_create_bank' => __( 'New bank account', 'wpacc' ),
						'button_create_cash' => __( 'New cash account', 'wpacc' ),
					],
				]
			)
		);
	}

}
