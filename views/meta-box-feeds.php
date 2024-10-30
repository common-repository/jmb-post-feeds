<?php
/**
 * JMB Post Feeds
 *
 * Feeds meta box view file.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( $last_update ) {
	?>
	<h4><strong style="font-weight: bold;"><?php _e( 'Last Updated:', $td ); ?></strong> <?php echo $last_update; ?></h4>
	<?php
}

?>
<table id="jmb-pf-feeds-table" class="jmb-pf-list-table">
	<thead>
		<tr>
			<th><?php _e( 'Format', $td ); ?></th>
			<th><?php _e( 'Name', $td ); ?></th>
			<th><?php _e( 'Filesize', $td ); ?></th>
			<th><?php _e( 'Path', $td ); ?></th>
			<th><?php _e( 'URL', $td ); ?></th>
			<th><?php _e( 'Action', $td ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php if ( empty( $feeds ) ) { ?>

			<tr>
				<td colspan="5">
					<p><?php _e( 'No feeds have been generated yet.', $td ); ?></p>
				</td>
			</tr>

		<?php } else { ?>

			<?php foreach ( $feeds as $feed_format => $feed ) { ?>
				<tr>
					<td>
						<?php if ( isset( $_export_formats[ $feed_format ] ) ) { ?>
							<strong><?php echo $_export_formats[ $feed_format ]['name']; ?></strong>
						<?php } ?>
					</td>

					<td><?php echo $feed['name']; ?></td>

					<td>
						<?php 
						if ( isset( $feed['filesize'] ) ) {
							echo number_format( $feed['filesize'] / 1024 , 2 ) . ' kb';
						}
						?>
					</td>

					<td><input type="text" value="<?php echo esc_attr( $feed['path'] ); ?>" readonly /></td>

					<td><input type="text" value="<?php echo $feed['url']; ?>" readonly /></td>

					<td>
						<a href="<?php echo $feed['url']; ?>" target="_blank" title="<?php _e( 'View', $td ); ?>" class="button button-default jmb-pf-btn-icon jmb-pf-btn-icon-view"></a>

						<a href="<?php echo site_url(); ?>/?jmb_pf_download=<?php echo $post_feed_id; ?>_<?php echo $feed_format; ?>" class="button button-default jmb-pf-btn-icon jmb-pf-btn-icon-download" title="<?php _e( 'Download', $td ); ?>"></a> 
					</td>
				</tr>
			<?php } ?>
			
		<?php } ?>
	</tbody>
</table>
