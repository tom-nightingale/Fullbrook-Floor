<?php

class PH_Property_Import_Process {

	/**
	 * @var int
	 */
	public $instance_id;

	/**
	 * @var array
	 */
	public $properties;

	/**
	 * @var array
	 */
	public $errors;

	/**
	 * @var array
	 */
	public $mappings;

	/**
	 * @var array
	 */
	public $import_log;

    public function __construct() 
    {

    }

    public function get_properties()
	{
		return $this->properties;
	}

	public function import_start()
	{
		wp_suspend_cache_invalidation( true );

		wp_defer_term_counting( true );
		wp_defer_comment_counting( true );
	}

	public function import_end()
	{
		wp_cache_flush();

		wp_suspend_cache_invalidation( false );

		wp_defer_term_counting( false );
		wp_defer_comment_counting( false );
	}

	public function delete_media( $post_id, $meta_key, $except_first = false )
	{
		$media_ids = get_post_meta( $post_id, $meta_key, TRUE );
		if ( !empty( $media_ids ) )
		{
			$i = 0;
			foreach ( $media_ids as $media_id )
			{
				if ( !$except_first || ( $except_first && $i > 0 ) )
				{
					if ( wp_delete_attachment( $media_id, TRUE ) !== FALSE )
					{
						// Deleted succesfully. Now remove from array
						if( ($key = array_search($media_id, $media_ids)) !== false)
						{
						    unset($media_ids[$key]);
						}
					}
					else
					{
						$this->add_error( 'Failed to delete ' . $meta_key . ' with attachment ID ' . $media_id, get_post_meta($post_id, $imported_ref_key, TRUE) );
					}
				}
				++$i;
			}
		}
		update_post_meta( $post_id, $meta_key, $media_ids );
	}

	public function add_missing_mapping( $mappings, $custom_field, $value, $import_id = '' )
	{
		$options = get_option( 'propertyhive_property_import' );

		if ( ph_clean($value) != '' && !isset($mappings[$custom_field][$value]) )
		{
			$mappings[$custom_field][$value] = '';

			if ( $import_id != '' && isset($options[$import_id]) )
			{
				$options[$import_id]['mappings'][$custom_field][$value] = '';

				update_option( 'propertyhive_property_import', $options );

				$this->add_log( 'Added new option (' . ph_clean($value) . ') to ' . $custom_field . ' mappings that you will need to assign' );
			}
		}

		if ( $import_id != '' && isset($options[$import_id]) )
		{
			return $options[$import_id];
		}

		return array();
	}

	public function add_error( $message, $agent_ref = '' )
	{
		$this->errors[] = date("Y-m-d H:i:s") . ' - ' . ( ( $agent_ref != '' ) ? 'AGENT_REF: ' . $agent_ref . ' - ' : '' ) . $message;

		if ( $this->instance_id != '' )
		{
			global $wpdb;
        
	        $wpdb->insert( 
	            $wpdb->prefix . "ph_propertyimport_logs_instance_log", 
	            array(
	                'instance_id' => $this->instance_id,
	                'severity' => 1,
	                'entry' => substr( ( ( $agent_ref != '' ) ? 'AGENT_REF: ' . $agent_ref . ' - ' : '' ) . $message, 0, 255),
	                'log_date' => date("Y-m-d H:i:s")
	            )
	        );

	        if ( defined( 'WP_CLI' ) && WP_CLI )
        	{
        		WP_CLI::log( date("Y-m-d H:i:s") . ' - ' . ( ( $agent_ref != '' ) ? 'AGENT_REF: ' . $agent_ref . ' - ' : '' ) . $message );
        	}
		}
	}

	public function get_import_log()
	{
		return $this->import_log;
	}

	public function get_errors()
	{
		return $this->errors;
	}

	public function add_log( $message, $agent_ref = '' )
	{
		if ( $this->instance_id != '' )
		{
			global $wpdb;
        
	        $wpdb->insert( 
	            $wpdb->prefix . "ph_propertyimport_logs_instance_log", 
	            array(
	                'instance_id' => $this->instance_id,
	                'severity' => 0,
	                'entry' => substr( ( ( $agent_ref != '' ) ? 'AGENT_REF: ' . $agent_ref . ' - ' : '' ) . $message, 0, 255),
	                'log_date' => date("Y-m-d H:i:s")
	            )
	        );

	        if ( defined( 'WP_CLI' ) && WP_CLI )
        	{
        		WP_CLI::log( date("Y-m-d H:i:s") . ' - ' . ( ( $agent_ref != '' ) ? 'AGENT_REF: ' . $agent_ref . ' - ' : '' ) . $message );
        	}
		}

		$this->import_log[] = date("Y-m-d H:i:s") . ' - ' . ( ( $agent_ref != '' ) ? 'AGENT_REF: ' . $agent_ref . ' - ' : '' ) . $message;
	}

}