<?php
/**
 * The journal display handler.
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountancy/Public
 */

namespace WP_Accountancy\Public;

use WP_Accountancy\Includes\JournalQuery;

/**
 * The Public filters.
 */
class JournalDisplay extends Display {

	/**
	 * Provide the top title
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Journal transactions', 'wpacc' );
	}

	/**
	 * Render the existing business
	 *
	 * @return string
	 */
	public function overview() : string {
		$journal = new JournalQuery( $this->business );
		return $this->form(
			$this->table->render(
				[
					'fields'  => [
						[
							'name'  => 'transaction_id',
							'label' => 'id',
							'type'  => 'static',
						],
						[
							'name'  => 'date',
							'label' => __( 'Invoice date', 'wpacc' ),
							'type'  => 'static',
						],
						[
							'name'  => 'description',
							'label' => __( 'Description', 'wpacc' ),
							'type'  => 'zoom',
						],
						[
							'name'  => 'debit_total',
							'label' => __( 'Debit', 'wpacc' ),
							'type'  => 'static',
						],
						[
							'name'  => 'credit_total',
							'label' => __( 'Credit', 'wpacc' ),
							'type'  => 'static',
						],
					],
					'items'   => $journal->get_results(),
					'options' => [ 'button_create' => __( 'New journal entry', 'wpacc' ) ],
				]
			)
		);
	}

}
