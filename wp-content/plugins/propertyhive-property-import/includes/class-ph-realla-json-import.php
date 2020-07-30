<?php
/**
 * Class for managing the import process of a Realla JSON file
 *
 * @package WordPress
 */
if ( class_exists( 'PH_Property_Import_Process' ) ) {

class PH_Realla_JSON_Import extends PH_Property_Import_Process {

	public function __construct( $instance_id = '' ) 
	{
		$this->instance_id = $instance_id;

		if ( isset($_GET['custom_property_import_cron']) )
	    {
	    	$current_user = wp_get_current_user();

	    	$this->add_log("Executed manually by " . ( ( isset($current_user->display_name) ) ? $current_user->display_name : '' ) );
	    }
	}

	public function parse( $options )
	{
		$this->properties = array(); // Reset properties in the event we're importing multiple files

		// Sales Properties
		$this->add_log("Obtaining properties");

		$contents = '';

		$response = wp_remote_post( 'https://realla.co/api/v1/listings/search?api_key=' . $options['api_key'], array(
			'method' => 'POST',
			'body' => '{
  "size": 999
}',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(
				'Content-Type' => 'application/json',
				'Accept' => 'application/json',
				'Authorization' => 'Basic ' . base64_encode( 'api:' . $options['api_key'] )
			),
			'cookies' => array()
		) );

		if ( is_wp_error( $response ) )
		{
			$this->add_error( 'Response: ' . $response->get_error_message() );

			return false;
		}

		$json = json_decode( $response['body'], TRUE );

		if ($json !== FALSE)
		{
			$this->add_log("Parsing properties");
			
            $properties_imported = 0;
            
            if ( isset($json['hits']['hits']) )
            {
				foreach ($json['hits']['hits'] as $property)
				{
					$property = $property['_source'];

					if ( isset($property['sale_stage']) && ( in_array($property['sale_stage'], array('withdrawn', 'off_market', 'sold', 'let', 'sold_or_let')) ) )
					{

					}
					else
					{
					    $this->properties[] = $property;
					}
	            } // end foreach property
	        }
        }
        else
        {
        	// Failed to parse XML
        	$this->add_error( 'Failed to parse JSON.' );

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

        do_action( "propertyhive_pre_import_properties_realla_json", $this->properties );
        $this->properties = apply_filters( "propertyhive_realla_json_properties_due_import", $this->properties );

		$this->add_log( 'Beginning to loop through ' . count($this->properties) . ' properties' );

		$property_row = 1;
		foreach ( $this->properties as $property )
		{
			$this->add_log( 'Importing property ' . $property_row .' with reference ' . $property['id'], $property['id'] );

			$inserted_updated = false;

			$args = array(
	            'post_type' => 'property',
	            'posts_per_page' => 1,
	            'post_status' => 'any',
	            'meta_query' => array(
	            	array(
		            	'key' => $imported_ref_key,
		            	'value' => $property['id']
		            )
	            )
	        );
	        $property_query = new WP_Query($args);
	        
	        if ($property_query->have_posts())
	        {
	        	$this->add_log( 'This property has been imported before. Updating it', $property['id'] );

	        	// We've imported this property before
	            while ($property_query->have_posts())
	            {
	                $property_query->the_post();

	                $post_id = get_the_ID();

	                $my_post = array(
				    	'ID'          	 => $post_id,
				    	'post_title'     => wp_strip_all_tags( $property['display_address'] ),
				    	'post_excerpt'   => ( ( isset($property['building_description']) && $property['building_description'] != '' ) ? $property['building_description'] : '' ),
				    	'post_content' 	 => '',
				    	'post_status'    => 'publish',
				  	);

				 	// Update the post into the database
				    $post_id = wp_update_post( $my_post );

				    if ( is_wp_error( $post_id ) ) 
					{
						$this->add_error( 'ERROR: Failed to update post. The error was as follows: ' . $post_id->get_error_message(), $property['id'] );
					}
					else
					{
						$inserted_updated = 'updated';
					}
	            }
	        }
	        else
	        {
	        	$this->add_log( 'This property hasn\'t been imported before. Inserting it', $property['id'] );

	        	// We've not imported this property before
				$postdata = array(
					'post_excerpt'   => ( ( isset($property['building_description']) && $property['building_description'] != '' ) ? $property['building_description'] : '' ),
					'post_content' 	 => '',
					'post_title'     => wp_strip_all_tags( $property['display_address'] ),
					'post_status'    => 'publish',
					'post_type'      => 'property',
					'comment_status' => 'closed',
				);

				$post_id = wp_insert_post( $postdata, true );

				if ( is_wp_error( $post_id ) ) 
				{
					$this->add_error( 'Failed to insert post. The error was as follows: ' . $post_id->get_error_message(), $property['id'] );
				}
				else
				{
					$inserted_updated = 'inserted';
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
					($property['display_address'] != '' || $property['building_description'] != '')
				)
				{
					$my_post = array(
				    	'ID'          	 => $post_id,
				    	'post_title'     => htmlentities(mb_convert_encoding(wp_strip_all_tags( $property['display_address'] ), 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
				    	'post_excerpt'   => htmlentities(mb_convert_encoding($property['building_description'], 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
				    	'post_content' 	 => '',
				    	'post_name' 	 => sanitize_title($property['display_address']),
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

				$this->add_log( 'Successfully ' . $inserted_updated . ' post. The post ID is ' . $post_id, $property['id'] );

				update_post_meta( $post_id, $imported_ref_key, $property['id'] );

				// Address
				update_post_meta( $post_id, '_reference_number', ( ( isset($property['contact_reference']) ) ? $property['contact_reference'] : '' ) );
				update_post_meta( $post_id, '_address_name_number', ( 
					trim(
						( isset($property['locating']['building']) ? $property['locating']['building'] : '' ) . ' ' .
						( isset($property['locating']['street_number']) ? $property['locating']['street_number'] : '' )
					)
				));
				update_post_meta( $post_id, '_address_street', ( ( isset($property['locating']['route']) ) ? $property['locating']['route'] : '' ) );
				update_post_meta( $post_id, '_address_two', '' );
				update_post_meta( $post_id, '_address_three', ( ( isset($property['locating']['postal_town']) ) ? $property['locating']['postal_town'] : '' ) );
				update_post_meta( $post_id, '_address_four', ( ( isset($property['locating']['locality']) ) ? $property['locating']['locality'] : '' ) );
				update_post_meta( $post_id, '_address_postcode', ( ( isset($property['locating']['postal_code']) ) ? $property['locating']['postal_code'] : '' ) );

				$country = get_option( 'propertyhive_default_country', 'GB' );
				update_post_meta( $post_id, '_address_country', $country );

				// Check main address fields and see if this location exists as a taxonomy to try and assign properties to location
				$address_fields_to_check = apply_filters( 'propertyhive_realla_json_address_fields_to_check', array('postal_town', 'locality') );
				$location_term_ids = array();

				foreach ( $address_fields_to_check as $address_field )
				{
					if ( isset($property['locating'][$address_field]) && trim($property['locating'][$address_field]) != '' ) 
					{
						$term = term_exists( trim($property['locating'][$address_field]), 'location');
						if ( $term !== 0 && $term !== null && isset($term['term_id']) )
						{
							$location_term_ids[] = (int)$term['term_id'];
						}
					}
				}

				if ( !empty($location_term_ids) )
				{
					wp_set_post_terms( $post_id, $location_term_ids, 'location' );
				}

				// Coordinates
				if ( isset($property['geo_location']['lat']) && isset($property['geo_location']['lon']) && $property['geo_location']['lat'] != '' && $property['geo_location']['lon'] != '' && $property['geo_location']['lat'] != '0' && $property['geo_location']['lon'] != '0' )
				{
					update_post_meta( $post_id, '_latitude', $property['geo_location']['lat'] );
					update_post_meta( $post_id, '_longitude', $property['geo_location']['lon'] );
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
								if ( isset($property['locating']['street_number']) && trim($property['locating']['street_number']) != '' ) { $address_to_geocode[] = $property['locating']['street_number']; }
								if ( isset($property['locating']['route']) && trim($property['locating']['route']) != '' ) { $address_to_geocode[] = $property['locating']['route']; }
								if ( isset($property['locating']['postal_town']) && trim($property['locating']['postal_town']) != '' ) { $address_to_geocode[] = $property['locating']['postal_town']; }
								if ( isset($property['locating']['locality']) && trim($property['locating']['locality']) != '' ) { $address_to_geocode[] = $property['locating']['locality']; }
								if ( isset($property['locating']['postal_code']) && trim($property['locating']['postal_code']) != '' ) { $address_to_geocode[] = $property['locating']['postal_code']; }

								$request_url = "https://maps.googleapis.com/maps/api/geocode/xml?address=" . urlencode( implode( ", ", $address_to_geocode ) ) . "&sensor=false&region=" . strtolower($country); // the request URL you'll send to google to get back your XML feed
			                    
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
							        	$this->add_error( 'Google Geocoding service returned status ' . $status, $property['id'] );
							        	sleep(3);
							        }
							    }
							    else
						        {
						        	$this->add_error( 'Failed to parse XML response from Google Geocoding service.', $property['id'] );
						        }
							}
							else
					        {
					        	$this->add_error( 'Failed to obtain co-ordinates as allow_url_fopen setting is disabled', $property['id'] );
					        }
					    }
					    else
					    {
					    	$this->add_log( 'Not performing Google Geocoding request as no API key present in settings', $property['id'] );
					    }
					}
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

				if ( isset($property['building_type']) )
				{
					if ( !empty($mapping) && isset($mapping[$property['building_type']]) )
					{
						wp_set_post_terms( $post_id, $mapping[$property['building_type']], $prefix . 'property_type' );
					}
					else
					{
						$this->add_log( 'Property received with a type (' . $property['building_type'] . ') that is not mapped', $property['id'] );

						$options = $this->add_missing_mapping( $mapping, $prefix . 'property_type', $property['building_type'], $import_id );
					}
				}

				update_post_meta( $post_id, '_for_sale', '' );
        		update_post_meta( $post_id, '_to_rent', '' );

        		if ( $property['transaction_type'] == 'for_sale' )
                {
                    update_post_meta( $post_id, '_for_sale', 'yes' );

                    update_post_meta( $post_id, '_commercial_price_currency', 'GBP' );

                    $price = preg_replace("/[^0-9.]/", '', $property['asking_price']);
                    if ( $price == '' )
                    {
                        $price = preg_replace("/[^0-9.]/", '', $property['asking_price']);
                    }
                    update_post_meta( $post_id, '_price_from', $price );

                    $price = preg_replace("/[^0-9.]/", '', $property['asking_price']);
                    if ( $price == '' )
                    {
                        $price = preg_replace("/[^0-9.]/", '', $property['asking_price']);
                    }
                    update_post_meta( $post_id, '_price_to', $price );

                    update_post_meta( $post_id, '_price_units', '' );

                    update_post_meta( $post_id, '_price_poa', ( ( isset($property['sale_poa']) && $property['sale_poa'] === true ) ? 'yes' : '' ) );

                    // Price Qualifier
					if ( isset($_POST['mapped_price_qualifier']) )
					{
						$mapping = $_POST['mapped_price_qualifier'];
					}
					else
					{
						$mapping = isset($options['mappings']['price_qualifier']) ? $options['mappings']['price_qualifier'] : array();
					}

					wp_delete_object_term_relationships( $post_id, 'price_qualifier' );
					if ( !empty($mapping) && isset($property['sale_price_qualifier']) && isset($mapping[$property['sale_price_qualifier']]) )
					{
		                wp_set_post_terms( $post_id, $mapping[$property['sale_price_qualifier']], 'price_qualifier' );
		            }

                    // Tenure
		            if ( isset($_POST['mapped_tenure']) )
					{
						$mapping = $_POST['mapped_tenure'];
					}
					else
					{
						$mapping = isset($options['mappings']['tenure']) ? $options['mappings']['tenure'] : array();
					}

		            wp_delete_object_term_relationships( $post_id, 'commercial_tenure' );
					if ( !empty($mapping) && isset($property['tenure']) && isset($mapping[$property['tenure']]) )
					{
			            wp_set_post_terms( $post_id, $mapping[$property['tenure']], 'commercial_tenure' );
		            }
                }

                if ( $property['transaction_type'] == 'for_rent' )
                {
                    update_post_meta( $post_id, '_to_rent', 'yes' );

                    update_post_meta( $post_id, '_commercial_rent_currency', 'GBP' );

                    $rent = preg_replace("/[^0-9.]/", '', $property['rental_rent']);
                    if ( $rent == '' )
                    {
                        $rent = preg_replace("/[^0-9.]/", '', $property['rental_rent']);
                    }
                    update_post_meta( $post_id, '_rent_from', $rent );

                    $rent = preg_replace("/[^0-9.]/", '', $property['rental_rent']);
                    if ( $rent == '' )
                    {
                        $rent = preg_replace("/[^0-9.]/", '', $property['rental_rent']);
                    }
                    update_post_meta( $post_id, '_rent_to', $rent );

                    $rent_units = '';
                    switch ( $property['rental_rent_type'] )
                    {
                    	case "PSF":
                    	{
							$rent_units = 'psqft';
							break;
                    	}
                    	case "PPPM":
                    	{
							$rent_units = 'pcm';
							break;
                    	}
                    	default:
                    	{
                    		$rent_units = strtolower($property['rental_rent_type']);
                    	}
                    }
                    update_post_meta( $post_id, '_rent_units', $rent_units);

                    update_post_meta( $post_id, '_rent_poa', ( ( isset($property['rental_poa']) && $property['rental_poa'] === true ) ? 'yes' : '' ) );
                }

                // Store price in common currency (GBP) used for ordering
	            $ph_countries = new PH_Countries();
	            $ph_countries->update_property_price_actual( $post_id );

	            $size = preg_replace("/[^0-9.]/", '', $property['rental_sizes_from']);
	            if ( $size == '' )
	            {
	                $size = preg_replace("/[^0-9.]/", '', $property['rental_sizes_to']);
	            }
	            update_post_meta( $post_id, '_floor_area_from', $size );

	            update_post_meta( $post_id, '_floor_area_from_sqft', convert_size_to_sqft( $size, str_replace(" ", "", str_replace("_", "", $property['rental_sizes_dimensions']) ) ) );

	            $size = preg_replace("/[^0-9.]/", '', $property['rental_sizes_to']);
	            if ( $size == '' )
	            {
	                $size = preg_replace("/[^0-9.]/", '', $property['rental_sizes_from']);
	            }
	            update_post_meta( $post_id, '_floor_area_to', $size );

	            update_post_meta( $post_id, '_floor_area_to_sqft', convert_size_to_sqft( $size, str_replace(" ", "", str_replace("_", "", $property['rental_sizes_dimensions']) ) ) );

	            update_post_meta( $post_id, '_floor_area_units', str_replace(" ", "", str_replace("_", "", $property['rental_sizes_dimensions']) ) );

	            /*$size = preg_replace("/[^0-9.]/", '', (string)$property->siteArea);
	            if ( (string)$property->siteArea == '0.00' )
	            {
	            	$size = '';
	            }

	            update_post_meta( $post_id, '_site_area_from', $size );

	            update_post_meta( $post_id, '_site_area_from_sqft', convert_size_to_sqft( $size, str_replace(" ", "", (string)$property->siteAreaUnits ) ) );

	            update_post_meta( $post_id, '_site_area_to', $size );

	            update_post_meta( $post_id, '_site_area_to_sqft', convert_size_to_sqft( $size, str_replace(" ", "", (string)$property->siteAreaUnits ) ) );

	            update_post_meta( $post_id, '_site_area_units', str_replace(" ", "", (string)$property->siteAreaUnits ) );*/

				// Marketing
				update_post_meta( $post_id, '_on_market', 'yes' );
				//update_post_meta( $post_id, '_featured', '' );

				// Availability
				$prefix = '';
				if ( isset($_POST['mapped_' . $prefix . 'availability']) )
				{
					$mapping = $_POST['mapped_' . $prefix . 'availability'];
				}
				else
				{
					$mapping = isset($options['mappings'][$prefix . 'availability']) ? $options['mappings'][$prefix . 'availability'] : array();
				}

        		wp_delete_object_term_relationships( $post_id, 'availability' );
				if ( !empty($mapping) && isset($property['sale_stage']) && isset($mapping[$property['sale_stage']]) )
				{
	                wp_set_post_terms( $post_id, $mapping[$property['sale_stage']], 'availability' );
	            }

	            // Features
				$features = array();
				if ( isset($property['executive_summary']) && !empty($property['executive_summary']) )
				{
					foreach ( $property['executive_summary'] as $feature )
					{
						$features[] = trim((string)$feature);
					}
				}

				update_post_meta( $post_id, '_features', count( $features ) );
        		
        		$i = 0;
		        foreach ( $features as $feature )
		        {
		            update_post_meta( $post_id, '_feature_' . $i, $feature );
		            ++$i;
		        }

		        // Full description
		        $descriptions = 0;
		        if ( isset($property['rental_description']) && $property['rental_description'] != '' )
		        {
					update_post_meta( $post_id, '_description_name_' . $descriptions, '' );
		            update_post_meta( $post_id, '_description_' . $descriptions, $property['rental_description'] );
		            ++$descriptions;
		        }
		        if ( isset($property['floors']) && is_array($property['floors']) && !empty($property['floors']) )
		        {
		        	update_post_meta( $post_id, '_description_name_' . $descriptions, 'Floors' );
		        	$description = '';
		        	foreach ( $property['floors'] as $floor )
		        	{
		        		if ( ( isset($floor['name']) && $floor['name'] != '' ) || ( isset($floor['floor']) && $floor['floor'] != '' ) )
		        		{
		        			if ( $description != '' )
		        			{
		        				$description .= "\n";
		        			}

		        			if ( isset($floor['name']) && $floor['name'] != '' ) { $description .= $floor['name'] . "\n"; }
		        			if ( isset($floor['floor']) && $floor['floor'] != '' ) { $description .= $floor['floor'] . "\n"; }
		        			if ( isset($floor['area_sqft']) && $floor['area_sqft'] != '' ) { $description .= 'Floor Area: ' . $floor['area_sqft'] . " SQ FT\n"; }
		        			if ( isset($floor['available_from']) && $floor['available_from'] != '' ) { $description .= 'Available From: ' . $floor['available_from'] . "\n"; }
		        			if ( isset($floor['availability']) && $floor['availability'] != '' ) { $description .= 'Availability: ' . ucwords($floor['availability']) . "\n"; }
		        			if ( isset($floor['rent']) && $floor['rent'] != '' ) { if ($floor['rent_poa'] == true) { $description .= 'Rent: POA' . "\n"; }else{ $description .= 'Rent: £' . number_format($floor['rent'], 2) . " " . $floor['rent_unit'] . "\n"; } }
		        			if ( isset($floor['service_charge']) && $floor['service_charge'] != '' ) { $description .= 'Service Charge: £' . number_format($floor['service_charge']) . " " . $floor['service_charge_unit'] . "\n"; }
		        			if ( isset($floor['rates_payable']) && $floor['rates_payable'] != '' ) { $description .= 'Rates: £' . number_format($floor['rates_payable']) . " " . $floor['rates_payable_unit'] ."\n"; }
		        			if ( isset($floor['planning_class']) && $floor['planning_class'] != '' ) { $description .= 'Planning Class: ' . $floor['planning_class'] . "\n"; }
		        			if ( isset($floor['key_details']) && $floor['key_details'] != '' ) { $description .= 'Key Details: ' . $floor['key_details'] . "\n"; }
		        		}
		        	}
		        	if ( $description != '' )
		        	{
			            update_post_meta( $post_id, '_description_' . $descriptions, trim($description) );
			            ++$descriptions;
			        }
		        }
		        if ( isset($property['tenancy_schedule']) && is_array($property['tenancy_schedule']) && !empty($property['tenancy_schedule']) )
		        {
		        	update_post_meta( $post_id, '_description_name_' . $descriptions, 'Tenancy Schedule' );
		        	$description = '';
		        	foreach ( $property['tenancy_schedule'] as $tenancy_schedule )
		        	{
		        		if ( isset($tenancy_schedule['unit_number']) && $tenancy_schedule['unit_number'] != '' )
		        		{
		        			if ( $description != '' )
		        			{
		        				$description .= "\n";
		        			}

		        			if ( isset($tenancy_schedule['unit_number']) && $tenancy_schedule['unit_number'] != '' ) { $description .= 'Unit: ' . $tenancy_schedule['unit_number'] . "\n"; }
		        			if ( isset($tenancy_schedule['expiry_date']) && $tenancy_schedule['expiry_date'] != '' ) { $description .= 'Expiry Date: ' . $tenancy_schedule['expiry_date'] . "\n"; }
		        			if ( isset($tenancy_schedule['rent']) && $tenancy_schedule['rent'] != '' ) { $description .= 'Rent: £' . number_format($tenancy_schedule['rent']) . "\n"; }
		        			if ( isset($tenancy_schedule['break_option']) && $tenancy_schedule['break_option'] != '' ) { $description .= 'Break Option: ' . $tenancy_schedule['break_option'] . "\n"; }
		        			if ( isset($tenancy_schedule['next_review']) && $tenancy_schedule['next_review'] != '' ) { $description .= 'Next Review: ' . $tenancy_schedule['next_review'] . "\n"; }
		        			if ( isset($tenancy_schedule['start_date']) && $tenancy_schedule['start_date'] != '' ) { $description .= 'Start Date: ' . $tenancy_schedule['start_date'] . "\n"; }
		        			if ( isset($tenancy_schedule['comments']) && $tenancy_schedule['comments'] != '' ) { $description .= 'Comments: ' . $tenancy_schedule['comments'] . "\n"; }
		        		}
		        	}
		            if ( $description != '' )
		        	{
			            update_post_meta( $post_id, '_description_' . $descriptions, trim($description) );
			            ++$descriptions;
			        }
		        }

	            update_post_meta( $post_id, '_descriptions', $descriptions );

	            // Media - Images
	            if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();
    				if (isset($property['photos']) && !empty($property['photos']))
	                {
	                    foreach ($property['photos'] as $photo)
	                    {
	                    	// Temp fix
	                    	$photo['original_url'] = str_replace("https://", "http://", $photo['original_url']);

	                    	$explode_url = explode("?", $photo['original_url']);
	                    	$photo['original_url'] = $explode_url[0];

							if ( 
								substr( strtolower($photo['original_url']), 0, 2 ) == '//' || 
								substr( strtolower($photo['original_url']), 0, 4 ) == 'http'
							)
							{
								// This is a URL
								$url = $photo['original_url'];

								$media_urls[] = array('url' => $url);
							}
						}
					}
					update_post_meta( $post_id, '_photo_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' photo URLs', $property['id'] );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_photos', TRUE );

					if (isset($property['photos']) && !empty($property['photos']))
	                {
	                    foreach ($property['photos'] as $photo)
	                    {
	                    	// Temp fix
	                    	$photo['original_url'] = str_replace("https://", "http://", $photo['original_url']);

	                    	$explode_url = explode("?", $photo['original_url']);
	                    	$photo['original_url'] = $explode_url[0];

							if ( 
								substr( strtolower($photo['original_url']), 0, 2 ) == '//' || 
								substr( strtolower($photo['original_url']), 0, 4 ) == 'http'
							)
							{
								// This is a URL
								$url = $photo['original_url'];
								$description = ( (isset($photo['description'])) ? $photo['description'] : '' );

								$filename = basename( $url );

								// Check, based on the URL, whether we have previously imported this media
								$imported_previously = false;
								$imported_previously_id = '';
								if ( is_array($previous_media_ids) && !empty($previous_media_ids) )
								{
									foreach ( $previous_media_ids as $previous_media_id )
									{
										if ( get_post_meta( $previous_media_id, '_imported_url', TRUE ) == $url )
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

								        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['id'] );
								    }
								    else
								    {
									    $id = media_handle_sideload( $file_array, $post_id, $description, array('post_title' => $filename) );

									    // Check for handle sideload errors.
									    if ( is_wp_error( $id ) ) 
									    {
									        @unlink( $file_array['tmp_name'] );
									        
									        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['id'] );
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

					$this->add_log( 'Successfully imported ' . count($media_ids) . ' photos (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property['id'] );
				}

				// Media - Floorplans
				if ( get_option('propertyhive_floorplans_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();
    				if (isset($property['floorplans']) && !empty($property['floorplans']))
	                {
	                    foreach ($property['floorplans'] as $floorplan)
	                    {
	                    	// Temp fix
	                    	$floorplan['original_url'] = str_replace("https://", "http://", $floorplan['original_url']);

	                    	$explode_url = explode("?", $floorplan['original_url']);
	                    	$floorplan['original_url'] = $explode_url[0];

							if ( 
								substr( strtolower($floorplan['original_url']), 0, 2 ) == '//' || 
								substr( strtolower($floorplan['original_url']), 0, 4 ) == 'http'
							)
							{
								// This is a URL
								$url = $floorplan['original_url'];

								$media_urls[] = array('url' => $url);
							}
						}
					}

					if (isset($property['documents']) && !empty($property['documents']))
	                {
	                    foreach ($property['documents'] as $floorplan)
	                    {
	                    	if (strtolower($brochure['document_type']) != 'floor plan')
	                    	{
	                    		continue;
	                    	}

	                    	// Temp fix
	                    	$floorplan['original_url'] = str_replace("https://", "http://", $floorplan['original_url']);

	                    	$explode_url = explode("?", $floorplan['original_url']);
	                    	$floorplan['original_url'] = $explode_url[0];

							if ( 
								substr( strtolower($floorplan['original_url']), 0, 2 ) == '//' || 
								substr( strtolower($floorplan['original_url']), 0, 4 ) == 'http'
							)
							{
								// This is a URL
								$url = $floorplan['original_url'];

								$media_urls[] = array('url' => $url);
							}
						}
					}

					update_post_meta( $post_id, '_floorplan_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' floorplan URLs', $property['id'] );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_floorplans', TRUE );

					if (isset($property['floorplans']) && !empty($property['floorplans']))
	                {
	                    foreach ($property['floorplans'] as $floorplan)
	                    {
	                    	// Temp fix
	                    	$floorplan['original_url'] = str_replace("https://", "http://", $floorplan['original_url']);

	                    	$explode_url = explode("?", $floorplan['original_url']);
	                    	$floorplan['original_url'] = $explode_url[0];

							if ( 
								substr( strtolower($floorplan['original_url']), 0, 2 ) == '//' || 
								substr( strtolower($floorplan['original_url']), 0, 4 ) == 'http'
							)
							{
								// This is a URL
								$url = $floorplan['original_url'];
								$description = ( (isset($floorplan['description'])) ? $floorplan['description'] : '' );
							    
								$filename = basename( $url );

								// Check, based on the URL, whether we have previously imported this media
								$imported_previously = false;
								$imported_previously_id = '';
								if ( is_array($previous_media_ids) && !empty($previous_media_ids) )
								{
									foreach ( $previous_media_ids as $previous_media_id )
									{
										if ( get_post_meta( $previous_media_id, '_imported_url', TRUE ) == $url )
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

								        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['id'] );
								    }
								    else
								    {
									    $id = media_handle_sideload( $file_array, $post_id, $description, array('post_title' => $filename) );

									    // Check for handle sideload errors.
									    if ( is_wp_error( $id ) ) 
									    {
									        @unlink( $file_array['tmp_name'] );
									        
									        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['id'] );
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
					if (isset($property['documents']) && !empty($property['documents']))
	                {
	                    foreach ($property['documents'] as $floorplan)
	                    {
	                    	if (strtolower($floorplan['document_type']) != 'floor plan')
	                    	{
	                    		continue;
	                    	}

	                    	// Temp fix
	                    	$floorplan['original_url'] = str_replace("https://", "http://", $floorplan['original_url']);

	                    	$explode_url = explode("?", $floorplan['original_url']);
	                    	$floorplan['original_url'] = $explode_url[0];

							if ( 
								substr( strtolower($floorplan['original_url']), 0, 2 ) == '//' || 
								substr( strtolower($floorplan['original_url']), 0, 4 ) == 'http'
							)
							{
								// This is a URL
								$url = $floorplan['original_url'];
								$description = ( (isset($floorplan['description'])) ? $floorplan['description'] : '' );
							    
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

								        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['id'] );
								    }
								    else
								    {
									    $id = media_handle_sideload( $file_array, $post_id, $description, array('post_title' => $filename) );

									    // Check for handle sideload errors.
									    if ( is_wp_error( $id ) ) 
									    {
									        @unlink( $file_array['tmp_name'] );
									        
									        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['id'] );
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

					$this->add_log( 'Successfully imported ' . count($media_ids) . ' floorplans (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property['id'] );
				}

				// Media - Brochures
				if ( get_option('propertyhive_brochures_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();
    				if (isset($property['documents']) && !empty($property['documents']))
	                {
	                    foreach ($property['documents'] as $brochure)
	                    {
	                    	if (strtolower($brochure['document_type']) == 'epc' || strtolower($brochure['document_type']) == 'floor plan')
	                    	{
	                    		continue;
	                    	}

	                    	// Temp fix
	                    	$brochure['original_url'] = str_replace("https://", "http://", $brochure['original_url']);

	                    	$explode_url = explode("?", $brochure['original_url']);
	                    	$brochure['original_url'] = $explode_url[0];

							if ( 
								substr( strtolower($brochure['original_url']), 0, 2 ) == '//' || 
								substr( strtolower($brochure['original_url']), 0, 4 ) == 'http'
							)
							{
								// This is a URL
								$url = $brochure['original_url'];

								$media_urls[] = array('url' => $url);
							}
						}
					}
					update_post_meta( $post_id, '_brochure_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' brochure URLs', $property['id'] );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_brochures', TRUE );

					if (isset($property['documents']) && !empty($property['documents']))
	                {
	                    foreach ($property['documents'] as $brochure)
	                    {
	                    	if (strtolower($brochure['document_type']) == 'epc' || strtolower($brochure['document_type']) == 'floor plan')
	                    	{
	                    		continue;
	                    	}

	                    	// Temp fix
	                    	$brochure['original_url'] = str_replace("https://", "http://", $brochure['original_url']);

	                    	$explode_url = explode("?", $brochure['original_url']);
	                    	$brochure['original_url'] = $explode_url[0];

							if ( 
								substr( strtolower($brochure['original_url']), 0, 2 ) == '//' || 
								substr( strtolower($brochure['original_url']), 0, 4 ) == 'http'
							)
							{
								// This is a URL
								$url = $brochure['original_url'];
								$description = ( (isset($brochure['description'])) ? $brochure['description'] : '' );
							    
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

								        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['id'] );
								    }
								    else
								    {
									    $id = media_handle_sideload( $file_array, $post_id, $description, array('post_title' => $filename) );

									    // Check for handle sideload errors.
									    if ( is_wp_error( $id ) ) 
									    {
									        @unlink( $file_array['tmp_name'] );
									        
									        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['id'] );
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

					$this->add_log( 'Successfully imported ' . count($media_ids) . ' brochures (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property['id'] );
				}

				// Media - EPCs
				if ( get_option('propertyhive_epcs_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();

    				if (isset($property['documents']) && !empty($property['documents']))
	                {
	                    foreach ($property['documents'] as $epc)
	                    {
	                    	if (strtolower($brochure['document_type']) != 'epc')
	                    	{
	                    		continue;
	                    	}

	                    	// Temp fix
	                    	$epc['original_url'] = str_replace("https://", "http://", $epc['original_url']);

	                    	$explode_url = explode("?", $epc['original_url']);
	                    	$epc['original_url'] = $explode_url[0];

							if ( 
								substr( strtolower($epc['original_url']), 0, 2 ) == '//' || 
								substr( strtolower($epc['original_url']), 0, 4 ) == 'http'
							)
							{
								// This is a URL
								$url = $epc['original_url'];

								$media_urls[] = array('url' => $url);
							}
						}
					}

					update_post_meta( $post_id, '_epc_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' EPC URLs', $property['id'] );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_epcs', TRUE );

					if (isset($property['documents']) && !empty($property['documents']))
	                {
	                    foreach ($property['documents'] as $epc)
	                    {
	                    	if (strtolower($brochure['document_type']) != 'epc')
	                    	{
	                    		continue;
	                    	}

	                    	// Temp fix
	                    	$epc['original_url'] = str_replace("https://", "http://", $epc['original_url']);

	                    	$explode_url = explode("?", $epc['original_url']);
	                    	$epc['original_url'] = $explode_url[0];

							if ( 
								substr( strtolower($epc['original_url']), 0, 2 ) == '//' || 
								substr( strtolower($epc['original_url']), 0, 4 ) == 'http'
							)
							{
								// This is a URL
								$url = $epc['original_url'];
								$description = ( (isset($epc['description'])) ? $epc['description'] : '' );
							    
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

								        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['id'] );
								    }
								    else
								    {
									    $id = media_handle_sideload( $file_array, $post_id, $description, array('post_title' => $filename) );

									    // Check for handle sideload errors.
									    if ( is_wp_error( $id ) ) 
									    {
									        @unlink( $file_array['tmp_name'] );
									        
									        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['id'] );
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

					$this->add_log( 'Successfully imported ' . count($media_ids) . ' EPCs (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property['id'] );
				}

				// Media - Virtual Tours
				/*$virtual_tours = array();
				if (isset($property->virtualTours) && !empty($property->virtualTours))
                {
                    foreach ($property->virtualTours as $virtualTours)
                    {
                        if (!empty($virtualTours->virtualTour))
                        {
                            foreach ($virtualTours->virtualTour as $virtualTour)
                            {
                            	$virtual_tours[] = $virtualTour;
                            }
                        }
                    }
                }

                update_post_meta( $post_id, '_virtual_tours', count($virtual_tours) );
                foreach ($virtual_tours as $i => $virtual_tour)
                {
                	update_post_meta( $post_id, '_virtual_tour_' . $i, (string)$virtual_tour );
                }

				$this->add_log( 'Successfully imported ' . count($virtual_tours) . ' virtual tours', $property['id'] );*/

				do_action( "propertyhive_property_imported_realla_json", $post_id, $property );

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
							$this->add_log( 'New meta data for ' . trim($key, '_') . ': ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), $property['id'] );
						}
						elseif ( $metadata_before[$key] != $metadata_after[$key] )
						{
							$this->add_log( 'Updated ' . trim($key, '_') . '. Before: ' . ( ( is_array($metadata_before[$key]) ) ? implode(", ", $metadata_before[$key]) : $metadata_before[$key] ) . ', After: ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), $property['id'] );
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
							$this->add_log( 'New taxonomy data for ' . $taxonomy_name . ': ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), $property['id'] );
						}
						elseif ( $taxonomy_terms_before[$taxonomy_name] != $taxonomy_terms_after[$taxonomy_name] )
						{
							$this->add_log( 'Updated ' . $taxonomy_name . '. Before: ' . ( ( is_array($taxonomy_terms_before[$taxonomy_name]) ) ? implode(", ", $taxonomy_terms_before[$taxonomy_name]) : $taxonomy_terms_before[$taxonomy_name] ) . ', After: ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), $property['id'] );
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

		do_action( "propertyhive_post_import_properties_realla_json" );

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
			$import_refs[] = $property['id'];
		}

		$args = array(
			'post_type' => 'property',
			'nopaging' => true,
			'meta_query' => array(
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
			),
		);
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

				do_action( "propertyhive_property_removed_realla_json", $post->ID );
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
		$mapping_values = $this->get_xml_mapping_values('availability');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['availability'][$mapping_value] = '';
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

		$mapping_values = $this->get_xml_mapping_values('price_qualifier');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['price_qualifier'][$mapping_value] = '';
			}
		}

		$mapping_values = $this->get_xml_mapping_values('tenure');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['tenure'][$mapping_value] = '';
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
        if ($custom_field == 'availability')
        {
            return array(
            	'available' => 'available',
            	'under_offer' => 'under_offer',
            );
        }
        if ($custom_field == 'commercial_property_type')
        {
        	return array(
                'office' => 'office',
                'land' => 'land',
                'serviced office' => 'serviced office',
                'retail' => 'retail',
                'car showroom' => 'car showroom',
            );
        }
        if ($custom_field == 'price_qualifier')
        {
        	return array(
        		'sale_no_refinement' => 'sale_no_refinement',
        		'sale_offers_in_excess' => 'sale_offers_in_excess',
        	);
        }
        if ($custom_field == 'tenure')
        {
            return array(
                'freehold' => 'freehold',
                'leasehold' => 'leasehold',
                'long_leasehold' => 'long_leasehold',
            );
        }
    }

}

}