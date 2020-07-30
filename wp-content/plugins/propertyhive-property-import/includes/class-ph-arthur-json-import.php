<?php
/**
 * Class for managing the import process of a Arthur JSON file
 *
 * @package WordPress
 */
if ( class_exists( 'PH_Property_Import_Process' ) ) {

class PH_Arthur_JSON_Import extends PH_Property_Import_Process {

	public function __construct( $instance_id = '' ) 
	{
		$this->instance_id = $instance_id;

		if ( $this->instance_id != '' && isset($_GET['custom_property_import_cron']) )
	    {
	    	$current_user = wp_get_current_user();

	    	$this->add_log("Executed manually by " . ( ( isset($current_user->display_name) ) ? $current_user->display_name : '' ) );
	    }
	}

	public function get_access_token_from_authorization_code($code, $import_id)
	{
		$options = get_option( 'propertyhive_property_import' );
        if ( is_array($options) && !empty($options) )
        {
            $client_id = '';
            $client_secret = '';

            $import_id_in_question = 

            $option = $options[$import_id];

            if ( $option['format'] == 'json_arthur' )
            {
                $client_id = $option['client_id'];
                $client_secret = $option['client_secret'];
            }
            else
            {
            	die("Trying to work with a non-Arthur import");
            }

            // we got a code, now use this to get an access token
            $response = wp_remote_post( 
                'https://auth.arthuronline.co.uk/oauth/token', 
                array(
                    'method' => 'POST',
                    'headers' => array(),
                    'body' => array( 
                        'grant_type' => 'authorization_code', 
                        'code' => $code,
                        'client_id' => $client_id, 
                        'client_secret' => $client_secret,
                        'redirect_uri' => admin_url('admin.php?page=propertyhive_import_properties&arthur_callback=1&import_id=' . $import_id),
                        'state' => uniqid(),
                    ),
                    'cookies' => array()
                )
            );

            if ( !is_wp_error( $response ) && isset($response['body']) ) 
            {
                $response = json_decode($response['body'], TRUE);

                if ( isset($response['access_token']) )
                {
	                $option['access_token'] = $response['access_token'];
	                $option['access_token_expires'] = time() + $response['expires_in'];
	                $option['refresh_token'] = $response['refresh_token'];

	                $options[$import_id] = $option;
	                update_option('propertyhive_property_import', $options );

	                return true;
	            }
	            else 
	            {
	                die( 'No access token in response: ' . print_r($response, TRUE) );
	            }
            } 
            else 
            {
                die( 'Something went wrong getting the access token: ' . print_r($response, TRUE) );
            }
        }

        return false;
	}

	public function refresh_access_token($option, $import_id)
	{
		$options = get_option( 'propertyhive_property_import' );
        if ( is_array($options) && !empty($options) )
        {
            if ( $option['format'] == 'json_arthur' )
            {
                
            }
            else
            {
            	die("Trying to work with a non-Arthur import");
            }

	        // we got a code, now use this to get an access token
	        $response = wp_remote_post( 
	            'https://auth.arthuronline.co.uk/oauth/token', 
	            array(
	                'method' => 'POST',
	                'headers' => array(),
	                'body' => array( 
	                    'grant_type' => 'refresh_token', 
	                    'refresh_token' => $option['refresh_token'],
	                    'client_id' => $option['client_id'],
	                    'client_secret' => $option['client_secret'],
	                    'state' => uniqid(),
	                ),
	                'cookies' => array()
	            )
	        );

	        if ( !is_wp_error( $response ) && isset($response['body']) ) 
	        {
	            $response = json_decode($response['body'], TRUE);

	            if ( isset($response['access_token']) )
	            {
	                $option['access_token'] = $response['access_token'];
	                $option['access_token_expires'] = time() + $response['expires_in'];
	                $option['refresh_token'] = $response['refresh_token'];

	                $options[$import_id] = $option;
	                update_option('propertyhive_property_import', $options );

	                return true;
	            }
	            else 
	            {
	                // It's possible the refresh token has expired. Try and get access token again from scratch
	                $this->add_error( 'No access token in response. ' . print_r($response, TRUE) );
	            }
	        } 
	        else 
	        {
	            $this->add_error( 'Something went wrong getting the access token from refresh token: ' . print_r($response->get_error_message(), TRUE) );
	        }
	    }

        return false;
	}

	public function parse( $import_id )
	{
		$this->add_log("Parsing properties");

		$options = get_option( 'propertyhive_property_import' );
        if ( is_array($options) && !empty($options) && isset($options[$import_id]) )
        {
        	$option = $options[$import_id];

        	// Check that access token hasn't expired...
        	if ( $option['access_token_expires'] < time() )
        	{	
        		$this->add_log("Access token has expired. Trying to get a new one using refresh token");
        		$got_access_token = $this->refresh_access_token($option, $import_id);
        		if ( $got_access_token === false )
        		{
        			$this->add_error("Failed to get new access token");
        			return false;
        		}

        		$this->add_error("Got new access token");

        		$options = get_option( 'propertyhive_property_import' );
        		$option = $options[$import_id];
        	}

        	$import_structure = isset($option['import_structure']) ? $option['import_structure'] : '';

        	$total_pages = 999;
        	$page = 1;

        	while ( $page <= $total_pages )
        	{
				$response = wp_remote_get( 
					apply_filters( 'propertyhive_arthur_properties_url', 'https://api.arthuronline.co.uk/v2/properties?unit_status=Available%20to%20Let,Under%20Offer&limit=100&page=' . $page ),
					array(
		                'headers' => array(
		                	'Authorization' => 'Bearer ' . $option['access_token'],
		                	'X-EntityID' => $option['entity_id']
		                ),
		                'body' => array(),
		                'cookies' => array(),
		                'timeout' => 30,
		            )
				);

				if ( !is_wp_error( $response ) && is_array( $response ) && isset($response['body']) ) 
				{
					$header = $response['headers']; // array of http header lines
					$body = $response['body']; // use the content
				
					$json = json_decode( $body, TRUE );

					if ($json !== FALSE && is_array($json['data']) && !empty($json['data']))
					{
						if ( $total_pages == 999 && isset($json['pagination']['pageCount']) )
						{
							$total_pages = $json['pagination']['pageCount'];
						}

						$this->add_log("Found " . count($json['data']) . " properties on page " . $page . " / " . $total_pages . " in JSON ready for parsing");

						foreach ($json['data'] as $property)
						{
							$property['units'] = array();

							if ( 
								class_exists( 'PH_Rooms' ) ||
								( !class_exists( 'PH_Rooms' ) && $import_structure != '' && $import_structure != 'top_level_only' )
							)
							{
								$response = wp_remote_get( 
									apply_filters( 'propertyhive_arthur_units_url', 'https://api.arthuronline.co.uk/v2/properties/' . $property['id'] . '/units?unit_status=Available%20to%20Let,Under%20Offer&limit=100', $property['id'] ),
									array(
						                'headers' => array(
						                	'Authorization' => 'Bearer ' . $option['access_token'],
						                	'X-EntityID' => $option['entity_id']
						                ),
						                'body' => array(),
						                'cookies' => array(),
		                				'timeout' => 30,
						            )
								);

								if ( !is_wp_error( $response ) && is_array( $response ) && isset($response['body']) ) 
								{
									$header = $response['headers']; // array of http header lines
									$body = $response['body']; // use the content

									$property_unit_json = json_decode( $body, TRUE );

									if ($property_json !== FALSE && isset($property_unit_json['data']) && is_array($property_unit_json['data']) )
									{
										if ( !empty($property_unit_json['data']) )
										{
											$property['units'] = $property_unit_json['data'];
										}
									}
									else
									{
										// Failed to parse JSON
							        	$this->add_error( 'Failed to parse units JSON file for property with ID ' . $property['id'] . '. Possibly invalid JSON' );
							        	return false;
									}
								}
							}

							$this->properties[] = $property;
						}
			        }
			        else
			        {
			        	// Failed to parse JSON
			        	$this->add_error( 'Failed to parse JSON file. Possibly invalid JSON' );
			        	return false;
			        }
		        }
		        else
		        {
		        	// Request failed
		        	$this->add_error( 'Request failed. Response: ' . print_r($response, TRUE) );
		        	return false;
		        }

		        ++$page;
		    }

	        return true;
	    }
	    else
	    {
	    	return false;
	    }
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

		$import_structure = isset($options['import_structure']) ? $options['import_structure'] : '';

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

        do_action( "propertyhive_pre_import_properties_arthur_json", $this->properties );
        $this->properties = apply_filters( "propertyhive_arthur_json_properties_due_import", $this->properties );

		$this->add_log( 'Beginning to loop through ' . count($this->properties) . ' properties' );

		$property_row = 1;
		foreach ( $this->properties as $property )
		{
			$this->add_log( 'Importing property ' . $property_row . ' with reference ' . $property['id'], $property['id'] );

			$inserted_updated = false;

			$display_address = $property['address_line_2'];
	        if ( isset($property['city']) && $property['city'] != '' )
	        {
	        	if ( $display_address != '' )
	        	{
	        		$display_address .= ', ';
	        	}
	        	$display_address .= $property['city'];
	        }

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
				    	'post_title'     => wp_strip_all_tags( $display_address ),
				    	'post_excerpt'   => '',
				    	'post_content' 	 => '',
				    	'post_status'    => $import_structure == 'units_only' ? 'private' : 'publish',
				    	'post_date' 	 => date("Y-m-d H:i:s", strtotime($unit['created'])),
						'post_modified'  => date("Y-m-d H:i:s", strtotime($unit['modified'])),
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
					'post_excerpt'   => '',
					'post_content' 	 => '',
					'post_title'     => wp_strip_all_tags( $display_address ),
					'post_status'    => $import_structure == 'units_only' ? 'private' : 'publish',
					'post_type'      => 'property',
					'comment_status' => 'closed',
					'post_date' 	 => date("Y-m-d H:i:s", strtotime($unit['created'])),
					'post_modified'  => date("Y-m-d H:i:s", strtotime($unit['modified'])),
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
				    	'post_excerpt'   => '',
				    	'post_content' 	 => '',
				    	'post_name' 	 => sanitize_title($display_address),
				    	'post_status'    => 'publish',
				    	'post_date' 	 => date("Y-m-d H:i:s", strtotime($unit['created'])),
						'post_modified'  => date("Y-m-d H:i:s", strtotime($unit['modified'])),
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
				update_post_meta( $post_id, '_reference_number', ( ( isset($property['id']) ) ? $property['id'] : '' ) );
				update_post_meta( $post_id, '_address_name_number', '' );
				update_post_meta( $post_id, '_address_street', ( ( isset($property['address_line_1']) ) ? $property['address_line_1'] : '' ) );
				update_post_meta( $post_id, '_address_two', ( ( isset($property['address_line_2']) ) ? $property['address_line_2'] : '' ) );
				update_post_meta( $post_id, '_address_three', ( ( isset($property['city']) ) ? $property['city'] : '' ) );
				update_post_meta( $post_id, '_address_four', ( ( isset($property['county']) ) ? $property['county'] : '' ) );
				update_post_meta( $post_id, '_address_postcode', ( ( isset($property['postcode']) ) ? $property['postcode'] : '' ) );

				$country = get_option( 'propertyhive_default_country', 'GB' );
				update_post_meta( $post_id, '_address_country', $country );

				// Check main address fields and see if this location exists as a taxonomy to try and assign properties to location
				$address_fields_to_check = apply_filters( 'propertyhive_arthur_json_address_fields_to_check', array('address_line_2', 'city', 'county') );
				$location_term_ids = array();

				foreach ( $address_fields_to_check as $address_field )
				{
					if ( isset($property[$address_field]) && trim($property[$address_field]) != '' ) 
					{
						$term = term_exists( trim($property[$address_field]), 'location');
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
				if ( isset($property['latitude']) && isset($property['longitude']) && $property['latitude'] != '' && $property['longitude'] != '' && $property['latitude'] != '0' && $property['longitude'] != '0' )
				{
					update_post_meta( $post_id, '_latitude', $property['latitude'] );
					update_post_meta( $post_id, '_longitude', $property['longitude'] );
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
								if ( isset($property['address_line_2']) && trim($property['address_line_2']) != '' ) { $address_to_geocode[] = $property['address_line_2']; }
								if ( isset($property['city']) && trim($property['city']) != '' ) { $address_to_geocode[] = $property['city']; }
								if ( isset($property['county']) && trim($property['county']) != '' ) { $address_to_geocode[] = $property['county']; }
								if ( isset($property['postcode']) && trim($property['postcode']) != '' ) { $address_to_geocode[] = $property['postcode']; }
								
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
				/*if ( isset($_POST['mapped_office'][$property['BranchDetails']['Name']]) && $_POST['mapped_office'][$property['BranchDetails']['Name']] != '' )
				{
					$office_id = $_POST['mapped_office'][$property['BranchDetails']['Name']];
				}
				elseif ( isset($options['offices']) && is_array($options['offices']) && !empty($options['offices']) )
				{
					foreach ( $options['offices'] as $ph_office_id => $branch_code )
					{
						if ( $branch_code == $property['BranchDetails']['Name'] )
						{
							$office_id = $ph_office_id;
							break;
						}
					}
				}*/
				update_post_meta( $post_id, '_office_id', $office_id );

				// Residential Details
				$department = 'residential-lettings';
				if ( 
					class_exists('PH_Rooms') &&
					get_option( 'propertyhive_active_departments_rooms' ) == 'yes' &&
					$import_structure == ''
				)
				{
					$department = 'rooms';
				}
				update_post_meta( $post_id, '_department', $department );
				
				update_post_meta( $post_id, '_bedrooms', ( ( isset($property['bedrooms']) && is_numeric($property['bedrooms']) ) ? $property['bedrooms'] : '' ) );
				update_post_meta( $post_id, '_bathrooms', ( ( isset($property['bathrooms']) && is_numeric($property['bathrooms']) ) ? $property['bathrooms'] : '' ) );
				update_post_meta( $post_id, '_reception_rooms', ( ( isset($property['receptions']) && is_numeric($property['receptions']) ) ? $property['receptions'] : '' ) );

				/*$prefix = '';
				if ( isset($_POST['mapped_' . $prefix . 'property_type']) )
				{
					$mapping = $_POST['mapped_' . $prefix . 'property_type'];
				}
				else
				{
					$mapping = isset($options['mappings'][$prefix . 'property_type']) ? $options['mappings'][$prefix . 'property_type'] : array();
				}

				wp_delete_object_term_relationships( $post_id, $prefix . 'property_type' );

				if ( isset($property['details']['propertyType']) )
				{
					if ( !empty($mapping) && isset($mapping[$property['type_name']]) )
					{
						wp_set_post_terms( $post_id, $mapping[$property['type_name']], $prefix . 'property_type' );
					}
					else
					{
						$this->add_log( 'Property received with a type (' . $property['type_name'] . ') that is not mapped', $property['id'] );
					}
				}*/
				
				if ( $department == 'residential-lettings' || $department == 'rooms' )
				{
					// Clean price
					$price = round(preg_replace("/[^0-9.]/", '', $property['portal_market_rent']));

					update_post_meta( $post_id, '_rent', $price );

					$rent_frequency = 'pw';
					$price_actual = $price_actual = ($price * 52) / 12;;
					switch ($property['portal_market_rent_frequency'])
					{
						case "Monthly": { $rent_frequency = 'pcm'; $price_actual = $price; break; }
					}
					update_post_meta( $post_id, '_rent_frequency', $rent_frequency );
					update_post_meta( $post_id, '_price_actual', $price_actual );
					update_post_meta( $post_id, '_currency', 'GBP' );
					
					//update_post_meta( $post_id, '_poa', ( ( isset($property['qualifier_name']) && strtolower($property['qualifier_name']) == 'poa' ) ? 'yes' : '') );

					update_post_meta( $post_id, '_deposit', $property['deposit_amount'] );
            		update_post_meta( $post_id, '_available_date', ( ( isset($property['date_available']) && $property['date_available'] != '' ) ? $property['date_available'] : '' ) );

            		// Furnished - not provided in XML
            		/*if ( isset($_POST['mapped_furnished']) )
					{
						$mapping = $_POST['mapped_furnished'];
					}
					else
					{
						$mapping = isset($options['mappings']['furnished']) ? $options['mappings']['furnished'] : array();
					}

            		wp_delete_object_term_relationships( $post_id, 'furnished' );
					if ( !empty($mapping) && isset($property['let_furn_id']) && isset($mapping[$property['let_furn_id']]) )
					{
		                wp_set_post_terms( $post_id, $mapping[$property['let_furn_id']], 'furnished' );
		            }*/
				}

				// Marketing
				update_post_meta( $post_id, '_on_market', 'yes' );
				/*update_post_meta( $post_id, '_featured', ( (isset($property['featured']) && $property['featured'] == '1') ? 'yes' : '' ) );

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
				if ( !empty($mapping) && isset($property['status']) && isset($mapping[$property['status']]) )
				{
	                wp_set_post_terms( $post_id, $mapping[$property['status']], 'availability' );
	            }*/

	            // Features
	            if ( isset($property['features']) && is_array($property['features']) && !empty($property['features']) )
	            {
	        		$i = 0;
			        foreach ( $property['features'] as $feature )
			        {
			            update_post_meta( $post_id, '_feature_' . $i, $feature );
			            ++$i;
			        }
			        update_post_meta( $post_id, '_features', $i );
			    }

		        // Rooms / Descriptions
		        // For now put the whole description in one room / description
				/*update_post_meta( $post_id, '_rooms', '1' );
				update_post_meta( $post_id, '_room_name_0', '' );
	            update_post_meta( $post_id, '_room_dimensions_0', '' );
	            update_post_meta( $post_id, '_room_description_0', str_replace(array("\r\n", "\n"), "", $property['description']) );*/
				
				// Media - Images
				if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();
    				if ( isset($property['image_urls']) && is_array($property['image_urls']) && !empty($property['image_urls']) )
					{
						foreach ( $property['image_urls'] as $url )
						{
							$media_urls[] = array('url' => $url);
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

					if ( isset($property['image_urls']) && is_array($property['image_urls']) && !empty($property['image_urls']) )
					{
						foreach ( $property['image_urls'] as $url )
						{
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
							        'name' => $filename . '.jpg',
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

								    	++$new;
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
    				if ( isset($property['floor_plan_urls']) && is_array($property['floor_plan_urls']) && !empty($property['floor_plan_urls']) )
					{
						foreach ( $property['floor_plan_urls'] as $url )
						{
							$media_urls[] = array('url' => $url);
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

					if ( isset($property['floor_plan_urls']) && is_array($property['floor_plan_urls']) && !empty($property['floor_plan_urls']) )
					{
						foreach ( $property['floor_plan_urls'] as $url )
						{
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
							        'name' => $filename . '.jpg',
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

								    	++$new;
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

				// Media - EPCs
				if ( get_option('propertyhive_epcs_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();
    				if ( isset($property['epc_urls']) && is_array($property['epc_urls']) && !empty($property['epc_urls']) )
					{
						foreach ( $property['epc_urls'] as $url )
						{
							$media_urls[] = array('url' => $url);
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

					if ( isset($property['epc_urls']) && is_array($property['epc_urls']) && !empty($property['epc_urls']) )
					{
						foreach ( $property['epc_urls'] as $url )
						{
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
							        'name' => $filename . '.jpg',
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

								    	++$new;
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

					$this->add_log( 'Imported ' . count($media_ids) . ' EPCs (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property['id'] );
				}

				if (
					isset($property['units']) && is_array($property['units']) && !empty($property['units']) 
				)
				{
					$num_units = count($property['units']);
					foreach ( $property['units'] as $unit_i => $unit )
					{
						$inserted_updated_unit = 'updated';

						$this->add_log( 'Importing unit ' . ($unit_i + 1) . ' of ' . $num_units . ' with reference ' . $unit['id'], $property['id'] . '-' . $unit['id'] );

						$args = array(
				            'post_type' => 'property',
				            'posts_per_page' => 1,
				            'post_status' => 'any',
				            'suppress_filters' => TRUE,
				            'meta_query' => array(
				            	array(
					            	'key' => $imported_ref_key,
					            	'value' => $property['id'] . '-' . $unit['id']
					            )
				            )
				        );
				        $unit_query = new WP_Query($args);

				        if ($unit_query->have_posts())
				        {
				        	$this->add_log( 'This property unit ' . $unit['id'] . ' has been imported before. Updating it', $property['id'] . '-' . $unit['id'] );

				        	// We've imported this property before
				            while ($unit_query->have_posts())
				            {
				                $unit_query->the_post();

				                $unit_post_id = get_the_ID();

				                $my_post = array(
							    	'ID'          	 => $unit_post_id,
							    	'post_title'     => wp_strip_all_tags( $unit['unit_ref'] . ' - ' . $display_address ),
							    	'post_excerpt'   => ( ( isset($unit['short_description']) && $unit['short_description'] != '' ) ? $unit['short_description'] : '' ),
							    	'post_content' 	 => ( ( isset($unit['description']) && $unit['description'] != '' ) ? $unit['description'] : ( ( isset($unit['short_description']) && $unit['short_description'] != '' ) ? $unit['short_description'] : '' ) ),
							    	'post_status'    => 'publish',
							    	'post_date' 	 => date("Y-m-d H:i:s", strtotime($unit['created'])),
							    	'post_modified'  => date("Y-m-d H:i:s", strtotime($unit['modified'])),
							  	);
							  	//if ( $import_structure == '' )
						        //{
						        	$my_post['post_parent'] = $post_id;
						        //}

							 	// Update the post into the database
							    $unit_post_id = wp_update_post( $my_post );

							    if ( is_wp_error( $unit_post_id ) ) 
								{
									$this->add_error( 'Failed to update post for unit ID ' . $unit['id'] . '. The error was as follows: ' . $unit_post_id->get_error_message(), $property['id'] . '-' . $unit['id'] );
								}
								else
								{
									$inserted_updated_unit = 'updated';
								}
				            }
				        }
				        else
				        {
				        	$this->add_log( 'This property unit ' . $unit['id'] . ' hasn\'t been imported before. Inserting it', $property['id'] . '-' . $unit['id'] );

				        	// We've not imported this property before
							$postdata = array(
								'post_excerpt'   => ( ( isset($unit['short_description']) && $unit['short_description'] != '' ) ? $unit['short_description'] : '' ),
								'post_content' 	 => ( ( isset($unit['description']) && $unit['description'] != '' ) ? $unit['description'] : ( ( isset($unit['short_description']) && $unit['short_description'] != '' ) ? $unit['short_description'] : '' ) ),
								'post_title'     => wp_strip_all_tags( $unit['unit_ref'] . ' - ' . $display_address ),
								'post_status'    => 'publish',
								'post_type'      => 'property',
								'comment_status' => 'closed',
								'post_date' 	 => date("Y-m-d H:i:s", strtotime($unit['created'])),
							    'post_modified'  => date("Y-m-d H:i:s", strtotime($unit['modified'])),
							);
							//if ( $import_structure == '' )
					        //{
					        	$postdata['post_parent'] = $post_id;
					        //}

							$unit_post_id = wp_insert_post( $postdata, true );

							if ( is_wp_error( $unit_post_id ) ) 
							{
								$this->add_error( 'Failed to insert post for unit ID ' . $unit['id'] . '. The error was as follows: ' . $unit_post_id->get_error_message(), $property['id'] . '-' . $unit['id'] );
							}
							else
							{
								$inserted_updated_unit = 'inserted';
							}
						}
						$property_query->reset_postdata();

						if ( $inserted_updated_unit !== false )
						{
							$this->add_log( 'Successfully ' . $inserted_updated . ' unit. The post ID is ' . $unit_post_id, $property['id'] . '-' . $unit['id'] );
							
							if ( $inserted_updated_unit == 'updated' )
							{
								// Get all meta data so we can compare before and after to see what's changed
								$unit_metadata_before = get_metadata('post', $unit_post_id, '', true);

								// Get all taxonomy/term data
								$unit_taxonomy_terms_before = array();
								$taxonomy_names = get_post_taxonomies( $unit_post_id );
								foreach ( $taxonomy_names as $taxonomy_name )
								{
									$unit_taxonomy_terms_before[$taxonomy_name] = wp_get_post_terms( $unit_post_id, $taxonomy_name, array('fields' => 'ids') );
								}
							}

							// Get all post meta and taxonomies for parent property and copy to this unit
							$parent_metadata = get_metadata('post', $post_id, '', true);
							foreach ( $parent_metadata as $key => $value)
							{
								if ( 
									in_array(
										$key, 
										array(
											'_address_name_number',
											'_address_street',
											'_address_two',
											'_address_three',
											'_address_four',
											'_address_postcode',
											'_address_country',
											'_latitude',
											'_longitude',
											'_owner_contact_id',
											'_negotiator_id',
											'_office_id',
										)
									) 
								)
								{
									$value = $value[0];
									if ( $key == '_address_name_number' )
									{
										$value = trim( $unit['unit_ref'] . ( $value != '' ? ' - ' . $value : '' ) );
									}
									update_post_meta( $unit_post_id, $key, $value );
								}
							}

							// Get all taxonomy/term data
							/*$parent_taxonomy_terms = array();
							$taxonomy_names = get_post_taxonomies( $post_id );
							foreach ( $taxonomy_names as $taxonomy_name )
							{
								$parent_taxonomy_terms = wp_get_post_terms( $post_id, $taxonomy_name, array('fields' => 'ids') );
								if ( !empty($parent_taxonomy_terms) )
								{
									wp_set_post_terms( $unit_post_id, $parent_taxonomy_terms, $taxonomy_name );
								}
							}*/

							update_post_meta( $unit_post_id, $imported_ref_key, $unit['property_id'] . '-' . $unit['id'] );

							update_post_meta( $unit_post_id, '_bedrooms', ( ( isset($property['bedrooms']) && is_numeric($unit['bedrooms']) ) ? $unit['bedrooms'] : '' ) );
							update_post_meta( $unit_post_id, '_bathrooms', ( ( isset($property['bathrooms']) && is_numeric($unit['bathrooms']) ) ? $unit['bathrooms'] : '' ) );
							update_post_meta( $unit_post_id, '_reception_rooms', ( ( isset($property['receptions']) && is_numeric($unit['receptions']) ) ? $unit['receptions'] : '' ) );

							if ( isset($unit['portal_unit_type']) && $unit['portal_unit_type'] != '' )
			                {
			                	// Availability
								if ( isset($_POST['mapped_property_type']) )
								{
									$mapping = $_POST['mapped_property_type'];
								}
								else
								{
									$mapping = isset($options['mappings']['property_type']) ? $options['mappings']['property_type'] : array();
								}

			                	if ( !empty($mapping) && isset($mapping[$unit['portal_unit_type']]) )
								{
									wp_set_post_terms( $unit_post_id, $mapping[$unit['portal_unit_type']], 'property_type' );
								}
								else
								{
									$this->add_log( 'Unit with ID ' . $unit['id'] . ' received with a property type (' . $unit['portal_unit_type'] . ') that is not mapped', $property['id'] . '-' . $unit['id'] );

									$options = $this->add_missing_mapping( $mapping, 'property_type', $unit['portal_unit_type'], $import_id );
								}
			                }
			                else
			                {
			                    // Setting to blank
			                    wp_delete_object_term_relationships( $unit_post_id, 'property_type' );
			                }

							$department = 'residential-lettings';
							if ( class_exists('PH_Rooms') && $import_structure == '' )
					        {
								$department = 'rooms';
							}
							update_post_meta( $unit_post_id, '_department', $department );

							$on_market = '';
							if ( strtolower($unit['unit_status']) != 'unavailable to let' )
							{
								$on_market = 'yes';
							}
							update_post_meta( $unit_post_id, '_on_market', $on_market );

			                update_post_meta( $unit_post_id, '_room_name', $unit['unit_ref'] );
			                $available_date = ( ( isset($unit['date_available']) && $unit['date_available'] != '' ) ? date("Y-m-d", strtotime($unit['date_available'])) : '' );
			                if ( $available_date == '' )
			                {
			                	$available_date = ( ( isset($unit['available_from']) && $unit['available_from'] != '' ) ? date("Y-m-d", strtotime($unit['available_from'])) : '' );
			                }
			                update_post_meta( $unit_post_id, '_available_date', $available_date );

			                $rent = preg_replace("/[^0-9.]/", '', $unit['portal_market_rent']);
			                update_post_meta( $unit_post_id, '_rent', $rent );

			                $price_actual = ($rent * 52) / 12;
			                $rent_frequency = 'pw';
			                switch ($unit['portal_market_rent_frequency'])
							{
								case "Monthly": { $rent_frequency = 'pcm'; $price_actual = $price; break; }
							}

			                update_post_meta( $unit_post_id, '_rent_frequency', $rent_frequency );

			                update_post_meta( $unit_post_id, '_currency', 'GBP' );

			                /*if ( $rent != '' && ( $parent_price == 0 || ( $parent_price != 0 && $parent_price > $rent ) ) )
			                {
			                    $parent_price = $rent;
			                }*/

			                update_post_meta( $unit_post_id, '_price_actual', $price_actual );

			                //update_post_meta( $unit_post_id, '_poa', '' );

			                update_post_meta( $unit_post_id, '_deposit', preg_replace("/[^0-9.]/", '', $unit['deposit_amount']) );

			                if ( isset($unit['furnished']) && $unit['furnished'] != '' )
			                {
			                	// Availability
								if ( isset($_POST['mapped_furnished']) )
								{
									$mapping = $_POST['mapped_furnished'];
								}
								else
								{
									$mapping = isset($options['mappings']['furnished']) ? $options['mappings']['furnished'] : array();
								}

			                	if ( !empty($mapping) && isset($mapping[$unit['furnished']]) )
								{
									wp_set_post_terms( $unit_post_id, $mapping[$unit['furnished']], 'furnished' );
								}
								else
								{
									$this->add_log( 'Unit with ID ' . $unit['id'] . ' received with a furnished value (' . $unit['furnished'] . ') that is not mapped', $property['id'] . '-' . $unit['id'] );

									$options = $this->add_missing_mapping( $mapping, 'furnished', $unit['furnished'], $import_id );
								}
			                }
			                else
			                {
			                    // Setting to blank
			                    wp_delete_object_term_relationships( $unit_post_id, 'furnished' );
			                }

			                if ( isset($unit['unit_status']) && $unit['unit_status'] != '' && strtolower($unit['unit_status']) != 'unavailable to let' )
			                {
			                	// Availability
								if ( isset($_POST['mapped_availability']) )
								{
									$mapping = $_POST['mapped_availability'];
								}
								else
								{
									$mapping = isset($options['mappings']['availability']) ? $options['mappings']['availability'] : array();
								}

			                	if ( !empty($mapping) && isset($mapping[$unit['unit_status']]) )
								{
									wp_set_post_terms( $unit_post_id, $mapping[$unit['unit_status']], 'availability' );
								}
								else
								{
									$this->add_log( 'Unit with ID ' . $unit['id'] . ' received with an availability (' . $unit['unit_status'] . ') that is not mapped', $property['id'] . '-' . $unit['id'] );

									$options = $this->add_missing_mapping( $mapping, 'availability', $unit['unit_status'], $import_id );
								}
			                }
			                else
			                {
			                    // Setting to blank
			                    wp_delete_object_term_relationships( $unit_post_id, 'availability' );
			                }

			                // Features
				            if ( isset($unit['features']) && is_array($unit['features']) && !empty($unit['features']) )
				            {
				        		$i = 0;
						        foreach ( $unit['features'] as $feature )
						        {
						            update_post_meta( $unit_post_id, '_feature_' . $i, $feature );
						            ++$i;
						        }
						        update_post_meta( $unit_post_id, '_features', $i );
						    }

						    // Rooms / Descriptions
					        // For now put the whole description in one room / description
							update_post_meta( $unit_post_id, '_rooms', '1' );
							update_post_meta( $unit_post_id, '_room_name_0', '' );
				            update_post_meta( $unit_post_id, '_room_dimensions_0', '' );
				            update_post_meta( $unit_post_id, '_room_description_0', str_replace(array("\r\n", "\n"), "", $unit['description']) );

			                // Media - Images
							if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
			    			{
			    				$media_urls = array();
			    				if ( isset($unit['image_urls']) && is_array($unit['image_urls']) && !empty($unit['image_urls']) )
								{
									foreach ( $unit['image_urls'] as $url )
									{
										$media_urls[] = array('url' => $url);
									}
								}

								// get parent property images
								$photo_urls = get_post_meta( $post_id, '_photo_urls', TRUE );
								if ( is_array($photo_urls) && !empty($photo_urls) )
								{
									$media_urls = array_merge($media_urls, $photo_urls);
								}

								update_post_meta( $unit_post_id, '_photo_urls', $media_urls );

								$this->add_log( 'Imported ' . count($media_urls) . ' photo URLs', $property['id'] . '-' . $unit['id'] );
			    			}
			    			else
			        		{
								$media_ids = array();
								$new = 0;
								$existing = 0;
								$deleted = 0;
								$previous_media_ids = get_post_meta( $unit_post_id, '_photos', TRUE );

								if ( isset($unit['image_urls']) && is_array($unit['image_urls']) && !empty($unit['image_urls']) )
								{
									foreach ( $unit['image_urls'] as $url )
									{
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
										        'name' => $filename . '.jpg',
										        'tmp_name' => $tmp
										    );

										    // Check for download errors
										    if ( is_wp_error( $tmp ) ) 
										    {
										        @unlink( $file_array[ 'tmp_name' ] );

										        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['id'] . '-' . $unit['id'] );
										    }
										    else
										    {
											    $id = media_handle_sideload( $file_array, $unit_post_id, $description );

											    // Check for handle sideload errors.
											    if ( is_wp_error( $id ) ) 
											    {
											        @unlink( $file_array['tmp_name'] );
											        
											        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['id'] . '-' . $unit['id'] );
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

								// get parent property images
								$photo_ids = get_post_meta( $post_id, '_photos', TRUE );
								if ( is_array($photo_ids) && !empty($photo_ids) )
								{
									$media_ids = array_merge($media_ids, $photo_ids);
								}

								update_post_meta( $unit_post_id, '_photos', $media_ids );

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

								$this->add_log( 'Imported ' . count($media_ids) . ' photos for unit ID ' . $unit['id'] . ' (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property['id'] . '-' . $unit['id'] );
							}

							// Media - Floorplans
							if ( get_option('propertyhive_floorplans_stored_as', '') == 'urls' )
			    			{
			    				$media_urls = array();
			    				if ( isset($unit['floor_plan_urls']) && is_array($unit['floor_plan_urls']) && !empty($unit['floor_plan_urls']) )
								{
									foreach ( $unit['floor_plan_urls'] as $url )
									{
										$media_urls[] = array('url' => $url);
									}
								}
								update_post_meta( $unit_post_id, '_floorplan_urls', $media_urls );

								$this->add_log( 'Imported ' . count($media_urls) . ' floorplan URLs', $property['id'] . '-' . $unit['id'] );
			    			}
			    			else
			        		{
								$media_ids = array();
								$new = 0;
								$existing = 0;
								$deleted = 0;
								$previous_media_ids = get_post_meta( $unit_post_id, '_floorplans', TRUE );

								if ( isset($unit['floor_plan_urls']) && is_array($unit['floor_plan_urls']) && !empty($unit['floor_plan_urls']) )
								{
									foreach ( $unit['floor_plan_urls'] as $url )
									{
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
										        'name' => $filename . '.jpg',
										        'tmp_name' => $tmp
										    );

										    // Check for download errors
										    if ( is_wp_error( $tmp ) ) 
										    {
										        @unlink( $file_array[ 'tmp_name' ] );

										        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['id'] . '-' . $unit['id'] );
										    }
										    else
										    {
											    $id = media_handle_sideload( $file_array, $unit_post_id, $description );

											    // Check for handle sideload errors.
											    if ( is_wp_error( $id ) ) 
											    {
											        @unlink( $file_array['tmp_name'] );
											        
											        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['id'] . '-' . $unit['id'] );
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
								update_post_meta( $unit_post_id, '_floorplans', $media_ids );

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

								$this->add_log( 'Imported ' . count($media_ids) . ' floorplans for unit ID ' . $unit['id'] . ' (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property['id'] . '-' . $unit['id'] );
							}

							// Media - EPCs
							if ( get_option('propertyhive_epcs_stored_as', '') == 'urls' )
			    			{
			    				$media_urls = array();
			    				if ( isset($unit['epc_urls']) && is_array($unit['epc_urls']) && !empty($unit['epc_urls']) )
								{
									foreach ( $unit['epc_urls'] as $url )
									{
										$media_urls[] = array('url' => $url);
									}
								}
								update_post_meta( $unit_post_id, '_epc_urls', $media_urls );

								$this->add_log( 'Imported ' . count($media_urls) . ' epc URLs', $property['id'] . '-' . $unit['id'] );
			    			}
			    			else
			        		{
								$media_ids = array();
								$new = 0;
								$existing = 0;
								$deleted = 0;
								$previous_media_ids = get_post_meta( $unit_post_id, '_epcs', TRUE );

								if ( isset($unit['epc_urls']) && is_array($unit['epc_urls']) && !empty($unit['epc_urls']) )
								{
									foreach ( $unit['epc_urls'] as $url )
									{
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
										        'name' => $filename . '.jpg',
										        'tmp_name' => $tmp
										    );

										    // Check for download errors
										    if ( is_wp_error( $tmp ) ) 
										    {
										        @unlink( $file_array[ 'tmp_name' ] );

										        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['id'] . '-' . $unit['id'] );
										    }
										    else
										    {
											    $id = media_handle_sideload( $file_array, $unit_post_id, $description );

											    // Check for handle sideload errors.
											    if ( is_wp_error( $id ) ) 
											    {
											        @unlink( $file_array['tmp_name'] );
											        
											        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['id'] . '-' . $unit['id'] );
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
								update_post_meta( $unit_post_id, '_epcs', $media_ids );

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

								$this->add_log( 'Imported ' . count($media_ids) . ' EPCs for unit ID ' . $unit['id'] . ' (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property['id'] . '-' . $unit['id'] );
							}

							do_action( "propertyhive_property_unit_imported_arthur_json", $unit_post_id, $unit );

							$post = get_post( $unit_post_id );
							do_action( "save_post_property", $unit_post_id, $post, false );
							do_action( "save_post", $unit_post_id, $post, false );

							if ( $inserted_updated_unit == 'updated' )
							{
								// Compare meta/taxonomy data before and after.

								$unit_metadata_after = get_metadata('post', $unit_post_id, '', true);

								foreach ( $unit_metadata_after as $key => $value)
								{
									if ( in_array($key, array('_photos', '_photo_urls', '_floorplans', '_floorplan_urls', '_brochures', '_brochure_urls', '_epcs', '_epc_urls', '_virtual_tours')) )
									{
										continue;
									}

									if ( !isset($unit_metadata_before[$key]) )
									{
										$this->add_log( 'New meta data for ' . trim($key, '_') . ': ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), $property['id'] . '-' . $unit['id'] );
									}
									elseif ( $unit_metadata_before[$key] != $unit_metadata_after[$key] )
									{
										$this->add_log( 'Updated ' . trim($key, '_') . '. Before: ' . ( ( is_array($unit_metadata_before[$key]) ) ? implode(", ", $unit_metadata_before[$key]) : $unit_metadata_before[$key] ) . ', After: ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), $property['id'] . '-' . $unit['id'] );
									}
								}

								$unit_taxonomy_terms_after = array();
								$taxonomy_names = get_post_taxonomies( $unit_post_id );
								foreach ( $taxonomy_names as $taxonomy_name )
								{
									$unit_taxonomy_terms_after[$taxonomy_name] = wp_get_post_terms( $unit_post_id, $taxonomy_name, array('fields' => 'ids') );
								}

								foreach ( $unit_taxonomy_terms_after as $taxonomy_name => $ids)
								{
									if ( !isset($unit_taxonomy_terms_before[$taxonomy_name]) )
									{
										$this->add_log( 'New taxonomy data for ' . $taxonomy_name . ': ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), $property['id'] . '-' . $unit['id'] );
									}
									elseif ( $unit_taxonomy_terms_before[$taxonomy_name] != $unit_taxonomy_terms_after[$taxonomy_name] )
									{
										$this->add_log( 'Updated ' . $taxonomy_name . '. Before: ' . ( ( is_array($unit_taxonomy_terms_before[$taxonomy_name]) ) ? implode(", ", $unit_taxonomy_terms_before[$taxonomy_name]) : $unit_taxonomy_terms_before[$taxonomy_name] ) . ', After: ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), $property['id'] . '-' . $unit['id'] );
									}
								}
							}
						}
					}
				}

				do_action( "propertyhive_property_imported_arthur_json", $post_id, $property );

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

		do_action( "propertyhive_post_import_properties_arthur_json" );

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

				if (
					isset($property['units']) && is_array($property['units']) && !empty($property['units']) 
				)
				{
					foreach ( $property['units'] as $unit )
					{
						$import_refs[] = $property['id'] . '-' . $unit['id'];
					}
				}
			}

			$args = array(
				'post_type' => 'property',
				'nopaging' => true,
				'suppress_filters' => TRUE,
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

					do_action( "propertyhive_property_removed_arthur_json", $post->ID );
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
		$mapping_values = $this->get_arthur_mapping_values('availability');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['availability'][$mapping_value] = '';
			}
		}

		$mapping_values = $this->get_arthur_mapping_values('property_type');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['property_type'][$mapping_value] = '';
			}
		}

		$mapping_values = $this->get_arthur_mapping_values('furnished');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['furnished'][$mapping_value] = '';
			}
		}

		$mapping_values = $this->get_arthur_mapping_values('office');
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
		return $this->get_arthur_mapping_values($custom_field);
	}

	public function get_arthur_mapping_values($custom_field) 
	{
        if ($custom_field == 'availability')
        {
            return array(
            	'Available To Let' => 'Available To Let',
            	'Under Offer' => 'Under Offer',
            	'Let' => 'Let',
            );
        }
        if ($custom_field == 'property_type')
        {
        	return array(
                'Apartment' => 'Apartment',
				'Bungalow' => 'Bungalow',
				'Chalet' => 'Chalet',
				'Cluster House' => 'Cluster House',
				'Cottage' => 'Cottage',
				'Detached' => 'Detached',
				'Detached Bungalow' => 'Detached Bungalow',
				'End of Terrace' => 'End of Terrace',
				'Flat' => 'Flat',
				'Ground Flat' => 'Ground Flat',
				'Ground Maisonette' => 'Ground Maisonette',
				'House' => 'House',
				'House Share' => 'House Share',
				'Land' => 'Land',
				'Link Detached House' => 'Link Detached House',
				'Maisonette' => 'Maisonette',
				'Mews' => 'Mews',
				'Mobile Home' => 'Mobile Home',
				'Penthouse' => 'Penthouse',
				'Semi-Detached' => 'Semi-Detached',
				'Semi-Detached Bungalow' => 'Semi-Detached Bungalow',
				'Studio' => 'Studio',
				'Terraced' => 'Terraced',
				'Terraced Bungalow' => 'Terraced Bungalow',
				'Town House' => 'Town House',
				'Villa' => 'Villa',
            );
        }
        if ($custom_field == 'furnished')
        {
        	return array(
        		'Furnished' => 'Furnished',
        		'Part-Furnished' => 'Part-Furnished',
        		'Unfurnished' => 'Unfurnished',
        	);
        }
    }
}

}