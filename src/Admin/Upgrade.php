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

use WP_Accountancy\Includes\Country;

/**
 * Upgrades of data or database at new versions of the plugin.
 */
class Upgrade {

	/**
	 * Plugin-database-version
	 */
	const DBVERSION = 52;

	/**
	 * Execute upgrade actions if needed.
	 *
	 * @return void
	 */
	public function run() : void {
		$data = get_plugin_data( WPACC_PLUGIN_PATH . 'wp-accountancy.php', false, false );
		update_option( 'wpacc-plugin-version', $data['Version'] );
		$database_version = intval( get_option( 'wpacc-database-version', 0 ) );
		if ( $database_version < self::DBVERSION ) {
			$this->set_options();
			$this->set_database();
			$this->migrate_data();
			$this->load_data();
			update_option( 'wpacc-database-version', self::DBVERSION );
		}
	}

	/**
	 * Convert options.
	 *
	 * @return void
	 */
	private function set_options() : void {
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
	 *
	 * @return void
	 * @noinspection PhpIncludeInspection
	 */
	public function set_database() : void {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$this->set_country();
		$this->set_business();
		$this->set_taxcode();
		$this->set_asset();
		$this->set_account();
		$this->set_actor();
		$this->set_transaction();
		$this->set_detail();
	}

	/**
	 * Business table.
	 *
	 * @return void
	 */
	private function set_country() : void {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		dbDelta(
			"CREATE TABLE {$wpdb->prefix}wpacc_country (
			name      TINYTEXT NOT NULL,
			language  TINYTEXT NOT NULL,
			currency  CHAR(3),
			file      TINYTEXT,
			UNIQUE KEY name_lang_idx ( name, language )
			) $charset_collate;"
		);
	}

