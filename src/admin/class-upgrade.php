<?php
/**
 * The admin actions of the plugin.
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountancy/admin
 */

namespace WP_Accountancy\Admin;

/**
 * Upgrades of data or database at new versions of the plugin.
 */
class Upgrade {

	/**
	 * Plugin-database-version
	 */
	const DBVERSION = 1;

	/**
	 * Execute upgrade actions if needed.
	 *
	 * @since 1.0.0
	 */
	public function run() {
		$data = get_plugin_data( plugin_dir_path( dirname( __FILE__ ) ) . 'wp-accountancy.php', false, false );
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
		$default_options = [];
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
	 * Convert database.
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
			name    TINYTEXT,
			adress  TEXT,
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
			business_id  INT (10),
			name         VARCHAR (50),
			rate         FLOAT,
			active       BOOLEAN DEFAULT TRUE,
			FOREIGN KEY (business_id) REFERENCES {$wpdb}wpacc_business(id),
			PRIMARY KEY  (id)
			) $charset_collate;"
		);

		/**
		 * The accounts of the general ledger. The COA exists for each business. A record can be a group, a group total or a regular account
		 * Regular accounts refer to the group using the group_id reference.
		 */
		dbDelta(
			"CREATE TABLE {$wpdb->prefix}wpacc_account (
			id          INT (10) NOT NULL AUTO_INCREMENT,
			business_id INT (10),
			taxcode_id  INT (10),
			name        VARCHAR (50),
			group_id    INT (10),
			type        TINYTEXT,
			active      BOOLEAN DEFAULT TRUE,
			FOREIGN KEY (business_id) REFERENCES {$wpdb}wpacc_business(id),
			FOREIGN KEY (taxcode_id) REFERENCES {$wpdb}wpacc_taxcode(id),
			PRIMARY KEY  (id)
			) $charset_collate;"
		);

		/**
		 * The creditors
		 */
		dbDelta(
			"CREATE TABLE {$wpdb->prefix}wpacc_creditor (
			id            INT (10) NOT NULL AUTO_INCREMENT,
			business_id   INT (10),
			name          VARCHAR (50),
			address       TEXT,
			email_address TINYTEXT,
			FOREIGN KEY (business_id) REFERENCES {$wpdb}wpacc_business(id),
			PRIMARY KEY  (id)
			) $charset_collate;"
		);

		/**
		 * The debtors
		 */
		dbDelta(
			"CREATE TABLE {$wpdb->prefix}wpacc_debtor (
			id              INT (10) NOT NULL AUTO_INCREMENT,
			business_id     INT (10),
			name            VARCHAR (50),
			address         TEXT,
			billing_address TEXT,
			email_address   TINYTEXT,
			FOREIGN KEY (business_id) REFERENCES {$wpdb}wpacc_business(id),
			PRIMARY KEY  (id),
			) $charset_collate;"
		);

		/**
		 * The transactions themselves. This record is used for all types, so including sales, purchases, banking,
		 */
		dbDelta(
			"CREATE TABLE {$wpdb->prefix}wpacc_transaction (
			id          INT (10) NOT NULL AUTO_INCREMENT,
			business_id INT (10),
			debtor_id   INT (10),
			creditor_id INT (10),
			reference   TINYTEXT,
			invoice     INT (10),
			address     TEXT,
			date        DATE,
			type        TINYTEXT,
			description TINYTEXT,
			FOREIGN KEY (debtor_id) REFERENCES {$wpdb}wpacc_debtor(id),
			FOREIGN KEY (creditor_id) REFERENCES {$wpdb}wpacc_creditor(id),
			FOREIGN KEY (business_id) REFERENCES {$wpdb}wpacc_business(id),
			PRIMARY KEY  (id)
			) $charset_collate;"
		);

		/**
		 * The transaction lines.
		 */
		dbDelta(
			"CREATE TABLE {$wpdb->prefix}wpacc_line (
			id             INT (10) NOT NULL AUTO_INCREMENT,
			transaction_id INT (10),
			account_id     INT (10),
			taxcode_id     INT (10),
			debtor_id      INT (10),
			creditor_id    INT (10),
			amount         FLOAT,
			unitprice      DECIMAL (13,4),
			description    TINYTEXT,
			FOREIGN KEY (account_id) REFERENCES {$wpdb}wpacc_account(id),
			FOREIGN KEY (taxcode_id) REFERENCES {$wpdb}wpacc_taxcode(id),
			PRIMARY KEY  (id)
			) $charset_collate;"
		);

	}


	/**
	 * Converteer data
	 */
	private function convert_data() {
		// Currently, no action.
	}

}
