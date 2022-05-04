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
	 * @param int $business_id The business id is required.
	 */
	public function __construct( int $business_id ) {
		$query          = new AccountQuery( $business_id, [ 'active' => 1 ] );
		$this->accounts = $query->get_results();
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
	 * @param string $xmlfile An XML file defining the COA.
	 *
	 * @return void
	 */
	public function import( string $xmlfile ) {
		$xml = file_get_contents( $xmlfile ); // phpcs:ignore
		if ( false !== $xml ) {
			$parser = xml_parser_create();
			xml_parse_into_struct( $parser, $xml, $coa );
			xml_parser_free( $parser );
		}
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
