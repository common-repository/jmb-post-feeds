<?php
/**
 * JMB Post Feeds
 *
 * Export Options meta box view file.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?><p>
	<label>
		<strong><?php _e( 'Formats:', $td ); ?></strong>
	</label>
	<br />

	<?php foreach ( $_export_formats as $format_id => $format ) { ?>
		<?php $checked = ( in_array( $format_id, $export_formats ) ? ' checked' : '' ); ?>

		<label>
			<input type="checkbox" name="jmb_pf_export_formats[]" value="<?php echo $format_id; ?>"<?php echo $checked; ?> /><?php echo $format['name']; ?>
		</label><br />
	<?php } ?>
</p>

<p>
	<label for="jmb-pf-export-filename">
		<strong><?php _e( 'Filename:', $td ); ?></strong>
	</label><br />

	<input type="text" name="jmb_pf_export_filename" id="jmb-pf-export-filename" value="<?php echo $export_filename; ?>" />
</p>

<p>
	<label>
		<input type="checkbox" name="jmb_pf_auto_update" value="1"<?php echo ( $auto_update ? ' checked' : '' ); ?> /> <?php _e( 'Update the feed automatically.', $td ); ?>
	</label>
</p>
