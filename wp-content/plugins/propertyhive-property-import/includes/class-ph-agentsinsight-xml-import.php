<?php
/**
 * Class for managing the import process of a agentsinsight* XML file
 *
 * @package WordPress
 */
if ( class_exists( 'PH_Property_Import_Process' ) ) {

class PH_agentsinsight_XML_Import extends PH_Property_Import_Process {

	/**
	 * @var string
	 */
	private $target_file;

	public function __construct( $target_file = '', $instance_id = '' ) 
	{
		$this->target_file = $target_file;
		$this->instance_id = $instance_id;

		if ( isset($_GET['custom_property_import_cron']) )
	    {
	    	$current_user = wp_get_current_user();

	    	$this->add_log("Executed manually by " . ( ( isset($current_user->display_name) ) ? $current_user->display_name : '' ) );
	    }
	}

	public function parse()
	{
		$this->properties = array(); // Reset properties in the event we're importing multiple files

		$xml = simplexml_load_file( $this->target_file );

		if ($xml !== FALSE)
		{
			$this->add_log("Parsing properties");
			
            $properties_imported = 0;
            
			foreach ($xml->property as $property)
			{
                $this->properties[] = $property;

            } // end foreach property
        }
        else
        {
        	// Failed to parse XML
        	$this->add_error( 'Failed to parse XML file. Possibly invalid XML' );

        	return false;
        }

        return true;
	}

	public function import( $import_id = '' )
	{
		global $wpdb;

		$imported_ref_key = ( ( $import_id != '' ) ? '_imported_ref_' . $import_id : '_imported_ref' );

		$options = get_option( 'propertyhive_property_import' );
		if (isset($options[$import_id]))
		{
			$options = $options[$import_id];
		}
		else
		{
			$options = array();
		}

		$this->add_log( 'Starting import' );

		$this->import_start();

		if ( !function_exists('media_handle_upload') ) {
			require_once(ABSPATH . "wp-admin" . '/includes/image.php');
			require_once(ABSPATH . "wp-admin" . '/includes/file.php');
			require_once(ABSPATH . "wp-admin" . '/includes/media.php');
		}

		// Get primary office in the event office mappings weren't set
		$primary_office_id = '';
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

                if (get_post_meta(get_the_ID(), 'primary', TRUE) == '1')
                {
                	$primary_office_id = get_the_ID();
                }
            }
        }
        $office_query->reset_postdata();

        do_action( "propertyhive_pre_import_properties_agentsinsight_xml", $this->properties );
        $this->properties = apply_filters( "propertyhive_agentsinsight_xml_properties_due_import", $this->properties );

		$this->add_log( 'Beginning to loop through ' . count($this->properties) . ' properties' );

		$property_row = 1;
		foreach ( $this->properties as $property )
		{
			$this->add_log( 'Importing property ' . $property_row .' with reference ' . (string)$property->id, (string)$property->id );

			$inserted_updated = false;
			$new_property = false;

			$display_address = array();
			if ( (string)$property->address1 != '' )
			{
				$display_address[] = (string)$property->address1;
			}
			if ( (string)$property->address2 != '' )
			{
				$display_address[] = (string)$property->address2;
			}
			if ( (string)$property->town != '' )
			{
				$display_address[] = (string)$property->town;
			}
			$display_address = implode(", ", $display_address);

			$args = array(
	            'post_type' => 'property',
	            'posts_per_page' => 1,
	            'post_status' => 'any',
	            'meta_query' => array(
	            	array(
		            	'key' => $imported_ref_key,
		            	'value' => (string)$property->id
		            )
	            )
	        );
	        $property_query = new WP_Query($args);
	        
	        if ($property_query->have_posts())
	        {
	        	$this->add_log( 'This property has been imported before. Updating it', (string)$property->id );

	        	// We've imported this property before
	            while ($property_query->have_posts())
	            {
	                $property_query->the_post();

	                $post_id = get_the_ID();

	                $my_post = array(
				    	'ID'          	 => $post_id,
				    	'post_title'     => wp_strip_all_tags( $display_address ),
				    	'post_excerpt'   => (string)$property->specification_summary,
				    	'post_content' 	 => '',
				    	'post_status'    => 'publish',
				  	);

				 	// Update the post into the database
				    $post_id = wp_update_post( $my_post );

				    if ( is_wp_error( $post_id ) ) 
					{
						$this->add_error( 'ERROR: Failed to update post. The error was as follows: ' . $post_id->get_error_message(), (string)$property->id );
					}
					else
					{
						$inserted_updated = 'updated';
					}
	            }
	        }
	        else
	        {
	        	$this->add_log( 'This property hasn\'t been imported before. Inserting it', (string)$property->id );

	        	// We've not imported this property before
				$postdata = array(
					'post_excerpt'   => (string)$property->specification_summary,
					'post_content' 	 => '',
					'post_title'     => wp_strip_all_tags( $display_address ),
					'post_status'    => 'publish',
					'post_type'      => 'property',
					'comment_status' => 'closed',
				);

				$post_id = wp_insert_post( $postdata, true );

				if ( is_wp_error( $post_id ) ) 
				{
					$this->add_error( 'Failed to insert post. The error was as follows: ' . $post_id->get_error_message(), (string)$property->id );
				}
				else
				{
					$inserted_updated = 'inserted';
					$new_property = true;
				}
			}
			$property_query->reset_postdata();

			if ( $inserted_updated !== false )
			{
				// Need to check title and excerpt and see if they've gone in as blank but weren't blank in the feed
				// If they are, then do the encoding
				$inserted_post = get_post( $post_id );
				if ( 
					$inserted_post && 
					$inserted_post->post_title == '' && $inserted_post->post_excerpt == '' && 
					($display_address != '' || (string)$property->specification_summary != '')
				)
				{
					$my_post = array(
				    	'ID'          	 => $post_id,
				    	'post_title'     => htmlentities(mb_convert_encoding(wp_strip_all_tags( $display_address ), 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
				    	'post_excerpt'   => htmlentities(mb_convert_encoding((string)$property->specification_summary, 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
				    	'post_content' 	 => '',
				    	'post_name' 	 => sanitize_title($display_address),
				    	'post_status'    => 'publish',
				  	);

				 	// Update the post into the database
				    wp_update_post( $my_post );
				}

				// Inserted property ok. Continue

				if ( $inserted_updated == 'updated' )
				{
					// Get all meta data so we can compare before and after to see what's changed
					$metadata_before = get_metadata('post', $post_id, '', true);

					// Get all taxonomy/term data
					$taxonomy_terms_before = array();
					$taxonomy_names = get_post_taxonomies( $post_id );
					foreach ( $taxonomy_names as $taxonomy_name )
					{
						$taxonomy_terms_before[$taxonomy_name] = wp_get_post_terms( $post_id, $taxonomy_name, array('fields' => 'ids') );
					}
				}

				$this->add_log( 'Successfully ' . $inserted_updated . ' post. The post ID is ' . $post_id, (string)$property->id );

				$skip_property = false;

				/*$previous_agentsinsight_xml_update_date = get_post_meta( $post_id, '_jupix_xml_update_date_' . $import_id, TRUE);

				if (
					( isset($options['only_updated']) && $options['only_updated'] == 'yes' )
				)
				{
					if (
						$previous_agentsinsight_xml_update_date == (string)$property->dateLastModified . ' ' . (string)$property->timeLastModified
					)
					{
						$skip_property = true;
					}
				}*/

				// Coordinates
				if ( isset($property->lat) && isset($property->lon) && (string)$property->lat != '' && (string)$property->lon != '' && (string)$property->lat != '0' && (string)$property->lon != '0' )
				{
					update_post_meta( $post_id, '_latitude', ( ( isset($property->lat) ) ? (string)$property->lat : '' ) );
					update_post_meta( $post_id, '_longitude', ( ( isset($property->lon) ) ? (string)$property->lon : '' ) );
				}
				else
				{
					// No lat/lng passed. Let's go and get it if none entered
					$lat = get_post_meta( $post_id, '_latitude', TRUE);
					$lng = get_post_meta( $post_id, '_longitude', TRUE);

					if ( $lat == '' || $lng == '' || $lat == '0' || $lng == '0' )
					{
						$api_key = get_option('propertyhive_google_maps_geocoding_api_key', '');
			            if ( $api_key == '' )
			            {
			                $api_key = get_option('propertyhive_google_maps_api_key', '');
			            }
						if ( $api_key != '' )
						{
							if ( ini_get('allow_url_fopen') )
							{
								// No lat lng. Let's get it
								$address_to_geocode = array();
								if ( trim($property->name) != '' ) { $address_to_geocode[] = (string)$property->name; }
								if ( trim($property->address1) != '' ) { $address_to_geocode[] = (string)$property->address1; }
								if ( trim($property->address2) != '' ) { $address_to_geocode[] = (string)$property->address2; }
								if ( trim($property->town) != '' ) { $address_to_geocode[] = (string)$property->town; }
								if ( trim($property->county) != '' ) { $address_to_geocode[] = (string)$property->county; }
								if ( trim($property->postcode) != '' ) { $address_to_geocode[] = (string)$property->postcode; }

								$request_url = "https://maps.googleapis.com/maps/api/geocode/xml?address=" . urlencode( implode( ", ", $address_to_geocode ) ) . "&sensor=false&region=gb"; // the request URL you'll send to google to get back your XML feed
			                    
								if ( $api_key != '' ) { $request_url .= "&key=" . $api_key; }

					            $xml = simplexml_load_file($request_url);

					            if ( $xml !== FALSE )
				            	{
						            $status = $xml->status; // Get the request status as google's api can return several responses
						            
						            if ($status == "OK") 
						            {
						                //request returned completed time to get lat / lang for storage
						                $lat = (string)$xml->result->geometry->location->lat;
						                $lng = (string)$xml->result->geometry->location->lng;
						                
						                if ($lat != '' && $lng != '')
						                {
						                    update_post_meta( $post_id, '_latitude', $lat );
						                    update_post_meta( $post_id, '_longitude', $lng );
						                }
						            }
						            else
							        {
							        	$this->add_error( 'Google Geocoding service returned status ' . $status, (string)$property->id );
							        	sleep(3);
							        }
							    }
							    else
						        {
						        	$this->add_error( 'Failed to parse XML response from Google Geocoding service.', (string)$property->id );
						        }
							}
							else
					        {
					        	$this->add_error( 'Failed to obtain co-ordinates as allow_url_fopen setting is disabled', (string)$property->id );
					        }
					    }
					    else
					    {
					    	$this->add_log( 'Not performing Google Geocoding request as no API key present in settings', (string)$property->id );
					    }
					}
				}

				if ( !$skip_property )
				{
					update_post_meta( $post_id, $imported_ref_key, (string)$property->id );

					// Address
					update_post_meta( $post_id, '_reference_number', (string)$property->id );
					update_post_meta( $post_id, '_address_name_number', ( ( isset($property->name) ) ? (string)$property->name : '' ) );
					update_post_meta( $post_id, '_address_street', ( ( isset($property->address1) ) ? (string)$property->address1 : '' ) );
					update_post_meta( $post_id, '_address_two', ( ( isset($property->address2) ) ? (string)$property->address2 : '' ) );
					update_post_meta( $post_id, '_address_three', ( ( isset($property->town) ) ? (string)$property->town : '' ) );
					update_post_meta( $post_id, '_address_four', ( ( isset($property->county) ) ? (string)$property->county : '' ) );
					update_post_meta( $post_id, '_address_postcode', ( ( isset($property->postcode) ) ? (string)$property->postcode : '' ) );

					$country = 'GB';
					/*if ( isset($property->country) && (string)$property->country != '' && class_exists('PH_Countries') )
					{
						$ph_countries = new PH_Countries();
						foreach ( $ph_countries->countries as $country_code => $country_details )
						{
							if ( strtolower((string)$property->country) == strtolower($country_details['name']) )
							{
								$country = $country_code;
								break;
							}
						}
					}*/
					update_post_meta( $post_id, '_address_country', $country );

	            	// Let's look at address fields to see if we find a match
	            	$address_fields_to_check = apply_filters( 'propertyhive_agentsinsight_xml_address_fields_to_check', array('town', 'county') );
					$location_term_ids = array();

					foreach ( $address_fields_to_check as $address_field )
					{
						if ( isset($property->{$address_field}) && trim((string)$property->{$address_field}) != '' ) 
						{
							$term = term_exists( trim((string)$property->{$address_field}), 'location');
							if ( $term !== 0 && $term !== null && isset($term['term_id']) )
							{
								$location_term_ids[] = (int)$term['term_id'];
							}
						}
					}

					if ( isset($property->submarkets) )
					{
						foreach ( $property->submarkets as $submarkets )
						{
							if ( isset($submarkets->submarket) )
							{
								foreach ( $submarkets->submarket as $submarket )
								{
									$term = term_exists( trim((string)$submarket->name), 'location');
									if ( $term !== 0 && $term !== null && isset($term['term_id']) )
									{
										$location_term_ids[] = (int)$term['term_id'];
									}
								}
							}
						}
					}

					if ( !empty($location_term_ids) )
					{
						wp_set_post_terms( $post_id, $location_term_ids, 'location' );
					}

					// Owner
					add_post_meta( $post_id, '_owner_contact_id', '', true );

					// Record Details
					add_post_meta( $post_id, '_negotiator_id', get_current_user_id(), true );
						
					$office_id = $primary_office_id;
					/*if ( isset($_POST['mapped_office'][(string)$property->branchID]) && $_POST['mapped_office'][(string)$property->branchID] != '' )
					{
						$office_id = $_POST['mapped_office'][(string)$property->branchID];
					}
					elseif ( isset($options['offices']) && is_array($options['offices']) && !empty($options['offices']) )
					{
						foreach ( $options['offices'] as $ph_office_id => $branch_code )
						{
							if ( $branch_code == (string)$property->branchID )
							{
								$office_id = $ph_office_id;
								break;
							}
						}
					}*/
					update_post_meta( $post_id, '_office_id', $office_id );

					$department = 'commercial';
					update_post_meta( $post_id, '_department', $department );

					$prefix = 'commercial_';

					if ( isset($_POST['mapped_' . $prefix . 'property_type']) )
					{
						$mapping = $_POST['mapped_' . $prefix . 'property_type'];
					}
					else
					{
						$mapping = isset($options['mappings'][$prefix . 'property_type']) ? $options['mappings'][$prefix . 'property_type'] : array();
					}

					wp_delete_object_term_relationships( $post_id, $prefix . 'property_type' );
					
					if ( isset($property->types) && isset($property->types->type) )
					{
						$property_types = $property->types->type;
						if ( !is_array($property_types) )
						{
							$property_types = array($property_types);
						}
						
						foreach ( $property->types->type as $type )
						{
							$propertyType = (string)$type;
							if ( !empty($mapping) && isset($mapping[$propertyType]) )
							{
								wp_set_post_terms( $post_id, $mapping[$propertyType], $prefix . 'property_type', TRUE );
							}
							else
							{
								$this->add_log( 'Property received with a type (' . $propertyType . ') that is not mapped', (string)$property->id );

								$options = $this->add_missing_mapping( $mapping, $prefix . 'property_type', $propertyType, $import_id );
							}
						}
					}

					update_post_meta( $post_id, '_for_sale', '' );
            		update_post_meta( $post_id, '_to_rent', '' );

            		if ( isset($property->price) && (string)$property->price != '' )
	                {
	                    update_post_meta( $post_id, '_for_sale', 'yes' );

	                    update_post_meta( $post_id, '_commercial_price_currency', 'GBP' );

	                    $price = preg_replace("/[^0-9.]/", '', (string)$property->price);
	                    update_post_meta( $post_id, '_price_from', $price );
	                    update_post_meta( $post_id, '_price_to', $price );

	                    update_post_meta( $post_id, '_price_units', '' );

	                    update_post_meta( $post_id, '_price_poa', ( strpos(strtolower((string)$property->price), 'application') !== false ? 'yes' : '' ) );

	                    // Tenure
			            /*if ( isset($_POST['mapped_tenure']) )
						{
							$mapping = $_POST['mapped_tenure'];
						}
						else
						{
							$mapping = isset($options['mappings']['tenure']) ? $options['mappings']['tenure'] : array();
						}

			            wp_delete_object_term_relationships( $post_id, 'commercial_tenure' );
						if ( !empty($mapping) && isset($property->propertyTenure) && isset($mapping[(string)$property->propertyTenure]) )
						{
				            wp_set_post_terms( $post_id, $mapping[(string)$property->propertyTenure], 'commercial_tenure' );
			            }

			            // Sale By
			            if ( isset($_POST['mapped_sale_by']) )
						{
							$mapping = $_POST['sale_by'];
						}
						else
						{
							$mapping = isset($options['mappings']['sale_by']) ? $options['mappings']['sale_by'] : array();
						}

			            wp_delete_object_term_relationships( $post_id, 'sale_by' );
						if ( !empty($mapping) && isset($property->saleBy) && isset($mapping[(string)$property->saleBy]) )
						{
				            wp_set_post_terms( $post_id, $mapping[(string)$property->saleBy], 'sale_by' );
			            }*/
	                }

	                if ( isset($property->rent) && (string)$property->rent != '' )
	                {
	                    update_post_meta( $post_id, '_to_rent', 'yes' );

	                    update_post_meta( $post_id, '_commercial_rent_currency', 'GBP' );

	                    $rent = preg_replace("/[^0-9.]/", '', (string)$property->rent);
	                    update_post_meta( $post_id, '_rent_from', $rent );
	                    update_post_meta( $post_id, '_rent_to', $rent );

	                    $rent_frequency = 'pcm';
	                    if ( strpos(strtolower((string)$property->rent), 'annum') !== false )
	                    {
	                    	$rent_frequency = 'pa';
	                    }
	                    elseif ( strpos(strtolower((string)$property->rent), 'ft') !== false )
	                    {
	                    	$rent_frequency = 'psqft';
	                    }

	                    update_post_meta( $post_id, '_rent_units', $rent_frequency);

	                    update_post_meta( $post_id, '_rent_poa', ( strpos(strtolower((string)$property->rent), 'application') !== false ? 'yes' : '' ) );
	                }

	                // Store price in common currency (GBP) used for ordering
		            $ph_countries = new PH_Countries();
		            $ph_countries->update_property_price_actual( $post_id );

		            $size = preg_replace("/[^0-9.]/", '', (string)$property->size_from);
		            update_post_meta( $post_id, '_floor_area_from', $size );

		            $size = preg_replace("/[^0-9.]/", '', (string)$property->size_to);
		            update_post_meta( $post_id, '_floor_area_to', $size );

		            $size = preg_replace("/[^0-9.]/", '', (string)$property->size_from_sqft);
		            update_post_meta( $post_id, '_floor_area_from_sqft', $size );

		            $size = preg_replace("/[^0-9.]/", '', (string)$property->size_to_sqft);
		            update_post_meta( $post_id, '_floor_area_to_sqft', $size );

		            $units = 'sqft';
		            switch ( (string)$property->area_size_unit )
		            {
		            	case "acres": { $units = 'acre'; break; }
		            }
		            update_post_meta( $post_id, '_floor_area_units', $units );

					// Marketing
					update_post_meta( $post_id, '_on_market', 'yes' );
					update_post_meta( $post_id, '_featured', ( isset($property->featured) && (string)$property->featured == 't' ) ? 'yes' : '' );

					// Availability
					$prefix = 'commercial_';
					if ( isset($_POST['mapped_' . $prefix . 'availability']) )
					{
						$mapping = $_POST['mapped_' . $prefix . 'availability'];
					}
					else
					{
						$mapping = isset($options['mappings'][$prefix . 'availability']) ? $options['mappings'][$prefix . 'availability'] : array();
					}

	        		wp_delete_object_term_relationships( $post_id, 'availability' );
					if ( !empty($mapping) && isset($property->status) && isset($mapping[(string)$property->status]) )
					{
		                wp_set_post_terms( $post_id, $mapping[(string)$property->status], 'availability' );
		            }

		            // Features
					/*$features = array();
					for ( $i = 1; $i <= 20; ++$i )
					{
						if ( isset($property->{'propertyFeature' . $i}) && trim((string)$property->{'propertyFeature' . $i}) != '' )
						{
							$features[] = trim((string)$property->{'propertyFeature' . $i});
						}
					}

					update_post_meta( $post_id, '_features', count( $features ) );
	        		
	        		$i = 0;
			        foreach ( $features as $feature )
			        {
			            update_post_meta( $post_id, '_feature_' . $i, $feature );
			            ++$i;
			        }*/

			        // Rooms / Descriptions
			        // For now put the whole description in one room / description
			        $description_i = 0;

			        if ( isset($property->specification_description) && (string)$property->specification_description != '' )
			        {
			        	update_post_meta( $post_id, '_description_name_' . $description_i, '' );
		            	update_post_meta( $post_id, '_description_' . $description_i, (string)$property->specification_description );

		            	++$description_i;
			        }
			        if ( isset($property->location) && (string)$property->location != '' )
			        {
			        	update_post_meta( $post_id, '_description_name_' . $description_i, 'Location' );
		            	update_post_meta( $post_id, '_description_' . $description_i, (string)$property->location );

		            	++$description_i;
			        }
			        if ( isset($property->marketing_text_1) && (string)$property->marketing_text_1 != '' )
			        {
			        	update_post_meta( $post_id, '_description_name_' . $description_i, (string)$property->marketing_title_1 );
		            	update_post_meta( $post_id, '_description_' . $description_i, (string)$property->marketing_text_1 );

		            	++$description_i;
			        }
			        if ( isset($property->marketing_text_2) && (string)$property->marketing_text_2 != '' )
			        {
			        	update_post_meta( $post_id, '_description_name_' . $description_i, (string)$property->marketing_title_2 );
		            	update_post_meta( $post_id, '_description_' . $description_i, (string)$property->marketing_text_2 );

		            	++$description_i;
			        }
			        if ( isset($property->marketing_text_3) && (string)$property->marketing_text_3 != '' )
			        {
			        	update_post_meta( $post_id, '_description_name_' . $description_i, (string)$property->marketing_title_3 );
		            	update_post_meta( $post_id, '_description_' . $description_i, (string)$property->marketing_text_3 );

		            	++$description_i;
			        }
			        if ( isset($property->marketing_text_4) && (string)$property->marketing_text_4 != '' )
			        {
			        	update_post_meta( $post_id, '_description_name_' . $description_i, (string)$property->marketing_title_4 );
		            	update_post_meta( $post_id, '_description_' . $description_i, (string)$property->marketing_text_4 );

		            	++$description_i;
			        }
			        if ( isset($property->marketing_text_5) && (string)$property->marketing_text_5 != '' )
			        {
			        	update_post_meta( $post_id, '_description_name_' . $description_i, (string)$property->marketing_title_5 );
		            	update_post_meta( $post_id, '_description_' . $description_i, (string)$property->marketing_text_5 );

		            	++$description_i;
			        }
			        if ( isset($property->marketing_text_transport) && (string)$property->marketing_text_transport != '' )
			        {
			        	update_post_meta( $post_id, '_description_name_' . $description_i, (string)$property->marketing_title_transport );
		            	update_post_meta( $post_id, '_description_' . $description_i, (string)$property->marketing_text_transport );

		            	++$description_i;
			        }
					update_post_meta( $post_id, '_descriptions', $description_i );
					
		            // Media - Images
		            if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
	    			{
	    				$media_urls = array();
	    				if (isset($property->images) && !empty($property->images))
		                {
		                    foreach ($property->images as $images)
		                    {
		                        if (!empty($images->image))
		                        {
		                            foreach ($images->image as $image)
		                            {
										if ( 
											substr( strtolower((string)$image), 0, 2 ) == '//' || 
											substr( strtolower((string)$image), 0, 4 ) == 'http'
										)
										{
											// This is a URL
											$url = str_replace("http://", "https://", (string)$image);

											$media_urls[] = array('url' => $url);
										}
									}
								}
							}
						}
						update_post_meta( $post_id, '_photo_urls', $media_urls );

						$this->add_log( 'Imported ' . count($media_urls) . ' photo URLs', (string)$property->id );
	    			}
	    			else
	    			{
						$media_ids = array();
						$new = 0;
						$existing = 0;
						$deleted = 0;
						$previous_media_ids = get_post_meta( $post_id, '_photos', TRUE );
						if (isset($property->images) && !empty($property->images))
		                {
		                    foreach ($property->images as $images)
		                    {
		                        if (!empty($images->image))
		                        {
		                            foreach ($images->image as $image)
		                            {
										if ( 
											substr( strtolower((string)$image), 0, 2 ) == '//' || 
											substr( strtolower((string)$image), 0, 4 ) == 'http'
										)
										{
											// This is a URL
											$url = (string)$image;
											$description = '';

											$filename = basename( $url );

											// Check, based on the URL, whether we have previously imported this media
											$imported_previously = false;
											$imported_previously_id = '';
											if ( is_array($previous_media_ids) && !empty($previous_media_ids) )
											{
												foreach ( $previous_media_ids as $previous_media_id )
												{
													if ( 
														get_post_meta( $previous_media_id, '_imported_url', TRUE ) == $url
													)
													{
														$imported_previously = true;
														$imported_previously_id = $previous_media_id;
														break;
													}
												}
											}
											
											if ($imported_previously)
											{
												$media_ids[] = $imported_previously_id;

												++$existing;
											}
											else
											{
											    $tmp = download_url( $url );
											    $file_array = array(
											        'name' => basename( $url ),
											        'tmp_name' => $tmp
											    );

											    // Check for download errors
											    if ( is_wp_error( $tmp ) ) 
											    {
											        @unlink( $file_array[ 'tmp_name' ] );

											        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), (string)$property->id );
											    }
											    else
											    {
												    $id = media_handle_sideload( $file_array, $post_id, $description, array('post_title' => $filename) );

												    // Check for handle sideload errors.
												    if ( is_wp_error( $id ) ) 
												    {
												        @unlink( $file_array['tmp_name'] );
												        
												        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), (string)$property->id );
												    }
												    else
												    {
												    	$media_ids[] = $id;

												    	update_post_meta( $id, '_imported_url', $url);

												    	++$new;
												    }
												}
											}
										}
									}
								}
							}
						}
						update_post_meta( $post_id, '_photos', $media_ids );

						// Loop through $previous_media_ids, check each one exists in $media_ids, and if it doesn't then delete
						if ( is_array($previous_media_ids) && !empty($previous_media_ids) )
						{
							foreach ( $previous_media_ids as $previous_media_id )
							{
								if ( !in_array($previous_media_id, $media_ids) )
								{
									if ( wp_delete_attachment( $previous_media_id, TRUE ) !== FALSE )
									{
										++$deleted;
									}
								}
							}
						}

						$this->add_log( 'Imported ' . count($media_ids) . ' photos (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->id );
					}

					// Media - Floorplans
					if ( get_option('propertyhive_floorplans_stored_as', '') == 'urls' )
	    			{
	    				$media_urls = array();
	    				if (isset($property->files) && !empty($property->files))
		                {
		                    foreach ($property->files as $files)
		                    {
		                        if (!empty($files->file))
		                        {
		                            foreach ($files->file as $file)
		                            {
										if ( 
											$file->type == '15' &&
											(
												substr( strtolower((string)$file->url), 0, 2 ) == '//' || 
												substr( strtolower((string)$file->url), 0, 4 ) == 'http'
											)
										)
										{
											// This is a URL
											$url = str_replace("http://", "https://", (string)$file->url);

											$media_urls[] = array('url' => $url);
										}
									}
								}
							}
						}
						update_post_meta( $post_id, '_floorplan_urls', $media_urls );

						$this->add_log( 'Imported ' . count($media_urls) . ' floorplan URLs', (string)$property->id );
	    			}
	    			else
	    			{
						$media_ids = array();
						$new = 0;
						$existing = 0;
						$deleted = 0;
						$previous_media_ids = get_post_meta( $post_id, '_floorplans', TRUE );
						if (isset($property->files) && !empty($property->files))
		                {
		                    foreach ($property->files as $files)
		                    {
		                        if (!empty($files->file))
		                        {
		                            foreach ($files->file as $file)
		                            {
										if ( 
											$file->type == '15' &&
											(
												substr( strtolower((string)$file->url), 0, 2 ) == '//' || 
												substr( strtolower((string)$file->url), 0, 4 ) == 'http'
											)
										)
										{
											// This is a URL
											$url = (string)$file->url;
											$description = (string)$file->description;
										    
											$filename = basename( $url );

											// Check, based on the URL, whether we have previously imported this media
											$imported_previously = false;
											$imported_previously_id = '';
											if ( is_array($previous_media_ids) && !empty($previous_media_ids) )
											{
												foreach ( $previous_media_ids as $previous_media_id )
												{
													if ( 
														get_post_meta( $previous_media_id, '_imported_url', TRUE ) == $url
													)
													{
														$imported_previously = true;
														$imported_previously_id = $previous_media_id;
														break;
													}
												}
											}
											
											if ($imported_previously)
											{
												$media_ids[] = $imported_previously_id;

												++$existing;
											}
											else
											{
											    $tmp = download_url( $url );
											    $file_array = array(
											        'name' => $filename,
											        'tmp_name' => $tmp
											    );

											    // Check for download errors
											    if ( is_wp_error( $tmp ) ) 
											    {
											        @unlink( $file_array[ 'tmp_name' ] );

											        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), (string)$property->id );
											    }
											    else
											    {
												    $id = media_handle_sideload( $file_array, $post_id, $description, array('post_title' => $filename) );

												    // Check for handle sideload errors.
												    if ( is_wp_error( $id ) ) 
												    {
												        @unlink( $file_array['tmp_name'] );
												        
												        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), (string)$property->id );
												    }
												    else
												    {
												    	$media_ids[] = $id;

												    	update_post_meta( $id, '_imported_url', $url);

												    	++$new;
												    }
												}
											}
										}
									}
								}
							}
						}
						update_post_meta( $post_id, '_floorplans', $media_ids );

						// Loop through $previous_media_ids, check each one exists in $media_ids, and if it doesn't then delete
						if ( is_array($previous_media_ids) && !empty($previous_media_ids) )
						{
							foreach ( $previous_media_ids as $previous_media_id )
							{
								if ( !in_array($previous_media_id, $media_ids) )
								{
									if ( wp_delete_attachment( $previous_media_id, TRUE ) !== FALSE )
									{
										++$deleted;
									}
								}
							}
						}

						$this->add_log( 'Imported ' . count($media_ids) . ' floorplans (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->id );
					}

					// Media - Brochures
					if ( get_option('propertyhive_brochures_stored_as', '') == 'urls' )
	    			{
	    				$media_urls = array();
	    				if (isset($property->files) && !empty($property->files))
		                {
		                    foreach ($property->files as $files)
		                    {
		                        if (!empty($files->file))
		                        {
		                            foreach ($files->file as $file)
		                            {
										if ( 
											$file->type == '11' &&
											(
												substr( strtolower((string)$file->url), 0, 2 ) == '//' || 
												substr( strtolower((string)$file->url), 0, 4 ) == 'http'
											)
										)
										{
											// This is a URL
											$url = str_replace("http://", "https://", (string)$file->url);

											$media_urls[] = array('url' => $url);
										}
									}
								}
							}
						}
						if ( isset($property->particulars_url) && (string)$property->particulars_url != '' )
						{
							$url = str_replace("http://", "https://", (string)$property->particulars_url);
							$media_urls[] = array('url' => $url);
						}
						update_post_meta( $post_id, '_brochure_urls', $media_urls );

						$this->add_log( 'Imported ' . count($media_urls) . ' brochure URLs', (string)$property->id );
	    			}
	    			else
	    			{
						$media_ids = array();
						$new = 0;
						$existing = 0;
						$deleted = 0;
						$previous_media_ids = get_post_meta( $post_id, '_brochures', TRUE );
						if (isset($property->files) && !empty($property->files))
		                {
		                    foreach ($property->files as $files)
		                    {
		                        if (!empty($files->file))
		                        {
		                            foreach ($files->file as $file)
		                            {
										if ( 
											$file->type == '11' &&
											(
												substr( strtolower((string)$file->url), 0, 2 ) == '//' || 
												substr( strtolower((string)$file->url), 0, 4 ) == 'http'
											)
										)
										{
											// This is a URL
											$url = (string)$file->url;
											$description = (string)$file->description;
										    
											$filename = basename( $url );

											// Check, based on the URL, whether we have previously imported this media
											$imported_previously = false;
											$imported_previously_id = '';
											if ( is_array($previous_media_ids) && !empty($previous_media_ids) )
											{
												foreach ( $previous_media_ids as $previous_media_id )
												{
													if ( 
														get_post_meta( $previous_media_id, '_imported_url', TRUE ) == $url
													)
													{
														$imported_previously = true;
														$imported_previously_id = $previous_media_id;
														break;
													}
												}
											}
											
											if ($imported_previously)
											{
												$media_ids[] = $imported_previously_id;

												++$existing;
											}
											else
											{
											    $tmp = download_url( $url );
											    $file_array = array(
											        'name' => $filename,
											        'tmp_name' => $tmp
											    );

											    // Check for download errors
											    if ( is_wp_error( $tmp ) ) 
											    {
											        @unlink( $file_array[ 'tmp_name' ] );

											        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), (string)$property->id );
											    }
											    else
											    {
												    $id = media_handle_sideload( $file_array, $post_id, $description, array('post_title' => $filename) );

												    // Check for handle sideload errors.
												    if ( is_wp_error( $id ) ) 
												    {
												        @unlink( $file_array['tmp_name'] );
												        
												        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), (string)$property->id );
												    }
												    else
												    {
												    	$media_ids[] = $id;

												    	update_post_meta( $id, '_imported_url', $url);

												    	++$new;
												    }
												}
											}
										}
									}
								}
							}
						}
						if (isset($property->particulars_url) && (string)$property->particulars_url != '')
		                {
							if ( 
								substr( strtolower((string)$property->particulars_url), 0, 2 ) == '//' || 
								substr( strtolower((string)$property->particulars_url), 0, 4 ) == 'http'
							)
							{
								// This is a URL
								$url = (string)$property->particulars_url;
								$description = '';
							    
								$filename = basename( $url );

								// Check, based on the URL, whether we have previously imported this media
								$imported_previously = false;
								$imported_previously_id = '';
								if ( is_array($previous_media_ids) && !empty($previous_media_ids) )
								{
									foreach ( $previous_media_ids as $previous_media_id )
									{
										if ( 
											get_post_meta( $previous_media_id, '_imported_url', TRUE ) == $url
										)
										{
											$imported_previously = true;
											$imported_previously_id = $previous_media_id;
											break;
										}
									}
								}
								
								if ($imported_previously)
								{
									$media_ids[] = $imported_previously_id;

									++$existing;
								}
								else
								{
								    $tmp = download_url( $url );
								    $file_array = array(
								        'name' => $filename,
								        'tmp_name' => $tmp
								    );

								    // Check for download errors
								    if ( is_wp_error( $tmp ) ) 
								    {
								        @unlink( $file_array[ 'tmp_name' ] );

								        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), (string)$property->id );
								    }
								    else
								    {
									    $id = media_handle_sideload( $file_array, $post_id, $description, array('post_title' => $filename) );

									    // Check for handle sideload errors.
									    if ( is_wp_error( $id ) ) 
									    {
									        @unlink( $file_array['tmp_name'] );
									        
									        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), (string)$property->id );
									    }
									    else
									    {
									    	$media_ids[] = $id;

									    	update_post_meta( $id, '_imported_url', $url);

									    	++$new;
									    }
									}
								}
							}
						}
						update_post_meta( $post_id, '_brochures', $media_ids );

						// Loop through $previous_media_ids, check each one exists in $media_ids, and if it doesn't then delete
						if ( is_array($previous_media_ids) && !empty($previous_media_ids) )
						{
							foreach ( $previous_media_ids as $previous_media_id )
							{
								if ( !in_array($previous_media_id, $media_ids) )
								{
									if ( wp_delete_attachment( $previous_media_id, TRUE ) !== FALSE )
									{
										++$deleted;
									}
								}
							}
						}

						$this->add_log( 'Imported ' . count($media_ids) . ' brochures (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->id );
					}

					// Media - EPCs
					if ( get_option('propertyhive_epcs_stored_as', '') == 'urls' )
	    			{
	    				$media_urls = array();

	    				if (isset($property->epcs) && !empty($property->epcs))
		                {
		                    foreach ($property->epcs as $epcs)
		                    {
		                        if (!empty($epcs->epc))
		                        {
		                            foreach ($epcs->epc as $epc)
		                            {
										if ( 
											substr( strtolower((string)$epc->url), 0, 2 ) == '//' || 
											substr( strtolower((string)$epc->url), 0, 4 ) == 'http'
										)
										{
											// This is a URL
											$url = str_replace("http://", "https://", (string)$epc->url);

											$media_urls[] = array('url' => $url);
										}
									}
								}
							}
						}

						update_post_meta( $post_id, '_epc_urls', $media_urls );

						$this->add_log( 'Imported ' . count($media_urls) . ' EPC URLs', (string)$property->id );
	    			}
	    			else
	    			{
						$media_ids = array();
						$new = 0;
						$existing = 0;
						$deleted = 0;
						$previous_media_ids = get_post_meta( $post_id, '_epcs', TRUE );
						if (isset($property->epcs) && !empty($property->epcs))
		                {
		                    foreach ($property->epcs as $epcs)
		                    {
		                        if (!empty($epcs->epc))
		                        {
		                            foreach ($epcs->epc as $epc)
		                            {
										if ( 
											substr( strtolower((string)$epc->url), 0, 2 ) == '//' || 
											substr( strtolower((string)$epc->url), 0, 4 ) == 'http'
										)
										{
											// This is a URL
											$url = (string)$epc->url;
											$description = (string)$epc->description;
										    
											$filename = basename( $url );

											// Check, based on the URL, whether we have previously imported this media
											$imported_previously = false;
											$imported_previously_id = '';
											if ( is_array($previous_media_ids) && !empty($previous_media_ids) )
											{
												foreach ( $previous_media_ids as $previous_media_id )
												{
													if ( 
														get_post_meta( $previous_media_id, '_imported_url', TRUE ) == $url
													)
													{
														$imported_previously = true;
														$imported_previously_id = $previous_media_id;
														break;
													}
												}
											}
											
											if ($imported_previously)
											{
												$media_ids[] = $imported_previously_id;

												++$existing;
											}
											else
											{
											    $tmp = download_url( $url );
											    $file_array = array(
											        'name' => $filename,
											        'tmp_name' => $tmp
											    );

											    // Check for download errors
											    if ( is_wp_error( $tmp ) ) 
											    {
											        @unlink( $file_array[ 'tmp_name' ] );

											        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), (string)$property->id );
											    }
											    else
											    {
												    $id = media_handle_sideload( $file_array, $post_id, $description, array('post_title' => $filename) );

												    // Check for handle sideload errors.
												    if ( is_wp_error( $id ) ) 
												    {
												        @unlink( $file_array['tmp_name'] );
												        
												        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), (string)$property->id );
												    }
												    else
												    {
												    	$media_ids[] = $id;

												    	update_post_meta( $id, '_imported_url', $url);

												    	++$new;
												    }
												}
											}
										}
									}
								}
							}
						}
						update_post_meta( $post_id, '_epcs', $media_ids );

						// Loop through $previous_media_ids, check each one exists in $media_ids, and if it doesn't then delete
						if ( is_array($previous_media_ids) && !empty($previous_media_ids) )
						{
							foreach ( $previous_media_ids as $previous_media_id )
							{
								if ( !in_array($previous_media_id, $media_ids) )
								{
									if ( wp_delete_attachment( $previous_media_id, TRUE ) !== FALSE )
									{
										++$deleted;
									}
								}
							}
						}

						$this->add_log( 'Imported ' . count($media_ids) . ' EPCs (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->id );
					}

					// Media - Virtual Tours
					$virtual_tours = array();
					if (isset($property->videos) && !empty($property->videos))
	                {
	                    foreach ($property->videos as $videos)
	                    {
	                        if (!empty($videos->url))
	                        {
	                            foreach ($videos->url as $url)
	                            {
	                            	$virtual_tours[] = $url;
	                            }
	                        }
	                    }
	                }

	                update_post_meta( $post_id, '_virtual_tours', count($virtual_tours) );
	                foreach ($virtual_tours as $i => $virtual_tour)
	                {
	                	update_post_meta( $post_id, '_virtual_tour_' . $i, (string)$virtual_tour );
	                }

					$this->add_log( 'Imported ' . count($virtual_tours) . ' virtual tours', (string)$property->id );
				}
				else
				{
					$this->add_log( 'Skipping property as not been updated', (string)$property->id );
				}
				
				update_post_meta( $post_id, '_agentsinsight_xml_update_date_' . $import_id, (string)$property->last_updated );

				do_action( "propertyhive_property_imported_agentsinsight_xml", $post_id, $property );

				$post = get_post( $post_id );
				do_action( "save_post_property", $post_id, $post, false );
				do_action( "save_post", $post_id, $post, false );

				if ( $inserted_updated == 'updated' )
				{
					// Compare meta/taxonomy data before and after.

					$metadata_after = get_metadata('post', $post_id, '', true);

					foreach ( $metadata_after as $key => $value)
					{
						if ( in_array($key, array('_photos', '_photo_urls', '_floorplans', '_floorplan_urls', '_brochures', '_brochure_urls', '_epcs', '_epc_urls', '_virtual_tours')) )
						{
							continue;
						}

						if ( !isset($metadata_before[$key]) )
						{
							$this->add_log( 'New meta data for ' . trim($key, '_') . ': ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), (string)$property->id );
						}
						elseif ( $metadata_before[$key] != $metadata_after[$key] )
						{
							$this->add_log( 'Updated ' . trim($key, '_') . '. Before: ' . ( ( is_array($metadata_before[$key]) ) ? implode(", ", $metadata_before[$key]) : $metadata_before[$key] ) . ', After: ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), (string)$property->id );
						}
					}

					$taxonomy_terms_after = array();
					$taxonomy_names = get_post_taxonomies( $post_id );
					foreach ( $taxonomy_names as $taxonomy_name )
					{
						$taxonomy_terms_after[$taxonomy_name] = wp_get_post_terms( $post_id, $taxonomy_name, array('fields' => 'ids') );
					}

					foreach ( $taxonomy_terms_after as $taxonomy_name => $ids)
					{
						if ( !isset($taxonomy_terms_before[$taxonomy_name]) )
						{
							$this->add_log( 'New taxonomy data for ' . $taxonomy_name . ': ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), (string)$property->id );
						}
						elseif ( $taxonomy_terms_before[$taxonomy_name] != $taxonomy_terms_after[$taxonomy_name] )
						{
							$this->add_log( 'Updated ' . $taxonomy_name . '. Before: ' . ( ( is_array($taxonomy_terms_before[$taxonomy_name]) ) ? implode(", ", $taxonomy_terms_before[$taxonomy_name]) : $taxonomy_terms_before[$taxonomy_name] ) . ', After: ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), (string)$property->id );
						}
					}
				}
			}

			if ( 
				isset($options['chunk_qty']) && $options['chunk_qty'] != '' && 
				isset($options['chunk_delay']) && $options['chunk_delay'] != '' &&
				($property_row % $options['chunk_qty'] == 0)
			)
			{
				$this->add_log( 'Pausing for ' . $options['chunk_delay'] . ' seconds' );
				sleep($options['chunk_delay']);
			}
			++$property_row;

		} // end foreach property

		do_action( "propertyhive_post_import_properties_agentsinsight_xml" );

		$this->import_end();

		$this->add_log( 'Finished import' );
	}

	public function remove_old_properties( $import_id = '', $do_remove = true )
	{
		global $wpdb, $post;

		$options = get_option( 'propertyhive_property_import' );
		if (isset($options[$import_id]))
		{
			$options = $options[$import_id];
		}
		else
		{
			$options = array();
		}

		$imported_ref_key = ( ( $import_id != '' ) ? '_imported_ref_' . $import_id : '_imported_ref' );

		// Get all properties that:
		// a) Don't have an _imported_ref matching the properties in $this->properties;
		// b) Haven't been manually added (.i.e. that don't have an _imported_ref at all)

		$import_refs = array();
		foreach ($this->properties as $property)
		{
			$import_refs[] = (string)$property->id;
		}

		$args = array(
			'post_type' => 'property',
			'nopaging' => true
		);

		$meta_query = array(
			'relation' => 'AND',
			array(
				'key'     => $imported_ref_key,
				'value'   => $import_refs,
				'compare' => 'NOT IN',
			),
			array(
				'key'     => '_on_market',
				'value'   => 'yes',
			),
		);

		$args['meta_query'] = $meta_query;

		$property_query = new WP_Query( $args );
		if ( $property_query->have_posts() )
		{
			while ( $property_query->have_posts() )
			{
				$property_query->the_post();

				if ($do_remove)
				{
					update_post_meta( $post->ID, '_on_market', '' );

					$this->add_log( 'Property ' . $post->ID . ' marked as not on market', get_post_meta($post->ID, $imported_ref_key, TRUE) );

					if ( isset($options['remove_action']) && $options['remove_action'] != '' )
					{
						if ( $options['remove_action'] == 'remove_all_media' || $options['remove_action'] == 'remove_all_media_except_first_image' )
						{
							// Remove all EPCs
							$this->delete_media( $post->ID, '_epcs' );

							// Remove all Brochures
							$this->delete_media( $post->ID, '_brochures' );

							// Remove all Floorplans
							$this->delete_media( $post->ID, '_floorplans' );

							// Remove all Images (except maybe the first)
							$this->delete_media( $post->ID, '_photos', ( ( $options['remove_action'] == 'remove_all_media_except_first_image' ) ? TRUE : FALSE ) );

							$this->add_log( 'Deleted property media', get_post_meta($post->ID, $imported_ref_key, TRUE) );
						}
						elseif ( $options['remove_action'] == 'draft_property' )
						{
							$my_post = array(
						    	'ID'          	 => $post->ID,
						    	'post_status'    => 'draft',
						  	);

						 	// Update the post into the database
						    $post_id = wp_update_post( $my_post );

						    if ( is_wp_error( $post_id ) ) 
							{
								$this->add_error( 'Failed to set post as draft. The error was as follows: ' . $post_id->get_error_message(), get_post_meta($post->ID, $imported_ref_key, TRUE) );
							}
							else
							{
								$this->add_log( 'Drafted property', get_post_meta($post->ID, $imported_ref_key, TRUE) );
							}
						}
						elseif ( $options['remove_action'] == 'remove_property' )
						{
							wp_delete_post( $post->ID, true );
							$this->add_log( 'Deleted property', get_post_meta($post->ID, $imported_ref_key, TRUE) );
						}
					}
				}

				do_action( "propertyhive_property_removed_agentsinsight_xml", $post->ID );
			}
		}
		wp_reset_postdata();

		unset($import_refs);
	}

	public function get_mappings( $import_id = '' )
	{
		if ( !empty($this->mappings) )
		{
			return $this->mappings;
		}

		// Build mappings
		$mapping_values = $this->get_xml_mapping_values('commercial_availability');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['commercial_availability'][$mapping_value] = '';
			}
		}

		$mapping_values = $this->get_xml_mapping_values('commercial_property_type');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['commercial_property_type'][$mapping_value] = '';
			}
		}

		$mapping_values = $this->get_xml_mapping_values('office');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['office'][$mapping_value] = '';
			}
		}
		
		return $this->mappings;
	}

	public function get_mapping_values($custom_field)
	{
		return $this->get_xml_mapping_values($custom_field);
	}

	public function get_xml_mapping_values($custom_field) 
	{
        if ($custom_field == 'commercial_availability')
        {
            return array(
                'Coming Soon' => 'Coming Soon',
                'Available' => 'Available',
                'Under Offer' => 'Under Offer',
                'Let' => 'Let',
                'Sold' => 'Sold',
            );
        }
        if ($custom_field == 'commercial_property_type')
        {
        	return array(
                'Office' => 'Office',
                'Serviced Office' => 'Serviced Office',
                'Industrial' => 'Industrial',
                'Retail' => 'Retail',
                'Residential' => 'Residential',
                'Leisure' => 'Leisure',
                'D1 (Non Residential Institutions)' => 'D1 (Non Residential Institutions)',
                'D2 (Assembly and Leisure)' => 'D2 (Assembly and Leisure)',
                'Land' => 'Land',
                'Development' => 'Development',
                'Investment' => 'Investment',
                'Trade Counter' => 'Trade Counter',
                'Storage' => 'Storage',
                'Other' => 'Other',
            );
        }
    }

}

}