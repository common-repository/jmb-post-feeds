<?php
/*
Plugin Name: JMB Post Feeds
Description: Generate post feeds in various formats.
Version: 1.0
Author: James Bailey
Text Domain: jmb-pf
Domain Path: /languages
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$jmb_post_feeds = JMB_Post_Feeds::get_instance();

class JMB_Post_Feeds {
	/**
	 * Singleton class instance.
	 *
	 * @var object
	 */
	private static $instance;

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
	 * Get the class instance.
	 *
	 * @return object
	 */
	function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$dir_path = plugin_dir_path( __FILE__ );

		require_once( $dir_path . 'includes/meta-boxes.php' );
		require_once( $dir_path . 'includes/exporter.php' );
		require_once( $dir_path . 'includes/functions.php' );
		require_once( $dir_path . 'includes/test-page.php' );
		require_once( $dir_path . 'includes/download-feed.php' );
		require_once( $dir_path . 'includes/feeds-table.php' );

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'before_delete_post', array( $this, 'before_delete_post' ) );
	}

	/**
	 * Plugin startup.
	 *
	 * @return null
	 */
	function init() {
		date_default_timezone_set( get_option( 'timezone_string' ) );

		$upload_dir = wp_upload_dir();

		$td = 'jmb-pf';
		
		$exporter = new JMB_Post_Feeds_Exporter;

		$this->config = array_merge( array( 
				'dev_mode'          => true,
				'text_domain'       => $td,
				'dir_path'          => str_replace( '\\', '/', plugin_dir_path( __FILE__ ) ),
				'dir_url'           => plugin_dir_url( __FILE__ ),
				'filter_operations' => array(
					'=', '!=',
					'<', '>',
					'<=', '>=',
					'in', 'not in',
					'like', 'not like',
					'empty', 'not empty',
				),
				'sorting_options' => array(
					'p.ID'            => __( 'ID', $td ),
					'p.post_title'    => __( 'Title', $td ),
					'p.post_date'     => __( 'Date Created', $td ),
					'p.post_modified' => __( 'Date Modified', $td ),
					'p.comment_count' => __( 'Comment Count', $td ),
				),
		    ),
			apply_filters( 'jmb_pf_plugin_settings', array(
				'feed_dir_path'  => str_replace( '\\', '/', $upload_dir['basedir'] ). '/jmb-post-feeds/',
				'feed_dir_url'   => $upload_dir['baseurl'] . '/jmb-post-feeds/',
				'log_file'       => 'c:/wamp/www/log.txt',
				'export_formats' => array(
					'csv' => array(
						'name'     => 'CSV',
						'ext'      => '.csv',
						'callback' => array( $exporter, 'csv' ),
					),
					'xml' => array(
						'name'     => 'XML',
						'ext'      => '.xml',
						'callback' => array( $exporter, 'xml' ),
					),
					'text' => array(
						'name'     => 'Text',
						'ext'      => '.txt',
						'callback' => array( $exporter, 'text' ),
					),
					'rss' => array(
						'name'     => 'RSS',
						'ext'      => '-rss.xml',
						'callback' => array( $exporter, 'rss' ),
					),
					'google_rss' => array(
						'name'     => 'Google RSS',
						'ext'      => '-google-rss.xml',
						'callback' => array( $exporter, 'google_rss' ),
					),
				),
			) )
		);

		// Load functions.
		$this->functions = new JMB_Post_Feeds_Functions( $this->config );

		// Load meta boxes.
		$meta_boxes = new JMB_Post_Feeds_Meta_Boxes( $this->config, $this->functions );

		if ( $this->dev_mode ) {
			// Load test page.
			$test_page = new JMB_Post_Feeds_Test_Page( $this->config, $this->functions );
		}

		// Load download feed functionality.
		$download_feed = new JMB_Post_Feeds_Download( $this->config, $this->functions );

		// Load the post feeds table modifications.
		$post_feeds_table = new JMB_Post_Feeds_Table( $this->config, $this->functions );

		// Register the custom post type.
		register_post_type( 'jmb_post_feed', array( 
			'description'  => __( 'Custom post type for the JMB Post Feeds plugin.', $td ),
			'public'       => false,
			'show_ui'      => true,
			'show_in_menu' => true,
			'supports'     => array( 'title' ),
			'labels'       => array(
				'name'               => _x( 'Post Feeds', 'post type general name', $td ),
				'singular_name'      => _x( 'Post Feed', 'post type singular name', $td ),
				'menu_name'          => _x( 'Post Feeds', 'admin menu', $td ),
				'name_admin_bar'     => _x( 'Post Feed', 'add new on admin bar', $td ),
				'add_new'            => _x( 'Add New', 'post feed', $td ),
				'add_new_item'       => __( 'Add New Post Feed', $td ),
				'new_item'           => __( 'New Post Feed', $td ),
				'edit_item'          => __( 'Edit Post Feed', $td ),
				'view_item'          => __( 'View Post Feed', $td ),
				'all_items'          => __( 'All Post Feeds', $td ),
				'search_items'       => __( 'Search Post Feeds', $td ),
				'parent_item_colon'  => __( 'Parent Post Feeds:', $td ),
				'not_found'          => __( 'No post feeds found.', $td ),
				'not_found_in_trash' => __( 'No post feeds found in Trash.', $td ),
			),
		) );
	}

	/**
	 * Load the admin CSS & JS.
	 *
	 * @param string $hook
	 * @return null
	 */
	function admin_enqueue_scripts( $hook ) {
		wp_register_script( 'jmb-post-feed-admin', $this->dir_url . 'assets/js/admin.js', array(), '1.0.0' );
		wp_register_style( 'jmb-post-feed-admin', $this->dir_url . 'assets/css/admin.css', array(), '1.0.0' );

		$load_assets = false;

		if ( 'post.php' == $hook ) {
			global $post;

			if ( 'jmb_post_feed' == $post->post_type ) {
				$load_assets = true;
			}
		} elseif ( 'edit.php' == $hook || 'post-new.php' == $hook ) {
			if ( ! empty( $_GET['post_type'] ) && ( 'jmb_post_feed' == $_GET['post_type'] ) ) {
				$load_assets = true;
			}
		}

		if ( $load_assets ) {
			wp_localize_script( 'jmb-post-feed-admin', 'data_obj', array( 
				'field_value_options' => $this->get_field_value_options(),
				'filter_operations'   => $this->filter_operations,
				'button_delete'       => __( 'Delete', $td ),
				'button_move'         => __( 'Move', $td ),
				'select_option'       => __( '--- Select ---', $td ),
			) );

			wp_enqueue_script( 'jmb-post-feed-admin' );
			wp_enqueue_style( 'jmb-post-feed-admin' );
		}
	}

	/**
	 * Delete a post feed's files when deleting the post feed.
	 *
	 * @param int $post_id
	 * @return null
	 */
	function before_delete_post( $post_id ) {
		global $post_type;

		if ( 'jmb_post_feed' != $post_type ) {
			return;
		}

		$feeds = get_post_meta( $post_id, 'jmb_pf_feeds', true );

		if ( is_array( $feeds ) ) {
			foreach ( $feeds as $feed ) {
				@unlink( $feed['path'] );
			}
		}
	}

}
