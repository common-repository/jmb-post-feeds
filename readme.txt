=== Plugin Name ===
Contributors: jmb272
Tags: xml, rss, csv, post feed, feed
Requires at least: 4.0
Tested up to: 4.8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

=== Description ===

Create post feeds in CSV, XML, RSS, Google RSS, Text & Custom formats.

Features:

- Add your own custom format using the 'jmb_pf_plugin_settings' filter.
- Filter & Refine search options to restrict the posts that appear in a feed.
- Choose the post types to include in the feed(s).
- Choose what formats to export a feed to.
- Set a feed to update automatically whenever a post matching the feed's criteria is updated.

=== Installation ===

1. Upload the plugin to the '/wp-content/plugins' directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

=== Frequently Asked Questions ===

= How do I create my own export format? =

First, register your Export Format with the plugin like so:

<?php
add_filter( 'jmb_pf_plugin_settings', function( $settings ) {
	$settings['export_formats']['text_2'] = array(
		'name'     => 'Text 2',
		'ext'      => '-text-2.txt',
		'callback' => 'jmb_pf_text_2_format',
	);

	return $settings;
});
?>

Then create the Export Function that takes the feed data and exports it to a file, like so:

<?php
function jmb_pf_text_2_format( $args = array() ) {
	$a = array_merge( array( 
		'file_name' => '',
		'posts'     => array(),
	), $args );

	if ( empty( $a['posts'] ) ) {
		return;
	}

	$text_content = '';

	foreach ( $a['posts'] as $post ) {
		if ( ! empty( $text_content ) ) {
			$text_content .= "\n";
		}

		foreach ( $post as $data_name => $data_value ) {
			$text_content .= sprintf( "%s ", $data_value );
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
?>
