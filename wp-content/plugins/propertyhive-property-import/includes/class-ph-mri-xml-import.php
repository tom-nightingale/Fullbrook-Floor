<?php
/**
 * Class for managing the import process of an MRI XML file
 *
 * @package WordPress
 */
if ( class_exists( 'PH_Property_Import_Process' ) ) {

class PH_MRI_XML_Import extends PH_Property_Import_Process {

	public function __construct( $instance_id = '' ) 
	{
		$this->instance_id = $instance_id;

		if ( isset($_GET['custom_property_import_cron']) )
	    {
	    	$current_user = wp_get_current_user();

	    	$this->add_log("Executed manually by " . ( ( isset($current_user->display_name) ) ? $current_user->display_name : '' ) );
	    }
	}

	public function parse( $import_id = '' )
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

		$departments = array( 'RS', 'RL' );

		foreach ( $departments as $department )
		{
			$data = array(
		        'upw' => $options['password'],
		        'de' => $department,
		        'pp' => 1000,
		    );

	  		$postvars = http_build_query($data);

			$response = wp_remote_post(
				$options['url'],
				array(
					'method' => 'POST',
					'headers' => array(),
					'body' => $postvars,
			    )
			);

			if ( is_wp_error( $response ) ) 
			{
				$this->add_error( 'Failed to request properties: ' . $response->get_error_message() );
				return false;
			}

			$contents = simplexml_load_string($response['body']);

			if ( $contents === false )
			{
				$this->add_error( 'Failed to decode properties request body: ' . $response['body'] );
				return false;
			}

			if ( isset($contents->houses->property) && !empty($contents->houses->property) )
			{
				foreach ( $contents->houses->property as $property ) 
				{
					$this->properties[] = $property;
				}
			}
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

        do_action( "propertyhive_pre_import_properties_mri_xml", $this->properties );
        $this->properties = apply_filters( "propertyhive_mri_xml_properties_due_import", $this->properties );

		$this->add_log( 'Beginning to loop through ' . count($this->properties) . ' properties' );

		$property_row = 1;
		foreach ( $this->properties as $property )
		{
			$this->add_log( 'Importing property ' . $property_row .' with reference ' . (string)$property->id, (string)$property->id );

			$inserted_updated = false;
			$new_property = false;

			$display_address = (string)$property->address->display_address;
			$summary_description = '';
			if ( isset($property->property_summary->short_description->para) && !empty($property->property_summary->short_description->para) )
			{
				foreach ( $property->property_summary->short_description->para as $para )
				{
					if ( $summary_description != '' )
					{
						$summary_description .= "\n\n";
					}
					$summary_description .= (string)$para;
				}
			}

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
				    	'post_excerpt'   => $summary_description,
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
					'post_excerpt'   => $summary_description,
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
					($display_address != '' || $summary_description != '')
				)
				{
					$my_post = array(
				    	'ID'          	 => $post_id,
				    	'post_title'     => htmlentities(mb_convert_encoding(wp_strip_all_tags( $display_address ), 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
				    	'post_excerpt'   => htmlentities(mb_convert_encoding($summary_description, 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
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

				// Coordinates
				if ( isset($property->latitude) && isset($property->longitude) && (string)$property->latitude != '' && (string)$property->longitude != '' && (string)$property->latitude != '0' && (string)$property->longitude != '0' )
				{
					update_post_meta( $post_id, '_latitude', (string)$property->latitude );
					update_post_meta( $post_id, '_longitude', (string)$property->longitude );
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
								if ( trim($property->address->address1) != '' ) { $address_to_geocode[] = (string)$property->address->address1; }
								if ( trim($property->address->address2) != '' ) { $address_to_geocode[] = (string)$property->address->address2; }
								if ( trim($property->address->address3) != '' ) { $address_to_geocode[] = (string)$property->address->address3; }
								if ( trim($property->address->town) != '' ) { $address_to_geocode[] = (string)$property->address->town; }
								if ( trim($property->address->county) != '' ) { $address_to_geocode[] = (string)$property->address->county; }
								if ( trim($property->address->postcode) != '' ) { $address_to_geocode[] = (string)$property->address->postcode; }

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

				update_post_meta( $post_id, $imported_ref_key, (string)$property->id );

				// Address
				update_post_meta( $post_id, '_reference_number', (string)$property->id );
				update_post_meta( $post_id, '_address_name_number', (string)$property->address->address1 );
				update_post_meta( $post_id, '_address_street', (string)$property->address->address2 );
				update_post_meta( $post_id, '_address_two', (string)$property->address->address3 );
				update_post_meta( $post_id, '_address_three', (string)$property->address->town );
				update_post_meta( $post_id, '_address_four', (string)$property->address->county );
				update_post_meta( $post_id, '_address_postcode', (string)$property->address->postcode );

				$country = 'GB';
				update_post_meta( $post_id, '_address_country', $country );

            	// Let's just look at address fields to see if we find a match
            	$address_fields_to_check = apply_filters( 'propertyhive_mri_xml_address_fields_to_check', array('address3', 'town', 'county', 'property_location', 'location_town') );
				$location_term_ids = array();

				foreach ( $address_fields_to_check as $address_field )
				{
					if ( isset($property->address->{$address_field}) && trim((string)$property->address->{$address_field}) != '' ) 
					{
						$term = term_exists( trim((string)$property->address->{$address_field}), 'location');
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
				if ( isset($_POST['mapped_office'][(string)$property->branch]) && $_POST['mapped_office'][(string)$property->branch] != '' )
				{
					$office_id = $_POST['mapped_office'][(string)$property->branch];
				}
				elseif ( isset($options['offices']) && is_array($options['offices']) && !empty($options['offices']) )
				{
					foreach ( $options['offices'] as $ph_office_id => $branch_code )
					{
						if ( $branch_code == (string)$property->branch )
						{
							$office_id = $ph_office_id;
							break;
						}
					}
				}
				update_post_meta( $post_id, '_office_id', $office_id );

				// Residential Details
				$department = 'residential-sales';
				if ( (string)$property->department == 'RL' )
				{
					$department = 'residential-lettings';
				}

				update_post_meta( $post_id, '_department', $department );
				update_post_meta( $post_id, '_bedrooms', ( ( isset($property->property_summary->beds) ) ? (string)$property->property_summary->beds : '' ) );
				update_post_meta( $post_id, '_bathrooms', ( ( isset($property->property_summary->baths) ) ? (string)$property->property_summary->baths : '' ) );
				update_post_meta( $post_id, '_reception_rooms', ( ( isset($property->property_summary->receptions) ) ? (string)$property->property_summary->receptions : '' ) );

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

				if ( isset($property->extra_info->prty_code) && isset($property->extra_info->prst_code) )
				{
					if ( !empty($mapping) && isset($mapping[(string)$property->extra_info->prty_code . '-' . (string)$property->extra_info->prst_code]) )
					{
						wp_set_post_terms( $post_id, $mapping[(string)$property->extra_info->prty_code . '-' . (string)$property->extra_info->prst_code], $prefix . 'property_type' );
					}
					else
					{
						$this->add_log( 'Property received with a type (' . (string)$property->extra_info->prty_code . '-' . (string)$property->extra_info->prst_code . ') that is not mapped', (string)$property->id );

						$options = $this->add_missing_mapping( $mapping, $prefix . 'property_type', (string)$property->extra_info->prty_code . '-' . (string)$property->extra_info->prst_code, $import_id );
					}
				}

				// Residential Sales Details
				if ( $department == 'residential-sales' )
				{
					// Clean price
					$price = round(preg_replace("/[^0-9.]/", '', (string)$property->property_summary->price));
					
					update_post_meta( $post_id, '_price', $price );
					update_post_meta( $post_id, '_price_actual', $price );
					update_post_meta( $post_id, '_poa', '' );
				}
				elseif ( $department == 'residential-lettings' )
				{
					// Clean price
					$price = round(preg_replace("/[^0-9.]/", '', (string)$property->property_summary->price));

					update_post_meta( $post_id, '_rent', $price );

					$rent_frequency = 'pcm';
					if ( strpos( strtolower((string)$property->property_summary->price_text), 'week') !== FALSE )
					{
						$rent_frequency = 'pw';
					}

					update_post_meta( $post_id, '_rent_frequency', $rent_frequency );
					update_post_meta( $post_id, '_price_actual', (string)$property->property_summary->price_monthly );
					
					update_post_meta( $post_id, '_poa', '' );

					update_post_meta( $post_id, '_deposit', '' );

					$available_date = '';
					if ( (string)$property->property_summary->available_from != '' )
					{
						$explode_available_date = explode("/", (string)$property->property_summary->available_from);
						$available_date = $explode_available_date[2] . '-' . $explode_available_date[1] . '-' . $explode_available_date[0];
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
					if ( !empty($mapping) && isset($property->property_summary->furnished) && isset($mapping[(string)$property->property_summary->furnished]) )
					{
		                wp_set_post_terms( $post_id, $mapping[(string)$property->property_summary->furnished], 'furnished' );
		            }
				}

				// Marketing
				update_post_meta( $post_id, '_on_market', 'yes' );
				update_post_meta( $post_id, '_featured', '' );

				// Availability
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

        		wp_delete_object_term_relationships( $post_id, 'availability' );
				if ( !empty($mapping) && isset($property->property_summary->status) && isset($mapping[(string)$property->property_summary->status]) )
				{
	                wp_set_post_terms( $post_id, $mapping[(string)$property->property_summary->status], 'availability' );
	            }

		        // Rooms / Descriptions
		        $rooms = 0;
		        if ( isset($property->property_summary->long_description->para) && !empty($property->property_summary->long_description->para) )
				{
					foreach ( $property->property_summary->long_description->para as $para )
					{
						update_post_meta( $post_id, '_room_name_0', '' );
			            update_post_meta( $post_id, '_room_dimensions_0', '' );
			            update_post_meta( $post_id, '_room_description_0', (string)$para );

			            ++$rooms;
			        }
			    }
		        update_post_meta( $post_id, '_rooms', $rooms );

	            // Media - Images
	            if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
    			{
					$media_urls = array();
    				if (isset($property->images->pictures) && !empty($property->images->pictures))
	                {
	                    foreach ($property->images->pictures as $images)
	                    {
	                        if (!empty($images->picture))
	                        {
	                            foreach ($images->picture as $image)
	                            {
	                            	$media_attributes = $image->attributes();

									if ( 
										isset($media_attributes['type']) &&
										$media_attributes['type'] == 'image' &&
										(
											substr( strtolower((string)$image), 0, 2 ) == '//' || 
											substr( strtolower((string)$image), 0, 4 ) == 'http'
										)
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
					for ( $i = 1; $i <= 30; ++$i )
					{
						if (isset($property->images->{'picture' . $i}) && !empty($property->images->{'picture' . $i}))
	                	{
	                		$media_attributes = $property->images->{'picture' . $i}->attributes();

							if ( 
								isset($media_attributes['type']) &&
								$media_attributes['type'] == 'image' &&
								(
									substr( strtolower((string)$property->images->{'picture' . $i}), 0, 2 ) == '//' || 
									substr( strtolower((string)$property->images->{'picture' . $i}), 0, 4 ) == 'http'
								)
							)
							{
								// This is a URL
								$url = str_replace("http://", "https://", (string)$property->images->{'picture' . $i});

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
					if (isset($property->images->pictures) && !empty($property->images->pictures))
	                {
	                    foreach ($property->images->pictures as $images)
	                    {
	                        if (!empty($images->picture))
	                        {
	                            foreach ($images->picture as $image)
	                            {
	                            	$media_attributes = $image->attributes();

									if ( 
										isset($media_attributes['type']) &&
										$media_attributes['type'] == 'image' &&
										(
											substr( strtolower((string)$image), 0, 2 ) == '//' || 
											substr( strtolower((string)$image), 0, 4 ) == 'http'
										)
									)
									{
										// This is a URL
										$url = (string)$image;
										$description = $media_attributes['description'];

										$modified = (string)$media_attributes['updated_date'];

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
											    	update_post_meta( $id, '_modified', $modified);

											    	++$new;
											    }
											}
										}
									}
								}
							}
						}
					}
					for ( $i = 1; $i <= 30; ++$i )
					{
						if (isset($property->images->{'picture' . $i}) && !empty($property->images->{'picture' . $i}))
	                	{
                        	$media_attributes = $property->images->{'picture' . $i}->attributes();

							if ( 
								isset($media_attributes['type']) &&
								$media_attributes['type'] == 'image' &&
								(
									substr( strtolower((string)$property->images->{'picture' . $i}), 0, 2 ) == '//' || 
									substr( strtolower((string)$property->images->{'picture' . $i}), 0, 4 ) == 'http'
								)
							)
							{
								// This is a URL
								$url = (string)$property->images->{'picture' . $i};
								$description = $media_attributes['description'];

								$modified = (string)$media_attributes['updated_date'];

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

					$this->add_log( 'Imported ' . count($media_ids) . ' photos (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->id );
				}

				// Media - Floorplans
	            if ( get_option('propertyhive_floorplans_stored_as', '') == 'urls' )
    			{
					$media_urls = array();
    				if (isset($property->images->floorplans) && !empty($property->images->floorplans))
	                {
	                    foreach ($property->images->floorplans as $images)
	                    {
	                        if (!empty($images->floorplan))
	                        {
	                            foreach ($images->floorplan as $image)
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
					for ( $i = 1; $i <= 30; ++$i )
					{
						if (isset($property->images->{'floorplan' . $i}) && !empty($property->images->{'floorplan' . $i}))
	                	{
	                		$media_attributes = $property->images->{'floorplan' . $i}->attributes();

							if ( 
								substr( strtolower((string)$property->images->{'floorplan' . $i}), 0, 2 ) == '//' || 
								substr( strtolower((string)$property->images->{'floorplan' . $i}), 0, 4 ) == 'http'
							)
							{
								// This is a URL
								$url = str_replace("http://", "https://", (string)$property->images->{'floorplan' . $i});

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
					if (isset($property->images->floorplans) && !empty($property->images->floorplans))
	                {
	                    foreach ($property->images->floorplans as $images)
	                    {
	                        if (!empty($images->floorplan))
	                        {
	                            foreach ($images->floorplan as $image)
	                            {
	                            	$media_attributes = $image->attributes();

									if ( 
										substr( strtolower((string)$image), 0, 2 ) == '//' || 
										substr( strtolower((string)$image), 0, 4 ) == 'http'
									)
									{
										// This is a URL
										$url = (string)$image;
										$description = $media_attributes['description'];

										$modified = (string)$media_attributes['updated_date'];

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
											    	update_post_meta( $id, '_modified', $modified);

											    	++$new;
											    }
											}
										}
									}
								}
							}
						}
					}
					for ( $i = 1; $i <= 30; ++$i )
					{
						if (isset($property->images->{'floorplan' . $i}) && !empty($property->images->{'floorplan' . $i}))
	                	{
                        	$media_attributes = $property->images->{'floorplan' . $i}->attributes();

							if ( 
								substr( strtolower((string)$property->images->{'floorplan' . $i}), 0, 2 ) == '//' || 
								substr( strtolower((string)$property->images->{'floorplan' . $i}), 0, 4 ) == 'http'
							)
							{
								// This is a URL
								$url = (string)$property->images->{'floorplan' . $i};
								$description = $media_attributes['description'];

								$modified = (string)$media_attributes['updated_date'];

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

					$this->add_log( 'Imported ' . count($media_ids) . ' floorplans (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->id );
				}

				// Media - Brochures
				if ( get_option('propertyhive_brochures_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();
    				if ( isset($property->links->brochure) && (string)$property->links->brochure != '' )
	                {
	                	$url = str_replace("http://", "https://", (string)$property->links->brochure);

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
					if ( isset($property->links->brochure) && (string)$property->links->brochure != '' )
	                {
                    	$media_attributes = $property->links->brochure->attributes();

						if ( 
							substr( strtolower((string)$property->links->brochure), 0, 2 ) == '//' || 
							substr( strtolower((string)$property->links->brochure), 0, 4 ) == 'http'
						)
						{
							// This is a URL
							$url = (string)$property->links->brochure;

							$modified = (string)$media_attributes['updated_date'];

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
								    	update_post_meta( $id, '_modified', $modified);

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
    				if (isset($property->images->epcs) && !empty($property->images->epcs))
	                {
	                    foreach ($property->images->epcs as $images)
	                    {
	                        if (!empty($images->epc))
	                        {
	                            foreach ($images->epc as $image)
	                            {
									if (
										substr( strtolower((string)$image), 0, 2 ) == '//' || 
										substr( strtolower((string)$image), 0, 4 ) == 'http'
									)
									{
										// This is a URL
										$url = str_replace("http://", "https://", (string)$image);
										$url = html_entity_decode($url);

										$media_urls[] = array('url' => $url);
									}
								}
							}
						}
					}
					for ( $i = 1; $i <= 30; ++$i )
					{
						if (isset($property->images->{'epc' . $i}) && !empty($property->images->{'epc' . $i}))
	                	{
	                		$media_attributes = $property->images->{'epc' . $i}->attributes();

							if ( 
								substr( strtolower((string)$property->images->{'epc' . $i}), 0, 2 ) == '//' || 
								substr( strtolower((string)$property->images->{'epc' . $i}), 0, 4 ) == 'http'
							)
							{
								// This is a URL
								$url = str_replace("http://", "https://", (string)$property->images->{'epc' . $i});

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
					if (isset($property->images->epcs) && !empty($property->images->epcs))
	                {
	                    foreach ($property->images->epcs as $images)
	                    {
	                        if (!empty($images->epc))
	                        {
	                            foreach ($images->epc as $image)
	                            {
	                            	$media_attributes = $image->attributes();

									if ( 
										substr( strtolower((string)$image), 0, 2 ) == '//' || 
										substr( strtolower((string)$image), 0, 4 ) == 'http'
									)
									{
										// This is a URL
										$url = (string)$image;

										$description = $media_attributes['description'];

										$modified = (string)$media_attributes['updated_date'];

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
											    	update_post_meta( $id, '_modified', $modified);

											    	++$new;
											    }
											}
										}
									}
								}
							}
						}
					}
					for ( $i = 1; $i <= 30; ++$i )
					{
						if (isset($property->images->{'epc' . $i}) && !empty($property->images->{'epc' . $i}))
	                	{
                        	$media_attributes = $property->images->{'epc' . $i}->attributes();

							if ( 
								substr( strtolower((string)$property->images->{'epc' . $i}), 0, 2 ) == '//' || 
								substr( strtolower((string)$property->images->{'epc' . $i}), 0, 4 ) == 'http'
							)
							{
								// This is a URL
								$url = (string)$property->images->{'epc' . $i};

								$description = $media_attributes['description'];

								$modified = (string)$media_attributes['updated_date'];

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

					$this->add_log( 'Imported ' . count($media_ids) . ' EPCs (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->id );
				}

				// Media - Virtual Tours
				$virtual_tours = array();
				if (isset($property->links->virtual_tour) && (string)$property->links->virtual_tour)
                {
                    $virtual_tours[] = (string)$property->links->virtual_tour;
                }

                update_post_meta( $post_id, '_virtual_tours', count($virtual_tours) );
                foreach ($virtual_tours as $i => $virtual_tour)
                {
                	update_post_meta( $post_id, '_virtual_tour_' . $i, (string)$virtual_tour );
                }

				$this->add_log( 'Imported ' . count($virtual_tours) . ' virtual tours', (string)$property->id );
				
				do_action( "propertyhive_property_imported_mri_xml", $post_id, $property );

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

		do_action( "propertyhive_post_import_properties_mri_xml" );

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

				do_action( "propertyhive_property_removed_mri_xml", $post->ID );
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
		if ( get_option( 'propertyhive_active_departments_sales' ) == 'yes' )
		{
			$mapping_values = $this->get_xml_mapping_values('sales_availability');
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
			$mapping_values = $this->get_xml_mapping_values('lettings_availability');
			if ( is_array($mapping_values) && !empty($mapping_values) )
			{
				foreach ($mapping_values as $mapping_value => $text_value)
				{
					$this->mappings['lettings_availability'][$mapping_value] = '';
				}
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

	public function get_mapping_values($custom_field, $import_id)
	{
		return $this->get_xml_mapping_values($custom_field);
	}

	public function get_xml_mapping_values($custom_field) 
	{
        if ($custom_field == 'sales_availability')
        {
            return array(
            	'AVAI' => 'For Sale',
            	'REACTIVATE' => 'Reactivated',
            	'UO' => 'Under Offer',
            );
        }
        if ($custom_field == 'lettings_availability')
        {
            return array(
                'ARGAV' => 'Available arranging tenancy',
                'AV_LET' => 'Available to let',
                'LETSTC' => 'Let - Subject to references',
                'QUBEUNAVIL' => 'Unavailable',
            );
        }
        if ($custom_field == 'property_type')
        {
        	return array(
                'HOUSE-DETATCH' => 'House - Detached',
                'HOUSE-SEMID' => 'Semi Detached',
            );
        }
        if ($custom_field == 'furnished')
        {
            return array(
            	'F' => 'Furnished',
            	'O' => 'Optional',
            	'P' => 'Part Furnished',
            	'U' => 'Unfurnished',
            );
        }
    }

    public function archive( $import_id )
    {
    	// Rename to append the date and '.processed' as to not get picked up again. Will be cleaned up every 7 days
    	$new_target_file = $this->target_file . '-' . gmdate("YmdHis") .'.processed';
		rename( $this->target_file, $new_target_file );
		
		$this->add_log( "Archived XML. Available for download for 7 days: " . str_replace("/includes", "", plugin_dir_url( __FILE__ )) . "/download.php?import_id=" . $import_id . "&file=" . base64_encode(basename($new_target_file)));
    }

}

}