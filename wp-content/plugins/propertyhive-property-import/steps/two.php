<?php
    $options = get_option( 'propertyhive_property_import' );
    if ( $import_id != '' )
    {
        if ( isset($options[$import_id]) )
        {
            $options = $options[$import_id];
        }
        else
        {
            die('Invalid automatic import. Please go back and try again');
        }
    }
    $existing_mappings = array();

    if ( isset($options['mappings']) )
    {
        $existing_mappings = $options['mappings'];
    }

	$mappings = $PH_Import_Instance->get_mappings($import_id);
?>

<script>
    var num_custom_mappings = new Array();
</script>

<form action="<?php echo admin_url('admin.php?page=propertyhive_import_properties'); ?>" name="frmPropertyImportTwo" method="post">

    <h3>Step 3. Import Settings</h3>

    <?php if (!isset($_POST['format'])) { ?><p><strong><?php _e('Great news! The file uploaded appears to be valid.', 'propertyhive'); ?></strong></p><?php } ?>

    <p><?php _e('The final step is to make sure that the custom fields in the uploaded file match that in your Property Hive installation', 'propertyhive'); ?></p>

    <p>On the left are the custom fields we found in the uploaded file, and on the right are the <a href="<?php echo admin_url('admin.php?page=ph-settings&tab=customfields'); ?>">custom fields setup in Property Hive</a>. Simply match as many of them as possible to ensure properties are imported with as much data as possible.</p>
    <br>
    <?php
        $availability_departments = get_option( 'propertyhive_availability_departments', array() );
        if ( !is_array($availability_departments) ) { $availability_departments = array(); }

        foreach ( $mappings as $custom_field_name => $custom_field_values )
        {
            if ( !empty($custom_field_values) )
            {
                // Gets the text values for the BLM values to assist the user as IDS don't mean much
                $import_mappings = $PH_Import_Instance->get_mapping_values($custom_field_name, $import_id);

                $ph_options = array();
                if ( $custom_field_name == 'office' )
                {
                    $args = array(
                        'post_type' => 'office',
                        'nopaging' => true,
                        'orderby' => 'title',
                        'order' => 'ASC'
                    );
                    $office_query = new WP_Query($args);
                    
                    if ($office_query->have_posts())
                    {
                        while ($office_query->have_posts())
                        {
                            $office_query->the_post();
                            
                            $ph_options[] = array(
                                'value' => get_the_ID(),
                                'text' => get_the_title(),
                                'original_text' => get_the_title(),
                            );
                        }
                    }
                    $office_query->reset_postdata();
                }
                elseif ( $custom_field_name == 'department' )
                {
                    $departments = ph_get_departments();

                    foreach ( $departments as $key => $value )
                    {
                        if ( get_option( 'propertyhive_active_departments_' . str_replace("residential-", "", $key) ) == 'yes' )
                        {
                            $ph_options[] = array(
                                'value' => $key,
                                'text' => $value,
                                'original_text' => $value,
                            );
                        }
                    }
                }
                else
                {
                    $taxonomy = $custom_field_name;
                    if ( $taxonomy == 'sales_availability' || $taxonomy == 'lettings_availability' || $taxonomy == 'commercial_availability' )
                    {
                        $taxonomy = 'availability';
                    }

                    $args = array(
                        'hide_empty' => false,
                        'parent' => 0
                    );
                    $terms = get_terms( $taxonomy, $args );
                    if ( !empty( $terms ) && !is_wp_error( $terms ) )
                    {
                        foreach ($terms as $term)
                        {
                            $option = array(
                                'value' => $term->term_id,
                                'text' => $term->name,
                                'original_text' => $term->name,
                            );

                            $ph_options[] = $option;

                            $args = array(
                                'hide_empty' => false,
                                'parent' => $term->term_id
                            );
                            $subterms = get_terms( $taxonomy, $args );
                            
                            if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
                            {
                                foreach ($subterms as $term)
                                {
                                    $option = array(
                                        'value' => $term->term_id,
                                        'text' => '- ' . $term->name,
                                        'original_text' => $term->name,
                                    );
                                    
                                    $ph_options[] = $option;

                                    $args = array(
                                        'hide_empty' => false,
                                        'parent' => $term->term_id
                                    );
                                    $subsubterms = get_terms( $taxonomy, $args );
                                    
                                    if ( !empty( $subsubterms ) && !is_wp_error( $subsubterms ) )
                                    {
                                        foreach ($subsubterms as $term)
                                        {
                                            $option = array(
                                                'value' => $term->term_id,
                                                'text' => '- - ' . $term->name,
                                                'original_text' => $term->name,
                                            );

                                            $ph_options[] = $option;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if ( 
                        ( $custom_field_name == 'sales_availability' || $custom_field_name == 'lettings_availability' || $custom_field_name == 'commercial_availability' ) && 
                        !empty($availability_departments) 
                    )
                    {
                        $new_ph_options = array();

                        // filter availabilities to only ones specific to this department
                        foreach ( $ph_options as $ph_option )
                        {
                            $include = true;

                            foreach ( $availability_departments as $availability_id => $departments )
                            {
                                if ( !empty($departments) )
                                {
                                    if ( $ph_option['value'] == $availability_id )
                                    {
                                        switch ( $custom_field_name )
                                        {
                                            case "sales_availability":
                                            {
                                                if ( !in_array('residential-sales', $departments) )
                                                {
                                                    $include = false;
                                                }
                                                break;
                                            }
                                            case "lettings_availability":
                                            {
                                                if ( !in_array('residential-lettings', $departments) )
                                                {
                                                    $include = false;
                                                }
                                                break;
                                            }
                                            case "commercial_availability":
                                            {
                                                if ( !in_array('commercial', $departments) )
                                                {
                                                    $include = false;
                                                }
                                                break;
                                            }
                                        }
                                    }
                                }
                            }

                            if ( $include )
                            {
                                $new_ph_options[] = $ph_option;
                            }
                        }

                        $ph_options = $new_ph_options;
                    }
                }
    ?>
    <hr>
    <h3><?php echo ucwords(str_replace("_", " ", strtolower($custom_field_name))); ?></h3>

    <?php
        switch ($custom_field_name)
        {
            case "availability":
            {
                if ( 
                    ( $import_id == '' && time() > PH_PROPERTYIMPORT_DEPARTMENT_AVAILABILITY_UPDATE ) || 
                    ( $import_id != '' && $import_id > PH_PROPERTYIMPORT_DEPARTMENT_AVAILABILITY_UPDATE )
                )
                {

                }
                else
                {
                    echo '<p>Note: Because the list of availabilities in WordPress are a single list at the moment, we are unable to split them out between sales and lettings.<br><br>
                    If a generic <em>AVAILABLE</em> or <em>ON MARKET</em> mapping is required below, but you have availabilities setup in Property Hive like \'For Sale\' and \'To Let\', please choose one and then edit them properties manually afterwards if doing a manual import, or <a href="https://docs.wp-property-hive.com/add-ons/property-import/handling-sales-and-lettings-availabilities/" target-"_blank">read our documentation</a> to see how to handle this with automatic imports.</p>';
                }
                break;
            }
            case "price_qualifier":
            {
                if ( isset($_POST['format']) && strpos($_POST['format'], 'blm') == FALSE)
                {

                }
                else
                {
                    echo '<p>Note: If the POA mapping is required below, this will be ignored as we will automatically check the POA box on a property record.</p>';
                }
                break;
            }
            case "property_type":
            {
                if ( isset($_POST['format']) && strpos($_POST['format'], 'blm') === FALSE && strpos($_POST['format'], 'dezrez') === FALSE)
                {
                    echo '<p>Note: We receive the type and style as two separate fields in this file format. The values on the left are a combination of both of these (i.e. TYPE - STYLE).</p>';
                }
                else
                {
                    
                }
                break;
            }
            case "office":
            {
                echo '<p>Note: If no office is mapped, we will default properties to the office marked as \'Primary\' under \'Settings &gt; Offices\'.</p>';
                break;
            }
        }
    ?>

    <table class="form-table" id="mapping_table_<?php echo $custom_field_name; ?>">
        <tr valign="top">
            <th style="width:auto" scope="row">Imported File Value</th>
            <th style="width:auto" scope="row">Property Hive Value</th>
        </tr>
        <?php 
            foreach ($custom_field_values as $blm_value => $ph_value) 
            {
                $blm_value = (string)$blm_value;
        ?>
        <tr valign="top">
            <th scope="row" class="titledesc">

                <label for="import_blm"><?php 
                    if ( $blm_value != '' )
                    {
                        echo $blm_value . ( ( isset($import_mappings[$blm_value]) && $import_mappings[$blm_value] != $blm_value ) ? '<span style="opacity:0.4"> - ' . $import_mappings[$blm_value] . '</span>' : '' );
                    }
                    else
                    {
                        echo '(no value)';
                    }
                ?></label>

            </th>
            <td class="forminp forminp-text">

                <?php
                    $options = array();

                    foreach ( $ph_options as $ph_option )
                    {
                        $option = '<option value="' . $ph_option['value'] . '"';
                        if ( 
                            (
                                isset($existing_mappings[$custom_field_name]) && 
                                isset($existing_mappings[$custom_field_name][$blm_value]) &&
                                $existing_mappings[$custom_field_name][$blm_value] == $ph_option['value']
                            )
                            ||
                            (
                                isset($existing_mappings) && 
                                empty($existing_mappings) && 
                                (
                                    $ph_option['original_text'] == $blm_value ||
                                    ( isset($import_mappings[$blm_value]) && $ph_option['original_text'] == $import_mappings[$blm_value] )
                                )
                            )
                        )
                        {
                            $option .= ' selected';
                        }
                        $option .= '>' . $ph_option['text'] . '</option>';
                        $options[] = $option;
                    }
                ?>
                <select name="mapped_<?php echo $custom_field_name; ?>[<?php echo $blm_value; ?>]">
                    <option value=""></option>
                    <?php echo implode("", $options); ?>
                </select>

            </td>
        </tr>
        <?php 
            } // end foreach custom field value 

            if ( $manual_automatic == 'automatic' )
            {
                // extract any custom mappings from the standard set
                $custom_mappings = array();
                if ( isset($existing_mappings[$custom_field_name]) )
                {
                    foreach ( $existing_mappings[$custom_field_name] as $blm_value => $ph_value ) 
                    {
                        if ( !isset($import_mappings[$blm_value]) )
                        {
                            $custom_mappings[$blm_value] = $ph_value;
                        }
                    }
                }

                $i = 0;
                foreach ($custom_mappings as $blm_value => $ph_value)
                {
        ?>
        <tr id="custom_mapping_row_<?php echo $i; ?>">
            <th scope="row" class="titledesc"><input type="text" name="custom_mapping[<?php echo $custom_field_name; ?>][]" value="<?php echo $blm_value; ?>"></th>
            <td class="forminp forminp-text">
                <select name="custom_mapping_value[<?php echo $custom_field_name; ?>][]">
                    <option value=""></option>
                    <?php
                        $options = array();
                        foreach ( $ph_options as $ph_option )
                        {
                            $option = '<option value="' . $ph_option['value'] . '"';
                            if ( 
                                isset($existing_mappings[$custom_field_name]) && 
                                isset($existing_mappings[$custom_field_name][$blm_value]) &&
                                $existing_mappings[$custom_field_name][$blm_value] == $ph_option['value']
                            )
                            {
                                $option .= ' selected';
                            }
                            $option .= '>' . $ph_option['text'] . '</option>';
                            $options[] = $option;
                        }
                        echo implode("", $options);
                    ?>
                </select>
            </td>
        </tr>
        <?php
                    ++$i;
                }
        ?>
        <script>
            var json_to_pass_<?php echo $custom_field_name; ?> = <?php echo json_encode($ph_options); ?>;
            num_custom_mappings['<?php echo $custom_field_name; ?>'] = <?php echo count($custom_mappings); ?>;
        </script>
        <tr class="row-add-custom">
            <td></td>
            <td>
                <a href="" onclick="addCustomMapping('<?php echo $custom_field_name; ?>', json_to_pass_<?php echo $custom_field_name; ?>); return false;">+ Add Additional Mapping</a>

                <span class="description">
                    <p><?php _e('The options listed above are a standard set provided by the third party. If your custom fields in the third party system differ from the standard list, you can add additional mappings by clicking the link above.', 'propertyhive'); ?></p>
                </span>
            </td>
        </tr>
        <?php
            }
        ?>
    </table>
    <?php
            }
        } // end foreach custom field
    ?>

    <p class="submit">
        <input type="hidden" name="import" value="1">
        <input type="hidden" name="import_id" value="<?php echo $import_id; ?>">
        <input type="hidden" name="manual_automatic" value="<?php echo $manual_automatic; ?>">
        <input type="hidden" name="format" value="<?php echo (isset($_POST['format'])) ? $_POST['format'] : ''; ?>">
        <input type="hidden" name="target_file" value="<?php echo $target_file; ?>">
        <a onclick="if (importing) { return false; }" id="cancel_import_step" href="<?php echo admin_url('admin.php?page=propertyhive_import_properties'); ?>" class="button">Cancel</a>
        <input name="save" id="save_import_step" class="button-primary" type="submit" value="<?php echo ( (!isset($_POST['format'])) ? 'Import Properties' : 'Finish' ); ?>" onclick="importing = true; setTimeout(function() { document.getElementById('save_import_step').disabled='disabled'; }, 1);">
    </p>

    <?php if (!isset($_POST['format'])) { ?>
    <span class="description">
        <p><?php _e('Please be patient after clicking \'Import Properties\' as the import may take a few minutes', 'propertyhive'); ?></p>
    </span>
    <?php } ?>

</form>

<script>

    var importing = false; // used to disabled button when click

    function addCustomMapping(term, ph_options)
    {
        var table = jQuery('#mapping_table_' + term);

        var new_row_html = '<tr id="custom_mapping_row_' + num_custom_mappings[term] + '">';
            new_row_html += '<th scope="row" class="titledesc"><input type="text" name="custom_mapping[' + term + '][]"></th>';
            new_row_html += '<td class="forminp forminp-text">';
            new_row_html += '<select name="custom_mapping_value[' + term + '][]">';
            new_row_html += '<option value=""></option>';
            for (var i in ph_options)
            {
                new_row_html += '<option value="' + ph_options[i].value + '">' + ph_options[i].text + '</option>';
            }
            new_row_html += '</select>';
            new_row_html += '</td>';
        new_row_html += '</tr>';

        table.find('tr.row-add-custom').before(new_row_html);

        num_custom_mappings[term] = num_custom_mappings[term] + 1;
    }

</script>