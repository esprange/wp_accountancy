<?php
/**
 * Register all actions and filters of the plugin
 *
 * @since      1.0.0
 *
 * @package    WP-Accountancy
 * @subpackage WP-Accountacy/Includes
 */

namespace WP_Accountancy\Includes;

/**
 * Maintain a list of all hooks within the plugin and register these using the WP API.
 */
class Loader {

	/**
	 * The plugin actions.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $actions    Registered actions.
	 */
	protected array $actions = [];

	/**
	 * The plugin filters.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $filters    Registered filters.
	 */
	protected array $filters = [];

	/**
	 * Add an actie.
	 *
	 * @since    1.0.0
	 * @param    string $hook             Action name.
	 * @param    object $component        Class name of the defined action method.
	 * @param    string $callback         Method name.
	 * @param    int    $priority         Optional priority. Default is 10.
	 * @param    int    $accepted_args    Optional number of arguments transferred to the callback. Default is 1.
	 */
	public function add_action( string $hook, object $component, string $callback, int $priority = 10, int $accepted_args = 1 ) : void {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Add a filter.
	 *
	 * @since    1.0.0
	 * @param    string $hook             Filter name.
	 * @param    object $component        Class name of the defined filter method.
	 * @param    string $callback         Method name.
	 * @param    int    $priority         Optional priority. Default is 10.
	 * @param    int    $accepted_args    Optional number of arguments transferred to the callback. Default is 1.
	 */
	public function add_filter( string $hook, object $component, string $callback, int $priority = 10, int $accepted_args = 1 ) : void {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Helper to register actions and filter.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    array  $hooks            Collection of hooks to register in.
	 * @param    string $hook             Name of hook.
	 * @param    object $component        Class De class name where the hook is registered at.
	 * @param    string $callback         Method name.
	 * @param    int    $priority         Optional priority. Default is 10.
	 * @param    int    $accepted_args    Optional number of arguments transferred to the callback. Default is 1.
	 * @return   array                    The collection.
	 */
	private function add( array $hooks, string $hook, object $component, string $callback, int $priority, int $accepted_args ) : array {
		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		);
		return $hooks;
	}

	/**
	 * Register filters, actions in WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() : void {
		foreach ( $this->filters as $hook ) {
			add_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}
		foreach ( $this->actions as $hook ) {
			add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}
	}

}
