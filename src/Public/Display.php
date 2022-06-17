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

/**
 * The Display class.
 */
abstract class Display {

	/**
	 * Overview function, to be rewritten in child class
	 *
	 * @return string
	 */
	abstract public function overview() : string;

	/**
	 * Provide the top title of the contents
	 *
	 * @return string
	 */
	abstract public function get_title() : string;

	/**
	 * Button object
	 *
	 * @var Button To render a button.
	 */
	public Button $button;

	/**
	 * Table object
	 *
	 * @var Table To render a table.
	 */
	public Table $table;

	/**
	 * Field object
	 *
	 * @var Field To render a field.
	 */
	public Field $field;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->button = new Button();
		$this->table  = new Table();
		$this->field  = new Field();
	}

	/**
	 * The controller.
	 *
	 * @return string
	 */
	final public function controller() : string {
		$action = filter_input( INPUT_POST, 'wpacc_action' ) ?: filter_input( INPUT_GET, 'wpacc_action' );
		$title  = '<p class="wpacc-title" >' . $this->get_title() . '</p>';
		if ( $action && method_exists( $this, $action ) ) {
			return $title . $this->$action();
		}
		return $title . $this->overview();
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
		$head = $this->head();
		$menu = $this->menu();
		return <<<EOT
		<div class="wpacc-container">
			<div class="wpacc-head" id="wpacc-head">
				$head
				<button class="wpacc-menu-dropdown" id="wpacc-menu-dropdown">&#9776;</button>
			</div>
			<div class="wpacc-menu" id="wpacc-menu">
				$menu
			</div>
			<div class="wpacc-main" id="wpacc-main">
				$contents
			</div>
		</div>
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

	/**
	 * Create notification for the user.
	 *
	 * @param int    $status  1 success, 0 error, -1 information.
	 * @param string $message The message.
	 * @return string Html text.
	 * @noinspection PhpUnnecessaryCurlyVarSyntaxInspection
	 */
	final protected function notify( int $status, string $message ) : string {
		$levels = [
			-1 => 'wpacc-inform',
			0  => 'wpacc-error',
			1  => 'wpacc-success',
		];
		return "<div class=\"{$levels[$status]}\"><p>$message</p></div>";
	}

	/**
	 * Prepare the top row
	 *
	 * @return string
	 *
	 * @todo For the multi business option, the business name should be a button.
	 */
	private function head() : string {
		global $wpacc_business;
		$businessdisplay = BusinessDisplay::Class;
		$html            = <<<EOT
		<a data-menu="$businessdisplay" id="wpacc-business" class="wpacc-business">$wpacc_business->name</a>
		EOT;
		return apply_filters( 'wpacc_head', $html );
	}

	/**
	 * Prepare the menu.
	 *
	 * @return string
	 */
	private function menu() : string {
		$menu = [
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
		$menu = apply_filters( 'wpacc_menu', $menu );
		$html = <<<EOT
		<nav class="wpacc-menu">
			<ul>
		EOT;
		foreach ( $menu as $key => $menu_item ) {
			$html .= <<<EOT
				<li><a data-menu="$key" >$menu_item</a></li>
			EOT;
		}
		$html .= <<<EOT
			</ul>
		</nav>
		EOT;
		return $html;
	}
}
