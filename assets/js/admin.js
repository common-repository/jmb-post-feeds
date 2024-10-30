(function($) {
	$(function() {
		$( '.jmb-pf-new-row-no-js' ).remove();

		var fields_table_action_col_width = 95;

		/**
		* Make the Fields list table sortable.
		*/
		var fields_table_sortable_options = {
			handle: 'button.jmb-pf-move-row',
			cancel: '',
			axis: 'y',
			stop: function( event, ui ) {
				var table = $( '#jmb-pf-fields-table' );
				var index = 0;

				var field_name_prefix = table.data( 'field-name-prefix' );

				table.find( 'tbody tr' ).not( '.jmb-pf-no-rows' ).each(function() {
					$( this ).attr( 'data-row-index', index );

					$( this ).find( 'input, select' ).each(function() {
						var new_field_name = field_name_prefix + '[' + index + '][' + $( this ).data( 'field-name' ) + ']';

						$(this).attr( 'name', new_field_name );
					});

					index++;
				});
			}
		};

		if ( $( '#jmb-pf-fields-table tbody tr' ).length < 3 ) {
			fields_table_sortable_options.disabled = true;
		} else {
			$( '#jmb-pf-fields-table .jmb-pf-action-col' ).attr( 'width', fields_table_action_col_width );
		}

		$( '#jmb-pf-fields-table tbody' ).sortable( fields_table_sortable_options );

		/**
		 * Add a new row to the Fields list table.
		 */
		$( 'body' ).on( 'click', '#jmb-pf-add-field', function() {
			var table = $( '#jmb-pf-fields-table' );
			var index = 0;

			table.find( '.jmb-pf-no-rows' ).hide();

			if ( table.find( 'tbody tr' ).length > 1 ) {
				index = parseInt( table.find( 'tbody tr' ).last().data( 'row-index' ) ) + 1;
			}

			var field_name = table.data( 'field-name-prefix' ) + '[' + index + ']';

			var row  = '<tr data-row-index="' + index + '">';
				row +=   '<td>';
				row +=     '<input type="text" name="' + field_name + '[name]" value="" data-field-name="name">';
				row +=   '</td>';
				row +=   '<td>';
				row +=     '<select name="' + field_name + '[value]" data-field-name="value">';
				row +=       '<option value="">' + data_obj.select_option + '</option>';

				$.each( data_obj.field_value_options, function( option_group, options ) {
					option_group = jmb_pf_capitalize( option_group );

					row += '<optgroup label="' + option_group + '">';

					$.each( options, function( option_value, option_name ) {
						row += '<option value="' + option_value + '">' + option_name + '</option>';
					} );

					row += '</optgroup>';
				} );

				row +=     '</select>';
				row +=   '</td>';
				row +=   '<td>';
				row +=     '<input type="text" name="' + field_name + '[max_length]" value="" data-field-name="max_length">';
				row +=   '</td>';
				row +=   '<td>';
				row +=     '<button type="button" class="button button-default jmb-pf-delete-row jmb-pf-btn-icon jmb-pf-btn-icon-delete" title="' + data_obj.button_delete + '"></button> ';
				row +=     '<button type="button" class="button button-default jmb-pf-move-row jmb-pf-btn-icon jmb-pf-btn-icon-move" title="' + data_obj.button_move + '"></button>';
				row +=   '</td>';
				row += '</tr>';

			table.find( 'tbody' ).append( row );
			table.find( '.jmb-pf-action-col' ).attr( 'width', fields_table_action_col_width );

			if ( table.find( 'tbody tr' ).length > 2 ) {
				table.find( 'tbody' ).sortable( 'option', 'disabled', false );
			}
		});

		/**
		 * Custom field value.
		 */
		$( 'body' ).on( 'change', '#jmb-pf-fields-table select[data-field-name="value"]', function() {
			var row   = $( this ).closest( 'tr' );
			var table = $( this ).closest( 'table' );

			var field_name_prefix = table.data( 'field-name-prefix' );
			var row_index = row.data( 'row-index' );

			if ( $( this ).val() == 'general:custom' ) {
				field_name = field_name_prefix + '[' + row_index + '][custom_value]';

				$( this ).after( '<input type="text" name="' + field_name + '" style="display:block; margin-top: 5px;" data-field-name="custom_value">' );
			} else {
				row.find( 'input[data-field-name="custom_value"]' ).remove();
			}
		});

		/**
		 * Add a new row to the Filters list table.
		 */
		$( 'body' ).on( 'click', '#jmb-pf-add-filter', function() {
			var table = $( '#jmb-pf-filters-table' );
			var index = 0;

			table.find( '.jmb-pf-no-rows' ).hide();

			if ( table.find( 'tbody tr' ).length > 1 ) {
				index = parseInt( table.find( 'tbody tr' ).last().data( 'row-index' ) ) + 1;
			}

			var field_name = table.data( 'field-name-prefix' ) + '[' + index + ']';

			var row  = '<tr data-row-index="' + index + '">';
				row +=   '<td>';
				row +=     '<select name="' + field_name + '[field]">';
				row +=       '<option value="">' + data_obj.select_option + '</option>';

				$.each( data_obj.field_value_options, function( option_group, options ) {
					if ( option_group == 'general' ) {
						return true;
					}

					option_group = jmb_pf_capitalize( option_group );

					row += '<optgroup label="' + option_group + '">';

					$.each( options, function( option_value, option_name ) {
						if ( option_value == 'post:permalink' || option_value == 'post:thumbnail' || option_value == 'post:post_type' ) {
							return true;
						}

						row += '<option value="' + option_value + '">' + option_name + '</option>';
					});

					row += '</optgroup>';
				} );

				row +=     '</select>';
				row +=   '</td>';
				row +=   '<td>';
				row +=     '<select name="' + field_name + '[operation]">';

				$.each( data_obj.filter_operations, function( op_key, op_value ) {
					row += '<option value="' + op_value + '">' + op_value + '</option>';
				});

				row +=     '</select>';
				row +=   '</td>';
				row +=   '<td>';
				row +=     '<input type="text" name="' + field_name + '[value]" value="">';
				row +=   '</td>';
				row +=   '<td>';
				row +=     '<button type="button" class="button button-default jmb-pf-delete-row jmb-pf-btn-icon jmb-pf-btn-icon-delete" title="' + data_obj.button_delete + '"></button> ';
				row +=   '</td>';
				row += '</tr>';

			table.find( 'tbody' ).append( row );
		});

		/**
		 * Delete a row from a list table.
		 */
		$( 'body' ).on( 'click', '.jmb-pf-list-table button.jmb-pf-delete-row', function() {
			var table = $( this ).closest( 'table.jmb-pf-list-table' );
			var row   = $( this ).closest( 'tr' );

			row.remove();

			var row_count = table.find( 'tbody tr' ).length;

			if ( table.attr( 'id' ) == 'jmb-pf-fields-table' ) {
				if ( row_count == 1 ) {
					table.find( '.jmb-pf-action-col' ).removeAttr( 'width' );
				} else if ( row_count < 3 ) {
					table.find( 'tbody' ).sortable( 'option', 'disabled', true );
				}
			}
			
			if ( row_count == 1 ) {
				table.find( '.jmb-pf-no-rows' ).show();
			}
		});

		$( '#jmb-pf-feeds-table input[readonly]' ).on( 'focus', function() {
			$(this).select();
		});
	});
})(jQuery);

function jmb_pf_capitalize( string ) {
	return string.charAt( 0 ).toUpperCase() + string.slice( 1 );
}
