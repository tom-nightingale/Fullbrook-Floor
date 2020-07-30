<?php
/**
 * Class for managing the import process of a Rentman XML file
 *
 * @package WordPress
 */
if ( class_exists( 'PH_Property_Import_Process' ) ) {

class PH_Rentman_XML_Import extends PH_Property_Import_Process {

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

			foreach ($xml->Properties as $properties)
			{
				foreach ($properties->Property as $property)
				{
	                if ((string)$property->Rentorbuy == 1 || (string)$property->Rentorbuy == 2)
	                {
	                    $this->properties[] = $property;
	                }
	            } // end foreach property
            } // end foreach properties
        }
        else
        {
        	// Failed to parse XML
        	$this->add_error( 'Failed to parse XML file. Possibly invalid XML' );
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

        do_action( "propertyhive_pre_import_properties_rentman_xml", $this->properties );
        $this->properties = apply_filters( "propertyhive_rentman_xml_properties_due_import", $this->properties );

		$this->add_log( 'Beginning to loop through ' . count($this->properties) . ' properties' );

		$property_row = 1;
		foreach ( $this->properties as $property )
		{
			$this->add_log( 'Importing property ' . $property_row .' with reference ' . (string)$property->Refnumber, (string)$property->Refnumber );

			$inserted_updated = false;

			$display_address = '';
			if ( (string)$property->Street != '' )
			{
				$display_address .= (string)$property->Street;
			}
			if ( (string)$property->Address3 != '' )
			{
				if ( $display_address != '' ) { $display_address .= ', '; }
				$display_address .= (string)$property->Address3;
			}

			$args = array(
	            'post_type' => 'property',
	            'posts_per_page' => 1,
	            'post_status' => 'any',
	            'meta_query' => array(
	            	array(
		            	'key' => $imported_ref_key,
		            	'value' => (string)$property->Refnumber
		            )
	            )
	        );
	        $property_query = new WP_Query($args);
	        
	        if ($property_query->have_posts())
	        {
	        	$this->add_log( 'This property has been imported before. Updating it', (string)$property->Refnumber );

	        	// We've imported this property before
	            while ($property_query->have_posts())
	            {
	                $property_query->the_post();

	                $post_id = get_the_ID();

	                $my_post = array(
				    	'ID'          	 => $post_id,
				    	'post_title'     => wp_strip_all_tags( $display_address ),
				    	'post_excerpt'   => (string)$property->Description,
				    	'post_content' 	 => '',
				    	'post_status'    => 'publish',
				  	);

				 	// Update the post into the database
				    $post_id = wp_update_post( $my_post );

				    if ( is_wp_error( $post_id ) ) 
					{
						$this->add_error( 'ERROR: Failed to update post. The error was as follows: ' . $post_id->get_error_message(), (string)$property->Refnumber );
					}
					else
					{
						$inserted_updated = 'updated';
					}
	            }
	        }
	        else
	        {
	        	$this->add_log( 'This property hasn\'t been imported before. Inserting it', (string)$property->Refnumber );

	        	// We've not imported this property before
				$postdata = array(
					'post_excerpt'   => (string)$property->Description,
					'post_content' 	 => '',
					'post_title'     => wp_strip_all_tags( $display_address ),
					'post_status'    => 'publish',
					'post_type'      => 'property',
					'comment_status' => 'closed',
				);

				$post_id = wp_insert_post( $postdata, true );

				if ( is_wp_error( $post_id ) ) 
				{
					$this->add_error( 'Failed to insert post. The error was as follows: ' . $post_id->get_error_message(), (string)$property->Refnumber );
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
					($display_address != '' || (string)$property->Description != '')
				)
				{
					$my_post = array(
				    	'ID'          	 => $post_id,
				    	'post_title'     => htmlentities(mb_convert_encoding(wp_strip_all_tags( $display_address ), 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
				    	'post_excerpt'   => htmlentities(mb_convert_encoding((string)$property->Description, 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
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

				$this->add_log( 'Successfully ' . $inserted_updated . ' post. The post ID is ' . $post_id, (string)$property->Refnumber );

				update_post_meta( $post_id, $imported_ref_key, (string)$property->Refnumber );

				// Address
				update_post_meta( $post_id, '_reference_number', (string)$property->Refnumber );
				update_post_meta( $post_id, '_address_name_number', ( ( isset($property->Number) ) ? (string)$property->Number : '' ) );
				update_post_meta( $post_id, '_address_street', ( ( isset($property->Street) ) ? (string)$property->Street : '' ) );
				update_post_meta( $post_id, '_address_two', '' );
				update_post_meta( $post_id, '_address_three', ( ( isset($property->Address3) ) ? (string)$property->Address3 : '' ) );
				update_post_meta( $post_id, '_address_four', ( ( isset($property->Address4) ) ? (string)$property->Address4 : '' ) );
				update_post_meta( $post_id, '_address_postcode', ( ( isset($property->Postcode) ) ? (string)$property->Postcode : '' ) );

				$country = 'GB';
				if ( isset($property->country) && (string)$property->country != '' && class_exists('PH_Countries') )
				{
					$ph_countries = new PH_Countries();
					foreach ( $ph_countries->countries as $country_code => $country_details )
					{
						if ( strtolower((string)$property->country) == strtolower($country_details['name']) )
						{
							$country = $country_code;
							break;
						}
					}
				}
				update_post_meta( $post_id, '_address_country', $country );

				// Coordinates
				if ( isset($property->Gloc) && (string)$property->Gloc != '' && count( explode(",", (string)$property->Gloc) ) == 2 )
				{
					$exploded_gloc = explode(",", (string)$property->Gloc);
					update_post_meta( $post_id, '_latitude', trim($exploded_gloc[0]) );
					update_post_meta( $post_id, '_longitude', trim($exploded_gloc[1]) );
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
								if ( trim($property->Number) != '' ) { $address_to_geocode[] = (string)$property->Number; }
								if ( trim($property->Street) != '' ) { $address_to_geocode[] = (string)$property->Street; }
								if ( trim($property->Address3) != '' ) { $address_to_geocode[] = (string)$property->Address3; }
								if ( trim($property->Address4) != '' ) { $address_to_geocode[] = (string)$property->Address4; }
								if ( trim($property->Postcode) != '' ) { $address_to_geocode[] = (string)$property->Postcode; }

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
							        	$this->add_error( 'Google Geocoding service returned status ' . $status, (string)$property->Refnumber );
							        	sleep(3);
							        }
							    }
							    else
						        {
						        	$this->add_error( 'Failed to parse XML response from Google Geocoding service.', (string)$property->Refnumber );
						        }
							}
							else
					        {
					        	$this->add_error( 'Failed to obtain co-ordinates as allow_url_fopen setting is disabled', (string)$property->Refnumber );
					        }
					    }
					    else
					    {
					    	$this->add_log( 'Not performing Google Geocoding request as no API key present in settings', (string)$property->Refnumber );
					    }
					}
				}

				// Owner
				add_post_meta( $post_id, '_owner_contact_id', '', true );

				// Record Details
				add_post_meta( $post_id, '_negotiator_id', get_current_user_id(), true );
					
				$office_id = $primary_office_id;
				if ( isset($_POST['mapped_office'][(string)$property->Branch]) && $_POST['mapped_office'][(string)$property->Branch] != '' )
				{
					$office_id = $_POST['mapped_office'][(string)$property->Branch];
				}
				elseif ( isset($options['offices']) && is_array($options['offices']) && !empty($options['offices']) )
				{
					foreach ( $options['offices'] as $ph_office_id => $branch_code )
					{
						if ( $branch_code == (string)$property->Branch )
						{
							$office_id = $ph_office_id;
							break;
						}
					}
				}
				update_post_meta( $post_id, '_office_id', $office_id );

				// Residential Details
				update_post_meta( $post_id, '_department', ( ((string)$property->Rentorbuy == 1) ? 'residential-lettings' : 'residential-sales' ) );
				update_post_meta( $post_id, '_bedrooms', ( ( isset($property->Beds) ) ? round((string)$property->Beds) : '' ) );
				update_post_meta( $post_id, '_bathrooms', ( ( isset($property->Baths) ) ? round((string)$property->Baths) : '' ) );
				update_post_meta( $post_id, '_reception_rooms', ( ( isset($property->Receps) ) ? round((string)$property->Receps) : '' ) );

				if ( isset($_POST['mapped_property_type']) )
				{
					$mapping = $_POST['mapped_property_type'];
				}
				else
				{
					$mapping = isset($options['mappings']['property_type']) ? $options['mappings']['property_type'] : array();
				}

				wp_delete_object_term_relationships( $post_id, 'property_type' );

				if ( isset($property->Type) )
				{
					if ( !empty($mapping) && isset($mapping[(string)$property->Type]) )
					{
						wp_set_post_terms( $post_id, $mapping[(string)$property->Type], 'property_type' );
					}
					else
					{
						$this->add_log( 'Property received with a type (' . (string)$property->Type . ') that is not mapped', (string)$property->Refnumber );

						$options = $this->add_missing_mapping( $mapping, 'property_type', (string)$property->Type, $import_id );
					}
				}

				// Residential Sales Details
				if ( (string)$property->Rentorbuy == 2 )
				{
					$price_attributes = $property->Saleprice->attributes();

					// Clean price
					$price = round(preg_replace("/[^0-9.]/", '', (string)$property->Saleprice));

					update_post_meta( $post_id, '_price', $price );
					update_post_meta( $post_id, '_price_actual', $price );
					update_post_meta( $post_id, '_poa', ( ( isset($price_attributes['Qualifier']) && $price_attributes['Qualifier'] == '2' ) ? 'yes' : '') );

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
					if ( !empty($mapping) && isset($price_attributes['Qualifier']) && isset($mapping[(string)$price_attributes['Qualifier']]) )
					{
		                wp_set_post_terms( $post_id, $mapping[(string)$price_attributes['Qualifier']], 'price_qualifier' );
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
					if ( !empty($mapping) && isset($price_attributes['Ownership']) && isset($mapping[(string)$price_attributes['Ownership']]) )
					{
			            wp_set_post_terms( $post_id, $mapping[(string)$price_attributes['Ownership']], 'tenure' );
		            }
				}
				elseif ( (string)$property->Rentorbuy == 1 )
				{
					$price_attributes = $property->Rent->attributes();

					// Clean price
					$price = round(preg_replace("/[^0-9.]/", '', (string)$property->Rent));

					update_post_meta( $post_id, '_rent', $price );

					$rent_frequency = 'pcm';
					$price_actual = $price;
					switch ((string)$price_attributes['Period'])
					{
						case "Month": { $rent_frequency = 'pcm'; $price_actual = $price; break; }
						case "Week": { $rent_frequency = 'pw'; $price_actual = ($price * 52) / 12; break; }
					}
					update_post_meta( $post_id, '_rent_frequency', $rent_frequency );
					update_post_meta( $post_id, '_price_actual', $price_actual );
					
					update_post_meta( $post_id, '_poa', ( ( isset($price_attributes['Qualifier']) && $price_attributes['Qualifier'] == '2' ) ? 'yes' : '') );

					update_post_meta( $post_id, '_deposit', '' );
					$available_date = '';
					if ( isset($property->Available) && (string)$property->Available != '' )
					{
						$explode_available_date = explode("/", (string)$property->Available);
						if ( count($explode_available_date) == 3 )
						{
							$available_date = $explode_available_date[2] . '-' . $explode_available_date[1] . '-' . $explode_available_date[0];
						}
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
					if ( !empty($mapping) && isset($property->Furnished) && isset($mapping[round((string)$property->Furnished)]) )
					{
		                wp_set_post_terms( $post_id, $mapping[round((string)$property->Furnished)], 'furnished' );
		            }
		        }			

				// Marketing
				update_post_meta( $post_id, '_on_market', 'yes' );
				update_post_meta( $post_id, '_featured', ( isset($property->Featured) && strtolower((string)$property->Featured) == 'true' ) ? 'yes' : '' );

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
				if ( !empty($mapping) && isset($property->Status) && isset($mapping[(string)$property->Status]) )
				{
	                wp_set_post_terms( $post_id, $mapping[(string)$property->Status], 'availability' );
	            }

	            // Features
				$features = array();
				if ( isset($property->Bulletpoints->BulletPoint) && !empty($property->Bulletpoints->BulletPoint) )
				{
					foreach ( $property->Bulletpoints->BulletPoint as $bulletpoint )
					{
						$features[] = (string)$bulletpoint;
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
	            if ( (string)$property->Comments != '' )
	            {
	            	update_post_meta( $post_id, '_room_name_' . $num_rooms, '' );
		            update_post_meta( $post_id, '_room_dimensions_' . $num_rooms, '' );
		            update_post_meta( $post_id, '_room_description_' . $num_rooms, (string)$property->Comments );

	            	++$num_rooms;
	            }

	            if ( isset($property->Rooms->Room) && !empty($property->Rooms->Room) )
				{
	            	foreach ( $property->Rooms->Room as $room )
	            	{
	            		update_post_meta( $post_id, '_room_name_' . $num_rooms, (string)$room->Title );
			            update_post_meta( $post_id, '_room_dimensions_' . $num_rooms, '' );
			            update_post_meta( $post_id, '_room_description_' . $num_rooms, (string)$room->Description );

		            	++$num_rooms;
	            	}
	            }

	            update_post_meta( $post_id, '_rooms', $num_rooms );

	            // Media - Images
	            if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();

					update_post_meta( $post_id, '_photo_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' photo URLs', (string)$property->Refnumber );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_photos', TRUE );
					$i = 0;
					if ( isset($property->Media->Item) && !empty($property->Media->Item) )
					{
		            	foreach ( $property->Media->Item as $image )
		            	{
							$media_file_name = (string)$image;
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
	                                    	$this->add_error( 'Failed to get filesize of new image file ' . $media_folder . '/' . $media_file_name, (string)$property->Refnumber );
	                                    }
	                                    
	                                    unset($new_image_size);
	                                }
	                                else
	                                {
	                                	$this->add_error( 'Failed to get filesize of existing image file ' . $current_image_path, (string)$property->Refnumber );
	                                }
	                                
	                                unset($current_image_size);
	                            }

	                            if ($upload)
	                            {
									// We've physically received the file
									$upload = wp_upload_bits(trim($media_file_name, '_'), null, file_get_contents($media_folder . '/' . $media_file_name));  
	                                
	                                if( isset($upload['error']) && $upload['error'] !== FALSE )
	                                {
	                                	$this->add_error( print_r($upload['error'], TRUE), (string)$property->Refnumber );
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
	                                    	$this->add_error( 'Failed inserting image attachment ' . $upload['file'] . ' - ' . print_r($attachment, TRUE), (string)$property->Refnumber );
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

							++$i;
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

					$this->add_log( 'Imported ' . count($media_ids) . ' photos (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->Refnumber );
				}

				// Media - Floorplans
				if ( get_option('propertyhive_floorplans_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();

					update_post_meta( $post_id, '_floorplan_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' floorplan URLs', (string)$property->Refnumber );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_floorplans', TRUE );
					$i = 0;
					if ( isset($property->Floorplan) && (string)$property->Floorplan != '' )
	                {
	                    $media_file_name = (string)$property->Floorplan;
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
	                                	$this->add_error( 'Failed to get filesize of new floorplan file ' . $media_folder . '/' . $media_file_name, (string)$property->Refnumber );
	                                }
	                                
	                                unset($new_image_size);
	                            }
	                            else
	                            {
	                            	$this->add_error( 'Failed to get filesize of existing floorplan file ' . $current_image_path, (string)$property->Refnumber );
	                            }
	                            
	                            unset($current_image_size);
	                        }

	                        if ($upload)
	                        {
								// We've physically received the file
								$upload = wp_upload_bits(trim($media_file_name, '_'), null, file_get_contents($media_folder . '/' . $media_file_name));  
	                            
	                            if( isset($upload['error']) && $upload['error'] !== FALSE )
	                            {
	                            	$this->add_error( print_r($upload['error'], TRUE), (string)$property->Refnumber );
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
	                                	$this->add_error( 'Failed inserting floorplan attachment ' . $upload['file'] . ' - ' . print_r($attachment, TRUE), (string)$property->Refnumber );
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

					$this->add_log( 'Imported ' . count($media_ids) . ' floorplans (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->Refnumber );
				}

				// Media - Brochures
				if ( get_option('propertyhive_brochures_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();

					update_post_meta( $post_id, '_brochure_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' brochure URLs', (string)$property->Refnumber );
    			}
    			else
    			{
					$media_ids = array();
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
	                                	$this->add_error( 'Failed to get filesize of new brochure file ' . $media_folder . '/' . $media_file_name, (string)$property->Refnumber );
	                                }
	                                
	                                unset($new_image_size);
	                            }
	                            else
	                            {
	                            	$this->add_error( 'Failed to get filesize of brochure file ' . $current_image_path, (string)$property->Refnumber );
	                            }
	                            
	                            unset($current_image_size);
	                        }

	                        if ($upload)
	                        {
								// We've physically received the file
								$upload = wp_upload_bits(trim($media_file_name, '_'), null, file_get_contents($media_folder . '/' . $media_file_name));  
	                            
	                            if( isset($upload['error']) && $upload['error'] !== FALSE )
	                            {
	                            	$this->add_error( print_r($upload['error'], TRUE), (string)$property->Refnumber );
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
	                                	$this->add_error( 'Failed inserting brochure attachment ' . $upload['file'] . ' - ' . print_r($attachment, TRUE), (string)$property->Refnumber );
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

					$this->add_log( 'Imported ' . count($media_ids) . ' brochures (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->Refnumber );
				}

				// Media - EPCs
				if ( get_option('propertyhive_epcs_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();

					update_post_meta( $post_id, '_epc_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' EPC URLs', (string)$property->Refnumber );
    			}
    			else
    			{
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
	                                	$this->add_error( 'Failed to get filesize of new EPC file ' . $media_folder . '/' . $media_file_name, (string)$property->Refnumber );
	                                }
	                                
	                                unset($new_image_size);
	                            }
	                            else
	                            {
	                            	$this->add_error( 'Failed to get filesize of EPC file ' . $current_image_path, (string)$property->Refnumber );
	                            }
	                            
	                            unset($current_image_size);
	                        }

	                        if ($upload)
	                        {
								// We've physically received the file
								$upload = wp_upload_bits(trim($media_file_name, '_'), null, file_get_contents($media_folder . '/' . $media_file_name));  
	                            
	                            if( isset($upload['error']) && $upload['error'] !== FALSE )
	                            {
	                            	$this->add_error( print_r($upload['error'], TRUE), (string)$property->Refnumber );
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
	                                	$this->add_error( 'Failed inserting EPC attachment ' . $upload['file'] . ' - ' . print_r($attachment, TRUE), (string)$property->Refnumber );
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

					$this->add_log( 'Imported ' . count($media_ids) . ' EPCs (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->Refnumber );
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

				$this->add_log( 'Imported ' . count($virtual_tours) . ' virtual tours', (string)$property->Refnumber );*/

				do_action( "propertyhive_property_imported_rentman_xml", $post_id, $property );

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
							$this->add_log( 'New meta data for ' . trim($key, '_') . ': ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), (string)$property->Refnumber );
						}
						elseif ( $metadata_before[$key] != $metadata_after[$key] )
						{
							$this->add_log( 'Updated ' . trim($key, '_') . '. Before: ' . ( ( is_array($metadata_before[$key]) ) ? implode(", ", $metadata_before[$key]) : $metadata_before[$key] ) . ', After: ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), (string)$property->Refnumber );
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
							$this->add_log( 'New taxonomy data for ' . $taxonomy_name . ': ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), (string)$property->Refnumber );
						}
						elseif ( $taxonomy_terms_before[$taxonomy_name] != $taxonomy_terms_after[$taxonomy_name] )
						{
							$this->add_log( 'Updated ' . $taxonomy_name . '. Before: ' . ( ( is_array($taxonomy_terms_before[$taxonomy_name]) ) ? implode(", ", $taxonomy_terms_before[$taxonomy_name]) : $taxonomy_terms_before[$taxonomy_name] ) . ', After: ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), (string)$property->Refnumber );
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

		do_action( "propertyhive_post_import_properties_rentman_xml" );

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
			$import_refs[] = (string)$property->Refnumber;
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

				do_action( "propertyhive_property_removed_rentman_xml", $post->ID );
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

		$mapping_values = $this->get_xml_mapping_values('location');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['location'][$mapping_value] = '';
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
                'Available' => 'Available',
                'Under Offer' => 'Under Offer',
                'Unavailable' => 'Unavailable',
                'Withdrawn' => 'Withdrawn',
                'Valuation' => 'Valuation',
                'For Sale' => 'For Sale',
                'ForSale&ToLet' => 'ForSale&ToLet',
                'Sold' => 'Sold',
            );
        }
        if ($custom_field == 'property_type')
        {
        	return array(
                'Detached' => 'Detached',
                'Semi' => 'Semi',
                'Terrace' => 'Terrace',
                'Apartment' => 'Apartment',
                'Flat' => 'Flat',
                'Studio' => 'Studio',
                'Cottage' => 'Cottage',
                'Bungalow' => 'Bungalow',
            );
        }
        if ($custom_field == 'price_qualifier')
        {
        	return array(
        		'1' => 'Asking',
        		'2' => 'Price on application',
        		'3' => 'Guide Price',
        		'4' => 'Offers in excess of',
        		'5' => 'Offers in region of',
        		'6' => 'Fixed',
        	);
        }
        if ($custom_field == 'tenure')
        {
        	return array(
                'Freehold' => 'Freehold',
                'Leasehold' => 'Leasehold',
                'ShareFreehold' => 'ShareFreehold',
            );
        }
        if ($custom_field == 'furnished')
        {
        	return array(
                '1' => 'Unknown',
                '2' => 'Furnished',
                '3' => 'Unfurnished',
                '4' => 'Part Furnished ',
                '5' => 'Furnished / Unfurnished',
            );
        }
    }

}

}