<?php
/**
 * Class for managing the import process of an Agency Pilot JSON file
 *
 * @package WordPress
 */
if ( class_exists( 'PH_Property_Import_Process' ) ) {

class PH_Agency_Pilot_JSON_Import extends PH_Property_Import_Process {

	/**
	 * @var string
	 */
	private $target_file;

	/**
	 * @var array
	 */
	private $terms;

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
		$json = json_decode( file_get_contents( 'https://' . $options['url'] . '/services/getPropertyJSON.aspx?w=103&fr=true&full=true&a=true&pw=' . $options['password'] ), TRUE );

		if ($json !== FALSE && isset($json['CRITERIA']) && !empty($json['CRITERIA']))
		{
			$this->add_log("Parsing properties");
			
            $properties = $json['CRITERIA'];

			$this->add_log("Found " . count($properties) . " properties in JSON ready for parsing");

			foreach ($properties as $property)
			{
				$this->properties[] = $property;
			}
        }
        else
        {
        	// Failed to parse JSON
        	$this->add_error( 'Failed to parse properties JSON file. Possibly invalid JSON' );

        	return false;
        }

        $json = json_decode( file_get_contents( 'https://' . $options['url'] . '/services/getPropertyJSON.aspx?w=1145&pw=' . $options['password'] ), TRUE );

		if ($json !== FALSE && isset($json['S_TERMS']) && !empty($json['S_TERMS']))
		{
			$this->add_log("Parsing terms");
			
            $terms = $json['S_TERMS'];

			$this->add_log("Found " . count($properties) . " terms in JSON ready for parsing");

			foreach ($terms as $term)
			{
				$this->terms[$term['NO']] = strtolower($term['NAME']);
			}
        }
        else
        {
        	// Failed to parse JSON
        	$this->add_error( 'Failed to parse terms JSON file. Possibly invalid JSON' );

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

        do_action( "propertyhive_pre_import_properties_agency_pilot_json", $this->properties );
        $this->properties = apply_filters( "propertyhive_agency_pilot_json_properties_due_import", $this->properties );

		$this->add_log( 'Beginning to loop through ' . count($this->properties) . ' properties' );

		$property_row = 1;
		foreach ( $this->properties as $property )
		{
			$this->add_log( 'Importing property with reference ' . $property['Key'], $property['Key'] );

			$inserted_updated = false;

			$args = array(
	            'post_type' => 'property',
	            'posts_per_page' => 1,
	            'post_status' => 'any',
	            'meta_query' => array(
	            	array(
		            	'key' => $imported_ref_key,
		            	'value' => $property['Key']
		            )
	            )
	        );
	        $property_query = new WP_Query($args);

	        $display_address = $property['Full_Address'];
            if ($display_address == '')
            {
                $display_address = $property['Street'];
                if ($property['District'] != '')
                {
                    if ($display_address != '')
                    {
                        $display_address .= ', ';
                    }
                    $display_address .= $property['District'];
                }
                if ($property['Town'] != '')
                {
                    if ($display_address != '')
                    {
                        $display_address .= ', ';
                    }
                    $display_address .= $property['Town'];
                }
            }
	        
	        if ($property_query->have_posts())
	        {
	        	$this->add_log( 'This property has been imported before. Updating it', $property['Key'] );

	        	// We've imported this property before
	            while ($property_query->have_posts())
	            {
	                $property_query->the_post();

	                $post_id = get_the_ID();

	                $my_post = array(
				    	'ID'          	 => $post_id,
				    	'post_title'     => wp_strip_all_tags( $display_address ),
				    	'post_excerpt'   => ( (isset($property['Description'])) ? $property['Description'] : '' ),
				    	'post_content' 	 => '',
				    	'post_status'    => 'publish',
				  	);

				 	// Update the post into the database
				    $post_id = wp_update_post( $my_post );

				    if ( is_wp_error( $post_id ) ) 
					{
						$this->add_error( 'Failed to update post. The error was as follows: ' . $post_id->get_error_message(), $property['Key'] );
					}
					else
					{
						$inserted_updated = 'updated';
					}
	            }
	        }
	        else
	        {
	        	$this->add_log( 'This property hasn\'t been imported before. Inserting it', $property['Key'] );

	        	// We've not imported this property before
				$postdata = array(
					'post_excerpt'   => ( (isset($property['Description'])) ? $property['Description'] : '' ),
					'post_content' 	 => '',
					'post_title'     => wp_strip_all_tags( $display_address ),
					'post_status'    => 'publish',
					'post_type'      => 'property',
					'comment_status' => 'closed',
				);

				$post_id = wp_insert_post( $postdata, true );

				if ( is_wp_error( $post_id ) ) 
				{
					$this->add_error( 'Failed to insert post. The error was as follows: ' . $post_id->get_error_message(), $property['Key'] );
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
				    	'post_excerpt'   => ( (isset($property['Description'])) ? htmlentities(mb_convert_encoding(wp_strip_all_tags( $property['Description'] ), 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8") : '' ),
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

				$this->add_log( 'Successfully ' . $inserted_updated . ' post. The post ID is ' . $post_id, $property['Key'] );

				update_post_meta( $post_id, $imported_ref_key, $property['Key'] );

				// Address
				update_post_meta( $post_id, '_reference_number',  ( ( isset($property['Number']) ) ? $property['Number'] : '' ) );
				update_post_meta( $post_id, '_address_name_number', trim( ( ( isset($property['Building_Name']) ) ? $property['Building_Name'] : '' ) . ' ' . ( ( isset($property['Address']['Number']) ) ? $property['Address']['Number'] : '' ) ) );
				update_post_meta( $post_id, '_address_street', ( ( isset($property['Street']) ) ? $property['Street'] : '' ) );
				update_post_meta( $post_id, '_address_two', ( ( isset($property['District']) ) ? $property['District'] : '' ) );
				update_post_meta( $post_id, '_address_three', ( ( isset($property['Town']) ) ? $property['Town'] : '' ) );
				update_post_meta( $post_id, '_address_four', '' );
				update_post_meta( $post_id, '_address_postcode', ( ( isset($property['Postcode']) ) ? $property['Postcode'] : '' ) );

				$country = get_option( 'propertyhive_default_country', 'GB' );
				update_post_meta( $post_id, '_address_country', $country );

				// Check main address fields and see if this location exists as a taxonomy to try and assign properties to location
				$address_fields_to_check = apply_filters( 'propertyhive_agency_pilot_json_address_fields_to_check', array('District', 'Town') );
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
				update_post_meta( $post_id, '_latitude', ( ( isset($property['Latitude']) ) ? $property['Latitude'] : '' ) );
				update_post_meta( $post_id, '_longitude', ( ( isset($property['Longitude']) ) ? $property['Longitude'] : '' ) );

				// Owner
				add_post_meta( $post_id, '_owner_contact_id', '', true );

				// Record Details
				add_post_meta( $post_id, '_negotiator_id', get_current_user_id(), true );
				
				$office_id = $primary_office_id;
				if ( isset($_POST['mapped_office'][$property['PartnerOffice']]) && $_POST['mapped_office'][$property['PartnerOffice']] != '' )
				{
					$office_id = $_POST['mapped_office'][$property['PartnerOffice']];
				}
				elseif ( isset($options['offices']) && is_array($options['offices']) && !empty($options['offices']) )
				{
					foreach ( $options['offices'] as $ph_office_id => $branch_code )
					{
						if ( $branch_code == $property['PartnerOffice'] )
						{
							$office_id = $ph_office_id;
							break;
						}
					}
				}
				update_post_meta( $post_id, '_office_id', $office_id );

				// Commercial Details
				update_post_meta( $post_id, '_department', 'commercial' );

				update_post_meta( $post_id, '_for_sale', '' );
        		update_post_meta( $post_id, '_to_rent', '' );

        		if ( $property['Freehold'] == '1' )
                {
                    update_post_meta( $post_id, '_for_sale', 'yes' );

                    update_post_meta( $post_id, '_commercial_price_currency', 'GBP' );

                    $price = preg_replace("/[^0-9.]/", '', $property['Freehold_From']);
                    if ( $price == '' )
                    {
                        $price = preg_replace("/[^0-9.]/", '', $property['Freehold_To']);
                    }
                    update_post_meta( $post_id, '_price_from', $price );

                    $price = preg_replace("/[^0-9.]/", '', $property['Freehold_To']);
                    if ( $price == '' )
                    {
                        $price = preg_replace("/[^0-9.]/", '', $property['Freehold_From']);
                    }
                    update_post_meta( $post_id, '_price_to', $price );

                    update_post_meta( $post_id, '_price_units', '' );

                    $poa = '';
                    if ( $property['Freehold_Term'] != '' && isset($this->terms[$property['Freehold_Term']]) )
                    {
	                    if ( strpos( $this->terms[$property['Freehold_Term']], 'application' ) !== FALSE || strpos( $this->terms[$property['Freehold_Term']], 'poa' ) !== FALSE )
	                    {
	                    	$poa = 'yes';
	                    }
	                }
                    update_post_meta( $post_id, '_price_poa', $poa );

                    // Tenure
		            if ( isset($_POST['mapped_commercial_tenure']) )
					{
						$mapping = $_POST['mapped_commercial_tenure'];
					}
					else
					{
						$mapping = isset($options['mappings']['commercial_tenure']) ? $options['mappings']['commercial_tenure'] : array();
					}

		            wp_delete_object_term_relationships( $post_id, 'commercial_tenure' );
					if ( !empty($mapping) && isset($property['Freehold_Term']) && isset($mapping[$property['Freehold_Term']]) )
					{
			            wp_set_post_terms( $post_id, $mapping[$property['Freehold_Term']], 'commercial_tenure' );
		            }
                }

                if ( $property['Leasehold'] == '1' )
                {
                    update_post_meta( $post_id, '_to_rent', 'yes' );

                    update_post_meta( $post_id, '_commercial_rent_currency', 'GBP' );

                    $rent = preg_replace("/[^0-9.]/", '', $property['Leasehold_From']);
                    if ( $rent == '' )
                    {
                        $rent = preg_replace("/[^0-9.]/", '', $property['Leasehold_To']);
                    }
                    update_post_meta( $post_id, '_rent_from', $rent );

                    $rent = preg_replace("/[^0-9.]/", '', $property['Leasehold_To']);
                    if ( $rent == '' )
                    {
                        $rent = preg_replace("/[^0-9.]/", '', $property['Leasehold_From']);
                    }
                    update_post_meta( $post_id, '_rent_to', $rent );

                    $rent_units = 'pa';
                    if ( $property['Leasehold_Term'] != '' && isset($this->terms[$property['Leasehold_Term']]) )
                    {
                    	if ( strpos( $this->terms[$property['Leasehold_Term']], 'month' ) !== FALSE || strpos( $this->terms[$property['Leasehold_Term']], 'pcm' ) !== FALSE )
	                    {
	                    	$rent_units = 'pcm';
	                    }
	                    elseif ( strpos( $this->terms[$property['Leasehold_Term']], 'week' ) !== FALSE || strpos( $this->terms[$property['Leasehold_Term']], 'pw' ) !== FALSE )
	                    {
	                    	$rent_units = 'pw';
	                    }
	                    elseif ( strpos( $this->terms[$property['Leasehold_Term']], 'quarter' ) !== FALSE || strpos( $this->terms[$property['Leasehold_Term']], 'pq' ) !== FALSE )
	                    {
	                    	$rent_units = 'pq';
	                    }
	                    elseif ( strpos( $this->terms[$property['Leasehold_Term']], 'sq ft' ) !== FALSE || strpos( $this->terms[$property['Leasehold_Term']], 'foot' ) !== FALSE )
	                    {
	                    	$rent_units = 'psf';
	                    }
	                    elseif ( strpos( $this->terms[$property['Leasehold_Term']], 'sq m' ) !== FALSE || strpos( $this->terms[$property['Leasehold_Term']], 'metre' ) !== FALSE )
	                    {
	                    	$rent_units = 'psf';
	                    }
                    }
                    update_post_meta( $post_id, '_rent_units', $rent_units);

                    $poa = '';
                    if ( $property['Leasehold_Term'] != '' && isset($this->terms[$property['Leasehold_Term']]) )
                    {
	                    if ( strpos( $this->terms[$property['Leasehold_Term']], 'application' ) !== FALSE || strpos( $this->terms[$property['Leasehold_Term']], 'poa' ) !== FALSE )
	                    {
	                    	$poa = 'yes';
	                    }
	                }
                    update_post_meta( $post_id, '_rent_poa', $poa );
                }

                // Store price in common currency (GBP) used for ordering
	            $ph_countries = new PH_Countries();
	            $ph_countries->update_property_price_actual( $post_id );

	            $size = preg_replace("/[^0-9.]/", '', $property['Min_Size']);
	            if ( $size == '' )
	            {
	                $size = preg_replace("/[^0-9.]/", '', $property['Max_Size']);
	            }
	            update_post_meta( $post_id, '_floor_area_from', $size );

	            update_post_meta( $post_id, '_floor_area_from_sqft', convert_size_to_sqft( $size, 'sqft' ) );

	            $size = preg_replace("/[^0-9.]/", '', $property['Max_Size']);
	            if ( $size == '' )
	            {
	                $size = preg_replace("/[^0-9.]/", '', $property['Min_Size']);
	            }
	            update_post_meta( $post_id, '_floor_area_to', $size );

	            update_post_meta( $post_id, '_floor_area_to_sqft', convert_size_to_sqft( $size, 'sqft' ) );

	            update_post_meta( $post_id, '_floor_area_units', 'sqft' );

	            $size = '';

	            update_post_meta( $post_id, '_site_area_from', $size );

	            update_post_meta( $post_id, '_site_area_from_sqft', convert_size_to_sqft( $size, 'sqft' ) );

	            update_post_meta( $post_id, '_site_area_to', $size );

	            update_post_meta( $post_id, '_site_area_to_sqft', convert_size_to_sqft( $size, 'sqft' ) );

	            update_post_meta( $post_id, '_site_area_units', 'sqft' );
				
				// Property Type
				if ( isset($_POST['mapped_commercial_property_type']) )
				{
					$mapping = $_POST['mapped_commercial_property_type'];
				}
				else
				{
					$mapping = isset($options['mappings']['commercial_property_type']) ? $options['mappings']['commercial_property_type'] : array();
				}

				wp_delete_object_term_relationships( $post_id, 'commercial_property_type' );
				
				if ( isset($property['UnitIDs']) && $property['UnitIDs'] != '' )
				{
					$explode_unit_ids = explode(",", $property['UnitIDs']);
					$term_ids = array();

					foreach ( $explode_unit_ids as $unit_id )
					{
						if ( !empty($mapping) && isset($mapping[$unit_id]) )
						{
							$term_ids[] = $mapping[$unit_id];
				            
			            }
					}

					if ( !empty($term_ids) )
					{
						wp_set_post_terms( $post_id, $term_ids, 'commercial_property_type' );
					}					
		            else
		            {
		            	$this->add_log( 'Property received with type (' . $property['UnitIDs'] . ') that are not mapped', $property['Key'] );

		            	$options = $this->add_missing_mapping( $mapping, 'commercial_property_type', $property['UnitIDs'], $import_id );
		            }
		        }

	            update_post_meta( $post_id, '_on_market', 'yes' );
				update_post_meta( $post_id, '_featured', '' );

				// Availability
				if ( isset($_POST['mapped_commercial_availability']) )
				{
					$mapping = $_POST['mapped_commercial_availability'];
				}
				else
				{
					$mapping = isset($options['mappings']['commercial_availability']) ? $options['mappings']['commercial_availability'] : array();
				}

        		wp_delete_object_term_relationships( $post_id, 'availability' );
        		if ( isset($property['Market_Status']) && $property['Market_Status'] != '' )
        		{
					if ( !empty($mapping) && isset($mapping[$property['Market_Status']]) )
					{
		                wp_set_post_terms( $post_id, $mapping[$property['Market_Status']], 'availability' );
		            }
		            else
		            {
		            	$this->add_log( 'Property received with an availability (' . $property['Market_Status'] . ') that is not mapped', $property['Key'] );

		            	$options = $this->add_missing_mapping( $mapping, 'availability', $property['Market_Status'], $import_id );
		            }
		        }

				$features = array();
				for ($i = 1; $i <= 10; ++$i)
				{
					if ( isset( $property['BulletPoint' . $i] ) && $property['BulletPoint' . $i] != '' )
					{
						$features[] = $property['BulletPoint' . $i];
					}
				}

				update_post_meta( $post_id, '_features', count( $features ) );
	
        		$i = 0;
		        foreach ( $features as $feature )
		        {
		            update_post_meta( $post_id, '_feature_' . $i, $feature );
		            ++$i;
		        }

				// Media - Images
				if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();
    				if (isset($property['AllPhotoKeys']) && $property['AllPhotoKeys'] != '')
	                {
	                	$images_array = explode(",", $property['AllPhotoKeys']);

						foreach ( $images_array as $image )
						{
							$image = str_replace("_sm.", ".", $image);
							$image = str_replace("_web.", ".", $image);
							$url = 'https://' . $options['url'] . '/store/property/' . $image;

							$media_urls[] = array('url' => $url);
						}
					}
					update_post_meta( $post_id, '_photo_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' photo URLs', $property['Key'] );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_photos', TRUE );

					if (isset($property['AllPhotoKeys']) && $property['AllPhotoKeys'] != '')
	                {
	                	$images_array = explode(",", $property['AllPhotoKeys']);

						foreach ( $images_array as $image )
						{
							$image = str_replace("_sm.", ".", $image);
							$image = str_replace("_web.", ".", $image);
							$url = 'https://' . $options['url'] . '/store/property/' . $image;

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
							        'name' => basename( $url ),
							        'tmp_name' => $tmp
							    );

							    // Check for download errors
							    if ( is_wp_error( $tmp ) ) 
							    {
							        @unlink( $file_array[ 'tmp_name' ] );

							        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['Key'] );
							    }
							    else
							    {
								    $id = media_handle_sideload( $file_array, $post_id, $description );

								    // Check for handle sideload errors.
								    if ( is_wp_error( $id ) ) 
								    {
								        @unlink( $file_array['tmp_name'] );
								        
								        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['Key'] );
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

					$this->add_log( 'Imported ' . count($media_ids) . ' photos (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property['Key'] );
				}

				// Media - Floorplans
				if ( get_option('propertyhive_floorplans_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();
    				if (isset($property['PhotoFloorPlanURL']) && $property['PhotoFloorPlanURL'] != '')
	                {
						$image = str_replace("_sm.", ".", $property['PhotoFloorPlanURL']);
						$image = str_replace("_web.", ".", $image);
						$url = $image;

						$media_urls[] = array('url' => $url);
					}
					update_post_meta( $post_id, '_floorplan_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' floorplan URLs', $property['Key'] );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_floorplans', TRUE );

					if (isset($property['PhotoFloorPlanURL']) && $property['PhotoFloorPlanURL'] != '')
	                {
						$image = str_replace("_sm.", ".", $property['PhotoFloorPlanURL']);
						$image = str_replace("_web.", ".", $image);
						$url = $image;

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
						        'name' => basename( $url ),
						        'tmp_name' => $tmp
						    );

						    // Check for download errors
						    if ( is_wp_error( $tmp ) ) 
						    {
						        @unlink( $file_array[ 'tmp_name' ] );

						        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['Key'] );
						    }
						    else
						    {
							    $id = media_handle_sideload( $file_array, $post_id, $description );

							    // Check for handle sideload errors.
							    if ( is_wp_error( $id ) ) 
							    {
							        @unlink( $file_array['tmp_name'] );
							        
							        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['Key'] );
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

					$this->add_log( 'Imported ' . count($media_ids) . ' floorplans (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property['Key'] );
				}

				// Media - Brochures
				if ( get_option('propertyhive_brochures_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();
    				
    				$brochure_urls = array();
					if ( isset($property['BrochureURL1']) && !empty($property['BrochureURL1']) )
					{
						$brochure_urls[] = 'https://' . $options['url'] . '/store/documents/other/' . $property['BrochureURL1'];
					}
					if ( isset($property['BrochureURL2']) && !empty($property['BrochureURL2']) )
					{
						$brochure_urls[] = 'https://' . $options['url'] . '/store/documents/other/' . $property['BrochureURL2'];
					}
					if ( isset($property['BrochureURL3']) && !empty($property['BrochureURL3']) )
					{
						$brochure_urls[] = 'https://' . $options['url'] . '/store/documents/other/' . $property['BrochureURL3'];
					}
					if ( !empty($brochure_urls) )
					{
						foreach ( $brochure_urls as $brochure )
						{
							// This is a URL
							$url = $brochure;

							$media_urls[] = array('url' => $url);
						}
					}
					update_post_meta( $post_id, '_brochure_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' brochure URLs', $property['Key'] );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_brochures', TRUE );

					$brochure_urls = array();
					if ( isset($property['BrochureURL1']) && !empty($property['BrochureURL1']) )
					{
						$brochure_urls[] = 'https://' . $options['url'] . '/store/documents/other/' . $property['BrochureURL1'];
					}
					if ( isset($property['BrochureURL2']) && !empty($property['BrochureURL2']) )
					{
						$brochure_urls[] = 'https://' . $options['url'] . '/store/documents/other/' . $property['BrochureURL2'];
					}
					if ( isset($property['BrochureURL3']) && !empty($property['BrochureURL3']) )
					{
						$brochure_urls[] = 'https://' . $options['url'] . '/store/documents/other/' . $property['BrochureURL3'];
					}
					if ( !empty($brochure_urls) )
					{
						foreach ( $brochure_urls as $brochure )
						{
							// This is a URL
							$url = $brochure;
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
							    $explode_url = explode( "?", basename( $url ) );
							    $file_array = array(
							        'name' => $explode_url[0],
							        'tmp_name' => $tmp
							    );

							    // Check for download errors
							    if ( is_wp_error( $tmp ) ) 
							    {
							        @unlink( $file_array[ 'tmp_name' ] );

							        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['Key'] );
							    }
							    else
							    {
								    $id = media_handle_sideload( $file_array, $post_id, $description );
								    
								    // Check for handle sideload errors.
								    if ( is_wp_error( $id ) ) 
								    {
								        @unlink( $file_array['tmp_name'] );
								        
								        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['Key'] );
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

					$this->add_log( 'Imported ' . count($media_ids) . ' brochures (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property['Key'] );
				}

				do_action( "propertyhive_property_imported_agency_pilot_json", $post_id, $property );

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
							$this->add_log( 'New meta data for ' . trim($key, '_') . ': ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), $property['Key'] );
						}
						elseif ( $metadata_before[$key] != $metadata_after[$key] )
						{
							$this->add_log( 'Updated ' . trim($key, '_') . '. Before: ' . ( ( is_array($metadata_before[$key]) ) ? implode(", ", $metadata_before[$key]) : $metadata_before[$key] ) . ', After: ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), $property['Key'] );
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
							$this->add_log( 'New taxonomy data for ' . $taxonomy_name . ': ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), $property['Key'] );
						}
						elseif ( $taxonomy_terms_before[$taxonomy_name] != $taxonomy_terms_after[$taxonomy_name] )
						{
							$this->add_log( 'Updated ' . $taxonomy_name . '. Before: ' . ( ( is_array($taxonomy_terms_before[$taxonomy_name]) ) ? implode(", ", $taxonomy_terms_before[$taxonomy_name]) : $taxonomy_terms_before[$taxonomy_name] ) . ', After: ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), $property['Key'] );
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

		do_action( "propertyhive_post_import_properties_agency_pilot_json" );

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
				$import_refs[] = $property['Key'];
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

					do_action( "propertyhive_property_removed_agency_pilot_json", $post->ID );
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
		$mapping_values = $this->get_agency_pilot_mapping_values('commercial_availability', $import_id);
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['commercial_availability'][$mapping_value] = '';
			}
		}

		$mapping_values = $this->get_agency_pilot_mapping_values('commercial_property_type', $import_id);
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['commercial_property_type'][$mapping_value] = '';
			}
		}

		/*$mapping_values = $this->get_agency_pilot_mapping_values('price_qualifier', $import_id);
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['price_qualifier'][$mapping_value] = '';
			}
		}*/

		$mapping_values = $this->get_agency_pilot_mapping_values('commercial_tenure', $import_id);
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['commercial_tenure'][$mapping_value] = '';
			}
		}

		$mapping_values = $this->get_agency_pilot_mapping_values('office', $import_id);
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['office'][$mapping_value] = '';
			}
		}
		
		return $this->mappings;
	}

	public function get_mapping_values($custom_field, $import_id = '')
	{
		return $this->get_agency_pilot_mapping_values($custom_field, $import_id);
	}

	public function get_agency_pilot_mapping_values($custom_field, $import_id = '') 
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

        if ($custom_field == 'commercial_availability')
        {
        	$json = json_decode( file_get_contents( 'https://' . $options['url'] . '/services/getPropertyJSON.aspx?w=1143&pw=' . $options['password'] ), TRUE );

			if ($json !== FALSE && isset($json['S_MKTSTATUS']) && !empty($json['S_MKTSTATUS']))
			{
				$options = array();
				foreach ( $json['S_MKTSTATUS'] as $value )
				{
					$options[$value['NO']] = $value['NAME'];
				}
				ksort($options);
				return $options;
			}
			else
			{
				return array(
	            	'' => ''
	            );
			}
        }
        if ($custom_field == 'commercial_property_type')
        {
        	$json = json_decode( file_get_contents( 'https://' . $options['url'] . '/services/getPropertyJSON.aspx?w=1146&pw=' . $options['password'] ), TRUE );

			if ($json !== FALSE && isset($json['S_UNIT']) && !empty($json['S_UNIT']))
			{
				$options = array();
				foreach ( $json['S_UNIT'] as $value )
				{
					$options[$value['NO']] = $value['NAME'];
				}
				ksort($options);
				return $options;
			}
			else
			{
				return array(
	            	'' => ''
	            );
			}
        }
        /*if ($custom_field == 'price_qualifier')
        {
        	return array(
        		'' => ''
        	);
        }*/
        if ($custom_field == 'commercial_tenure')
        {
            $json = json_decode( file_get_contents( 'https://' . $options['url'] . '/services/getPropertyJSON.aspx?w=1145&pw=' . $options['password'] ), TRUE );

			if ($json !== FALSE && isset($json['S_TERMS']) && !empty($json['S_TERMS']))
			{
				$options = array();
				foreach ( $json['S_TERMS'] as $value )
				{
					$options[$value['NO']] = $value['NAME'];
				}
				ksort($options);
				return $options;
			}
			else
			{
				return array(
	            	'' => ''
	            );
			}
        }
    }
}

}