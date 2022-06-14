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
 * @property bool   active
 * @property int    order_number
 * @property float  initial_value
 */
class Account extends Entity {
	public const ASSETS_ITEM    = 'assets';
	public const LIABILITY_ITEM = 'liability';
	public const EQUITY_ITEM    = 'equity';
	public const INCOME_ITEM    = 'income';
	public const EXPENSE_ITEM   = 'expense';
	public const BANK_ITEM      = 'bank';
	public const CASH_ITEM      = 'cash';
	public const TOTAL_ITEM     = 'total';
	public const COA_ITEMS      = [ self::ASSETS_ITEM, self::LIABILITY_ITEM, self::EQUITY_ITEM, self::INCOME_ITEM, self::EXPENSE_ITEM, self::TOTAL_ITEM ];

	/**
	 * Constructor
	 *
	 * @param int $account_id  The account id, if specified, the account is retrieved from the db.
	 */
	public function __construct( int $account_id = 0 ) {
		global $wpacc_business;
		$this->fetch(
			[
				'id'            => $account_id,
				'business_id'   => $wpacc_business->id,
				'taxcode_id'    => null,
				'group_id'      => null,
				'name'          => '',
				'active'        => true,
				'order_number'  => 0,
				'type'          => '',
				'initial_value' => 0.0,
			]
		);
	}

}
