<?php
/**
 * Definition account class
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountacy/Includes
 */

namespace WP_Accountancy\Includes;

/**
 * Account class.
 *
 * @property int    id
 * @property int    business_id
 * @property int    taxcode_id
 * @property int    group_id
 * @property string name
 * @property string type
 * @property bool   active,
 * @property int    order_number
 */
class Account {
	public const ASSETS_ITEM    = 'assets';
	public const LIABILITY_ITEM = 'liability';
	public const EQUITY_ITEM    = 'equity';
	public const INCOME_ITEM    = 'income';
	public const EXPENSE_ITEM   = 'expense';
	public const TOTAL_ITEM     = 'total';
	public const VALID_ITEMS    = [ self::ASSETS_ITEM, self::LIABILITY_ITEM, self::EQUITY_ITEM, self::INCOME_ITEM, self::EXPENSE_ITEM, self::TOTAL_ITEM ];

	/**
	 * Constructor
	 *
	 * @param int $business_id The business_id, required.
	 * @param int $account_id  The account id, if specified, the account is retrieved from the db.
	 */
	public function __construct( int $business_id, int $account_id = 0 ) {
		$data = [
			'id'           => $account_id,
			'business_id'  => $business_id,
			'taxcode_id'   => null,
			'group_id'     => null,
			'name'         => '',
			'active'       => true,
			'order_number' => 0,
			'type'         => '',
		];
		if ( $business_id ) {
			global $wpdb;
			$result = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}wpacc_account where id = %d",
					$account_id
				)
			);
			if ( $result ) {
				$data = $result;
			}
		}
		foreach ( $data as $property => $value ) {
			$this->$property = $value;
		}
	}

	/**
	 * Update the account.
	 *
	 * @return int The account id.
	 */
	public function update() : int {
		if ( $this->business_id ) {
			global $wpdb;
			$data = [
				'id'           => $this->id,
				'business_id'  => $this->business_id,
				'name'         => $this->name,
				'taxcode_id'   => $this->taxcode_id,
				'group_id'     => $this->group_id,
				'order_number' => $this->order_number,
				'active'       => $this->active,
				'type'         => $this->type,
			];
			$wpdb->replace( "{$wpdb->prefix}wpacc_account", $data );
			$this->id = $wpdb->insert_id;
			return $this->id;
		}
		return 0;
	}

}
