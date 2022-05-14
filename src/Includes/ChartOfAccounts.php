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
	 */
	public function __construct() {
		$this->accounts = ( new AccountQuery( [ 'active' => 1 ] ) )->get_results();
	}

	/**
	 * Add the account to the collection
	 *
	 * @param Account $account The account to add.
	 *
	 * @return void
	 */
	public function add( Account $account ) {
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
	public function remove( Account $account ) {
		$index = $this->find( $account->id );
		if ( false !== $index ) {
			unset( $this->accounts[ $index ] );
		}
	}

	/**
	 * Import a chart of accounts.
	 *
	 * @param string $jsonfile An json file defining the COA.
	 *
	 * @return void
	 */
	public function import( string $jsonfile ) {
		$json = file_get_contents( $jsonfile, true ); // phpcs:ignore
		$coa  = json_decode( $json, true )['coa'] ?? [];
		foreach ( $coa as $account_item ) {
			if ( isset( $account_item['name'] ) && isset( $account_item['type'] ) && in_array( $account_item['type'], Account::VALID_ITEMS, true ) ) {
				$account       = new Account();
				$account->name = $account_item['name'];
				$account->type = $account_item['type'];
				$account->update();
				$this->add( $account );
			}
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
