<?php
/**
 * Class for managing the import process of a Veco JSON file
 *
 * @package WordPress
 */
if ( class_exists( 'PH_Property_Import_Process' ) ) {

class PH_Veco_JSON_Import extends PH_Property_Import_Process {

	public function __construct( $instance_id = '' ) 
	{
		$this->instance_id = $instance_id;

		if ( $this->instance_id != '' && isset($_GET['custom_property_import_cron']) )
	    {
	    	$current_user = wp_get_current_user();

	    	$this->add_log("Executed manually by " . ( ( isset($current_user->display_name) ) ? $current_user->display_name : '' ) );
	    }
	}

	public function parse( $options )
	{
		$response = wp_remote_get( 
			'https://passport.eurolink.co/api/properties/v1/?size=9999', 
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $options['access_token'],
					'Content-Type' => 'application/json'
				),
				'body' => '',
		    )
		);

		if ( !is_wp_error( $response ) && is_array( $response ) ) 
		{
			$contents = $response['body'];

			$json = json_decode( $contents, TRUE );

			if ($json !== FALSE && isset($json['Data']) && is_array($json['Data']) && !empty($json['Data']))
			{
				$this->add_log("Parsing properties");
				
				foreach ($json['Data'] as $property)
				{
					if (isset($property['_source']))
					{
						$this->properties[] = $property['_source'];
					}
				}

				$this->add_log("Found " . count($this->properties) . " properties in JSON ready for importing");
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
	    	$this->add_error( 'Failed to obtain JSON. Dump of response as follows: ' . print_r($response, TRUE) );
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

        do_action( "propertyhive_pre_import_properties_veco_json", $this->properties );
        $this->properties = apply_filters( "propertyhive_veco_json_properties_due_import", $this->properties );

		$this->add_log( 'Beginning to veco through ' . count($this->properties) . ' properties' );

		$property_row = 1;
		foreach ( $this->properties as $property )
		{
			if ( !isset($property['fake']) )
			{
				$this->add_log( 'Importing property with reference ' . $property['WebID'], $property['WebID'] );

				$inserted_updated = false;

				$args = array(
		            'post_type' => 'property',
		            'posts_per_page' => 1,
		            'post_status' => 'any',
		            'meta_query' => array(
		            	array(
			            	'key' => $imported_ref_key,
			            	'value' => $property['WebID']
			            )
		            )
		        );
		        $property_query = new WP_Query($args);

		        $display_address = isset($property['Property']['ShortAddress']) ? $property['Property']['ShortAddress'] : '';
		        if ( trim($display_address) == '' )
		        {
		        	$display_address = array();
			        if ( isset($property['Address']['Street']) && trim($property['Address']['Street']) != '' )
			        {
			        	$display_address[] = trim($property['Address']['Street']);
			        }
			        if ( isset($property['Address']['Line2']) && trim($property['Address']['Line2']) != '' )
			        {
			        	$display_address[] = trim($property['Address']['Line2']);
			        }
			        elseif ( isset($property['Address']['PostTown']) && trim($property['Address']['PostTown']) != '' )
			        {
			        	$display_address[] = trim($property['Address']['PostTown']);
			        }
			        elseif ( isset($property['Address']['County']) && trim($property['Address']['County']) != '' )
			        {
			        	$display_address[] = trim($property['Address']['County']);
			        }
			        $display_address = implode(", ", $display_address);
			    }
		        
		        if ($property_query->have_posts())
		        {
		        	$this->add_log( 'This property has been imported before. Updating it', $property['WebID'] );

		        	// We've imported this property before
		            while ($property_query->have_posts())
		            {
		                $property_query->the_post();

		                $post_id = get_the_ID();

		                $my_post = array(
					    	'ID'          	 => $post_id,
					    	'post_title'     => wp_strip_all_tags( $display_address ),
					    	'post_excerpt'   => $property['Property']['SummaryDescription'],
					    	'post_content' 	 => '',
					    	'post_status'    => 'publish',
					  	);

					 	// Update the post into the database
					    $post_id = wp_update_post( $my_post );

					    if ( is_wp_error( $post_id ) ) 
						{
							$this->add_error( 'Failed to update post. The error was as follows: ' . $post_id->get_error_message(), $property['WebID'] );
						}
						else
						{
							$inserted_updated = 'updated';
						}
		            }
		        }
		        else
		        {
		        	$this->add_log( 'This property hasn\'t been imported before. Inserting it', $property['WebID'] );

		        	// We've not imported this property before
					$postdata = array(
						'post_excerpt'   => $property['Property']['SummaryDescription'],
						'post_content' 	 => '',
						'post_title'     => wp_strip_all_tags( $display_address ),
						'post_status'    => 'publish',
						'post_type'      => 'property',
						'post_date'      => ( isset($property['Property']['InsertDate']) ) ? date( 'Y-m-d H:i:s', strtotime( $property['Property']['InsertDate'] )) : '',
						'comment_status' => 'closed',
					);

					$post_id = wp_insert_post( $postdata, true );

					if ( is_wp_error( $post_id ) ) 
					{
						$this->add_error( 'Failed to insert post. The error was as follows: ' . $post_id->get_error_message(), $property['WebID'] );
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
					    	'post_excerpt'   => htmlentities(mb_convert_encoding(wp_strip_all_tags( $property['Property']['SummaryDescription'] ), 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
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

					$this->add_log( 'Successfully ' . $inserted_updated . ' post. The post ID is ' . $post_id, $property['WebID'] );

					$previous_veco_json_update_date = get_post_meta( $post_id, '_veco_json_update_date_' . $import_id, TRUE);

					$skip_property = false;
					if (
						( isset($options['only_updated']) && $options['only_updated'] == 'yes' )
					)
					{
						if (
							isset($property['Property']['UpdatedDate']) && 
							$previous_veco_json_update_date == $property['Property']['UpdatedDate']
						)
						{
							$skip_property = true;
						}
					}

					// Coordinates
					if ( isset($property['Location']['Latitude']) && isset($property['Location']['Longitude']) && $property['Location']['Latitude'] != '' && $property['Location']['Longitude'] != '' && $property['Location']['Latitude'] != '0' && $property['Location']['Longitude'] != '0' )
					{
						update_post_meta( $post_id, '_latitude', ( ( isset($property['Location']['Latitude']) ) ? $property['Location']['Latitude'] : '' ) );
						update_post_meta( $post_id, '_longitude', ( ( isset($property['Location']['Longitude']) ) ? $property['Location']['Longitude'] : '' ) );
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
									if ( isset($property['Address']['Street']) && trim($property['Address']['Street']) != '' ) { $address_to_geocode[] = $property['Address']['Street']; }
									if ( isset($property['Address']['PostTown']) && trim($property['Address']['PostTown']) != '' ) { $address_to_geocode[] = $property['Address']['PostTown']; }
									if ( isset($property['Address']['County']) && trim($property['Address']['County']) != '' ) { $address_to_geocode[] = $property['Address']['County']; }
									if ( isset($property['Postcode']['PostcodeFull']) && trim($property['Postcode']['PostcodeFull']) != '' ) { $address_to_geocode[] = $property['Postcode']['PostcodeFull']; }
									
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
								        	$this->add_error( 'Google Geocoding service returned status ' . $status, $property['WebID'] );
								        	sleep(3);
								        }
								    }
							        else
							        {
							        	$this->add_error( 'Failed to parse XML response from Google Geocoding service.', $property['WebID'] );
							        }
								}
								else
						        {
						        	$this->add_error( 'Failed to obtain co-ordinates as allow_url_fopen setting is disabled', $property['WebID'] );
						        }
						    }
						    else
						    {
						    	$this->add_log( 'Not performing Google Geocoding request as no API key present in settings', $property['WebID'] );
						    }
						}
					}

					if ( !$skip_property )
					{
						update_post_meta( $post_id, $imported_ref_key, $property['WebID'] );

						// Address
						update_post_meta( $post_id, '_reference_number', '' );
						update_post_meta( $post_id, '_address_name_number', trim( ( ( isset($property['Address']['BuildingName']) ) ? $property['Address']['BuildingName'] : '' ) . ' ' . ( ( isset($property['Address']['BuildingNumber']) ) ? $property['Address']['BuildingNumber'] : '' ) ) );
						update_post_meta( $post_id, '_address_street', ( ( isset($property['Address']['Street']) ) ? $property['Address']['Street'] : '' ) );
						update_post_meta( $post_id, '_address_two', ( ( isset($property['Address']['Line2']) ) ? $property['Address']['Line2'] : '' ) );
						update_post_meta( $post_id, '_address_three', ( ( isset($property['Address']['PostTown']) ) ? $property['Address']['PostTown'] : '' ) );
						update_post_meta( $post_id, '_address_four', ( ( isset($property['Address']['County']) ) ? $property['Address']['County'] : '' ) );
						update_post_meta( $post_id, '_address_postcode', ( ( isset($property['Postcode']['PostcodeFull']) ) ? $property['Postcode']['PostcodeFull'] : '' ) );

						$country = get_option( 'propertyhive_default_country', 'GB' );
						update_post_meta( $post_id, '_address_country', $country );

						// Check main address fields and see if this location exists as a taxonomy to try and assign properties to location
						$address_fields_to_check = apply_filters( 'propertyhive_veco_json_address_fields_to_check', array('Line2', 'Line3', 'Line4', 'PostTown', 'County') );
						$location_term_ids = array();

						foreach ( $address_fields_to_check as $address_field )
						{
							if ( isset($property['Address'][$address_field]) && trim($property['Address'][$address_field]) != '' ) 
							{
								$term = term_exists( trim($property['Address'][$address_field]), 'location');
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

						// Owner
						add_post_meta( $post_id, '_owner_contact_id', '', true );

						// Record Details
						add_post_meta( $post_id, '_negotiator_id', get_current_user_id(), true );
						
						$office_id = $primary_office_id;
						if ( isset($_POST['mapped_office'][$property['Office']['Name']]) && $_POST['mapped_office'][$property['Office']['Name']] != '' )
						{
							$office_id = $_POST['mapped_office'][$property['Office']['Name']];
						}
						elseif ( isset($options['offices']) && is_array($options['offices']) && !empty($options['offices']) )
						{
							foreach ( $options['offices'] as $ph_office_id => $branch_code )
							{
								if ( $branch_code == $property['Office']['Name'] )
								{
									$office_id = $ph_office_id;
									break;
								}
							}
						}
						update_post_meta( $post_id, '_office_id', $office_id );

						// Residential Details
						$department = 'residential-sales';
						if ( $property['Property']['Category'] == 'Lettings' )
						{
							$department = 'residential-lettings';
						}
						update_post_meta( $post_id, '_department', $department );
						
						update_post_meta( $post_id, '_bedrooms', ( ( isset($property['Property']['Bedrooms']) ) ? $property['Property']['Bedrooms'] : '' ) );
						update_post_meta( $post_id, '_bathrooms', ( ( isset($property['Property']['Bathrooms']) ) ? $property['Property']['Bathrooms'] : '' ) );
						update_post_meta( $post_id, '_reception_rooms', ( ( isset($property['Property']['Receptions']) ) ? $property['Property']['Receptions'] : '' ) );

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

						if ( isset($property['Property']['PropertyType']) )
						{
							if ( !empty($mapping) && isset($mapping[$property['Property']['PropertyType']]) )
							{
								wp_set_post_terms( $post_id, $mapping[$property['Property']['PropertyType']], $prefix . 'property_type' );
							}
							else
							{
								$this->add_log( 'Property received with a type (' . $property['Property']['PropertyType'] . ') that is not mapped', $property['WebID'] );

								$options = $this->add_missing_mapping( $mapping, $prefix . 'property_type', $property['Property']['PropertyType'], $import_id );
							}
						}

						// Residential Sales Details
						if ( $department == 'residential-sales' )
						{
							// Clean price
							$price = round(preg_replace("/[^0-9.]/", '', $property['Property']['Amount']));

							update_post_meta( $post_id, '_price', $price );
							update_post_meta( $post_id, '_price_actual', $price );
							update_post_meta( $post_id, '_poa', ( $property['Property']['PriceStatus'] == 'Price on Application' ) ? 'yes' : '' );
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
							if ( !empty($mapping) && isset($property['Property']['PriceStatus']) && isset($mapping[$property['Property']['PriceStatus']]) )
							{
				                wp_set_post_terms( $post_id, $mapping[$property['Property']['PriceStatus']], 'price_qualifier' );
				            }

				            // Tenure
				            /*if ( isset($_POST['mapped_tenure']) )
							{
								$mapping = $_POST['mapped_tenure'];
							}
							else
							{
								$mapping = isset($options['mappings']['tenure']) ? $options['mappings']['tenure'] : array();
							}

				            wp_delete_object_term_relationships( $post_id, 'tenure' );
							if ( !empty($mapping) && isset($property->propertyTenure) && isset($mapping[(string)$property->propertyTenure]) )
							{
					            wp_set_post_terms( $post_id, $mapping[(string)$property->propertyTenure], 'tenure' );
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

						if ( $department == 'residential-lettings' )
						{
							update_post_meta( $post_id, '_rent', $property['Property']['Amount'] );

							$rent_frequency = 'pcm';
							$price_actual = $property['Property']['Amount'];
							switch ($property['Property']['RentPeriod'])
							{
								case "per week": { $rent_frequency = 'pw'; $price_actual = ($property['Property']['Amount'] * 52) / 12; break; }
								case "per month": { $rent_frequency = 'pcm'; $price_actual = $property['Property']['Amount']; break; }
							}
							update_post_meta( $post_id, '_rent_frequency', $rent_frequency );
							update_post_meta( $post_id, '_price_actual', $price_actual );
							update_post_meta( $post_id, '_currency', 'GBP' );
							update_post_meta( $post_id, '_poa', ( $property['Property']['PriceStatus'] == 'Price on Application' ) ? 'yes' : '' );

							update_post_meta( $post_id, '_deposit', '' );
		            		update_post_meta( $post_id, '_available_date', ( (isset($property['Property']['AvailableFromDate']) && $property['Property']['AvailableFromDate'] != '') ? date("Y-m-d", strtotime($property['Property']['AvailableFromDate'])) : '' ) );

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
							if ( !empty($mapping) && isset($property['Property']['Furnished']) && isset($mapping[$property['Property']['Furnished']]) )
							{
				                wp_set_post_terms( $post_id, $mapping[$property['Property']['Furnished']], 'furnished' );
				            }
						}

						// Marketing
						update_post_meta( $post_id, '_on_market', 'yes' );
						update_post_meta( $post_id, '_featured', ( isset($property['Property']['Featured']) && strtolower($property['Property']['Featured']) == 'true' ) ? 'yes' : '' );

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

		        		wp_delete_object_term_relationships( $post_id, 'availability' );
						if ( !empty($mapping) && isset($property['Property']['Status']) && isset($mapping[$property['Property']['Status']]) )
						{
			                wp_set_post_terms( $post_id, $mapping[$property['Property']['Status']], 'availability' );
			            }

			            // Features
						$features = array();
						for ( $i = 1; $i <= 10; ++$i )
						{
							if ( isset($property['Features']['Feature' . $i]) && trim($property['Features']['Feature' . $i]) != '' )
							{
								$features[] = trim($property['Features']['Feature' . $i]);
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
				        // For now put the whole description in one room / description
						update_post_meta( $post_id, '_rooms', '1' );
						update_post_meta( $post_id, '_room_name_0', '' );
			            update_post_meta( $post_id, '_room_dimensions_0', '' );
			            update_post_meta( $post_id, '_room_description_0', str_replace(array("\r\n", "\n"), "", $property['Property']['RichTextDescription']) );
						
						// Media - Images
						if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
	        			{
	        				$media_urls = array();
	        				for ( $i = 1; $i <= 25; ++$i )
							{
								if ( isset($property['Photos']['Photo' . $i]) && $property['Photos']['Photo' . $i] != '' )
								{
									// This is a URL
									$url = 'https://passport.eurolink.co/api/properties/v1/media/' . $property['Photos']['Photo' . $i];

									$media_urls[] = array('url' => $url);
								}
							}
							update_post_meta( $post_id, '_photo_urls', $media_urls );

							$this->add_log( 'Imported ' . count($media_urls) . ' photo URLs', $property['WebID'] );
	        			}
	        			else
		        		{
							$media_ids = array();
							$new = 0;
							$existing = 0;
							$deleted = 0;
							$previous_media_ids = get_post_meta( $post_id, '_photos', TRUE );

							for ( $i = 1; $i <= 25; ++$i )
							{
								if ( isset($property['Photos']['Photo' . $i]) && $property['Photos']['Photo' . $i] != '' )
								{
									// This is a URL
									$url = 'https://passport.eurolink.co/api/properties/v1/media/' . $property['Photos']['Photo' . $i];
									$description = isset($property['Photos']['Description' . $i]) ? $property['Photos']['Description' . $i] : '';
								    
									$modified = $property['Property']['UpdatedDate'];

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

									        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['WebID'] );
									    }
									    else
									    {
										    $id = media_handle_sideload( $file_array, $post_id, $description );

										    // Check for handle sideload errors.
										    if ( is_wp_error( $id ) ) 
										    {
										        @unlink( $file_array['tmp_name'] );
										        
										        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['WebID'] );
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
							update_post_meta( $post_id, '_photos', $media_ids );

							// Veco through $previous_media_ids, check each one exists in $media_ids, and if it doesn't then delete
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

							$this->add_log( 'Imported ' . count($media_ids) . ' photos (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property['WebID'] );
						}

						// Media - Floorplans
						if ( get_option('propertyhive_floorplans_stored_as', '') == 'urls' )
	        			{
	        				$media_urls = array();
	        				for ( $i = 1; $i <= 5; ++$i )
							{
								if ( isset($property['FloorPlans']['Plan' . $i]) && $property['FloorPlans']['Plan' . $i] != '' )
								{
									// This is a URL
									$url = 'https://passport.eurolink.co/api/properties/v1/media/' . $property['FloorPlans']['Plan' . $i];

									$media_urls[] = array('url' => $url);
								}
							}
							update_post_meta( $post_id, '_floorplan_urls', $media_urls );

							$this->add_log( 'Imported ' . count($media_urls) . ' floorplan URLs', $property['WebID'] );
	        			}
	        			else
	        			{
							$media_ids = array();
							$new = 0;
							$existing = 0;
							$deleted = 0;
							$previous_media_ids = get_post_meta( $post_id, '_floorplans', TRUE );

							for ( $i = 1; $i <= 5; ++$i )
							{
								if ( isset($property['FloorPlans']['Plan' . $i]) && $property['FloorPlans']['Plan' . $i] != '' )
								{
									// This is a URL
									$url = 'https://passport.eurolink.co/api/properties/v1/media/' . $property['FloorPlans']['Plan' . $i];
									$description = '';

									$modified = $property['Property']['UpdatedDate'];
								    
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

									        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['WebID'] );
									    }
									    else
									    {
										    $id = media_handle_sideload( $file_array, $post_id, $description );

										    // Check for handle sideload errors.
										    if ( is_wp_error( $id ) ) 
										    {
										        @unlink( $file_array['tmp_name'] );
										        
										        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['WebID'] );
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
							update_post_meta( $post_id, '_floorplans', $media_ids );

							// Veco through $previous_media_ids, check each one exists in $media_ids, and if it doesn't then delete
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

							$this->add_log( 'Imported ' . count($media_ids) . ' floorplans (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property['WebID'] );
						}

						// Media - Brochures
						if ( get_option('propertyhive_brochures_stored_as', '') == 'urls' )
	        			{
	        				$media_urls = array();
	        				for ( $i = 1; $i <= 2; ++$i )
							{
								if ( isset($property['Brochures']['Document' . $i]) && $property['Brochures']['Document' . $i] != '' )
								{
									// This is a URL
									$url = 'https://passport.eurolink.co/api/properties/v1/media/' . $property['Brochures']['Document' . $i];

									$media_urls[] = array('url' => $url);
								}
							}
							update_post_meta( $post_id, '_brochure_urls', $media_urls );

							$this->add_log( 'Imported ' . count($media_urls) . ' brochure URLs', $property['WebID'] );
	        			}
	        			else
	        			{
							$media_ids = array();
							$new = 0;
							$existing = 0;
							$deleted = 0;
							$previous_media_ids = get_post_meta( $post_id, '_brochures', TRUE );

							for ( $i = 1; $i <= 2; ++$i )
							{
								if ( isset($property['Brochures']['Document' . $i]) && $property['Brochures']['Document' . $i] != '' )
								{
									// This is a URL
									$url = 'https://passport.eurolink.co/api/properties/v1/media/' . $property['Brochures']['Document' . $i];
									$description = isset($property['Brochures']['Description' . $i]) ? $property['Brochures']['Description' . $i] : '';
								    
									$modified = $property['Property']['UpdatedDate'];

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

									        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['WebID'] );
									    }
									    else
									    {
										    $id = media_handle_sideload( $file_array, $post_id, $description );

										    // Check for handle sideload errors.
										    if ( is_wp_error( $id ) ) 
										    {
										        @unlink( $file_array['tmp_name'] );
										        
										        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['WebID'] );
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
							update_post_meta( $post_id, '_brochures', $media_ids );

							// Veco through $previous_media_ids, check each one exists in $media_ids, and if it doesn't then delete
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

							$this->add_log( 'Imported ' . count($media_ids) . ' brochures (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property['WebID'] );
						}

						// Media - EPCs
						if ( get_option('propertyhive_epcs_stored_as', '') == 'urls' )
	        			{
	        				$media_urls = array();
	        				for ( $i = 1; $i <= 2; ++$i )
							{
								if ( isset($property['EPCs']['Image' . $i]) && $property['EPCs']['Image' . $i] != '' )
								{
									// This is a URL
									$url = 'https://passport.eurolink.co/api/properties/v1/media/' . $property['EPCs']['Image' . $i];

									$media_urls[] = array('url' => $url);
								}
							}
	        				for ( $i = 1; $i <= 2; ++$i )
							{
								if ( isset($property['EPCs']['Document' . $i]) && $property['EPCs']['Document' . $i] != '' )
								{
									// This is a URL
									$url = 'https://passport.eurolink.co/api/properties/v1/media/' . $property['EPCs']['Document' . $i];

									$media_urls[] = array('url' => $url);
								}
							}
							update_post_meta( $post_id, '_epc_urls', $media_urls );

							$this->add_log( 'Imported ' . count($media_urls) . ' EPC URLs', $property['WebID'] );
	        			}
	        			else
	        			{
							$media_ids = array();
							$new = 0;
							$existing = 0;
							$deleted = 0;
							$previous_media_ids = get_post_meta( $post_id, '_epcs', TRUE );
							
							for ( $i = 1; $i <= 2; ++$i )
							{
								if ( isset($property['EPCs']['Image' . $i]) && $property['EPCs']['Image' . $i] != '' )
								{
									// This is a URL
									$url = 'https://passport.eurolink.co/api/properties/v1/media/' . $property['EPCs']['Image' . $i];
									$description = '';

									$modified = $property['Property']['UpdatedDate'];
								    
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

									        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['WebID'] );
									    }
									    else
									    {
										    $id = media_handle_sideload( $file_array, $post_id, $description );

										    // Check for handle sideload errors.
										    if ( is_wp_error( $id ) ) 
										    {
										        @unlink( $file_array['tmp_name'] );
										        
										        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['WebID'] );
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
							for ( $i = 1; $i <= 2; ++$i )
							{
								if ( isset($property['EPCs']['Document' . $i]) && $property['EPCs']['Document' . $i] != '' )
								{
									// This is a URL
									$url = 'https://passport.eurolink.co/api/properties/v1/media/' . $property['EPCs']['Document' . $i];
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
									        'name' => $filename,
									        'tmp_name' => $tmp
									    );

									    // Check for download errors
									    if ( is_wp_error( $tmp ) ) 
									    {
									        @unlink( $file_array[ 'tmp_name' ] );

									        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['WebID'] );
									    }
									    else
									    {
										    $id = media_handle_sideload( $file_array, $post_id, $description );

										    // Check for handle sideload errors.
										    if ( is_wp_error( $id ) ) 
										    {
										        @unlink( $file_array['tmp_name'] );
										        
										        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['WebID'] );
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

							// Veco through $previous_media_ids, check each one exists in $media_ids, and if it doesn't then delete
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

							$this->add_log( 'Imported ' . count($media_ids) . ' epcs (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property['WebID'] );
						}

						// Media - Virtual Tours
						$virtual_tours = array();
						for ( $i = 1; $i <= 4; ++$i )
						{
							if ( isset($property['Videos']['Video' . $i]) && $property['Videos']['Video' . $i] != '' )
							{
								// This is a URL
								$url = $property['Videos']['Video' . $i];

								$virtual_tours[] = $url;
							}
						}

		                update_post_meta( $post_id, '_virtual_tours', count($virtual_tours) );
		                foreach ( $virtual_tours as $i => $virtual_tour )
		                {
		                	update_post_meta( $post_id, '_virtual_tour_' . $i, (string)$virtual_tour );
		                }

						$this->add_log( 'Imported ' . count($virtual_tours) . ' virtual tours', $property['WebID'] );
					}
					else
					{
						$this->add_log( 'Skipping property as not been updated', $property['WebID'] );
					}
					
					update_post_meta( $post_id, '_veco_json_update_date_' . $import_id, $property['Property']['UpdatedDate'] );

					do_action( "propertyhive_property_imported_veco_json", $post_id, $property );

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
								$this->add_log( 'New meta data for ' . trim($key, '_') . ': ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), $property['WebID'] );
							}
							elseif ( $metadata_before[$key] != $metadata_after[$key] )
							{
								$this->add_log( 'Updated ' . trim($key, '_') . '. Before: ' . ( ( is_array($metadata_before[$key]) ) ? implode(", ", $metadata_before[$key]) : $metadata_before[$key] ) . ', After: ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), $property['WebID'] );
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
								$this->add_log( 'New taxonomy data for ' . $taxonomy_name . ': ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), $property['WebID'] );
							}
							elseif ( $taxonomy_terms_before[$taxonomy_name] != $taxonomy_terms_after[$taxonomy_name] )
							{
								$this->add_log( 'Updated ' . $taxonomy_name . '. Before: ' . ( ( is_array($taxonomy_terms_before[$taxonomy_name]) ) ? implode(", ", $taxonomy_terms_before[$taxonomy_name]) : $taxonomy_terms_before[$taxonomy_name] ) . ', After: ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), $property['WebID'] );
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

			}

		} // end foreach property

		do_action( "propertyhive_post_import_properties_veco_json" );

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
				$import_refs[] = $property['WebID'];
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

					do_action( "propertyhive_property_removed_veco_json", $post->ID );
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
				$mapping_values = $this->get_veco_mapping_values('sales_availability');
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
				$mapping_values = $this->get_veco_mapping_values('lettings_availability');
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
				$mapping_values = $this->get_veco_mapping_values('commercial_availability');
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
			$mapping_values = $this->get_veco_mapping_values('availability');
			if ( is_array($mapping_values) && !empty($mapping_values) )
			{
				foreach ($mapping_values as $mapping_value => $text_value)
				{
					$this->mappings['availability'][$mapping_value] = '';
				}
			}
		}

		$mapping_values = $this->get_veco_mapping_values('property_type');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['property_type'][$mapping_value] = '';
			}
		}

		$mapping_values = $this->get_veco_mapping_values('price_qualifier');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['price_qualifier'][$mapping_value] = '';
			}
		}

        $mapping_values = $this->get_veco_mapping_values('furnished');
        if ( is_array($mapping_values) && !empty($mapping_values) )
        {
            foreach ($mapping_values as $mapping_value => $text_value)
            {
                $this->mappings['furnished'][$mapping_value] = '';
            }
        }

		$mapping_values = $this->get_veco_mapping_values('office');
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
		return $this->get_veco_mapping_values($custom_field);
	}

	public function get_veco_mapping_values($custom_field) 
	{
        if ($custom_field == 'availability')
        {
            return array(
            	'Available' => 'Available',
                'Let Agreed' => 'Let Agreed',
                'SSTC' => 'SSTC',
                'Under Offer' => 'Under Offer',
            );
        }
        if ($custom_field == 'sales_availability')
        {
            return array(
            	'Available' => 'Available',
                'SSTC' => 'SSTC',
                'Under Offer' => 'Under Offer',
            );
        }
        if ($custom_field == 'lettings_availability')
        {
            return array(
            	'Available' => 'Available',
                'Let Agreed' => 'Let Agreed',
            );
        }
        if ($custom_field == 'property_type')
        {
        	return array(
				'House' => 'House',
				'Flat' => 'Flat',
				'Apartment' => 'Apartment',
            );
        }
        if ($custom_field == 'price_qualifier')
        {
        	return array(
        		'Price on Application' => 'Price on Application',
                'Guide Price' => 'Guide Price',
                'Fixed Price' => 'Fixed Price',
                'Offers in Excess of' => 'Offers in Excess of',
                'Offers In Region Of' => 'Offers In Region Of',
                'Sale by Tender' => 'Sale by Tender',
                'From' => 'From',
                'Offers Over' => 'Offers Over',
        	);
        }
        if ($custom_field == 'furnished')
        {
            return array(
                'Furnished' => 'Furnished',
                'Part Furnished' => 'Part Furnished',
                'Unfurnished' => 'Unfurnished',
            );
        }
    }
}

}