<?php
/**
 * Class for managing the import process of a SuperControl XML file
 *
 * @package WordPress
 */
if ( class_exists( 'PH_Property_Import_Process' ) ) {

class PH_SuperControl_XML_Import extends PH_Property_Import_Process {

	public function __construct( $instance_id = '' ) 
	{
		$this->instance_id = $instance_id;

		if ( isset($_GET['custom_property_import_cron']) )
	    {
	    	$current_user = wp_get_current_user();

	    	$this->add_log("Executed manually by " . ( ( isset($current_user->display_name) ) ? $current_user->display_name : '' ) );
	    }
	}

	public function parse( $options, $import_id )
	{
		$this->properties = array(); // Reset properties in the event we're importing multiple files

		$contents = '';

		$response = wp_remote_post( 
			'https://api.supercontrol.co.uk/api/endpoint/v1/GetProperties', 
			array( 
				'timeout' => 120,
				'body' => '<?xml version="1.0" encoding="UTF-8"?>
<scAPI>
  <client>
    <ID>' . $options['client_id'] . '</ID>
    <key>' . $options['api_key'] . '</key>
  </client>
</scAPI>'
			) 
		);
		if ( !is_wp_error($response) && is_array( $response ) ) 
		{
			$contents = $response['body'];

			// ensure errors node doesn't exist

			$xml = simplexml_load_string( $contents );

			if ($xml !== FALSE)
			{
				if ( isset($xml->error->msg) )
				{
					// we have an error node
					$this->add_error( 'Error returned from GetProperties: ' . $xml->error->msg );

	        		return false;
				}
			}
	        else
	        {
	        	// Failed to parse XML
	        	$this->add_error( 'Failed to parse GetProperties XML file. Possibly invalid XML' );

	        	return false;
	        }
		}
		else
		{
			$this->add_error( "Failed to obtain GetProperties XML. Dump of response as follows: " . print_r($response, TRUE) );

			return false;
		}

		$this->add_log("Parsing properties");
		
        $properties_imported = 0;
        
        if ( isset($xml->GetProperties) )
        {
			foreach ($xml->GetProperties->property as $property)
			{
				$property_attributes = $property->attributes();
				
				if ( isset($property_attributes['enabled']) && (string)$property_attributes['enabled'] == 'yes' )
				{
					$property_id = (string)$property_attributes['id'];

					// Make request for individual property
					$contents = '';

					$response = wp_remote_post( 
						'https://api.supercontrol.co.uk/api/endpoint/v1/GetProperty', 
						array( 
							'timeout' => 120,
							'body' => '<?xml version="1.0" encoding="UTF-8"?>
								<scAPI>
								  <client>
								    <ID>' . $options['client_id'] . '</ID>
								    <key>' . $options['api_key'] . '</key>
								    <propertyID>' . $property_id . '</propertyID>
								  </client>
								</scAPI>'
						) 
					);
					if ( !is_wp_error($response) && is_array( $response ) ) 
					{
						$contents = $response['body'];

						$property_xml = simplexml_load_string( $contents );

						if ($property_xml !== FALSE)
						{
							if ( isset($property_xml->error->msg) )
							{
								// we have an error node
								$this->add_error( 'Error returned from GetProperty(' . $property_id . '): ' . $property_xml->error->msg );

				        		return false;
							}
							else
							{
								$this->properties[] = $property_xml->GetProperty->property;
							}
						}
				        else
				        {
				        	// Failed to parse XML
				        	$this->add_error( 'Failed to parse GetProperty(' . $property_id . ') XML file. Possibly invalid XML' );

				        	return false;
				        }
					}
					else
					{
						$this->add_error( "Failed to obtain GetProperty(" . $property_id . ") XML. Dump of response as follows: " . print_r($response, TRUE) );

						return false;
					}
		        }
	        } // end foreach property
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

        do_action( "propertyhive_pre_import_properties_supercontrol_xml", $this->properties );
        $this->properties = apply_filters( "propertyhive_supercontrol_xml_properties_due_import", $this->properties );

		$this->add_log( 'Beginning to loop through ' . count($this->properties) . ' properties' );

		$property_row = 1;
		foreach ( $this->properties as $property )
		{
			$property_id = (string)$property->propertycode;

			$this->add_log( 'Importing property ' . $property_row .' with reference ' . $property_id, $property_id );

			$inserted_updated = false;
			$new_property = false;

			$display_address = (string)$property->propertyname;

			$args = array(
	            'post_type' => 'property',
	            'posts_per_page' => 1,
	            'post_status' => 'any',
	            'meta_query' => array(
	            	array(
		            	'key' => $imported_ref_key,
		            	'value' => $property_id
		            )
	            )
	        );
	        $property_query = new WP_Query($args);
	        
	        if ($property_query->have_posts())
	        {
	        	$this->add_log( 'This property has been imported before. Updating it', $property_id );

	        	// We've imported this property before
	            while ($property_query->have_posts())
	            {
	                $property_query->the_post();

	                $post_id = get_the_ID();

	                $my_post = array(
				    	'ID'          	 => $post_id,
				    	'post_title'     => wp_strip_all_tags( $display_address ),
				    	'post_excerpt'   => (string)$property->shortdescription,
				    	'post_content' 	 => '',
				    	'post_status'    => 'publish',
				  	);

				 	// Update the post into the database
				    $post_id = wp_update_post( $my_post );

				    if ( is_wp_error( $post_id ) ) 
					{
						$this->add_error( 'ERROR: Failed to update post. The error was as follows: ' . $post_id->get_error_message(), $property_id );
					}
					else
					{
						$inserted_updated = 'updated';
					}
	            }
	        }
	        else
	        {
	        	$this->add_log( 'This property hasn\'t been imported before. Inserting it', $property_id );

	        	// We've not imported this property before
				$postdata = array(
					'post_excerpt'   => (string)$property->shortdescription,
					'post_content' 	 => '',
					'post_title'     => wp_strip_all_tags( $display_address ),
					'post_status'    => 'publish',
					'post_type'      => 'property',
					'comment_status' => 'closed',
				);

				$post_id = wp_insert_post( $postdata, true );

				if ( is_wp_error( $post_id ) ) 
				{
					$this->add_error( 'Failed to insert post. The error was as follows: ' . $post_id->get_error_message(), $property_id );
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
					($display_address != '' || (string)$property->shortdescription != '')
				)
				{
					$my_post = array(
				    	'ID'          	 => $post_id,
				    	'post_title'     => htmlentities(mb_convert_encoding(wp_strip_all_tags( $display_address ), 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
				    	'post_excerpt'   => htmlentities(mb_convert_encoding((string)$property->shortdescription, 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
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

				$this->add_log( 'Successfully ' . $inserted_updated . ' post. The post ID is ' . $post_id, $property_id );

				$previous_supercontrol_xml_update_date = get_post_meta( $post_id, '_supercontrol_xml_update_date_' . $import_id, TRUE);

				$skip_property = false;
				if (
					( isset($options['only_updated']) && $options['only_updated'] == 'yes' )
				)
				{
					if (
						$previous_supercontrol_xml_update_date == (string)$property->lastupdate
					)
					{
						$skip_property = true;
					}
				}

				// Coordinates
				if ( isset($property->latitude) && isset($property->longitude) && (string)$property->latitude != '' && (string)$property->longitude != '' && (string)$property->latitude != '0' && (string)$property->longitude != '0' )
				{
					update_post_meta( $post_id, '_latitude', ( ( isset($property->latitude) ) ? (string)$property->latitude : '' ) );
					update_post_meta( $post_id, '_longitude', ( ( isset($property->longitude) ) ? (string)$property->longitude : '' ) );
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
								if ( trim($property->propertyaddress) != '' ) { $address_to_geocode[] = (string)$property->propertyaddress; }
								if ( trim($property->propertypostcode) != '' ) { $address_to_geocode[] = (string)$property->propertypostcode; }	

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
							        	$this->add_error( 'Google Geocoding service returned status ' . $status, $property_id );
							        	sleep(3);
							        }
							    }
							    else
						        {
						        	$this->add_error( 'Failed to parse XML response from Google Geocoding service.', $property_id );
						        }
							}
							else
					        {
					        	$this->add_error( 'Failed to obtain co-ordinates as allow_url_fopen setting is disabled', $property_id );
					        }
					    }
					    else
					    {
					    	$this->add_log( 'Not performing Google Geocoding request as no API key present in settings', $property_id );
					    }
					}
				}

				if ( !$skip_property )
				{
					update_post_meta( $post_id, $imported_ref_key, $property_id );

					// Address
					update_post_meta( $post_id, '_reference_number', trim( ( isset($property->clientcode) && (string)$property->clientcode != '' ) ? (string)$property->clientcode : (string)$property->propertycode ) );
					update_post_meta( $post_id, '_address_name_number', '' );
					update_post_meta( $post_id, '_address_street', trim( ( isset($property->propertyaddress) ) ? (string)$property->propertyaddress : '' ) );
					update_post_meta( $post_id, '_address_two', '' );
					update_post_meta( $post_id, '_address_three', trim( ( isset($property->propertytown) ) ? (string)$property->propertytown : '' ) );
					update_post_meta( $post_id, '_address_four', '' );
					update_post_meta( $post_id, '_address_postcode', trim( ( isset($property->propertypostcode) ) ? (string)$property->propertypostcode : '' ) );

					$country = (string)$property->countryiso;
					update_post_meta( $post_id, '_address_country', $country );

					if ( isset($_POST['mapped_location']) )
					{
						$mapping = $_POST['mapped_location'];
					}
					else
					{
						$mapping = isset($options['mappings']['location']) ? $options['mappings']['location'] : array();
					}

	        		wp_delete_object_term_relationships( $post_id, 'location' );

	            	$address_fields_to_check = apply_filters( 'propertyhive_supercontrol_xml_address_fields_to_check', array('propertytown', 'regionname', 'regionname1', 'regionname2', 'regionname3', 'regionname4') );
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

					// Residential Details
					$department = 'residential-lettings';

					update_post_meta( $post_id, '_department', $department );
					update_post_meta( $post_id, '_bedrooms', ( ( isset($property->bedrooms_new) ) ? (string)$property->bedrooms_new : '' ) );
					update_post_meta( $post_id, '_bathrooms', ( ( isset($property->bathrooms_new) ) ? (string)$property->bathrooms_new : '' ) );
					update_post_meta( $post_id, '_reception_rooms', '' );

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

					if ( isset($property->typename) )
					{
						if ( !empty($mapping) && isset($mapping[trim((string)$property->typename)]) )
						{
							wp_set_post_terms( $post_id, $mapping[trim((string)$property->typename)], $prefix . 'property_type' );
						}
						else
						{
							$this->add_log( 'Property received with a type (' . trim((string)$property->typename) . ') that is not mapped', $property_id );

							$options = $this->add_missing_mapping( $mapping, $prefix . 'property_type', trim((string)$property->typename), $import_id );
						}
					}

					// Clean price
					$price = round(preg_replace("/[^0-9.]/", '', (string)$property->mindaily));

					update_post_meta( $post_id, '_rent', $price );

					$rent_frequency = 'pw';
					$price_actual = $price;
					/*switch ((string)$property->rentFrequency)
					{
						case "1": { $rent_frequency = 'pcm'; $price_actual = $price; break; }
						case "2": { $rent_frequency = 'pw'; $price_actual = ($price * 52) / 12; break; }
						case "3": { $rent_frequency = 'pa'; $price_actual = $price / 12; break; }
					}*/
					update_post_meta( $post_id, '_rent_frequency', $rent_frequency );
					update_post_meta( $post_id, '_price_actual', $price_actual );
					
					update_post_meta( $post_id, '_poa', ( ( isset($property->toLetPOA) && $property->toLetPOA == '1' ) ? 'yes' : '') );

					update_post_meta( $post_id, '_deposit', trim((string)$property->deposit) );
            		update_post_meta( $post_id, '_available_date', '' );

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
					if ( !empty($mapping) && isset($property->furnished) && isset($mapping[(string)$property->furnished]) )
					{
		                wp_set_post_terms( $post_id, $mapping[(string)$property->furnished], 'furnished' );
		            }*/

					// Marketing
					update_post_meta( $post_id, '_on_market', 'yes' );
					update_post_meta( $post_id, '_featured', '' );

					// Availability
					/*$prefix = '';
					if ( isset($_POST['mapped_' . $prefix . 'availability']) )
					{
						$mapping = $_POST['mapped_' . $prefix . 'availability'];
					}
					else
					{
						$mapping = isset($options['mappings'][$prefix . 'availability']) ? $options['mappings'][$prefix . 'availability'] : array();
					}

	        		wp_delete_object_term_relationships( $post_id, 'availability' );
					if ( !empty($mapping) && isset($property->availability) && isset($mapping[(string)$property->availability]) )
					{
		                wp_set_post_terms( $post_id, $mapping[(string)$property->availability], 'availability' );
		            }*/

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
					update_post_meta( $post_id, '_rooms', '1' );
					update_post_meta( $post_id, '_room_name_0', '' );
		            update_post_meta( $post_id, '_room_dimensions_0', '' );
		            update_post_meta( $post_id, '_room_description_0', str_replace(array("\r\n", "\n"), "", (string)$property->webdescription) );

		            $urls = array();
		            if ( isset($property->photos->img) )
		            {
		            	foreach ( $property->photos->img as $img )
		            	{
		            		if ( isset($img->customlarge) && trim((string)$img->customlarge) != '' )
		            		{
								preg_match('/<img(.*)src(.*)=(.*)"(.*)"/U', trim((string)$img->customlarge), $result);
								$urls[] = array_pop($result);
		            		}
		            	}
		            }

		            // Media - Images
		            if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
	    			{
	    				$media_urls = array();
	    				if (!empty($urls))
		                {
		                    foreach ($urls as $url)
		                    {
								$media_urls[] = array('url' => $url);
							}
						}
						update_post_meta( $post_id, '_photo_urls', $media_urls );

						$this->add_log( 'Imported ' . count($media_urls) . ' photo URLs', $property_id );
	    			}
	    			else
	    			{
						$media_ids = array();
						$new = 0;
						$existing = 0;
						$deleted = 0;
						$previous_media_ids = get_post_meta( $post_id, '_photos', TRUE );
						if (!empty($urls))
		                {
		                    foreach ($urls as $url)
		                    {
								// This is a URL
								$description = '';

								/*$media_attributes = $image->attributes();
								$modified = (string)$media_attributes['modified'];*/

								$filename = basename( $url );

								// Check, based on the URL, whether we have previously imported this media
								$imported_previously = false;
								$imported_previously_id = '';
								if ( is_array($previous_media_ids) && !empty($previous_media_ids) )
								{
									foreach ( $previous_media_ids as $previous_media_id )
									{
										if ( 
											get_post_meta( $previous_media_id, '_imported_url', TRUE ) == $url/*
											&&
											(
												get_post_meta( $previous_media_id, '_modified', TRUE ) == '' 
												||
												(
													get_post_meta( $previous_media_id, '_modified', TRUE ) != '' &&
													get_post_meta( $previous_media_id, '_modified', TRUE ) == $modified
												)
											)*/
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

								        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property_id );
								    }
								    else
								    {
									    $id = media_handle_sideload( $file_array, $post_id, $description, array('post_title' => $filename) );

									    // Check for handle sideload errors.
									    if ( is_wp_error( $id ) ) 
									    {
									        @unlink( $file_array['tmp_name'] );
									        
									        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property_id );
									    }
									    else
									    {
									    	$media_ids[] = $id;

									    	update_post_meta( $id, '_imported_url', $url);
									    	//update_post_meta( $id, '_modified', $modified);

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

						$this->add_log( 'Imported ' . count($media_ids) . ' photos (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property_id );
					}
				}
				else
				{
					$this->add_log( 'Skipping property as not been updated', $property_id );
				}
				
				update_post_meta( $post_id, '_supercontrol_xml_update_date_' . $import_id, (string)$property->lastupdate );

				// Import availability
				if ( apply_filters('propertyhive_import_supercontrol_availability', TRUE) === TRUE )
				{
					// Make request for property availability
					$contents = '';

					$response = wp_remote_post( 
						'https://api.supercontrol.co.uk/api/endpoint/v1/GetAvailability', 
						array( 
							'timeout' => 120,
							'body' => '<?xml version="1.0" encoding="UTF-8"?>
								<scAPI>
								  <client>
								    <ID>' . $options['client_id'] . '</ID>
								    <key>' . $options['api_key'] . '</key>
								    <Property>
								        <PropertyID>' . $property_id . '</PropertyID>
								    </Property>
								  </client>
								</scAPI>'
						) 
					);
					if ( !is_wp_error($response) && is_array( $response ) ) 
					{
						$contents = $response['body'];

						$availability_xml = simplexml_load_string( $contents );

						if ($availability_xml !== FALSE)
						{
							if ( isset($availability_xml->error->msg) )
							{
								// we have an error node
								$this->add_error( 'Error returned from GetAvailability(' . $property_id . '): ' . $availability_xml->error->msg );
							}
							else
							{
								// success
								$booking_i = 0;
								$booked_dates = array();
								foreach ( $availability_xml->GetAvailability->BookedStays->BookedStay as $booked_stay )
								{
									$arrival_date = strtotime( (string)$booked_stay->ArrivalDate );
									$departure_date = strtotime( (string)$booked_stay->DepartureDate );

									// Loop between dates, 24 hours at a time
									for ( $i = $arrival_date; $i <= $departure_date; $i = $i + 86400 ) 
									{
									  	$booked_dates[] = date( 'Y-m-d', $i );
									}
									update_post_meta( $post_id, '_arrival_date_' . $booking_i, (string)$booked_stay->ArrivalDate );
									update_post_meta( $post_id, '_departure_date_' . $booking_i, (string)$booked_stay->DepartureDate );

									++$booking_i;
								}
								update_post_meta( $post_id, '_booked_dates', $booked_dates );
								update_post_meta( $post_id, '_booked_stays', $booking_i );

								for ( $i = $booking_i; $i < 50; ++$i )
								{
									delete_post_meta( $post_id, '_arrival_date_' . $i );
									delete_post_meta( $post_id, '_departure_date_' . $i );
								}
							}
						}
				        else
				        {
				        	// Failed to parse XML
				        	$this->add_error( 'Failed to parse GetAvailability(' . $property_id . ') XML file. Possibly invalid XML' );
				        }
					}
					else
					{
						$this->add_error( "Failed to obtain GetAvailability(" . $property_id . ") XML. Dump of response as follows: " . print_r($response, TRUE) );
					}
				}

				do_action( "propertyhive_property_imported_supercontrol_xml", $post_id, $property );

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
							$this->add_log( 'New meta data for ' . trim($key, '_') . ': ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), $property_id );
						}
						elseif ( $metadata_before[$key] != $metadata_after[$key] )
						{
							$this->add_log( 'Updated ' . trim($key, '_') . '. Before: ' . ( ( is_array($metadata_before[$key]) ) ? implode(", ", $metadata_before[$key]) : $metadata_before[$key] ) . ', After: ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), $property_id );
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
							$this->add_log( 'New taxonomy data for ' . $taxonomy_name . ': ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), $property_id );
						}
						elseif ( $taxonomy_terms_before[$taxonomy_name] != $taxonomy_terms_after[$taxonomy_name] )
						{
							$this->add_log( 'Updated ' . $taxonomy_name . '. Before: ' . ( ( is_array($taxonomy_terms_before[$taxonomy_name]) ) ? implode(", ", $taxonomy_terms_before[$taxonomy_name]) : $taxonomy_terms_before[$taxonomy_name] ) . ', After: ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), $property_id );
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

		do_action( "propertyhive_post_import_properties_supercontrol_xml" );

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
			$property_id = (string)$property->propertycode;

			$import_refs[] = $property_id;
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

				do_action( "propertyhive_property_removed_supercontrol_xml", $post->ID );
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

            );
        }
        if ($custom_field == 'property_type')
        {
        	return array(
                'Standard' => 'Standard',
            );
        }
    }

}

}