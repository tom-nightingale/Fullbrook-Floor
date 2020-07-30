<?php
    $options = get_option( 'propertyhive_property_import' );
    if ( is_array($options) && !empty($options) )
    {
?>
<h3>Active Automatic Imports</h3>

<table class="widefat" cellspacing="0">
    <thead>
        <tr>
            <th class="format"><?php _e( 'Format', 'propertyhive' ); ?></th>
            <th class="details"><?php _e( 'Details', 'propertyhive' ); ?></th>
            <th class="frequency"><?php _e( 'Import Frequency', 'propertyhive' ); ?></th>
            <th class="lastran"><?php _e( 'Last Ran', 'propertyhive' ); ?></th>
            <th class="nextdue"><?php _e( 'Next Due To Run', 'propertyhive' ); ?></th>
            <th class="status"><?php _e( 'Status', 'propertyhive' ); ?></th>
            <th class="actions">&nbsp;</th>
        </tr>
    </thead>
    <tbody>
    <?php
        $one_running = false;
        foreach ( $options as $import_id => $option )
        {
            if ( isset($option['deleted']) && $option['deleted'] == 1 )
            {
                continue;
            }

            $name = $option['format'];
            $details = '';
            $additional_actions = '';
            switch ($option['format'])
            {
                case "blm_local":
                {
                    $name = 'BLM';
                    $details = 'Directory: ' . $option['local_directory'];
                    break;
                }
                case "blm_remote":
                {
                    $name = 'BLM';
                    $details = 'URL: ' . $option['url'];
                    break;
                }
                case "xml_dezrez":
                {
                    $name = 'DezrezOne XML';
                    $details = 'API Key: ' . $option['api_key'] . '<br>EAID: ' . $option['eaid'];
                    if ( isset($option['branch_ids']) && trim($option['branch_ids']) != '' )
                    {
                        $details .= '<br>Branch IDs: ' . $option['branch_ids'];
                    }
                    break;
                }
                case "json_dezrez":
                {
                    $name = 'Dezrez Rezi JSON';
                    $details = 'API Key: ' . $option['api_key'];
                    if ( isset($option['branch_ids']) && trim($option['branch_ids']) != '' )
                    {
                        $details .= '<br>Branch IDs: ' . $option['branch_ids'];
                    }
                    if ( isset($option['tags']) && trim($option['tags']) != '' )
                    {
                        $details .= '<br>Tags: ' . $option['tags'];
                    }
                    break;
                }
                case "xml_expertagent":
                {
                    $name = 'ExpertAgent XML';
                    $details = 'FTP Host: ' . $option['ftp_host'] . '<br>FTP User: ' . $option['ftp_user'] . '<br>FTP Pass: ' . $option['ftp_pass'];
                    break;
                }
                case "xml_jupix":
                {
                    $name = 'Jupix XML';
                    $details = 'XML URL: ' . $option['xml_url'];
                    break;
                }
                case "xml_vebra_api":
                {
                    $name = 'Vebra XML API';
                    $details = 'Username: ' . $option['username'] . '<br>Password: ' . $option['password'] . '<br>Datafeed ID: ' . $option['datafeed_id'];
                    break;
                }
                case "xml_acquaint":
                {
                    $name = 'Acquaint XML';
                    $details = 'XML URL(s): ' . $option['xml_url'];
                    break;
                }
                case "xml_citylets":
                {
                    $name = 'Citylets XML';
                    $details = 'XML URL: ' . $option['xml_url'];
                    break;
                }
                case "xml_sme_professional":
                {
                    $name = 'SME Professional XML';
                    $details = 'XML URL: ' . $option['xml_url'];
                    break;
                }
                case "thesaurus":
                {
                    $name = 'MRI (Thesaurus Format)';
                    $details = 'FTP Host: ' . $option['ftp_host'] . '<br>FTP User: ' . $option['ftp_user'] . '<br>FTP Pass: ' . $option['ftp_pass'];
                    break;
                }
                case "xml_mri":
                {
                    $name = 'MRI (XML Format)';
                    $details = 'URL: ' . $option['url'] . '<br>Password: ' . $option['password'];
                    break;
                }
                case "jet":
                {
                    $name = 'Reapit / JET';
                    $details = 'URL: ' . $option['url'] . '<br>User: ' . $option['user'] . '<br>Pass: ' . $option['pass'];
                    break;
                }
                case "xml_rentman":
                {
                    $name = 'Rentman XML';
                    $details = 'Directory: ' . $option['local_directory'];
                    break;
                }
                case "json_letmc":
                {
                    $name = 'AgentOS API';
                    $details = 'API Key: ' . $option['api_key'] . '<br>Short Name: ' . $option['short_name'];
                    break;
                }
                case "reaxml_local":
                {
                    $name = 'REAXML Local';
                    $details = 'Directory: ' . $option['local_directory'];
                    break;
                }
                case "xml_10ninety":
                {
                    $name = '10ninety XML';
                    $details = 'XML URL: ' . $option['xml_url'];
                    break;
                }
                case "xml_domus":
                {
                    $name = 'Domus API';
                    $details = 'API URL: ' . $option['xml_url'];
                    break;
                }
                case "json_realla":
                {
                    $name = 'Realla API';
                    $details = 'API Key: ' . $option['api_key'];
                    break;
                }
                case "json_agency_pilot":
                {
                    $name = 'Agency Pilot JSON';
                    $details = 'URL: ' . $option['url'] . '<br>Password: ' . $option['password'];
                    break;
                }
                case "api_agency_pilot":
                {
                    $name = 'Agency Pilot REST API';
                    $details = 'URL: ' . $option['url'] . '<br>Client ID: ' . ( ( isset($option['client_id']) ) ? $option['client_id'] : '-' ) . '<br>Client Secret: ' . ( ( isset($option['client_secret']) ) ? $option['client_secret'] : '-' );
                    break;
                }
                case "xml_propertyadd":
                {
                    $name = 'PropertyADD XML';
                    $details = 'URL: ' . $option['url'];
                    break;
                }
                case "xml_gnomen":
                {
                    $name = 'Gnomen XML';
                    $details = 'URL: ' . $option['url'];
                    break;
                }
                case "xml_webedge":
                {
                    $name = 'WebEDGE XML';
                    $details = 'Shared Secret: ' . $option['shared_secret'] . '<br>Request URL: ' . untrailingslashit(get_site_url()) . '/webedge-send-property/';
                    break;
                }
                case "xml_kyero":
                {
                    $name = 'Kyero XML';
                    $details = 'URL: ' . $option['url'];
                    break;
                }
                case "xml_resales_online":
                {
                    $name = 'ReSales Online XML';
                    $details = 'URL: ' . $option['url'];
                    break;
                }
                case "json_loop":
                {
                    $name = 'Loop API';
                    $details = 'URL: ' . $option['url'] . '<br>API Key: ' . $option['client_id'];
                    break;
                }
                case "json_veco":
                {
                    $name = 'Veco API';
                    $details = 'Access Token: ' . $option['access_token'];
                    break;
                }
                case "xml_estatesit":
                {
                    $name = 'Estates IT XML';
                    $details = 'Directory: ' . $option['local_directory'];
                    break;
                }
                case "xml_juvo":
                {
                    $name = 'Juvo XML';
                    $details = 'URL: ' . $option['url'];
                    break;
                }
                case "json_utili":
                {
                    $name = 'Utili API';
                    $details = 'API Key: ' . $option['api_key'] . '<br>Account Name: ' . $option['account_name'];
                    break;
                }
                case "json_arthur":
                {
                    $name = 'Arthur Online API';
                    $details = 'Client ID: ' . $option['client_id'] . '<br>Client Secret: ' . $option['client_secret'] . '<br>Entity ID: ' . $option['entity_id'] . '<br>Access Token: ' . $option['access_token'] . '<br>Expires: ' . ( ( isset($option['access_token_expires']) && $option['access_token_expires'] != '' ) ? date("jS F Y", $option['access_token_expires']) : '-' );
                    if ( $option['access_token'] == '' )
                    {
                        $additional_actions .= '<a href="https://auth.arthuronline.co.uk/oauth/authorize?client_id=' . $option['client_id'] . '&redirect_uri=' . urlencode(admin_url('admin.php?page=propertyhive_import_properties&arthur_callback=1&import_id=' . $import_id)) . '&state=' . uniqid() . '" class="button button-primary">Authorize</a>';
                    }
                    else
                    {
                        $additional_actions .= '<a href="https://auth.arthuronline.co.uk/oauth/authorize?client_id=' . $option['client_id'] . '&redirect_uri=' . urlencode(admin_url('admin.php?page=propertyhive_import_properties&arthur_callback=1&import_id=' . $import_id)) . '&state=' . uniqid() . '" class="button">Re-Authorize</a>';
                    }

                    break;
                }
                case "xml_supercontrol":
                {
                    $name = 'SuperControl API';
                    $details = 'Client ID: ' . $option['client_id'] . '<br>API Key: ' . $option['api_key'];
                    break;
                }
                case "xml_agentsinsight":
                {
                    $name = 'agentsinsight* XML';
                    $details = 'URL: ' . $option['xml_url'];
                    break;
                }
                case "json_rex":
                {
                    $name = 'Rex API';
                    $details = 'Username: ' . $option['username'] . '<br>Password: ' . $option['password'];
                    break;
                }
                case "xml_decorus":
                {
                    $name = 'Decorus / Landlord Manager XML';
                    $details = 'Directory: ' . $option['local_directory'];
                    break;
                }
            }

            $name = apply_filters( 'propertyhive_property_import_format_name', $name, $option );
            $details = apply_filters( 'propertyhive_property_import_format_details', $details, $option );

            $time_offset = (int) get_option('gmt_offset') * 60 * 60;
    ?>
        <tr>
            <td class="format"><?php echo $name; ?></td>
            <td class="details" style="overflow-wrap: break-word; word-wrap:break-word; max-width:300px;"><?php echo $details; ?></td>
            <td class="frequency"><?php echo ucwords( str_replace("_", " ", $option['import_frequency']) ); ?></td>
            <td class="lastran"><?php

                $ran_before = false;
                $row = $wpdb->get_row( "
                    SELECT 
                        start_date, end_date
                    FROM 
                        " .$wpdb->prefix . "ph_propertyimport_logs_instance
                    WHERE 
                        import_id = '" . $import_id . "'
                    /*    start_date < end_date*/
                    ORDER BY start_date DESC LIMIT 1
                ", ARRAY_A);
                if ( null !== $row )
                {
                    $ran_before = true;
                    if ($row['start_date'] <= $row['end_date'])
                    {
                        echo date("jS F Y H:i", strtotime($row['start_date']) + $time_offset);
                    }
                    elseif ($row['end_date'] == '0000-00-00 00:00:00')
                    {
                        echo 'Running now...<br>Started at ' . date("H:i jS F", strtotime($row['start_date']) + $time_offset);
                    }
                }
                else
                {
                    echo '-';
                }

            ?></td>
            <td class="nextdue">
                <?php
                    if ( isset($option['running']) && $option['running'] == 1 )
                    {
                        $next_due = wp_next_scheduled( 'phpropertyimportcronhook' );

                        if ( $next_due == FALSE )
                        {
                            echo 'Whoops. WordPress doesn\'t have the import scheduled. A quick fix to this is to deactivate, then re-activate the plugin.';
                        }
                        else
                        {
                            $last_start_date = '2000-01-01 00:00:00';
                            $row = $wpdb->get_row( "
                                SELECT 
                                    start_date
                                FROM 
                                    " .$wpdb->prefix . "ph_propertyimport_logs_instance
                                WHERE
                                    import_id = '" . $import_id . "'
                                ORDER BY start_date DESC LIMIT 1
                            ", ARRAY_A);
                            if ( null !== $row )
                            {
                                $last_start_date = $row['start_date'];   
                            }
                            $last_start_date = strtotime($last_start_date);

                            $got_next_due = false;

                            while ( $got_next_due === false )
                            {
                                switch ($option['import_frequency'])
                                {
                                    case "every_15_minutes":
                                    {
                                        if ( ( ($next_due - $last_start_date) / 60 / 60 ) >= 0.25 )
                                        {
                                            $got_next_due = $next_due;
                                        }
                                        break;
                                    }
                                    case "hourly":
                                    {
                                        if ( ( ($next_due - $last_start_date) / 60 / 60 ) >= 1 )
                                        {
                                            $got_next_due = $next_due;
                                        }
                                        break;
                                    }
                                    case "twicedaily":
                                    {
                                        if ( ( ($next_due - $last_start_date) / 60 / 60 ) >= 12 )
                                        {
                                            $got_next_due = $next_due;
                                        }
                                        break;
                                    }
                                    default: // daily
                                    {
                                        if ( ( ($next_due - $last_start_date) / 60 / 60 ) >= 24 )
                                        {
                                            $got_next_due = $next_due;
                                        }
                                    }
                                }
                                $next_due = $next_due + 900;
                            }
                            
                            if ( gmdate("Y-m-d", $got_next_due) == gmdate("Y-m-d") )
                            {
                                echo 'Today at ' . date("H:i", $got_next_due + $time_offset);
                            }
                            elseif ( gmdate("Y-m-d", $got_next_due) == gmdate("Y-m-d", strtotime('tomorrow')) )
                            {
                                echo 'Tomorrow at ' . date("H:i", $got_next_due + $time_offset);
                            }
                            else
                            {
                                echo gmdate("H:i jS F", $got_next_due + $time_offset);
                            }
                        }
                    }
                    else
                    {
                        echo '-';
                    }
                ?>
            </td>
            <td class="status"><?php echo ( ( isset($option['running']) && $option['running'] == 1 ) ? '<strong style="color:#090">Active</strong>' : '<strong style="color:#900">Inactive</strong>' ) ; ?></td>
            <td>
                <?php 
                    if ( isset($option['running']) && $option['running'] == 1 )
                    {
                        $one_running = true;
                    }
                    echo ( ( isset($option['running']) && $option['running'] == 1 ) ? 
                        '<a href="' . admin_url('admin.php?page=propertyhive_import_properties&running=0&import_id=' . $import_id) . '" class="button">Pause Automatic Import</a>' : 
                        '<a href="' . admin_url('admin.php?page=propertyhive_import_properties&running=1&import_id=' . $import_id) . '" class="button-primary">' . ( ( $ran_before ) ? 'Resume' : 'Start' ) . ' Automatic Import</a>' ) ; 
                    ?>

                    <?php echo $additional_actions; ?>

                    <a href="<?php echo admin_url('admin.php?page=propertyhive_import_properties&edit=' . $import_id); ?>" class="button"><?php echo __( 'Edit', 'propertyhive' ); ?></a>

                    <?php
                        if ( isset($option['running']) && $option['running'] == 1  )
                        {

                        }
                        else
                        {
                            // Check if any properties are on the market and assigned to this portal, in which case show a more informative message
                            $additional_notice = '';
                            $args = array(
                                'post_type' => 'property',
                                'posts_per_page' => 1,
                                'meta_query' => array(
                                    'relation' => 'AND',
                                    array(
                                        'key'     => '_imported_ref_' . $import_id,
                                        'compare' => 'EXISTS',
                                    ),
                                    array(
                                        'key'     => '_on_market',
                                        'value'   => 'yes',
                                    ),
                                ),
                            );
                            $property_query = new WP_Query( $args );
                            if ( $property_query->have_posts() )
                            {
                                $additional_notice = 'There are properties still on the market assigned to this import. Please note that if you delete this import will have to remove these from the market manually.\n\n';
                            }
                    ?>
                    <a href="<?php echo admin_url('admin.php?page=propertyhive_import_properties&delete=' . $import_id); ?>" class="button" onclick="var confirmbox = confirm('<?php echo $additional_notice . __( 'Once deleted this cannot be undone. Are you sure you wish to delete this import?', 'propertyhive' ); ?>'); return confirmbox;"><?php echo __( 'Delete', 'propertyhive' ); ?></a>
                    <?php
                        }
                    ?>
                    <a href="<?php echo admin_url('admin.php?page=propertyhive_import_properties&logs=' . $import_id); ?>" class="button"><?php echo __( 'View Logs', 'propertyhive' ); ?></a>
            </td>
        </tr>
        <?php
            }
        ?>
    </tbody>
</table>

<?php
    if ($one_running) {
        echo '<br><a onclick="setTimeout(function() { jQuery(\'#run_now\').html(\'Running...\'); jQuery(\'#run_now\').attr(\'disabled\', true) }, 10);" href="' . admin_url('admin.php?page=propertyhive_import_properties&custom_property_import_cron=phpropertyimportcronhook') . '" id="run_now" class="button">Run Now</a><br>';
    }
?>

<br>
<hr>
<br>

<?php
    }
?>

<form action="<?php echo admin_url('admin.php?page=propertyhive_import_properties'); ?>" name="frmPropertyImportOne" method="post" enctype="multipart/form-data">

    <h3>Step 1. Create a New Import</h3>

    <p><?php _e('Please select whether you would like to do a manual one-off upload, or whether the imports should occur automatically on a regular basis', 'propertyhive'); ?></p>

    <table class="form-table">
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="manual_automatic_manual"><?php _e( 'Import Type', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">
                    <label>
                    <input type="radio" name="manual_automatic" id="manual_automatic_automatic" value="automatic" checked />
                    Automatic
                </label>
                <span class="description">
                    <p><?php _e('Select this if your properties are managed elsewhere and should be automatically imported on a regular basis', 'propertyhive'); ?></p>
                </span>
                <br>
                <label>
                    <input type="radio" name="manual_automatic" id="manual_automatic_manual" value="manual" />
                    Manual
                </label>
                <span class="description">
                    <p><?php _e('Upload the file and perform a one-off import of properties', 'propertyhive'); ?></p>
                </span>
            </td>
        </tr>
    </table>

    <p class="submit">
        <input name="save" id="save_import_step" class="button-primary" type="submit" value="Continue">
     </p>

</form>