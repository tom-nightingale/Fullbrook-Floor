<?php
/**
 * Class for managing the import process of a Loop JSON file
 *
 * @package WordPress
 */
if ( class_exists( 'PH_Property_Import_Process' ) ) {

class PH_Loop_JSON_Import extends PH_Property_Import_Process {

	/**
	 * @var string
	 */
	private $target_file;

	public function __construct( $target_file = '', $instance_id = '' ) 
	{
		$this->target_file = $target_file;
		$this->instance_id = $instance_id;

		if ( $this->instance_id != '' && isset($_GET['custom_property_import_cron']) )
	    {
	    	$current_user = wp_get_current_user();

	    	$this->add_log("Executed manually by " . ( ( isset($current_user->display_name) ) ? $current_user->display_name : '' ) );
	    }
	}

	public function parse( $import_id = '' )
	{
		$json = json_decode( file_get_contents( $this->target_file ), TRUE );

		if ($json !== FALSE && is_array($json) && !empty($json))
		{
			$this->add_log("Parsing properties");
			
			$this->add_log("Found " . count($json) . " properties in JSON ready for parsing");

			foreach ($json as $property)
			{
				$this->properties[] = $property;
			}
        }
        else
        {
        	// Failed to parse JSON
        	$this->add_error( 'Failed to parse JSON file. Possibly invalid JSON' );
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

        do_action( "propertyhive_pre_import_properties_loop_json", $this->properties );
        $this->properties = apply_filters( "propertyhive_loop_json_properties_due_import", $this->properties );

		$this->add_log( 'Beginning to loop through ' . count($this->properties) . ' properties' );

		$property_row = 1;
		foreach ( $this->properties as $property )
		{
			if ( !isset($property['fake']) )
			{
				$this->add_log( 'Importing property with reference ' . $property['listingId'], $property['listingId'] );

				$inserted_updated = false;

				$args = array(
		            'post_type' => 'property',
		            'posts_per_page' => 1,
		            'post_status' => 'any',
		            'meta_query' => array(
		            	array(
			            	'key' => $imported_ref_key,
			            	'value' => $property['listingId']
			            )
		            )
		        );
		        $property_query = new WP_Query($args);

		        if ( isset($property['details']['displayAddress']) && trim($property['details']['displayAddress']) != '' )
		        {
		        	$display_address = trim($property['details']['displayAddress']);
		        }
		        else
		        {
			        $display_address = array();
			        if ( isset($property['details']['address']['street']) && trim($property['details']['address']['street']) != '' )
			        {
			        	$display_address[] = trim($property['details']['address']['street']);
			        }
			        if ( isset($property['details']['address']['locality']) && trim($property['details']['address']['locality']) != '' )
			        {
			        	$display_address[] = trim($property['details']['address']['locality']);
			        }
			        elseif ( isset($property['details']['address']['town']) && trim($property['details']['address']['town']) != '' )
			        {
			        	$display_address[] = trim($property['details']['address']['town']);
			        }
			        elseif ( isset($property['details']['address']['district']) && trim($property['details']['address']['district']) != '' )
			        {
			        	$display_address[] = trim($property['details']['address']['district']);
			        }
			        $display_address = implode(", ", $display_address);
		       	}
		        
		        if ($property_query->have_posts())
		        {
		        	$this->add_log( 'This property has been imported before. Updating it', $property['listingId'] );

		        	// We've imported this property before
		            while ($property_query->have_posts())
		            {
		                $property_query->the_post();

		                $post_id = get_the_ID();

		                $my_post = array(
					    	'ID'          	 => $post_id,
					    	'post_title'     => wp_strip_all_tags( $display_address ),
					    	'post_excerpt'   => $property['details']['shortDescription'],
					    	'post_content' 	 => '',
					    	'post_status'    => 'publish',
					  	);

					 	// Update the post into the database
					    $post_id = wp_update_post( $my_post );

					    if ( is_wp_error( $post_id ) ) 
						{
							$this->add_error( 'Failed to update post. The error was as follows: ' . $post_id->get_error_message(), $property['listingId'] );
						}
						else
						{
							$inserted_updated = 'updated';
						}
		            }
		        }
		        else
		        {
		        	$this->add_log( 'This property hasn\'t been imported before. Inserting it', $property['listingId'] );

		        	// We've not imported this property before
					$postdata = array(
						'post_excerpt'   => $property['details']['shortDescription'],
						'post_content' 	 => '',
						'post_title'     => wp_strip_all_tags( $display_address ),
						'post_status'    => 'publish',
						'post_type'      => 'property',
						'comment_status' => 'closed',
					);

					$post_id = wp_insert_post( $postdata, true );

					if ( is_wp_error( $post_id ) ) 
					{
						$this->add_error( 'Failed to insert post. The error was as follows: ' . $post_id->get_error_message(), $property['listingId'] );
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
					    	'post_excerpt'   => htmlentities(mb_convert_encoding(wp_strip_all_tags( $property['details']['shortDescription'] ), 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
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

					$this->add_log( 'Successfully ' . $inserted_updated . ' post. The post ID is ' . $post_id, $property['listingId'] );

					update_post_meta( $post_id, $imported_ref_key, $property['listingId'] );

					// Address
					update_post_meta( $post_id, '_reference_number', '' );
					update_post_meta( $post_id, '_address_name_number', trim( ( ( isset($property['details']['address']['paon']) ) ? $property['details']['address']['paon'] : '' ) . ' ' . ( ( isset($property['details']['address']['saon']) ) ? $property['details']['address']['saon'] : '' ) ) );
					update_post_meta( $post_id, '_address_street', ( ( isset($property['details']['address']['street']) ) ? $property['details']['address']['street'] : '' ) );
					update_post_meta( $post_id, '_address_two', ( ( isset($property['details']['address']['locality']) ) ? $property['details']['address']['locality'] : '' ) );
					update_post_meta( $post_id, '_address_three', ( ( isset($property['details']['address']['town']) ) ? $property['details']['address']['town'] : '' ) );
					update_post_meta( $post_id, '_address_four', ( ( isset($property['details']['address']['county']) ) ? $property['details']['address']['county'] : '' ) );
					update_post_meta( $post_id, '_address_postcode', ( ( isset($property['details']['address']['postcode']) ) ? $property['details']['address']['postcode'] : '' ) );

					$country = get_option( 'propertyhive_default_country', 'GB' );
					update_post_meta( $post_id, '_address_country', $country );

					// Check main address fields and see if this location exists as a taxonomy to try and assign properties to location
					$address_fields_to_check = apply_filters( 'propertyhive_loop_json_address_fields_to_check', array('locality', 'town', 'county') );
					$location_term_ids = array();

					foreach ( $address_fields_to_check as $address_field )
					{
						if ( isset($property['details']['address'][$address_field]) && trim($property['details']['address'][$address_field]) != '' ) 
						{
							$term = term_exists( trim($property['details']['address'][$address_field]), 'location');
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
								if ( isset($property['details']['address']['street']) && trim($property['details']['address']['street']) != '' ) { $address_to_geocode[] = $property['details']['address']['street']; }
								if ( isset($property['details']['address']['locality']) && trim($property['details']['address']['locality']) != '' ) { $address_to_geocode[] = $property['details']['address']['locality']; }
								if ( isset($property['details']['address']['town']) && trim($property['details']['address']['town']) != '' ) { $address_to_geocode[] = $property['details']['address']['town']; }
								if ( isset($property['details']['address']['county']) && trim($property['details']['address']['county']) != '' ) { $address_to_geocode[] = $property['details']['address']['county']; }
								if ( isset($property['details']['address']['postcode']) && trim($property['details']['address']['postcode']) != '' ) { $address_to_geocode[] = $property['details']['address']['postcode']; }
								
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
							        	$this->add_error( 'Google Geocoding service returned status ' . $status, $property['listingId'] );
							        	sleep(3);
							        }
							    }
						        else
						        {
						        	$this->add_error( 'Failed to parse XML response from Google Geocoding service.', $property['listingId'] );
						        }
							}
							else
					        {
					        	$this->add_error( 'Failed to obtain co-ordinates as allow_url_fopen setting is disabled', $property['listingId'] );
					        }
					    }
					    else
					    {
					    	$this->add_log( 'Not performing Google Geocoding request as no API key present in settings', $property['listingId'] );
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
					$department = 'residential-sales';
					update_post_meta( $post_id, '_department', $department );
					
					update_post_meta( $post_id, '_bedrooms', ( ( isset($property['details']['bedrooms']) ) ? $property['details']['bedrooms'] : '' ) );
					update_post_meta( $post_id, '_bathrooms', ( ( isset($property['details']['bathrooms']) ) ? $property['details']['bathrooms'] : '' ) );
					update_post_meta( $post_id, '_reception_rooms', ( ( isset($property['details']['receptionRooms']) ) ? $property['details']['receptionRooms'] : '' ) );

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

					if ( isset($property['details']['propertyType']) )
					{
						if ( !empty($mapping) && isset($mapping[$property['details']['propertyType']]) )
						{
							wp_set_post_terms( $post_id, $mapping[$property['details']['propertyType']], $prefix . 'property_type' );
						}
						else
						{
							$this->add_log( 'Property received with a type (' . $property['details']['propertyType'] . ') that is not mapped', $property['listingId'] );

							$options = $this->add_missing_mapping( $mapping, $prefix . 'property_type', $property['details']['propertyType'], $import_id );
						}
					}

					// Residential Sales Details
					if ( $department == 'residential-sales' )
					{
						// Clean price
						$price = round(preg_replace("/[^0-9.]/", '', $property['details']['price']));

						update_post_meta( $post_id, '_price', $price );
						update_post_meta( $post_id, '_price_actual', $price );
						update_post_meta( $post_id, '_poa', ( ( isset($property['details']['priceQualifier']) && $property['details']['priceQualifier'] == '5' ) ? 'yes' : '') );
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
						if ( !empty($mapping) && isset($property['details']['priceQualifier']) && isset($mapping[$property['details']['priceQualifier']]) )
						{
			                wp_set_post_terms( $post_id, $mapping[$property['details']['priceQualifier']], 'price_qualifier' );
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

					// Marketing
					update_post_meta( $post_id, '_on_market', 'yes' );
					add_post_meta( $post_id, '_featured', '' );

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
					if ( !empty($mapping) && isset($property['details']['status']) && isset($mapping[$property['details']['status']]) )
					{
		                wp_set_post_terms( $post_id, $mapping[$property['details']['status']], 'availability' );
		            }

		            // Features
					$features = array();
					if ( isset($property['details']['features']) && is_array($property['details']['features']) && !empty($property['details']['features']) )
					{
						foreach ( $property['details']['features'] as $feature )
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
			        // For now put the whole description in one room / description
					update_post_meta( $post_id, '_rooms', '1' );
					update_post_meta( $post_id, '_room_name_0', '' );
		            update_post_meta( $post_id, '_room_dimensions_0', '' );
		            update_post_meta( $post_id, '_room_description_0', str_replace(array("\r\n", "\n"), "", $property['details']['fullDescription']) );
					
					// Media - Images
					if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
        			{
        				$media_urls = array();
        				if ( isset($property['details']['media']) && is_array($property['details']['media']) && !empty($property['details']['media']) )
						{
							foreach ( $property['details']['media'] as $image )
							{
								if ( 
									isset($image['url']) && $image['url'] != ''
									&&
									(
										substr( strtolower($image['url']), 0, 2 ) == '//' || 
										substr( strtolower($image['url']), 0, 4 ) == 'http'
									)
									&&
									isset($image['mediaType']) && $image['mediaType'] == '1'
								)
								{
									// This is a URL
									$url = $image['url'] . '-big.jpg';

									$media_urls[] = array('url' => $url);
								}
							}
						}
						update_post_meta( $post_id, '_photo_urls', $media_urls );

						$this->add_log( 'Imported ' . count($media_urls) . ' photo URLs', $property['listingId'] );
        			}
        			else
	        		{
						$media_ids = array();
						$new = 0;
						$existing = 0;
						$deleted = 0;
						$previous_media_ids = get_post_meta( $post_id, '_photos', TRUE );

						if ( isset($property['details']['media']) && is_array($property['details']['media']) && !empty($property['details']['media']) )
						{
							foreach ( $property['details']['media'] as $image )
							{
								if ( 
									isset($image['url']) && $image['url'] != ''
									&&
									(
										substr( strtolower($image['url']), 0, 2 ) == '//' || 
										substr( strtolower($image['url']), 0, 4 ) == 'http'
									)
									&&
									isset($image['mediaType']) && $image['mediaType'] == '1'
								)
								{
									// This is a URL
									$url = $image['url'];
									$description = '';
									$modified = $image['dateUpdated'];
								    
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
									    $tmp = download_url( $url . '-big.jpg' );

									    $file_array = array(
									        'name' => $filename,
									        'tmp_name' => $tmp
									    );

									    // Check for download errors
									    if ( is_wp_error( $tmp ) ) 
									    {
									        @unlink( $file_array[ 'tmp_name' ] );

									        $this->add_error( 'An error occurred whilst importing ' . $url . '-big.jpg. The error was as follows: ' . $tmp->get_error_message(), $property['listingId'] );
									    }
									    else
									    {
										    $id = media_handle_sideload( $file_array, $post_id, $description );

										    // Check for handle sideload errors.
										    if ( is_wp_error( $id ) ) 
										    {
										        @unlink( $file_array['tmp_name'] );
										        
										        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '-big.jpg. The error was as follows: ' . $id->get_error_message(), $property['listingId'] );
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

						$this->add_log( 'Imported ' . count($media_ids) . ' photos (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property['listingId'] );
					}

					// Media - Floorplans
					if ( get_option('propertyhive_floorplans_stored_as', '') == 'urls' )
        			{
        				$media_urls = array();
        				if ( isset($property['details']['media']) && is_array($property['details']['media']) && !empty($property['details']['media']) )
						{
							foreach ( $property['details']['media'] as $image )
							{
								if ( 
									isset($image['url']) && $image['url'] != ''
									&&
									(
										substr( strtolower($image['url']), 0, 2 ) == '//' || 
										substr( strtolower($image['url']), 0, 4 ) == 'http'
									)
									&&
									isset($image['mediaType']) && $image['mediaType'] == '2'
								)
								{
									// This is a URL
									$url = $image['url'];

									$media_urls[] = array('url' => $url);
								}
							}
						}
						update_post_meta( $post_id, '_floorplan_urls', $media_urls );

						$this->add_log( 'Imported ' . count($media_urls) . ' floorplan URLs', $property['listingId'] );
        			}
        			else
        			{
						$media_ids = array();
						$new = 0;
						$existing = 0;
						$deleted = 0;
						$previous_media_ids = get_post_meta( $post_id, '_floorplans', TRUE );

						if ( isset($property['details']['media']) && is_array($property['details']['media']) && !empty($property['details']['media']) )
						{
							foreach ( $property['details']['media'] as $image )
							{
								if ( 
									isset($image['url']) && $image['url'] != ''
									&&
									(
										substr( strtolower($image['url']), 0, 2 ) == '//' || 
										substr( strtolower($image['url']), 0, 4 ) == 'http'
									)
									&&
									isset($image['mediaType']) && $image['mediaType'] == '2'
								)
								{
									// This is a URL
									$url = $image['url'];
									$description = '';
									$modified = $image['dateUpdated'];
								    
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

									        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['listingId'] );
									    }
									    else
									    {
										    $id = media_handle_sideload( $file_array, $post_id, $description );

										    // Check for handle sideload errors.
										    if ( is_wp_error( $id ) ) 
										    {
										        @unlink( $file_array['tmp_name'] );
										        
										        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['listingId'] );
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

						$this->add_log( 'Imported ' . count($media_ids) . ' floorplans (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property['listingId'] );
					}

					// Media - Brochures
					if ( get_option('propertyhive_brochures_stored_as', '') == 'urls' )
        			{
        				$media_urls = array();
        				if ( isset($property['details']['media']) && is_array($property['details']['media']) && !empty($property['details']['media']) )
						{
							foreach ( $property['details']['media'] as $image )
							{
								if ( 
									isset($image['url']) && $image['url'] != ''
									&&
									(
										substr( strtolower($image['url']), 0, 2 ) == '//' || 
										substr( strtolower($image['url']), 0, 4 ) == 'http'
									)
									&&
									isset($image['mediaType']) && $image['mediaType'] == '4'
								)
								{
									// This is a URL
									$url = $image['url'];

									$media_urls[] = array('url' => $url);
								}
							}
						}
						update_post_meta( $post_id, '_brochure_urls', $media_urls );

						$this->add_log( 'Imported ' . count($media_urls) . ' brochure URLs', $property['listingId'] );
        			}
        			else
        			{
						$media_ids = array();
						$new = 0;
						$existing = 0;
						$deleted = 0;
						$previous_media_ids = get_post_meta( $post_id, '_brochures', TRUE );

						if ( isset($property['details']['media']) && is_array($property['details']['media']) && !empty($property['details']['media']) )
						{
							foreach ( $property['details']['media'] as $image )
							{
								if ( 
									isset($image['url']) && $image['url'] != ''
									&&
									(
										substr( strtolower($image['url']), 0, 2 ) == '//' || 
										substr( strtolower($image['url']), 0, 4 ) == 'http'
									)
									&&
									isset($image['mediaType']) && $image['mediaType'] == '4'
								)
								{
									// This is a URL
									$url = $image['url'];
									$description = '';
									$modified = $image['dateUpdated'];
								    
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

									        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['listingId'] );
									    }
									    else
									    {
										    $id = media_handle_sideload( $file_array, $post_id, $description );

										    // Check for handle sideload errors.
										    if ( is_wp_error( $id ) ) 
										    {
										        @unlink( $file_array['tmp_name'] );
										        
										        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['listingId'] );
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

						$this->add_log( 'Imported ' . count($media_ids) . ' brochures (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property['listingId'] );
					}

					// Media - EPCs
					if ( get_option('propertyhive_epcs_stored_as', '') == 'urls' )
        			{
        				$media_urls = array();
        				if ( isset($property['details']['media']) && is_array($property['details']['media']) && !empty($property['details']['media']) )
						{
							foreach ( $property['details']['media'] as $image )
							{
								if ( 
									isset($image['url']) && $image['url'] != ''
									&&
									(
										substr( strtolower($image['url']), 0, 2 ) == '//' || 
										substr( strtolower($image['url']), 0, 4 ) == 'http'
									)
									&&
									isset($image['mediaType']) && $image['mediaType'] == '3'
								)
								{
									// This is a URL
									$url = $image['url'];

									$media_urls[] = array('url' => $url);
								}
							}
						}
						update_post_meta( $post_id, '_epc_urls', $media_urls );

						$this->add_log( 'Imported ' . count($media_urls) . ' EPC URLs', $property['listingId'] );
        			}
        			else
        			{
						$media_ids = array();
						$new = 0;
						$existing = 0;
						$deleted = 0;
						$previous_media_ids = get_post_meta( $post_id, '_epcs', TRUE );
						
						if ( isset($property['details']['media']) && is_array($property['details']['media']) && !empty($property['details']['media']) )
						{
							foreach ( $property['details']['media'] as $image )
							{
								if ( 
									isset($image['url']) && $image['url'] != ''
									&&
									(
										substr( strtolower($image['url']), 0, 2 ) == '//' || 
										substr( strtolower($image['url']), 0, 4 ) == 'http'
									)
									&&
									isset($image['mediaType']) && $image['mediaType'] == '3'
								)
								{
									// This is a URL
									$url = $image['url'];
									$description = '';
									$modified = $image['dateUpdated'];
								    
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

									        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['listingId'] );
									    }
									    else
									    {
										    $id = media_handle_sideload( $file_array, $post_id, $description );

										    // Check for handle sideload errors.
										    if ( is_wp_error( $id ) ) 
										    {
										        @unlink( $file_array['tmp_name'] );
										        
										        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['listingId'] );
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

						$this->add_log( 'Imported ' . count($media_ids) . ' epcs (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property['listingId'] );
					}

					// Media - Virtual Tours
					$virtual_tours = array();
					if ( isset($property['details']['media']) && is_array($property['details']['media']) && !empty($property['details']['media']) )
					{
						foreach ( $property['details']['media'] as $image )
						{
							if ( 
								isset($image['url']) && $image['url'] != ''
								&&
								(
									substr( strtolower($image['url']), 0, 2 ) == '//' || 
									substr( strtolower($image['url']), 0, 4 ) == 'http'
								)
								&&
								isset($image['mediaType']) && ( $image['mediaType'] == '5' || $image['mediaType'] == '6' )
							)
							{
								// This is a URL
								$url = $image['url'];

								$virtual_tours[] = $url;
							}
						}
					}
					if ( isset($property['details']['propertyAttributes']) && is_array($property['details']['propertyAttributes']) && !empty($property['details']['propertyAttributes']) )
					{
						foreach ( $property['details']['propertyAttributes'] as $image )
						{
							if ( 
								isset($image['id']) && $image['id'] == '28'
								&&
								isset($image['valueText']) && $image['valueText'] != ''
								&&
								(
									substr( strtolower($image['valueText']), 0, 2 ) == '//' || 
									substr( strtolower($image['valueText']), 0, 4 ) == 'http'
								)
							)
							{
								// This is a URL
								$url = $image['valueText'];

								$virtual_tours[] = $url;
							}
						}
					}

	                update_post_meta( $post_id, '_virtual_tours', count($virtual_tours) );
	                foreach ( $virtual_tours as $i => $virtual_tour )
	                {
	                	update_post_meta( $post_id, '_virtual_tour_' . $i, (string)$virtual_tour );
	                }

					$this->add_log( 'Imported ' . count($virtual_tours) . ' virtual tours', $property['listingId'] );

					do_action( "propertyhive_property_imported_loop_json", $post_id, $property );

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
								$this->add_log( 'New meta data for ' . trim($key, '_') . ': ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), $property['listingId'] );
							}
							elseif ( $metadata_before[$key] != $metadata_after[$key] )
							{
								$this->add_log( 'Updated ' . trim($key, '_') . '. Before: ' . ( ( is_array($metadata_before[$key]) ) ? implode(", ", $metadata_before[$key]) : $metadata_before[$key] ) . ', After: ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), $property['listingId'] );
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
								$this->add_log( 'New taxonomy data for ' . $taxonomy_name . ': ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), $property['listingId'] );
							}
							elseif ( $taxonomy_terms_before[$taxonomy_name] != $taxonomy_terms_after[$taxonomy_name] )
							{
								$this->add_log( 'Updated ' . $taxonomy_name . '. Before: ' . ( ( is_array($taxonomy_terms_before[$taxonomy_name]) ) ? implode(", ", $taxonomy_terms_before[$taxonomy_name]) : $taxonomy_terms_before[$taxonomy_name] ) . ', After: ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), $property['listingId'] );
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

		do_action( "propertyhive_post_import_properties_loop_json" );

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
				$import_refs[] = $property['listingId'];
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

					do_action( "propertyhive_property_removed_loop_json", $post->ID );
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
		$mapping_values = $this->get_loop_mapping_values('availability');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['availability'][$mapping_value] = '';
			}
		}

		$mapping_values = $this->get_loop_mapping_values('property_type');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['property_type'][$mapping_value] = '';
			}
		}

		$mapping_values = $this->get_loop_mapping_values('price_qualifier');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['price_qualifier'][$mapping_value] = '';
			}
		}

		$mapping_values = $this->get_loop_mapping_values('office');
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
		return $this->get_loop_mapping_values($custom_field);
	}

	public function get_loop_mapping_values($custom_field) 
	{
        if ($custom_field == 'availability')
        {
            return array(
            	'2' => 'For Sale',
                '3' => 'Under Offer',
                '4' => 'Sold',
            );
        }
        if ($custom_field == 'property_type')
        {
        	return array(
                '0' => 'Unknown',
				'1' => 'Terraced',
				'2' => 'EndOfTerrace',
				'3' => 'SemiDetached',
				'4' => 'Detached',
				'5' => 'MewsHouse',
				'6' => 'Flat',
				'7' => 'Maisonette',
				'8' => 'Bungalow',
				'9' => 'TownHouse',
				'10' => 'Cottage',
				'11' => 'FarmOrBarn',
				'12' => 'MobileOrStatic',
				'13' => 'Land',
				'14' => 'Studio',
				'15' => 'BlockOfFlats',
				'16' => 'Office',
				'17' => 'CountryHouse',
            );
        }
        if ($custom_field == 'price_qualifier')
        {
        	return array(
        		'0' => 'None',
                '1' => 'OffersOver',
                '2' => 'OffersInRegionOf',
                '3' => 'PartBuyPartRent',
                '4' => 'ComingSoon',
                '5' => 'POA',
                '6' => 'From',
                '7' => 'FixedPrice',
                '8' => 'PriceOnRequest',
                '9' => 'SharedEquity',
                '10' => 'SharedOwenrship',
                '11' => 'GuidePrice',
                '12' => 'SaleByTender',
                '13' => 'SoldPrice',
                '14' => 'SoldSTC',
                '15' => 'Withdrawn',
        	);
        }
    }
}

}