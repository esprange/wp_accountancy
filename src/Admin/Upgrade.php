<?php
/**
 * The Admin actions of the plugin.
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountancy/Admin
 */

namespace WP_Accountancy\Admin;

/**
 * Upgrades of data or database at new versions of the plugin.
 */
class Upgrade {

	/**
	 * Plugin-database-version
	 */
	const DBVERSION = 32;

	/**
	 * Execute upgrade actions if needed.
	 */
	public function run(): void {
		$data = get_plugin_data( WPACC_PLUGIN_PATH . 'wp-accountancy.php', false, false );
		update_option( 'wpacc-plugin-version', $data['Version'] );
		$database_version = intval( get_option( 'wpacc-database-version', 0 ) );
		if ( $database_version < self::DBVERSION ) {
			$this->convert_options();
			$this->convert_database();
			$this->convert_data();
			update_option( 'wpacc-database-version', self::DBVERSION );
		}
	}

	/**
	 * Convert options.
	 */
	private function convert_options(): void {
		$default_options = [
			'multibusiness' => false,
		];
		$default_setup   = [];
		$current_options = get_option( 'wpacc-options', [] );
		$current_setup   = get_option( 'wpacc-setup', [] );
		$options         = [];
		$setup           = [];
		foreach ( array_keys( $default_options ) as $key ) {
			if ( isset( $current_options[ $key ] ) ) {
				$options[ $key ] = $current_options[ $key ];
			}
		}
		foreach ( array_keys( $default_setup ) as $key ) {
			if ( isset( $current_setup[ $key ] ) ) {
				$setup[ $key ] = $current_setup[ $key ];
			} elseif ( isset( $current_options[ $key ] ) ) {
				$setup[ $key ] = $current_options[ $key ];
			}
		}
		update_option( 'wpacc-options', wp_parse_args( $options, $default_options ) );
		update_option( 'wpacc-setup', wp_parse_args( $setup, $default_setup ) );
	}

	/**
	 * Convert database. A long method but no reason to split it up into smaller segments.
	 */
	public function convert_database(): void {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$this->convert_business();
		$this->convert_taxcode();
		$this->convert_asset();
		$this->convert_account();
		$this->convert_actor();
		$this->convert_transaction();
		$this->convert_detail();
	}

