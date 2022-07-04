<?php
/**
 * Definition of the chart of accounts class
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountacy/Includes
 */

namespace WP_Accountancy\Includes;

/**
 * The Chart of Accounts class.
 *
 * @since      1.0.0
 */
class ChartOfAccounts {

	/**
	 * The accounts of the COA.
	 *
	 * @var array $accounts The accounts collection.
	 */
	private array $accounts;

	/**
	 * The constructor
	 *
	 * @param Business $business The business.
	 */
	public function __construct( Business $business ) {
		$this->accounts = ( new AccountQuery( $business, [ 'active' => 1 ] ) )->get_results();
	}

	/**
	 * Add the account to the collection
	 *
	 * @param Account $account The account to add.
	 *
	 * @return void
	 */
	public function add( Account $account ) : void {
		if ( false === $this->find( $account->id ) ) {
			$this->accounts[] = $account;
		}
	}

	/**
	 * Remove an account from the collection
	 *
	 * @param Account $account The account to remove.
	 *
	 * @return void
	 */
	public function remove( Account $account ) : void {
		$index = $this->find( $account->id );
		if ( false !== $index ) {
			unset( $this->accounts[ $index ] );
		}
	}

	/**
	 * Give the accounts.
	 *
	 * @return array
	 */
	public function get_results() :array {
		return $this->accounts;
	}

	/**
	 * Find the account in the collection.
	 *
	 * @param int $account_id The account id to search for.
	 *
	 * @return int|bool The index in the array or false if not found
	 */
	private function find( int $account_id ) : int|bool {
		foreach ( $this->accounts as $index => $account ) {
			if ( $account_id === $account->id ) {
				return $index;
			}
		}
		return false;
	}
}
