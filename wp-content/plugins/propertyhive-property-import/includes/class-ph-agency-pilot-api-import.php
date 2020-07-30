<?php
/**
 * Class for managing the import process using the Agency Pilot REST API
 *
 * @package WordPress
 */
if ( class_exists( 'PH_Property_Import_Process' ) ) {

class PH_Agency_Pilot_API_Import extends PH_Property_Import_Process {

	/**
	 * @var string
	 */
	private $target_file;

	/**
	 * @var string
	 */
	private $token;

	public function __construct( $instance_id = '' ) 
	{
		$this->instance_id = $instance_id;

		if ( $this->instance_id != '' && isset($_GET['custom_property_import_cron']) )
	    {
	    	$current_user = wp_get_current_user();

	    	$this->add_log("Executed manually by " . ( ( isset($current_user->display_name) ) ? $current_user->display_name : '' ) );
	    }
	}

	public function get_token($options)
	{
		$token = '';

		$response = wp_remote_post(
			$options['url'] . '/api/CurrentVersion/Token',
			array(
				'method' => 'POST',
				'timeout' => 45,
				'headers' => array( 
					'Content-Type' => 'application/xwww-form-urlencoded', 
					'Accept' => 'application/json' 
				),
				'body' => array( 
					'grant_type' => 'client_credentials', 
					'client_id' => ( ( isset($options['client_id']) ) ? $options['client_id'] : '' ), 
					'client_secret' => ( ( isset($options['client_secret']) ) ? $options['client_secret'] : '' ) 
				),
		    )
		);

		if ( is_wp_error( $response ) ) 
		{
			$this->add_error( 'Failed to request token: ' . $response->get_error_message() );
			return false;
		}
		else
		{
			$body = json_decode($response['body'], TRUE);

			if ( $body === false )
			{
				$this->add_error( 'Failed to decode token request body: ' . $response['body'] );
				return false;
			}
			else
			{
				if ( isset($body['access_token']) )
				{
					$token = $body['access_token'];

					$this->add_log("Got token " . $token );

					return $token;
				}
				else
				{
					$this->add_error( 'Failed to get access_token part of response body: ' . $response['body'] );
					return false;
				}
			}
		}
	}

	public function parse( $options )
	{
		$this->token = $this->get_token($options);

		if ( $this->token === false )
		{
			return false;
		}

		$response = wp_remote_post(
			$options['url'] . '/api/CurrentVersion/PropertyFeed/Property',
			array(
				'method' => 'POST',
				'timeout' => 45,
				'headers' => array( 
					'Authorization' => 'Bearer ' . $this->token, 
					'Content-Type' => 'application/json', 
					'Accept' => 'application/json' 
				),
				'body' => '{
					"DisplayOptions":{
						"Additional": true,
						"Photos": true,
						"DocumentMedia": true,
						"Floors": true,
						"Agents": true,
						"Auctions": false,
						"SystemDetails": true,
						"Categories": true
					},
					"FilterOptions":{
						"ActiveOnly": true,
						"ShowOnInternet": true
					}
				}',
		    )
		);

		if ( is_wp_error( $response ) ) 
		{
			$this->add_error( 'Failed to request properties: ' . $response->get_error_message() );
			return false;
		}
		else
		{
			$body = json_decode($response['body'], TRUE);

			if ( $body === false )
			{
				$this->add_error( 'Failed to decode properties request body: ' . $response['body'] );
				return false;
			}
			else
			{
				foreach ( $body as $property ) 
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

        do_action( "propertyhive_pre_import_properties_agency_pilot_api", $this->properties, $options, $this->token );
        $this->properties = apply_filters( "propertyhive_agency_pilot_api_properties_due_import", $this->properties, $options, $this->token );

		$this->add_log( 'Beginning to loop through ' . count($this->properties) . ' properties' );

		$property_row = 1;
		foreach ( $this->properties as $property )
		{
			$this->add_log( 'Importing property ' . $property_row . ' with reference ' . $property['ID'], $property['ID'] );

			$inserted_updated = false;

			$args = array(
	            'post_type' => 'property',
	            'posts_per_page' => 1,
	            'post_status' => 'any',
	            'meta_query' => array(
	            	array(
		            	'key' => $imported_ref_key,
		            	'value' => $property['ID']
		            )
	            )
	        );
	        $property_query = new WP_Query($args);

	        $display_address = ( ( isset($property['Address']['DisplayAddress']) ) ? $property['Address']['DisplayAddress'] : '' );
            if ($display_address == '')
            {
                $display_address = $property['Address']['Street'];
                if ($property['Address']['Town'] != '')
                {
                    if ($display_address != '')
                    {
                        $display_address .= ', ';
                    }
                    $display_address .= $property['Address']['Town'];
                }
            }
	        
	        if ($property_query->have_posts())
	        {
	        	$this->add_log( 'This property has been imported before. Updating it', $property['ID'] );

	        	// We've imported this property before
	            while ($property_query->have_posts())
	            {
	                $property_query->the_post();

	                $post_id = get_the_ID();

	                $my_post = array(
				    	'ID'          	 => $post_id,
				    	'post_title'     => wp_strip_all_tags( $display_address ),
				    	'post_excerpt'   => $property['Description'],
				    	'post_content' 	 => '',
				    	'post_status'    => 'publish',
				  	);

				 	// Update the post into the database
				    $post_id = wp_update_post( $my_post );

				    if ( is_wp_error( $post_id ) ) 
					{
						$this->add_error( 'Failed to update post. The error was as follows: ' . $post_id->get_error_message(), $property['ID'] );
					}
					else
					{
						$inserted_updated = 'updated';
					}
	            }
	        }
	        else
	        {
	        	$this->add_log( 'This property hasn\'t been imported before. Inserting it', $property['ID'] );

	        	// We've not imported this property before
				$postdata = array(
					'post_date'      => ( $property['SystemDetail']['DateRegistered'] ) ? date( 'Y-m-d H:i:s', strtotime( $property['SystemDetail']['DateRegistered'] )) : '',
					'post_date_gmt'  => ( $property['SystemDetail']['DateRegistered'] ) ? date( 'Y-m-d H:i:s', strtotime( $property['SystemDetail']['DateRegistered'] )) : '',
					'post_excerpt'   => $property['Description'],
					'post_content' 	 => '',
					'post_title'     => wp_strip_all_tags( $display_address ),
					'post_status'    => 'publish',
					'post_type'      => 'property',
					'comment_status' => 'closed',
				);

				$post_id = wp_insert_post( $postdata, true );

				if ( is_wp_error( $post_id ) ) 
				{
					$this->add_error( 'Failed to insert post. The error was as follows: ' . $post_id->get_error_message(), $property['ID'] );
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
				    	'post_excerpt'   => htmlentities(mb_convert_encoding(wp_strip_all_tags( $property['Description'] ), 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
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

				$this->add_log( 'Successfully ' . $inserted_updated . ' post. The post ID is ' . $post_id, $property['ID'] );

				update_post_meta( $post_id, $imported_ref_key, $property['ID'] );

				// Address
				update_post_meta( $post_id, '_reference_number',  ( ( isset($property['FileRef']) ) ? $property['FileRef'] : '' ) );
				update_post_meta( $post_id, '_address_name_number', trim( ( ( isset($property['Address']['Building_Name']) ) ? $property['Address']['Building_Name'] : '' ) . ' ' . ( ( isset($property['Address']['SecondaryName']) ) ? $property['Address']['SecondaryName'] : '' ) ) );
				update_post_meta( $post_id, '_address_street', ( ( isset($property['Address']['Street']) ) ? $property['Address']['Street'] : '' ) );
				update_post_meta( $post_id, '_address_two', ( ( isset($property['Address']['District']) ) ? $property['Address']['District'] : '' ) );
				update_post_meta( $post_id, '_address_three', ( ( isset($property['Address']['Town']) ) ? $property['Address']['Town'] : '' ) );
				update_post_meta( $post_id, '_address_four', ( ( isset($property['Address']['County']) ) ? $property['Address']['County'] : '' ) );
				update_post_meta( $post_id, '_address_postcode', ( ( isset($property['Postcode']) ) ? $property['Postcode'] : '' ) );

				$country = get_option( 'propertyhive_default_country', 'GB' );
				update_post_meta( $post_id, '_address_country', $country );

				// Check main address fields and see if this location exists as a taxonomy to try and assign properties to location
				$address_fields_to_check = apply_filters( 'propertyhive_agency_pilot_api_address_fields_to_check', array('District', 'Town', 'County') );
				$location_term_ids = array();

				foreach ( $address_fields_to_check as $address_field )
				{
					if ( isset($property['Address'][$address_field]) && trim($property['Address'][$address_field]) != '' ) 
					{
						$term = term_exists( trim($property['Address'][$address_field]), 'location' );
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
				update_post_meta( $post_id, '_latitude', ( ( isset($property['Address']['Latitude']) ) ? $property['Address']['Latitude'] : '' ) );
				update_post_meta( $post_id, '_longitude', ( ( isset($property['Address']['Longitude']) ) ? $property['Address']['Longitude'] : '' ) );

				// Owner
				add_post_meta( $post_id, '_owner_contact_id', '', true );

				// Record Details
				$negotiator_id = false;
				if ( isset($property['SystemDetail']['AccountManagers']) && is_array($property['SystemDetail']['AccountManagers']) && !empty($property['SystemDetail']['AccountManagers']) )
				{
					foreach ( $property['SystemDetail']['AccountManagers'] as $account_manager )
					{
						if ( $negotiator_id !== false )
						{
							continue;
						}

						$negotiator_row = $wpdb->get_row( $wpdb->prepare(
					        "SELECT `ID` FROM $wpdb->users WHERE `display_name` = %s", $account_manager['Name']
					    ) );
					    if ( null !== $negotiator_row )
					    {
					    	$negotiator_id = $negotiator_row->ID;
					    }
					}
				}
				if ( $negotiator_id === false )
				{
					$negotiator_id = get_current_user_id();
				}
				update_post_meta( $post_id, '_negotiator_id', (int)$negotiator_id, true );

				$office_id = $primary_office_id;

				if ( isset($_POST['mapped_office'][$property['SystemDetail']['Partner']['ID']]) && $_POST['mapped_office'][$property['SystemDetail']['Partner']['ID']] != '' )
				{
					$office_id = $_POST['mapped_office'][$property['SystemDetail']['Partner']['ID']];
				}
				elseif ( isset($options['offices']) && is_array($options['offices']) && !empty($options['offices']) )
				{
					foreach ( $options['offices'] as $ph_office_id => $branch_code )
					{
						if ( isset($property['SystemDetail']['Partner']['ID']) && $branch_code == $property['SystemDetail']['Partner']['ID'] )
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

        		if ( $property['Tenure']['ForSale'] == true )
                {
                    update_post_meta( $post_id, '_for_sale', 'yes' );

                    update_post_meta( $post_id, '_commercial_price_currency', 'GBP' );

                    $price = preg_replace("/[^0-9.]/", '', $property['Tenure']['ForSalePriceFrom']);
                    if ( $price == '' )
                    {
                        $price = preg_replace("/[^0-9.]/", '', $property['Tenure']['ForSalePriceTo']);
                    }
                    update_post_meta( $post_id, '_price_from', $price );

                    $price = preg_replace("/[^0-9.]/", '', $property['Tenure']['ForSalePriceTo']);
                    if ( $price == '' || $price == '0' )
                    {
                        $price = preg_replace("/[^0-9.]/", '', $property['Tenure']['ForSalePriceFrom']);
                    }
                    update_post_meta( $post_id, '_price_to', $price );

                    update_post_meta( $post_id, '_price_units', '' );

                    update_post_meta( $post_id, '_price_poa', '' );

                    // Tenure
		            /*if ( isset($_POST['mapped_commercial_tenure']) )
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
		            }*/
                }

                if ( $property['Tenure']['ForRent'] == true )
                {
                    update_post_meta( $post_id, '_to_rent', 'yes' );

                    update_post_meta( $post_id, '_commercial_rent_currency', 'GBP' );

                    $rent = preg_replace("/[^0-9.]/", '', $property['Tenure']['ForRentPriceFrom']);
                    if ( $rent == '' )
                    {
                        $rent = preg_replace("/[^0-9.]/", '', $property['Tenure']['ForRentPriceTo']);
                    }
                    update_post_meta( $post_id, '_rent_from', $rent );

                    $rent = preg_replace("/[^0-9.]/", '', $property['Tenure']['ForRentPriceTo']);
                    if ( $rent == '' || $rent == '0' )
                    {
                        $rent = preg_replace("/[^0-9.]/", '', $property['Tenure']['ForRentPriceFrom']);
                    }
                    update_post_meta( $post_id, '_rent_to', $rent );

                    update_post_meta( $post_id, '_rent_units', 'pa'); // look at ForRentTerm field

                    update_post_meta( $post_id, '_rent_poa', '' );
                }

                // Store price in common currency (GBP) used for ordering
	            $ph_countries = new PH_Countries();
	            $ph_countries->update_property_price_actual( $post_id );

	            $units = 'sqft';
	            if ( isset($property['Size']['Dimension']['Name']) )
	            {
	            	switch ( $property['Size']['Dimension']['Name'] )
	            	{
	            		case "Sq M": { $units = 'sqm'; break; }
	            		case "Acres": { $units = 'acre'; break; }
	            	}
	            }

	            $size = preg_replace("/[^0-9.]/", '', $property['Size']['MinSize']);
	            if ( $size == '' )
	            {
	                $size = preg_replace("/[^0-9.]/", '', $property['Size']['MaxSize']);
	            }
	            update_post_meta( $post_id, '_floor_area_from', $size );

	            update_post_meta( $post_id, '_floor_area_from_sqft', convert_size_to_sqft( $size, $units ) );

	            $size = preg_replace("/[^0-9.]/", '', $property['Size']['MaxSize']);
	            if ( $size == '' || $size == '0' )
	            {
	                $size = preg_replace("/[^0-9.]/", '', $property['Size']['MinSize']);
	            }
	            update_post_meta( $post_id, '_floor_area_to', $size );

	            update_post_meta( $post_id, '_floor_area_to_sqft', convert_size_to_sqft( $size, $units ) );

	            update_post_meta( $post_id, '_floor_area_units', $units );

	            $size = '';

	            update_post_meta( $post_id, '_site_area_from', $size );

	            update_post_meta( $post_id, '_site_area_from_sqft', convert_size_to_sqft( $size, 'sqft' ) );

	            update_post_meta( $post_id, '_site_area_to', $size );

	            update_post_meta( $post_id, '_site_area_to_sqft', convert_size_to_sqft( $size, 'sqft' ) );

	            update_post_meta( $post_id, '_site_area_units', $units );
				
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
				
				if ( isset($property['PropertyTypes']) && is_array($property['PropertyTypes']) && !empty($property['PropertyTypes']) )
				{
					$term_ids = array();

					foreach ( $property['PropertyTypes'] as $property_type )
					{
						if ( !empty($mapping) && isset($mapping[$property_type['ID']]) )
						{
							$term_ids[] = $mapping[$property_type['ID']];
			            }
					}

					if ( !empty($term_ids) )
					{
						wp_set_post_terms( $post_id, $term_ids, 'commercial_property_type' );
					}					
		            else
		            {
		            	//$this->add_log( 'Property received with type (' . $property['UnitIDs'] . ') that are not mapped', $property['ID'] );
		            }
		        }

	            update_post_meta( $post_id, '_on_market', 'yes' );
				update_post_meta( $post_id, '_featured', ( isset($property['Featured']) && $property['Featured'] == true ) ? 'yes' : '' );

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
        		if ( isset($property['MarketStatus']) && is_array($property['MarketStatus']) )
        		{
					if ( !empty($mapping) && isset($mapping[$property['MarketStatus']['ID']]) )
					{
		                wp_set_post_terms( $post_id, $mapping[$property['MarketStatus']['ID']], 'availability' );
		            }
		            else
		            {
		            	$this->add_log( 'Property received with an availability (' . $property['MarketStatus']['ID'] . ') that is not mapped', $property['ID'] );

		            	$options = $this->add_missing_mapping( $mapping, 'availability', $property['MarketStatus']['ID'], $import_id );
		            }
		        }

				$features = array();
				if ( isset($property['Additional']['Bullets']) && is_array($property['Additional']['Bullets']) && !empty($property['Additional']['Bullets']) )
				{
					foreach ( $property['Additional']['Bullets'] as $bullet )
					{
						if ( isset($bullet['BulletPoint']) && $bullet['BulletPoint'] != '' )
						{
							$features[] = $bullet['BulletPoint'];
						}
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
    				if ( isset($property['Photos']) && is_array($property['Photos']) && !empty($property['Photos']) )
	                {
						foreach ( $property['Photos'] as $image )
						{
							$url = $image['URL'];
							$url = str_replace("_sm.", ".", $url);
							$url = str_replace("_web.", ".", $url);
		                    
		                    $media_urls[] = array('url' => $url);
		                }
					}
					update_post_meta( $post_id, '_photo_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' photo URLs', $property['ID'] );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_photos', TRUE );

					if ( isset($property['Photos']) && is_array($property['Photos']) && !empty($property['Photos']) )
	                {
						foreach ( $property['Photos'] as $image )
						{
							$url = $image['URL'];
							$url = str_replace("_sm.", ".", $url);
							$url = str_replace("_web.", ".", $url);

							$description = $image['Name'];
						    
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

							        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['ID'] );
							    }
							    else
							    {
								    $id = media_handle_sideload( $file_array, $post_id, $description );

								    // Check for handle sideload errors.
								    if ( is_wp_error( $id ) ) 
								    {
								        @unlink( $file_array['tmp_name'] );
								        
								        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['ID'] );
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

					$this->add_log( 'Imported ' . count($media_ids) . ' photos (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property['ID'] );
				}

				// Media - Floorplans
				/*$media_ids = array();
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

					        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['ID'] );
					    }
					    else
					    {
						    $id = media_handle_sideload( $file_array, $post_id, $description );

						    // Check for handle sideload errors.
						    if ( is_wp_error( $id ) ) 
						    {
						        @unlink( $file_array['tmp_name'] );
						        
						        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['ID'] );
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

				$this->add_log( 'Imported ' . count($media_ids) . ' floorplans (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property['ID'] );*/
				
				// Media - Brochures
				if ( get_option('propertyhive_brochures_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();
    				if ( isset($property['DocumentMedia']) && is_array($property['DocumentMedia']) && !empty($property['DocumentMedia']) )
					{
						foreach ( $property['DocumentMedia'] as $brochure )
						{
							if ( !isset($brochure['Description']) || ( isset($brochure['Description']) && strpos(strtolower($brochure['Description']), 'brochure') === FALSE ) )
							{
								continue;
							}

							if ( isset($brochure['URLs']) && is_array($brochure['URLs']) && !empty($brochure['URLs']) )
							{
								foreach ($brochure['URLs'] as $url)
								{
		                    		$media_urls[] = array('url' => $url);
		                    	}
		                    }
		                }
					}
					update_post_meta( $post_id, '_brochure_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' brochure URLs', $property['ID'] );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_brochures', TRUE );

					if ( isset($property['DocumentMedia']) && is_array($property['DocumentMedia']) && !empty($property['DocumentMedia']) )
					{
						foreach ( $property['DocumentMedia'] as $brochure )
						{
							if ( !isset($brochure['Description']) || ( isset($brochure['Description']) && $brochure['Description'] != 'Brochure') )
							{
								continue;
							}

							if ( isset($brochure['URLs']) && is_array($brochure['URLs']) && !empty($brochure['URLs']) )
							{
								foreach ($brochure['URLs'] as $url)
								{
									// This is a URL
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

									        $this->add_error( 'An error occurred whilst importing brochure ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['ID'] );
									    }
									    else
									    {
										    $id = media_handle_sideload( $file_array, $post_id, $description );
										    
										    // Check for handle sideload errors.
										    if ( is_wp_error( $id ) ) 
										    {
										        @unlink( $file_array['tmp_name'] );
										        
										        $this->add_error( 'ERROR: An error occurred whilst importing brochure ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['ID'] );
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

					$this->add_log( 'Imported ' . count($media_ids) . ' brochures (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property['ID'] );
				}

				// Media - EPCs
				if ( get_option('propertyhive_epcs_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();
    				if ( isset($property['DocumentMedia']) && is_array($property['DocumentMedia']) && !empty($property['DocumentMedia']) )
					{
						foreach ( $property['DocumentMedia'] as $epc )
						{
							if ( !isset($epc['Description']) || ( isset($epc['Description']) && strpos(strtolower($epc['Description']), 'epc') === FALSE) )
							{
								continue;
							}

							if ( isset($epc['URLs']) && is_array($epc['URLs']) && !empty($epc['URLs']) )
							{
								foreach ($epc['URLs'] as $url)
								{
		                    		$media_urls[] = array('url' => $url);
		                    	}
		                    }
		                }
					}
					update_post_meta( $post_id, '_epc_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' EPC URLs', $property['ID'] );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_epcs', TRUE );

					if ( isset($property['DocumentMedia']) && is_array($property['DocumentMedia']) && !empty($property['DocumentMedia']) )
					{
						foreach ( $property['DocumentMedia'] as $epc )
						{
							if ( !isset($epc['Description']) || ( isset($epc['Description']) && $epc['Description'] != 'EPC') )
							{
								continue;
							}

							if ( isset($epc['URLs']) && is_array($epc['URLs']) && !empty($epc['URLs']) )
							{
								foreach ($epc['URLs'] as $url)
								{
									// This is a URL
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

									        $this->add_error( 'An error occurred whilst importing EPC ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['ID'] );
									    }
									    else
									    {
										    $id = media_handle_sideload( $file_array, $post_id, $description );
										    
										    // Check for handle sideload errors.
										    if ( is_wp_error( $id ) ) 
										    {
										        @unlink( $file_array['tmp_name'] );
										        
										        $this->add_error( 'ERROR: An error occurred whilst importing EPC ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['ID'] );
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

					$this->add_log( 'Imported ' . count($media_ids) . ' EPCs (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property['ID'] );
				}

				if ( isset($property['Additional']['VirtualTourUrl']) && $property['Additional']['VirtualTourUrl'] != '' )
				{
					update_post_meta($post_id, '_virtual_tours', 1 );
			        update_post_meta($post_id, '_virtual_tour_0', $property['Additional']['VirtualTourUrl']);

			        $this->add_log( 'Imported 1 virtual tour', $property['ID'] );
				}
				else
				{
					update_post_meta($post_id, '_virtual_tours', 0 );
				}

				do_action( "propertyhive_property_imported_agency_pilot_api", $post_id, $property );

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
							$this->add_log( 'New meta data for ' . trim($key, '_') . ': ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), $property['ID'] );
						}
						elseif ( $metadata_before[$key] != $metadata_after[$key] )
						{
							$this->add_log( 'Updated ' . trim($key, '_') . '. Before: ' . ( ( is_array($metadata_before[$key]) ) ? implode(", ", $metadata_before[$key]) : $metadata_before[$key] ) . ', After: ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), $property['ID'] );
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
							$this->add_log( 'New taxonomy data for ' . $taxonomy_name . ': ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), $property['ID'] );
						}
						elseif ( $taxonomy_terms_before[$taxonomy_name] != $taxonomy_terms_after[$taxonomy_name] )
						{
							$this->add_log( 'Updated ' . $taxonomy_name . '. Before: ' . ( ( is_array($taxonomy_terms_before[$taxonomy_name]) ) ? implode(", ", $taxonomy_terms_before[$taxonomy_name]) : $taxonomy_terms_before[$taxonomy_name] ) . ', After: ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), $property['ID'] );
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

		do_action( "propertyhive_post_import_properties_agency_pilot_api" );

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
				$import_refs[] = $property['ID'];
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

					do_action( "propertyhive_property_removed_agency_pilot_api", $post->ID );
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

		$this->token = $this->get_token($options);

		if ( $this->token === false )
		{
			return array(
            	'' => ''
            );
		}

        if ($custom_field == 'commercial_availability')
        {
        	$response = wp_remote_get(
				$options['url'] . '/api/CurrentVersion/PropertyFeed/MarketStatus',
				array(
					'headers' => array( 
						'Authorization' => 'Bearer ' . $this->token, 
						'Content-Type' => 'application/json', 
						'Accept' => 'application/json' 
					),
					'body' => '',
			    )
			);

			if ( is_wp_error( $response ) ) 
			{
				$this->add_error( 'Failed to request commercial availabilities: ' . $response->get_error_message() );
				return array(
	            	'' => ''
	            );
			}
			else
			{
				$json = json_decode($response['body'], TRUE);

				if ( $json === false )
				{
					$this->add_error( 'Failed to decode commercial availabilities request body: ' . $response['body'] );
					return array(
		            	'' => ''
		            );
				}
				else
				{
					if ( $json !== FALSE )
					{
						$options = array();
						foreach ( $json as $value )
						{
							$options[$value['ID']] = $value['Name'];
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
        if ($custom_field == 'commercial_property_type')
        {
        	$response = wp_remote_get(
				$options['url'] . '/api/CurrentVersion/PropertyFeed/PropertyTypes',
				array(
					'headers' => array( 
						'Authorization' => 'Bearer ' . $this->token, 
						'Content-Type' => 'application/json', 
						'Accept' => 'application/json' 
					),
					'body' => '',
			    )
			);

			if ( is_wp_error( $response ) ) 
			{
				$this->add_error( 'Failed to request commercial types: ' . $response->get_error_message() );
				return array(
	            	'' => ''
	            );
			}
			else
			{
				$json = json_decode($response['body'], TRUE);

				if ( $json === false )
				{
					$this->add_error( 'Failed to decode commercial types request body: ' . $response['body'] );
					return array(
		            	'' => ''
		            );
				}
				else
				{
					if ($json !== FALSE)
					{
						$options = array();
						foreach ( $json as $value )
						{
							$options[$value['ID']] = $value['Name'];
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
        /*if ($custom_field == 'price_qualifier')
        {
        	return array(
        		'' => ''
        	);
        }*/
        if ($custom_field == 'commercial_tenure')
        {
        	return array(
            	'' => ''
            );
        	
        	$response = wp_remote_get(
				$options['url'] . '/api/CurrentVersion/PropertyFeed/Terms',
				array(
					'headers' => array( 
						'Authorization' => 'Bearer ' . $this->token, 
						'Content-Type' => 'application/json', 
						'Accept' => 'application/json' 
					),
					'body' => '',
			    )
			);

			if ( is_wp_error( $response ) ) 
			{
				$this->add_error( 'Failed to request commercial tenures: ' . $response->get_error_message() );
				return array(
	            	'' => ''
	            );
			}
			else
			{
				$json = json_decode($response['body'], TRUE);

				if ( $json === false )
				{
					$this->add_error( 'Failed to decode commercial tenures request body: ' . $response['body'] );
					return array(
		            	'' => ''
		            );
				}
				else
				{
					if ($json !== FALSE)
					{
						$options = array();
						foreach ( $json as $value )
						{
							$options[$value['ID']] = $value['Name'];
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
}

}