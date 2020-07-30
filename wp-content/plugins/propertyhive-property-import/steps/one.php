<?php
    $options = get_option( 'propertyhive_property_import' );
    $import_id = '';
    if ( isset($_GET['edit']) )
    {
        if ( isset($options[$_GET['edit']]) )
        {
            $options = $options[$_GET['edit']];
            $import_id = $_GET['edit'];
        }
        else
        {
            die('Invalid automatic import. Please go back and try again');
        }
    }

    $manual_automatic = '';
    if ( isset($_POST['manual_automatic']) )
    {
        $manual_automatic = $_POST['manual_automatic'];
    }
    elseif ( isset($_GET['edit']) && $_GET['edit'] != '' )
    {
        $manual_automatic = 'automatic';
    }
?>


<form action="<?php echo admin_url('admin.php?page=propertyhive_import_properties'); ?>" name="frmPropertyImportOne" method="post" enctype="multipart/form-data">

    <?php
        /* MANUAL */
        if ( $manual_automatic == 'manual' )
        {
    ?>
    <h3>Step 2. Upload and validate file</h3>

    <p><?php _e('You must validate the file to be uploaded first before being able to perform the import', 'propertyhive'); ?></p>

    <table class="form-table">
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="import_blm"><?php _e( 'BLM File', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">
                <input type="file" name="import_blm" id="import_blm" />

                <span class="description">
                    <p><?php _e('A BLM file is a text file similar to a CSV, using special characters (normally ^ and ~) to denote the end of each field and line.', 'propertyhive'); ?></p>
                </span>

            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="import_csv"><?php _e( 'CSV File', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">
                <input type="file" name="import_csv" id="import_csv" />

                <span class="description">
                    <p><?php _e('A CSV (Comma Separated Values) is a text file that has its values separated by comma', 'propertyhive'); ?></p>
                </span>

            </td>
        </tr>
    </table>

    <p class="submit">
        <input type="hidden" name="pre_test" value="1">
        <input type="hidden" name="manual_automatic" value="<?php echo $manual_automatic; ?>">
        <a href="<?php echo admin_url('admin.php?page=propertyhive_import_properties'); ?>" class="button">Cancel</a>
        <input name="save" id="save_import_step" class="button-primary" type="submit" value="Validate File" onclick="setTimeout(function() { document.getElementById('save_import_step').disabled='disabled'; }, 1);">
     </p>
    <?php
        }

        /* AUTOMATIC */
        if ( $manual_automatic == 'automatic' )
        {
            $uploads_dir = wp_upload_dir();
            if( $uploads_dir['error'] === FALSE )
            {
                $uploads_dir = $uploads_dir['basedir'] . '/ph_import/';
            }
    ?>
    <style type="text/css">
        .format-options td { padding:5px 10px 5px 0; }
    </style>

    <h3>Step 2. Enter Details</h3>

    <p><?php _e('Select the file format and where the file to import will be located, along with other details relating to the automatic import.', 'propertyhive'); ?></p>

    <h3>Select Format</h3>

    <?php
        $this_format =
            ( isset($_POST['format']) && $_POST['format'] == 'blm_local' ) ? 
                true : 
                ( ( isset($options['format']) && $options['format'] == 'blm_local' ) ? true : false );
    ?>
    <table class="form-table">
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="format_blm_local"><?php _e( 'BLM File - Local Directory', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <label>
                    <input type="radio" name="format" id="format_blm_local" value="blm_local"<?php echo ( $this_format ? ' checked' : '' ); ?> />
                    Select
                </label>
                <br><br>
                <div class="format-options" id="blm_local_options" style="display:<?php echo ( $this_format ? 'block' : 'none' ); ?>">
                    <table>
                        <tr>
                            <td>
                                Local Directory
                            </td>
                            <td>
                                <input type="text" name="blm_local_directory" id="blm_local_directory" value="<?php
                                    echo ( isset($_POST['blm_local_directory']) ) ? 
                                        $_POST['blm_local_directory'] : 
                                        ( isset($options['local_directory']) ? $options['local_directory'] : $uploads_dir );
                                ?>" />
                                <span class="description">
                                    <p><?php _e('When the import runs it will parse all BLM files found in this local directory in date order', 'propertyhive'); ?></p>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Only import updated properties
                            </td>
                            <td>
                                <input type="checkbox" name="blm_local_only_updated" id="blm_local_only_updated" value="yes" <?php
                                    echo ( isset($_POST['blm_local_only_updated']) && $_POST['blm_local_only_updated'] == 'yes' ) ? 
                                        'checked' : 
                                        ( ( ( isset($options['only_updated']) && $options['only_updated'] == 'yes' ) || !isset($options['only_updated']) ) ? 'checked' : '' );
                                ?> />
                            </td>
                        </tr>
                    </table>

                    

                    <?php if ( !ini_get('allow_url_fopen') ) { ?>
                    <span class="description">
                        <p style="color:#900; font-weight:700;"><?php _e('BLMs aren\'t provided with latitude and longitude co-ordinates. As a result we make a geocoding request to obtain them. You server does not have the \'allow_url_fopen\' setting activated so we won\'t be able to obtain coordinates for properties.', 'propertyhive'); ?></p>
                    </span>
                    <?php } ?>
                </div>

            </td>
        </tr>
    </table>

    <?php

        $this_format =
            ( isset($_POST['format']) && $_POST['format'] == 'blm_remote' ) ? 
                true : 
                ( ( isset($options['format']) && $options['format'] == 'blm_remote' ) ? true : false );
    ?>
    <table class="form-table">
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="format_blm_remote"><?php _e( 'BLM File - Remote URL', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <label>
                    <input type="radio" name="format" id="format_blm_remote" value="blm_remote"<?php echo ( $this_format ? ' checked' : '' ); ?> />
                    Select
                </label>
                <br><br>
                <div class="format-options" id="blm_remote_options" style="display:<?php echo ( $this_format ? 'block' : 'none' ); ?>">
                    <table>
                        <tr>
                            <td>
                                URL to BLM
                            </td>
                            <td>
                                <input type="text" name="blm_remote_url" id="blm_remote_url" placeholder="http://" value="<?php
                                    echo ( isset($_POST['blm_remote_url']) ) ? 
                                        $_POST['blm_remote_url'] : 
                                        ( isset($options['url']) ? $options['url'] : '' );
                                ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Only import updated properties
                            </td>
                            <td>
                                <input type="checkbox" name="blm_remote_only_updated" id="blm_remote_only_updated" value="yes" <?php
                                    echo ( isset($_POST['blm_remote_only_updated']) && $_POST['blm_remote_only_updated'] == 'yes' ) ? 
                                        'checked' : 
                                        ( ( ( isset($options['only_updated']) && $options['only_updated'] == 'yes' ) || !isset($options['only_updated']) ) ? 'checked' : '' );
                                ?> />
                            </td>
                        </tr>
                    </table>

                    <?php if ( !ini_get('allow_url_fopen') ) { ?>
                    <span class="description">
                        <p style="color:#900; font-weight:700;"><?php _e('BLMs aren\'t provided with latitude and longitude co-ordinates. As a result we make a geocoding request to obtain them. You server does not have the \'allow_url_fopen\' setting activated so we won\'t be able to obtain coordinates for properties.', 'propertyhive'); ?></p>
                    </span>
                    <?php } ?>
                </div>

            </td>
        </tr>
    </table>

    <?php
        $this_format =
            ( isset($_POST['format']) && $_POST['format'] == 'xml_dezrez' ) ? 
                true : 
                ( ( isset($options['format']) && $options['format'] == 'xml_dezrez' ) ? true : false );
    ?>

    <table class="form-table">
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="format_xml_dezrez"><?php _e( 'DezrezOne XML', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <label>
                    <input type="radio" name="format" id="format_xml_dezrez" value="xml_dezrez"<?php echo ( $this_format ? ' checked' : '' ); ?> />
                    Select
                </label>
                <br><br>
                <div class="format-options" id="xml_dezrez_options" style="display:<?php echo ( $this_format ? 'block' : 'none' ); ?>">
                    <table>
                        <tr>
                            <td>
                                API Key
                            </td>
                            <td>
                                <input type="text" name="dezrez_xml_api_key" id="dezrez_xml_api_key" value="<?php
                                    echo ( isset($_POST['dezrez_xml_api_key']) ) ? 
                                        $_POST['dezrez_xml_api_key'] : 
                                        ( isset($options['api_key']) ? $options['api_key'] : '' );
                                ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Estate Agency ID
                            </td>
                            <td>
                                <input type="text" name="dezrez_xml_estate_agency_id" id="dezrez_xml_estate_agency_id" value="<?php
                                    echo ( isset($_POST['dezrez_xml_estate_agency_id']) ) ? 
                                        $_POST['dezrez_xml_estate_agency_id'] : 
                                        ( isset($options['eaid']) ? $options['eaid'] : '' );
                                ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Branch ID(s)
                            </td>
                            <td>
                                <input type="text" name="dezrez_xml_branch_ids" id="dezrez_xml_branch_ids" value="<?php
                                    echo ( isset($_POST['dezrez_xml_branch_ids']) ) ? 
                                        $_POST['dezrez_xml_branch_ids'] : 
                                        ( isset($options['branch_ids']) ? $options['branch_ids'] : '' );
                                ?>" />

                                <span class="description">
                                    <p><?php _e('A comma-delimited list of Dezrez branch IDs. Leave blank to import properties for all branches', 'propertyhive'); ?></p>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Only import updated properties
                            </td>
                            <td>
                                <input type="checkbox" name="dezrez_xml_only_updated" id="dezrez_xml_only_updated" value="yes" <?php
                                    echo ( isset($_POST['dezrez_xml_only_updated']) && $_POST['dezrez_xml_only_updated'] == 'yes' ) ? 
                                        'checked' : 
                                        ( ( ( isset($options['only_updated']) && $options['only_updated'] == 'yes' ) || !isset($options['only_updated']) ) ? 'checked' : '' );
                                ?> />
                            </td>
                        </tr>
                    </table>

                    <?php 
                        if ( !ini_get('allow_url_fopen') && !function_exists('curl_version') ) 
                        {
                            echo '<span style="color:#900">' . __( 'Either allow_url_fopen or cURL must be enabled on the server in order to use this format', 'propertyhive' ) . '</span>';
                        } 
                    ?>
                </div>

            </td>
        </tr>
    </table>

    <?php
        $this_format =
            ( isset($_POST['format']) && $_POST['format'] == 'json_dezrez' ) ? 
                true : 
                ( ( isset($options['format']) && $options['format'] == 'json_dezrez' ) ? true : false );
    ?>

    <table class="form-table">
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="format_json_dezrez"><?php _e( 'Dezrez Rezi JSON', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <label>
                    <input type="radio" name="format" id="format_json_dezrez" value="json_dezrez"<?php echo ( $this_format ? ' checked' : '' ); ?> />
                    Select
                </label>
                <br><br>
                <div class="format-options" id="json_dezrez_options" style="display:<?php echo ( $this_format ? 'block' : 'none' ); ?>">
                    <table>
                        <tr>
                            <td>
                                API Key
                            </td>
                            <td>
                                <input type="text" name="dezrez_json_api_key" id="dezrez_json_api_key" value="<?php
                                    echo ( isset($_POST['dezrez_json_api_key']) ) ? 
                                        $_POST['dezrez_json_api_key'] : 
                                        ( isset($options['api_key']) ? $options['api_key'] : '' );
                                ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Branch ID(s)
                            </td>
                            <td>
                                <input type="text" name="dezrez_json_branch_ids" id="dezrez_json_branch_ids" value="<?php
                                    echo ( isset($_POST['dezrez_json_branch_ids']) ) ? 
                                        $_POST['dezrez_json_branch_ids'] : 
                                        ( isset($options['branch_ids']) ? $options['branch_ids'] : '' );
                                ?>" />

                                <span class="description">
                                    <p><?php _e('A comma-delimited list of Dezrez branch IDs. Leave blank to import properties for all branches', 'propertyhive'); ?></p>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Tag(s)
                            </td>
                            <td>
                                <input type="text" name="dezrez_json_tags" id="dezrez_json_tags" value="<?php
                                    echo ( isset($_POST['dezrez_json_tags']) ) ? 
                                        $_POST['dezrez_json_tags'] : 
                                        ( isset($options['tags']) ? $options['tags'] : '' );
                                ?>" />

                                <span class="description">
                                    <p><?php _e('A comma-delimited list of Agent Defined tags within Dezrez. Leave blank if not wanting to filter properties by tag', 'propertyhive'); ?></p>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Only import updated properties
                            </td>
                            <td>
                                <input type="checkbox" name="dezrez_json_only_updated" id="dezrez_json_only_updated" value="yes" <?php
                                    echo ( isset($_POST['dezrez_json_only_updated']) && $_POST['dezrez_json_only_updated'] == 'yes' ) ? 
                                        'checked' : 
                                        ( ( ( isset($options['only_updated']) && $options['only_updated'] == 'yes' ) || !isset($options['only_updated']) ) ? 'checked' : '' );
                                ?> />
                            </td>
                        </tr>
                    </table>

                    <?php 
                        if ( !function_exists('curl_version') ) 
                        {
                            echo '<span style="color:#900">' . __( 'cURL must be enabled on the server in order to use this format', 'propertyhive' ) . '</span>';
                        } 
                    ?>
                </div>

            </td>
        </tr>
    </table>

    <?php
        $this_format =
            ( isset($_POST['format']) && $_POST['format'] == 'xml_expertagent' ) ? 
                true : 
                ( ( isset($options['format']) && $options['format'] == 'xml_expertagent' ) ? true : false );
    ?>

    <table class="form-table">
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="format_xml_expertagent"><?php _e( 'ExpertAgent XML', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <label>
                    <input type="radio" name="format" id="format_xml_expertagent" value="xml_expertagent"<?php echo ( $this_format ? ' checked' : '' ); ?> />
                    Select
                </label>
                <br><br>
                <div class="format-options" id="xml_expertagent_options" style="display:<?php echo ( $this_format ? 'block' : 'none' ); ?>">
                    <table>
                        <tr>
                            <td>
                                FTP Host
                            </td>
                            <td>
                                <input type="text" name="expertagent_xml_ftp_host" id="expertagent_xml_ftp_host" value="<?php
                                    echo ( isset($_POST['expertagent_xml_ftp_host']) ) ? 
                                        $_POST['expertagent_xml_ftp_host'] : 
                                        ( isset($options['ftp_host']) ? $options['ftp_host'] : 'ftp.expertagent.co.uk' );
                                ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                FTP Username
                            </td>
                            <td>
                                <input type="text" name="expertagent_xml_ftp_user" id="expertagent_xml_ftp_user" value="<?php
                                    echo ( isset($_POST['expertagent_xml_ftp_user']) ) ? 
                                        $_POST['expertagent_xml_ftp_user'] : 
                                        ( isset($options['ftp_user']) ? $options['ftp_user'] : '' );
                                ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                FTP Password
                            </td>
                            <td>
                                <input type="text" name="expertagent_xml_ftp_pass" id="expertagent_xml_ftp_pass" value="<?php
                                    echo ( isset($_POST['expertagent_xml_ftp_pass']) ) ? 
                                        $_POST['expertagent_xml_ftp_pass'] : 
                                        ( isset($options['ftp_pass']) ? $options['ftp_pass'] : '' );
                                ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                FTP Directory
                            </td>
                            <td>
                                <input type="text" name="expertagent_xml_ftp_dir" id="expertagent_xml_ftp_dir" value="<?php
                                    echo ( isset($_POST['expertagent_xml_ftp_dir']) ) ? 
                                        $_POST['expertagent_xml_ftp_dir'] : 
                                        ( isset($options['ftp_dir']) ? $options['ftp_dir'] : '' );
                                ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Use FTP Passive Mode
                            </td>
                            <td>
                                <input type="checkbox" name="expertagent_xml_ftp_passive" id="expertagent_xml_ftp_passive" value="1" <?php
                                    echo ( 
                                        ( isset($_POST['expertagent_xml_ftp_passive']) && $_POST['expertagent_xml_ftp_passive'] == '1' )
                                        ||
                                        ( !isset($_POST['expertagent_xml_ftp_passive']) && ( isset($options['ftp_passive']) && $options['ftp_passive'] == '1' ) )
                                    ) ? 
                                        'checked' : 
                                        '';
                                ?> />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                XML File Name
                            </td>
                            <td>
                                <input type="text" name="expertagent_xml_filename" id="expertagent_xml_filename" value="<?php
                                    echo ( isset($_POST['expertagent_xml_filename']) ) ? 
                                        $_POST['expertagent_xml_filename'] : 
                                        ( isset($options['xml_filename']) ? $options['xml_filename'] : 'properties.xml' );
                                ?>" />
                            </td>
                        </tr>
                    </table>
                </div>

            </td>
        </tr>
    </table>

    <?php
        $this_format =
            ( isset($_POST['format']) && $_POST['format'] == 'xml_jupix' ) ? 
                true : 
                ( ( isset($options['format']) && $options['format'] == 'xml_jupix' ) ? true : false );
    ?>

    <table class="form-table">
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="format_xml_jupix"><?php _e( 'Jupix XML', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <label>
                    <input type="radio" name="format" id="format_xml_jupix" value="xml_jupix"<?php echo ( $this_format ? ' checked' : '' ); ?> />
                    Select
                </label>
                <br><br>
                <div class="format-options" id="xml_jupix_options" style="display:<?php echo ( $this_format ? 'block' : 'none' ); ?>">
                    <table>
                        <tr>
                            <td>
                                XML URL
                            </td>
                            <td>
                                <input placeholder="http://" type="text" name="jupix_xml_url" id="jupix_xml_url" value="<?php
                                    echo ( isset($_POST['jupix_xml_url']) ) ? 
                                        $_POST['jupix_xml_url'] : 
                                        ( isset($options['xml_url']) ? $options['xml_url'] : '' );
                                ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Only import updated properties
                            </td>
                            <td>
                                <input type="checkbox" name="jupix_xml_only_updated" id="jupix_xml_only_updated" value="yes" <?php
                                    echo ( isset($_POST['jupix_xml_only_updated']) && $_POST['jupix_xml_only_updated'] == 'yes' ) ? 
                                        'checked' : 
                                        ( ( isset($options['only_updated']) && $options['only_updated'] == 'yes' ) ? 'checked' : '' );
                                ?> />
                            </td>
                        </tr>
                    </table>

                    <?php 
                        if ( !ini_get('allow_url_fopen') && !function_exists('curl_version') ) 
                        {
                            echo '<span style="color:#900">' . __( 'Either allow_url_fopen or cURL must be enabled on the server in order to use this format', 'propertyhive' ) . '</span>';
                        } 
                    ?>
                </div>

            </td>
        </tr>
    </table>

    <?php
        $this_format =
            ( isset($_POST['format']) && $_POST['format'] == 'xml_vebra_api' ) ? 
                true : 
                ( ( isset($options['format']) && $options['format'] == 'xml_vebra_api' ) ? true : false );
    ?>

    <table class="form-table">
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="format_xml_vebra_api"><?php _e( 'Vebra API XML', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <label>
                    <input type="radio" name="format" id="format_xml_vebra_api" value="xml_vebra_api"<?php echo ( $this_format ? ' checked' : '' ); ?> />
                    Select
                </label>
                <br><br>
                <div class="format-options" id="xml_vebra_api_options" style="display:<?php echo ( $this_format ? 'block' : 'none' ); ?>">
                    <table>
                        <tr>
                            <td>
                                Username
                            </td>
                            <td>
                                <input type="text" name="vebra_api_xml_username" id="vebra_api_xml_username" value="<?php
                                    echo ( isset($_POST['vebra_api_xml_username']) ) ? 
                                        $_POST['vebra_api_xml_username'] : 
                                        ( isset($options['username']) ? $options['username'] : '' );
                                ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Password
                            </td>
                            <td>
                                <input type="text" name="vebra_api_xml_password" id="vebra_api_xml_password" value="<?php
                                    echo ( isset($_POST['vebra_api_xml_password']) ) ? 
                                        $_POST['vebra_api_xml_password'] : 
                                        ( isset($options['password']) ? $options['password'] : '' );
                                ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Datafeed ID
                            </td>
                            <td>
                                <input type="text" name="vebra_api_xml_datafeed_id" id="vebra_api_xml_datafeed_id" value="<?php
                                    echo ( isset($_POST['vebra_api_xml_datafeed_id']) ) ? 
                                        $_POST['vebra_api_xml_datafeed_id'] : 
                                        ( isset($options['datafeed_id']) ? $options['datafeed_id'] : '' );
                                ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Only import updated properties
                            </td>
                            <td>
                                <input type="checkbox" name="vebra_api_xml_only_updated" id="vebra_api_xml_only_updated" value="yes" <?php
                                    echo ( isset($_POST['vebra_api_xml_only_updated']) && $_POST['vebra_api_xml_only_updated'] == 'yes' ) ? 
                                        'checked' : 
                                        ( ( ( isset($options['only_updated']) && $options['only_updated'] == 'yes' ) || !isset($options['only_updated']) ) ? 'checked' : '' );
                                ?> />
                            </td>
                        </tr>
                    </table>

                    <?php 
                        if ( !function_exists('curl_version') ) 
                        {
                            echo '<span style="color:#900">' . __( 'cURL must be enabled on the server in order to use this format', 'propertyhive' ) . '</span>';
                        } 
                    ?>
                    
                </div>

            </td>
        </tr>
    </table>

    <?php
        $this_format =
            ( isset($_POST['format']) && $_POST['format'] == 'xml_acquaint' ) ? 
                true : 
                ( ( isset($options['format']) && $options['format'] == 'xml_acquaint' ) ? true : false );
    ?>

    <table class="form-table">
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="format_xml_acquaint"><?php _e( 'Acquaint XML', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <label>
                    <input type="radio" name="format" id="format_xml_acquaint" value="xml_acquaint"<?php echo ( $this_format ? ' checked' : '' ); ?> />
                    Select
                </label>
                <br><br>
                <div class="format-options" id="xml_acquaint_options" style="display:<?php echo ( $this_format ? 'block' : 'none' ); ?>">
                    <table>
                        <tr>
                            <td>
                                XML URL(s)
                            </td>
                            <td>
                                <input placeholder="http://" type="text" name="acquaint_xml_url" id="acquaint_xml_url" value="<?php
                                    echo ( isset($_POST['acquaint_xml_url']) ) ? 
                                        $_POST['acquaint_xml_url'] : 
                                        ( isset($options['xml_url']) ? $options['xml_url'] : 'http://www.acquaintcrm.co.uk/datafeeds/standardxml/' );
                                ?>" />

                                <span class="description">
                                    <p><?php _e('A comma separated list of URL\'s to the acquaint XML data', 'propertyhive'); ?></p>
                                </span>
                            </td>
                        </tr>
                    </table>

                    <?php 
                        if ( !ini_get('allow_url_fopen') && !function_exists('curl_version') ) 
                        {
                            echo '<span style="color:#900">' . __( 'Either allow_url_fopen or cURL must be enabled on the server in order to use this format', 'propertyhive' ) . '</span>';
                        } 
                    ?>
                </div>

            </td>
        </tr>
    </table>

    <?php
        $this_format =
            ( isset($_POST['format']) && $_POST['format'] == 'xml_citylets' ) ? 
                true : 
                ( ( isset($options['format']) && $options['format'] == 'xml_citylets' ) ? true : false );
    ?>

    <table class="form-table">
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="format_xml_citylets"><?php _e( 'Citylets XML', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <label>
                    <input type="radio" name="format" id="format_xml_citylets" value="xml_citylets"<?php echo ( $this_format ? ' checked' : '' ); ?> />
                    Select
                </label>
                <br><br>
                <div class="format-options" id="xml_citylets_options" style="display:<?php echo ( $this_format ? 'block' : 'none' ); ?>">
                    <table>
                        <tr>
                            <td>
                                XML URL
                            </td>
                            <td>
                                <input placeholder="http://" type="text" name="citylets_xml_url" id="citylets_xml_url" value="<?php
                                    echo ( isset($_POST['citylets_xml_url']) ) ? 
                                        $_POST['citylets_xml_url'] : 
                                        ( isset($options['xml_url']) ? $options['xml_url'] : '' );
                                ?>" />
                            </td>
                        </tr>
                    </table>

                    <?php 
                        if ( !ini_get('allow_url_fopen') && !function_exists('curl_version') ) 
                        {
                            echo '<span style="color:#900">' . __( 'Either allow_url_fopen or cURL must be enabled on the server in order to use this format', 'propertyhive' ) . '</span>';
                        } 
                    ?>
                </div>

            </td>
        </tr>
    </table>

    <?php
        $this_format =
            ( isset($_POST['format']) && $_POST['format'] == 'xml_sme_professional' ) ? 
                true : 
                ( ( isset($options['format']) && $options['format'] == 'xml_sme_professional' ) ? true : false );
    ?>

    <table class="form-table">
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="format_xml_sme_professional"><?php _e( 'SME Professional XML', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <label>
                    <input type="radio" name="format" id="format_xml_sme_professional" value="xml_sme_professional"<?php echo ( $this_format ? ' checked' : '' ); ?> />
                    Select
                </label>
                <br><br>
                <div class="format-options" id="xml_sme_professional_options" style="display:<?php echo ( $this_format ? 'block' : 'none' ); ?>">
                    <table>
                        <tr>
                            <td>
                                XML URL
                            </td>
                            <td>
                                <input placeholder="http://" type="text" name="sme_professional_xml_url" id="sme_professional_xml_url" value="<?php
                                    echo ( isset($_POST['sme_professional_xml_url']) ) ? 
                                        $_POST['sme_professional_xml_url'] : 
                                        ( isset($options['xml_url']) ? $options['xml_url'] : '' );
                                ?>" />
                            </td>
                        </tr>
                    </table>

                    <?php 
                        if ( !ini_get('allow_url_fopen') && !function_exists('curl_version') ) 
                        {
                            echo '<span style="color:#900">' . __( 'Either allow_url_fopen or cURL must be enabled on the server in order to use this format', 'propertyhive' ) . '</span>';
                        } 
                    ?>
                </div>

            </td>
        </tr>
    </table>

    <?php
        $this_format =
            ( isset($_POST['format']) && $_POST['format'] == 'thesaurus' ) ? 
                true : 
                ( ( isset($options['format']) && $options['format'] == 'thesaurus' ) ? true : false );
    ?>

    <table class="form-table">
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="format_thesaurus"><?php _e( 'MRI (Thesaurus Format)', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <label>
                    <input type="radio" name="format" id="format_thesaurus" value="thesaurus"<?php echo ( $this_format ? ' checked' : '' ); ?> />
                    Select
                </label>
                <br><br>
                <div class="format-options" id="thesaurus_options" style="display:<?php echo ( $this_format ? 'block' : 'none' ); ?>">
                    <table>
                        <tr>
                            <td>
                                FTP Host
                            </td>
                            <td>
                                <input type="text" name="thesaurus_ftp_host" id="thesaurus_ftp_host" value="<?php
                                    echo ( isset($_POST['thesaurus_ftp_host']) ) ? 
                                        $_POST['thesaurus_ftp_host'] : 
                                        ( isset($options['ftp_host']) ? $options['ftp_host'] : '' );
                                ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                FTP Username
                            </td>
                            <td>
                                <input type="text" name="thesaurus_ftp_user" id="thesaurus_ftp_user" value="<?php
                                    echo ( isset($_POST['thesaurus_ftp_user']) ) ? 
                                        $_POST['thesaurus_ftp_user'] : 
                                        ( isset($options['ftp_user']) ? $options['ftp_user'] : '' );
                                ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                FTP Password
                            </td>
                            <td>
                                <input type="text" name="thesaurus_ftp_pass" id="thesaurus_ftp_pass" value="<?php
                                    echo ( isset($_POST['thesaurus_ftp_pass']) ) ? 
                                        $_POST['thesaurus_ftp_pass'] : 
                                        ( isset($options['ftp_pass']) ? $options['ftp_pass'] : '' );
                                ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Data File FTP Directory
                            </td>
                            <td>
                                <input type="text" name="thesaurus_ftp_dir" id="thesaurus_ftp_dir" value="<?php
                                    echo ( isset($_POST['thesaurus_ftp_dir']) ) ? 
                                        $_POST['expertagent_xml_ftp_dir'] : 
                                        ( isset($options['ftp_dir']) ? $options['ftp_dir'] : '/data' );
                                ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Image FTP Directory
                            </td>
                            <td>
                                <input type="text" name="thesaurus_image_ftp_dir" id="thesaurus_image_ftp_dir" value="<?php
                                    echo ( isset($_POST['thesaurus_image_ftp_dir']) ) ? 
                                        $_POST['thesaurus_image_ftp_dir'] : 
                                        ( isset($options['image_ftp_dir']) ? $options['image_ftp_dir'] : '/images_l,/images_b' );
                                ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Brochure FTP Directory
                            </td>
                            <td>
                                <input type="text" name="thesaurus_brochure_ftp_dir" id="thesaurus_brochure_ftp_dir" value="<?php
                                    echo ( isset($_POST['thesaurus_brochure_ftp_dir']) ) ? 
                                        $_POST['thesaurus_brochure_ftp_dir'] : 
                                        ( isset($options['brochure_ftp_dir']) ? $options['brochure_ftp_dir'] : '/pdf' );
                                ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Use FTP Passive Mode
                            </td>
                            <td>
                                <input type="checkbox" name="thesaurus_ftp_passive" id="thesaurus_ftp_passive" value="1" <?php
                                    echo ( 
                                        ( isset($_POST['thesaurus_ftp_passive']) && $_POST['thesaurus_ftp_passive'] == '1' )
                                        ||
                                        ( !isset($_POST['thesaurus_ftp_passive']) && ( isset($options['ftp_passive']) && $options['ftp_passive'] == '1' ) )
                                    ) ? 
                                        'checked' : 
                                        '';
                                ?> />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Data File Name
                            </td>
                            <td>
                                <input type="text" name="thesaurus_filename" id="thesaurus_filename" value="<?php
                                    echo ( isset($_POST['thesaurus_filename']) ) ? 
                                        $_POST['thesaurus_filename'] : 
                                        ( isset($options['filename']) ? $options['filename'] : 'data.file' );
                                ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Only import updated properties
                            </td>
                            <td>
                                <input type="checkbox" name="thesaurus_only_updated" id="thesaurus_only_updated" value="yes" <?php
                                    echo ( isset($_POST['thesaurus_only_updated']) && $_POST['thesaurus_only_updated'] == 'yes' ) ? 
                                        'checked' : 
                                        ( ( ( isset($options['only_updated']) && $options['only_updated'] == 'yes' ) ) ? 'checked' : '' );
                                ?> />
                            </td>
                        </tr>
                    </table>
                </div>

            </td>
        </tr>
    </table>

    <?php
        $this_format =
            ( isset($_POST['format']) && $_POST['format'] == 'xml_mri' ) ? 
                true : 
                ( ( isset($options['format']) && $options['format'] == 'xml_mri' ) ? true : false );
    ?>
    <table class="form-table">
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="format_xml_mri"><?php _e( 'MRI (XML Format)', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <label>
                    <input type="radio" name="format" id="format_xml_mri" value="xml_mri"<?php echo ( $this_format ? ' checked' : '' ); ?> />
                    Select
                </label>
                <br><br>
                <div class="format-options" id="xml_mri_options" style="display:<?php echo ( $this_format ? 'block' : 'none' ); ?>">
                    <table>
                        <tr>
                            <td>
                                URL
                            </td>
                            <td>
                                <input type="text" name="mri_xml_url" id="mri_xml_url" value="<?php
                                    echo ( isset($_POST['mri_xml_url']) ) ? 
                                        $_POST['mri_xml_url'] : 
                                        ( isset($options['url']) ? $options['url'] : '' );
                                ?>" placeholder="https://v4.salesandlettings.online/pls/{client}/aspasia_search.html" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Password
                            </td>
                            <td>
                                <input type="text" name="mri_xml_password" id="mri_xml_password" value="<?php
                                    echo ( isset($_POST['mri_xml_password']) ) ? 
                                        $_POST['mri_xml_password'] : 
                                        ( isset($options['password']) ? $options['password'] : '' );
                                ?>" />
                            </td>
                        </tr>
                    </table>
                </div>

            </td>
        </tr>
    </table>

    <?php
        $this_format =
            ( isset($_POST['format']) && $_POST['format'] == 'jet' ) ? 
                true : 
                ( ( isset($options['format']) && $options['format'] == 'jet' ) ? true : false );
    ?>

    <table class="form-table">
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="format_jet"><?php _e( 'Reapit / JET', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <label>
                    <input type="radio" name="format" id="format_jet" value="jet"<?php echo ( $this_format ? ' checked' : '' ); ?> />
                    Select
                </label>
                <br><br>
                <div class="format-options" id="jet_options" style="display:<?php echo ( $this_format ? 'block' : 'none' ); ?>">
                    <table>
                        <tr>
                            <td>
                                URL
                            </td>
                            <td>
                                <input type="text" name="jet_url" id="jet_url" value="<?php
                                    echo ( isset($_POST['jet_url']) ) ? 
                                        $_POST['jet_url'] : 
                                        ( isset($options['url']) ? $options['url'] : 'https://webservice.jetsoftware.co.uk/<ID HERE>/?wsdl' );
                                ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Username
                            </td>
                            <td>
                                <input type="text" name="jet_user" id="jet_user" value="<?php
                                    echo ( isset($_POST['jet_user']) ) ? 
                                        $_POST['jet_user'] : 
                                        ( isset($options['user']) ? $options['user'] : '' );
                                ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Password
                            </td>
                            <td>
                                <input type="text" name="jet_pass" id="jet_pass" value="<?php
                                    echo ( isset($_POST['jet_pass']) ) ? 
                                        $_POST['jet_pass'] : 
                                        ( isset($options['pass']) ? $options['pass'] : '' );
                                ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Only import updated properties
                            </td>
                            <td>
                                <input type="checkbox" name="jet_only_updated" id="jet_only_updated" value="yes" <?php
                                    echo ( isset($_POST['jet_only_updated']) && $_POST['jet_only_updated'] == 'yes' ) ? 
                                        'checked' : 
                                        ( ( isset($options['only_updated']) && $options['only_updated'] == 'yes' ) ? 'checked' : '' );
                                ?> />
                            </td>
                        </tr>
                    </table>

                    <?php if ( !class_exists('SOAPClient') ) { ?>
                    <span class="description">
                        <p style="color:#900; font-weight:700;"><?php _e('SOAP is required to import properties from Reapit / JET but doesn\'t appear to be enabled on your server.', 'propertyhive'); ?></p>
                    </span>
                    <?php } ?>
                </div>

            </td>
        </tr>
    </table>

    <?php
        $this_format =
            ( isset($_POST['format']) && $_POST['format'] == 'xml_rentman' ) ? 
                true : 
                ( ( isset($options['format']) && $options['format'] == 'xml_rentman' ) ? true : false );
    ?>
    <table class="form-table">
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="format_xml_rentman"><?php _e( 'Rentman XML', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <label>
                    <input type="radio" name="format" id="format_xml_rentman" value="xml_rentman"<?php echo ( $this_format ? ' checked' : '' ); ?> />
                    Select
                </label>
                <br><br>
                <div class="format-options" id="xml_rentman_options" style="display:<?php echo ( $this_format ? 'block' : 'none' ); ?>">
                    <table>
                        <tr>
                            <td>
                                Local Directory
                            </td>
                            <td>
                                <input type="text" name="rentman_xml_directory" id="rentman_xml_directory" value="<?php
                                    echo ( isset($_POST['rentman_xml_directory']) ) ? 
                                        $_POST['rentman_xml_directory'] : 
                                        ( isset($options['local_directory']) ? $options['local_directory'] : $uploads_dir );
                                ?>" />
                            </td>
                        </tr>
                    </table>

                    <span class="description">
                        <p><?php _e('When the import runs it will parse all XML files found in the local directory in date order', 'propertyhive'); ?></p>
                    </span>
                </div>

            </td>
        </tr>
    </table>

    <?php
        $this_format =
            ( isset($_POST['format']) && $_POST['format'] == 'json_letmc' ) ? 
                true : 
                ( ( isset($options['format']) && $options['format'] == 'json_letmc' ) ? true : false );
    ?>

    <table class="form-table">
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="format_json_letmc"><?php _e( 'AgentOS API', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <label>
                    <input type="radio" name="format" id="format_json_letmc" value="json_letmc"<?php echo ( $this_format ? ' checked' : '' ); ?> />
                    Select
                </label>
                <br><br>
                <div class="format-options" id="json_letmc_options" style="display:<?php echo ( $this_format ? 'block' : 'none' ); ?>">
                    <table>
                        <tr>
                            <td>
                                API Key
                            </td>
                            <td>
                                <input type="text" name="letmc_json_api_key" id="letmc_json_api_key" value="<?php
                                    echo ( isset($_POST['letmc_json_api_key']) ) ? 
                                        $_POST['letmc_json_api_key'] : 
                                        ( isset($options['api_key']) ? $options['api_key'] : '' );
                                ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Short Name
                            </td>
                            <td>
                                <input type="text" name="letmc_json_short_name" id="letmc_json_short_name" value="<?php
                                    echo ( isset($_POST['letmc_json_short_name']) ) ? 
                                        $_POST['letmc_json_short_name'] : 
                                        ( isset($options['short_name']) ? $options['short_name'] : '' );
                                ?>" />
                            </td>
                        </tr>
                    </table>

                    <?php 
                        echo '<span style="color:#900">' . __( 'AgentOS are very strict on the number of requests made per minute. As it takes so many individual requests to obtain the data we require, we\'ve had to add pauses to prevent you hitting this throttling limit. As a result, imports from AgentOS may take a while and therefore you\'ll likely need to increase the timeout limit on your server.' , 'propertyhive' ) . '</span>';
                        
                    ?>
                </div>

            </td>
        </tr>
    </table>

    <?php
        $this_format =
            ( isset($_POST['format']) && $_POST['format'] == 'reaxml_local' ) ? 
                true : 
                ( ( isset($options['format']) && $options['format'] == 'reaxml_local' ) ? true : false );
    ?>
    <table class="form-table">
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="format_reaxml_local"><?php _e( 'REAXML - Local Directory', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <label>
                    <input type="radio" name="format" id="format_reaxml_local" value="reaxml_local"<?php echo ( $this_format ? ' checked' : '' ); ?> />
                    Select
                </label>
                <br><br>
                <div class="format-options" id="reaxml_local_options" style="display:<?php echo ( $this_format ? 'block' : 'none' ); ?>">
                    <table>
                        <tr>
                            <td>
                                Local Directory
                            </td>
                            <td>
                                <input type="text" name="reaxml_local_directory" id="reaxml_local_directory" value="<?php
                                    echo ( isset($_POST['reaxml_local_directory']) ) ? 
                                        $_POST['reaxml_local_directory'] : 
                                        ( isset($options['local_directory']) ? $options['local_directory'] : $uploads_dir );
                                ?>" />
                            </td>
                        </tr>
                    </table>

                    <span class="description">
                        <p><?php _e('When the import runs it will parse all REAXML files found in the local directory in date order', 'propertyhive'); ?></p>
                    </span>
                </div>

            </td>
        </tr>
    </table>

    <?php
        $this_format =
            ( isset($_POST['format']) && $_POST['format'] == 'xml_10ninety' ) ? 
                true : 
                ( ( isset($options['format']) && $options['format'] == 'xml_10ninety' ) ? true : false );
    ?>

    <table class="form-table">
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="format_xml_10ninety"><?php _e( '10ninety XML', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <label>
                    <input type="radio" name="format" id="format_xml_10ninety" value="xml_10ninety"<?php echo ( $this_format ? ' checked' : '' ); ?> />
                    Select
                </label>
                <br><br>
                <div class="format-options" id="xml_10ninety_options" style="display:<?php echo ( $this_format ? 'block' : 'none' ); ?>">
                    <table>
                        <tr>
                            <td>
                                XML URL
                            </td>
                            <td>
                                <input placeholder="http://" type="text" name="10ninety_xml_url" id="10ninety_xml_url" value="<?php
                                    echo ( isset($_POST['10ninety_xml_url']) ) ? 
                                        $_POST['10ninety_xml_url'] : 
                                        ( isset($options['xml_url']) ? $options['xml_url'] : '' );
                                ?>" />
                            </td>
                        </tr>
                    </table>

                    <?php 
                        if ( !ini_get('allow_url_fopen') && !function_exists('curl_version') ) 
                        {
                            echo '<span style="color:#900">' . __( 'Either allow_url_fopen or cURL must be enabled on the server in order to use this format', 'propertyhive' ) . '</span>';
                        } 
                    ?>
                </div>

            </td>
        </tr>
    </table>

    <?php
        $this_format =
            ( isset($_POST['format']) && $_POST['format'] == 'xml_domus' ) ? 
                true : 
                ( ( isset($options['format']) && $options['format'] == 'xml_domus' ) ? true : false );
    ?>

    <table class="form-table">
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="format_xml_domus"><?php _e( 'Domus API', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <label>
                    <input type="radio" name="format" id="format_xml_domus" value="xml_domus"<?php echo ( $this_format ? ' checked' : '' ); ?> />
                    Select
                </label>
                <br><br>
                <div class="format-options" id="xml_domus_options" style="display:<?php echo ( $this_format ? 'block' : 'none' ); ?>">
                    <table>
                        <tr>
                            <td>
                                XML URL
                            </td>
                            <td>
                                <input placeholder="http://mysite.domus.net/site/go/api/" type="text" name="domus_xml_url" id="domus_xml_url" value="<?php
                                    echo ( isset($_POST['domus_xml_url']) ) ? 
                                        $_POST['domus_xml_url'] : 
                                        ( isset($options['xml_url']) ? $options['xml_url'] : '' );
                                ?>" />
                            </td>
                        </tr>
                    </table>

                    <?php 
                        if ( !ini_get('allow_url_fopen') && !function_exists('curl_version') ) 
                        {
                            echo '<span style="color:#900">' . __( 'Either allow_url_fopen or cURL must be enabled on the server in order to use this format', 'propertyhive' ) . '</span>';
                        } 
                    ?>
                </div>

            </td>
        </tr>
    </table>

    <?php
        $this_format =
            ( isset($_POST['format']) && $_POST['format'] == 'json_realla' ) ? 
                true : 
                ( ( isset($options['format']) && $options['format'] == 'json_realla' ) ? true : false );
    ?>

    <table class="form-table">
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="format_json_realla"><?php _e( 'Realla API', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <label>
                    <input type="radio" name="format" id="format_json_realla" value="json_realla"<?php echo ( $this_format ? ' checked' : '' ); ?> />
                    Select
                </label>
                <br><br>
                <div class="format-options" id="json_realla_options" style="display:<?php echo ( $this_format ? 'block' : 'none' ); ?>">
                    <table>
                        <tr>
                            <td>
                                API Key
                            </td>
                            <td>
                                <input type="text" name="realla_api_key" id="realla_api_key" value="<?php
                                    echo ( isset($_POST['realla_api_key']) ) ? 
                                        $_POST['realla_api_key'] : 
                                        ( isset($options['api_key']) ? $options['api_key'] : '' );
                                ?>" />
                            </td>
                        </tr>
                    </table>
                </div>

            </td>
        </tr>
    </table>

    <?php
        $this_format =
            ( isset($_POST['format']) && $_POST['format'] == 'json_agency_pilot' ) ? 
                true : 
                ( ( isset($options['format']) && $options['format'] == 'json_agency_pilot' ) ? true : false );
    ?>

    <table class="form-table">
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="format_json_agency_pilot"><?php _e( 'Agency Pilot JSON', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <label>
                    <input type="radio" name="format" id="format_json_agency_pilot" value="json_agency_pilot"<?php echo ( $this_format ? ' checked' : '' ); ?> />
                    Select
                </label>
                <br><br>
                <div class="format-options" id="json_agency_pilot_options" style="display:<?php echo ( $this_format ? 'block' : 'none' ); ?>">
                    <table>
                        <tr>
                            <td>
                                URL
                            </td>
                            <td>
                                <input type="text" name="agency_pilot_url" id="agency_pilot_url" placeholder="sitename.agencypilot.com" value="<?php
                                    echo ( isset($_POST['agency_pilot_url']) ) ? 
                                        $_POST['agency_pilot_url'] : 
                                        ( isset($options['url']) ? $options['url'] : '' );
                                ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Password
                            </td>
                            <td>
                                <input type="text" name="agency_pilot_password" id="agency_pilot_password" value="<?php
                                    echo ( isset($_POST['agency_pilot_password']) ) ? 
                                        $_POST['agency_pilot_password'] : 
                                        ( isset($options['password']) ? $options['password'] : '' );
                                ?>" />
                            </td>
                        </tr>
                    </table>
                </div>

            </td>
        </tr>
    </table>

    <?php
        $this_format =
            ( isset($_POST['format']) && $_POST['format'] == 'api_agency_pilot' ) ? 
                true : 
                ( ( isset($options['format']) && $options['format'] == 'api_agency_pilot' ) ? true : false );
    ?>

    <table class="form-table">
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="format_api_agency_pilot"><?php _e( 'Agency Pilot REST API', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <label>
                    <input type="radio" name="format" id="format_api_agency_pilot" value="api_agency_pilot"<?php echo ( $this_format ? ' checked' : '' ); ?> />
                    Select
                </label>
                <br><br>
                <div class="format-options" id="api_agency_pilot_options" style="display:<?php echo ( $this_format ? 'block' : 'none' ); ?>">
                    <table>
                        <tr>
                            <td>
                                URL
                            </td>
                            <td>
                                <input type="text" name="agency_pilot_api_url" id="agency_pilot_api_url" placeholder="https://{site}.agencypilot.com" value="<?php
                                    echo ( isset($_POST['agency_pilot_api_url']) ) ? 
                                        $_POST['agency_pilot_api_url'] : 
                                        ( isset($options['url']) ? $options['url'] : '' );
                                ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Client ID
                            </td>
                            <td>
                                <input type="text" name="agency_pilot_api_client_id" id="agency_pilot_api_client_id" placeholder="" value="<?php
                                    echo ( isset($_POST['agency_pilot_api_client_id']) ) ? 
                                        $_POST['agency_pilot_api_client_id'] : 
                                        ( isset($options['client_id']) ? $options['client_id'] : '' );
                                ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Client Secret
                            </td>
                            <td>
                                <input type="text" name="agency_pilot_api_client_secret" id="agency_pilot_api_client_secret" value="<?php
                                    echo ( isset($_POST['agency_pilot_api_client_secret']) ) ? 
                                        $_POST['agency_pilot_api_client_secret'] : 
                                        ( isset($options['client_secret']) ? $options['client_secret'] : '' );
                                ?>" />
                            </td>
                        </tr>
                    </table>
                </div>

            </td>
        </tr>
    </table>

    <?php
        $this_format =
            ( isset($_POST['format']) && $_POST['format'] == 'xml_propertyadd' ) ? 
                true : 
                ( ( isset($options['format']) && $options['format'] == 'xml_propertyadd' ) ? true : false );
    ?>

    <table class="form-table">
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="format_xml_propertyadd"><?php _e( 'PropertyADD XML', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <label>
                    <input type="radio" name="format" id="format_xml_propertyadd" value="xml_propertyadd"<?php echo ( $this_format ? ' checked' : '' ); ?> />
                    Select
                </label>
                <br><br>
                <div class="format-options" id="xml_propertyadd_options" style="display:<?php echo ( $this_format ? 'block' : 'none' ); ?>">
                    <table>
                        <tr>
                            <td>
                                URL
                            </td>
                            <td>
                                <input type="text" name="propertyadd_xml_url" id="propertyadd_xml_url" placeholder="https://pa.{your-site}.net" value="<?php
                                    echo ( isset($_POST['propertyadd_xml_url']) ) ? 
                                        $_POST['propertyadd_xml_url'] : 
                                        ( isset($options['url']) ? $options['url'] : '' );
                                ?>" />
                            </td>
                        </tr>
                    </table>
                </div>

            </td>
        </tr>
    </table>

    <?php
        $this_format =
            ( isset($_POST['format']) && $_POST['format'] == 'xml_gnomen' ) ? 
                true : 
                ( ( isset($options['format']) && $options['format'] == 'xml_gnomen' ) ? true : false );
    ?>

    <table class="form-table">
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="format_xml_gnomen"><?php _e( 'Gnomen XML', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <label>
                    <input type="radio" name="format" id="format_xml_gnomen" value="xml_gnomen"<?php echo ( $this_format ? ' checked' : '' ); ?> />
                    Select
                </label>
                <br><br>
                <div class="format-options" id="xml_gnomen_options" style="display:<?php echo ( $this_format ? 'block' : 'none' ); ?>">
                    <table>
                        <tr>
                            <td>
                                URL
                            </td>
                            <td>
                                <input type="text" name="gnomen_xml_url" id="gnomen_xml_url" placeholder="http://xml-feed.{your-site}.gnomen-europe.com/" value="<?php
                                    echo ( isset($_POST['gnomen_xml_url']) ) ? 
                                        $_POST['gnomen_xml_url'] : 
                                        ( isset($options['url']) ? $options['url'] : '' );
                                ?>" />
                            </td>
                        </tr>
                    </table>
                </div>

            </td>
        </tr>
    </table>

    <?php
        $this_format =
            ( isset($_POST['format']) && $_POST['format'] == 'xml_webedge' ) ? 
                true : 
                ( ( isset($options['format']) && $options['format'] == 'xml_webedge' ) ? true : false );
    ?>

    <table class="form-table">
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="format_xml_webedge"><?php _e( 'WebEDGE/Propertynews.com XML', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <label>
                    <input type="radio" name="format" id="format_xml_webedge" value="xml_webedge"<?php echo ( $this_format ? ' checked' : '' ); ?> />
                    Select
                </label>
                <br><br>
                <div class="format-options" id="xml_webedge_options" style="display:<?php echo ( $this_format ? 'block' : 'none' ); ?>">
                    <table>
                        <tr>
                            <td>
                                Shared Secret
                            </td>
                            <td>
                                <input type="text" name="webedge_xml_shared_secret" id="webedge_xml_shared_secret" placeholder="" value="<?php
                                    echo ( isset($_POST['webedge_xml_shared_secret']) ) ? 
                                        $_POST['webedge_xml_shared_secret'] : 
                                        ( isset($options['shared_secret']) ? $options['shared_secret'] : '' );
                                ?>" />
                            </td>
                        </tr>
                    </table>
                </div>

            </td>
        </tr>
    </table>

    <?php
        $this_format =
            ( isset($_POST['format']) && $_POST['format'] == 'xml_kyero' ) ? 
                true : 
                ( ( isset($options['format']) && $options['format'] == 'xml_kyero' ) ? true : false );
    ?>

    <table class="form-table">
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="format_xml_kyero"><?php _e( 'Kyero XML', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <label>
                    <input type="radio" name="format" id="format_xml_kyero" value="xml_kyero"<?php echo ( $this_format ? ' checked' : '' ); ?> />
                    Select
                </label>
                <br><br>
                <div class="format-options" id="xml_kyero_options" style="display:<?php echo ( $this_format ? 'block' : 'none' ); ?>">
                    <table>
                        <tr>
                            <td>
                                URL
                            </td>
                            <td>
                                <input type="text" name="kyero_xml_url" id="kyero_xml_url" placeholder="http://" value="<?php
                                    echo ( isset($_POST['kyero_xml_url']) ) ? 
                                        $_POST['kyero_xml_url'] : 
                                        ( isset($options['url']) ? $options['url'] : '' );
                                ?>" />
                            </td>
                        </tr>
                    </table>
                </div>

            </td>
        </tr>
    </table>

    <?php
        $this_format =
            ( isset($_POST['format']) && $_POST['format'] == 'xml_resales_online' ) ? 
                true : 
                ( ( isset($options['format']) && $options['format'] == 'xml_resales_online' ) ? true : false );
    ?>

    <table class="form-table">
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="format_xml_resales_online"><?php _e( 'ReSales Online XML', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <label>
                    <input type="radio" name="format" id="format_xml_resales_online" value="xml_resales_online"<?php echo ( $this_format ? ' checked' : '' ); ?> />
                    Select
                </label>
                <br><br>
                <div class="format-options" id="xml_resales_online_options" style="display:<?php echo ( $this_format ? 'block' : 'none' ); ?>">
                    <table>
                        <tr>
                            <td>
                                URL
                            </td>
                            <td>
                                <input type="text" name="resales_online_xml_url" id="resales_online_xml_url" placeholder="http://" value="<?php
                                    echo ( isset($_POST['resales_online_xml_url']) ) ? 
                                        $_POST['resales_online_xml_url'] : 
                                        ( isset($options['url']) ? $options['url'] : '' );
                                ?>" />
                            </td>
                        </tr>
                    </table>
                </div>

            </td>
        </tr>
    </table>

    <?php
        $this_format =
            ( isset($_POST['format']) && $_POST['format'] == 'json_loop' ) ? 
                true : 
                ( ( isset($options['format']) && $options['format'] == 'json_loop' ) ? true : false );
    ?>

    <table class="form-table">
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="format_json_loop"><?php _e( 'Loop API', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <label>
                    <input type="radio" name="format" id="format_json_loop" value="json_loop"<?php echo ( $this_format ? ' checked' : '' ); ?> />
                    Select
                </label>
                <br><br>
                <div class="format-options" id="json_loop_options" style="display:<?php echo ( $this_format ? 'block' : 'none' ); ?>">
                    <table>
                        <tr>
                            <td>
                                URL
                            </td>
                            <td>
                                <input type="text" name="loop_json_url" id="loop_json_url" placeholder="https://" value="<?php
                                    echo ( isset($_POST['loop_json_url']) ) ? 
                                        $_POST['loop_json_url'] : 
                                        ( isset($options['url']) ? $options['url'] : 'https://live-loop-publicapi.azurewebsites.net/api/v1/website/team-listings' );
                                ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                API Key
                            </td>
                            <td>
                                <input type="text" name="loop_json_client_id" id="loop_json_client_id" placeholder="" value="<?php
                                    echo ( isset($_POST['loop_json_client_id']) ) ? 
                                        $_POST['loop_json_client_id'] : 
                                        ( isset($options['client_id']) ? $options['client_id'] : '' );
                                ?>" />
                            </td>
                        </tr>
                    </table>
                </div>

            </td>
        </tr>
    </table>

    <?php
        $this_format =
            ( isset($_POST['format']) && $_POST['format'] == 'json_veco' ) ? 
                true : 
                ( ( isset($options['format']) && $options['format'] == 'json_veco' ) ? true : false );
    ?>

    <table class="form-table">
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="format_json_veco"><?php _e( 'Veco API', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <label>
                    <input type="radio" name="format" id="format_json_veco" value="json_veco"<?php echo ( $this_format ? ' checked' : '' ); ?> />
                    Select
                </label>
                <br><br>
                <div class="format-options" id="json_veco_options" style="display:<?php echo ( $this_format ? 'block' : 'none' ); ?>">
                    <table>
                        <tr>
                            <td>
                                Access Token
                            </td>
                            <td>
                                <input type="text" name="veco_json_access_token" id="veco_json_access_token" placeholder="" value="<?php
                                    echo ( isset($_POST['veco_json_access_token']) ) ? 
                                        $_POST['veco_json_access_token'] : 
                                        ( isset($options['access_token']) ? $options['access_token'] : '' );
                                ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Only import updated properties
                            </td>
                            <td>
                                <input type="checkbox" name="veco_json_only_updated" id="veco_json_only_updated" value="yes" <?php
                                    echo ( isset($_POST['veco_json_only_updated']) && $_POST['veco_json_only_updated'] == 'yes' ) ? 
                                        'checked' : 
                                        ( ( ( isset($options['only_updated']) && $options['only_updated'] == 'yes' ) || !isset($options['only_updated']) ) ? 'checked' : '' );
                                ?> />
                            </td>
                        </tr>
                    </table>
                </div>

            </td>
        </tr>
    </table>

    <?php
        $this_format =
            ( isset($_POST['format']) && $_POST['format'] == 'xml_estatesit' ) ? 
                true : 
                ( ( isset($options['format']) && $options['format'] == 'xml_estatesit' ) ? true : false );
    ?>
    <table class="form-table">
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="format_blm_local"><?php _e( 'Estates IT XML', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <label>
                    <input type="radio" name="format" id="format_xml_estatesit" value="xml_estatesit"<?php echo ( $this_format ? ' checked' : '' ); ?> />
                    Select
                </label>
                <br><br>
                <div class="format-options" id="xml_estatesit_options" style="display:<?php echo ( $this_format ? 'block' : 'none' ); ?>">
                    <table>
                        <tr>
                            <td>
                                Local Directory
                            </td>
                            <td>
                                <input type="text" name="xml_estatesit_local_directory" id="xml_estatesit_local_directory" value="<?php
                                    echo ( isset($_POST['xml_estatesit_local_directory']) ) ? 
                                        $_POST['xml_estatesit_local_directory'] : 
                                        ( isset($options['local_directory']) ? $options['local_directory'] : $uploads_dir );
                                ?>" />
                                <span class="description">
                                    <p><?php _e('When the import runs it will parse all XML files found in this local directory in date order', 'propertyhive'); ?></p>
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>

            </td>
        </tr>
    </table>

    <?php
        $this_format =
            ( isset($_POST['format']) && $_POST['format'] == 'xml_juvo' ) ? 
                true : 
                ( ( isset($options['format']) && $options['format'] == 'xml_juvo' ) ? true : false );
    ?>

    <table class="form-table">
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="format_xml_juvo"><?php _e( 'Juvo XML', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <label>
                    <input type="radio" name="format" id="format_xml_juvo" value="xml_juvo"<?php echo ( $this_format ? ' checked' : '' ); ?> />
                    Select
                </label>
                <br><br>
                <div class="format-options" id="xml_juvo_options" style="display:<?php echo ( $this_format ? 'block' : 'none' ); ?>">
                    <table>
                        <tr>
                            <td>
                                URL
                            </td>
                            <td>
                                <input type="text" name="juvo_xml_url" id="juvo_xml_url" placeholder="http://" value="<?php
                                    echo ( isset($_POST['juvo_xml_url']) ) ? 
                                        $_POST['juvo_xml_url'] : 
                                        ( isset($options['url']) ? $options['url'] : '' );
                                ?>" />
                            </td>
                        </tr>
                    </table>
                </div>

            </td>
        </tr>
    </table>

    <?php
        $this_format =
            ( isset($_POST['format']) && $_POST['format'] == 'json_utili' ) ? 
                true : 
                ( ( isset($options['format']) && $options['format'] == 'json_utili' ) ? true : false );
    ?>

    <table class="form-table">
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="format_json_utili"><?php _e( 'Utili API', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <label>
                    <input type="radio" name="format" id="format_json_utili" value="json_utili"<?php echo ( $this_format ? ' checked' : '' ); ?> />
                    Select
                </label>
                <br><br>
                <div class="format-options" id="json_utili_options" style="display:<?php echo ( $this_format ? 'block' : 'none' ); ?>">
                    <table>
                        <tr>
                            <td>
                                API Key
                            </td>
                            <td>
                                <input type="text" name="utili_json_api_key" id="utili_json_api_key" placeholder="" value="<?php
                                    echo ( isset($_POST['utili_json_api_key']) ) ? 
                                        $_POST['utili_json_api_key'] : 
                                        ( isset($options['api_key']) ? $options['api_key'] : '' );
                                ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Account Name
                            </td>
                            <td>
                                <input type="text" name="utili_json_account_name" id="utili_json_account_name" placeholder="" value="<?php
                                    echo ( isset($_POST['utili_json_account_name']) ) ? 
                                        $_POST['utili_json_account_name'] : 
                                        ( isset($options['account_name']) ? $options['account_name'] : '' );
                                ?>" />
                            </td>
                        </tr>
                    </table>
                </div>

            </td>
        </tr>
    </table>

    <?php
        $this_format =
            ( isset($_POST['format']) && $_POST['format'] == 'json_arthur' ) ? 
                true : 
                ( ( isset($options['format']) && $options['format'] == 'json_arthur' ) ? true : false );
    ?>

    <table class="form-table">
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="format_json_arthur"><?php _e( 'Arthur Online API', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <label>
                    <input type="radio" name="format" id="format_json_arthur" value="json_arthur"<?php echo ( $this_format ? ' checked' : '' ); ?> />
                    Select
                </label>
                <br><br>
                <div class="format-options" id="json_arthur_options" style="display:<?php echo ( $this_format ? 'block' : 'none' ); ?>">

                    <input type="hidden" name="arthur_json_access_token" value="<?php echo ( isset($options['access_token']) ? $options['access_token'] : '' ); ?>">
                    <input type="hidden" name="arthur_json_access_token_expires" value="<?php echo ( isset($options['access_token_expires']) ? $options['access_token_expires'] : '' ); ?>">
                    <input type="hidden" name="arthur_json_refresh_token" value="<?php echo ( isset($options['refresh_token']) ? $options['refresh_token'] : '' ); ?>">

                    <p>See details on how to create an application: <a href="https://apidocs.arthuronline.co.uk/apis/(apispage:create-application)" target="_blank">https://apidocs.arthuronline.co.uk/apis/(apispage:create-application)</a></p>

                    <table>
                        <tr>
                            <td>
                                Client ID
                            </td>
                            <td>
                                <input type="text" name="arthur_json_client_id" id="arthur_json_client_id" placeholder="" value="<?php
                                    echo ( isset($_POST['arthur_json_client_id']) ) ? 
                                        $_POST['arthur_json_client_id'] : 
                                        ( isset($options['client_id']) ? $options['client_id'] : '' );
                                ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Client Secret
                            </td>
                            <td>
                                <input type="text" name="arthur_json_client_secret" id="arthur_json_client_secret" placeholder="" value="<?php
                                    echo ( isset($_POST['arthur_json_client_secret']) ) ? 
                                        $_POST['arthur_json_client_secret'] : 
                                        ( isset($options['client_secret']) ? $options['client_secret'] : '' );
                                ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Callback URL
                            </td>
                            <td>
                                <em><?php if ( $import_id == '' ) { echo 'Callback will appear here after being saved.'; }else{ echo admin_url('admin.php?page=propertyhive_import_properties&arthur_callback=1&import_id=' . $import_id); } ?></em>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Entity ID
                            </td>
                            <td>
                                <input type="text" name="arthur_json_entity_id" id="arthur_json_entity_id" placeholder="" value="<?php
                                    echo ( isset($_POST['arthur_json_entity_id']) ) ? 
                                        $_POST['arthur_json_entity_id'] : 
                                        ( isset($options['entity_id']) ? $options['entity_id'] : '' );
                                ?>" />
                                <span class="description">
                                    <p>From the Settings menu in <a href="https://system.arthuronline.co.uk/" target="_blank">Arthur</a>, click <strong>OAuth Applications</strong> under <strong>Your Account</strong> section. Your <strong>ENTITY_ID</strong> will be displayed above the list of applications.</p>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Import Structure
                            </td>
                            <td>
                                <?php
                                    $import_structure = ( isset($_POST['arthur_json_import_structure']) ) ? 
                                        $_POST['arthur_json_import_structure'] : 
                                        ( isset($options['import_structure']) ? $options['import_structure'] : '' );
                                ?>
                                <select name="arthur_json_import_structure" id="arthur_json_import_structure">
                                    <option value=""<?php echo $import_structure == '' ? ' selected' : ''; ?>>Top-level property with units/rooms as children</option>
                                    <option value="no_children"<?php echo $import_structure == 'no_children' ? ' selected' : ''; ?>>Top-level property and units as properties</option>
                                    <option value="top_level_only"<?php echo $import_structure == 'top_level_only' ? ' selected' : ''; ?>>Top-level property only. Don't import units</option>
                                </select>
                                <span class="description">
                                    <p>If you wish units in Arthur to be imported as units/rooms in Property Hive you'll need our <a href="https://wp-property-hive.com/addons/rooms-and-student-accommodation/" target="_blank">Rooms and Student Accommodation Add On</a></p>
                                </span>
                            </td>
                        </tr>
                    </table>

                </div>

            </td>
        </tr>
    </table>

    <?php
        $this_format =
            ( isset($_POST['format']) && $_POST['format'] == 'xml_supercontrol' ) ? 
                true : 
                ( ( isset($options['format']) && $options['format'] == 'xml_supercontrol' ) ? true : false );
    ?>

    <table class="form-table">
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="format_xml_supercontrol"><?php _e( 'SuperControl API', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <label>
                    <input type="radio" name="format" id="format_xml_supercontrol" value="xml_supercontrol"<?php echo ( $this_format ? ' checked' : '' ); ?> />
                    Select
                </label>
                <br><br>
                <div class="format-options" id="xml_supercontrol_options" style="display:<?php echo ( $this_format ? 'block' : 'none' ); ?>">

                    <table>
                        <tr>
                            <td>
                                Client ID
                            </td>
                            <td>
                                <input type="text" name="supercontrol_xml_client_id" id="supercontrol_xml_client_id" placeholder="" value="<?php
                                    echo ( isset($_POST['supercontrol_xml_client_id']) ) ? 
                                        $_POST['supercontrol_xml_client_id'] : 
                                        ( isset($options['client_id']) ? $options['client_id'] : '' );
                                ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                API Key
                            </td>
                            <td>
                                <input type="text" name="supercontrol_xml_api_key" id="supercontrol_xml_api_key" placeholder="" value="<?php
                                    echo ( isset($_POST['supercontrol_xml_api_key']) ) ? 
                                        $_POST['supercontrol_xml_api_key'] : 
                                        ( isset($options['api_key']) ? $options['api_key'] : '' );
                                ?>" />
                            </td>
                        </tr>
                    </table>

                </div>

            </td>
        </tr>
    </table>

    <?php
        $this_format =
            ( isset($_POST['format']) && $_POST['format'] == 'xml_agentsinsight' ) ? 
                true : 
                ( ( isset($options['format']) && $options['format'] == 'xml_agentsinsight' ) ? true : false );
    ?>

    <table class="form-table">
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="format_xml_agentsinsight"><?php _e( 'agentsinsight* XML', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <label>
                    <input type="radio" name="format" id="format_xml_agentsinsight" value="xml_agentsinsight"<?php echo ( $this_format ? ' checked' : '' ); ?> />
                    Select
                </label>
                <br><br>
                <div class="format-options" id="xml_agentsinsight_options" style="display:<?php echo ( $this_format ? 'block' : 'none' ); ?>">
                    <table>
                        <tr>
                            <td>
                                XML URL
                            </td>
                            <td>
                                <input placeholder="http://" type="text" name="agentsinsight_xml_url" id="agentsinsight_xml_url" value="<?php
                                    echo ( isset($_POST['agentsinsight_xml_url']) ) ? 
                                        $_POST['agentsinsight_xml_url'] : 
                                        ( isset($options['xml_url']) ? $options['xml_url'] : '' );
                                ?>" />
                            </td>
                        </tr>
                        <?php /*<tr>
                            <td>
                                Only import updated properties
                            </td>
                            <td>
                                <input type="checkbox" name="agentsinsight_xml_only_updated" id="agentsinsight_xml_only_updated" value="yes" <?php
                                    echo ( isset($_POST['agentsinsight_xml_only_updated']) && $_POST['agentsinsight_xml_only_updated'] == 'yes' ) ? 
                                        'checked' : 
                                        ( ( isset($options['only_updated']) && $options['only_updated'] == 'yes' ) ? 'checked' : '' );
                                ?> />
                            </td>
                        </tr>*/ ?>
                    </table>

                    <?php 
                        /*if ( !ini_get('allow_url_fopen') && !function_exists('curl_version') ) 
                        {
                            echo '<span style="color:#900">' . __( 'Either allow_url_fopen or cURL must be enabled on the server in order to use this format', 'propertyhive' ) . '</span>';
                        } */
                    ?>
                </div>

            </td>
        </tr>
    </table>

    <?php
        $this_format =
            ( isset($_POST['format']) && $_POST['format'] == 'json_rex' ) ? 
                true : 
                ( ( isset($options['format']) && $options['format'] == 'json_rex' ) ? true : false );
    ?>

    <table class="form-table">
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="format_json_rex"><?php _e( 'Rex API', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <label>
                    <input type="radio" name="format" id="format_json_rex" value="json_rex"<?php echo ( $this_format ? ' checked' : '' ); ?> />
                    Select
                </label>
                <br><br>
                <div class="format-options" id="json_rex_options" style="display:<?php echo ( $this_format ? 'block' : 'none' ); ?>">
                    <table>
                        <tr>
                            <td>
                                Username
                            </td>
                            <td>
                                <input placeholder="" type="text" name="rex_json_username" id="rex_json_username" value="<?php
                                    echo ( isset($_POST['rex_json_username']) ) ? 
                                        $_POST['rex_json_username'] : 
                                        ( isset($options['username']) ? $options['username'] : '' );
                                ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Password
                            </td>
                            <td>
                                <input placeholder="" type="text" name="rex_json_password" id="rex_json_password" value="<?php
                                    echo ( isset($_POST['rex_json_password']) ) ? 
                                        $_POST['rex_json_password'] : 
                                        ( isset($options['password']) ? $options['password'] : '' );
                                ?>" />
                            </td>
                        </tr>
                    </table>

                    <?php 
                        /*if ( !ini_get('allow_url_fopen') && !function_exists('curl_version') ) 
                        {
                            echo '<span style="color:#900">' . __( 'Either allow_url_fopen or cURL must be enabled on the server in order to use this format', 'propertyhive' ) . '</span>';
                        } */
                    ?>
                </div>

            </td>
        </tr>
    </table>

    <?php
        $this_format =
            ( isset($_POST['format']) && $_POST['format'] == 'xml_decorus' ) ? 
                true : 
                ( ( isset($options['format']) && $options['format'] == 'xml_decorus' ) ? true : false );
    ?>
    <table class="form-table">
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="format_xml_decorus"><?php _e( 'Decorus / Landlord Manager XML', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <label>
                    <input type="radio" name="format" id="format_xml_decorus" value="xml_decorus"<?php echo ( $this_format ? ' checked' : '' ); ?> />
                    Select
                </label>
                <br><br>
                <div class="format-options" id="xml_decorus_options" style="display:<?php echo ( $this_format ? 'block' : 'none' ); ?>">
                    <table>
                        <tr>
                            <td>
                                Local Directory
                            </td>
                            <td>
                                <input type="text" name="decorus_xml_directory" id="decorus_xml_directory" value="<?php
                                    echo ( isset($_POST['decorus_xml_directory']) ) ? 
                                        $_POST['decorus_xml_directory'] : 
                                        ( isset($options['local_directory']) ? $options['local_directory'] : $uploads_dir );
                                ?>" />
                                <span class="description">
                                    <p><?php _e('When the import runs it will parse all XML files found in this local directory in date order', 'propertyhive'); ?></p>
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>

            </td>
        </tr>
    </table>

    <?php
        do_action( 'propertyhive_property_import_setup_details', $options );
    ?>

    <hr>

    <h3>Branch Information</h3>

    <p>We need a way to assign properties to their respective branch during the import. Third parties will normally send through a unique name or code for each branch.</p>

    <p>On the left are the offices setup in Property Hive. On the right you should enter the code or name that the third party use to specify each branch.</p>

    <p>Note: If nothing is entered below properties will all automatically get assigned to the primary office.</p>

    <table class="form-table">
    <?php
        $args = array(
            'post_type' => 'office',
            'nopaging' => true
        );
        $office_query = new WP_Query($args);
        
        if ($office_query->have_posts())
        {
            while ($office_query->have_posts())
            {
                $office_query->the_post();
    ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="offices_<?php echo get_the_ID(); ?>"><?php echo get_the_title(); ?></label>
            </th>
            <td class="forminp forminp-text">

                <input type="text" name="offices[<?php echo get_the_ID(); ?>]" id="offices_<?php echo get_the_ID(); ?>" value="<?php
                    if ( isset($options['offices'][get_the_ID()]) )
                    {
                        echo $options['offices'][get_the_ID()];
                    }
                ?>" />

            </td>
        </tr>
    <?php
            }
        }
        $office_query->reset_postdata();
    ?>
    </table>

    <hr>

    <h3>Additional Options</h3>

    <table class="form-table">
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="import_blm"><?php _e( 'Import Frequency', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <?php 
                    $frequency = 'hourly';
                    if ( isset($_POST['import_frequency']) )
                    {
                        $frequency = $_POST['import_frequency'];
                    }
                    elseif ( isset($options['import_frequency']) )
                    {
                        $frequency = $options['import_frequency'];
                    }
                ?>

                <label>
                    <input type="radio" name="import_frequency" id="import_frequency_every_15_minutes" value="every_15_minutes"<?php echo ( $frequency == 'every_15_minutes' ? ' checked' : '' ); ?> />
                    Every 15 Minutes
                </label>
                <br>
                <label>
                    <input type="radio" name="import_frequency" id="import_frequency_hourly" value="hourly"<?php echo ( $frequency == 'hourly' ? ' checked' : '' ); ?> />
                    Hourly
                </label>
                <br>
                <label>
                    <input type="radio" name="import_frequency" id="import_frequency_twicedaily" value="twicedaily"<?php echo ( $frequency == 'twicedaily' ? ' checked' : '' ); ?> />
                    Twice Daily
                </label>
                <br>
                <label>
                    <input type="radio" name="import_frequency" id="import_frequency_daily" value="daily"<?php echo ( $frequency == 'daily' ? ' checked' : '' ); ?> />
                    Daily
                </label>

                <span class="description">
                    <p><?php _e('Please note that by default automated tasks in WordPress are not executed by the server, but rather by visitors to the site. As a result, even if you have it set to run hourly above, if nobody visits the site for four hours, it won\'t run in those hours where nobody visited the site. If you require imports to be ran in a timely manner we recommend switching to use a cron job on the server. Instructions for this can be found <a href="https://easyengine.io/tutorials/wordpress/wp-cron-crontab/" target="_blank" rel="nofollow">here</a>, or alternatively speak to your hosting company.<br><br>If using a format which makes requests to the third party\'s server, please bear in mind a high frequency can result in them blocking/blacklisting your IP. In these scenarios we suggest an import frequency of hourly or less.', 'propertyhive'); ?></p>
                </span>

            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="running"><?php _e( 'Start Running Immediately', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <?php
                    $running = 'hourly';
                    if ( isset($_POST['running']) )
                    {
                        $running = $_POST['running'];
                    }
                    elseif ( isset($options['running']) )
                    {
                        $running = $options['running'];
                    }
                ?>
                <input type="checkbox" name="running" id="running" value="1"<?php echo ( $running == '1' ? ' checked' : '' ); ?>>

                <span class="description">
                    <p><?php _e('You can run and pause imports at any time from the main \'Import Properties\' screen', 'propertyhive'); ?></p>
                </span>

            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="dont_remove"><?php _e( 'Don\'t Remove Properties Automatically', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <?php
                    $dont_remove = '0';
                    if ( isset($_POST['dont_remove']) )
                    {
                        $dont_remove = $_POST['dont_remove'];
                    }
                    elseif ( isset($options['dont_remove']) )
                    {
                        $dont_remove = $options['dont_remove'];
                    }
                ?>
                <input type="checkbox" name="dont_remove" id="dont_remove" value="1"<?php echo ( $dont_remove == '1' ? ' checked' : '' ); ?>>

                <span class="description">
                    <p><?php _e('Whether to remove properties automatically if it is no longer received in the file', 'propertyhive'); ?></p>
                </span>

            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="remove_action"><?php _e( 'Additional Actions When Removing Properties', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <?php
                    $remove_action = '';
                    if ( isset($_POST['remove_action']) )
                    {
                        $remove_action = $_POST['remove_action'];
                    }
                    elseif ( isset($options['remove_action']) )
                    {
                        $remove_action = $options['remove_action'];
                    }
                ?>
                <select name="remove_action" id="remove_action">
                    <option value=""<?php if ( $remove_action == '' ) { echo ' selected'; } ?>>No Additional Action</option>
                    <option value="remove_all_media"<?php if ( $remove_action == 'remove_all_media' ) { echo ' selected'; } ?>>Remove All Media (images, floorplans etc)</option>
                    <option value="remove_all_media_except_first_image"<?php if ( $remove_action == 'remove_all_media_except_first_image' ) { echo ' selected'; } ?>>Remove All Media Except The First Image</option>
                    <option value="draft_property"<?php if ( $remove_action == 'draft_property' ) { echo ' selected'; } ?>>Draft Property</option>
                    <option value="remove_property"<?php if ( $remove_action == 'remove_property' ) { echo ' selected'; } ?>>Delete Property</option>
                </select>

                <span class="description">
                    <p><?php _e('Over time, as properties come on and off the market, your server could get full of property media belonging to properties no longer available. Use this option to determine what should happen to media belonging to properties being taken off the market. The reason for the \'Except First Image\' option is because the property URL\'s will still be accessible from search engines, social media or by visiting the URL direct. As a result, you might want to just leave one image in place should someone visit the property URL at a later date. Please note, if drafting or deleting properties you will end up with 404 errors as the property URL\'s are no longer accessible.', 'propertyhive'); ?></p>
                </span>

            </td>
        </tr>

        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="remove_action"><?php _e( 'Email Reports', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <?php
                    $email_reports = '';
                    if ( isset($_POST['email_reports']) )
                    {
                        $email_reports = $_POST['email_reports'];
                    }
                    elseif ( isset($options['email_reports']) )
                    {
                        $email_reports = $options['email_reports'];
                    }
                ?>
                <select name="email_reports" id="email_reports" onchange="if ( jQuery(this).val() == 'yes' ) { jQuery('#email_reports_options').show(); }else{ jQuery('#email_reports_options').hide(); }">
                    <option value=""<?php if ( $email_reports == '' ) { echo ' selected'; } ?>>Disabled</option>
                    <option value="yes"<?php if ( $email_reports == 'yes' ) { echo ' selected'; } ?>>Enabled</option>
                </select>

                <span class="description">
                    <p><?php _e('If enabled an email will be sent to the specified email address containing the log each time an import completes.', 'propertyhive'); ?></p>
                </span>

            </td>
        </tr>

        <tr valign="top" id="email_reports_options" <?php if ( $email_reports != 'yes' ) { echo 'style="display:none"'; } ?>>
            <th scope="row" class="titledesc">
                <label for="remove_action"><?php _e( 'Send Email Reports To', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <?php
                    $email_reports_to = get_option('admin_email');
                    if ( isset($_POST['email_reports_to']) )
                    {
                        $email_reports_to = $_POST['email_reports_to'];
                    }
                    elseif ( isset($options['email_reports_to']) )
                    {
                        $email_reports_to = $options['email_reports_to'];
                    }
                ?>
                <input type="email" name="email_reports_to" value="<?php echo $email_reports_to; ?>">

            </td>
        </tr>

        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="remove_action"><?php _e( 'Process in Chunks', 'propertyhive' ); ?></label>
            </th>
            <td class="forminp forminp-text">

                <?php
                    $chunk_qty = '';
                    if ( isset($_POST['chunk_qty']) )
                    {
                        $chunk_qty = $_POST['chunk_qty'];
                    }
                    elseif ( isset($options['chunk_qty']) )
                    {
                        $chunk_qty = $options['chunk_qty'];
                    }

                    $chunk_delay = '';
                    if ( isset($_POST['chunk_delay']) )
                    {
                        $chunk_delay = $_POST['chunk_delay'];
                    }
                    elseif ( isset($options['chunk_delay']) )
                    {
                        $chunk_delay = $options['chunk_delay'];
                    }
                ?>

                Process <input type="number" name="chunk_qty" style="width:50px;" value="<?php echo $chunk_qty; ?>"> records at a time<br>with a <input type="number" style="width:50px;" name="chunk_delay" value="<?php echo $chunk_delay; ?>"> second pause between each chunk.

                <span class="description">
                    <p><?php _e('By default Property Hive will try to process all records in one go with no pauses. If you want to reduce server load, or maybe properties are being sent to a third party such as Rightmove with throttling limits, you might need to slow this process down. Above you can specify to add a pause between every X number of properties imported. Note that adding a delay will increase the time the imports take to run and increase the chance of you hitting execution limits set by your server. Leave these blank if you\'re unsure or want to use the default functionality', 'propertyhive'); ?></p>
                </span>

            </td>
        </tr>
    </table>

    <p class="submit">
        <input type="hidden" name="save_automatic_details" value="1">
        <input type="hidden" name="import_id" value="<?php echo $import_id; ?>">
        <input type="hidden" name="manual_automatic" value="<?php echo $manual_automatic; ?>">
        <a href="<?php echo admin_url('admin.php?page=propertyhive_import_properties'); ?>" class="button">Cancel</a>
        <input name="save" id="save_import_step" class="button-primary" type="submit" value="Continue" onclick="setTimeout(function() { document.getElementById('save_import_step').disabled='disabled'; }, 1);">
    </p>

    <script>
        jQuery(document).ready(function()
        {
            jQuery('input[name=\'format\']').change(function()
            {
                var format = jQuery('input[name=\'format\']:checked').val();

                jQuery('div.format-options').hide();
                jQuery('div#' + format + '_options').show();
            });
        });
    </script>
    <?php
        }
    ?>

</form>