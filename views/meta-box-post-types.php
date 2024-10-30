<?php
/**
 * JMB Post Feeds
 *
 * Post Types meta box view file.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

foreach ( $_post_types as $post_type_id => $post_type_name ) {
	$checked = ( in_array( $post_type_id, $post_types ) ? ' checked' : '' );
	?>
	<label>
		<input type="checkbox" name="jmb_pf_post_types[]" value="<?php echo $post_type_id; ?>"<?php echo $checked; ?> /> <?php echo $post_type_name; ?>
	</label><br />
	<?php
}
