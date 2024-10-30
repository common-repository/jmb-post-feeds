<?php
/**
 * JMB Post Feeds
 *
 * Exporter class - the methods in here are responsible for exporting feed data to a particular format.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class JMB_Post_Feeds_Exporter {
	/**
	 * CSV format.
	 *
	 * @param array $args
	 * @return bool|string
	 */
	function csv( $args = array() ) {
		$a = array_merge( array( 
			'file_name' => '',
			'posts'     => '',
			'separator' => ',',
			'enclosure' => '"',
		), $args );

		if ( empty( $a['posts'] ) || empty( $a['separator'] ) ) {
			return false;
		}

		$columns = str_replace( array( '_', '-' ), ' ', array_keys( $a['posts'][0] ) );

		$rows = array();

		$rows[] = $columns;

		$i = 1;

		foreach ( $a['posts'] as $post ) {
			$rows[ $i ] = array();

			foreach ( $post as $post_field => $post_value ) {
				$rows[ $i ][ $post_field ] = $post_value;
			}

			$i++;
		}

		// Generate the CSV content.

		ob_start();

		$fp = fopen( 'php://output', 'w' );

		foreach ( $rows as $row ) {
			fputcsv( $fp, $row, $a['separator'], $a['enclosure'] );
		}

		fclose( $fp );

		$csv_content = ob_get_contents();

		ob_end_clean();

		// Save the CSV content to a file or return it.

		if ( ! empty( $a['file_name'] ) ) {
			$fp = fopen( $a['file_name'], 'w' );
			fputs( $fp, $csv_content );
			fclose( $fp );

			return true;
		}

		return $csv_content;
	}

	/**
	 * XML format.
	 *
	 * @param array $args
	 * @return bool|string
	 */
	function xml( $args = array() ) {
		$a = array_merge( array( 
			'file_name' => '',
			'posts'     => array(),
		), $args );

		if ( empty( $a['posts'] ) || ! class_exists( 'SimpleXMLElement' ) ) {
			return false;
		}

		$_xml = new SimpleXMLElement( '<channel/>' );

		$_posts = $_xml->addChild( 'posts' );

		$_fields = str_replace( ' ', '_', array_map( 'strtolower', array_keys( $a['posts'][0] ) ) );

		foreach ( $a['posts'] as $post ) {
			$_post = $_posts->addChild( 'post' );

			foreach ( $_fields as $field ) {
				$value = ( isset( $post[ $field ] ) ? $post[ $field ] : '' );

				$_post->addChild( $field, htmlspecialchars( $value ) );
			}
		}

		$xml_content = $_xml->asXML();

		if ( ! empty( $a['file_name'] ) ) {
			$fp = fopen( $a['file_name'], 'w' );
			fputs( $fp, $xml_content );
			fclose( $fp );

			return true;
		}

		return $xml_content;
	}

	/**
	 * Plain text format.
	 *
	 * @param array $args
	 * @return bool|string
	 */
	function text( $args = array() ) {
		$a = array_merge( array( 
			'file_name' => '',
			'posts'     => array(),
		), $args );

		if ( empty( $a['posts'] ) ) {
			return false;
		}

		$text_content = '';

		foreach ( $a['posts'] as $post ) {
			if ( ! empty( $text_content ) ) {
				$text_content .= "\n";
			}

			foreach ( $post as $data_name => $data_value ) {
				$text_content .= sprintf( "%s: %s\n", $data_name, $data_value );
			}
		}
		
		if ( ! empty( $a['file_name'] ) ) {
			$fp = fopen( $a['file_name'], 'w' );
			fputs( $fp, $text_content );
			fclose( $fp );

			return true;
		}

		return $text_content;
	}

	/**
	 * RSS format.
	 *
	 * @param array $args
	 * @return bool|string
	 */
	function rss( $args = array() ) {
		$a = array_merge( array( 
			'file_name'    => '',
			'posts'        => array(),
			'post_feed_id' => false,
		), $args );

		if ( empty( $a['posts'] ) || ! $a['post_feed_id'] || ! class_exists( 'SimpleXMLElement' ) ) {
			return false;
		}

		$post_feed = get_post( $a['post_feed_id'] );

		if ( ! $post_feed ) {
			return false;
		}

		$site_title       = get_post_meta( $post_feed->ID, 'jmb_pf_site_title', true );
		$site_url         = get_post_meta( $post_feed->ID, 'jmb_pf_site_url', true );
		$site_description = get_post_meta( $post_feed->ID, 'jmb_pf_site_description', true );

		if ( empty( $site_title ) || empty( $site_url ) || empty( $site_description ) ) {
			return false;
		}

		$_fields = str_replace( ' ', '_', array_map( 'strtolower', array_keys( $a['posts'][0] ) ) );

		$_xml = new SimpleXMLElement('<rss/>');
		$_xml->addAttribute( 'version', '2.0' );

		$_channel = $_xml->addChild( 'channel' );

		$_channel->addChild( 'title', esc_attr( $site_title ) );
		$_channel->addChild( 'link', esc_url( $site_url ) );
		$_channel->addChild( 'description', esc_attr( strip_tags( $site_description ) ) );

		foreach ( $a['posts'] as $post ) {
			$_item = $_channel->addChild( 'item' );

			foreach ( $_fields as $field ) {
				$value = ( isset( $post[ $field ] ) ? $post[ $field ] : '' );

				$_item->addChild( $field, htmlspecialchars( $value ) );
			}
		}

		$xml_content = $_xml->asXML();

		if ( ! empty( $a['file_name'] ) ) {
			$fp = fopen( $a['file_name'], 'w' );
			fputs( $fp, $xml_content );
			fclose( $fp );

			return true;
		}

		return $xml_content;
	}

	/**
	 * Google RSS format.
	 *
	 * @param array $args
	 * @return bool|string
	 */
	function google_rss( $args = array() ) {
		$a = array_merge( array( 
			'file_name'    => '',
			'posts'        => array(),
			'post_feed_id' => false,
		), $args );

		if ( empty( $a['posts'] ) || ! $a['post_feed_id'] || ! class_exists( 'SimpleXMLElement' ) ) {
			return false;
		}

		$post_feed = get_post( $a['post_feed_id'] );

		if ( ! $post_feed ) {
			return false;
		}

		$site_title       = get_post_meta( $post_feed->ID, 'jmb_pf_site_title', true );
		$site_url         = get_post_meta( $post_feed->ID, 'jmb_pf_site_url', true );
		$site_description = get_post_meta( $post_feed->ID, 'jmb_pf_site_description', true );

		if ( empty( $site_title ) || empty( $site_url ) || empty( $site_description ) ) {
			return false;
		}

		$_fields = str_replace( ' ', '_', array_map( 'strtolower', array_keys( $a['posts'][0] ) ) );

		$_xml = new SimpleXMLElement( '<rss/>' );
		$_xml->addAttribute( 'xmlns:xmlns:g', 'http://base.google.com/ns/1.0' );
		$_xml->addAttribute( 'version', '2.0' );

		$_channel = $_xml->addChild( 'channel' );

		$_channel->addChild( 'title', esc_attr( $site_title ) );
		$_channel->addChild( 'link', esc_url( $site_url ) );
		$_channel->addChild( 'description', esc_attr( strip_tags( $site_description ) ) );

		foreach ( $a['posts'] as $post ) {
			$_item = $_channel->addChild( 'item' );

			foreach ( $_fields as $field ) {
				$value = ( isset( $post[ $field ] ) ? $post[ $field ] : '' );

				$_item->addChild( 'g:g:' . $field, htmlspecialchars( $value ) );
			}
		}

		$xml_content = $_xml->asXML();

		if ( ! empty( $a['file_name'] ) ) {
			$fp = fopen( $a['file_name'], 'w' );
			fputs( $fp, $xml_content );
			fclose( $fp );

			return true;
		}

		return $xml_content;
	}

}
