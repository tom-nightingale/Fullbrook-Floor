<?php
/**
 * Class for managing the import process of a PropertyADD XML file
 *
 * @package WordPress
 */
if ( class_exists( 'PH_Property_Import_Process' ) ) {

class PH_PropertyADD_XML_Import extends PH_Property_Import_Process {

	public function __construct( $instance_id = '' ) 
	{
		$this->instance_id = $instance_id;

		if ( $this->instance_id != '' && isset($_GET['custom_property_import_cron']) )
	    {
	    	$current_user = wp_get_current_user();

	    	$this->add_log("Executed manually by " . ( ( isset($current_user->display_name) ) ? $current_user->display_name : '' ) );
	    }

	    define('ALLOW_UNFILTERED_UPLOADS', true);
	}

	public function parse( $options = array() )
	{
		$this->properties = array(); // Reset properties in the event we're importing multiple files

		$xml = simplexml_load_file( $options['url'] . '/property-ajaxsearch.aspx?mode=fulldetails&roomdetail=1' );

		if ($xml !== FALSE)
		{
			$this->add_log("Parsing sales properties");
			
			foreach ($xml->Property as $property)
			{
                $this->properties[] = $property;
            } // end foreach property
        }
        else
        {
        	// Failed to parse XML
        	$this->add_error( 'Failed to parse sales XML file. Possibly invalid XML' );
        	return false;
        }

        $xml = simplexml_load_file( $options['url'] . '/property-ajaxsearch.aspx?mode=fulllettingsdetails&roomdetail=1' );

		if ($xml !== FALSE)
		{
			$this->add_log("Parsing lettings properties");
			
			foreach ($xml->Property as $property)
			{
                $this->properties[] = $property;
            } // end foreach property
        }
        else
        {
        	// Failed to parse XML
        	$this->add_error( 'Failed to parse lettings XML file. Possibly invalid XML' );
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

        do_action( "propertyhive_pre_import_properties_propertyadd_xml", $this->properties );
        $this->properties = apply_filters( "propertyhive_propertyadd_xml_properties_due_import", $this->properties );

		$this->add_log( 'Beginning to loop through ' . count($this->properties) . ' properties' );

		$property_row = 1;
		foreach ( $this->properties as $property )
		{
			$this->add_log( 'Importing property ' . $property_row .' with reference ' . (string)$property->Property_ID, (string)$property->Property_ID );

			$inserted_updated = false;

			$display_address = (string)$property->Property_MarketAddress;

			$args = array(
	            'post_type' => 'property',
	            'posts_per_page' => 1,
	            'post_status' => 'any',
	            'meta_query' => array(
	            	array(
		            	'key' => $imported_ref_key,
		            	'value' => (string)$property->Property_ID
		            )
	            )
	        );
	        $property_query = new WP_Query($args);
	        
	        if ($property_query->have_posts())
	        {
	        	$this->add_log( 'This property has been imported before. Updating it', (string)$property->Property_ID );

	        	// We've imported this property before
	            while ($property_query->have_posts())
	            {
	                $property_query->the_post();

	                $post_id = get_the_ID();

	                $my_post = array(
				    	'ID'          	 => $post_id,
				    	'post_title'     => wp_strip_all_tags( $display_address ),
				    	'post_excerpt'   => html_entity_decode(html_entity_decode((string)$property->Property_ShortMarketingText)),
				    	'post_content' 	 => '',
				    	'post_status'    => 'publish',
				  	);

				 	// Update the post into the database
				    $post_id = wp_update_post( $my_post );

				    if ( is_wp_error( $post_id ) ) 
					{
						$this->add_error( 'ERROR: Failed to update post. The error was as follows: ' . $post_id->get_error_message(), (string)$property->Property_ID );
					}
					else
					{
						$inserted_updated = 'updated';
					}
	            }
	        }
	        else
	        {
	        	$this->add_log( 'This property hasn\'t been imported before. Inserting it', (string)$property->Property_ID );

	        	// We've not imported this property before
				$postdata = array(
					'post_excerpt'   => html_entity_decode(html_entity_decode((string)$property->Property_ShortMarketingText)),
					'post_content' 	 => '',
					'post_title'     => wp_strip_all_tags( $display_address ),
					'post_status'    => 'publish',
					'post_type'      => 'property',
					'comment_status' => 'closed',
				);

				$post_id = wp_insert_post( $postdata, true );

				if ( is_wp_error( $post_id ) ) 
				{
					$this->add_error( 'Failed to insert post. The error was as follows: ' . $post_id->get_error_message(), (string)$property->Property_ID );
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
					($display_address != '' || html_entity_decode(html_entity_decode((string)$property->Property_ShortMarketingText)) != '')
				)
				{
					$my_post = array(
				    	'ID'          	 => $post_id,
				    	'post_title'     => htmlentities(mb_convert_encoding(wp_strip_all_tags( $display_address ), 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
				    	'post_excerpt'   => htmlentities(mb_convert_encoding(html_entity_decode(html_entity_decode((string)$property->Property_ShortMarketingText)), 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
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

				$this->add_log( 'Successfully ' . $inserted_updated . ' post. The post ID is ' . $post_id, (string)$property->Property_ID );

				update_post_meta( $post_id, $imported_ref_key, (string)$property->Property_ID );

				// Address
				update_post_meta( $post_id, '_reference_number', (string)$property->Property_ID );

				$explode_market_address = explode(",", (string)$property->Property_MarketAddress);
				array_pop($explode_market_address); // remove last part of address. Likely to be postcode. Could maybe be done better to actually detect if last part is a postcode or not
				update_post_meta( $post_id, '_address_name_number', '' );
				update_post_meta( $post_id, '_address_street', ( isset($explode_market_address[0]) ? $explode_market_address[0] : '' ) );
				update_post_meta( $post_id, '_address_two', ( isset($explode_market_address[1]) ? $explode_market_address[1] : '' ) );
				update_post_meta( $post_id, '_address_three', ( isset($explode_market_address[2]) ? $explode_market_address[2] : '' ) );
				update_post_meta( $post_id, '_address_four', ( isset($explode_market_address[3]) ? $explode_market_address[3] : '' ) );
				update_post_meta( $post_id, '_address_postcode', (string)$property->Property_PostCode );

				$country = 'GB';
				update_post_meta( $post_id, '_address_country', $country );

				// Check main address fields and see if this location exists as a taxonomy to try and assign properties to location
				$address_fields_to_check = explode(",", $display_address);
				$location_term_ids = array();

				foreach ( $address_fields_to_check as $address_field )
				{
					if ( isset($address_field) && trim($address_field) != '' ) 
					{
						$term = term_exists( trim($address_field), 'location');
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
				if ( isset($property->Property_Latitude) && isset($property->Property_Longitude) && (string)$property->Property_Latitude != '' && (string)$property->Property_Longitude != '' && (string)$property->Property_Latitude != '0' && (string)$property->Property_Longitude != '0' )
				{
					update_post_meta( $post_id, '_latitude', trim((string)$property->Property_Latitude) );
					update_post_meta( $post_id, '_longitude', trim((string)$property->Property_Longitude) );
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
								$address_to_geocode = array((string)$property->Property_MarketAddress);

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
							        	$this->add_error( 'Google Geocoding service returned status ' . $status, (string)$property->Property_ID );
							        	sleep(3);
							        }
							    }
							    else
						        {
						        	$this->add_error( 'Failed to parse XML response from Google Geocoding service.', (string)$property->Property_ID );
						        }
							}
							else
					        {
					        	$this->add_error( 'Failed to obtain co-ordinates as allow_url_fopen setting is disabled', (string)$property->Property_ID );
					        }
					    }
					    else
					    {
					    	$this->add_log( 'Not performing Google Geocoding request as no API key present in settings', (string)$property->Property_ID );
					    }
					}
				}

				// Owner
				add_post_meta( $post_id, '_owner_contact_id', '', true );

				// Record Details
				add_post_meta( $post_id, '_negotiator_id', get_current_user_id(), true );
					
				$office_id = $primary_office_id;
				if ( isset($_POST['mapped_office'][(string)$property->Property_BranchID]) && $_POST['mapped_office'][(string)$property->Property_BranchID] != '' )
				{
					$office_id = $_POST['mapped_office'][(string)$property->Property_BranchID];
				}
				elseif ( isset($options['offices']) && is_array($options['offices']) && !empty($options['offices']) )
				{
					foreach ( $options['offices'] as $ph_office_id => $branch_code )
					{
						if ( $branch_code == (string)$property->Property_BranchID )
						{
							$office_id = $ph_office_id;
							break;
						}
					}
				}
				update_post_meta( $post_id, '_office_id', $office_id );

				// Residential Details
				update_post_meta( $post_id, '_department', ( ((string)$property->Property_Basis != 'For Sale') ? 'residential-lettings' : 'residential-sales' ) );
				update_post_meta( $post_id, '_bedrooms', ( ( isset($property->Property_Bedrooms) ) ? round((string)$property->Property_Bedrooms) : '' ) );
				update_post_meta( $post_id, '_bathrooms', ( ( isset($property->Property_Bathrooms) ) ? round((string)$property->Property_Bathrooms) : '' ) );
				update_post_meta( $post_id, '_reception_rooms', ( ( isset($property->Property_ReceptionRooms) ) ? round((string)$property->Property_ReceptionRooms) : '' ) );

				if ( isset($_POST['mapped_property_type']) )
				{
					$mapping = $_POST['mapped_property_type'];
				}
				else
				{
					$mapping = isset($options['mappings']['property_type']) ? $options['mappings']['property_type'] : array();
				}

				wp_delete_object_term_relationships( $post_id, 'property_type' );

				if ( isset($property->Property_Type) )
				{
					if ( !empty($mapping) && isset($mapping[(string)$property->Property_Type]) )
					{
						wp_set_post_terms( $post_id, $mapping[(string)$property->Property_Type], 'property_type' );
					}
					else
					{
						$this->add_log( 'Property received with a type (' . (string)$property->Property_Type . ') that is not mapped', (string)$property->Property_ID );

						$options = $this->add_missing_mapping( $mapping, 'property_type', (string)$property->Property_Type, $import_id );
					}
				}

				// Residential Sales Details
				if ( (string)$property->Property_Basis == 'For Sale' )
				{
					// Clean price
					$price = round(preg_replace("/[^0-9.]/", '', (string)$property->Property_PlainPrice));

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
					if ( !empty($mapping) && isset($property->Property_PriceQualifier) && isset($mapping[trim((string)$property->Property_PriceQualifier)]) )
					{
		                wp_set_post_terms( $post_id, $mapping[trim((string)$property->Property_PriceQualifier)], 'price_qualifier' );
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
					if ( !empty($mapping) && isset($property->Property_Tenure) && isset($mapping[(string)$property->Property_Tenure]) )
					{
			            wp_set_post_terms( $post_id, $mapping[(string)$property->Property_Tenure], 'tenure' );
		            }
				}
				else
				{
					// Clean price
					$price = round(preg_replace("/[^0-9.]/", '', (string)$property->Property_PlainPrice));

					update_post_meta( $post_id, '_rent', $price );

					$rent_frequency = 'pcm';
					if ( isset($property->Property_PriceBasis) && in_array((string)$property->Property_PriceBasis, array('PCM', 'PW', 'PPPW', 'PQ', 'PA')) )
					{
						$rent_frequency = strtolower($property->Property_PriceBasis);
					}
					$price_actual = $price;
					update_post_meta( $post_id, '_rent_frequency', $rent_frequency );
					update_post_meta( $post_id, '_price_actual', $price_actual );
					update_post_meta( $post_id, '_currency', 'GBP' );
					
					update_post_meta( $post_id, '_poa', '' );

					update_post_meta( $post_id, '_deposit', (string)$property->Property_PlainDeposit);
					$available_date = '';
					if ( isset($property->Property_AvailableDate) && (string)$property->Property_AvailableDate != '' )
					{
						$available_date = (string)$property->Property_AvailableDate;
					}
            		update_post_meta( $post_id, '_available_date', $available_date );

            		// Furnished - not provided in XML
            		if ( isset($_POST['mapped_furnished']) )
					{
						$mapping = $_POST['mapped_furnished'];
					}
					else
					{
						$mapping = isset($options['mappings']['furnished']) ? $options['mappings']['furnished'] : array();
					}

            		wp_delete_object_term_relationships( $post_id, 'furnished' );
					if ( !empty($mapping) && isset($property->Property_FurnishBasis) && isset($mapping[round((string)$property->Property_FurnishBasis)]) )
					{
		                wp_set_post_terms( $post_id, $mapping[round((string)$property->Property_FurnishBasis)], 'furnished' );
		            }
		        }			

				// Marketing
				update_post_meta( $post_id, '_on_market', 'yes' );
				update_post_meta( $post_id, '_featured', ( isset($property->Property_Featured) && strtolower((string)$property->Property_Featured) == 'True' ) ? 'yes' : '' );

				// Availability
				if ( isset($_POST['mapped_availability']) )
				{
					$mapping = $_POST['mapped_availability'];
				}
				else
				{
					$mapping = isset($options['mappings']['availability']) ? $options['mappings']['availability'] : array();
				}

        		wp_delete_object_term_relationships( $post_id, 'availability' );
				if ( !empty($mapping) && isset($property->PropertyStatus_Desc) && isset($mapping[(string)$property->PropertyStatus_Desc]) )
				{
	                wp_set_post_terms( $post_id, $mapping[(string)$property->PropertyStatus_Desc], 'availability' );
	            }

	            // Features
				$features = array();
				if ( isset($property->SalesPoints->SalesPoint) && !empty($property->SalesPoints->SalesPoint) )
				{
					foreach ( $property->SalesPoints->SalesPoint as $bulletpoint )
					{
						$features[] = (string)$bulletpoint->Feature_Desc;
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
	            if ( (string)$property->Property_LongMarketingText != '' )
	            {
	            	update_post_meta( $post_id, '_room_name_' . $num_rooms, '' );
		            update_post_meta( $post_id, '_room_dimensions_' . $num_rooms, '' );
		            update_post_meta( $post_id, '_room_description_' . $num_rooms, html_entity_decode(html_entity_decode((string)$property->Property_LongMarketingText)) );

	            	++$num_rooms;
	            }

	            if ( isset($property->Rooms->Room) && !empty($property->Rooms->Room) )
				{
	            	foreach ( $property->Rooms->Room as $room )
	            	{
	            		update_post_meta( $post_id, '_room_name_' . $num_rooms, (string)$room->Room_Desc );
			            update_post_meta( $post_id, '_room_dimensions_' . $num_rooms, (string)$room->Room_Measurements );
			            update_post_meta( $post_id, '_room_description_' . $num_rooms, (string)$room->Room_Notes );

		            	++$num_rooms;
	            	}
	            }

	            update_post_meta( $post_id, '_rooms', $num_rooms );

	            // Media - Images
	            if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();
    				if (isset($property->Images->Image) && !empty($property->Images->Image))
	                {
	                    foreach ($property->Images->Image as $image)
	                    {
	                    	if ( 
								substr( strtolower((string)$image->Image_Url), 0, 2 ) == '//' || 
								substr( strtolower((string)$image->Image_Url), 0, 4 ) == 'http'
							)
							{
								// This is a URL
								$url = (string)$image->Image_Url;

								$media_urls[] = array('url' => $url);
							}
						}
					}
					update_post_meta( $post_id, '_photo_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' photo URLs', (string)$property->Property_ID );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_photos', TRUE );
					$i = 0;
					if (isset($property->Images->Image) && !empty($property->Images->Image))
	                {
	                    foreach ($property->Images->Image as $image)
	                    {
	                    	if ( 
								substr( strtolower((string)$image->Image_Url), 0, 2 ) == '//' || 
								substr( strtolower((string)$image->Image_Url), 0, 4 ) == 'http'
							)
							{
								// This is a URL
								$url = (string)$image->Image_Url;
								$description = (string)$image->Image_Description;

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

								        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), (string)$property->Property_ID );
								    }
								    else
								    {
									    $id = media_handle_sideload( $file_array, $post_id, $description, array('post_title' => $filename) );

									    // Check for handle sideload errors.
									    if ( is_wp_error( $id ) ) 
									    {
									        @unlink( $file_array['tmp_name'] );

									        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), (string)$property->Property_ID );
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

					$this->add_log( 'Imported ' . count($media_ids) . ' photos (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->Property_ID );
				}

				// Media - Floorplans
				if ( get_option('propertyhive_floorplans_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();
    				if (isset($property->Floorplans->FloorPlan) && !empty($property->Floorplans->FloorPlan))
	                {
	                    foreach ($property->Floorplans->FloorPlan as $floorPlan)
	                    {
							if ( 
								substr( strtolower((string)$floorPlan->FloorPlan_Url), 0, 2 ) == '//' || 
								substr( strtolower((string)$floorPlan->FloorPlan_Url), 0, 4 ) == 'http'
							)
							{
								// This is a URL
								$url = (string)$floorPlan->FloorPlan_Url;

								$media_urls[] = array('url' => $url);
							}
						}
					}
					update_post_meta( $post_id, '_floorplan_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' floorplan URLs', (string)$property->Property_ID );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_floorplans', TRUE );
					$i = 0;
					if (isset($property->Floorplans->FloorPlan) && !empty($property->Floorplans->FloorPlan))
	                {
	                    foreach ($property->Floorplans->FloorPlan as $floorPlan)
	                    {
							if ( 
								substr( strtolower((string)$floorPlan->FloorPlan_Url), 0, 2 ) == '//' || 
								substr( strtolower((string)$floorPlan->FloorPlan_Url), 0, 4 ) == 'http'
							)
							{
								// This is a URL
								$url = (string)$floorPlan->FloorPlan_Url;
								$description = (string)$floorPlan->FloorPlan_Description;

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

								        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), (string)$property->Property_ID );
								    }
								    else
								    {
									    $id = media_handle_sideload( $file_array, $post_id, $description, array('post_title' => $filename) );

									    // Check for handle sideload errors.
									    if ( is_wp_error( $id ) ) 
									    {
									        @unlink( $file_array['tmp_name'] );
									        
									        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), (string)$property->Property_ID );
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

					$this->add_log( 'Imported ' . count($media_ids) . ' floorplans (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->Property_ID );
				}
				
				// Media - Brochures
				/*$media_ids = array();
				$new = 0;
				$existing = 0;
				$deleted = 0;
				$previous_media_ids = get_post_meta( $post_id, '_brochures', TRUE );
				$i = 0;
				if (isset($property->Brochure) && (string)$property->Brochure != '')
                {
                    $media_file_name = (string)$property->Brochure;
					$media_folder = dirname( $this->target_file );
					$description = '';

					if ( file_exists( $media_folder . '/' . $media_file_name ) )
					{
						$upload = true;
                        $replacing_attachment_id = '';
                        if ( isset($previous_media_ids[$i]) ) 
                        {                                    
                            // get this attachment
                            $current_image_path = get_post_meta( $previous_media_ids[$i], '_imported_path', TRUE );
                            $current_image_size = filesize( $current_image_path );
                            
                            if ($current_image_size > 0 && $current_image_size !== FALSE)
                            {
                                $replacing_attachment_id = $previous_media_ids[$i];
                                
                                $new_image_size = filesize( $media_folder . '/' . $media_file_name );
                                
                                if ($new_image_size > 0 && $new_image_size !== FALSE)
                                {
                                    if ($current_image_size == $new_image_size)
                                    {
                                        $upload = false;
                                    }
                                    else
                                    {
                                        
                                    }
                                }
                                else
                                {
                                	$this->add_error( 'Failed to get filesize of new brochure file ' . $media_folder . '/' . $media_file_name, (string)$property->Property_ID );
                                }
                                
                                unset($new_image_size);
                            }
                            else
                            {
                            	$this->add_error( 'Failed to get filesize of brochure file ' . $current_image_path, (string)$property->Property_ID );
                            }
                            
                            unset($current_image_size);
                        }

                        if ($upload)
                        {
							// We've physically received the file
							$upload = wp_upload_bits(trim($media_file_name, '_'), null, file_get_contents($media_folder . '/' . $media_file_name));  
                            
                            if( isset($upload['error']) && $upload['error'] !== FALSE )
                            {
                            	$this->add_error( print_r($upload['error'], TRUE), (string)$property->Property_ID );
                            }
                            else
                            {
                            	// We don't already have a thumbnail and we're presented with an image
                                $wp_filetype = wp_check_filetype( $upload['file'], null );
                            
                                $attachment = array(
                                     //'guid' => $wp_upload_dir['url'] . '/' . trim($media_file_name, '_'), 
                                     'post_mime_type' => $wp_filetype['type'],
                                     'post_title' => ( ( $description != '' ) ? $description : preg_replace('/\.[^.]+$/', '', trim($media_file_name, '_')) ),
                                     'post_content' => '',
                                     'post_status' => 'inherit'
                                );
                                $attach_id = wp_insert_attachment( $attachment, $upload['file'], $post_id );
                                
                                if ( $attach_id === FALSE || $attach_id == 0 )
                                {    
                                	$this->add_error( 'Failed inserting brochure attachment ' . $upload['file'] . ' - ' . print_r($attachment, TRUE), (string)$property->Property_ID );
                                }
                                else
                                {  
                                    $attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
                                    wp_update_attachment_metadata( $attach_id,  $attach_data );

                                	update_post_meta( $attach_id, '_imported_path', $upload['file']);

                                	$media_ids[] = $attach_id;

                                	++$new;
                                }
                            }
                        }
                        else
                        {
                        	if ( isset($previous_media_ids[$i]) ) 
                        	{
                        		$media_ids[] = $previous_media_ids[$i];

                        		if ( $description != '' )
								{
									$my_post = array(
								    	'ID'          	 => $previous_media_ids[$i],
								    	'post_title'     => $description,
								    );

								 	// Update the post into the database
								    wp_update_post( $my_post );
								}

								++$existing;
                        	}
                        }

                        unlink($media_folder . '/' . $media_file_name);
					}
					else
					{
						if ( isset($previous_media_ids[$i]) ) 
                    	{
                    		$media_ids[] = $previous_media_ids[$i];

                    		if ( $description != '' )
							{
								$my_post = array(
							    	'ID'          	 => $previous_media_ids[$i],
							    	'post_title'     => $description,
							    );

							 	// Update the post into the database
							    wp_update_post( $my_post );
							}

							++$existing;
                    	}
					}
				}
				update_post_meta( $post_id, '_brochures', $media_ids );

				$this->add_log( 'Imported ' . count($media_ids) . ' brochures (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->Property_ID );

				// Media - EPCs
				$media_ids = array();
				$new = 0;
				$existing = 0;
				$deleted = 0;
				$previous_media_ids = get_post_meta( $post_id, '_epcs', TRUE );
				$i = 0;
				if ( isset($property->Epc) && (string)$property->Epc != '')
                {
                    $media_file_name = (string)$property->Epc;
					$media_folder = dirname( $this->target_file );
					$description = '';

					if ( file_exists( $media_folder . '/' . $media_file_name ) )
					{
						$upload = true;
                        $replacing_attachment_id = '';
                        if ( isset($previous_media_ids[$i]) ) 
                        {                                    
                            // get this attachment
                            $current_image_path = get_post_meta( $previous_media_ids[$i], '_imported_path', TRUE );
                            $current_image_size = filesize( $current_image_path );
                            
                            if ($current_image_size > 0 && $current_image_size !== FALSE)
                            {
                                $replacing_attachment_id = $previous_media_ids[$i];
                                
                                $new_image_size = filesize( $media_folder . '/' . $media_file_name );
                                
                                if ($new_image_size > 0 && $new_image_size !== FALSE)
                                {
                                    if ($current_image_size == $new_image_size)
                                    {
                                        $upload = false;
                                    }
                                    else
                                    {
                                        
                                    }
                                }
                                else
                                {
                                	$this->add_error( 'Failed to get filesize of new EPC file ' . $media_folder . '/' . $media_file_name, (string)$property->Property_ID );
                                }
                                
                                unset($new_image_size);
                            }
                            else
                            {
                            	$this->add_error( 'Failed to get filesize of EPC file ' . $current_image_path, (string)$property->Property_ID );
                            }
                            
                            unset($current_image_size);
                        }

                        if ($upload)
                        {
							// We've physically received the file
							$upload = wp_upload_bits(trim($media_file_name, '_'), null, file_get_contents($media_folder . '/' . $media_file_name));  
                            
                            if( isset($upload['error']) && $upload['error'] !== FALSE )
                            {
                            	$this->add_error( print_r($upload['error'], TRUE), (string)$property->Property_ID );
                            }
                            else
                            {
                            	// We don't already have a thumbnail and we're presented with an image
                                $wp_filetype = wp_check_filetype( $upload['file'], null );
                            
                                $attachment = array(
                                     //'guid' => $wp_upload_dir['url'] . '/' . trim($media_file_name, '_'), 
                                     'post_mime_type' => $wp_filetype['type'],
                                     'post_title' => ( ( $description != '' ) ? $description : preg_replace('/\.[^.]+$/', '', trim($media_file_name, '_')) ),
                                     'post_content' => '',
                                     'post_status' => 'inherit'
                                );
                                $attach_id = wp_insert_attachment( $attachment, $upload['file'], $post_id );
                                
                                if ( $attach_id === FALSE || $attach_id == 0 )
                                {    
                                	$this->add_error( 'Failed inserting EPC attachment ' . $upload['file'] . ' - ' . print_r($attachment, TRUE), (string)$property->Property_ID );
                                }
                                else
                                {  
                                    $attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
                                    wp_update_attachment_metadata( $attach_id,  $attach_data );

                                	update_post_meta( $attach_id, '_imported_path', $upload['file']);

                                	$media_ids[] = $attach_id;

                                	++$new;
                                }
                            }
                        }
                        else
                        {
                        	if ( isset($previous_media_ids[$i]) ) 
                        	{
                        		$media_ids[] = $previous_media_ids[$i];

                        		if ( $description != '' )
								{
									$my_post = array(
								    	'ID'          	 => $previous_media_ids[$i],
								    	'post_title'     => $description,
								    );

								 	// Update the post into the database
								    wp_update_post( $my_post );
								}

								++$existing;
                        	}
                        }

                        unlink($media_folder . '/' . $media_file_name);
					}
					else
					{
						if ( isset($previous_media_ids[$i]) ) 
                    	{
                    		$media_ids[] = $previous_media_ids[$i];

                    		if ( $description != '' )
							{
								$my_post = array(
							    	'ID'          	 => $previous_media_ids[$i],
							    	'post_title'     => $description,
							    );

							 	// Update the post into the database
							    wp_update_post( $my_post );
							}

							++$existing;
                    	}
					}
				}
				update_post_meta( $post_id, '_epcs', $media_ids );

				$this->add_log( 'Imported ' . count($media_ids) . ' EPCs (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->Property_ID );*/

				// Media - Virtual Tours
				$virtual_tours = array();
				if ( isset($property->Property_VirtualTour) && (string)$property->Property_VirtualTour != '' )
                {
                    $virtual_tours[] = (string)$property->Property_VirtualTour;
                }

                update_post_meta( $post_id, '_virtual_tours', count($virtual_tours) );
                foreach ($virtual_tours as $i => $virtual_tour)
                {
                	update_post_meta( $post_id, '_virtual_tour_' . $i, (string)$virtual_tour );
                }

				$this->add_log( 'Imported ' . count($virtual_tours) . ' virtual tours', (string)$property->Property_ID );

				do_action( "propertyhive_property_imported_propertyadd_xml", $post_id, $property );

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
							$this->add_log( 'New meta data for ' . trim($key, '_') . ': ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), (string)$property->Property_ID );
						}
						elseif ( $metadata_before[$key] != $metadata_after[$key] )
						{
							$this->add_log( 'Updated ' . trim($key, '_') . '. Before: ' . ( ( is_array($metadata_before[$key]) ) ? implode(", ", $metadata_before[$key]) : $metadata_before[$key] ) . ', After: ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), (string)$property->Property_ID );
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
							$this->add_log( 'New taxonomy data for ' . $taxonomy_name . ': ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), (string)$property->Property_ID );
						}
						elseif ( $taxonomy_terms_before[$taxonomy_name] != $taxonomy_terms_after[$taxonomy_name] )
						{
							$this->add_log( 'Updated ' . $taxonomy_name . '. Before: ' . ( ( is_array($taxonomy_terms_before[$taxonomy_name]) ) ? implode(", ", $taxonomy_terms_before[$taxonomy_name]) : $taxonomy_terms_before[$taxonomy_name] ) . ', After: ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), (string)$property->Property_ID );
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

		do_action( "propertyhive_post_import_properties_propertyadd_xml" );

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
			$import_refs[] = (string)$property->Property_ID;
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

				do_action( "propertyhive_property_removed_propertyadd_xml", $post->ID );
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
        if ($custom_field == 'price_qualifier')
        {
        	return array(
        		'Guide Price' => 'Guide Price',
        		'Offers Over' => 'Offers Over',
        		'Offers in Excess of' => 'Offers in Excess of',
        	);
        }
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