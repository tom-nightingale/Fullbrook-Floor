<?php
/**
 * Class for managing the import process of a SME Professional XML file
 *
 * @package WordPress
 */
if ( class_exists( 'PH_Property_Import_Process' ) ) {

class PH_SME_Professional_XML_Import extends PH_Property_Import_Process {

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

        do_action( "propertyhive_pre_import_properties_sme_professional_xml", $this->properties );
        $this->properties = apply_filters( "propertyhive_sme_professional_xml_properties_due_import", $this->properties );

		$this->add_log( 'Beginning to loop through ' . count($this->properties) . ' properties' );

		$property_row = 1;
		foreach ( $this->properties as $property )
		{
			$this->add_log( 'Importing property ' . $property_row . ' with reference ' . (string)$property->agent_ref, (string)$property->agent_ref );

			$inserted_updated = false;

			$args = array(
	            'post_type' => 'property',
	            'posts_per_page' => 1,
	            'post_status' => 'any',
	            'meta_query' => array(
	            	array(
		            	'key' => $imported_ref_key,
		            	'value' => (string)$property->agent_ref
		            )
	            )
	        );
	        $property_query = new WP_Query($args);
	        
	        if ($property_query->have_posts())
	        {
	        	$this->add_log( 'This property has been imported before. Updating it', (string)$property->agent_ref );

	        	// We've imported this property before
	            while ($property_query->have_posts())
	            {
	                $property_query->the_post();

	                $post_id = get_the_ID();

	                $my_post = array(
				    	'ID'          	 => $post_id,
				    	'post_title'     => wp_strip_all_tags( (string)$property->p_addr_short ),
				    	'post_excerpt'   => (string)$property->p_sdetails,
				    	'post_content' 	 => '',
				    	'post_status'    => 'publish',
				  	);

				 	// Update the post into the database
				    $post_id = wp_update_post( $my_post );

				    if ( is_wp_error( $post_id ) ) 
					{
						$this->add_error( 'ERROR: Failed to update post. The error was as follows: ' . $post_id->get_error_message(), (string)$property->agent_ref );
					}
					else
					{
						$inserted_updated = 'updated';
					}
	            }
	        }
	        else
	        {
	        	$this->add_log( 'This property hasn\'t been imported before. Inserting it', (string)$property->agent_ref );

	        	// We've not imported this property before
				$postdata = array(
					'post_excerpt'   => (string)$property->p_sdetails,
					'post_content' 	 => '',
					'post_title'     => wp_strip_all_tags( (string)$property->p_addr_short ),
					'post_status'    => 'publish',
					'post_type'      => 'property',
					'comment_status' => 'closed',
				);

				$post_id = wp_insert_post( $postdata, true );

				if ( is_wp_error( $post_id ) ) 
				{
					$this->add_error( 'Failed to insert post. The error was as follows: ' . $post_id->get_error_message(), (string)$property->agent_ref );
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
					((string)$property->p_addr_short != '' || (string)$property->p_sdetails != '')
				)
				{
					$my_post = array(
				    	'ID'          	 => $post_id,
				    	'post_title'     => htmlentities(mb_convert_encoding(wp_strip_all_tags( (string)$property->p_addr_short ), 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
				    	'post_excerpt'   => htmlentities(mb_convert_encoding((string)$property->p_sdetails, 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
				    	'post_content' 	 => '',
				    	'post_name' 	 => sanitize_title((string)$property->p_addr_short),
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

				$this->add_log( 'Successfully ' . $inserted_updated . ' post. The post ID is ' . $post_id, (string)$property->agent_ref );

				update_post_meta( $post_id, $imported_ref_key, (string)$property->agent_ref );

				// Address
				update_post_meta( $post_id, '_reference_number', ( ( isset($property->my_unique_id) ) ? (string)$property->my_unique_id : '' ) );
				update_post_meta( $post_id, '_address_name_number', trim( ( ( isset($property->flat_number) ) ? (string)$property->flat_number : '' ) . ' ' . ( ( isset($property->street_number) ) ? (string)$property->street_number : '' ) ) );
				update_post_meta( $post_id, '_address_street', ( ( isset($property->p_addr_short) ) ? (string)$property->p_addr_short : '' ) );
				update_post_meta( $post_id, '_address_two', ( ( isset($property->p_addr2) ) ? (string)$property->p_addr2 : '' ) );
				update_post_meta( $post_id, '_address_three', ( ( isset($property->area) ) ? (string)$property->area : '' ) );
				update_post_meta( $post_id, '_address_four', ( ( isset($property->city) ) ? (string)$property->city : '' ) );
				update_post_meta( $post_id, '_address_postcode', ( ( isset($property->p_postcode) ) ? (string)$property->p_postcode : '' ) );

				$country = 'GB';
				update_post_meta( $post_id, '_address_country', $country );

				if ( isset($property->p_geocode_lat) && isset($property->p_geocode_lon) && (string)$property->p_geocode_lat != '' && (string)$property->p_geocode_lon != '' && (string)$property->p_geocode_lat != '0' && (string)$property->p_geocode_lon != '0' )
				{
					update_post_meta( $post_id, '_latitude', ( ( isset($property->p_geocode_lat) ) ? (string)$property->p_geocode_lat : '' ) );
					update_post_meta( $post_id, '_longitude', ( ( isset($property->p_geocode_lon) ) ? (string)$property->p_geocode_lon : '' ) );
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
							        	$this->add_error( 'Google Geocoding service returned status ' . $status, (string)$property->agent_ref );
							        	sleep(3);
							        }
							    }
						        else
						        {
						        	$this->add_error( 'Failed to parse XML response from Google Geocoding service.', (string)$property->agent_ref );
						        }
							}
							else
					        {
					        	$this->add_error( 'Failed to obtain co-ordinates as allow_url_fopen setting is disabled', (string)$property->agent_ref );
					        }
					    }
					    else
					    {
					    	$this->add_log( 'Not performing Google Geocoding request as no API key present in settings', (string)$property->agent_ref );
					    }
					}
				}			

				// Owner
				add_post_meta( $post_id, '_owner_contact_id', '', true );

				// Record Details
				add_post_meta( $post_id, '_negotiator_id', get_current_user_id(), true );
					
				$office_id = $primary_office_id;
				if ( isset($property->branch_id) )
				{
					if ( isset($_POST['mapped_office'][(string)$property->branch_id]) && $_POST['mapped_office'][(string)$property->branch_id] != '' )
					{
						$office_id = $_POST['mapped_office'][(string)$property->branch_id];
					}
					elseif ( isset($options['offices']) && is_array($options['offices']) && !empty($options['offices']) )
					{
						foreach ( $options['offices'] as $ph_office_id => $branch_code )
						{
							if ( $branch_code == (string)$property->branch_id )
							{
								$office_id = $ph_office_id;
								break;
							}
						}
					}
				}
				update_post_meta( $post_id, '_office_id', $office_id );

				// Residential Details
				update_post_meta( $post_id, '_department', 'residential-lettings' );
				update_post_meta( $post_id, '_bedrooms', ( ( isset($property->p_rooms) ) ? (string)$property->p_rooms : '' ) );
				update_post_meta( $post_id, '_bathrooms', ( ( isset($property->bathrooms) ) ? (string)$property->bathrooms : '' ) );
				update_post_meta( $post_id, '_reception_rooms', ( ( isset($property->public_rooms) ) ? (string)$property->public_rooms : '' ) );

				if ( isset($_POST['mapped_property_type']) )
				{
					$mapping = $_POST['mapped_property_type'];
				}
				else
				{
					$mapping = isset($options['mappings']['property_type']) ? $options['mappings']['property_type'] : array();
				}

				wp_delete_object_term_relationships( $post_id, 'property_type' );
				
				if ( isset($property->p_type) && (string)$property->p_type != '' )
				{
					if ( !empty($mapping) && isset($mapping[(string)$property->p_type]) )
					{
						wp_set_post_terms( $post_id, $mapping[(string)$property->p_type], 'property_type' );
					}
					else
					{
						$this->add_log( 'Property received with a type (' . (string)$property->p_type . ') that is not mapped', (string)$property->agent_ref );

						$options = $this->add_missing_mapping( $mapping, 'property_type', (string)$property->p_type, $import_id );
					}
				}

				// Clean price
				$price = round(preg_replace("/[^0-9.]/", '', (string)$property->p_pcm));

				update_post_meta( $post_id, '_rent', $price );

				$rent_frequency = 'pcm';
				$price_actual = $price;
				
				update_post_meta( $post_id, '_rent_frequency', $rent_frequency );
				update_post_meta( $post_id, '_price_actual', $price_actual );
				
				update_post_meta( $post_id, '_poa', '' );

				update_post_meta( $post_id, '_currency', 'GBP' );

				update_post_meta( $post_id, '_deposit', ( ( isset($property->deposit) ) ? (string)$property->deposit : '' ) );
        		
				$available_date = ( ( isset($property->p_avail) ) ? (string)$property->p_avail : '' );
				if ( $available_date != '' )
				{ 
					$explode_available_date = explode("/", $available_date);
					if ( count($explode_available_date) )
					{
						$available_date = $explode_available_date[2] . '-' . $explode_available_date[1] . '-' . $explode_available_date[0];
					}
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

        		if ( isset($property->furnished) && (string)$property->furnished != '' )
				{
					if ( !empty($mapping) && isset($mapping[(string)$property->furnished]) )
					{
						wp_set_post_terms( $post_id, $mapping[(string)$property->furnished], 'furnished' );
					}
					else
					{
						$this->add_log( 'Property received with a furnished (' . (string)$property->furnished . ') that is not mapped', (string)$property->agent_ref );

						$options = $this->add_missing_mapping( $mapping, 'furnished', (string)$property->furnished, $import_id );
					}
		        }

	            // Parking
				if ( isset($_POST['mapped_parking']) )
				{
					$mapping = $_POST['mapped_parking'];
				}
				else
				{
					$mapping = isset($options['mappings']['parking']) ? $options['mappings']['parking'] : array();
				}

        		wp_delete_object_term_relationships( $post_id, 'parking' );
				
				if ( isset($property->parking) && (string)$property->parking != '' )
				{
					if ( !empty($mapping) && isset($mapping[(string)$property->parking]) )
					{
						wp_set_post_terms( $post_id, $mapping[(string)$property->parking], 'parking' );
					}
					else
					{
						$this->add_log( 'Property received with a parking (' . (string)$property->parking . ') that is not mapped', (string)$property->agent_ref );

						$options = $this->add_missing_mapping( $mapping, 'parking', (string)$property->parking, $import_id );
					}
				}

				// Outside Space
				if ( isset($_POST['mapped_outside_space']) )
				{
					$mapping = $_POST['mapped_outside_space'];
				}
				else
				{
					$mapping = isset($options['mappings']['outside_space']) ? $options['mappings']['outside_space'] : array();
				}

        		wp_delete_object_term_relationships( $post_id, 'outside_space' );
				
				if ( isset($property->garden) && (string)$property->garden != '' )
				{
					if ( !empty($mapping) && isset($mapping[(string)$property->garden]) )
					{
						wp_set_post_terms( $post_id, $mapping[(string)$property->garden], 'outside_space' );
					}
					else
					{
						$this->add_log( 'Property received with an outside_space (' . (string)$property->garden . ') that is not mapped', (string)$property->agent_ref );

						$options = $this->add_missing_mapping( $mapping, 'outside_space', (string)$property->garden, $import_id );
					}
				}
				
				// Marketing
				update_post_meta( $post_id, '_on_market', 'yes' );
				update_post_meta( $post_id, '_featured', ( ( isset($property->featured) && (string)$property->featured == 'true' ) ? 'yes' : '' ) );

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
				
				if ( isset($property->status) && (string)$property->status != '' )
				{
					if ( !empty($mapping) && isset($mapping[(string)$property->status]) )
					{
						wp_set_post_terms( $post_id, $mapping[(string)$property->status], 'availability' );
					}
					else
					{
						$this->add_log( 'Property received with an availability (' . (string)$property->status . ') that is not mapped', (string)$property->agent_ref );

						$options = $this->add_missing_mapping( $mapping, 'availability', (string)$property->status, $import_id );
					}
				}

	            // Features
				$features = array();
				foreach ($property->features->children() as $key => $value) 
				{
					if ( trim((string)$value) != '' )
					{
						$features[] = trim((string)$value);
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
		        // For now put the whole description in one room
				update_post_meta( $post_id, '_rooms', '1' );
				update_post_meta( $post_id, '_room_name_0', '' );
	            update_post_meta( $post_id, '_room_dimensions_0', '' );
	            update_post_meta( $post_id, '_room_description_0', (string)$property->p_details );

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
									// This is a URL
									$url = (string)$image;

									$media_urls[] = array('url' => $url);
								}
							}
						}
					}
					update_post_meta( $post_id, '_photo_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' photo URLs', (string)$property->agent_ref );
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
									// This is a URL
									$url = (string)$image;
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

									        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), (string)$property->agent_ref );
									    }
									    else
									    {
										    $id = media_handle_sideload( $file_array, $post_id, $description, array('post_title' => $filename) );

										    // Check for handle sideload errors.
										    if ( is_wp_error( $id ) ) 
										    {
										        @unlink( $file_array['tmp_name'] );
										        
										        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), (string)$property->agent_ref );
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

					$this->add_log( 'Imported ' . count($media_ids) . ' photos (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->agent_ref );
				}

				// Media - Floorplans
				if ( get_option('propertyhive_floorplans_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();
    				if (isset($property->floor_plan_pdf_file_url) && (string)$property->floor_plan_pdf_file_url != '')
	                {
	                    $media_urls[] = array('url' => (string)$property->floor_plan_pdf_file_url);
					}
					update_post_meta( $post_id, '_floorplan_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' floorplan URLs', (string)$property->agent_ref );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_floorplans', TRUE );
					if (isset($property->floor_plan_pdf_file_url) && (string)$property->floor_plan_pdf_file_url != '')
	                {
						// This is a URL
						$url = (string)$property->floor_plan_pdf_file_url;
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

						        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), (string)$property->agent_ref );
						    }
						    else
						    {
							    $id = media_handle_sideload( $file_array, $post_id, $description, array('post_title' => $filename) );

							    // Check for handle sideload errors.
							    if ( is_wp_error( $id ) ) 
							    {
							        @unlink( $file_array['tmp_name'] );
							        
							        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), (string)$property->agent_ref );
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

					$this->add_log( 'Imported ' . count($media_ids) . ' floorplans (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->agent_ref );
				}

				// Media - EPCs
				if ( get_option('propertyhive_epcs_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();

    				if (isset($property->epc_pdf_file_url) && (string)$property->epc_pdf_file_url != '')
	                {
						// This is a URL
						$url = (string)$property->epc_pdf_file_url;

						$media_urls[] = array('url' => $url);
					}

					update_post_meta( $post_id, '_epc_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' EPC URLs', (string)$property->agent_ref );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_epcs', TRUE );
					if (isset($property->epc_pdf_file_url) && (string)$property->epc_pdf_file_url != '')
	                {
						// This is a URL
						$url = (string)$property->epc_pdf_file_url;
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

						        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), (string)$property->agent_ref );
						    }
						    else
						    {
							    $id = media_handle_sideload( $file_array, $post_id, $description, array('post_title' => $filename) );

							    // Check for handle sideload errors.
							    if ( is_wp_error( $id ) ) 
							    {
							        @unlink( $file_array['tmp_name'] );
							        
							        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), (string)$property->agent_ref );
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

					$this->add_log( 'Imported ' . count($media_ids) . ' EPCs (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->agent_ref );
				}

				// Media - Virtual Tours
				$virtual_tours = array();
				if (isset($property->vir) && (string)$property->vir != '')
                {
                    $virtual_tours[] = (string)$property->vir;          
                }

                update_post_meta( $post_id, '_virtual_tours', count($virtual_tours) );
                foreach ($virtual_tours as $i => $virtual_tour)
                {
                	update_post_meta( $post_id, '_virtual_tour_' . $i, (string)$virtual_tour );
                }

				$this->add_log( 'Imported ' . count($virtual_tours) . ' virtual tours', (string)$property->agent_ref );

				do_action( "propertyhive_property_imported_sme_professional_xml", $post_id, $property );

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
							$this->add_log( 'New meta data for ' . trim($key, '_') . ': ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), (string)$property->agent_ref );
						}
						elseif ( $metadata_before[$key] != $metadata_after[$key] )
						{
							$this->add_log( 'Updated ' . trim($key, '_') . '. Before: ' . ( ( is_array($metadata_before[$key]) ) ? implode(", ", $metadata_before[$key]) : $metadata_before[$key] ) . ', After: ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), (string)$property->agent_ref );
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
							$this->add_log( 'New taxonomy data for ' . $taxonomy_name . ': ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), (string)$property->agent_ref );
						}
						elseif ( $taxonomy_terms_before[$taxonomy_name] != $taxonomy_terms_after[$taxonomy_name] )
						{
							$this->add_log( 'Updated ' . $taxonomy_name . '. Before: ' . ( ( is_array($taxonomy_terms_before[$taxonomy_name]) ) ? implode(", ", $taxonomy_terms_before[$taxonomy_name]) : $taxonomy_terms_before[$taxonomy_name] ) . ', After: ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), (string)$property->agent_ref );
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

		do_action( "propertyhive_post_import_properties_sme_professional_xml" );

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
			$import_refs[] = (string)$property->agent_ref;
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

				do_action( "propertyhive_property_removed_sme_professional_xml", $post->ID );
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

		/*$mapping_values = $this->get_xml_mapping_values('price_qualifier');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['price_qualifier'][$mapping_value] = '';
			}
		}*/

		/*$mapping_values = $this->get_xml_mapping_values('tenure');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['tenure'][$mapping_value] = '';
			}
		}*/

		$mapping_values = $this->get_xml_mapping_values('furnished');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['furnished'][$mapping_value] = '';
			}
		}

		$mapping_values = $this->get_xml_mapping_values('parking');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['parking'][$mapping_value] = '';
			}
		}

		$mapping_values = $this->get_xml_mapping_values('outside_space');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['outside_space'][$mapping_value] = '';
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
                'let' => 'To Let',
                'underoffer' => 'Under Offer',
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
            	'Furnished' => 'Furnished',
            	'Unfurnished' => 'Unfurnished',
            	'Part furnished' => 'Part furnished',
            );
        }
        if ($custom_field == 'parking')
        {
            return array(
            	'Private' => 'Private',
            	'Driveway' => 'Driveway',
            );
        }
        if ($custom_field == 'outside_space')
        {
            return array(
            	'Private Garden' => 'Private Garden',
            );
        }
    }

}

}