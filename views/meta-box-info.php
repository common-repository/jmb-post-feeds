<?php
/**
 * JMB Post Feeds
 *
 * Additional Info meta box view file.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<p>
	<label for="jmb-pf-site-title">
		<strong><?php _e( 'Site Title', $td ); ?></strong>
	</label><br />

	<input type="text" name="jmb_pf_site_title" id="jmb-pf-site-title" value="<?php echo $site_title; ?>" />
</p>

<p>
	<label for="jmb-pf-site-url">
		<strong><?php _e( 'Site URL', $td ); ?></strong>
	</label><br />

	<input type="text" name="jmb_pf_site_url" id="jmb-pf-site-url" value="<?php echo $site_url; ?>" />
</p>

<p>
	<label for="jmb-pf-site-description">
		<strong><?php _e( 'Site Description', $td ); ?></strong>
	</label><br />

	<textarea name="jmb_pf_site_description" id="jmb-pf-site-description" rows="6" cols="80" style="max-width: 100%;"><?php echo $site_description; ?></textarea>
</p>

<div class="hide-if-no-js">
	<button type="button" id="jmb-pf-info-default-values" class="button button-primary"><?php _e( 'Use Default Values', $td ); ?></button>
</div>
<div class="hide-if-js">
	<label>
		<input type="checkbox" name="jmb_pf_info_default_values" value="1" /> <?php _e( 'Use Default Values', $td ); ?>
	</label>
</div>

<script>
(function($) {
	$(function() {
		$('#jmb-pf-info-default-values').on('click', function() {
			$('input[name="jmb_pf_site_title"]').val('<?php echo $default_site_title; ?>');
			$('input[name="jmb_pf_site_url"]').val('<?php echo $default_site_url; ?>');
			$('textarea[name="jmb_pf_site_description"]').val('<?php echo $default_site_description; ?>');
		});
	});
})(jQuery);
</script>
