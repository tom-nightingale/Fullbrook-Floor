<?php
/**
 * Class for managing the import process of a Gnomen XML file
 *
 * @package WordPress
 */
if ( class_exists( 'PH_Property_Import_Process' ) ) {

class PH_Gnomen_XML_Import extends PH_Property_Import_Process {

	public function __construct( $instance_id = '' ) 
	{
		$this->instance_id = $instance_id;

		if ( $this->instance_id != '' && isset($_GET['custom_property_import_cron']) )
	    {
	    	$current_user = wp_get_current_user();

	    	$this->add_log("Executed manually by " . ( ( isset($current_user->display_name) ) ? $current_user->display_name : '' ) );
	    }
	}

	public function parse( $options = array() )
	{
		$this->properties = array(); // Reset properties in the event we're importing multiple files

		$xml = simplexml_load_file( $options['url'] );
		if ($xml !== FALSE)
		{
			$this->add_log("Parsing properties");
			
			foreach ($xml->property as $property)
			{
				$explode_url = explode("/", $options['url']);
				if ( $explode_url[count($explode_url)-1] == 'xml-feed' )
				{
					$url = trim($options['url'], '/') . '-details~action=detail,pid=' . (string)$property->id;
				}
				else
				{
					$url = trim($options['url'], '/') . '/xml-feed-details~action=detail,pid=' . (string)$property->id;
				}

				$response = wp_remote_get( $url );

				if ( is_array( $response ) ) 
				{
					$body = $response['body']; 
				
					$xml_string = "<?xml version='1.0'?>" . $body;
					
					$property_xml = simplexml_load_string( $xml_string );

					if ($property_xml !== FALSE)
					{
						$property_xml->addChild( 'latitude' );
						$property_xml->latitude = ( isset($property->latitude) ? (string)$property->latitude : '' );
						$property_xml->addChild( 'longitude' );
						$property_xml->longitude = ( isset($property->longitude) ? (string)$property->longitude : '' );
						$property_xml->addChild( 'videotour' );
						$property_xml->videotour = ( isset($property->videotour) ? (string)$property->videotour : '' );
						$property_xml->addChild( 'virtualtour' );
						$property_xml->virtualtour = ( isset($property->virtualtour) ? (string)$property->virtualtour : '' );
						$property_xml->addChild( 'epc' );
						$property_xml->epc = ( isset($property->epc) ? (string)$property->epc : '' );
						$property_xml->addChild( 'brochure' );
						$property_xml->brochure = ( isset($property->brochure) ? (string)$property->brochure : '' );

	                	$this->properties[] = $property_xml;
	                }
	                else
			        {
			        	// Failed to parse XML
			        	$this->add_error( 'Failed to parse property (id: ' . (string)$property->id . ', url: ' . $url . ') XML file. Possibly invalid XML' );
			        	return false;
			        }
		        }
		        else
		        {
		        	$this->add_error( 'Failed to obtain property (id: ' . (string)$property->id . ', url: ' . $url . ') XML file.' );
			        return false;
		        }
            } // end foreach property
        }
        else
        {
        	// Failed to parse XML
        	$this->add_error( 'Failed to parse properties XML file. Possibly invalid XML' );
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

        do_action( "propertyhive_pre_import_properties_gnomen_xml", $this->properties );
        $this->properties = apply_filters( "propertyhive_gnomen_xml_properties_due_import", $this->properties );

		$this->add_log( 'Beginning to loop through ' . count($this->properties) . ' properties' );

		$property_row = 1;
		foreach ( $this->properties as $property )
		{
			$this->add_log( 'Importing property ' . $property_row .' with reference ' . (string)$property->id, (string)$property->id );

			$inserted_updated = false;

			$display_address = isset($property->address1) ? trim((string)$property->address1) : '';
			if ( isset($property->address2) && trim((string)$property->address2) != '' )
			{
				if ( trim($display_address) != '' )
				{
					$display_address .= ', ';
				}
				$display_address .= (string)$property->address2;
			}
			elseif ( isset($property->area) && trim((string)$property->area) != '' )
			{
				if ( trim($display_address) != '' )
				{
					$display_address .= ', ';
				}
				$display_address .= (string)$property->area;
			}
			$display_address = trim($display_address);

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
				    	'post_excerpt'   => html_entity_decode(html_entity_decode((string)$property->description)),
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
					'post_excerpt'   => html_entity_decode(html_entity_decode((string)$property->description)),
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
					($display_address != '' || html_entity_decode(html_entity_decode((string)$property->description)) != '')
				)
				{
					$my_post = array(
				    	'ID'          	 => $post_id,
				    	'post_title'     => htmlentities(mb_convert_encoding(wp_strip_all_tags( $display_address ), 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
				    	'post_excerpt'   => htmlentities(mb_convert_encoding(html_entity_decode(html_entity_decode((string)$property->description)), 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
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

				update_post_meta( $post_id, $imported_ref_key, (string)$property->id );

				// Address
				update_post_meta( $post_id, '_reference_number', (string)$property->reference );

				update_post_meta( $post_id, '_address_name_number', trim(( isset($property->property_no) ? (string)$property->property_no : '' ) . ' ' . ( isset($property->property_name) ? (string)$property->property_name : '' )) );
				update_post_meta( $post_id, '_address_street', ( isset($property->address1) ? (string)$property->address1 : '' ) );
				update_post_meta( $post_id, '_address_two', ( isset($property->address2) ? (string)$property->address2 : '' ) );
				update_post_meta( $post_id, '_address_three', ( isset($property->area) ? (string)$property->area : '' ) );
				update_post_meta( $post_id, '_address_four', ( isset($property->county) ? (string)$property->county : '' ) );
				update_post_meta( $post_id, '_address_postcode', ( isset($property->postcode) ? (string)$property->postcode : '' ) );

				$country = 'GB';
				update_post_meta( $post_id, '_address_country', $country );

				// Check main address fields and see if this location exists as a taxonomy to try and assign properties to location
				$address_fields_to_check = apply_filters( 'propertyhive_gnomen_xml_address_fields_to_check', array('address2', 'area', 'county') );
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

				if ( !empty($location_term_ids) )
				{
					wp_set_post_terms( $post_id, $location_term_ids, 'location' );
				}

				// Coordinates
				if ( isset($property->latitude) && isset($property->longitude) && (string)$property->latitude != '' && (string)$property->longitude != '' && (string)$property->latitude != '0' && (string)$property->longitude != '0' )
				{
					update_post_meta( $post_id, '_latitude', trim((string)$property->latitude) );
					update_post_meta( $post_id, '_longitude', trim((string)$property->longitude) );
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
								$address_to_geocode = array((string)$property->postcode);

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
					    	$this->add_log( 'Not performing Google Geocoding request as no API key present in settings', (string)$property->AGENT_REF );
					    }
					}
				}

				// Owner
				add_post_meta( $post_id, '_owner_contact_id', '', true );

				// Record Details
				add_post_meta( $post_id, '_negotiator_id', get_current_user_id(), true );
					
				$office_id = $primary_office_id;
				if ( isset($_POST['mapped_office'][(string)$property->branch_name]) && $_POST['mapped_office'][(string)$property->branch_name] != '' )
				{
					$office_id = $_POST['mapped_office'][(string)$property->branch_name];
				}
				elseif ( isset($options['offices']) && is_array($options['offices']) && !empty($options['offices']) )
				{
					foreach ( $options['offices'] as $ph_office_id => $branch_code )
					{
						if ( $branch_code == (string)$property->branch_name )
						{
							$office_id = $ph_office_id;
							break;
						}
					}
				}
				update_post_meta( $post_id, '_office_id', $office_id );

				// Residential Details
	            $department = ( ( isset($property->transaction) && (string)$property->transaction == 'Sale' ) ? 'residential-sales' : 'residential-lettings' );
	            if ( isset($property->category) && (string)$property->category == '2' )
	            {
	            	$department = 'commercial';
	            }
	            update_post_meta( $post_id, '_department', $department );

				update_post_meta( $post_id, '_bedrooms', ( ( isset($property->beds) ) ? round((string)$property->beds) : '' ) );
				update_post_meta( $post_id, '_bathrooms', ( ( isset($property->baths) ) ? round((string)$property->baths) : '' ) );
				update_post_meta( $post_id, '_reception_rooms', ( ( isset($property->receptions) ) ? round((string)$property->receptions) : '' ) );

				$prefix = '';
				if ( $department == 'commercial' )
				{
					$prefix = 'commercial_';
				}
				if ( isset($_POST['mapped_' . $prefix . 'property_type']) )
				{
					$mapping = $_POST['mapped_' . $prefix . 'property_type'];
				}
				else
				{
					$mapping = isset($options['mappings'][$prefix . 'property_type']) ? $options['mappings'][$prefix . 'property_type'] : array();
				}

				wp_delete_object_term_relationships( $post_id, $prefix . 'property_type' );

				if ( isset($property->property_type) )
				{
					if ( !empty($mapping) && isset($mapping[(string)$property->property_type]) )
					{
						wp_set_post_terms( $post_id, $mapping[(string)$property->property_type], $prefix . 'property_type' );
					}
					else
					{
						$this->add_log( 'Property received with a type (' . (string)$property->property_type . ') that is not mapped', (string)$property->id );

						$options = $this->add_missing_mapping( $mapping, $prefix . 'property_type', (string)$property->property_type, $import_id );
					}
				}

				// Residential Sales Details
				if ( $department == 'residential-sales' )
				{
					// Clean price
					$price = round(preg_replace("/[^0-9.]/", '', (string)$property->base_price));

					update_post_meta( $post_id, '_price', $price );
					update_post_meta( $post_id, '_price_actual', $price );
					update_post_meta( $post_id, '_poa', '' );

					update_post_meta( $post_id, '_currency', 'GBP' );

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
					if ( !empty($mapping) && isset($property->price_qualifier) && isset($mapping[(string)$property->price_qualifier]) )
					{
		                wp_set_post_terms( $post_id, $mapping[(string)$property->price_qualifier], 'price_qualifier' );
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

		            wp_delete_object_term_relationships( $post_id, 'tenure' );
					if ( !empty($mapping) && isset($property->tenure) && isset($mapping[(string)$property->tenure]) )
					{
			            wp_set_post_terms( $post_id, $mapping[(string)$property->tenure], 'tenure' );
		            }
				}
				elseif ( $department == 'residential-lettings' )
				{
					// Clean price
					$price = round(preg_replace("/[^0-9.]/", '', (string)$property->base_price));

					update_post_meta( $post_id, '_rent', $price );

					$rent_frequency = isset($property->frequency) ? strtolower((string)$property->frequency) : 'pcm';
					$price_actual = $price;
					update_post_meta( $post_id, '_rent_frequency', $rent_frequency );
					update_post_meta( $post_id, '_price_actual', $price_actual );

					update_post_meta( $post_id, '_currency', 'GBP' );
					
					update_post_meta( $post_id, '_poa', '' );

					update_post_meta( $post_id, '_deposit', '');
					$available_date = '';
					if ( isset($property->available_date) && (string)$property->available_date != '' )
					{
						$available_date = (string)$property->available_date;
					}
            		update_post_meta( $post_id, '_available_date', $available_date );

            		// Furnished
            		if ( isset($_POST['mapped_furnished']) )
					{
						$mapping = $_POST['mapped_furnished'];
					}
					else
					{
						$mapping = isset($options['mappings']['furnished']) ? $options['mappings']['furnished'] : array();
					}

            		wp_delete_object_term_relationships( $post_id, 'furnished' );
					if ( !empty($mapping) && isset($property->furnished) && isset($mapping[round((string)$property->furnished)]) )
					{
		                wp_set_post_terms( $post_id, $mapping[round((string)$property->furnished)], 'furnished' );
		            }
		        }
		        elseif ( $department == 'commercial' )
				{
					update_post_meta( $post_id, '_for_sale', '' );
            		update_post_meta( $post_id, '_to_rent', '' );

            		if ( isset($property->transaction) && (string)$property->transaction == 'Sale' )
	                {
	                    update_post_meta( $post_id, '_for_sale', 'yes' );

	                    update_post_meta( $post_id, '_commercial_price_currency', 'GBP' );

	                    $price = round(preg_replace("/[^0-9.]/", '', (string)$property->base_price));
	                    update_post_meta( $post_id, '_price_from', $price );
	                    update_post_meta( $post_id, '_price_to', $price );

	                    update_post_meta( $post_id, '_price_units', '' );

	                    update_post_meta( $post_id, '_price_poa', '' );

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
						if ( !empty($mapping) && isset($property->tenure) && isset($mapping[(string)$property->tenure]) )
						{
				            wp_set_post_terms( $post_id, $mapping[(string)$property->tenure], 'commercial_tenure' );
			            }
	                }

	                if ( isset($property->transaction) && (string)$property->transaction == 'Let' )
	                {
	                    update_post_meta( $post_id, '_to_rent', 'yes' );

	                    update_post_meta( $post_id, '_commercial_rent_currency', 'GBP' );

	                    $rent = round(preg_replace("/[^0-9.]/", '', (string)$property->base_price));
	                    update_post_meta( $post_id, '_rent_from', $rent );
	                    update_post_meta( $post_id, '_rent_to', $rent );

	                    $rent_frequency = isset($property->frequency) ? strtolower((string)$property->frequency) : 'pcm';
	                    update_post_meta( $post_id, '_rent_units', $rent_frequency);

	                    update_post_meta( $post_id, '_rent_poa', '' );
	                }

	                // Store price in common currency (GBP) used for ordering
		            $ph_countries = new PH_Countries();
		            $ph_countries->update_property_price_actual( $post_id );

		            $size = '';
		            update_post_meta( $post_id, '_floor_area_from', $size );
		            update_post_meta( $post_id, '_floor_area_from_sqft', $size );
		            update_post_meta( $post_id, '_floor_area_to', $size );
		            update_post_meta( $post_id, '_floor_area_to_sqft', $size );
		            update_post_meta( $post_id, '_floor_area_units', 'sqft' );

		            $size = '';
		            update_post_meta( $post_id, '_site_area_from', $size );
		            update_post_meta( $post_id, '_site_area_from_sqft', $size );
		            update_post_meta( $post_id, '_site_area_to', $size );
		            update_post_meta( $post_id, '_site_area_to_sqft', $size );
		            update_post_meta( $post_id, '_site_area_units', 'sqft' );
		        }		

				// Marketing
				update_post_meta( $post_id, '_on_market', 'yes' );
				//add_post_meta( $post_id, '_featured', '' );

				// Availability
				if ( isset($_POST['mapped_availability']) )
				{
					$mapping = $_POST['mapped_availability'];
				}
				else
				{
					$mapping = isset($options['mappings']['availability']) ? $options['mappings']['availability'] : array();
				}
				$mapping = array_change_key_case($mapping, CASE_LOWER);

        		wp_delete_object_term_relationships( $post_id, 'availability' );
				if ( !empty($mapping) && isset($property->status) && isset($mapping[strtolower((string)$property->status)]) )
				{
	                wp_set_post_terms( $post_id, $mapping[strtolower((string)$property->status)], 'availability' );
	            }

	            // Features
				$features = array();
				if ( isset($property->features->feature) && !empty($property->features->feature) )
				{
					foreach ( $property->features->feature as $feature )
					{
						$features[] = (string)$feature;
					}
				}
				
				update_post_meta( $post_id, '_features', count( $features ) );
        		
        		$i = 0;
		        foreach ( $features as $feature )
		        {
		            update_post_meta( $post_id, '_feature_' . $i, $feature );
		            ++$i;
		        }

		        // Rooms
	            $num_rooms = 0;
	            if ( isset($property->full_description) && (string)$property->full_description != '' )
	            {
	            	update_post_meta( $post_id, '_room_name_' . $num_rooms, '' );
		            update_post_meta( $post_id, '_room_dimensions_' . $num_rooms, '' );
		            update_post_meta( $post_id, '_room_description_' . $num_rooms, html_entity_decode(html_entity_decode((string)$property->full_description)) );

	            	++$num_rooms;
	            }

	            update_post_meta( $post_id, '_rooms', $num_rooms );

	            // Media - Images
	            if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();
    				if (isset($property->images->image) && !empty($property->images->image))
	                {
	                    foreach ($property->images->image as $image)
	                    {
	                    	if ( 
								substr( strtolower((string)$image), 0, 2 ) == '//' || 
								substr( strtolower((string)$image), 0, 4 ) == 'http'
							)
							{
								// This is a URL
								$url = (string)$image;

								$media_urls[] = array('url' => $url);
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
					$i = 0;
					if (isset($property->images->image) && !empty($property->images->image))
	                {
	                    foreach ($property->images->image as $image)
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
    				if (isset($property->floorplans->floorplan) && !empty($property->floorplans->floorplan))
	                {
	                    foreach ($property->floorplans->floorplan as $floorplan)
	                    {
							if ( 
								substr( strtolower((string)$floorplan), 0, 2 ) == '//' || 
								substr( strtolower((string)$floorplan), 0, 4 ) == 'http'
							)
							{
								// This is a URL
								$url = (string)$floorplan;

								$media_urls[] = array('url' => $url);
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
					$i = 0;
					if (isset($property->floorplans->floorplan) && !empty($property->floorplans->floorplan))
	                {
	                    foreach ($property->floorplans->floorplan as $floorplan)
	                    {
							if ( 
								substr( strtolower((string)$floorplan), 0, 2 ) == '//' || 
								substr( strtolower((string)$floorplan), 0, 4 ) == 'http'
							)
							{
								// This is a URL
								$url = (string)$floorplan;
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
								        'name' => basename( $url ) . '.jpg',
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
    				if (isset($property->brochure) && !empty($property->brochure))
	                {
	                    foreach ($property->brochure as $brochure)
	                    {
							if ( 
								substr( strtolower((string)$brochure), 0, 2 ) == '//' || 
								substr( strtolower((string)$brochure), 0, 4 ) == 'http'
							)
							{
								// This is a URL
								$url = (string)$brochure;

								$media_urls[] = array('url' => $url);
							}
						}
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
					$i = 0;
					if (isset($property->brochure) && (string)$property->brochure != '')
	                {
	                    if ( 
							substr( strtolower((string)$property->brochure), 0, 2 ) == '//' || 
							substr( strtolower((string)$property->brochure), 0, 4 ) == 'http'
						)
						{
							// This is a URL
							$url = (string)$property->brochure;
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
					update_post_meta( $post_id, '_brochures', $media_ids );

					$this->add_log( 'Imported ' . count($media_ids) . ' brochures (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->id );
				}

				// Media - EPCs
				if ( get_option('propertyhive_epcs_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();
    				if (isset($property->epc) && !empty($property->epc))
	                {
	                    foreach ($property->epc as $epc)
	                    {
							if ( 
								substr( strtolower((string)$epc), 0, 2 ) == '//' || 
								substr( strtolower((string)$epc), 0, 4 ) == 'http'
							)
							{
								// This is a URL
								$url = (string)$epc;

								$media_urls[] = array('url' => $url);
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
					$i = 0;
					if ( isset($property->epc) && (string)$property->epc != '')
	                {
	                    if ( 
							substr( strtolower((string)$property->epc), 0, 2 ) == '//' || 
							substr( strtolower((string)$property->epc), 0, 4 ) == 'http'
						)
						{
							// This is a URL
							$url = (string)$property->epc;
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
					update_post_meta( $post_id, '_epcs', $media_ids );

					$this->add_log( 'Imported ' . count($media_ids) . ' EPCs (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->id );
				}

				// Media - Virtual Tours
				$virtual_tours = array();
				if ( isset($property->videotour) && (string)$property->videotour != '' )
                {
                    $virtual_tours[] = (string)$property->videotour;
                }
                if ( isset($property->virtualtour) && (string)$property->virtualtour != '' )
                {
                    $virtual_tours[] = (string)$property->virtualtour;
                }

                update_post_meta( $post_id, '_virtual_tours', count($virtual_tours) );
                foreach ($virtual_tours as $i => $virtual_tour)
                {
                	update_post_meta( $post_id, '_virtual_tour_' . $i, (string)$virtual_tour );
                }

				$this->add_log( 'Imported ' . count($virtual_tours) . ' virtual tours', (string)$property->id );

				do_action( "propertyhive_property_imported_gnomen_xml", $post_id, $property );

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

		do_action( "propertyhive_post_import_properties_gnomen_xml" );

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

				do_action( "propertyhive_property_removed_gnomen_xml", $post->ID );
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

		$mapping_values = $this->get_xml_mapping_values('property_type');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['property_type'][$mapping_value] = '';
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

		$mapping_values = $this->get_xml_mapping_values('furnished');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['furnished'][$mapping_value] = '';
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
                'For Sale' => 'For Sale',
                'To Let' => 'To Let',
                'Sold Subject to Contract' => 'Sold Subject to Contract',
                'Under Offer' => 'Under Offer',
                'Let Agreed' => 'Let Agreed',
            );
        }
        if ($custom_field == 'property_type')
        {
        	return array(
                'Detached House' => 'Detached House',
                'Semi-Detached House' => 'Semi-Detached House',
                'Terraced House' => 'Terraced House',
                'End of Terrace House' => 'End of Terrace House',
                'Town House' => 'Town House',
                'Apartment' => 'Apartment',
                'Flat' => 'Flat',
                'Maisonette' => 'Maisonette'
            );
        }
        if ($custom_field == 'commercial_property_type')
        {
        	return array(
                'Office' => 'Office',
                'Warehouse conversion' => 'Warehouse conversion'
            );
        }
        /*if ($custom_field == 'price_qualifier')
        {
        	return array(
        		'1' => 'Asking',
        		'2' => 'Price on application',
        		'3' => 'Guide Price',
        		'4' => 'Offers in excess of',
        		'5' => 'Offers in region of',
        		'6' => 'Fixed',
        	);
        }*/
        if ($custom_field == 'tenure')
        {
        	return array(
                'Freehold' => 'Freehold',
                'Leasehold' => 'Leasehold',
            );
        }
        if ($custom_field == 'furnished')
        {
        	return array(
                'Furnished' => 'Furnished',
                'Unfurnished' => 'Unfurnished',
                'Unfurnished (White Goods)' => 'Unfurnished (White Goods)',
                'Part Furnished' => 'Part Furnished',
            );
        }
    }

}

}