<?php
/**
 * JMB Post Feeds
 *
 * Download feed files via a virtual URL.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class JMB_Post_Feeds_Download {
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
	 *
	 * @param array $config
	 * @param array $functions
	 */
	function __construct( $config, $functions ) {
		$this->config    = $config;
		$this->functions = $functions;

		if ( isset( $_GET['jmb_pf_download'] ) && is_user_logged_in() && current_user_can( 'manage_options' ) ) {
			$data = $_GET['jmb_pf_download'];

			$data_parts = explode( '_', $data );

			if ( count( $data_parts ) == 2 ) {
				$post_feed_id = $data_parts[0];
				$feed_format  = $data_parts[1];

				if ( $post_feed = get_post( $post_feed_id ) ) {
					$feeds = get_post_meta( $post_feed_id, 'jmb_pf_feeds', true );

					if ( isset( $feeds[ $feed_format ] ) ) {
						$feed = $feeds[ $feed_format ];

						if ( file_exists( $feed['path'] ) ) {
							header( 'Content-type: application/x-msdownload', true, 200 );
							header( 'Content-disposition: attachment; filename=' . $feed['name'] );
							header( 'Pragma: no-cache' );
							header( 'Expires: 0' );

							echo file_get_contents( $feed['path'] );

							exit;
						}
					}
				}
			}
		}

	}

}
