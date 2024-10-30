<?php
/**
 * JMB Post Feeds
 *
 * Meta boxes. 
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class JMB_Post_Feeds_Meta_Boxes {
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

		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
	}

	/**
	 * Register the meta boxes.
	 *
	 * @return null
	 */
	function add_meta_boxes() {
		$td = $this->text_domain;

		$meta_boxes = array(
			'jmb_pf_fields_meta_box' => array(
				'title'    => __( 'Fields', $td ),
				'callback' => array( $this, 'fields' ),
			),
			'jmb_pf_filters_meta_box' => array(
				'title'    => __( 'Filters', $td ),
				'callback' => array( $this, 'filters' ),
			),
			'jmb_pf_feeds_meta_box' => array(
				'title'    => __( 'Feeds', $td ),
				'callback' => array( $this, 'feeds' ),
			),
			'jmb_pf_info_meta_box' => array(
				'title'    => __( 'Additional Information', $td ),
				'callback' => array( $this, 'info' ),
			),
			'jmb_pf_refine_search_meta_box' => array(
				'title'    => __( 'Refine Search', $td ),
				'callback' => array( $this, 'refine_search' ),
				'context'  => 'side',
			),
			'jmb_pf_export_options_meta_box' => array(
				'title'    => __( 'Export Options', $td ),
				'callback' => array( $this, 'export_options' ),
				'context'  => 'side',
			),
			'jmb_pf_post_types_meta_box' => array(
				'title'    => __( 'Post Types', $td ),
				'callback' => array( $this, 'post_types' ),
				'context'  => 'side',
			),
		);

		foreach ( $meta_boxes as $meta_box_id => $meta_box ) {
			$meta_box = array_merge( array(
				'title'    => '',
				'callback' => array(),
				'screen'   => 'jmb_post_feed',
				'context'  => 'advanced',
				'priority' => 'default',
			), $meta_box );

			if ( ! $meta_box['title'] || ! $meta_box['callback'] ) {
				continue;
			}

			add_meta_box( $meta_box_id, $meta_box['title'], $meta_box['callback'], $meta_box['screen'], $meta_box['context'], $meta_box['priority'] );
		}
	}

	/**
	 * Save the meta box data.
	 *
	 * @param int $post_id
	 * @param object $post
	 * @return null
	 */
	function save_post( $post_id, $post ) {
		// Auto update feeds.
		if ( $post->post_type != 'jmb_post_feed' ) {
			$posts = $this->get_posts( array( 
				'fields'  => 'ids',
				'filters' => array(
					array(
						'field'     => 'post:post_type',
						'operation' => '=',
						'value'     => 'jmb_post_feed',
					),
					array(
						'field'     => 'meta:jmb_pf_auto_update',
						'operation' => '=',
						'value'     => '1',
					),
					array(
						'field'     => 'meta:jmb_pf_post_types',
						'operation' => 'like',
						'value'     => '%"' . $post->post_type . '"%',
					),
				),
			) );

			if ( $posts ) {
				foreach ( $posts as $post_id ) {
					$this->generate_feed_files( $post_id );
				}
			}

			return;
		}

		// Save meta box data.
		if ( empty( $_POST['_jmb_pf_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['_jmb_pf_nonce'], 'jmb-pf-save-meta-box-data' ) ) {
			return;
		}

		$save_data = array(
			'jmb_pf_fields'  => array(),
			'jmb_pf_filters' => array(),
		);

		if ( isset( $_POST['jmb_pf_fields'] ) ) {
			foreach ( $_POST['jmb_pf_fields'] as $field ) {
				if ( ! empty( $field['name'] ) && ! empty( $field['value'] ) && empty( $field['delete'] ) ) {
					$save_data['jmb_pf_fields'][] = $field;
				}
			}
		}

		if ( isset( $_POST['jmb_pf_filters'] ) ) {
			foreach ( $_POST['jmb_pf_filters'] as $filter ) {
				if ( ! empty( $filter['field'] ) && empty( $filter['delete'] ) ) {
					$save_data['jmb_pf_filters'][] = $filter;
				}
			}
		}

		if ( ! empty( $_POST['jmb_pf_info_default_values'] ) ) {
			$save_data['jmb_pf_site_title']       = esc_attr( get_option( 'blogname' ) );
			$save_data['jmb_pf_site_url']         = esc_url( get_option( 'siteurl' ) );
			$save_data['jmb_pf_site_description'] = esc_attr( get_option( 'blogdescription' ) );
		} else {
			$save_data['jmb_pf_site_title']       = ( isset( $_POST['jmb_pf_site_title'] ) ? esc_attr( $_POST['jmb_pf_site_title'] ) : '' );
			$save_data['jmb_pf_site_url']         = ( isset( $_POST['jmb_pf_site_url'] ) ? esc_attr( $_POST['jmb_pf_site_url'] ) : '' );
			$save_data['jmb_pf_site_description'] = ( isset( $_POST['jmb_pf_site_description'] ) ? esc_attr( $_POST['jmb_pf_site_description'] ) : '' );
		}

		$save_data['jmb_pf_orderby'] = ( isset( $_POST['jmb_pf_orderby'] ) ? esc_attr( $_POST['jmb_pf_orderby'] ) : '' );
		$save_data['jmb_pf_order']   = ( isset( $_POST['jmb_pf_order'] )   ? esc_attr( $_POST['jmb_pf_order'] )   : '' );
		$save_data['jmb_pf_limit']   = ( isset( $_POST['jmb_pf_limit'] )   ? (int) $_POST['jmb_pf_limit']         : 0 );
		$save_data['jmb_pf_offset']  = ( isset( $_POST['jmb_pf_offset'] )  ? (int) $_POST['jmb_pf_offset']        : 0 );

		$save_data['jmb_pf_export_filename'] = ( isset( $_POST['jmb_pf_export_filename'] ) ? esc_attr( $_POST['jmb_pf_export_filename'] ) : '' );
		$save_data['jmb_pf_export_formats']  = ( isset( $_POST['jmb_pf_export_formats'] ) ? $_POST['jmb_pf_export_formats'] : array() );
		$save_data['jmb_pf_auto_update']     = ( ! empty( $_POST['jmb_pf_auto_update'] ) ? 1 : 0 );

		// No export filename specified - generate one from the post title.
		if ( empty( $save_data['jmb_pf_export_filename'] ) ) {
			$save_data['jmb_pf_export_filename'] = str_replace( ' ', '-', strtolower( $post->post_title ) );
		}

		// Is the export filename already in use by another feed?
		$file_name_post_count = $this->get_posts( array(
			'count'   => true,
			'filters' => array(
				array(
					'field'     => 'post:post_type',
					'operation' => '=',
					'value'     => 'jmb_post_feed',
				),
				array(
					'field'     => 'post:id',
					'operation' => '!=',
					'value'     => $post_id,
				),
				array(
					'field'     => 'meta:jmb_pf_export_filename',
					'operation' => '=',
					'value'     => $save_data['jmb_pf_export_filename'],
				),
			),
		) );

		if ( $file_name_post_count ) {
			$save_data['jmb_pf_export_filename'] .= '-' . $file_name_post_count;
		}

		$save_data['jmb_pf_post_types'] = ( isset( $_POST['jmb_pf_post_types'] ) ? $_POST['jmb_pf_post_types'] : array() );

		foreach ( $save_data as $data_key => $data_value ) {
			update_post_meta( $post_id, $data_key, $data_value );
		}

		$this->generate_feed_files( $post_id );
	}

	/**
	 * Display the Fields meta box.
	 *
	 * @return null
	 */
	function fields() {
		global $post;

		$td = $this->text_domain;

		$field_value_options = $this->get_field_value_options();

		$fields = get_post_meta( $post->ID, 'jmb_pf_fields', true );

		if ( ! is_array( $fields ) ) {
			$fields = array();
		}

		wp_nonce_field( 'jmb-pf-save-meta-box-data', '_jmb_pf_nonce' );

		include( $this->dir_path . 'views/meta-box-fields.php' );
	}

	/**
	 * Display the Filters meta box.
	 *
	 * @return null
	 */
	function filters() {
		global $post;

		$td = $this->text_domain;

		$field_value_options = $this->get_field_value_options();

		unset( $field_value_options['general'] );
		unset( $field_value_options['post']['post:post_type'] );
		unset( $field_value_options['post']['post:permalink'] );
		unset( $field_value_options['post']['post:thumbnail'] );

		$operations = $this->filter_operations;

		$filters = get_post_meta( $post->ID, 'jmb_pf_filters', true );

		if ( ! is_array( $filters ) ) {
			$filters = array();
		}

		include( $this->dir_path . 'views/meta-box-filters.php' );
	}

	/**
	 * Display the Additional Info meta box.
	 *
	 * @return null
	 */
	function info() {
		global $post;

		$td = $this->text_domain;

		$default_site_title       = esc_attr( get_option( 'blogname' ) );
		$default_site_url         = esc_url( get_option( 'siteurl' ) );
		$default_site_description = esc_attr( get_option( 'blogdescription' ) );

		$site_title       = esc_attr( get_post_meta( $post->ID, 'jmb_pf_site_title', true ) );
		$site_url         = esc_attr( get_post_meta( $post->ID, 'jmb_pf_site_url', true ) );
		$site_description = esc_attr( get_post_meta( $post->ID, 'jmb_pf_site_description', true ) );

		include( $this->dir_path . 'views/meta-box-info.php' );
	}

	/**
	 * Display the Refine Search meta box.
	 *
	 * @return null
	 */
	function refine_search() {
		global $post;

		$td = $this->text_domain;

		$sorting_options = $this->sorting_options;

		$orderby = get_post_meta( $post->ID, 'jmb_pf_orderby', true );
		$order   = get_post_meta( $post->ID, 'jmb_pf_order', true );
		$limit   = (int) get_post_meta( $post->ID, 'jmb_pf_limit', true );
		$offset  = (int) get_post_meta( $post->ID, 'jmb_pf_offset', true );

		include( $this->dir_path . 'views/meta-box-refine-search.php' );
	}

	/**
	 * Display the Export Options meta box.
	 *
	 * @return null
	 */
	function export_options() {
		global $post;

		$td = $this->text_domain;

		$_export_formats = $this->export_formats;

		$export_filename = esc_attr( get_post_meta( $post->ID, 'jmb_pf_export_filename', true ) );
		$export_formats  = get_post_meta( $post->ID, 'jmb_pf_export_formats', true );
		$auto_update     = get_post_meta( $post->ID, 'jmb_pf_auto_update', true );

		if ( ! is_array( $export_formats ) ) {
			$export_formats = array();
		}

		include( $this->dir_path . 'views/meta-box-export-options.php' );
	}

	/**
	 * Display the Feeds meta box.
	 *
	 * @return null
	 */
	function feeds() {
		global $post;

		$td = $this->text_domain;

		$_export_formats = $this->export_formats;

		$post_feed_id = $post->ID;

		$feeds       = get_post_meta( $post->ID, 'jmb_pf_feeds', true );
		$last_update = get_post_meta( $post->ID, 'jmb_pf_last_update', true );

		if ( $last_update ) {
			$last_update = date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $last_update );
		}

		include( $this->dir_path . 'views/meta-box-feeds.php' );
	}

	/**
	 * Display the Post Types meta box.
	 *
	 * @return null
	 */
	function post_types() {
		global $post;

		$_post_types = get_post_types();

		unset( $_post_types['jmb_post_feed'] );

		$post_types = get_post_meta( $post->ID, 'jmb_pf_post_types', true );

		if ( ! is_array( $post_types ) ) {
			$post_types = array();
		}

		include( $this->dir_path . 'views/meta-box-post-types.php' );
	}

}
