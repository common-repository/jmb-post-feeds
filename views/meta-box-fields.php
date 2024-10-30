<?php
/**
 * JMB Post Feeds
 *
 * Fields meta box view file.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$field_name_prefix = 'jmb_pf_fields';

// Add three new, empty fields for non JS users.
for ( $i = 0; $i < 3; $i++ ) {
	$fields[] = array(
		'name'          => '',
		'value'         => '',
		'max_length'    => '',
		'_is_new_no_js' => 1,
	);
}

?>
<table id="jmb-pf-fields-table" class="jmb-pf-list-table" data-field-name-prefix="<?php echo $field_name_prefix; ?>">
	<thead>
		<tr>
			<th><?php _e( 'Name', $td ); ?></th>
			<th><?php _e( 'Value', $td ); ?></th>
			<th><?php _e( 'Max Length', $td ); ?></th>
			<th class="jmb-pf-action-col"><?php _e( 'Action', $td ); ?></th>
		</tr>
	</thead>
	<tbody>
		<tr class="jmb-pf-no-rows hide-if-no-js"<?php echo ( ! empty( $fields ) ? ' style="display: none;"' : '' ); ?>>
			<td colspan="4">
				<p><?php _e( 'No fields added.', $td ); ?></p>
			</td>
		</tr>

		<?php $i = 0; foreach ( $fields as $field ) { ?>

			<?php $field_name = $field_name_prefix . '[' . $i . ']'; ?>

			<tr data-row-index="<?php echo $i; ?>"<?php echo ( ! empty( $field['_is_new_no_js'] ) ? ' class="jmb-pf-new-row-no-js"' : '' ); ?>>
				<td>
					<input type="text" name="<?php echo $field_name; ?>[name]" value="<?php echo esc_attr( $field['name'] ); ?>" data-field-name="name" />
				</td>

				<td>
					<select name="<?php echo $field_name; ?>[value]" data-field-name="value">
						<option value=""><?php _e( '--- Select ---' ); ?></option>

						<?php foreach ( $field_value_options as $option_group => $options ) { ?>
							<optgroup label="<?php echo esc_attr( ucfirst( $option_group ) ); ?>">

								<?php foreach ( $options as $option_value => $option_text ) { ?>

									<?php $selected = ( $option_value == $field['value'] ? ' selected' : '' ); ?>

									<option value="<?php echo esc_attr( $option_value ); ?>"<?php echo $selected; ?>><?php echo esc_attr( $option_text ); ?></option>

								<?php } ?>

							</optgroup>
						<?php } ?>

					</select>

					<?php if ( 'general:custom' == $field['value'] ) { ?>
						<input type="text" name="<?php echo $field_name; ?>[custom_value]" value="<?php echo ( isset( $field['custom_value'] ) ? esc_attr( $field['custom_value'] ) : '' ); ?>" style="display: block; margin-top: 5px;" data-field-name="custom_value" />
					<?php } ?>
				</td>

				<td>
					<input type="text" name="<?php echo $field_name; ?>[max_length]" value="<?php echo (int) $field['max_length']; ?>" data-field-name="max_length" />
				</td>

				<td>
					<?php if ( empty( $field['_is_new_no_js'] ) ) { ?>
						<div class="hide-if-no-js">
							<button type="button" class="button button-default jmb-pf-delete-row jmb-pf-btn-icon jmb-pf-btn-icon-delete" title="<?php _e( 'Delete', $td ); ?>"></button>
							<button type="button" class="button button-default jmb-pf-move-row jmb-pf-btn-icon jmb-pf-btn-icon-move" title="<?php _e( 'Move', $td ); ?>"></button>
						</div>

						<div class="hide-if-js">
							<label><input type="checkbox" name="<?php echo $field_name; ?>[delete]" value="1" data-field-name="delete" /><?php _e( 'Delete', $td ); ?></label>
						</div>
					<?php } ?>
				</td>
			</tr>

		<?php $i++; } ?>
	</tbody>
</table>

<div class="hide-if-no-js">
	<br />
	<button type="button" id="jmb-pf-add-field" class="button button-primary"><?php _e( 'Add Field', $td ); ?></button>
</div>
