<?php
/**
 * JMB Post Feeds
 *
 * Refine Search meta box view file.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<p>
	<label for="jmb-pf-sort-by">
		<strong><?php _e( 'Sort By', $td ); ?></strong>
	</label><br />

	<select name="jmb_pf_orderby" id="jmb-pf-sort-by">
		<option value=""><?php _e( 'Default', $td ); ?></option>

		<?php foreach ( $sorting_options as $sort_value => $sort_name ) { ?>

			<?php $selected = ( $sort_value == $orderby ? ' selected' : '' ); ?>

			<option value="<?php echo $sort_value; ?>"<?php echo $selected; ?>><?php echo $sort_name; ?></option>

		<?php } ?>
	</select>
</p>

<p>
	<label for="jmb-pf-order">
		<strong><?php _e( 'Order', $td ); ?></strong>
	</label><br />

	<label>
		<input type="radio" name="jmb_pf_order" id="jmb-pf-order" value="asc"<?php echo ( 'asc' == $order ? ' checked' : '' ); ?> /><?php _e( 'ASC', $td ); ?>
	</label> &nbsp;
	<label>
		<input type="radio" name="jmb_pf_order" value="desc"<?php echo ( 'desc' == $order ? ' checked' : '' ); ?> /><?php _e( 'DESC', $td ); ?>
	</label>
</p>

<p>
	<label for="jmb-pf-limit">
		<strong><?php _e( 'Limit', $td ); ?></strong>
	</label><br />

	<input type="text" name="jmb_pf_limit" id="jmb-pf-limit" value="<?php echo $limit; ?>" />
</p>

<p>
	<label for="jmb-pf-offset">
		<strong><?php _e( 'Offset', $td ); ?></strong>
	</label><br />

	<input type="text" name="jmb_pf_offset" id="jmb-pf-offset" value="<?php echo $offset; ?>" />
</p>
