<?php
/**
 * The Display base handler.
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountancy/Public
 */

namespace WP_Accountancy\Public;

use WP_Accountancy\Includes\Business;
use function WP_Accountancy\Includes\business;

/**
 * The Display class.
 */
abstract class Display {

	/**
	 * The business.
	 *
	 * @var Business $business The business object.
	 */
	protected Business $business;

	/**
	 * Overview function, to be rewritten in child class
	 *
	 * @return string
	 */
	abstract public function overview() : string;

	/**
	 * The constructor
	 *
	 * @param Business $business The business.
	 */
	final public function __construct( Business $business ) {
		$this->business = $business;
	}

	/**
	 * The controller.
	 *
	 * @return string
	 */
	final public function controller() : string {
		$action = filter_input( INPUT_POST, 'wpacc_action' ) ?: filter_input( INPUT_GET, 'wpacc_action' );
		if ( $action && method_exists( $this, $action ) ) {
			return $this->$action();
		}
		return $this->overview();
	}

	/**
	 * Check the nonce
	 *
	 * @return bool
	 */
	final public function check_nonce() : bool {
		return ( check_ajax_referer( 'wpacc_nonce', false ) );
	}

	/**
	 * The display container.
	 *
	 * @param string $contents The contents of the container.
	 *
	 * @return string
	 */
	final public function container( string $contents ) : string {
		$left_menu = [
			SummaryDisplay::Class      => __( 'Summary', 'wpacc' ),
			BankcashDisplay::Class     => __( 'Bank and Cash Accounts', 'wpacc' ),
			InteraccountDisplay::Class => __( 'Inter Account Transfers', 'wpacc' ),
			DebtorDisplay::Class       => __( 'Customers', 'wpacc' ),
			SalesDisplay::Class        => __( 'Sales Invoices', 'wpacc' ),
			CreditnoteDisplay::Class   => __( 'Credit Notes', 'wpacc' ),
			CreditorDisplay::Class     => __( 'Suppliers', 'wpacc' ),
			PurchaseDisplay::Class     => __( 'Purchase Invoices', 'wpacc' ),
			AssetDisplay::Class        => __( 'Fixed Assets', 'wpacc' ),
			DepreciationDisplay::Class => __( 'Depreciation Entries', 'wpacc' ),
			JournalDisplay::Class      => __( 'Journal Entries', 'wpacc' ),
			ReportDisplay::Class       => __( 'Reports', 'wpacc' ),
			SettingDisplay::Class      => __( 'Settings', 'wpacc' ),
		];
		$top_menu  = [
			BusinessDisplay::Class => __( 'Business', 'wpacc' ),
		];
		$html      = <<<EOT
		<div class="wpacc-container">
			<div class="wpacc-head">
				<nav class="wpacc-menu wpacc-menu-top" >
				<ul>
		EOT;
		foreach ( $top_menu as $key => $menu_item ) {
			$html .= <<<EOT
				<li><a data-menu="$key">$menu_item</a></li>
		EOT;
		}
		$html .= <<<EOT
				</ul>
				</nav>
				<span style="text-align: center">{$this->business->name}</span>
			</div>
			<div style="float: left; width: 20%;">
			<nav class="wpacc-menu">
			<ul>
		EOT;
		foreach ( $left_menu as $key => $menu_item ) {
			$html .= <<<EOT
			<li><a data-menu="$key" >$menu_item</a></li>
			EOT;
		}
		$html .= <<<EOT
			</ul>
			</nav>
			</div>
			<div style="float: right; width: 80%; padding-left: 10px" id="wpacc">$contents</div>
		</div>
		EOT;
		return $html;
	}

	/**
	 * Build a form submit button
	 *
	 * @param string $action The button action.
	 * @param string $text   The button label.
	 *
	 * @return string
	 */
	final protected function action_button( string $action, string $text ) : string {
		return <<<EOT
		<button name="wpacc_action" type="button" value="$action" >$text</button>
		EOT;
	}

	/**
	 * Build a form container
	 *
	 * @param string $contents The contents of the form.
	 *
	 * @return string
	 */
	final protected function form( string $contents ) : string {
		$nonce   = wp_create_nonce( 'wpacc_nonce' );
		$display = get_class( $this );
		return <<<EOT
		<form id="wpacc-form" class="wpacc-form" method="post" data-wpacc_display="$display" data-wpacc_nonce="$nonce">$contents</form>
		EOT;
	}

}
