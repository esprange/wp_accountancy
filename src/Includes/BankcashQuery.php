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
		$this->query_where .= $wpdb->prepare( ' AND type IN ( %s, %s )', Account::CASH_ITEM, Account::BANK_ITEM );
		$locale             = get_locale();
		return $wpdb->get_results(
			"SELECT id AS bankcash_id, name AS name, FORMAT( COALESCE( total, 0 ) + initial_value, 2, '$locale' ) AS actual_balance
			FROM {$wpdb->prefix}wpacc_account
			    LEFT JOIN
			    ( SELECT SUM( unitprice) AS total, account_id FROM {$wpdb->prefix}wpacc_detail ) detail
			    ON id = detail.account_id
			WHERE $this->query_where
			ORDER BY name",
			OBJECT_K
		);
	}

}
