<form action="<?php echo admin_url('admin.php?page=propertyhive_import_properties'); ?>" name="frmPropertyImportCsvMapping" method="post">

    <h3>Step 3. Column Mapping</h3>

    
        <?php 
            foreach ($propertyhive_fields as $i => $propertyhive_field) 
            {
                if ( $propertyhive_field['value_type'] == 'section_start' )
                {
                    echo '
                    <h3>' .  __( $propertyhive_field['label'], 'propertyhive' ) . '</h3>
                    <table class="form-table">
                        <tr valign="top">
                            <th style="width:auto" scope="row">Property Hive Field</th>
                            <th style="width:auto" scope="row">CSV Field</th>
                        </tr>';
                    continue;
                }

                if ( $propertyhive_field['value_type'] == 'section_end' )
                {
                    echo '</table>';
                    continue;
                }
        ?>
        <tr valign="top">
            <th scope="row" class="titledesc" style="font-weight:400;">

                <label>
                    <?php echo __( $propertyhive_field['label'], 'propertyhive' ); ?>
                    <?php if (isset($propertyhive_field['required']) && $propertyhive_field['required']) { echo '<span style="color:#900;">*</span>'; } ?>
                </label>

            </th>
            <td class="forminp forminp-text">

                <?php
                    $options = array();

                    foreach ( $column_headers as $j => $column_header )
                    {
                        $option = '<option value="' . $j . '"';
                        if ( isset($column_mappings[$i]) && $column_mappings[$i] == $j && $column_mappings[$i] != '' )
                        {
                            $option .= ' selected';
                        }
                        $option .= '>' . $column_header . '</option>';
                        $options[] = $option;
                    }
                ?>
                <select name="column_mapping[<?php echo $i; ?>]">
                    <option value=""></option>
                    <?php echo implode("", $options); ?>
                </select>

                <?php
                    if ( 
                        isset($propertyhive_field['desc']) && 
                        $propertyhive_field['desc'] != ''
                    )
                    {
                        echo '<p><em>' . $propertyhive_field['desc'] . '</em></p>';
                    }

                    if ( 
                        isset($propertyhive_field['possible_values']) && 
                        is_array($propertyhive_field['possible_values']) && 
                        !empty($propertyhive_field['possible_values'])
                    )
                    {
                        echo '<p><em>' . __( 'Possible Values', 'propertyhive' ) . ':<br>' . implode("<br>", $propertyhive_field['possible_values']) . '</em></p>';
                    }
                ?>

            </td>
        </tr>
        <?php
        	}
        ?>
    </table>

    <p class="submit">
        <input type="hidden" name="csv_mapping" value="1">
        <input type="hidden" name="target_file" value="<?php echo $target_file; ?>">
        <a href="<?php echo admin_url('admin.php?page=propertyhive_import_properties'); ?>" class="button">Cancel</a>
        <input name="save" id="save_import_step" class="button-primary" type="submit" value="Continue" onclick="setTimeout(function() { document.getElementById('save_import_step').disabled='disabled'; }, 1);">
    </p>

</form>