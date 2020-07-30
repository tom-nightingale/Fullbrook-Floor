<?php
/**
 * Class for managing the import process of a Decorus XML file
 *
 * @package WordPress
 */
if ( class_exists( 'PH_Property_Import_Process' ) ) {

class PH_Decorus_XML_Import extends PH_Property_Import_Process {

	/**
	 * @var string
	 */
	private $target_file;

	public function __construct( $target_file = '', $instance_id = '' ) 
	{
		$this->target_file = $target_file;
		$this->instance_id = $instance_id;

		if ( isset($_GET['custom_property_import_cron']) )
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
            
			foreach ($xml->v_letitnow as $property)
			{
				if ( (string)$property->prop_status != 'Invisible' )
				{
	                $this->properties[] = $property;
	            }
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

        do_action( "propertyhive_pre_import_properties_decorus_xml", $this->properties );
        $this->properties = apply_filters( "propertyhive_decorus_xml_properties_due_import", $this->properties );

		$this->add_log( 'Beginning to loop through ' . count($this->properties) . ' properties' );

		$property_row = 1;
		foreach ( $this->properties as $property )
		{
			$this->add_log( 'Importing property ' . $property_row .' with reference ' . (string)$property->refno_prop, (string)$property->refno_prop );

			$inserted_updated = false;
			$new_property = false;

			$display_address = array();
			if ( isset($property->prop_name) && (string)$property->prop_name != '' )
			{
				$display_address[] = (string)$property->prop_name;
			}
			if ( isset($property->district) && (string)$property->district != '' )
			{
				$display_address[] = (string)$property->district;
			}
			elseif ( isset($property->city) && (string)$property->city != '' )
			{
				$display_address[] = (string)$property->city;
			}
			$display_address = implode(", ", $display_address);

			$args = array(
	            'post_type' => 'property',
	            'posts_per_page' => 1,
	            'post_status' => 'any',
	            'meta_query' => array(
	            	array(
		            	'key' => $imported_ref_key,
		            	'value' => (string)$property->refno_prop
		            )
	            )
	        );
	        $property_query = new WP_Query($args);
	        
	        if ($property_query->have_posts())
	        {
	        	$this->add_log( 'This property has been imported before. Updating it', (string)$property->refno_prop );

	        	// We've imported this property before
	            while ($property_query->have_posts())
	            {
	                $property_query->the_post();

	                $post_id = get_the_ID();

	                $my_post = array(
				    	'ID'          	 => $post_id,
				    	'post_title'     => wp_strip_all_tags( $display_address ),
				    	'post_excerpt'   => (string)$property->summary,
				    	'post_content' 	 => '',
				    	'post_status'    => 'publish',
				  	);

				 	// Update the post into the database
				    $post_id = wp_update_post( $my_post );

				    if ( is_wp_error( $post_id ) ) 
					{
						$this->add_error( 'ERROR: Failed to update post. The error was as follows: ' . $post_id->get_error_message(), (string)$property->refno_prop );
					}
					else
					{
						$inserted_updated = 'updated';
					}
	            }
	        }
	        else
	        {
	        	$this->add_log( 'This property hasn\'t been imported before. Inserting it', (string)$property->refno_prop );

	        	// We've not imported this property before
				$postdata = array(
					'post_excerpt'   => (string)$property->summary,
					'post_content' 	 => '',
					'post_title'     => wp_strip_all_tags( $display_address ),
					'post_status'    => 'publish',
					'post_type'      => 'property',
					'comment_status' => 'closed',
				);

				$post_id = wp_insert_post( $postdata, true );

				if ( is_wp_error( $post_id ) ) 
				{
					$this->add_error( 'Failed to insert post. The error was as follows: ' . $post_id->get_error_message(), (string)$property->refno_prop );
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
					($display_address != '' || (string)$property->summary != '')
				)
				{
					$my_post = array(
				    	'ID'          	 => $post_id,
				    	'post_title'     => htmlentities(mb_convert_encoding(wp_strip_all_tags( $display_address ), 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
				    	'post_excerpt'   => htmlentities(mb_convert_encoding((string)$property->summary, 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
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

				$this->add_log( 'Successfully ' . $inserted_updated . ' post. The post ID is ' . $post_id, (string)$property->refno_prop );

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
							if ( trim($property->prop_name) != '' ) { $address_to_geocode[] = (string)$property->prop_name; }
							if ( trim($property->district) != '' ) { $address_to_geocode[] = (string)$property->district; }
							if ( trim($property->city) != '' ) { $address_to_geocode[] = (string)$property->city; }
							if ( trim($property->pcode) != '' ) { $address_to_geocode[] = (string)$property->pcode; }

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
						        	$this->add_error( 'Google Geocoding service returned status ' . $status, (string)$property->refno_prop );
						        	sleep(3);
						        }
						    }
						    else
					        {
					        	$this->add_error( 'Failed to parse XML response from Google Geocoding service.', (string)$property->refno_prop );
					        }
						}
						else
				        {
				        	$this->add_error( 'Failed to obtain co-ordinates as allow_url_fopen setting is disabled', (string)$property->refno_prop );
				        }
				    }
				    else
				    {
				    	$this->add_log( 'Not performing Google Geocoding request as no API key present in settings', (string)$property->refno_prop );
				    }
				}

				update_post_meta( $post_id, $imported_ref_key, (string)$property->refno_prop );

				// Address
				update_post_meta( $post_id, '_reference_number', (string)$property->prop_ref );
				update_post_meta( $post_id, '_address_name_number', '' );
				update_post_meta( $post_id, '_address_street', ( ( isset($property->prop_name) ) ? (string)$property->prop_name : '' ) );
				update_post_meta( $post_id, '_address_two', ( ( isset($property->district) ) ? (string)$property->district : '' ) );
				update_post_meta( $post_id, '_address_three', ( ( isset($property->city) ) ? (string)$property->city : '' ) );
				update_post_meta( $post_id, '_address_four', ( ( isset($property->county) ) ? (string)$property->county : '' ) );
				update_post_meta( $post_id, '_address_postcode', ( ( isset($property->pcode) ) ? (string)$property->pcode : '' ) );

				$country = 'GB';
				update_post_meta( $post_id, '_address_country', $country );

            	// Let's just look at address fields to see if we find a match
            	$address_fields_to_check = apply_filters( 'propertyhive_decorus_xml_address_fields_to_check', array('district', 'city', 'county', 'region') );
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
				update_post_meta( $post_id, '_office_id', $office_id );

				// Residential Details
				$department = 'residential-sales';
				if ( (string)$property->adv_rent == 'true' || (string)$property->adv_rent === true )
				{
					$department = 'residential-lettings';
				}

				update_post_meta( $post_id, '_department', $department );
				update_post_meta( $post_id, '_bedrooms', ( ( isset($property->beds) ) ? (string)$property->beds : '' ) );
				update_post_meta( $post_id, '_bathrooms', ( ( isset($property->baths) ) ? (string)$property->baths : '' ) );
				update_post_meta( $post_id, '_reception_rooms', ( ( isset($property->recs) ) ? (string)$property->recs : '' ) );

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

				if ( isset($property->prop_type) )
				{
					if ( !empty($mapping) && isset($mapping[(string)$property->prop_type]) )
					{
						wp_set_post_terms( $post_id, $mapping[(string)$property->prop_type], $prefix . 'property_type' );
					}
					else
					{
						$this->add_log( 'Property received with a type (' . (string)$property->prop_type . ') that is not mapped', (string)$property->refno_prop );

						$options = $this->add_missing_mapping( $mapping, $prefix . 'property_type', (string)$property->prop_type, $import_id );
					}
				}

				// Residential Sales Details
				if ( $department == 'residential-sales' )
				{
					// Clean price
					$price = round(preg_replace("/[^0-9.]/", '', (string)$property->forsale));
					
					update_post_meta( $post_id, '_price', $price );
					update_post_meta( $post_id, '_price_actual', $price );
					update_post_meta( $post_id, '_poa', '' );
				}
				elseif ( $department == 'residential-lettings' )
				{
					// Clean price
					$rent_frequency = 'pcm';
					if ( (string)$property->adv_period == 'pwk' )
					{
						$price = round(preg_replace("/[^0-9.]/", '', (string)$property->rentpwk));
						$rent_frequency = 'pw';
						$price_actual = round(preg_replace("/[^0-9.]/", '', (string)$property->rentpcm));
					}
					else
					{
						$price = round(preg_replace("/[^0-9.]/", '', (string)$property->rentpcm));
					}
					$price_actual = round(preg_replace("/[^0-9.]/", '', (string)$property->rentpcm));

					update_post_meta( $post_id, '_rent', $price );
					update_post_meta( $post_id, '_rent_frequency', $rent_frequency );
					update_post_meta( $post_id, '_price_actual', $price_actual );
					
					update_post_meta( $post_id, '_poa', '' );

					$deposit = round(preg_replace("/[^0-9.]/", '', (string)$property->deposit));
					update_post_meta( $post_id, '_deposit', $deposit );
            		update_post_meta( $post_id, '_available_date', '' );

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
					if ( !empty($mapping) && isset($property->furnished) && isset($mapping[(string)$property->furnished]) )
					{
		                wp_set_post_terms( $post_id, $mapping[(string)$property->furnished], 'furnished' );
		            }
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

        		wp_delete_object_term_relationships( $post_id, 'availability' );
				if ( !empty($mapping) && isset($property->prop_status) && isset($mapping[(string)$property->prop_status]) )
				{
	                wp_set_post_terms( $post_id, $mapping[(string)$property->prop_status], 'availability' );
	            }

	            // Features
				$features = array();
				for ( $i = 1; $i <= 8; ++$i )
				{
					if ( isset($property->{'bullet' . $i}) && trim((string)$property->{'bullet' . $i}) != '' )
					{
						$features[] = trim((string)$property->{'bullet' . $i});
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
		        /*if ( (string)$property->department == 'Commercial' )
				{
					update_post_meta( $post_id, '_descriptions', '1' );
					update_post_meta( $post_id, '_description_name_0', '' );
		            update_post_meta( $post_id, '_description_0', str_replace(array("\r\n", "\n"), "", (string)$property->fullDescription) );
				}
				else
				{*/
					update_post_meta( $post_id, '_rooms', '1' );
					update_post_meta( $post_id, '_room_name_0', '' );
		            update_post_meta( $post_id, '_room_dimensions_0', '' );
		            update_post_meta( $post_id, '_room_description_0', str_replace(array("\r\n", "\n"), "", (string)$property->adv_text) );
		        //}

	            // Media - Images
	            if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
    			{
					$this->add_log( 'Images can\'t be imported whilst media is stored as URLs', (string)$property->refno_prop );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_photos', TRUE );
					for ( $i = 1; $i <= 8; ++$i )
					{
						if ( isset($property->{'image' . $i}) && trim((string)$property->{'image' . $i}) != '' )
						{
							$media_file_name = trim((string)$property->{'image' . $i});
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
	                                    	$this->add_error( 'Failed to get filesize of new image file ' . $media_folder . '/' . $media_file_name, (string)$property->refno_prop );
	                                    }
                                        
                                        unset($new_image_size);
                                    }
                                    else
                                    {
                                    	$this->add_error( 'Failed to get filesize of existing image file ' . $current_image_path, (string)$property->refno_prop );
                                    }
                                    
                                    unset($current_image_size);
                                }

                                if ($upload)
                                {
									// We've physically received the file
									$upload = wp_upload_bits(trim($media_file_name, '_'), null, file_get_contents($media_folder . '/' . $media_file_name));  
	                                
	                                if( isset($upload['error']) && $upload['error'] !== FALSE )
	                                {
	                                	$this->add_error( print_r($upload['error'], TRUE), (string)$property->refno_prop );
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
                                        	$this->add_error( 'Failed inserting image attachment ' . $upload['file'] . ' - ' . print_r($attachment, TRUE), (string)$property->refno_prop );
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

	                            $files_to_unlink[] = $media_folder . '/' . $media_file_name;
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

					$this->add_log( 'Imported ' . count($media_ids) . ' photos (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->refno_prop );
				}

				// Media - Brochures
				if ( get_option('propertyhive_brochures_stored_as', '') == 'urls' )
    			{
    				$this->add_log( 'Brochures can\'t be imported whilst media is stored as URLs', (string)$property->refno_prop );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_brochures', TRUE );
					if (isset($property->brochure) && (string)$property->brochure != '')
	                {
						$media_file_name = trim((string)$property->brochure);
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
                                    	$this->add_error( 'Failed to get filesize of new brochure file ' . $media_folder . '/' . $media_file_name, (string)$property->refno_prop );
                                    }
                                    
                                    unset($new_image_size);
                                }
                                else
                                {
                                	$this->add_error( 'Failed to get filesize of existing brochure file ' . $current_image_path, (string)$property->refno_prop );
                                }
                                
                                unset($current_image_size);
                            }

                            if ($upload)
                            {
								// We've physically received the file
								$upload = wp_upload_bits(trim($media_file_name, '_'), null, file_get_contents($media_folder . '/' . $media_file_name));  
                                
                                if( isset($upload['error']) && $upload['error'] !== FALSE )
                                {
                                	$this->add_error( print_r($upload['error'], TRUE), (string)$property->refno_prop );
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
                                    	$this->add_error( 'Failed inserting brochure attachment ' . $upload['file'] . ' - ' . print_r($attachment, TRUE), (string)$property->refno_prop );
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

                            $files_to_unlink[] = $media_folder . '/' . $media_file_name;
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

					$this->add_log( 'Imported ' . count($media_ids) . ' brochures (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->refno_prop );
				}

				// Media - EPCs
				if ( get_option('propertyhive_epcs_stored_as', '') == 'urls' )
    			{
    				$this->add_log( 'EPCs can\'t be imported whilst media is stored as URLs', (string)$property->refno_prop );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_epcs', TRUE );
					if (isset($property->energy_graph) && (string)$property->energy_graph != '')
	                {
						$media_file_name = trim((string)$property->energy_graph);
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
                                    	$this->add_error( 'Failed to get filesize of new EPC file ' . $media_folder . '/' . $media_file_name, (string)$property->refno_prop );
                                    }
                                    
                                    unset($new_image_size);
                                }
                                else
                                {
                                	$this->add_error( 'Failed to get filesize of existing EPC file ' . $current_image_path, (string)$property->refno_prop );
                                }
                                
                                unset($current_image_size);
                            }

                            if ($upload)
                            {
								// We've physically received the file
								$upload = wp_upload_bits(trim($media_file_name, '_'), null, file_get_contents($media_folder . '/' . $media_file_name));  
                                
                                if( isset($upload['error']) && $upload['error'] !== FALSE )
                                {
                                	$this->add_error( print_r($upload['error'], TRUE), (string)$property->refno_prop );
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
                                    	$this->add_error( 'Failed inserting EPC attachment ' . $upload['file'] . ' - ' . print_r($attachment, TRUE), (string)$property->refno_prop );
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

                            $files_to_unlink[] = $media_folder . '/' . $media_file_name;
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

					$this->add_log( 'Imported ' . count($media_ids) . ' EPCs (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->refno_prop );
				}
				
				do_action( "propertyhive_property_imported_decorus_xml", $post_id, $property );

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
							$this->add_log( 'New meta data for ' . trim($key, '_') . ': ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), (string)$property->refno_prop );
						}
						elseif ( $metadata_before[$key] != $metadata_after[$key] )
						{
							$this->add_log( 'Updated ' . trim($key, '_') . '. Before: ' . ( ( is_array($metadata_before[$key]) ) ? implode(", ", $metadata_before[$key]) : $metadata_before[$key] ) . ', After: ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), (string)$property->refno_prop );
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
							$this->add_log( 'New taxonomy data for ' . $taxonomy_name . ': ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), (string)$property->refno_prop );
						}
						elseif ( $taxonomy_terms_before[$taxonomy_name] != $taxonomy_terms_after[$taxonomy_name] )
						{
							$this->add_log( 'Updated ' . $taxonomy_name . '. Before: ' . ( ( is_array($taxonomy_terms_before[$taxonomy_name]) ) ? implode(", ", $taxonomy_terms_before[$taxonomy_name]) : $taxonomy_terms_before[$taxonomy_name] ) . ', After: ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), (string)$property->refno_prop );
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

		do_action( "propertyhive_post_import_properties_decorus_xml" );

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
			$import_refs[] = (string)$property->refno_prop;
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

				do_action( "propertyhive_property_removed_decorus_xml", $post->ID );
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
		if ( 
			( $import_id == '' && time() > PH_PROPERTYIMPORT_DEPARTMENT_AVAILABILITY_UPDATE ) || 
			( $import_id != '' && $import_id > PH_PROPERTYIMPORT_DEPARTMENT_AVAILABILITY_UPDATE )
		)
		{
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
		}
		else
		{
			$mapping_values = $this->get_xml_mapping_values('availability');
			if ( is_array($mapping_values) && !empty($mapping_values) )
			{
				foreach ($mapping_values as $mapping_value => $text_value)
				{
					$this->mappings['availability'][$mapping_value] = '';
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
        if ($custom_field == 'availability')
        {
            return array(
                'Available' => 'Available',
                'Let Agreed' => 'Let Agreed',
            );
        }
        if ($custom_field == 'sales_availability')
        {
            return array(
                'Available' => 'Available',
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
                'Apartment' => 'Apartment',
                'Bungalow' => 'Bungalow',
                'Chalet' => 'Chalet',
                'Cottage' => 'Cottage',
                'Detached House' => 'Detached House',
                'Flat' => 'Flat',
                'Flat Share' => 'Flat Share',
                'Ground-floor flat' => 'Ground-floor flat',
                'House' => 'House',
                'House Share' => 'House Share',
                'Link detached House' => 'Link detached House',
                'Maisonette' => 'Maisonette',
                'Mews House' => 'Mews House',
                'Office' => 'Office',
                'Penthouse' => 'Penthouse',
                'Semi-detached House' => 'Semi-detached House',
                'Serviced Apartment' => 'Serviced Apartment',
                'Studio Flat' => 'Studio Flat',
                'Terraced House' => 'Terraced House',
                'Town House' => 'Town House',
                'Unit' => 'Unit',
            );
        }
        if ($custom_field == 'furnished')
        {
            return array(
            	'Furnished' => 'Furnished',
            	'Unfurnished' => 'Unfurnished',
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