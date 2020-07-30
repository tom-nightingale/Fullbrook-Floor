<?php
/**
 * Class for managing the import process of a Rex API JSON file
 *
 * @package WordPress
 */
if ( class_exists( 'PH_Property_Import_Process' ) ) {

class PH_Rex_JSON_Import extends PH_Property_Import_Process {

	public function __construct( $instance_id = '' ) 
	{
		$this->instance_id = $instance_id;

		if ( $this->instance_id != '' && isset($_GET['custom_property_import_cron']) )
	    {
	    	$current_user = wp_get_current_user();

	    	$this->add_log("Executed manually by " . ( ( isset($current_user->display_name) ) ? $current_user->display_name : '' ) );
	    }
	}

	private function get_token( $import_id = '' )
	{
		$options = get_option( 'propertyhive_property_import' );
		if (isset($options[$import_id]))
		{
			$options = $options[$import_id];
		}
		else
		{
			$options = array();
		}

		$url = 'https://api.uk.rexsoftware.com';

		$endpoint = '/v1/rex/Authentication/login';

		$data = array(
			'email' => $options['username'],
		    'password' => $options['password'],
		    //'application' => 'rex' // getting error when using this even though it's in the docs
		);

		$data = apply_filters( 'propertyhive_rex_authentication_request_body', $data );

		$data = json_encode($data);

		if ( !$data )
		{
			$this->add_error( 'Failed to encode authentication request data' );
			return false;
		}

		$response = wp_remote_post(
			$url . $endpoint,
			array(
				'body' => $data,
				'headers' => array(
					'Content-Type' => 'application/json'
				),
			)
		);

		if ( is_wp_error($response) )
		{
			$this->add_error( 'WP Error returned in response from authentication' );
			return false;
		}

		$json = json_decode( $response['body'], TRUE );

		if ( !$json )
		{
			$this->add_error( 'Failed to decode authentication response data' );
			return false;
		}

		if ( isset($json['error']) && !empty($json['error']) )
		{
			$this->add_error( 'Error returned in response from authentication: ' . print_r( $json['error'], TRUE ) );
        	return false;
		}

		if ( !isset($json['result']) )
		{
			$this->add_error( 'No result in response from authentication: ' . print_r( $json, TRUE ) );
        	return false;
		}

		// get token from result
		$token = $json['result'];

		return $token;
	}

	public function parse( $import_id = '' )
	{
		$token = $this->get_token( $import_id );

		if ( !$token )
		{
			return false;
		}

		$url = 'https://api.uk.rexsoftware.com';

		$endpoint = '/v1/rex/published-listings/search';

		$data = array(
			'result_format' => 'website_overrides_applied',
			'extra_options' => array(
				'extra_fields' => array( 'documents', 'highlights', 'links', 'rooms', 'images', 'floorplans', 'tags', 'features', 'advert_internet', 'advert_brochure', 'advert_stocklist', 'subcategories' ),
			),
			'criteria' => array(
				array(
					"name" => "listing.system_listing_state", 
					"type" => "notin",
					"value" => array("withdrawn")
				)
			),
			'limit' => 100,
			'order_by' => array('system_publication_time' => 'desc')
		);

		$data = apply_filters( 'propertyhive_rex_property_request_body', $data );

		$data = json_encode($data);

		if ( !$data )
		{
			$this->add_error( 'Failed to encode property request data' );
			return false;
		}

		$response = wp_remote_post(
			$url . $endpoint,
			array(
				'body' => $data,
				'headers' => array(
					'Content-Type' => 'application/json',
					'Authorization' => 'Bearer ' . $token
				),
			)
		);

		if ( is_wp_error($response) )
		{
			$this->add_error( 'WP Error returned in response from properties' );
			return false;
		}

		$json = json_decode( $response['body'], TRUE );

		if ( !$json )
		{
			$this->add_error( 'Failed to decode property response data' );
			return false;
		}

		if ( isset($json['error']) && !empty($json['error']) )
		{
			$this->add_error( 'Error returned in response from properties: ' . print_r( $json['error'], TRUE ) );
        	return false;
		}

		if ( !isset($json['result']) )
		{
			$this->add_error( 'No result in response from properties: ' . print_r( $json, TRUE ) );
        	return false;
		}

		if ( is_array($json['result']['rows']) && !empty($json['result']['rows']) )
		{
			$this->add_log("Parsing properties");
			
			$this->add_log("Found " . count($json['result']['rows']) . " properties in JSON ready for parsing");

			foreach ($json['result']['rows'] as $property)
			{
				$this->properties[] = $property;
			}
        }
        else
        {
        	// Failed to parse JSON
        	$this->add_error( 'Rows missing or empty from property response' );
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

        do_action( "propertyhive_pre_import_properties_rex_json", $this->properties );
        $this->properties = apply_filters( "propertyhive_rex_json_properties_due_import", $this->properties );

		$this->add_log( 'Beginning to loop through ' . count($this->properties) . ' properties' );

		$property_row = 1;
		foreach ( $this->properties as $property )
		{
			$this->add_log( 'Importing property with reference ' . $property['id'], $property['id'] );

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

	        $display_address = array();
	        if ( isset($property['address']['street_name']) && trim($property['address']['street_name']) != '' )
	        {
	        	$display_address[] = trim($property['address']['street_name']);
	        }
	        if ( isset($property['address']['locality']) && trim($property['address']['locality']) != '' )
	        {
	        	$display_address[] = trim($property['address']['locality']);
	        }
	        elseif ( isset($property['address']['suburb_or_town']) && trim($property['address']['suburb_or_town']) != '' )
	        {
	        	$display_address[] = trim($property['address']['suburb_or_town']);
	        }
	        $display_address = implode(", ", $display_address);
	        
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
				    	'post_title'     => wp_strip_all_tags( $display_address ),
				    	'post_excerpt'   => $property['advert_internet']['heading'],
				    	'post_content' 	 => '',
				    	'post_status'    => 'publish',
				  	);

				 	// Update the post into the database
				    $post_id = wp_update_post( $my_post );

				    if ( is_wp_error( $post_id ) ) 
					{
						$this->add_error( 'Failed to update post. The error was as follows: ' . $post_id->get_error_message(), $property['id'] );
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
					'post_date'      => isset($property['system_publication_timestamp']) ? date( 'Y-m-d H:i:s', $property['system_publication_timestamp'] ) : '',
					'post_date_gmt'  => isset($property['system_publication_timestamp']) ? date( 'Y-m-d H:i:s', $property['system_publication_timestamp'] ) : '',
					'post_excerpt'   => $property['advert_internet']['heading'],
					'post_content' 	 => '',
					'post_title'     => wp_strip_all_tags( $display_address ),
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
					$inserted_post->post_title == '' && 
					($display_address != '')
				)
				{
					$my_post = array(
				    	'ID'          	 => $post_id,
				    	'post_title'     => htmlentities(mb_convert_encoding(wp_strip_all_tags( $display_address ), 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
				    	'post_excerpt'   => htmlentities(mb_convert_encoding(wp_strip_all_tags( $property['advert_internet']['heading'] ), 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
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

				$this->add_log( 'Successfully ' . $inserted_updated . ' post. The post ID is ' . $post_id, $property['id'] );

				update_post_meta( $post_id, $imported_ref_key, $property['id'] );

				// Address
				update_post_meta( $post_id, '_reference_number', $property['id'] );
				update_post_meta( $post_id, '_address_name_number', trim( ( ( isset($property['address']['unit_number']) ) ? $property['address']['unit_number'] : '' ) . ' ' . ( ( isset($property['address']['street_number']) ) ? $property['address']['street_number'] : '' ) ) );
				update_post_meta( $post_id, '_address_street', ( ( isset($property['address']['street_name']) ) ? $property['address']['street_name'] : '' ) );
				update_post_meta( $post_id, '_address_two', ( ( isset($property['address']['locality']) ) ? $property['address']['locality'] : '' ) );
				update_post_meta( $post_id, '_address_three', ( ( isset($property['address']['suburb_or_town']) ) ? $property['address']['suburb_or_town'] : '' ) );
				update_post_meta( $post_id, '_address_four', ( ( isset($property['address']['state_or_region']) ) ? $property['address']['state_or_region'] : '' ) );
				update_post_meta( $post_id, '_address_postcode', ( ( isset($property['address']['postcode']) ) ? $property['address']['postcode'] : '' ) );
				
				$country = ( ( isset($property['address']['country']) ) ? strtoupper($property['address']['country']) : get_option( 'propertyhive_default_country', 'UK' ) );
				if ( $country == 'UK' )
				{
					$country = 'GB';
				}
				update_post_meta( $post_id, '_address_country', $country );

				// Check main address fields and see if this location exists as a taxonomy to try and assign properties to location
				$address_fields_to_check = apply_filters( 'propertyhive_rex_json_address_fields_to_check', array('locality', 'suburb_or_town', 'state_or_region') );
				$location_term_ids = array();

				foreach ( $address_fields_to_check as $address_field )
				{
					if ( isset($property['address'][$address_field]) && trim($property['address'][$address_field]) != '' ) 
					{
						$term = term_exists( trim($property['address'][$address_field]), 'location');
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
				if ( isset($property['address']['latitude']) && isset($property['address']['longitude']) && $property['address']['latitude'] != '' && $property['address']['longitude'] != '' && $property['address']['latitude'] != '0' && $property['address']['longitude'] != '0' )
				{
					update_post_meta( $post_id, '_latitude', $property['address']['latitude'] );
					update_post_meta( $post_id, '_longitude', $property['address']['longitude'] );
				}
				else
				{
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
								if ( isset($property['address']['street_name']) && trim($property['address']['street_name']) != '' ) { $address_to_geocode[] = $property['address']['street_name']; }
								if ( isset($property['address']['locality']) && trim($property['address']['locality']) != '' ) { $address_to_geocode[] = $property['address']['locality']; }
								if ( isset($property['address']['suburb_or_town']) && trim($property['address']['suburb_or_town']) != '' ) { $address_to_geocode[] = $property['address']['suburb_or_town']; }
								if ( isset($property['address']['state_or_region']) && trim($property['address']['state_or_region']) != '' ) { $address_to_geocode[] = $property['address']['state_or_region']; }
								if ( isset($property['address']['postcode']) && trim($property['address']['postcode']) != '' ) { $address_to_geocode[] = $property['address']['postcode']; }
								
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
				$negotiator_id = false;
				if ( isset($property['listing_agent_1']['name']) )
				{
					$negotiator_row = $wpdb->get_row( $wpdb->prepare(
				        "SELECT `ID` FROM $wpdb->users WHERE `display_name` = %s", $property['listing_agent_1']['name']
				    ) );
				    if ( null !== $negotiator_row )
				    {
				    	$negotiator_id = $negotiator_row->ID;
				    }
				}
				if ( $negotiator_id === false )
				{
					$negotiator_id = get_current_user_id();
				}
				update_post_meta( $post_id, '_negotiator_id', $negotiator_id, true );
				
				$office_id = $primary_office_id;
				update_post_meta( $post_id, '_office_id', $office_id );

				// Residential Details
				$department = 'residential-sales';
				if ( isset($property['listing_category_id']) )
				{
					switch ( $property['listing_category_id'] )
					{
						case "residential_letting": { $department = 'residential-lettings'; break; }
					}
				}
				update_post_meta( $post_id, '_department', $department );
				
				update_post_meta( $post_id, '_bedrooms', ( ( isset($property['attributes']['bedrooms']) ) ? $property['attributes']['bedrooms'] : '' ) );
				update_post_meta( $post_id, '_bathrooms', ( ( isset($property['attributes']['bathrooms']) ) ? $property['attributes']['bathrooms'] : '' ) );
				update_post_meta( $post_id, '_reception_rooms', ( ( isset($property['attributes']['living_areas']) ) ? $property['attributes']['living_areas'] : '' ) );

				$prefix = '';
				if ( isset($_POST['mapped_' . $prefix . 'property_type']) )
				{
					$mapping = $_POST['mapped_' . $prefix . 'property_type'];
				}
				else
				{
					$mapping = isset($options['mappings'][$prefix . 'property_type']) ? $options['mappings'][$prefix . 'property_type'] : array();
				}

				wp_delete_object_term_relationships( $post_id, $prefix . 'property_type' );

				if ( isset($property['subcategories']) && is_array($property['subcategories']) && !empty($property['subcategories']) )
				{
					$term_ids = array();
					foreach ( $property['subcategories'] as $subcategory )
					{
						if ( !empty($mapping) && isset($mapping[$subcategory]) )
						{
							$term_ids[] = $mapping[$subcategory];
						}
						else
						{
							$this->add_log( 'Property received with a type (' . $subcategory . ') that is not mapped', (string)$property->propertyID );

							$options = $this->add_missing_mapping( $mapping, $prefix . 'property_type', $subcategory, $import_id );
						}
					}

					if ( !empty($term_ids) )
					{
						wp_set_post_terms( $post_id, $term_ids, $prefix . 'property_type' );
					}
				}

				// Residential Sales Details
				if ( $department == 'residential-sales' )
				{
					// Clean price
					$price = round(preg_replace("/[^0-9.]/", '', $property['price_match']));

					update_post_meta( $post_id, '_price', $price );
					update_post_meta( $post_id, '_price_actual', $price );
					update_post_meta( $post_id, '_poa', ( ( ( isset($property['state_hide_price']) && $property['state_hide_price'] == '1' ) || ( isset($property['price_advertise_as']) && $property['price_advertise_as'] == 'Price On Application' ) ) ? 'yes' : '') );
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

					$price_qualifier = '';
					$explode_price = explode("£", trim($property['price_advertise_as']));
					if ( count($explode_price) == 2 )
					{
						if ( !empty($mapping) && isset($explode_price[0]) && isset($mapping[trim($explode_price[0])]) )
						{
			                wp_set_post_terms( $post_id, $mapping[trim($explode_price[0])], 'price_qualifier' );
			            }
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
					if ( !empty($mapping) && isset($property['attributes']['tenure']) && isset($mapping[$property['attributes']['tenure']]) )
					{
			            wp_set_post_terms( $post_id, $mapping[$property['attributes']['tenure']], 'tenure' );
		            }
				}
				elseif ( $department == 'residential-lettings' )
				{
					$price = round(preg_replace("/[^0-9.]/", '', $property['price_rent']));

					update_post_meta( $post_id, '_rent', $price );

					$rent_frequency = 'pcm';
					$price_actual = $price;

					update_post_meta( $post_id, '_rent_frequency', $rent_frequency );
					update_post_meta( $post_id, '_price_actual', $price_actual );
					update_post_meta( $post_id, '_currency', 'GBP' );
					
					update_post_meta( $post_id, '_poa', ( ( isset($property['state_hide_price']) && $property['state_hide_price'] == '1' ) ? 'yes' : '') );

					$deposit = round(preg_replace("/[^0-9.]/", '', $property['price_bond']));
					update_post_meta( $post_id, '_deposit', $deposit );

            		update_post_meta( $post_id, '_available_date', ( isset($property['available_from_date']) ? $property['available_from_date'] : '' ) );
				}

				// Marketing
				update_post_meta( $post_id, '_on_market', 'yes' );
				update_post_meta( $post_id, '_featured', '' );

				// Availability
				if ( 
					( $import_id == '' && time() > PH_PROPERTYIMPORT_DEPARTMENT_AVAILABILITY_UPDATE ) ||
					( $import_id != '' && $import_id > PH_PROPERTYIMPORT_DEPARTMENT_AVAILABILITY_UPDATE )
				)
				{
					if ( isset($_POST['mapped_' . str_replace('residential-', '', $department) . '_availability']) )
					{
						$mapping = $_POST['mapped_' . str_replace('residential-', '', $department) . '_availability'];
					}
					else
					{
						$mapping = isset($options['mappings'][str_replace('residential-', '', $department) . '_availability']) ? 
							$options['mappings'][str_replace('residential-', '', $department) . '_availability'] : 
							array();
					}
				}
				else
				{
					$prefix = '';
					if ( isset($_POST['mapped_' . $prefix . 'availability']) )
					{
						$mapping = $_POST['mapped_' . $prefix . 'availability'];
					}
					else
					{
						$mapping = isset($options['mappings'][$prefix . 'availability']) ? $options['mappings'][$prefix . 'availability'] : array();
					}
				}

				$availability = isset($property['project_listing_status']) ? $property['project_listing_status'] : '';
				if ( $availability == '' || $availability === null )
				{
					$availability = 'Available';
				}

        		wp_delete_object_term_relationships( $post_id, 'availability' );
				if ( !empty($mapping) && isset($mapping[$availability]) )
				{
	                wp_set_post_terms( $post_id, $mapping[$availability], 'availability' );
	            }

	            // Features
				$features = array();
				if ( isset($property['features']) && is_array($property['features']) && !empty($property['features']) )
				{
					foreach ( $property['features'] as $feature )
					{
						$features[] = trim($feature);
					}
				}
				update_post_meta( $post_id, '_features', count( $features ) );
        		
        		$i = 0;
		        foreach ( $features as $feature )
		        {
		            update_post_meta( $post_id, '_feature_' . $i, $feature );
		            ++$i;
		        }

		        // Rooms / Descriptions
		        $num_rooms = 0;

		        if ( isset($property['advert_brochure']) && isset($property['advert_brochure']['body']) && $property['advert_brochure']['body'] != '' )
		        {
		        	update_post_meta( $post_id, '_room_name_' . $num_rooms, '' );
		            update_post_meta( $post_id, '_room_dimensions_' . $num_rooms, '' );
		            update_post_meta( $post_id, '_room_description_' . $num_rooms, str_replace(array("\r\n", "\n"), "", $property['advert_brochure']['body']) );

		            ++$num_rooms;
		        }

		        if ( isset($property['rooms']) && is_array($property['rooms']) && !empty($property['rooms']) )
		        {
		        	foreach ( $property['rooms'] as $room )
		        	{
		        		update_post_meta( $post_id, '_room_name_' . $num_rooms, $room['room_type'] );
			            update_post_meta( $post_id, '_room_dimensions_' . $num_rooms, $room['dimensions'] );
			            update_post_meta( $post_id, '_room_description_' . $num_rooms, str_replace(array("\r\n", "\n"), "", $room['description']) );

			            ++$num_rooms;
		        	}
		        }
				update_post_meta( $post_id, '_rooms', $num_rooms );

				// Media - Images
				if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();
    				if ( isset($property['images']) && is_array($property['images']) && !empty($property['images']) )
					{
						foreach ( $property['images'] as $image )
						{
							if ( 
								isset($image['url']) && $image['url'] != ''
								&&
								(
									substr( strtolower($image['url']), 0, 2 ) == '//' || 
									substr( strtolower($image['url']), 0, 4 ) == 'http'
								)
							)
							{
								// This is a URL
								$url = $image['url'];
								if ( substr($url, 0, 2) == '//' )
								{
									$url = 'https:' . $url;
								}

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

					if ( isset($property['images']) && is_array($property['images']) && !empty($property['images']) )
					{
						foreach ( $property['images'] as $image )
						{
							if ( 
								isset($image['url']) && $image['url'] != ''
								&&
								(
									substr( strtolower($image['url']), 0, 2 ) == '//' || 
									substr( strtolower($image['url']), 0, 4 ) == 'http'
								)
							)
							{
								// This is a URL
								$url = $image['url'];
								if ( substr($url, 0, 2) == '//' )
								{
									$url = 'https:' . $url;
								}
								$description = '';
								$modified = $image['modtime'];
							    
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
											&&
											(
												get_post_meta( $previous_media_id, '_modified', TRUE ) == '' 
												||
												(
													get_post_meta( $previous_media_id, '_modified', TRUE ) != '' &&
													get_post_meta( $previous_media_id, '_modified', TRUE ) == $modified
												)
											)
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

									if ( $description != '' )
									{
										$my_post = array(
									    	'ID'          	 => $imported_previously_id,
									    	'post_title'     => $description,
									    );

									 	// Update the post into the database
									    wp_update_post( $my_post );
									}

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
									    $id = media_handle_sideload( $file_array, $post_id, $description );

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
									    	update_post_meta( $id, '_modified', $modified);

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

					$this->add_log( 'Imported ' . count($media_ids) . ' photos (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property['id'] );
				}

				// Media - Floorplans
				if ( get_option('propertyhive_floorplans_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();
    				if ( isset($property['floorplans']) && is_array($property['floorplans']) && !empty($property['floorplans']) )
					{
						foreach ( $property['floorplans'] as $image )
						{
							if ( 
								isset($image['url']) && $image['url'] != ''
								&&
								(
									substr( strtolower($image['url']), 0, 2 ) == '//' || 
									substr( strtolower($image['url']), 0, 4 ) == 'http'
								)
							)
							{
								// This is a URL
								$url = $image['url'];
								if ( substr($url, 0, 2) == '//' )
								{
									$url = 'https:' . $url;
								}

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

					if ( isset($property['floorplans']) && is_array($property['floorplans']) && !empty($property['floorplans']) )
					{
						foreach ( $property['floorplans'] as $image )
						{
							if ( 
								isset($image['url']) && $image['url'] != ''
								&&
								(
									substr( strtolower($image['url']), 0, 2 ) == '//' || 
									substr( strtolower($image['url']), 0, 4 ) == 'http'
								)
							)
							{
								// This is a URL
								$url = $image['url'];
								if ( substr($url, 0, 2) == '//' )
								{
									$url = 'https:' . $url;
								}
								$description = '';
								$modified = $image['modtime'];
							    
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
											&&
											(
												get_post_meta( $previous_media_id, '_modified', TRUE ) == '' 
												||
												(
													get_post_meta( $previous_media_id, '_modified', TRUE ) != '' &&
													get_post_meta( $previous_media_id, '_modified', TRUE ) == $modified
												)
											)
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

									if ( $description != '' )
									{
										$my_post = array(
									    	'ID'          	 => $imported_previously_id,
									    	'post_title'     => $description,
									    );

									 	// Update the post into the database
									    wp_update_post( $my_post );
									}

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
									    $id = media_handle_sideload( $file_array, $post_id, $description );

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
									    	update_post_meta( $id, '_modified', $modified);

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

					$this->add_log( 'Imported ' . count($media_ids) . ' floorplans (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property['id'] );
				}

				// Media - Brochures
				if ( get_option('propertyhive_brochures_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();
    				if ( isset($property['documents']) && is_array($property['documents']) && !empty($property['documents']) )
					{
						foreach ( $property['documents'] as $image )
						{
							if ( 
								isset($image['url']) && $image['url'] != ''
								&&
								(
									substr( strtolower($image['url']), 0, 2 ) == '//' || 
									substr( strtolower($image['url']), 0, 4 ) == 'http'
								)
							)
							{
								// This is a URL
								$url = $image['url'];
								if ( substr($url, 0, 2) == '//' )
								{
									$url = 'https:' . $url;
								}

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

					if ( isset($property['documents']) && is_array($property['documents']) && !empty($property['documents']) )
					{
						foreach ( $property['documents'] as $image )
						{
							if ( 
								isset($image['url']) && $image['url'] != ''
								&&
								(
									substr( strtolower($image['url']), 0, 2 ) == '//' || 
									substr( strtolower($image['url']), 0, 4 ) == 'http'
								)
							)
							{
								// This is a URL
								$url = $image['url'];
								if ( substr($url, 0, 2) == '//' )
								{
									$url = 'https:' . $url;
								}
								$description = '';
								$modified = '';
							    
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
											&&
											(
												get_post_meta( $previous_media_id, '_modified', TRUE ) == '' 
												||
												(
													get_post_meta( $previous_media_id, '_modified', TRUE ) != '' &&
													get_post_meta( $previous_media_id, '_modified', TRUE ) == $modified
												)
											)
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

									if ( $description != '' )
									{
										$my_post = array(
									    	'ID'          	 => $imported_previously_id,
									    	'post_title'     => $description,
									    );

									 	// Update the post into the database
									    wp_update_post( $my_post );
									}

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
									    $id = media_handle_sideload( $file_array, $post_id, $description );

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
									    	update_post_meta( $id, '_modified', $modified);

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

					$this->add_log( 'Imported ' . count($media_ids) . ' brochures (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property['id'] );
				}

				// Media - Virtual Tours
				$virtual_tours = array();
				if ( isset($property['links']) && is_array($property['links']) && !empty($property['links']) )
				{
					foreach ( $property['links'] as $image )
					{
						if ( 
							isset($image['link_url']) && $image['link_url'] != ''
							&&
							(
								substr( strtolower($image['link_url']), 0, 2 ) == '//' || 
								substr( strtolower($image['link_url']), 0, 4 ) == 'http'
							)
							&&
							isset($image['link_type']) && ( $image['link_type'] == 'virtual_tour' || $image['link_type'] == 'video_link' )
						)
						{
							// This is a URL
							$url = $image['link_url'];

							$virtual_tours[] = $url;
						}
					}
				}

                update_post_meta( $post_id, '_virtual_tours', count($virtual_tours) );
                foreach ( $virtual_tours as $i => $virtual_tour )
                {
                	update_post_meta( $post_id, '_virtual_tour_' . $i, (string)$virtual_tour );
                }

				$this->add_log( 'Imported ' . count($virtual_tours) . ' virtual tours', $property['id'] );

				do_action( "propertyhive_property_imported_rex_json", $post_id, $property );

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
				$property_row == $options['chunk_qty']
			)
			{
				$this->add_log( 'Pausing for ' . $options['chunk_delay'] . ' seconds' );
				sleep($options['chunk_delay']);
			}
			++$property_row;

		} // end foreach property

		do_action( "propertyhive_post_import_properties_rex_json" );

		$this->import_end();

		$this->add_log( 'Finished import' );
	}

	public function remove_old_properties( $import_id = '', $do_remove = true )
	{
		global $wpdb, $post;

		if ( !empty($this->properties) )
		{
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

					do_action( "propertyhive_property_removed_rex_json", $post->ID );
				}
			}
			wp_reset_postdata();

			unset($import_refs);
		}
	}

	public function get_mappings( $import_id = '' )
	{
		if ( !empty($this->mappings) )
		{
			return $this->mappings;
		}

		// Build mappings
		if ( 
			( $import_id == '' && time() > PH_PROPERTYIMPORT_DEPARTMENT_AVAILABILITY_UPDATE ) || 
			( $import_id != '' && $import_id > PH_PROPERTYIMPORT_DEPARTMENT_AVAILABILITY_UPDATE )
		)
		{
			if ( get_option( 'propertyhive_active_departments_sales' ) == 'yes' )
			{
				$mapping_values = $this->get_rex_mapping_values('sales_availability');
				if ( is_array($mapping_values) && !empty($mapping_values) )
				{
					foreach ($mapping_values as $mapping_value => $text_value)
					{
						$this->mappings['sales_availability'][$mapping_value] = '';
					}
				}
			}

			if ( get_option( 'propertyhive_active_departments_lettings' ) == 'yes' )
			{
				$mapping_values = $this->get_rex_mapping_values('lettings_availability');
				if ( is_array($mapping_values) && !empty($mapping_values) )
				{
					foreach ($mapping_values as $mapping_value => $text_value)
					{
						$this->mappings['lettings_availability'][$mapping_value] = '';
					}
				}
			}

			/*if ( get_option( 'propertyhive_active_departments_commercial' ) == 'yes' )
			{
				$mapping_values = $this->get_rex_mapping_values('commercial_availability');
				if ( is_array($mapping_values) && !empty($mapping_values) )
				{
					foreach ($mapping_values as $mapping_value => $text_value)
					{
						$this->mappings['commercial_availability'][$mapping_value] = '';
					}
				}
			}*/
		}
		else
		{
			$mapping_values = $this->get_rex_mapping_values('availability');
			if ( is_array($mapping_values) && !empty($mapping_values) )
			{
				foreach ($mapping_values as $mapping_value => $text_value)
				{
					$this->mappings['availability'][$mapping_value] = '';
				}
			}
		}

		$mapping_values = $this->get_rex_mapping_values('property_type');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['property_type'][$mapping_value] = '';
			}
		}

		$mapping_values = $this->get_rex_mapping_values('price_qualifier');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['price_qualifier'][$mapping_value] = '';
			}
		}

		$mapping_values = $this->get_rex_mapping_values('tenure');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['tenure'][$mapping_value] = '';
			}
		}

		$mapping_values = $this->get_rex_mapping_values('office');
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
		return $this->get_rex_mapping_values($custom_field);
	}

	public function get_rex_mapping_values($custom_field) 
	{
        if ($custom_field == 'availability')
        {
            return array(
            	'Available' => 'Available',
            	'Under Offer' => 'Under Offer',
            	'Exchanged' => 'Exchanged',
            	'Completed' => 'Completed',
            );
        }
        if ($custom_field == 'sales_availability')
        {
            return array(
            	'Available' => 'Available',
            	'Under Offer' => 'Under Offer',
            	'Exchanged' => 'Exchanged',
            	'Completed' => 'Completed',
            );
        }
        if ($custom_field == 'lettings_availability')
        {
            return array(
            	'Available' => 'Available',
            	'Under Offer' => 'Under Offer',
            );
        }
        if ($custom_field == 'property_type')
        {
        	return array(
                'Apartment' => 'Apartment',
                'Barn Conversion' => 'Barn Conversion',
                'Block of Flats' => 'Block of Flats',
                'Bungalow' => 'Bungalow',
                'Chalet' => 'Chalet',
                'Coach House' => 'Coach House',
                'Country House' => 'Country House',
                'Cottage' => 'Cottage',
                'Detached bungalow' => 'Detached bungalow',
                'Detached house' => 'Detached house',
                'End of terrace house' => 'End of terrace house',
                'Finca' => 'Finca',
                'Flat' => 'Flat',
                'House Boat' => 'House Boat',
                'Link detached house' => 'Link detached house',
                'Lodge' => 'Lodge',
                'Longere' => 'Longere',
                'Maisonette' => 'Maisonette',
                'Mews house' => 'Mews house',
                'Park home' => 'Park home',
                'Riad' => 'Riad',
                'Semi-detached bungalow' => 'Semi-detached bungalow',
                'Semi-detached house' => 'Semi-detached house',
                'Studio' => 'Studio',
                'Terraced bungalow' => 'Terraced bungalow',
                'Terraced House' => 'Terraced House',
                'Town House' => 'Town House',
                'Villa' => 'Villa',
            );
        }
        if ($custom_field == 'price_qualifier')
        {
        	return array(
                'Fixed Price' => 'Fixed Price',
                'Guide Price' => 'Guide Price',
                'Offers Over' => 'Offers Over',
                'Offers In The Region Of' => 'Offers In The Region Of',
            );
        }
        if ($custom_field == 'tenure')
        {
            return array(
            	'Freehold' => 'Freehold',
            	'Leasehold' => 'Leasehold',
            );
        }
    }
}

}