<?php
/**
 * Plugin Name: Property Hive Property Import Add On
 * Plugin Uri: http://wp-property-hive.com/addons/property-import/
 * Description: Add On for Property Hive allowing you to import properties manually or on an automatic basis
 * Version: 1.1.82
 * Author: PropertyHive
 * Author URI: http://wp-property-hive.com
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'PH_Property_Import' ) ) :

final class PH_Property_Import {

    /**
     * @var string
     */
    public $version = '1.1.82';

    /**
     * @var Property Hive The single instance of the class
     */
    protected static $_instance = null;
    
    /**
     * Main Property Hive Property Import Instance
     *
     * Ensures only one instance of Property Hive Property Import is loaded or can be loaded.
     *
     * @static
     * @return Property Hive Property Import - Main instance
     */
    public static function instance() 
    {
        if ( is_null( self::$_instance ) ) 
        {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor.
     */
    public function __construct() {

        $this->id    = 'propertyimport';
        $this->label = __( 'Import Properties', 'propertyhive' );

        // Define constants
        $this->define_constants();

        // Include required files
        $this->includes();

        add_action( 'admin_init', array( $this, 'run_custom_property_import_cron') );

        add_action( 'admin_init', array( $this, 'propertyhive_property_portal_add_on_integration') );

        add_action( 'admin_init', array( $this, 'admin_redirects' ) );

        add_action( 'admin_init', array( $this, 'check_arthur_authorization_code' ), 1 );

        add_action( 'admin_notices', array( $this, 'propertyimport_error_notices') );
        add_action( 'admin_init', array( $this, 'check_propertyimport_is_scheduled'), 99 );

        add_filter( "plugin_action_links_" . plugin_basename( __FILE__ ), array( $this, 'plugin_add_settings_link' ) );

        add_action( 'admin_menu', array( $this, 'admin_menu' ) );

        add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

        add_action( 'wp_ajax_propertyhive_dismiss_notice_jupix_export_enquiries', array( $this, 'ajax_propertyhive_dismiss_notice_jupix_export_enquiries' ) );

        add_action( 'phpropertyimportcronhook', array( $this, 'property_import_execute_feed' ) );

        add_filter( 'cron_schedules', array( $this, 'custom_cron_recurrence' ) );

        add_filter( 'propertyhive_use_google_maps_geocoding_api_key', array( $this, 'enable_separate_geocoding_api_key' ) );

        add_action( 'init', array( $this, 'webedge_rewrite_rules' ) );
        add_filter( 'query_vars', array( $this, 'webedge_query_vars' ) );
        add_action( 'parse_request', array( $this, 'run_webedge_import' ), 99 );

        // Redirects
        add_action('init', array( $this, 'check_jupix_redirect' ) );
        add_action('init', array( $this, 'check_import_redirect' ) );
        
        if ( class_exists( 'WP_CLI' ) ) 
        {
            WP_CLI::add_command( 'import-properties', array( $this, 'property_import_execute_feed' ) );
        }
    }

    public function check_jupix_redirect()
    {
        if ( isset($_GET['profileID']) )
        {
            $args = array(
                'post_type' => 'property',
                'meta_compare_key' => 'LIKE',
                'meta_key'     => '_imported_ref_',
                'meta_value'   => sanitize_text_field($_GET['profileID'])
            );
            $my_query = new WP_Query( $args );

            if ( $my_query->have_posts() )
            {
                while ( $my_query->have_posts() )
                {
                    $my_query->the_post();

                    header("HTTP/1.1 301 Moved Permanently");
                    header("Location: " . get_permalink(get_the_ID()));

                    exit();
                }

            }
            wp_reset_postdata();
        }
    }

    public function check_import_redirect()
    {
        if ( isset($_GET['imported_id']) )
        {
            $args = array(
                'post_type' => 'property',
                'meta_compare_key' => 'LIKE',
                'meta_key'     => '_imported_ref_',
                'meta_value'   => sanitize_text_field($_GET['imported_id'])
            );
            $my_query = new WP_Query( $args );

            if ( $my_query->have_posts() )
            {
                while ( $my_query->have_posts() )
                {
                    $my_query->the_post();

                    header("HTTP/1.1 301 Moved Permanently");
                    header("Location: " . get_permalink(get_the_ID()));

                    exit();
                }

            }
            wp_reset_postdata();
        }
    }

    public function enable_separate_geocoding_api_key( $return )
    {
        return true;
    }

    public function check_arthur_authorization_code()
    {
        if ( isset($_GET['arthur_callback']) && (int)$_GET['arthur_callback'] == 1 )
        {
            if ( !isset($_GET['code']) )
            {
                die('No authorization code present');
            }

            if ( !isset($_GET['import_id']) )
            {
                die('No import_id present. Please check your redirect URL');
            }

            // Load Importer API
            require_once ABSPATH . 'wp-admin/includes/import.php';

            if ( ! class_exists( 'WP_Importer' ) ) 
            {
                $class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
                if ( file_exists( $class_wp_importer ) ) require_once $class_wp_importer;
            }

            require_once dirname( __FILE__ ) . '/includes/class-ph-arthur-json-import.php';

            $PH_Arthur_JSON_Import = new PH_Arthur_JSON_Import();

            $PH_Arthur_JSON_Import->get_access_token_from_authorization_code($_GET['code'], $_GET['import_id']);
        }
    }

    public function webedge_rewrite_rules()
    {
        add_rewrite_rule(
            '^webedge-send-property/?',
            'index.php?webedge=1',
            'top'
        );
    }

    public function webedge_query_vars( $query_vars ){
        $query_vars[] = 'webedge';
        return $query_vars;
    }

    public function run_webedge_import($query)
    {
        if (
            isset($query->query_vars['webedge']) && 
            $query->query_vars['webedge'] == '1'
        )
        {
            global $wpdb;

            // Load Importer API
            require_once ABSPATH . 'wp-admin/includes/import.php';

            if ( ! class_exists( 'WP_Importer' ) ) 
            {
                $class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
                if ( file_exists( $class_wp_importer ) ) require_once $class_wp_importer;
            }

            require_once dirname( __FILE__ ) . '/includes/class-ph-webedge-xml-import.php';

            $import_options = get_option( 'propertyhive_property_import' );
            if ( is_array($import_options) && !empty($import_options) )
            {
                foreach ( $import_options as $import_id => $options )
                {
                    if ( $options['format'] != 'xml_webedge' )
                    {
                        continue;
                    }

                    if ( $options['running'] != '1' )
                    {
                        // Service unavailable
                        echo '<?xml version="1.0" encoding="UTF-8"?>
                        <response xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="http://feeds.propertynews.com/schemas/response.xsd" id="" action="" agent="">
                          <result>120</result>
                          <message>Import not active</message>
                          <secret></secret>
                        </response>';
                    }
                    else
                    {
                        // log instance start
                        $wpdb->insert( 
                            $wpdb->prefix . "ph_propertyimport_logs_instance", 
                            array(
                                'import_id' => $import_id,
                                'start_date' => gmdate("Y-m-d H:i:s")
                            )
                        );
                        $instance_id = $wpdb->insert_id;

                        $PH_WebEDGE_XML_Import = new PH_WebEDGE_XML_Import( $instance_id );

                        $property = $PH_WebEDGE_XML_Import->validate( $options );

                        // Ok to import
                        if ( $property !== FALSE )
                        {
                            $property_attributes = $property->attributes();

                            $new_secret = md5($property->secret . $options['shared_secret']);

                            if ( $property_attributes['action'] == 'DELETE' )
                            {
                                $PH_WebEDGE_XML_Import->remove( $property, $import_id, (!isset($options['dont_remove']) || $options['dont_remove'] != '1') ?  true : false );

                                echo '<?xml version="1.0" encoding="UTF-8"?>
                                <response xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="http://feeds.propertynews.com/schemas/response.xsd" id="' . (string)$property_attributes['id'] . '" action="' . (string)$property_attributes['action'] . '" agent="' . (string)$property_attributes['agent'] . '">
                                  <result>00</result>
                                  <message>Processed OK</message>
                                  <secret>' . $new_secret . '</secret>
                                </response>';
                            }
                            else
                            {
                                $PH_WebEDGE_XML_Import->import( $property, $import_id );

                                echo '<?xml version="1.0" encoding="UTF-8"?>
                                <response xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="http://feeds.propertynews.com/schemas/response.xsd" id="' . (string)$property_attributes['id'] . '" action="' . (string)$property_attributes['action'] . '" agent="' . (string)$property_attributes['agent'] . '">
                                  <result>00</result>
                                  <message>Processed OK</message>
                                  <secret>' . $new_secret . '</secret>
                                </response>';
                            }
                        }

                        // log instance end
                        $wpdb->update( 
                            $wpdb->prefix . "ph_propertyimport_logs_instance", 
                            array( 
                                'end_date' => gmdate("Y-m-d H:i:s")
                            ),
                            array( 'id' => $instance_id )
                        );

                        die(); // Die here as only one feed could be processed at any one time. Otherwise duplicate responses would be sent.
                    }
                }
            }
            
            die();
        }
    }

    public function plugin_add_settings_link( $links )
    {
        $settings_link = '<a href="' . admin_url('admin.php?page=propertyhive_import_properties') . '">' . __( 'Settings' ) . '</a>';
        array_push( $links, $settings_link );
        return $links;
    }

    public function ajax_propertyhive_dismiss_notice_jupix_export_enquiries()
    {
        update_option( 'jupix_export_enquiries_notice_dismissed', 'yes' );
        
        // Quit out
        die();
    }

    /**
     * Enqueue scripts
     */
    public function admin_scripts() {

        $suffix       = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

        $assets_path = str_replace( array( 'http:', 'https:' ), '', untrailingslashit( plugins_url( '/', __FILE__ ) ) ) . '/assets/';

        // Register scripts
        wp_register_script( 'propertyhive_property_import_admin', $assets_path . 'js/admin' . /*$suffix .*/ '.js', array( 'jquery' ), PH_PROPERTYIMPORT_VERSION );

        if ( get_option('jupix_export_enquiries_notice_dismissed', '') != 'yes' )
        {
            $options = get_option( 'propertyhive_property_import' );
            if ( is_array($options) && !empty($options) )
            {
                foreach ( $options as $import_id => $option )
                {
                    if ( isset($option['deleted']) && $option['deleted'] == 1 )
                    {
                        continue;
                    }

                    if ( $option['format'] == 'xml_jupix' && isset($option['running']) && $option['running'] == 1 )
                    {
                        wp_enqueue_script( 'propertyhive_property_import_admin' );
                        break;
                    }
                }
            }
        }
    }

    public function check_propertyimport_is_scheduled()
    {
        $schedule = wp_get_schedule( 'phpropertyimportcronhook' );

        if ( $schedule === FALSE )
        {
            // Hmm... cron job not found. Let's set it up
            $timestamp = wp_next_scheduled( 'phpropertyimportcronhook' );
            wp_unschedule_event($timestamp, 'phpropertyimportcronhook' );
            wp_clear_scheduled_hook('phpropertyimportcronhook');
            
            $next_schedule = time() - 60;
            wp_schedule_event( $next_schedule, 'every_fifteen_minutes', 'phpropertyimportcronhook' );
        }
    }

    /**
     * Handle redirects to import page after install.
     */
    public function admin_redirects()
    {
        // Setup wizard redirect
        if ( get_transient( '_ph_property_import_activation_redirect' ) ) 
        {
            delete_transient( '_ph_property_import_activation_redirect' );

            // Don't do redirect if part of multisite, doing batch-activate, or if no permission
            if ( is_network_admin() || isset( $_GET['activate-multi'] ) || ! current_user_can( 'manage_propertyhive' ) ) {
                return;
            }

            wp_safe_redirect( admin_url( 'admin.php?page=propertyhive_import_properties' ) );
            exit;
        }
    }

    public function custom_cron_recurrence( $schedules ) 
    {
        $schedules['every_fifteen_minutes'] = array(
            'interval'  => 900,
            'display'   => __( 'Every 15 Minutes', 'propertyhive' )
        );
         
        return $schedules;
    }

    public function propertyhive_property_portal_add_on_integration()
    {
        // Is the property portal add on activated
        if (class_exists('PH_Property_Portal'))
        {
            //add_action( 'propertyhive_agent_branch_template_fields', array( $this, 'add_propertyhive_agent_branch_template_fields'), 1 );
            add_action( 'propertyhive_agent_branch_existing_fields', array( $this, 'add_propertyhive_agent_branch_existing_fields'), 1, 1 );
            add_action( 'propertyhive_save_agent_branches', array( $this, 'do_propertyhive_save_agent_branches'), 1 );
        }
    }

    public function add_propertyhive_agent_branch_existing_fields( $branch_post_id )
    {
        if ( get_option( 'propertyhive_active_departments_sales' ) == 'yes' )
        {
            propertyhive_wp_text_input( array( 
                'id' => '_branch_code_sales[existing_' . $branch_post_id . ']', 
                'label' => __( 'Branch Code (Sales)', 'propertyhive' ), 
                'desc_tip' => false, 
                'value' => get_post_meta( $branch_post_id, '_branch_code_sales', true ),
                'type' => 'text'
            ) );
        }
        if ( get_option( 'propertyhive_active_departments_lettings' ) == 'yes' )
        {
            propertyhive_wp_text_input( array( 
                'id' => '_branch_code_lettings[existing_' . $branch_post_id . ']', 
                'label' => __( 'Branch Code (Lettings)', 'propertyhive' ), 
                'desc_tip' => false, 
                'value' => get_post_meta( $branch_post_id, '_branch_code_lettings', true ),
                'type' => 'text'
            ) );
        }
        if ( get_option( 'propertyhive_active_departments_commercial' ) == 'yes' )
        {
            propertyhive_wp_text_input( array( 
                'id' => '_branch_code_commercial[existing_' . $branch_post_id . ']', 
                'label' => __( 'Branch Code (Commercial)', 'propertyhive' ), 
                'desc_tip' => false, 
                'value' => get_post_meta( $branch_post_id, '_branch_code_commercial', true ),
                'type' => 'text'
            ) );
        }
    }

    public function do_propertyhive_save_agent_branches()
    {
        foreach ($_POST['_branch_name'] as $key => $value)
        {
            $existing = FALSE;
            if ( strpos($key, 'existing_') !== FALSE )
            {
                $existing = str_replace('existing_', '', $key);
            }

            if ($existing !== FALSE)
            {
                $branch_id = $existing;

                // This is an existing branch
                update_post_meta( $branch_id, '_branch_code_sales', $_POST['_branch_code_sales'][$key] );
                update_post_meta( $branch_id, '_branch_code_lettings', $_POST['_branch_code_lettings'][$key] );
            }
        }
    }

    /**
     * Define PH Property Import Constants
     */
    private function define_constants() 
    {
        define( 'PH_PROPERTYIMPORT_PLUGIN_FILE', __FILE__ );
        define( 'PH_PROPERTYIMPORT_VERSION', $this->version );
        define( 'PH_PROPERTYIMPORT_DEPARTMENT_AVAILABILITY_UPDATE', 1589328000 );
    }

    private function includes()
    {
        include_once( 'includes/class-ph-property-import-install.php' );
        require( __DIR__ . '/includes/class-ph-property-import-process.php' );
    }

    public function property_import_execute_feed() 
    {
        require( __DIR__ . '/cron.php' );

        if ( defined( 'WP_CLI' ) && WP_CLI )
        {
            WP_CLI::success( "Import completed successfully" );
        }
    }

    public function run_custom_property_import_cron() 
    {
        if( isset($_GET['custom_property_import_cron']) )
        {
            do_action($_GET['custom_property_import_cron']);
        }
    }

    /**
     * Output error message if core Property Hive plugin isn't active
     */
    public function propertyimport_error_notices() 
    {
        if (!is_plugin_active('propertyhive/propertyhive.php'))
        {
            $message = "The Property Hive plugin must be installed and activated before you can use the Property Hive Property Import add-on";
            echo"<div class=\"error\"> <p>$message</p></div>";
        }
        else
        {
            global $wpdb;

            // Check timeout limit
            $screen = get_current_screen();
            if ( $screen->id == 'property-hive_page_propertyhive_import_properties' )
            {
                // Check if import might have got stuck. Where:
                // - End date is empty
                // - Last entry in log for this import is more than 30 minutes ago
                $row = $wpdb->get_row( "SELECT 
                        id, end_date
                    FROM 
                        " .$wpdb->prefix . "ph_propertyimport_logs_instance
                    ORDER BY start_date DESC 
                    LIMIT 1", ARRAY_A);
                if ( null !== $row )
                {
                    if ($row['end_date'] == '0000-00-00 00:00:00')
                    {
                        // The last import ran is currently running. Now check log
                        $row = $wpdb->get_row( "SELECT 
                            log_date
                        FROM 
                            " .$wpdb->prefix . "ph_propertyimport_logs_instance_log
                        WHERE 
                            instance_id = '" . $row['id'] . "'
                        ORDER BY log_date DESC 
                        LIMIT 1", ARRAY_A);
                        if ( null !== $row )
                        {
                            $last_log_entry = $row['log_date'];
                            if (time() - strtotime($last_log_entry) > (30 * 60) )
                            {
                                echo '<div class="notice notice-info is-dismissible"><p><strong>' .
                                __( 'It looks like your latest import might have fallen over? It\'s likely the \'max_execution_time\' PHP setting needs increasing on your server. Your web hosting company should be able to increase this providing you\'re not on shared hosting.', 'propertyhive' ) .
                                '</strong></p></div>';
                            }
                        }
                    }
                }
            }
            if ( $screen->id == 'property' && isset($_GET['post']) && get_post_type($_GET['post']) == 'property' )
            {
                // Check if this property was imported from somewhere and warn if it was
                $post_meta = get_post_meta($_GET['post']);

                $imported = false;

                foreach ($post_meta as $key => $val )
                {
                    if ( strpos($key, '_imported_ref_') !== FALSE )
                    {
                        echo '<div class="notice notice-info"><p>';
                        
                        echo __( '<strong>It looks like this property was imported automatically. Please note that any changes made manually might get overwritten the next time an import runs.</strong><br><br><em>Import Details: ' . $key . ': ' . $val[0] . '</em>', 'propertyhive' );
                        
                        $property_import_id = str_replace("_imported_ref_", "", $key);
                        $options = get_option( 'propertyhive_property_import' );
                        if ( is_array($options) && !empty($options) )
                        {
                            foreach ( $options as $import_id => $option )
                            {
                                if ( $import_id == $property_import_id && isset($option['deleted']) && $option['deleted'] == 1 )
                                {
                                    echo '<br><br><strong style="color:#900">' . __( 'This property was imported by an import which no longer exists.', 'propertyhive' ) . '</strong>';
                                }
                            }
                        }

                        echo '</p></div>';
                        break;
                    }
                }
            }

            if ( get_option('jupix_export_enquiries_notice_dismissed', '') != 'yes' )
            {
                $options = get_option( 'propertyhive_property_import' );
                if ( is_array($options) && !empty($options) )
                {
                    foreach ( $options as $import_id => $option )
                    {
                        if ( isset($option['deleted']) && $option['deleted'] == 1 )
                        {
                            continue;
                        }

                        if ( $option['format'] == 'xml_jupix' && isset($option['running']) && $option['running'] == 1 )
                        {
                            echo '<div class="notice notice-info" id="ph_notice_jupix_export_enquiries"><p>' .
                                __( '<strong>It looks like you\'re importing properties from Jupix. Did you know you can now automatically send enquiries made through the website back into your Jupix account?</strong>', 'propertyhive' ) .
                                '</p>
                                <p>
                                    <a href="https://wp-property-hive.com/addons/export-jupix-enquiries/" target="_blank" class="button-primary">View Jupix Enquiries Add On</a>
                                    <a href="" class="button" id="ph_dismiss_notice_jupix_export_enquiries">Dismiss</a>
                                </p></div>';
                            break;
                        }
                    }
                }
            }

            $error = '';    
            $uploads_dir = wp_upload_dir();
            if( $uploads_dir['error'] === FALSE )
            {
                $uploads_dir = $uploads_dir['basedir'] . '/ph_import/';
                
                if ( ! @file_exists($uploads_dir) )
                {
                    if ( ! @mkdir($uploads_dir) )
                    {
                        $error = 'Unable to create subdirectory in uploads folder for use by Property Hive Property Import plugin. Please ensure the <a href="http://codex.wordpress.org/Changing_File_Permissions" target="_blank" title="WordPress Codex - Changing File Permissions">correct permissions</a> are set.';
                    }
                }
                else
                {
                    if ( ! @is_writeable($uploads_dir) )
                    {
                        $error = 'The uploads folder is not currently writeable and will need to be before properties can be imported. Please ensure the <a href="http://codex.wordpress.org/Changing_File_Permissions" target="_blank" title="WordPress Codex - Changing File Permissions">correct permissions</a> are set.';
                    }
                }
            }
            else
            {
                $error = 'An error occured whilst trying to create the uploads folder. Please ensure the <a href="http://codex.wordpress.org/Changing_File_Permissions" target="_blank" title="WordPress Codex - Changing File Permissions">correct permissions</a> are set. '.$uploads_dir['error'];
            }
            
            if( $error != '' )
            {
                echo '<div class="error"><p><strong>'.$error.'</strong></p></div>';
            }
        }
    }

    /**
     * Admin Menu
     */
    function admin_menu() 
    {
        add_submenu_page( 'propertyhive', __( 'Import Properties', 'propertyhive' ),  __( 'Import Properties', 'propertyhive' ) , 'manage_propertyhive', 'propertyhive_import_properties', array( $this, 'admin_page' ) );
    }

    /**
     * Admin Page
     */
    function admin_page() {

        global $propertyhive;

        if ( isset( $_GET['running'] ) && isset( $_GET['import_id'] ) )
        {
            $options = get_option( 'propertyhive_property_import' );
            if ( isset($options[$_GET['import_id']]) )
            {
                $options[$_GET['import_id']]['running'] = ( ( $_GET['running'] == 1 ) ? 1 : '' );
            }
            update_option( 'propertyhive_property_import', $options );
        }

        if ( isset($_GET['delete']) && $_GET['delete'] != '' )
        {
            $options = get_option( 'propertyhive_property_import' );
            if ( isset($options[$_GET['delete']]) )
            {
                $options[$_GET['delete']]['deleted'] = 1;
            }
            update_option( 'propertyhive_property_import', $options );
        }

        $errors = array();

        $ok_to_import = false;
        $do_csv_mapping = false;
        $imported = false;
        $pre_test_errors = array();
        $format = '';
        $manual_automatic = 'manual';

        if ( isset($_POST['pre_test']) && $_POST['pre_test'] == 1 )
        {
            $manual_automatic = $_POST['manual_automatic'];

            // Delete any previous imports
            $uploads_dir = wp_upload_dir();
            if( $uploads_dir['error'] === FALSE )
            {
                $uploads_dir = $uploads_dir['basedir'] . '/ph_import/';

                $handle = opendir($uploads_dir);
                while ( ($file = readdir($handle)) !== false ) 
                {
                    @unlink( $uploads_dir . '/' . $file );
                }
                closedir($handle);
            }

            if ( $_FILES['import_blm']['size'] == 0 && $_FILES['import_csv']['size'] == 0 )
            {
                $errors[] = 'Please select a file to import';
            }
            else
            {
                $file_upload_name = '';
                if ( $_FILES['import_blm']['size'] != 0 )
                {
                    $file_upload_name = 'import_blm';
                }
                elseif ( $_FILES['import_csv']['size'] != 0 )
                {
                    $file_upload_name = 'import_csv';
                }

                try {

                    // Check $_FILES[$file_upload_name]['error'] value.
                    switch ($_FILES[$file_upload_name]['error']) {
                        case UPLOAD_ERR_OK:
                            break;
                        case UPLOAD_ERR_NO_FILE:
                            throw new RuntimeException('No file sent.');
                        case UPLOAD_ERR_INI_SIZE:
                        case UPLOAD_ERR_FORM_SIZE:
                            $errors[] = __( 'File exceeded filesize limit.', 'propertyhive' );
                        default:
                            $errors[] = __( 'Unknown error when uploading file.', 'propertyhive' );
                    }

                    if ( empty($errors) )
                    {  
                        $ext = '';
                        if ( class_exists('finfo') )
                        {
                            // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
                            // Check MIME Type by yourself.
                            /*$finfo = new finfo(FILEINFO_MIME_TYPE);
                            if (false === $ext = array_search(
                                $finfo->file($_FILES[$file_upload_name]['tmp_name']),
                                array(
                                    'blm' => 'text/plain',
                                    'csv' => 'text/plain',
                                ),
                                true
                            )) {
                                $errors[] = __( 'Uploaded file must be of type .blm or .csv', 'propertyhive' );
                            }*/
                        }
                        else
                        {
                            $explode_file_name = explode(".", $_FILES[$file_upload_name]['name']);
                            $ext = $explode_file_name[count($explode_file_name)-1];
                        }
                    }

                    if ( empty($errors) )
                    {
                        $uploads_dir = wp_upload_dir();
                        $uploads_dir = $uploads_dir['basedir'] . '/ph_import/';

                        $import_file_name = sha1_file($_FILES[$file_upload_name]['tmp_name']) . '.' . $ext;
                        $target_file = sprintf(
                            $uploads_dir . '%s',
                            $import_file_name
                        );

                        // You should name it uniquely.
                        // DO NOT USE $_FILES['upfile']['name'] WITHOUT ANY VALIDATION !!
                        // On this example, obtain safe unique name from its binary data.
                        if (!move_uploaded_file(
                            $_FILES[$file_upload_name]['tmp_name'],
                            $target_file
                        )) {
                            $errors[] = __( 'Failed to move uploaded file.', 'propertyhive' );
                        }
                    }

                    if ( empty($errors) )
                    {
                        // File is uploaded and we're ready to start importing

                        @set_time_limit(0);

                        // Load Importer API
                        require_once ABSPATH . 'wp-admin/includes/import.php';

                        if ( ! class_exists( 'WP_Importer' ) ) {
                            $class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
                            if ( file_exists( $class_wp_importer ) ) require $class_wp_importer;
                        }

                        // includes
                        if ( $file_upload_name == 'import_blm' )
                        {
                            require dirname( __FILE__ ) . '/includes/class-ph-blm-import.php';

                            $PH_Import = new PH_BLM_Import( $target_file );

                            $PH_Import->parse();

                            list( $passed_properties, $failed_properties ) = $PH_Import->pre_test();

                            $pre_test_errors = $PH_Import->get_errors();

                            if ( empty($pre_test_errors) )
                            {
                                $ok_to_import = true;
                            }  
                        }
                        elseif ( $file_upload_name == 'import_csv' )
                        {
                            require dirname( __FILE__ ) . '/includes/class-ph-csv-import.php';

                            $file_contents = file_get_contents($target_file);
                            $file_contents = str_replace("\r\n", "\n", $file_contents);
                            $file_contents = str_replace("\r", "\n", $file_contents);
                            file_put_contents($target_file, $file_contents);

                            $PH_Import = new PH_CSV_Import( $target_file );

                            $do_csv_mapping = true;
                        }                   
                    }

                } catch ( RuntimeException $e ) {

                    $errors[] = $e->getMessage();

                }
            }
        }
        elseif ( ( isset($_POST['save_automatic_details']) && $_POST['save_automatic_details'] == 1 ) )
        {
            $manual_automatic = $_POST['manual_automatic'];

            // Save details
            if ( !isset($_POST['format']) || ( isset($_POST['format']) && $_POST['format'] == '' ) )
            {
                $errors[] = 'Please select a format in order to continue';
            }
            else
            {
                // Load Importer API
                require_once ABSPATH . 'wp-admin/includes/import.php';

                if ( ! class_exists( 'WP_Importer' ) ) {
                    $class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
                    if ( file_exists( $class_wp_importer ) ) require $class_wp_importer;
                }

                $existing_options = get_option( 'propertyhive_property_import' );
                if ( isset($existing_options[$_POST['import_id']]) )
                {
                    $existing_options = $existing_options[$_POST['import_id']];
                }
                
                $mappings = array();

                if ( isset($existing_options['mappings']) )
                {
                    $mappings = $existing_options['mappings'];
                }

                $options = array(
                    'format' => $_POST['format'],
                    'import_frequency' => $_POST['import_frequency'],
                    'running' => ( (isset($_POST['running'])) ? $_POST['running'] : '' ),
                    'dont_remove' => ( (isset($_POST['dont_remove'])) ? $_POST['dont_remove'] : '' ),
                    'remove_action' => ( (isset($_POST['remove_action'])) ? $_POST['remove_action'] : '' ),
                    'email_reports' => ( (isset($_POST['email_reports'])) ? $_POST['email_reports'] : '' ),
                    'email_reports_to' => ( (isset($_POST['email_reports_to'])) ? $_POST['email_reports_to'] : '' ),
                    'chunk_qty' => ( (isset($_POST['chunk_qty'])) ? $_POST['chunk_qty'] : '' ),
                    'chunk_delay' => ( (isset($_POST['chunk_delay'])) ? $_POST['chunk_delay'] : '' ),
                    'mappings' => $mappings,
                    'offices' => $_POST['offices'],
                );

                switch ( $_POST['format'] )
                {
                    case "blm_local":
                    {
                        // includes
                        require dirname( __FILE__ ) . '/includes/class-ph-blm-import.php';

                        $PH_BLM_Import = new PH_BLM_Import( '' );

                        $options = array_merge(
                            $options, 
                            array(
                                'local_directory' => $_POST['blm_local_directory'],
                                'only_updated' => ( (isset($_POST['blm_local_only_updated'])) ? $_POST['blm_local_only_updated'] : '' ),
                            )
                        );


                        break;
                    }
                    case "blm_remote":
                    {
                        // includes
                        require dirname( __FILE__ ) . '/includes/class-ph-blm-import.php';

                        $PH_BLM_Import = new PH_BLM_Import( '' );

                        $options = array_merge(
                            $options, 
                            array(
                                'url' => $_POST['blm_remote_url'],
                                'only_updated' => ( (isset($_POST['blm_remote_only_updated'])) ? $_POST['blm_remote_only_updated'] : '' ),
                            )
                        );


                        break;
                    }
                    case "xml_dezrez":
                    {
                        // includes
                        require dirname( __FILE__ ) . '/includes/class-ph-dezrez-xml-import.php';

                        $PH_Dezrez_XML_Import = new PH_Dezrez_XML_Import();

                        $options = array_merge(
                            $options, 
                            array(
                                'api_key' => $_POST['dezrez_xml_api_key'],
                                'eaid' => $_POST['dezrez_xml_estate_agency_id'],
                                'branch_ids' => $_POST['dezrez_xml_branch_ids'],
                                'only_updated' => ( (isset($_POST['dezrez_xml_only_updated'])) ? $_POST['dezrez_xml_only_updated'] : '' ),
                            )
                        );

                        break;
                    }
                    case "json_dezrez":
                    {
                        // includes
                        require dirname( __FILE__ ) . '/includes/class-ph-dezrez-json-import.php';

                        $PH_Dezrez_JSON_Import = new PH_Dezrez_JSON_Import();

                        $options = array_merge(
                            $options, 
                            array(
                                'api_key' => $_POST['dezrez_json_api_key'],
                                'branch_ids' => $_POST['dezrez_json_branch_ids'],
                                'tags' => $_POST['dezrez_json_tags'],
                                'only_updated' => ( (isset($_POST['dezrez_json_only_updated'])) ? $_POST['dezrez_json_only_updated'] : '' ),
                            )
                        );

                        break;
                    }
                    case "xml_expertagent":
                    {
                        // includes
                        require dirname( __FILE__ ) . '/includes/class-ph-expertagent-xml-import.php';

                        $PH_ExpertAgent_XML_Import = new PH_ExpertAgent_XML_Import();

                        $options = array_merge(
                            $options, 
                            array(
                                'ftp_host' => str_replace("ftp://", "", $_POST['expertagent_xml_ftp_host']),
                                'ftp_user' => $_POST['expertagent_xml_ftp_user'],
                                'ftp_pass' => $_POST['expertagent_xml_ftp_pass'],
                                'ftp_dir' => $_POST['expertagent_xml_ftp_dir'],
                                'ftp_passive' => ( (isset($_POST['expertagent_xml_ftp_passive'])) ? $_POST['expertagent_xml_ftp_passive'] : '' ),
                                'xml_filename' => $_POST['expertagent_xml_filename'],
                            )
                        );

                        break;
                    }
                    case "xml_jupix":
                    {
                        // includes
                        require dirname( __FILE__ ) . '/includes/class-ph-jupix-xml-import.php';

                        $PH_Jupix_XML_Import = new PH_Jupix_XML_Import();

                        $options = array_merge(
                            $options, 
                            array(
                                'xml_url' => $_POST['jupix_xml_url'],
                                'only_updated' => ( (isset($_POST['jupix_xml_only_updated'])) ? $_POST['jupix_xml_only_updated'] : '' ),
                            )
                        );
                        break;
                    }
                    case "xml_vebra_api":
                    {
                        // includes
                        require dirname( __FILE__ ) . '/includes/class-ph-vebra-api-xml-import.php';

                        $PH_Vebra_API_XML_Import = new PH_Vebra_API_XML_Import();

                        $options = array_merge(
                            $options, 
                            array(
                                'username' => trim($_POST['vebra_api_xml_username']),
                                'password' => trim($_POST['vebra_api_xml_password']),
                                'datafeed_id' => trim($_POST['vebra_api_xml_datafeed_id']),
                                'only_updated' => ( (isset($_POST['vebra_api_xml_only_updated'])) ? $_POST['vebra_api_xml_only_updated'] : '' ),
                            )
                        );

                        break;
                    }
                    case "xml_acquaint":
                    {
                        // includes
                        require dirname( __FILE__ ) . '/includes/class-ph-acquaint-xml-import.php';

                        $PH_Acquaint_XML_Import = new PH_Acquaint_XML_Import();

                        $options = array_merge(
                            $options, 
                            array(
                                'xml_url' => $_POST['acquaint_xml_url'],
                            )
                        );
                        break;
                    }
                    case "xml_citylets":
                    {
                        // includes
                        require dirname( __FILE__ ) . '/includes/class-ph-citylets-xml-import.php';

                        $PH_Citylets_XML_Import = new PH_Citylets_XML_Import();

                        $options = array_merge(
                            $options, 
                            array(
                                'xml_url' => $_POST['citylets_xml_url'],
                            )
                        );
                        break;
                    }
                    case "xml_sme_professional":
                    {
                        // includes
                        require dirname( __FILE__ ) . '/includes/class-ph-sme-professional-xml-import.php';

                        $PH_SME_Professional_XML_Import = new PH_SME_Professional_XML_Import();

                        $options = array_merge(
                            $options, 
                            array(
                                'xml_url' => $_POST['sme_professional_xml_url'],
                            )
                        );
                        break;
                    }
                    case "thesaurus":
                    {
                        // includes
                        require dirname( __FILE__ ) . '/includes/class-ph-thesaurus-import.php';

                        $PH_Thesaurus_Import = new PH_Thesaurus_Import();

                        $options = array_merge(
                            $options, 
                            array(
                                'ftp_host' => str_replace("ftp://", "", $_POST['thesaurus_ftp_host']),
                                'ftp_user' => $_POST['thesaurus_ftp_user'],
                                'ftp_pass' => $_POST['thesaurus_ftp_pass'],
                                'ftp_dir' => $_POST['thesaurus_ftp_dir'],
                                'image_ftp_dir' => $_POST['thesaurus_image_ftp_dir'],
                                'brochure_ftp_dir' => $_POST['thesaurus_brochure_ftp_dir'],
                                'ftp_passive' => ( (isset($_POST['thesaurus_ftp_passive'])) ? $_POST['thesaurus_ftp_passive'] : '' ),
                                'filename' => $_POST['thesaurus_filename'],
                                'only_updated' => ( (isset($_POST['thesaurus_only_updated'])) ? $_POST['thesaurus_only_updated'] : '' ),
                            )
                        );

                        break;
                    }
                    case "jet":
                    {
                        // includes
                        require dirname( __FILE__ ) . '/includes/class-ph-jet-import.php';

                        $PH_JET_Import = new PH_JET_Import();

                        $options = array_merge(
                            $options, 
                            array(
                                'url' => $_POST['jet_url'],
                                'user' => $_POST['jet_user'],
                                'pass' => $_POST['jet_pass'],
                                'only_updated' => ( (isset($_POST['jet_only_updated'])) ? $_POST['jet_only_updated'] : '' ),
                            )
                        );

                        break;
                    }
                    case "xml_rentman":
                    {
                        // includes
                        require dirname( __FILE__ ) . '/includes/class-ph-rentman-xml-import.php';

                        $PH_Rentman_XML_Import = new PH_Rentman_XML_Import();

                        $options = array_merge(
                            $options, 
                            array(
                                'local_directory' => $_POST['rentman_xml_directory'],
                            )
                        );


                        break;
                    }
                    case "json_letmc":
                    {
                        // includes
                        require dirname( __FILE__ ) . '/includes/class-ph-letmc-json-import.php';

                        $PH_LetMC_JSON_Import = new PH_LetMC_JSON_Import();

                        $options = array_merge(
                            $options, 
                            array(
                                'api_key' => $_POST['letmc_json_api_key'],
                                'short_name' => $_POST['letmc_json_short_name'],
                            )
                        );

                        break;
                    }
                    case "reaxml_local":
                    {
                        // includes
                        require dirname( __FILE__ ) . '/includes/class-ph-reaxml-import.php';

                        $PH_REAXML_Import = new PH_REAXML_Import( '' );

                        $options = array_merge(
                            $options, 
                            array(
                                'local_directory' => $_POST['reaxml_local_directory'],
                            )
                        );


                        break;
                    }
                    case "xml_10ninety":
                    {
                        // includes
                        require dirname( __FILE__ ) . '/includes/class-ph-10ninety-xml-import.php';

                        $PH_10ninety_XML_Import = new PH_10ninety_XML_Import();

                        $options = array_merge(
                            $options, 
                            array(
                                'xml_url' => $_POST['10ninety_xml_url'],
                            )
                        );
                        break;
                    }
                    case "xml_domus":
                    {
                        // includes
                        require dirname( __FILE__ ) . '/includes/class-ph-domus-xml-import.php';

                        $PH_Domus_XML_Import = new PH_Domus_XML_Import();

                        $options = array_merge(
                            $options, 
                            array(
                                'xml_url' => $_POST['domus_xml_url'],
                            )
                        );
                        break;
                    }
                    case "json_realla":
                    {
                        // includes
                        require dirname( __FILE__ ) . '/includes/class-ph-realla-json-import.php';

                        $PH_Realla_JSON_Import = new PH_Realla_JSON_Import();

                        $options = array_merge(
                            $options, 
                            array(
                                'api_key' => $_POST['realla_api_key'],
                            )
                        );
                        break;
                    }
                    case "json_agency_pilot":
                    {
                        // includes
                        require dirname( __FILE__ ) . '/includes/class-ph-agency-pilot-json-import.php';

                        $PH_Agency_Pilot_JSON_Import = new PH_Agency_Pilot_JSON_Import();

                        $options = array_merge(
                            $options, 
                            array(
                                'url' => trim(str_replace("http://", "", str_replace("https://", "", $_POST['agency_pilot_url']))),
                                'password' => trim($_POST['agency_pilot_password']),
                            )
                        );
                        break;
                    }
                    case "api_agency_pilot":
                    {
                        // includes
                        require dirname( __FILE__ ) . '/includes/class-ph-agency-pilot-api-import.php';

                        $PH_Agency_Pilot_API_Import = new PH_Agency_Pilot_API_Import();

                        $options = array_merge(
                            $options, 
                            array(
                                'url' => trim($_POST['agency_pilot_api_url']),
                                'client_id' => trim($_POST['agency_pilot_api_client_id']),
                                'client_secret' => trim($_POST['agency_pilot_api_client_secret']),
                            )
                        );
                        break;
                    }
                    case "xml_propertyadd":
                    {
                        // includes
                        require dirname( __FILE__ ) . '/includes/class-ph-propertyadd-xml-import.php';

                        $PH_PropertyADD_XML_Import = new PH_PropertyADD_XML_Import();

                        $options = array_merge(
                            $options, 
                            array(
                                'url' => trim($_POST['propertyadd_xml_url']),
                            )
                        );
                        break;
                    }
                    case "xml_gnomen":
                    {
                        // includes
                        require dirname( __FILE__ ) . '/includes/class-ph-gnomen-xml-import.php';

                        $PH_Gnomen_XML_Import = new PH_Gnomen_XML_Import();

                        $options = array_merge(
                            $options, 
                            array(
                                'url' => trim($_POST['gnomen_xml_url']),
                            )
                        );
                        break;
                    }
                    case "xml_webedge":
                    {
                        // includes
                        require dirname( __FILE__ ) . '/includes/class-ph-webedge-xml-import.php';

                        $PH_WebEDGE_XML_Import = new PH_WebEDGE_XML_Import();

                        $options = array_merge(
                            $options, 
                            array(
                                'shared_secret' => trim($_POST['webedge_xml_shared_secret']),
                            )
                        );
                        break;
                    }
                    case "xml_kyero":
                    {
                        // includes
                        require dirname( __FILE__ ) . '/includes/class-ph-kyero-xml-import.php';

                        $PH_Kyero_XML_Import = new PH_Kyero_XML_Import();

                        $options = array_merge(
                            $options, 
                            array(
                                'url' => trim($_POST['kyero_xml_url']),
                            )
                        );
                        break;
                    }
                    case "xml_resales_online":
                    {
                        // includes
                        require dirname( __FILE__ ) . '/includes/class-ph-resales-online-xml-import.php';

                        $PH_ReSales_Online_XML_Import = new PH_ReSales_Online_XML_Import();

                        $options = array_merge(
                            $options, 
                            array(
                                'url' => trim($_POST['resales_online_xml_url']),
                            )
                        );
                        break;
                    }
                    case "json_loop":
                    {
                        // includes
                        require dirname( __FILE__ ) . '/includes/class-ph-loop-json-import.php';

                        $PH_Loop_JSON_Import = new PH_Loop_JSON_Import();

                        $options = array_merge(
                            $options, 
                            array(
                                'url' => trim($_POST['loop_json_url']),
                                'client_id' => trim($_POST['loop_json_client_id']),
                            )
                        );
                        break;
                    }
                    case "json_veco":
                    {
                        // includes
                        require dirname( __FILE__ ) . '/includes/class-ph-veco-json-import.php';

                        $PH_Veco_JSON_Import = new PH_Veco_JSON_Import();

                        $options = array_merge(
                            $options, 
                            array(
                                'access_token' => trim($_POST['veco_json_access_token']),
                                'only_updated' => ( (isset($_POST['veco_json_only_updated'])) ? $_POST['veco_json_only_updated'] : '' ),
                            )
                        );
                        break;
                    }
                    case "xml_estatesit":
                    {
                        // includes
                        require dirname( __FILE__ ) . '/includes/class-ph-estatesit-xml-import.php';

                        $PH_EstatesIT_XML_Import = new PH_EstatesIT_XML_Import();

                        $options = array_merge(
                            $options, 
                            array(
                                'local_directory' => $_POST['xml_estatesit_local_directory'],
                            )
                        );
                        break;
                    }
                    case "xml_juvo":
                    {
                        // includes
                        require dirname( __FILE__ ) . '/includes/class-ph-juvo-xml-import.php';

                        $PH_Juvo_XML_Import = new PH_Juvo_XML_Import();

                        $options = array_merge(
                            $options, 
                            array(
                                'url' => $_POST['juvo_xml_url'],
                            )
                        );
                        break;
                    }
                    case "json_utili":
                    {
                        // includes
                        require dirname( __FILE__ ) . '/includes/class-ph-utili-json-import.php';

                        $PH_Utili_JSON_Import = new PH_Utili_JSON_Import();

                        $options = array_merge(
                            $options, 
                            array(
                                'api_key' => $_POST['utili_json_api_key'],
                                'account_name' => $_POST['utili_json_account_name'],
                            )
                        );
                        break;
                    }
                    case "json_arthur":
                    {
                        // includes
                        require dirname( __FILE__ ) . '/includes/class-ph-arthur-json-import.php';

                        $PH_Arthur_JSON_Import = new PH_Arthur_JSON_Import();

                        $options = array_merge(
                            $options, 
                            array(
                                'client_id' => $_POST['arthur_json_client_id'],
                                'client_secret' => $_POST['arthur_json_client_secret'],
                                'entity_id' => $_POST['arthur_json_entity_id'],
                                'import_structure' => $_POST['arthur_json_import_structure'],
                                'access_token' => $_POST['arthur_json_access_token'],
                                'access_token_expires' => $_POST['arthur_json_access_token_expires'],
                                'refresh_token' => $_POST['arthur_json_refresh_token'],
                            )
                        );
                        break;
                    }
                    case "xml_supercontrol":
                    {
                        // includes
                        require dirname( __FILE__ ) . '/includes/class-ph-supercontrol-xml-import.php';

                        $PH_SuperControl_XML_Import = new PH_SuperControl_XML_Import();

                        $options = array_merge(
                            $options, 
                            array(
                                'client_id' => $_POST['supercontrol_xml_client_id'],
                                'api_key' => $_POST['supercontrol_xml_api_key'],
                            )
                        );
                        break;
                    }
                    case "xml_agentsinsight":
                    {
                        // includes
                        require dirname( __FILE__ ) . '/includes/class-ph-agentsinsight-xml-import.php';

                        $PH_agentsinsight_XML_Import = new PH_agentsinsight_XML_Import();

                        $options = array_merge(
                            $options, 
                            array(
                                'xml_url' => $_POST['agentsinsight_xml_url'],
                                //'only_updated' => ( (isset($_POST['agentsinsight_xml_only_updated'])) ? $_POST['agentsinsight_xml_only_updated'] : '' ),
                            )
                        );
                        break;
                    }
                    case "json_rex":
                    {
                        // includes
                        require dirname( __FILE__ ) . '/includes/class-ph-rex-json-import.php';

                        $PH_Rex_JSON_Import = new PH_Rex_JSON_Import();

                        $options = array_merge(
                            $options, 
                            array(
                                'username' => $_POST['rex_json_username'],
                                'password' => $_POST['rex_json_password'],
                            )
                        );
                        break;
                    }
                    case "xml_decorus":
                    {
                        // includes
                        require dirname( __FILE__ ) . '/includes/class-ph-decorus-xml-import.php';

                        $PH_Decorus_XML_Import = new PH_Decorus_XML_Import();

                        $options = array_merge(
                            $options, 
                            array(
                                'local_directory' => $_POST['decorus_xml_directory'],
                            )
                        );
                        break;
                    }
                    case "xml_mri":
                    {
                        // includes
                        require dirname( __FILE__ ) . '/includes/class-ph-mri-xml-import.php';

                        $PH_MRI_XML_Import = new PH_MRI_XML_Import();

                        $options = array_merge(
                            $options, 
                            array(
                                'url' => $_POST['mri_xml_url'],
                                'password' => $_POST['mri_xml_password'],
                            )
                        );
                        break;
                    }
                }

                $options = apply_filters( 'propertyhive_property_import_setup_details_save', $options );
                
                $import_id = $_POST['import_id'];
                if ( $import_id == '' )
                {
                    $import_id = time();
                }
                $previous_options = get_option( 'propertyhive_property_import' );
                $previous_options[$import_id] = $options;
                update_option( 'propertyhive_property_import', $previous_options );

                // Setup schedule

                $ok_to_import = true;
                $format = $_POST['format'];

                flush_rewrite_rules(false); // used for WebEDGE format where new rewrite rule is being setup
            }            
        }
        elseif ( isset($_POST['import']) && $_POST['import'] == 1 )
        {
            // Do import

            $manual_automatic = $_POST['manual_automatic'];
            $format = $format;

            if ( $_POST['manual_automatic'] == 'manual' )
            {
                if ( isset($_POST['target_file']) && file_exists($_POST['target_file']) && is_readable($_POST['target_file']) )
                {
                    // Load Importer API
                    require_once ABSPATH . 'wp-admin/includes/import.php';

                    if ( ! class_exists( 'WP_Importer' ) ) {
                        $class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
                        if ( file_exists( $class_wp_importer ) ) require $class_wp_importer;
                    }

                    if ( $_POST['format'] == 'csv' )
                    {
                        // includes
                        require dirname( __FILE__ ) . '/includes/class-ph-csv-import.php';

                        // File is uploaded and we're ready to start importing
                        $PH_Import = new PH_CSV_Import( $_POST['target_file'] );
                    }
                    else
                    {
                        // includes
                        require dirname( __FILE__ ) . '/includes/class-ph-blm-import.php';

                        // File is uploaded and we're ready to start importing
                        $PH_Import = new PH_BLM_Import( $_POST['target_file'] );
                    }
                    
                    $PH_Import->parse();

                    $PH_Import->import();

                    unlink($_POST['target_file']);

                    $imported = true;
                }
                else
                {
                    $errors[] = 'Something went wrong. The file you uploaded no longer exists. Possibly a permissions issue? Please try again';
                }
            }
            elseif ( $_POST['manual_automatic'] == 'automatic' )
            {
                // save mappings
                
                $mappings = array();

                if ( isset( $_POST['mapped_department'] ) )
                {
                    $mappings['department'] = $_POST['mapped_department'];
                }
                if ( isset($_POST['custom_mapping']['department']) && $_POST['custom_mapping']['department'] != '' )
                {
                    foreach ( $_POST['custom_mapping']['department'] as $key => $custom_mapping )
                    {
                        if ( trim($custom_mapping) != '' )
                        {
                            if ( isset($_POST['custom_mapping_value']['department'][$key]) && trim($_POST['custom_mapping_value']['department'][$key]) != '' )
                            {
                                if ( !isset($mappings['department']) ) { $mappings['department'] = array(); }
                                $mappings['department'][$custom_mapping] = $_POST['custom_mapping_value']['department'][$key];
                            }
                        }
                    }
                }

                if ( isset( $_POST['mapped_availability'] ) )
                {
                    $mappings['availability'] = $_POST['mapped_availability'];
                }
                if ( isset($_POST['custom_mapping']['availability']) )
                {
                    foreach ( $_POST['custom_mapping']['availability'] as $key => $custom_mapping )
                    {
                        if ( trim($custom_mapping) != '' )
                        {
                            if ( isset($_POST['custom_mapping_value']['availability'][$key]) && trim($_POST['custom_mapping_value']['availability'][$key]) != '' )
                            {
                                if ( !isset($mappings['availability']) ) { $mappings['availability'] = array(); }
                                $mappings['availability'][$custom_mapping] = $_POST['custom_mapping_value']['availability'][$key];
                            }
                        }
                    }
                }

                if ( isset( $_POST['mapped_sales_availability'] ) )
                {
                    $mappings['sales_availability'] = $_POST['mapped_sales_availability'];
                }
                if ( isset($_POST['custom_mapping']['sales_availability']) )
                {
                    foreach ( $_POST['custom_mapping']['sales_availability'] as $key => $custom_mapping )
                    {
                        if ( trim($custom_mapping) != '' )
                        {
                            if ( isset($_POST['custom_mapping_value']['sales_availability'][$key]) && trim($_POST['custom_mapping_value']['sales_availability'][$key]) != '' )
                            {
                                if ( !isset($mappings['sales_availability']) ) { $mappings['sales_availability'] = array(); }
                                $mappings['sales_availability'][$custom_mapping] = $_POST['custom_mapping_value']['sales_availability'][$key];
                            }
                        }
                    }
                }

                if ( isset( $_POST['mapped_lettings_availability'] ) )
                {
                    $mappings['lettings_availability'] = $_POST['mapped_lettings_availability'];
                }
                if ( isset($_POST['custom_mapping']['lettings_availability']) )
                {
                    foreach ( $_POST['custom_mapping']['lettings_availability'] as $key => $custom_mapping )
                    {
                        if ( trim($custom_mapping) != '' )
                        {
                            if ( isset($_POST['custom_mapping_value']['lettings_availability'][$key]) && trim($_POST['custom_mapping_value']['lettings_availability'][$key]) != '' )
                            {
                                if ( !isset($mappings['lettings_availability']) ) { $mappings['lettings_availability'] = array(); }
                                $mappings['lettings_availability'][$custom_mapping] = $_POST['custom_mapping_value']['lettings_availability'][$key];
                            }
                        }
                    }
                }

                if ( isset( $_POST['mapped_commercial_availability'] ) )
                {
                    $mappings['commercial_availability'] = $_POST['mapped_commercial_availability'];
                }
                if ( isset($_POST['custom_mapping']['commercial_availability']) )
                {
                    foreach ( $_POST['custom_mapping']['commercial_availability'] as $key => $custom_mapping )
                    {
                        if ( trim($custom_mapping) != '' )
                        {
                            if ( isset($_POST['custom_mapping_value']['commercial_availability'][$key]) && trim($_POST['custom_mapping_value']['commercial_availability'][$key]) != '' )
                            {
                                if ( !isset($mappings['commercial_availability']) ) { $mappings['commercial_availability'] = array(); }
                                $mappings['commercial_availability'][$custom_mapping] = $_POST['custom_mapping_value']['commercial_availability'][$key];
                            }
                        }
                    }
                }

                if ( isset( $_POST['mapped_property_type'] ) )
                {
                    $mappings['property_type'] = $_POST['mapped_property_type'];
                }
                if ( isset($_POST['custom_mapping']['property_type']) && $_POST['custom_mapping']['property_type'] != '' )
                {
                    foreach ( $_POST['custom_mapping']['property_type'] as $key => $custom_mapping )
                    {
                        if ( trim($custom_mapping) != '' )
                        {
                            if ( isset($_POST['custom_mapping_value']['property_type'][$key]) && trim($_POST['custom_mapping_value']['property_type'][$key]) != '' )
                            {
                                if ( !isset($mappings['property_type']) ) { $mappings['property_type'] = array(); }
                                $mappings['property_type'][$custom_mapping] = $_POST['custom_mapping_value']['property_type'][$key];
                            }
                        }
                    }
                }

                if ( isset( $_POST['mapped_commercial_property_type'] ) )
                {
                    $mappings['commercial_property_type'] = $_POST['mapped_commercial_property_type'];
                }
                if ( isset($_POST['custom_mapping']['commercial_property_type']) && $_POST['custom_mapping']['commercial_property_type'] != '' )
                {
                    foreach ( $_POST['custom_mapping']['commercial_property_type'] as $key => $custom_mapping )
                    {
                        if ( trim($custom_mapping) != '' )
                        {
                            if ( isset($_POST['custom_mapping_value']['commercial_property_type'][$key]) && trim($_POST['custom_mapping_value']['commercial_property_type'][$key]) != '' )
                            {
                                if ( !isset($mappings['commercial_property_type']) ) { $mappings['commercial_property_type'] = array(); }
                                $mappings['commercial_property_type'][$custom_mapping] = $_POST['custom_mapping_value']['commercial_property_type'][$key];
                            }
                        }
                    }
                }

                if ( isset( $_POST['mapped_price_qualifier'] ) )
                {
                    $mappings['price_qualifier'] = $_POST['mapped_price_qualifier'];
                }
                if ( isset($_POST['custom_mapping']['price_qualifier']) && $_POST['custom_mapping']['price_qualifier'] != '' )
                {
                    foreach ( $_POST['custom_mapping']['price_qualifier'] as $key => $custom_mapping )
                    {
                        if ( trim($custom_mapping) != '' )
                        {
                            if ( isset($_POST['custom_mapping_value']['price_qualifier'][$key]) && trim($_POST['custom_mapping_value']['price_qualifier'][$key]) != '' )
                            {
                                if ( !isset($mappings['price_qualifier']) ) { $mappings['price_qualifier'] = array(); }
                                $mappings['price_qualifier'][$custom_mapping] = $_POST['custom_mapping_value']['price_qualifier'][$key];
                            }
                        }
                    }
                }

                if ( isset( $_POST['mapped_tenure'] ) )
                {
                    $mappings['tenure'] = $_POST['mapped_tenure'];
                }
                if ( isset($_POST['custom_mapping']['tenure']) && $_POST['custom_mapping']['tenure'] != '' )
                {
                    foreach ( $_POST['custom_mapping']['tenure'] as $key => $custom_mapping )
                    {
                        if ( trim($custom_mapping) != '' )
                        {
                            if ( isset($_POST['custom_mapping_value']['tenure'][$key]) && trim($_POST['custom_mapping_value']['tenure'][$key]) != '' )
                            {
                                if ( !isset($mappings['tenure']) ) { $mappings['tenure'] = array(); }
                                $mappings['tenure'][$custom_mapping] = $_POST['custom_mapping_value']['tenure'][$key];
                            }
                        }
                    }
                }

                if ( isset( $_POST['mapped_commercial_tenure'] ) )
                {
                    $mappings['commercial_tenure'] = $_POST['mapped_commercial_tenure'];
                }
                if ( isset($_POST['custom_mapping']['commercial_tenure']) && $_POST['custom_mapping']['commercial_tenure'] != '' )
                {
                    foreach ( $_POST['custom_mapping']['commercial_tenure'] as $key => $custom_mapping )
                    {
                        if ( trim($custom_mapping) != '' )
                        {
                            if ( isset($_POST['custom_mapping_value']['commercial_tenure'][$key]) && trim($_POST['custom_mapping_value']['commercial_tenure'][$key]) != '' )
                            {
                                if ( !isset($mappings['commercial_tenure']) ) { $mappings['commercial_tenure'] = array(); }
                                $mappings['commercial_tenure'][$custom_mapping] = $_POST['custom_mapping_value']['commercial_tenure'][$key];
                            }
                        }
                    }
                }

                if ( isset( $_POST['mapped_sale_by'] ) )
                {
                    $mappings['sale_by'] = $_POST['mapped_sale_by'];
                }
                if ( isset($_POST['custom_mapping']['sale_by']) && $_POST['custom_mapping']['sale_by'] != '' )
                {
                    foreach ( $_POST['custom_mapping']['sale_by'] as $key => $custom_mapping )
                    {
                        if ( trim($custom_mapping) != '' )
                        {
                            if ( isset($_POST['custom_mapping_value']['sale_by'][$key]) && trim($_POST['custom_mapping_value']['sale_by'][$key]) != '' )
                            {
                                if ( !isset($mappings['sale_by']) ) { $mappings['sale_by'] = array(); }
                                $mappings['sale_by'][$custom_mapping] = $_POST['custom_mapping_value']['sale_by'][$key];
                            }
                        }
                    }
                }

                if ( isset( $_POST['mapped_furnished'] ) )
                {
                    $mappings['furnished'] = $_POST['mapped_furnished'];
                }
                if ( isset($_POST['custom_mapping']['furnished']) && $_POST['custom_mapping']['furnished'] != '' )
                {
                    foreach ( $_POST['custom_mapping']['furnished'] as $key => $custom_mapping )
                    {
                        if ( trim($custom_mapping) != '' )
                        {
                            if ( isset($_POST['custom_mapping_value']['furnished'][$key]) && trim($_POST['custom_mapping_value']['furnished'][$key]) != '' )
                            {
                                if ( !isset($mappings['furnished']) ) { $mappings['furnished'] = array(); }
                                $mappings['furnished'][$custom_mapping] = $_POST['custom_mapping_value']['furnished'][$key];
                            }
                        }
                    }
                }

                if ( isset( $_POST['mapped_location'] ) )
                {
                    $mappings['location'] = $_POST['mapped_location'];
                }
                if ( isset($_POST['custom_mapping']['location']) && $_POST['custom_mapping']['location'] != '' )
                {
                    foreach ( $_POST['custom_mapping']['location'] as $key => $custom_mapping )
                    {
                        if ( trim($custom_mapping) != '' )
                        {
                            if ( isset($_POST['custom_mapping_value']['location'][$key]) && trim($_POST['custom_mapping_value']['location'][$key]) != '' )
                            {
                                if ( !isset($mappings['location']) ) { $mappings['location'] = array(); }
                                $mappings['location'][$custom_mapping] = $_POST['custom_mapping_value']['location'][$key];
                            }
                        }
                    }
                }

                if ( isset( $_POST['mapped_outside_space'] ) )
                {
                    $mappings['outside_space'] = $_POST['mapped_outside_space'];
                }
                if ( isset($_POST['custom_mapping']['outside_space']) && $_POST['custom_mapping']['outside_space'] != '' )
                {
                    foreach ( $_POST['custom_mapping']['outside_space'] as $key => $custom_mapping )
                    {
                        if ( trim($custom_mapping) != '' )
                        {
                            if ( isset($_POST['custom_mapping_value']['outside_space'][$key]) && trim($_POST['custom_mapping_value']['outside_space'][$key]) != '' )
                            {
                                if ( !isset($mappings['outside_space']) ) { $mappings['outside_space'] = array(); }
                                $mappings['outside_space'][$custom_mapping] = $_POST['custom_mapping_value']['outside_space'][$key];
                            }
                        }
                    }
                }

                if ( isset( $_POST['mapped_parking'] ) )
                {
                    $mappings['parking'] = $_POST['mapped_parking'];
                }
                if ( isset($_POST['custom_mapping']['parking']) && $_POST['custom_mapping']['parking'] != '' )
                {
                    foreach ( $_POST['custom_mapping']['parking'] as $key => $custom_mapping )
                    {
                        if ( trim($custom_mapping) != '' )
                        {
                            if ( isset($_POST['custom_mapping_value']['parking'][$key]) && trim($_POST['custom_mapping_value']['parking'][$key]) != '' )
                            {
                                if ( !isset($mappings['parking']) ) { $mappings['parking'] = array(); }
                                $mappings['parking'][$custom_mapping] = $_POST['custom_mapping_value']['parking'][$key];
                            }
                        }
                    }
                }

                $options = get_option( 'propertyhive_property_import' );
                $options[$_POST['import_id']]['mappings'] = $mappings;

                update_option( 'propertyhive_property_import', $options );

                $imported = true;
            }
            else
            {
                $errors[] = 'Whoops. We\'re missing whether the feed is manual or automatic';
            }
        }
        elseif ( isset($_POST['csv_mapping']) && $_POST['csv_mapping'] == 1 )
        {
            // Save fields so we have them stored for next time, or if any validation errors
            if ( isset($_POST['column_mapping']) && is_array($_POST['column_mapping']) && !empty($_POST['column_mapping']) )
            {
                update_option( 'propertyhive_property_import_csv_column_mapping', $_POST['column_mapping'] );

                // Load Importer API
                require_once ABSPATH . 'wp-admin/includes/import.php';

                if ( ! class_exists( 'WP_Importer' ) ) {
                    $class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
                    if ( file_exists( $class_wp_importer ) ) require $class_wp_importer;
                }

                require dirname( __FILE__ ) . '/includes/class-ph-csv-import.php';

                $PH_CSV_Import = new PH_CSV_Import( $_POST['target_file'] );

                $PH_CSV_Import->parse();

                list( $passed_properties, $failed_properties ) = $PH_CSV_Import->pre_test();

                $pre_test_errors = $PH_CSV_Import->get_errors();

                if ( empty($pre_test_errors) )
                {
                    $ok_to_import = true;
                }
            }
        }
        else
        {
            // pre_test not set
        }

        if ( 
            !$do_csv_mapping && 
            empty($errors) && 
            (
                (isset($_POST['pre_test']) && $_POST['pre_test'] == '1')
                || 
                (isset($_POST['csv_mapping']) && $_POST['csv_mapping'] == '1')
            )
        )
        {
            echo '<div class="' . ( ( $failed_properties > 0 ) ? 'error' : 'updated' ) . '">
            <p><strong>Validation Results:</strong><br>';
            if ( isset($PH_BLM_Import) )
            {
                echo $passed_properties . ' / ' . count( $PH_BLM_Import->get_properties() ) . ' properties passed validation<br>
                ' . $failed_properties . ' / ' . count( $PH_BLM_Import->get_properties() ) . ' properties failed validation';
            }
            elseif ( isset($PH_CSV_Import) )
            {
                echo $passed_properties . ' / ' . count( $PH_CSV_Import->get_properties() ) . ' properties passed validation<br>
                ' . $failed_properties . ' / ' . count( $PH_CSV_Import->get_properties() ) . ' properties failed validation';
               
            }
            echo ( ( $failed_properties > 0 ) ? '<br><br>All properties must pass validation before being able to continue' : '' ) . '
            </p>
            </div>';

            if (isset($PH_CSV_Import) && $failed_properties == 0)
            {
                echo '<form method="post">';
                 echo '<p class="submit">
                    <input type="hidden" name="import" value="1">
        <input type="hidden" name="import_id" value="">
        <input type="hidden" name="format" value="csv">
        <input type="hidden" name="manual_automatic" value="manual">
        <input type="hidden" name="target_file" value="' . $_POST['target_file'] . '">
        <a onclick="if (importing) { return false; }" id="cancel_import_step" href="' . admin_url('admin.php?page=propertyhive_import_properties') . '" class="button">Cancel</a>
        <input name="save" id="save_import_step" class="button-primary" type="submit" value="Import Properties" onclick="importing = true; setTimeout(function() { document.getElementById(\'save_import_step\').disabled=\'disabled\'; }, 1);">
                </p>';
                echo '</form>';
                die();
            }
        }
        if ( isset($pre_test_errors) && is_array($pre_test_errors) && !empty($pre_test_errors) )
        {
            $errors = array_merge($errors, $pre_test_errors);
        }

        if ( $ok_to_import )
        {
            if ( isset($_POST['manual_automatic']) && $_POST['manual_automatic'] == 'manual' )
            {
                // make sure the file still exists
                if ( !file_exists($target_file) || !is_readable($target_file) )
                {
                    $errors[] = 'Something went wrong. The file you uploaded no longer exists. Possibly a permissions issue? Please try again';
                    $ok_to_import = false;
                }
            }
            elseif ( isset($_POST['manual_automatic']) && $_POST['manual_automatic'] == 'automatic' )
            {
                //echo '<div class="updated"><p>Your automatic import has been setup and will first run </strong></p></div>';
            }
        }

        if ( !empty($errors) )
        {
            echo '<div class="error"><p><strong>' . implode("<br>", $errors) . '</strong></p></div>';
        }
    ?>

        <div class="wrap propertyhive">

            <h1><?php _e('Import Properties', 'propertyhive'); ?></h1>
            
            <?php
                if ( isset($_GET['logs']) && $_GET['logs'] != '' )
                {
                    $this->logs( $_GET['logs'] );
                }
                elseif ( isset($_GET['log']) && $_GET['log'] != '' )
                {
                    $this->log( $_GET['log'] );
                }
                elseif ( $imported )
                {
                    $object = '';
                    if ( isset($PH_ExpertAgent_XML_Import) )
                    {
                        $object = $PH_ExpertAgent_XML_Import;
                    }
                    elseif ( isset($PH_Dezrez_XML_Import) )
                    {
                        $object = $PH_Dezrez_XML_Import;
                    }
                    elseif ( isset($PH_Dezrez_JSON_Import) )
                    {
                        $object = $PH_Dezrez_JSON_Import;
                    }
                    elseif ( isset($PH_BLM_Import) )
                    {
                        $object = $PH_BLM_Import;
                    }
                    elseif ( isset($PH_Jupix_XML_Import) )
                    {
                        $object = $PH_Jupix_XML_Import;
                    }
                    elseif ( isset($PH_Vebra_API_XML_Import) )
                    {
                        $object = $PH_Vebra_API_XML_Import;
                    }
                    elseif ( isset($PH_Acquaint_XML_Import) )
                    {
                        $object = $PH_Acquaint_XML_Import;
                    }
                    elseif ( isset($PH_Citylets_XML_Import) )
                    {
                        $object = $PH_Citylets_XML_Import;
                    }
                    elseif ( isset($PH_SME_Professional_XML_Import) )
                    {
                        $object = $PH_SME_Professional_XML_Import;
                    }
                    elseif ( isset($PH_Thesaurus_Import) )
                    {
                        $object = $PH_Thesaurus_Import;
                    }
                    elseif ( isset($PH_JET_Import) )
                    {
                        $object = $PH_JET_Import;
                    }
                    elseif ( isset($PH_Rentman_XML_Import) )
                    {
                        $object = $PH_Rentman_XML_Import;
                    }
                    elseif ( isset($PH_LetMC_JSON_Import) )
                    {
                        $object = $PH_LetMC_JSON_Import;
                    }
                    elseif ( isset($PH_REAXML_Import) )
                    {
                        $object = $PH_REAXML_Import;
                    }
                    elseif ( isset($PH_10ninety_XML_Import) )
                    {
                        $object = $PH_10ninety_XML_Import;
                    }
                    elseif ( isset($PH_Domus_XML_Import) )
                    {
                        $object = $PH_Domus_XML_Import;
                    }
                    elseif ( isset($PH_Realla_JSON_Import) )
                    {
                        $object = $PH_Realla_JSON_Import;
                    }
                    elseif ( isset($PH_Agency_Pilot_JSON_Import) )
                    {
                        $object = $PH_Agency_Pilot_JSON_Import;
                    }
                    elseif ( isset($PH_Agency_Pilot_API_Import) )
                    {
                        $object = $PH_Agency_Pilot_API_Import;
                    }
                    elseif ( isset($PH_PropertyADD_XML_Import) )
                    {
                        $object = $PH_PropertyADD_XML_Import;
                    }
                    elseif ( isset($PH_Gnomen_XML_Import) )
                    {
                        $object = $PH_Gnomen_XML_Import;
                    }
                    elseif ( isset($PH_WebEDGE_XML_Import) )
                    {
                        $object = $PH_WebEDGE_XML_Import;
                    }
                    elseif ( isset($PH_Kyero_XML_Import) )
                    {
                        $object = $PH_Kyero_XML_Import;
                    }
                    elseif ( isset($PH_ReSales_Online_XML_Import) )
                    {
                        $object = $PH_ReSales_Online_XML_Import;
                    }
                    elseif ( isset($PH_Loop_JSON_Import) )
                    {
                        $object = $PH_Loop_JSON_Import;
                    }
                    elseif ( isset($PH_Veco_JSON_Import) )
                    {
                        $object = $PH_Veco_JSON_Import;
                    }
                    elseif ( isset($PH_EstatesIT_XML_Import) )
                    {
                        $object = $PH_EstatesIT_XML_Import;
                    }
                    elseif ( isset($PH_Juvo_XML_Import) )
                    {
                        $object = $PH_Juvo_XML_Import;
                    }
                    elseif ( isset($PH_Utili_JSON_Import) )
                    {
                        $object = $PH_Utili_JSON_Import;
                    }
                    elseif ( isset($PH_Arthur_JSON_Import) )
                    {
                        $object = $PH_Arthur_JSON_Import;
                    }
                    elseif ( isset($PH_SuperControl_XML_Import) )
                    {
                        $object = $PH_SuperControl_XML_Import;
                    }
                    elseif ( isset($PH_Import) )
                    {
                        $object = $PH_Import;
                    }
                    elseif ( isset($PH_agentsinsight_XML_Import) )
                    {
                        $object = $PH_agentsinsight_XML_Import;
                    }
                    elseif ( isset($PH_Rex_JSON_Import) )
                    {
                        $object = $PH_Rex_JSON_Import;
                    }
                    elseif ( isset($PH_Decorus_XML_Import) )
                    {
                        $object = $PH_Decorus_XML_Import;
                    }
                    elseif ( isset($PH_MRI_XML_Import) )
                    {
                        $object = $PH_MRI_XML_Import;
                    }
                    $object = apply_filters( 'propertyhive_property_import_object', $object, $format );
                    $this->step_three( $object, $manual_automatic );
                }
                elseif ( $ok_to_import )
                {
                    $object = '';
                    if ( isset($PH_ExpertAgent_XML_Import) )
                    {
                        $object = $PH_ExpertAgent_XML_Import;
                    }
                    elseif ( isset($PH_Dezrez_XML_Import) )
                    {
                        $object = $PH_Dezrez_XML_Import;
                    }
                    elseif ( isset($PH_Dezrez_JSON_Import) )
                    {
                        $object = $PH_Dezrez_JSON_Import;
                    }
                    elseif ( isset($PH_BLM_Import) )
                    {
                        $object = $PH_BLM_Import;
                    }
                    elseif ( isset($PH_Jupix_XML_Import) )
                    {
                        $object = $PH_Jupix_XML_Import;
                    }
                    elseif ( isset($PH_Vebra_API_XML_Import) )
                    {
                        $object = $PH_Vebra_API_XML_Import;
                    }
                    elseif ( isset($PH_Acquaint_XML_Import) )
                    {
                        $object = $PH_Acquaint_XML_Import;
                    }
                    elseif ( isset($PH_Citylets_XML_Import) )
                    {
                        $object = $PH_Citylets_XML_Import;
                    }
                    elseif ( isset($PH_SME_Professional_XML_Import) )
                    {
                        $object = $PH_SME_Professional_XML_Import;
                    }
                    elseif ( isset($PH_Thesaurus_Import) )
                    {
                        $object = $PH_Thesaurus_Import;
                    }
                    elseif ( isset($PH_JET_Import) )
                    {
                        $object = $PH_JET_Import;
                    }
                    elseif ( isset($PH_Rentman_XML_Import) )
                    {
                        $object = $PH_Rentman_XML_Import;
                    }
                    elseif ( isset($PH_LetMC_JSON_Import) )
                    {
                        $object = $PH_LetMC_JSON_Import;
                    }
                    elseif ( isset($PH_REAXML_Import) )
                    {
                        $object = $PH_REAXML_Import;
                    }
                    elseif ( isset($PH_10ninety_XML_Import) )
                    {
                        $object = $PH_10ninety_XML_Import;
                    }
                    elseif ( isset($PH_Domus_XML_Import) )
                    {
                        $object = $PH_Domus_XML_Import;
                    }
                    elseif ( isset($PH_Realla_JSON_Import) )
                    {
                        $object = $PH_Realla_JSON_Import;
                    }
                    elseif ( isset($PH_Agency_Pilot_JSON_Import) )
                    {
                        $object = $PH_Agency_Pilot_JSON_Import;
                    }
                    elseif ( isset($PH_Agency_Pilot_API_Import) )
                    {
                        $object = $PH_Agency_Pilot_API_Import;
                    }
                    elseif ( isset($PH_PropertyADD_XML_Import) )
                    {
                        $object = $PH_PropertyADD_XML_Import;
                    }
                    elseif ( isset($PH_Gnomen_XML_Import) )
                    {
                        $object = $PH_Gnomen_XML_Import;
                    }
                    elseif ( isset($PH_WebEDGE_XML_Import) )
                    {
                        $object = $PH_WebEDGE_XML_Import;
                    }
                    elseif ( isset($PH_Kyero_XML_Import) )
                    {
                        $object = $PH_Kyero_XML_Import;
                    }
                    elseif ( isset($PH_ReSales_Online_XML_Import) )
                    {
                        $object = $PH_ReSales_Online_XML_Import;
                    }
                    elseif ( isset($PH_Loop_JSON_Import) )
                    {
                        $object = $PH_Loop_JSON_Import;
                    }
                    elseif ( isset($PH_Veco_JSON_Import) )
                    {
                        $object = $PH_Veco_JSON_Import;
                    }
                    elseif ( isset($PH_EstatesIT_XML_Import) )
                    {
                        $object = $PH_EstatesIT_XML_Import;
                    }
                    elseif ( isset($PH_Juvo_XML_Import) )
                    {
                        $object = $PH_Juvo_XML_Import;
                    }
                    elseif ( isset($PH_Utili_JSON_Import) )
                    {
                        $object = $PH_Utili_JSON_Import;
                    }
                    elseif ( isset($PH_Arthur_JSON_Import) )
                    {
                        $object = $PH_Arthur_JSON_Import;
                    }
                    elseif ( isset($PH_SuperControl_XML_Import) )
                    {
                        $object = $PH_SuperControl_XML_Import;
                    }
                    elseif ( isset($PH_Import) )
                    {
                        $object = $PH_Import;
                    }
                    elseif ( isset($PH_agentsinsight_XML_Import) )
                    {
                        $object = $PH_agentsinsight_XML_Import;
                    }
                    elseif ( isset($PH_Rex_JSON_Import) )
                    {
                        $object = $PH_Rex_JSON_Import;
                    }
                    elseif ( isset($PH_Decorus_XML_Import) )
                    {
                        $object = $PH_Decorus_XML_Import;
                    }
                    elseif ( isset($PH_MRI_XML_Import) )
                    {
                        $object = $PH_MRI_XML_Import;
                    }
                    $object = apply_filters( 'propertyhive_property_import_object', $object, $format );
                    $this->step_two( ( isset($target_file) ? $target_file : '' ), $object, $manual_automatic, ( (isset($import_id) ? $import_id : '' ) ) );
                }
                elseif ( $do_csv_mapping )
                {
                    $this->step_csv_mapping( $PH_Import );
                }
                else
                {
                    if ( ( isset($_POST['manual_automatic']) && $_POST['manual_automatic'] != '' ) || ( isset($_GET['edit']) && $_GET['edit'] != '' ) )
                    {
                        $options = get_option( 'propertyhive_property_import' );

                        if ( ( isset($_GET['edit']) && $_GET['edit'] != '' ) && !isset($options[$_GET['edit']]) )
                        {
                            die('Invalid automatic import1. Please go back and try again');
                        }

                        $this->step_one();
                    }
                    else
                    {
                        $this->step_zero();
                    }
                }
            ?>

        </div>

    <?php

    }

    // Choosing manual vs automatic
    private function step_zero()
    {
        global $wpdb;

        include( __DIR__ . "/steps/zero.php");
    }

    // Uploading manual file or setting automatic options
    private function step_one()
    {
        global $wpdb;

        include( __DIR__ . "/steps/one.php");
    }

    // Set custom field mapping
    private function step_two( $target_file, $PH_Import_Instance, $manual_automatic, $import_id = '' )
    {
        global $wpdb;

        include( __DIR__ . "/steps/two.php");
    }

    // Finished / logs
    private function step_three( $PH_Import, $manual_automatic )
    {
        include( __DIR__ . "/steps/three.php");
    }

    private function step_csv_mapping( $PH_Import )
    {
        $column_headers = $PH_Import->get_csv_column_headers();
        $propertyhive_fields = $PH_Import->get_propertyhive_fields();

        $column_mappings = get_option( 'propertyhive_property_import_csv_column_mapping', array() );
        if ( !is_array($column_mappings) )
        {
            $column_mappings = array();
        }

        $target_file = $PH_Import->get_target_file();

        include( __DIR__ . "/steps/csv-mapping.php");
    }

    // logs
    private function logs( $import_id )
    {
        global $wpdb;

        include( __DIR__ . "/steps/logs.php");
    }

    // log
    private function log( $instance_id )
    {
        global $wpdb;

        include( __DIR__ . "/steps/log.php");
    }
}

endif;

/**
 * Returns the main instance of PH_Property_Import to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return PH_Property_Import
 */
function PHPI() {
    return PH_Property_Import::instance();
}

PHPI();

if( is_admin() && file_exists(  dirname( __FILE__ ) . '/propertyhive-property-import-update.php' ) )
{
    include_once( dirname( __FILE__ ) . '/propertyhive-property-import-update.php' );
}