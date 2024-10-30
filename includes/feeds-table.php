<?php
/**
 * JMB Post Feeds
 *
 * Modifications for the jmb_post_feed post type table.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class JMB_Post_Feeds_Table {
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

		add_filter( 'manage_jmb_post_feed_posts_columns', array( $this, 'manage_columns' ) );
		add_action( 'manage_posts_custom_column', array( $this, 'custom_columns' ), 10, 2 );
	}

	/**
	 * Add custom table columns.
	 *
	 * @param array $columns
	 * @return array
	 */
	function manage_columns( $columns ) {
		$td = $this->text_domain;

		$date = $columns['date'];

		unset( $columns['date'] );

		$columns['jmb_pf_feeds']        = __( 'Feeds', $td );
		$columns['jmb_pf_last_updated'] = __( 'Last Updated', $td );
		$columns['date'] = $date;

		return $columns;
	}

	/**
	 * Retrieve and display the post data for the custom table columns.
	 *
	 * @param string $column
	 * @param int $post_id
	 * @return null
	 */
	function custom_columns( $column, $post_id ) {
		switch ( $column ) {
			case 'jmb_pf_last_updated':
				$last_update = get_post_meta( $post_id, 'jmb_pf_last_update', true );

				if ( $last_update ) {
					$last_update = date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $last_update );
				}

				echo $last_update;
			break;
			case 'jmb_pf_feeds':
				$export_formats = $this->export_formats;
				$feeds = get_post_meta( $post_id, 'jmb_pf_feeds', true );

				foreach ( $feeds as $feed_format => $feed ) {
					if ( ! isset( $export_formats[ $feed_format ] ) ) {
						continue;
					}
					?>
					<a href="<?php echo site_url(); ?>/?jmb_pf_download=<?php echo $post_id; ?>_<?php echo $feed_format; ?>" class="button button-default" target="_blank"><strong><?php echo $export_formats[ $feed_format ]['name']; ?></strong></a> 
					<?php
				}
			break;
		}
	}

}
