<?php
/**
 * JMB Post Feeds
 *
 * Test Page.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class JMB_Post_Feeds_Test_Page {
	/**
	 * Plugin settings.
	 *
	 * @var array
	 */
	private $config;

	/**
	 * Plugin functions.
	 *
	 * @var object
	 */
	private $functions;

	/**
	 * Retrieve config array items directly.
	 *
	 * @param string $key
	 * @return mixed
	 */
	function __get( $key ) {
		if ( isset( $this->config[ $key ] ) ) {
			return $this->config[ $key ];
		}
	}

	/**
	 * Call a functions object method directly.
	 *
	 * @param string $name
	 * @param array $arguments
	 * @return mixed
	 */
	function __call( $name, $arguments = array() ) {
		if ( method_exists( $this, $name ) ) {
			return call_user_method_array( $name, $this, $arguments );
		} elseif ( method_exists( $this->functions, $name ) ) {
			return call_user_method_array( $name, $this->functions, $arguments );
		}

		return;
	}

	/**
	 * Constructor
	 */
	function __construct( $config, $functions ) {
		$this->config    = $config;
		$this->functions = $functions;

		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	/**
	 * Register the page.
	 *
	 * @return null
	 */
	function admin_menu() {
		add_submenu_page( 'edit.php?post_type=jmb_post_feed', __( 'Testing', $td ), __( 'Testing', $td ), 'manage_options', 'jmb-pf-test-page', array( $this, 'display' ) );
	}

	/**
	 * Display the page.
	 *
	 * @return null
	 */
	function display() {
		$td = $this->text_domain;

		?><div class="wrap">
			<h1><?php _e( 'Testing', $td ); ?></h1>

		</div><?php
	}

}
