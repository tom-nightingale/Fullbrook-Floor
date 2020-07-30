<?php
/**
 * Class for managing the import process of a Citylets XML file
 *
 * @package WordPress
 */
if ( class_exists( 'PH_Property_Import_Process' ) ) {

class PH_Citylets_XML_Import extends PH_Property_Import_Process {

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

        do_action( "propertyhive_pre_import_properties_citylets_xml", $this->properties );
        $this->properties = apply_filters( "propertyhive_citylets_xml_properties_due_import", $this->properties );

		$this->add_log( 'Beginning to loop through ' . count($this->properties) . ' properties' );

		$property_row = 1;
		foreach ( $this->properties as $property )
		{
			$this->add_log( 'Importing property ' . $property_row . ' with reference ' . (string)$property->propertyID, (string)$property->propertyID );

			$inserted_updated = false;

			$args = array(
	            'post_type' => 'property',
	            'posts_per_page' => 1,
	            'post_status' => 'any',
	            'meta_query' => array(
	            	array(
		            	'key' => $imported_ref_key,
		            	'value' => (string)$property->propertyID
		            )
	            )
	        );
	        $property_query = new WP_Query($args);
	        
	        if ($property_query->have_posts())
	        {
	        	$this->add_log( 'This property has been imported before. Updating it', (string)$property->propertyID );

	        	// We've imported this property before
	            while ($property_query->have_posts())
	            {
	                $property_query->the_post();

	                $post_id = get_the_ID();

	                $my_post = array(
				    	'ID'          	 => $post_id,
				    	'post_title'     => wp_strip_all_tags( (string)$property->ShortAddress1 ),
				    	'post_excerpt'   => (string)$property->ShortDesc,
				    	'post_content' 	 => '',
				    	'post_status'    => 'publish',
				  	);

				 	// Update the post into the database
				    $post_id = wp_update_post( $my_post );

				    if ( is_wp_error( $post_id ) ) 
					{
						$this->add_error( 'ERROR: Failed to update post. The error was as follows: ' . $post_id->get_error_message(), (string)$property->propertyID );
					}
					else
					{
						$inserted_updated = 'updated';
					}
	            }
	        }
	        else
	        {
	        	$this->add_log( 'This property hasn\'t been imported before. Inserting it', (string)$property->propertyID );

	        	// We've not imported this property before
				$postdata = array(
					'post_excerpt'   => (string)$property->ShortDesc,
					'post_content' 	 => '',
					'post_title'     => wp_strip_all_tags( (string)$property->ShortAddress1 ),
					'post_status'    => 'publish',
					'post_type'      => 'property',
					'comment_status' => 'closed',
				);

				$post_id = wp_insert_post( $postdata, true );

				if ( is_wp_error( $post_id ) ) 
				{
					$this->add_error( 'Failed to insert post. The error was as follows: ' . $post_id->get_error_message(), (string)$property->propertyID );
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
					((string)$property->ShortAddress1 != '' || (string)$property->ShortDesc != '')
				)
				{
					$my_post = array(
				    	'ID'          	 => $post_id,
				    	'post_title'     => htmlentities(mb_convert_encoding(wp_strip_all_tags( (string)$property->ShortAddress1 ), 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
				    	'post_excerpt'   => htmlentities(mb_convert_encoding((string)$property->ShortDesc, 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
				    	'post_content' 	 => '',
				    	'post_name' 	 => sanitize_title((string)$property->ShortAddress1),
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

				$this->add_log( 'Successfully ' . $inserted_updated . ' post. The post ID is ' . $post_id, (string)$property->propertyID );

				update_post_meta( $post_id, $imported_ref_key, (string)$property->propertyID );

				// Address
				update_post_meta( $post_id, '_reference_number', '' );
				update_post_meta( $post_id, '_address_name_number', '' );
				update_post_meta( $post_id, '_address_street', ( ( isset($property->Address1) ) ? (string)$property->Address1 : '' ) );
				update_post_meta( $post_id, '_address_two', ( ( isset($property->Address2) ) ? (string)$property->Address2 : '' ) );
				update_post_meta( $post_id, '_address_three', ( ( isset($property->Area) ) ? (string)$property->Area : '' ) );
				update_post_meta( $post_id, '_address_four', ( ( isset($property->City) ) ? (string)$property->City : '' ) );
				update_post_meta( $post_id, '_address_postcode', ( ( isset($property->Postcode) ) ? (string)$property->Postcode : '' ) );

				$country = 'GB';
				update_post_meta( $post_id, '_address_country', $country );

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
							if ( isset($property->Address1) && trim((string)$property->Address1) != '' ) { $address_to_geocode[] = (string)$property->Address1; }
							if ( isset($property->Address2) && trim((string)$property->Address2) != '' ) { $address_to_geocode[] = (string)$property->Address2; }
							if ( isset($property->Area) && trim((string)$property->Area) != '' ) { $address_to_geocode[] = (string)$property->Area; }
							if ( isset($property->City) && trim((string)$property->City) != '' ) { $address_to_geocode[] = (string)$property->City; }
							if ( isset($property->Postcode) && trim((string)$property->Postcode) != '' ) { $address_to_geocode[] = (string)$property->Postcode; }
							
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
						        	$this->add_error( 'Google Geocoding service returned status ' . $status, (string)$property->propertyID );
						        	sleep(3);
						        }
						    }
					        else
					        {
					        	$this->add_error( 'Failed to parse XML response from Google Geocoding service.', (string)$property->propertyID );
					        }
						}
						else
				        {
				        	$this->add_error( 'Failed to obtain co-ordinates as allow_url_fopen setting is disabled', (string)$property->propertyID );
				        }
			        }
				    else
				    {
				    	$this->add_log( 'Not performing Google Geocoding request as no API key present in settings', (string)$property->propertyID );
				    }
				}				

				// Owner
				add_post_meta( $post_id, '_owner_contact_id', '', true );

				// Record Details
				add_post_meta( $post_id, '_negotiator_id', get_current_user_id(), true );
					
				$office_id = $primary_office_id;
				if ( isset($_POST['mapped_office'][(string)$property->agentID]) && $_POST['mapped_office'][(string)$property->agentID] != '' )
				{
					$office_id = $_POST['mapped_office'][(string)$property->agentID];
				}
				elseif ( isset($options['offices']) && is_array($options['offices']) && !empty($options['offices']) )
				{
					foreach ( $options['offices'] as $ph_office_id => $branch_code )
					{
						if ( $branch_code == (string)$property->agentID )
						{
							$office_id = $ph_office_id;
							break;
						}
					}
				}
				update_post_meta( $post_id, '_office_id', $office_id );

				// Residential Details
				update_post_meta( $post_id, '_department', 'residential-lettings' );
				update_post_meta( $post_id, '_bedrooms', ( ( isset($property->TotalBedrooms) ) ? (string)$property->TotalBedrooms : '' ) );
				update_post_meta( $post_id, '_bathrooms', ( ( isset($property->Bathrooms) ) ? (string)$property->Bathrooms : '' ) );
				update_post_meta( $post_id, '_reception_rooms', '' );

				if ( isset($_POST['mapped_property_type']) )
				{
					$mapping = $_POST['mapped_property_type'];
				}
				else
				{
					$mapping = isset($options['mappings']['property_type']) ? $options['mappings']['property_type'] : array();
				}

				wp_delete_object_term_relationships( $post_id, 'property_type' );
				
				if ( isset($property->PropertyType) && (string)$property->PropertyType != '' )
				{
					if ( !empty($mapping) && isset($mapping[(string)$property->PropertyType]) )
					{
						wp_set_post_terms( $post_id, $mapping[(string)$property->PropertyType], 'property_type' );
					}
					else
					{
						$this->add_log( 'Property received with a type (' . (string)$property->PropertyType . ') that is not mapped', (string)$property->propertyID );

						$options = $this->add_missing_mapping( $mapping, 'property_type', (string)$property->PropertyType, $import_id );
					}
				}

				// Clean price
				$price = round(preg_replace("/[^0-9.]/", '', (string)$property->Rent));

				update_post_meta( $post_id, '_rent', $price );

				$rent_frequency = 'pcm';
				$price_actual = $price;
				
				update_post_meta( $post_id, '_rent_frequency', $rent_frequency );
				update_post_meta( $post_id, '_price_actual', $price_actual );
				
				update_post_meta( $post_id, '_currency', 'GBP' );
				
				update_post_meta( $post_id, '_poa', '' );

				update_post_meta( $post_id, '_deposit', ( ( isset($property->Deposit) ) ? (string)$property->Deposit : '' ) );
        		
				$available_date = ( ( isset($property->AvailabilityDate) ) ? (string)$property->AvailabilityDate : '' );
				if ( $available_date != '' )
				{ 
					$available_date = date('Y-m-d', strtotime($available_date));
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
				if ( !empty($mapping) && isset($property->Furnished) && isset($mapping[(string)$property->Furnished]) )
				{
	                wp_set_post_terms( $post_id, $mapping[(string)$property->Furnished], 'furnished' );
	            }
				
				// Marketing
				update_post_meta( $post_id, '_on_market', 'yes' );
				update_post_meta( $post_id, '_featured', '' );

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
				if ( !empty($mapping) && isset($mapping['A']) )
				{
	                wp_set_post_terms( $post_id, $mapping['A'], 'availability' );
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

		        // Rooms
		        // For now put the whole description in one room
				update_post_meta( $post_id, '_rooms', '1' );
				update_post_meta( $post_id, '_room_name_0', '' );
	            update_post_meta( $post_id, '_room_dimensions_0', '' );
	            update_post_meta( $post_id, '_room_description_0', (string)$property->FullDesc );

	            // Media - Images
	            if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();
    				if (isset($property->photos) && !empty($property->photos))
	                {
	                    foreach ($property->photos as $images)
	                    {
	                        if (!empty($images->photo))
	                        {
	                            foreach ($images->photo as $image)
	                            {
									if ( 
										substr( strtolower((string)$image), 0, 1 ) == '/' 
									)
									{
										// This is a URL
										$url = 'https://www.citylets.co.uk' . (string)$image;

										$media_urls[] = array('url' => $url);
									}
								}
							}
						}
					}
					update_post_meta( $post_id, '_photo_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' photo URLs', (string)$property->propertyID );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_photos', TRUE );
					if (isset($property->photos) && !empty($property->photos))
	                {
	                    foreach ($property->photos as $images)
	                    {
	                        if (!empty($images->photo))
	                        {
	                            foreach ($images->photo as $image)
	                            {
									if ( 
										substr( strtolower((string)$image), 0, 1 ) == '/' 
									)
									{
										// This is a URL
										$url = 'https://www.citylets.co.uk' . (string)$image;
										$description = '';
									    
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

										        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), (string)$property->propertyID );
										    }
										    else
										    {
											    $id = media_handle_sideload( $file_array, $post_id, $description, array('post_title' => $filename) );

											    // Check for handle sideload errors.
											    if ( is_wp_error( $id ) ) 
											    {
											        @unlink( $file_array['tmp_name'] );
											        
											        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), (string)$property->propertyID );
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

					$this->add_log( 'Imported ' . count($media_ids) . ' photos (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->propertyID );
				}

				// Media - Floorplans
				if ( get_option('propertyhive_floorplans_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();
    				if (isset($property->floorplans) && !empty($property->floorplans))
	                {
	                    foreach ($property->floorplans as $floorplans)
	                    {
	                        if (!empty($floorplans->floorplan))
	                        {
	                            foreach ($floorplans->floorplan as $floorplan)
	                            {
									if ( 
										substr( strtolower((string)$floorplan), 0, 1 ) == '/'
									)
									{
										// This is a URL
										$url = 'https://www.citylets.co.uk' . (string)$floorplan;

										$media_urls[] = array('url' => $url);
									}
								}
							}
						}
					}
					update_post_meta( $post_id, '_floorplan_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' floorplan URLs', (string)$property->propertyID );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_floorplans', TRUE );
					if (isset($property->floorplans) && !empty($property->floorplans))
	                {
	                    foreach ($property->floorplans as $floorplans)
	                    {
	                        if (!empty($floorplans->floorplan))
	                        {
	                            foreach ($floorplans->floorplan as $floorplan)
	                            {
									if ( 
										substr( strtolower((string)$floorplan), 0, 1 ) == '/'
									)
									{
										// This is a URL
										$url = 'https://www.citylets.co.uk' . (string)$floorplan;
										$description = '';
									    
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

										        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), (string)$property->propertyID );
										    }
										    else
										    {
											    $id = media_handle_sideload( $file_array, $post_id, $description, array('post_title' => $filename) );

											    // Check for handle sideload errors.
											    if ( is_wp_error( $id ) ) 
											    {
											        @unlink( $file_array['tmp_name'] );
											        
											        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), (string)$property->propertyID );
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

					$this->add_log( 'Imported ' . count($media_ids) . ' floorplans (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->propertyID );
				}

				// Media - EPCs
				if ( get_option('propertyhive_epcs_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();

    				if (isset($property->EPCDoc) && (string)$property->EPCDoc != '')
	                {
						if ( 
							substr( strtolower((string)$property->EPCDoc), 0, 1 ) == '/'
						)
						{
							// This is a URL
							$url = 'https://www.citylets.co.uk' . (string)$epcGraph;

							$media_urls[] = array('url' => $url);
						}
					}

					update_post_meta( $post_id, '_epc_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' EPC URLs', (string)$property->propertyID );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_epcs', TRUE );
					if (isset($property->EPCDoc) && (string)$property->EPCDoc != '')
	                {
						if ( 
							substr( strtolower((string)$property->EPCDoc), 0, 1 ) == '/'
						)
						{
							// This is a URL
							$url = 'https://www.citylets.co.uk' . (string)$epcGraph;
							$description = '';
						    
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

							        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), (string)$property->propertyID );
							    }
							    else
							    {
								    $id = media_handle_sideload( $file_array, $post_id, $description, array('post_title' => $filename) );

								    // Check for handle sideload errors.
								    if ( is_wp_error( $id ) ) 
								    {
								        @unlink( $file_array['tmp_name'] );
								        
								        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), (string)$property->propertyID );
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

					$this->add_log( 'Imported ' . count($media_ids) . ' EPCs (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->propertyID );
				}

				// Media - Virtual Tours
				$virtual_tours = array();
				if (isset($property->VirtualTourURL) && (string)$property->VirtualTourURL != '')
                {
                    $virtual_tours[] = (string)$property->VirtualTourURL;          
                }

                update_post_meta( $post_id, '_virtual_tours', count($virtual_tours) );
                foreach ($virtual_tours as $i => $virtual_tour)
                {
                	update_post_meta( $post_id, '_virtual_tour_' . $i, (string)$virtual_tour );
                }

				$this->add_log( 'Imported ' . count($virtual_tours) . ' virtual tours', (string)$property->propertyID );

				do_action( "propertyhive_property_imported_citylets_xml", $post_id, $property );

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
							$this->add_log( 'New meta data for ' . trim($key, '_') . ': ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), (string)$property->propertyID );
						}
						elseif ( $metadata_before[$key] != $metadata_after[$key] )
						{
							$this->add_log( 'Updated ' . trim($key, '_') . '. Before: ' . ( ( is_array($metadata_before[$key]) ) ? implode(", ", $metadata_before[$key]) : $metadata_before[$key] ) . ', After: ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), (string)$property->propertyID );
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
							$this->add_log( 'New taxonomy data for ' . $taxonomy_name . ': ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), (string)$property->propertyID );
						}
						elseif ( $taxonomy_terms_before[$taxonomy_name] != $taxonomy_terms_after[$taxonomy_name] )
						{
							$this->add_log( 'Updated ' . $taxonomy_name . '. Before: ' . ( ( is_array($taxonomy_terms_before[$taxonomy_name]) ) ? implode(", ", $taxonomy_terms_before[$taxonomy_name]) : $taxonomy_terms_before[$taxonomy_name] ) . ', After: ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), (string)$property->propertyID );
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

		do_action( "propertyhive_post_import_properties_citylets_xml" );

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
			$import_refs[] = (string)$property->propertyID;
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

				do_action( "propertyhive_property_removed_citylets_xml", $post->ID );
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
                'A' => 'Default',
            );
        }
        if ($custom_field == 'property_type')
        {
        	return array(
        		'F' => 'Flat',
        		'S' => 'Studio',
        		'H' => 'Detached House',
        		'SH' => 'Semi Detached House',
        		'TH' => 'Terraced House',
        		'M' => 'Mews',
        		'T' => 'Town House',
        		'B' => 'Bungalow',
        		'P' => 'Penthouse',
        		'SA' => 'Serviced Apartment',
        		'D' => 'Double Upper',
        		'I' => 'Single Room',
        		'J' => 'Double Room',
        		'V' => 'Villa',
        		'C' => 'Cottage',
        		'G' => 'Garage',
        		'Q' => 'Parking Space',
            );
        }
        
        if ($custom_field == 'furnished')
        {
            return array(
            	'Y' => 'Yes',
            	'N' => 'No',
            );
        }
    }

}

}