	/**
	 * Business table.
	 */
	private function convert_business() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		dbDelta(
			"CREATE TABLE {$wpdb->prefix}wpacc_business (
			id       INT (10) NOT NULL AUTO_INCREMENT,
			slug     TINYTEXT,
			name     TINYTEXT,
			address  TEXT,
			country  TINYTEXT,
			logo_url TINYTEXT,
			logo     TINYTEXT,
			PRIMARY KEY  (id)
			) $charset_collate;"
		);
	}

	/**
	 * Taxcodes, can be different for each business.
	 */
	private function convert_taxcode() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		dbDelta(
			"CREATE TABLE {$wpdb->prefix}wpacc_taxcode (
			id           INT (10) NOT NULL AUTO_INCREMENT,
			business_id  INT (10) NOT NULL,
			name         VARCHAR (50) NOT NULL,
			rate         FLOAT,
			active       BOOL DEFAULT TRUE,
			PRIMARY KEY  (id)
			) $charset_collate;"
		);
		$this->foreign_key( 'taxcode', 'business' );
	}

	/**
	 * Taxcodes, can be different for each business.
	 */
	private function convert_asset() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		dbDelta(
			"CREATE TABLE {$wpdb->prefix}wpacc_asset (
			id           INT (10) NOT NULL AUTO_INCREMENT,
			business_id  INT (10) NOT NULL,
			name         VARCHAR (50) NOT NULL,
			description  TEXT,
			rate         FLOAT,
			cost         DECIMAL (13,4),
			provision    DECIMAL (13,4),
			PRIMARY KEY  (id)
			) $charset_collate;"
		);
		$this->foreign_key( 'asset', 'business' );
	}

	/**
	 * The accounts of the general ledger. The COA exists for each business. A record can be a group, a group total or a regular account
	 * Regular accounts refer to the group using the group_id reference.
	 */
	private function convert_account() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		dbDelta(
			"CREATE TABLE {$wpdb->prefix}wpacc_account (
			id            INT (10) NOT NULL AUTO_INCREMENT,
			business_id   INT (10) NOT NULL,
			taxcode_id    INT (10) NULL,
			name          VARCHAR (50),
			group_id      INT (10) NULL,
			type          TINYTEXT,
			order_number  INT,
			active        BOOL DEFAULT TRUE,
			initial_value DECIMAL(13,4) DEFAULT 0.0,
			PRIMARY KEY  (id)
			) $charset_collate;"
		);
		$this->foreign_key( 'account', 'business' );
		$this->foreign_key( 'account', 'taxcode' );
		$this->foreign_key( 'account', 'account', 'group_id' );
		$this->foreign_key( 'account', 'business' );
	}

	/**
	 * The actors
	 */
	private function convert_actor() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		dbDelta(
			"CREATE TABLE {$wpdb->prefix}wpacc_actor (
			id              INT (10) NOT NULL AUTO_INCREMENT,
			business_id     INT (10) NOT NULL,
			name            VARCHAR (50),
			address         TEXT,
			billing_address TEXT,
			email_address   TINYTEXT,
			active          BOOL DEFAULT TRUE,
			type            TINYTEXT,
			PRIMARY KEY  (id)
			) $charset_collate;"
		);
		$this->foreign_key( 'actor', 'business' );
	}

	/**
	 * The transactions themselves. This record is used for all types, so including sales, purchases, banking,
	 */
	private function convert_transaction() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		dbDelta(
			"CREATE TABLE {$wpdb->prefix}wpacc_transaction (
			id          INT (10) NOT NULL AUTO_INCREMENT,
			business_id INT (10) NOT NULL,
			actor_id    INT (10) NULL,
			reference   TINYTEXT,
			invoice_id  TINYTEXT NULL,
			address     TEXT,
			date        DATE,
			type        TINYTEXT,
			description TINYTEXT,
			PRIMARY KEY  (id)
			) $charset_collate;"
		);
		$this->foreign_key( 'transaction', 'business' );
		$this->foreign_key( 'transaction', 'actor' );
	}

	/**
	 * The transaction details.
	 */
	private function convert_detail() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		dbDelta(
			"CREATE TABLE {$wpdb->prefix}wpacc_detail (
			id             INT (10) NOT NULL AUTO_INCREMENT,
			transaction_id INT (10) NOT NULL,
			account_id     INT (10),
			taxcode_id     INT (10) NULL,
			actor_id       INT (10) NULL,
			quantity       FLOAT,
			unitprice      DECIMAL (13,4),
			description    TINYTEXT,
			order_number   INT,
			PRIMARY KEY  (id)
			) $charset_collate;"
		);
		$this->foreign_key( 'detail', 'transaction' );
		$this->foreign_key( 'detail', 'account' );
		$this->foreign_key( 'detail', 'taxcode' );
		$this->foreign_key( 'detail', 'actor' );
	}

	/**
	 * Create the foreignkey if is does not exist yet.
	 *
	 * @param string $table   The table for which the constraint is required.
	 * @param string $parent  The parent table to which the foreign key refers.
	 * @param string $foreign The foreign key, optional.
	 * @param string $action  Can be Cascade or Restrict or..
	 *
	 * @return void
	 */
	private function foreign_key( string $table, string $parent, string $foreign = '', string $action = 'CASCADE' ): void {
		if ( defined( 'WPACC_TEST' ) ) {
			return; // Phpunit creates temporary tables which don't allow foreign key constraints.
		}
		global $wpdb;
		$foreign = $foreign ?: "{$parent}_id";
		// phpcs:disable -- next line cannot be used with prepare.
		if ( ! $wpdb->get_var(
			"SELECT COUNT(*)
			    FROM information_schema.TABLE_CONSTRAINTS
			    WHERE
			        information_schema.TABLE_CONSTRAINTS.CONSTRAINT_TYPE = 'FOREIGN KEY' AND
			      	information_schema.TABLE_CONSTRAINTS.TABLE_SCHEMA = '$wpdb->dbname' AND
			      	information_schema.TABLE_CONSTRAINTS.CONSTRAINT_NAME = 'fk_{$parent}_$table'"
			) ) {
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}wpacc_$table
				ADD CONSTRAINT fk_{$parent}_$table FOREIGN KEY ($foreign) REFERENCES {$wpdb->prefix}wpacc_$parent(id)
				ON DELETE $action ON UPDATE $action"
			);
		}
		// phpcs:enable
	}

	/**
	 * Converteer data
	 */
	private function convert_data(): void {
		// Currently, no action.
	}

}