	/**
	 * Business table.
	 *
	 * @return void
	 */
	private function set_business() : void {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		dbDelta(
			"CREATE TABLE {$wpdb->prefix}wpacc_business (
			id          INT (10) NOT NULL AUTO_INCREMENT,
			slug        TINYTEXT,
			name        TINYTEXT NOT NULL,
			address     TEXT,
			country     TINYTEXT NOT NULL,
			language    TINYTEXT NOT NULL,
			currency    CHAR(3)  DEFAULT 'EUR',
			decimals    TINYINT  DEFAULT 2,
			decimalsep  CHAR(1)  DEFAULT ',',
			thousandsep CHAR(1)  DEFAULT '.',
			dateformat  CHAR(10) DEFAULT 'd-m-Y',
			timeformat  CHAR(10) DEFAULT 'H:i',
			logo_url    TINYTEXT,
			logo        TINYTEXT,
			UNIQUE KEY name_idx (name),
			PRIMARY KEY  (id)
			) $charset_collate;"
		);
	}

	/**
	 * Taxcodes, can be different for each business.
	 *
	 * @return void
	 */
	private function set_taxcode() : void {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		dbDelta(
			"CREATE TABLE {$wpdb->prefix}wpacc_taxcode (
			id           INT (10) NOT NULL AUTO_INCREMENT,
			business_id  INT (10) NOT NULL,
			account_id   INT (10) NULL,
			name         VARCHAR (50) NOT NULL,
			rate         FLOAT,
			active       BOOL DEFAULT TRUE,
			UNIQUE KEY business_name_idx ( business_id, name ),
			PRIMARY KEY  (id)
			) $charset_collate;"
		);
		$this->foreign_key( 'taxcode', 'business' );
		$this->foreign_key( 'taxcode', 'account' );
	}

	/**
	 * Taxcodes, can be different for each business.
	 *
	 * @return void
	 */
	private function set_asset() : void {
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
			UNIQUE KEY business_name_idx ( business_id, name ),
			PRIMARY KEY  (id)
			) $charset_collate;"
		);
		$this->foreign_key( 'asset', 'business' );
	}

	/**
	 * The accounts of the general ledger. The COA exists for each business. A record can be a group, a group total or a regular account
	 * Regular accounts refer to the group using the group_id reference.
	 *
	 * @return void
	 */
	private function set_account() : void {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		dbDelta(
			"CREATE TABLE {$wpdb->prefix}wpacc_account (
			id            INT (10) NOT NULL AUTO_INCREMENT,
			business_id   INT (10) NOT NULL,
			taxcode_id    INT (10) NULL,
			name          VARCHAR (50) NOT NULL,
			group_id      INT (10) NULL,
			type          TINYTEXT,
			order_number  INT,
			active        BOOL DEFAULT TRUE,
			initial_value DECIMAL(13,4) DEFAULT 0.0,
			UNIQUE KEY business_name_idx (business_id, name ),
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
	 *
	 * @return void
	 */
	private function set_actor() : void {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		dbDelta(
			"CREATE TABLE {$wpdb->prefix}wpacc_actor (
			id              INT (10) NOT NULL AUTO_INCREMENT,
			business_id     INT (10) NOT NULL,
			name            VARCHAR (50) NOT NULL,
			address         TEXT,
			billing_address TEXT,
			email_address   TINYTEXT,
			active          BOOL DEFAULT TRUE,
			type            TINYTEXT,
			UNIQUE KEY business_name_idx ( business_id, name ),
			PRIMARY KEY  (id)
			) $charset_collate;"
		);
		$this->foreign_key( 'actor', 'business' );
	}

	/**
	 * The transactions themselves. This record is used for all types, so including sales, purchases, banking.
	 *
	 * @return void
	 */
	private function set_transaction() : void {
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
			tax_include BOOL DEFAULT TRUE,
			PRIMARY KEY  (id)
			) $charset_collate;"
		);
		$this->foreign_key( 'transaction', 'business' );
		$this->foreign_key( 'transaction', 'actor' );
	}

	/**
	 * The transaction details.
	 *
	 * @return void
	 */
	private function set_detail() : void {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		dbDelta(
			"CREATE TABLE {$wpdb->prefix}wpacc_detail (
			id             INT (10) NOT NULL AUTO_INCREMENT,
			transaction_id INT (10) NOT NULL,
			account_id     INT (10) NOT NULL,
			taxcode_id     INT (10) NULL,
			actor_id       INT (10) NULL,
			quantity       FLOAT DEFAULT 1.0,
			unitprice      DECIMAL (13,4) DEFAULT 0.0,
			description    TINYTEXT,
			order_number   INT,
			debit          DECIMAL (13,4) DEFAULT 0.0,
			credit         DECIMAL (13,4) DEFAULT 0.0,
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
	 *
	 * @noinspection PhpSameParameterValueInspection
	 */
	private function foreign_key( string $table, string $parent, string $foreign = '', string $action = 'CASCADE' ) : void {
		if ( defined( 'WPACC_TEST' ) ) {
			return; // Phpunit creates temporary tables which don't allow foreign key constraints.
		}
		global $wpdb;
		$foreign = $foreign ?: "{$parent}_id";
		$db_name = DB_NAME;
		// phpcs:disable -- next line cannot be used with prepare.
		if ( $wpdb->get_var(
			"SELECT COUNT(*)
			    FROM information_schema.TABLE_CONSTRAINTS
			    WHERE
			        information_schema.TABLE_CONSTRAINTS.CONSTRAINT_TYPE = 'FOREIGN KEY' AND
			      	information_schema.TABLE_CONSTRAINTS.TABLE_SCHEMA = '$db_name' AND
			      	information_schema.TABLE_CONSTRAINTS.CONSTRAINT_NAME = 'fk_{$parent}_$table'"
			) ) {
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}wpacc_$table DROP FOREIGN KEY fk_{$parent}_$table" );
		}
		$wpdb->query( "ALTER TABLE {$wpdb->prefix}wpacc_$table
			ADD CONSTRAINT fk_{$parent}_$table FOREIGN KEY ($foreign) REFERENCES {$wpdb->prefix}wpacc_$parent(id)
			ON DELETE $action ON UPDATE $action"
		);
		// phpcs:enable
	}

	/**
	 * Load data
	 *
	 * @return void
	 */
	private function load_data() : void {
		$countries_data = file_get_contents( __DIR__ . '\..\Templates\countries.json' ); // phpcs:ignore
		if ( false === $countries_data ) {
			trigger_error( 'Error loading countries, file cannot be opened', E_USER_ERROR ); // phpcs:ignore
		}
		$countries = json_decode( $countries_data );
		if ( $countries ) {
			foreach ( $countries as $item ) {
				$country           = new Country( $item->country, $item->language );
				$country->file     = $item->file;
				$country->currency = $item->currency;
				$country->insert();
			}
			return;
		}
		trigger_error( 'Error loading countries, no data', E_USER_ERROR ); // phpcs:ignore
	}

	/**
	 * Converteer data
	 */
	private function migrate_data() : void {
		// Currently, no action.
	}

}
