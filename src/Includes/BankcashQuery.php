<?php
/**
 * Definition bank cash query class
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountacy/Includes
 */

namespace WP_Accountancy\Includes;

/**
 * Account query class.
 */
class BankcashQuery extends AccountQuery {

	/**
	 * Get the raw account results.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_results() : array {
		global $wpdb;
		$this->query_where .= $wpdb->prepare( ' AND type = %s OR type = %s', Account::BANK_ITEM, Account::CASH_ITEM );
		$locale             = get_locale();
		return $wpdb->get_results(
			"SELECT a.id AS bankcash_id, a.name AS name, FORMAT( COALESCE( SUM( d.unitprice ), 0 ) + a.initial_value, 2, '$locale' ) AS actual_balance
			FROM {$wpdb->prefix}wpacc_account AS a LEFT JOIN {$wpdb->prefix}wpacc_detail AS d ON a.id = d.account_id $this->query_where GROUP BY d.account_id
			ORDER BY a.name",
			OBJECT_K
		);
	}

}
