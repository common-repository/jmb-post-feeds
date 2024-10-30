<?php
/**
 * JMB Post Feeds
 *
 * Functions
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class JMB_Post_Feeds_Functions {
	/**
	 * Plugin settings.
	 *
	 * @var array
	 */
	private $config;

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
	 * Constructor
	 */
	function __construct( $config ) {
		$this->config = $config;
	}

	/**
	 * Output data to a file - useful for logging while developing & debugging.
	 *
	 * @param mixed $data 
	 * @param array $args
	 * @return bool
	 */
	function write_log( $data, $args = array() ) {
		if ( ! file_exists( $this->log_file ) || ! is_file( $this->log_file ) ) {
			if ( ! @touch( $this->log_file ) ) {
				return false;
			}
		} 

		$a = array_merge( array(
			'file' => $this->log_file,
			'mode' => 'w'
		), $args );

		if ( empty( $a['file'] ) || ( ! file_exists( $a['file'] ) ) ){
			return false;
		}

		$fp = fopen( $a['file'], $a['mode'] );

		if ( ! $fp ) {
			return false;
		}

		if ( is_array( $data ) ) {
			fputs( $fp, print_r( $data, true ) );
		} else {
			fputs( $fp, $data );
		}

		fclose( $fp );

		return true;
	}

	/**
	 * Returns an array of options for the Fields table value field.
	 *
	 * @return array
	 */
	function get_field_value_options() {
		global $wpdb;

		$td = $this->text_domain;

		$options = array(
			'general' => array(
				'empty'  => esc_attr__( 'Empty', $td ),
				'custom' => esc_attr__( 'Custom', $td ),
			),
			'post' => array(
				'id'            => esc_attr__( 'ID', $td ),
				'post_title'    => esc_attr__( 'Title', $td ),
				'post_content'  => esc_attr__( 'Content', $td ),
				'post_excerpt'  => esc_attr__( 'Excerpt', $td ),
				'post_type'     => esc_attr__( 'Post Type', $td ),
				'post_status'   => esc_attr__( 'Post Status', $td ),
				'permalink'     => esc_attr__( 'Permalink', $td ),
				'thumbnail'     => esc_attr__( 'Thumbnail', $td ),
			),
		);

		$sql  = 'SELECT DISTINCT meta_key FROM ' . $wpdb->postmeta;
		$sql .= ' WHERE meta_key NOT LIKE \'\_%\'';
		$sql .= ' AND meta_key NOT LIKE \'jmb\_pf\_%\'';
		$sql .= ' ORDER BY meta_key DESC';

		$meta_keys = $wpdb->get_col( $sql );

		if ( $meta_keys ) {
			$options['meta'] = array();

			foreach ( $meta_keys as $meta_key ) {
				$options['meta'][$meta_key] = $meta_key;
			}
		}

		foreach ( $options as $option_group => $group_options ) {
			foreach ( $group_options as $option_value => $option_name ) {
				unset( $group_options[$option_value] );

				$group_options[$option_group . ':' . $option_value] = $option_name;
			}

			$options[$option_group] = $group_options;
		}

		return $options;
	}

	/**
	 * Custom posts query function.
	 *
	 * @param array $args
	 * @return mixed
	 */
	function get_posts( $args = array() ) {
        $a = array_merge( array(
            'fields'  => 'p.*',
            'filters' => array(),
            'orderby' => 'p.post_date',
            'order'   => 'DESC',
            'limit'   => 10,
            'offset'  => 0,
			'count'   => false,
        ), $args );

        global $wpdb;

        $order = '';

        if ( ! empty( $a['orderby'] ) ) {
            $order = ' ORDER BY ' . $a['orderby'] . ' ' . ( strtoupper( $a['order'] ) != 'ASC' ? 'DESC' : 'ASC' );
        }

        $limit  = ( $a['limit']  ? ' LIMIT ' . (int)$a['limit']   : '' );
        $offset = ( $a['offset'] ? ' OFFSET ' . (int)$a['offset'] : '' );

        $fields = 'p.*';

        $return_column = false;

        //-----

        if ( ! empty( $a['fields'] ) ) {
            if ( is_array( $a['fields'] ) ) {
                $fields = '';

                foreach ( $a['fields'] as $field ) {
                    $field_parts = explode( ':', $field );

                    if ( ! empty( $fields ) ) {
						$fields .= ', ';
					}

                    if ( count( $field_parts ) > 1 ) {
                        switch ( $field_parts[0] ) {
                            case 'meta':
                                $meta_key = $field_parts[1];
                                $fields .= sprintf( "(SELECT meta_value FROM $wpdb->postmeta WHERE post_id = p.ID AND meta_key = '%s') AS %s", $meta_key, $meta_key );
							break;
                            case 'post':
                                $fields .= $field_parts[1];
							break;
                            default:
                                $fields .= $field;
                        }
                    } else {
                        $fields .= $field;
                    }
                }
            } else {
                if ( 'ids' == $a['fields'] ) {
                    $fields        = 'p.ID';
                    $return_column = true;
                } else {
                    $a['fields'] = array_map( 'trim', explode( ',', $a['fields'] ) );

                    return $this->get_posts( $a );
                }
            }
        }

        //-----

        $where = '';

        if ( ! empty( $a['filters'] ) && is_array( $a['filters'] ) ) {
            foreach ( $a['filters'] as $filter ) {
                $field_parts = explode( ':', $filter['field'] );

                if ( count( $field_parts ) > 1 ) {
                    switch ( $field_parts[0] ) {
                        case 'meta':
                            $meta_key = $field_parts[1];
                            $filter['field'] = sprintf( "(SELECT meta_value FROM $wpdb->postmeta WHERE post_id = p.ID AND meta_key = '%s')", $meta_key );
						break;
                        case 'post':
                            $filter['field'] = 'p.'.$field_parts[1];
						break;
                    }
                }

                switch ( strtolower( $filter['operation'] ) ) {
                    case '>=':
                    case '<=':
                    case '>':
                    case '<':
                    case '=':
                    case '!=':
                        $where .= ( ! $where ? ' WHERE' : ' AND' );
                        $where .= ' ' . sprintf( "%s " . $filter['operation'] . " '%s'", $filter['field'], trim( esc_attr( $filter['value'] ) ) );
					break;
                    case 'not in':
                    case 'in':
                        $where .= ( ! $where ? ' WHERE' : ' AND' );

                        if ( ! is_array( $filter['value'] ) ) {
                            $filter['value'] = array_map( 'trim', array_map( 'esc_attr', explode( ',', $filter['value'] ) ) );
                        }

                        $where .= ' ' . sprintf( '%s', $filter['field'] ) . ' ' . strtoupper( $filter['operation'] ) . " ('" . implode( "','", $filter['value'] ) . "')";
					break;
                    case 'not like':
                    case 'like':
                        $where .= ( ! $where ? ' WHERE' : ' AND' );
                        $where .= ' ' . sprintf( "%s %s '%s'", $filter['field'], strtoupper( $filter['operation']), trim( html_entity_decode( esc_attr( $filter['value'] ) ) ) );
					break;
                    case 'empty':
                        $where .= ( ! $where ? ' WHERE' : ' AND' );
                        $where .= ' ' . sprintf( "%s IS NULL OR %s = ''", $filter['field'], $filter['field'] );
					break;
                    case 'not empty':
                        $where .= ( ! $where ? ' WHERE' : ' AND' );
                        $where .= ' ' . sprintf( "%s IS NOT NULL AND %s != ''", $filter['field'], $filter['field'] );
					break;
                }
            }
        }

        //-----

		if ( $a['count'] ) {
			$fields = 'COUNT(*)';
		}

		$sql = "SELECT $fields FROM $wpdb->posts p" . $where . $order . $limit . $offset;

		if ( $a['count'] ) {
			$results = (int) $wpdb->get_var( $sql );
		} elseif ( $return_column ) {
            $results = $wpdb->get_col( $sql );
        } else {
            $results = $wpdb->get_results( $sql, OBJECT );
        }

        return $results;
	}

	/**
	 * Get the ID's of posts matching a post feed's criteria.
	 *
	 * @param int $post_feed_id
	 * @return array|bool An array of Post ID's or FALSE on failure.
	 */
	function get_feed_post_ids( $post_feed_id ) {
		$post = get_post( $post_feed_id );

		if ( ! $post || ( 'jmb_post_feed' != $post->post_type ) ) {
			return false;
		}

		$filters = get_post_meta( $post->ID, 'jmb_pf_filters', true );
		$orderby = get_post_meta( $post->ID, 'jmb_pf_orderby', true );
		$order   = get_post_meta( $post->ID, 'jmb_pf_order', true );
		$limit   = get_post_meta( $post->ID, 'jmb_pf_limit', true );
		$offset  = get_post_meta( $post->ID, 'jmb_pf_offset', true );

		if ( ! is_array( $filters ) ) {
			$filters = array();
		}

		$post_types = get_post_meta( $post->ID, 'jmb_pf_post_types', true );

		if ( is_array( $post_types ) && ! empty( $post_types ) ) {
			$filters[] = array(
				'field'     => 'post:post_type',
				'operation' => 'in',
				'value'     => implode( ',', $post_types )
			);
		}

		$post_ids = $this->get_posts( array( 
			'fields'  => 'ids',
			'filters' => $filters,
			'orderby' => $orderby,
			'order'   => $order,
			'limit'   => $limit,
			'offset'  => $offset,
		) );

		return $post_ids;
	}

	/**
	 * Gather the post feed data into an array ready for export.
	 *
	 * @param int $post_feed_id
	 * @return array|bool An array of post data or FALSE on failure.
	 */
	function get_feed_post_data( $post_feed_id ) {
		if ( ! $post_ids = $this->get_feed_post_ids( $post_feed_id ) ) {
			return false;
		}

		if ( ! $fields = get_post_meta( $post_feed_id, 'jmb_pf_fields', true ) ) {
			return false;
		}

		$posts = array();

		foreach ( $post_ids as $post_id ) {
			$post = get_post( $post_id );

			if ( ! $post ) {
				continue;
			}

			$_post = array();

			foreach ( $fields as $field ) {
				$value_parts = explode( ':', $field['value'] );

				$field['name'] = strtolower( $field['name'] );

				switch ( $value_parts[0] ) {
					case 'general':
						switch ( $value_parts[1] ) {
							case 'empty':
								$_post[ $field['name'] ] = '';
							break;
							case 'custom':
								$value = '';

								if ( isset( $field['custom_value'] ) ) {
									$value = $field['custom_value'];

									preg_match_all('/{([a-zA-Z0-9:_]+)}/', $value, $tags);

									if ( isset( $tags[1] ) ) {
										foreach ( $tags[1] as $tag ) {
											$tag_parts = explode( ':', $tag );

											if ( count($tag_parts) > 1 ) {
												$tag_type  = $tag_parts[0];
												$tag_value = $tag_parts[1];

												switch ( $tag_type ) {
													case 'meta':
														$meta_value = get_post_meta( $post->ID, $tag_value, true );
														$value      = str_replace( '{'.$tag.'}', $meta_value, $value );
													break;
													case 'option':
														$option_value = get_option( $tag_value );
														$value        = str_replace( '{'.$tag.'}', $option_value, $value );
													break;
													case 'post':
														$post_value = '';

														switch ( $tag_value ) {
															case 'id': 
																$post_value = $post->ID;
															break;
															case 'permalink': 
																$post_value = get_permalink( $post_id );
															break;
															case 'thumbnail': 
																$post_value = get_the_post_thumbnail_url( $post_id, array( 300, 300 ) );
															break;
															default:
																$post_value = ( property_exists( $post, $tag_value ) ? $post->$tag_value : '' );
														}

														$value = str_replace('{'.$tag.'}', $post_value, $value);
													break;
												}
											}
										}
									}
								}

								$_post[ $field['name'] ] = $this->filter_feed_data( $value );
							break;
						}
					break;
					case 'meta':
						$_post[ $field['name'] ] = $this->filter_feed_data( get_post_meta( $post->ID, $value_parts[1], true ) );
					break;
					case 'post':
						switch ( $value_parts[1] ) {
							case 'id':
								$_post[ $field['name'] ] = $post->ID;
							break;
							case 'permalink':
								$_post[ $field['name'] ] = get_permalink( $post->ID );
							break;
							case 'thumbnail':
								$_post[ $field['name'] ] = get_the_post_thumbnail_url( $post->ID, array( 300, 300 ) );
							break;
							default:
								$_post[ $field['name'] ] = ( property_exists( $post, $value_parts[1] ) ? $this->filter_feed_data( $post->$value_parts[1] ) : '' );
						}
					break;
				}

				if ( ! empty( $field['max_length'] ) ) {
					$_post[ $field['name'] ] = substr( $_post[ $field['name'] ], 0, (int) $field['max_length'] );
				}
			}

			if ( $_post ) {
				$posts[] = $_post;
			}
		}

		return $posts;
	}

	/**
	 * Filter the feed post data.
	 *
	 * @param string $value
	 * @return string
	 */
	function filter_feed_data( $value ) {
        $value = str_replace("&nbsp;", ' ', $value);
        $value = str_replace(array("\r\n\r\n", "\r\n"), "\n", $value);
        $value = str_replace(array("\t", "\n"), ' ', strip_tags($value));
    	$value = str_replace(array('instock', 'outofstock'), array('in stock', 'out of stock'), $value);

        return $value;
	}

	/**
	 * Generate the feed files.
	 *
	 * @param int $post_feed_id
	 * @return bool
	 */
	function generate_feed_files( $post_feed_id ) {
		$post_feed = get_post( $post_feed_id );

		if ( ! $post_feed ) {
			return false;
		}

		$feed_post_data = $this->get_feed_post_data( $post_feed_id );

		if ( ! $feed_post_data ) {
			return false;
		}
		
		// Delete the current feed files.

		$current_feeds = get_post_meta( $post_feed_id, 'jmb_pf_feeds', true );

		if ( is_array( $current_feeds ) ) {
			foreach ( $current_feeds as $current_feed ) {
				if ( file_exists( $current_feed['path'] ) ) {
					@unlink( $current_feed['path'] );
				}
			}
		}

		// Generate the new feed files.

		$exporter = new JMB_Post_Feeds_Exporter;

		$export_filename = get_post_meta( $post_feed_id, 'jmb_pf_export_filename', true );
		$export_formats  = get_post_meta( $post_feed_id, 'jmb_pf_export_formats', true );

		// No export filename specified - generate one from the post title.
		if ( empty( $export_filename ) ) {
			$export_filename = str_replace( ' ', '-', strtolower( $post->post_title ) );

			update_post_meta( $post_feed_id, 'jmb_pf_export_filename', $export_filename );
		}

		$feeds = array();

		if ( ! file_exists( $this->feed_dir_path ) || ! is_dir( $this->feed_dir_path ) ) {
			@mkdir( $this->feed_dir_path );
		}

		foreach ( $export_formats as $export_format ) {
			if ( ! $_export_format = ( isset( $this->export_formats[ $export_format ] ) ? $this->export_formats[ $export_format ] : false ) ) {
				continue;
			}

			if ( empty( $_export_format['callback'] ) || ! is_callable( $_export_format['callback'] ) ) {
				continue;
			}

			$file_name = $export_filename . $_export_format['ext'];
			$file_path = $this->feed_dir_path . $file_name;
			$file_url  = $this->feed_dir_url . $file_name;

			$result = call_user_func( $_export_format['callback'], array( 
				'file_name'    => $file_path,
				'posts'        => $feed_post_data,
				'post_feed_id' => $post_feed_id,
			) );

			if ( $result ) {
				$feeds[ $export_format ] = array(
					'name'     => $file_name,
					'path'     => $file_path,
					'url'      => $file_url,
					'filesize' => filesize( $file_path ),
				);
			}
		}

		update_post_meta( $post_feed_id, 'jmb_pf_feeds', $feeds );
		update_post_meta( $post_feed_id, 'jmb_pf_last_update', time() );

		return true;
	}

}
