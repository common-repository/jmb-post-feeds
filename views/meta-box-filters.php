<?php
/**
 * JMB Post Feeds
 *
 * Filters meta box view file.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$field_name_prefix = 'jmb_pf_filters';

// Add three new, empty filters for non JS users.
for ( $i = 0; $i < 3; $i++ ) {
	$filters[] = array(
		'field'         => '',
		'operation'     => '',
		'value'         => '',
		'_is_new_no_js' => 1,
	);
}

?>
<table id="jmb-pf-filters-table" class="jmb-pf-list-table" data-field-name-prefix="<?php echo $field_name_prefix; ?>">
	<thead>
		<tr>
			<th><?php _e( 'Field', $td ); ?></th>
			<th><?php _e( 'Operation', $td ); ?></th>
			<th><?php _e( 'Value', $td ); ?></th>
			<th><?php _e( 'Action', $td ); ?></th>
		</tr>
	</thead>
	<tbody>
		<tr class="jmb-pf-no-rows hide-if-no-js"<?php echo ( ! empty( $filters ) ? ' style="display: none;"' : '' ); ?>>
			<td colspan="4">
				<p><?php _e( 'No filters added.', $td ); ?></p>
			</td>
		</tr>

		<?php $i = 0; foreach ( $filters as $filter ) { ?>

			<?php $field_name = $field_name_prefix . '[' . $i . ']'; ?>

			<tr data-row-index="<?php echo $i; ?>"<?php echo ( ! empty( $filter['_is_new_no_js'] ) ? ' class="jmb-pf-new-row-no-js"' : '' ); ?>>
				<td>
					<select name="<?php echo $field_name; ?>[field]" data-field-name="field">
						<option value=""><?php _e( '--- Select ---', $td ); ?></option>

						<?php foreach ( $field_value_options as $option_group => $options ) { ?>
							<optgroup label="<?php echo esc_attr( ucfirst( $option_group ) ); ?>">

								<?php foreach ( $options as $option_value => $option_text ) { ?>

									<?php $selected = ( $option_value == $filter['field'] ? ' selected' : '' ); ?>

									<option value="<?php echo esc_attr( $option_value ); ?>"<?php echo $selected; ?>><?php echo esc_attr( $option_text ); ?></option>

								<?php } ?>

							</optgroup>
						<?php } ?>
					</select>
				</td>

				<td>
					<select name="<?php echo $field_name; ?>[operation]" data-field-name="operation">
						<?php foreach ( $operations as $operation ) { ?>

							<?php $selected = ( $operation == $filter['operation'] ? ' selected' : '' ); ?>

							<option value="<?php echo $operation; ?>"<?php echo $selected; ?>><?php echo $operation; ?></option>

						<?php } ?>
					</select>
				</td>

				<td>
					<input type="text" name="<?php echo $field_name; ?>[value]" value="<?php echo esc_attr( $filter['value'] ); ?>" data-field-name="value">
				</td>

				<td>
					<?php if ( empty( $filter['_is_new_no_js'] ) ) { ?>
						<div class="hide-if-no-js">
							<button type="button" class="button button-default jmb-pf-delete-row jmb-pf-btn-icon jmb-pf-btn-icon-delete" title="<?php _e( 'Delete', $td ); ?>"></button>
						</div>

						<div class="hide-if-js">
							<label>
								<input type="checkbox" name="<?php echo $field_name; ?>[delete]" value="1" data-field-name="delete" /> <?php _e( 'Delete', $td ); ?>
							</label>
						</div>
					<?php } ?>
				</td>
			</tr>

		<?php $i++; } ?>
	</tbody>
</table>

<div class="hide-if-no-js">
	<br />
	<button type="button" id="jmb-pf-add-filter" class="button button-primary"><?php _e( 'Add Filter', $td ); ?></button>
</div>
