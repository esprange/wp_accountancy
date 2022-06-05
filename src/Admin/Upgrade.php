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
	const DBVERSION = 10;

	/**
	 * Execute upgrade actions if needed.
	 *
	 * @since 1.0.0
	 */
	public function run() {
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
	 * Converteer opties.
	 */
	private function convert_options() {
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
		update_option( 'wpacc-opties', wp_parse_args( $options, $default_options ) );
		update_option( 'wpacc-setup', wp_parse_args( $setup, $default_setup ) );
	}

	/**
	 * Convert database. A long method but no reason to split it up into smaller segments.
	 *
	 * @suppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	public function convert_database() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		/**
		 * Business table.
		 */
		dbDelta(
			"CREATE TABLE {$wpdb->prefix}wpacc_business (
			id      INT (10) NOT NULL AUTO_INCREMENT,
			slug	TINYTEXT,
			name    TINYTEXT,
			address TEXT,
			country TINYTEXT,
			logo    TINYTEXT,
			PRIMARY KEY  (id)
			) $charset_collate;"
		);

		/**
		 * Taxcodes, can be different for each business.
		 */
		dbDelta(
			"CREATE TABLE {$wpdb->prefix}wpacc_taxcode (
			id           INT (10) NOT NULL AUTO_INCREMENT,
			business_id  INT (10) NOT NULL,
			name         VARCHAR (50) NOT NULL,
			rate         FLOAT,
			active       TINYINT(1) DEFAULT 1,
			PRIMARY KEY  (id)
			) $charset_collate;"
		);
		$this->foreign_key( 'taxcode', 'business' );

		/**
		 * Taxcodes, can be different for each business.
		 */
		dbDelta(
			"CREATE TABLE {$wpdb->prefix}wpacc_asset (
			id           INT (10) NOT NULL AUTO_INCREMENT,
			business_id  INT (10) NOT NULL,
			name         VARCHAR (50) NOT NULL,
			description  TEXT,
			rate         FLOAT,
			cost        DECIMAL (13,4),
			provision    DECIMAL (13,4),
			PRIMARY KEY  (id)
			) $charset_collate;"
		);
		$this->foreign_key( 'asset', 'business' );

		/**
		 * The accounts of the general ledger. The COA exists for each business. A record can be a group, a group total or a regular account
		 * Regular accounts refer to the group using the group_id reference.
		 */
		dbDelta(
			"CREATE TABLE {$wpdb->prefix}wpacc_account (
			id           INT (10) NOT NULL AUTO_INCREMENT,
			business_id  INT (10) NOT NULL,
			taxcode_id   INT (10),
			name         VARCHAR (50),
			group_id     INT (10),
			type         TINYTEXT,
			order_number INT,
			active       TINYINT(1) DEFAULT 1,
			PRIMARY KEY  (id)
			) $charset_collate;"
		);
		$this->foreign_key( 'account', 'business' );
		$this->foreign_key( 'account', 'taxcode' );
		$this->foreign_key( 'account', 'account', 'group_id' );

		/**
		 * The creditors
		 */
		dbDelta(
			"CREATE TABLE {$wpdb->prefix}wpacc_creditor (
			id            INT (10) NOT NULL AUTO_INCREMENT,
			business_id   INT (10) NOT NULL,
			name          VARCHAR (50),
			address       TEXT,
			email_address TINYTEXT,
			active        TINYINT(1) DEFAULT 1,
			PRIMARY KEY  (id)
			) $charset_collate;"
		);
		$this->foreign_key( 'creditor', 'business' );

		/**
		 * The debtors
		 */
		dbDelta(
			"CREATE TABLE {$wpdb->prefix}wpacc_debtor (
			id              INT (10) NOT NULL AUTO_INCREMENT,
			business_id     INT (10) NOT NULL,
			name            VARCHAR (50),
			address         TEXT,
			billing_address TEXT,
			email_address   TINYTEXT,
			active          TINYINT(1) DEFAULT 1,
			PRIMARY KEY  (id)
			) $charset_collate;"
		);
		$this->foreign_key( 'debtor', 'business' );

		/**
		 * The transactions themselves. This record is used for all types, so including sales, purchases, banking,
		 */
		dbDelta(
			"CREATE TABLE {$wpdb->prefix}wpacc_transaction (
			id          INT (10) NOT NULL AUTO_INCREMENT,
			business_id INT (10) NOT NULL,
			debtor_id   INT (10),
			creditor_id INT (10),
			reference   TINYTEXT,
			invoice_id  TINYTEXT,
			address     TEXT,
			date        DATE,
			type        TINYTEXT,
			description TINYTEXT,
			PRIMARY KEY  (id)
			) $charset_collate;"
		);
		$this->foreign_key( 'transaction', 'business' );
		$this->foreign_key( 'transaction', 'debtor' );
		$this->foreign_key( 'transaction', 'creditor' );

		/**
		 * The transaction details.
		 */
		dbDelta(
			"CREATE TABLE {$wpdb->prefix}wpacc_detail (
			id             INT (10) NOT NULL AUTO_INCREMENT,
			transaction_id INT (10) NOT NULL,
			account_id     INT (10),
			taxcode_id     INT (10),
			debtor_id      INT (10),
			creditor_id    INT (10),
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
		$this->foreign_key( 'detail', 'debtor' );
		$this->foreign_key( 'detail', 'creditor' );
	}

	/**
	 * Create the foreignkey if is does not exist yet.
	 *
	 * @param string $table   The table for which the constraint is required.
	 * @param string $parent  The parent table to which the foreign key refers.
	 * @param string $foreign The foreign key, optional.
	 *
	 * @return void
	 */
	private function foreign_key( string $table, string $parent, string $foreign = '' ) {
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
			        CONSTRAINT_SCHEMA = DATABASE() AND
			        CONSTRAINT_NAME   = 'fk_{$parent}_$table' AND
			        CONSTRAINT_TYPE   = 'FOREIGN KEY'"
			) ) {
			$query = "ALTER TABLE {$wpdb->prefix}wpacc_$table
				ADD CONSTRAINT fk_{$parent}_$table FOREIGN KEY ($foreign) REFERENCES {$wpdb->prefix}wpacc_$parent(id)";
			echo $query;
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}wpacc_$table
				ADD CONSTRAINT fk_{$parent}_$table FOREIGN KEY ($foreign) REFERENCES {$wpdb->prefix}wpacc_$parent(id)"
			);
		}
		// phpcs:enable
	}

	/**
	 * Converteer data
	 */
	private function convert_data() {
		// Currently, no action.
	}

}
