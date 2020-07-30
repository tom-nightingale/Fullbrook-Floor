<?php
/**
 * WordPress Importer class for managing the import process of a LetMC JSON file
 *
 * @package WordPress
 * @subpackage Importer
 */
if ( class_exists( 'PH_Property_Import_Process' ) ) {

class PH_LetMC_JSON_Import extends PH_Property_Import_Process {

	public function __construct( $instance_id = '' ) 
	{
		$this->instance_id = $instance_id;

		if ( isset($_GET['custom_property_import_cron']) )
	    {
	    	$current_user = wp_get_current_user();

	    	$this->add_log("Executed manually by " . ( ( isset($current_user->display_name) ) ? $current_user->display_name : '' ) );
	    }
	}

	public function parse( $options, $import_id = '' )
	{
		$this->add_log("Obtaining branches");

		$options = get_option( 'propertyhive_property_import' );
		if (isset($options[$import_id]))
		{
			$options = $options[$import_id];
		}
		else
		{
			$options = array();
		}

		$requests = 0;

		$branches_url = 'https://live-api.letmc.com/v4/advertising/' . urlencode($options['short_name']) . '/company/branches/0/1000';
		$fields = array(
			'api_key' => urlencode($options['api_key']),
		);

		$fields_string = '';
		foreach ($fields as $key => $value) { $fields_string .= $key . '=' . $value . '&'; }
		$fields_string = rtrim($fields_string, '&');

		$branches_url = $branches_url . '?' . $fields_string;

		$response = wp_remote_get( $branches_url );

		++$requests;
		if ( $requests >= 10 ) { sleep(5); $requests = 0; }

		if ( is_array($response) && isset($response['body']) ) 
		{
			$branches_json = json_decode($response['body'], TRUE);

			if ( $branches_json === FALSE || is_null($branches_json) )
			{
				$this->add_error("Failed to parse branches JSON: " . $response['body']);
				return false;
			}

			$branches = $branches_json['Data'];

			$this->add_log("Found " . count($branches) . " branches");

			foreach ( $branches as $branch )
			{
				$this->add_log("Obtaining properties for branch " . $branch['Name'] . " (" . $branch['OID'] . ")");
				
				// Sales Properties
				$sales_instructions = array();
				$sales_instructions_url = 'https://live-api.letmc.com/v4/advertising/' . urlencode($options['short_name']) . '/sales/advertised/0/1000';
				$fields = array(
					'api_key' => urlencode($options['api_key']),
					'branchID' => $branch['OID'],
					'onlyDevelopement' => 'false',
					'onlyInvestements' => 'false',
				);

				$fields_string = '';
				foreach ($fields as $key => $value) { $fields_string .= $key . '=' . $value . '&'; }
				$fields_string = rtrim($fields_string, '&');

				$sales_instructions_url = $sales_instructions_url . '?' . $fields_string;

				$response = wp_remote_get( $sales_instructions_url );

				++$requests;
				if ( $requests >= 10 ) { sleep(5); $requests = 0; }

				if ( is_array($response) && isset($response['body']) ) 
				{
					$sales_instructions_json = json_decode($response['body'], TRUE);

					if ( $sales_instructions_json === FALSE || is_null($sales_instructions_json) )
					{
						$this->add_error("Failed to parse sales properties summary JSON: " . $response['body']);
						return false;
					}
					else
					{
						$sales_instructions = $sales_instructions_json['Data'];

						$this->add_log("Found " . count($sales_instructions) . " sales instructions");

						foreach ( $sales_instructions as $property )
						{
							// Get sales instruction data
							$property_url = 'https://live-api.letmc.com/v4/advertising/' . urlencode($options['short_name']) . '/sales/salesinstructions/' . $property['OID'];
							$fields = array(
								'api_key' => urlencode($options['api_key']),
							);

							$fields_string = '';
							foreach ($fields as $key => $value) { $fields_string .= $key . '=' . $value . '&'; }
							$fields_string = rtrim($fields_string, '&');

							$property_url = $property_url . '?' . $fields_string;

							$response = wp_remote_get( $property_url );

							++$requests;
							if ( $requests >= 10 ) { sleep(5); $requests = 0; }

							if ( is_array($response) && isset($response['body']) ) 
							{
								$property_json = json_decode($response['body'], TRUE);

								if ( $property_json === FALSE || is_null($property_json) )
								{
									$this->add_error("Failed to parse full sales data JSON: " . $response['body'], $property['OID']);
									return false;
								}
								else
								{
									$property = array_merge($property_json, $property);
									$property['State'] = $property_json['State'];

									$property['department'] = 'residential-sales';
								}
							}
							else
							{
								$this->add_error("Failed to obtain full sales JSON: " . print_r($response, TRUE), $property['OID']);
								return false;
							}

							// Get features
							$features = array();

							$property_features_url = 'https://live-api.letmc.com/v4/advertising/' . urlencode($options['short_name']) . '/sales/salesinstructions/' . $property['OID'] . '/features/0/1000';
							$fields = array(
								'api_key' => urlencode($options['api_key']),
							);

							$fields_string = '';
							foreach ($fields as $key => $value) { $fields_string .= $key . '=' . $value . '&'; }
							$fields_string = rtrim($fields_string, '&');

							$property_features_url = $property_features_url . '?' . $fields_string;

							$response = wp_remote_get( $property_features_url );

							++$requests;
							if ( $requests >= 10 ) { sleep(5); $requests = 0; }

							if ( is_array($response) && isset($response['body']) ) 
							{
								$property_features_json = json_decode($response['body'], TRUE);

								if ( $property_features_json === FALSE || is_null($property_features_json) )
								{
									$this->add_error("Failed to parse property features JSON: " . $response['body'], $property['OID']);
									return false;
								}
								else
								{
									$property_features = $property_features_json['Data'];

									foreach ( $property_features as $property_feature )
									{
										$features[] = $property_feature['Name'];
									}
								}
							}
							else
							{
								$this->add_error("Failed to obtain property features JSON: " . print_r($response, TRUE), $property['OID']);
								return false;
							}

							// Get floorplans
							$floorplans = array();

							$property_floorplans_url = 'https://live-api.letmc.com/v4/advertising/' . urlencode($options['short_name']) . '/sales/salesinstructions/' . $property['OID'] . '/floorplans/0/1000';
							$fields = array(
								'api_key' => urlencode($options['api_key']),
							);

							$fields_string = '';
							foreach ($fields as $key => $value) { $fields_string .= $key . '=' . $value . '&'; }
							$fields_string = rtrim($fields_string, '&');

							$property_floorplans_url = $property_floorplans_url . '?' . $fields_string;

							$response = wp_remote_get( $property_floorplans_url );

							++$requests;
							if ( $requests >= 10 ) { sleep(5); $requests = 0; }

							if ( is_array($response) && isset($response['body']) ) 
							{
								$property_floorplans_json = json_decode($response['body'], TRUE);

								if ( $property_floorplans_json === FALSE || is_null($property_floorplans_json) )
								{
									$this->add_error("Failed to parse property floorplans JSON: " . $response['body'], $property['OID']);
									return false;
								}
								else
								{
									$property_floorplans = $property_floorplans_json['Data'];

									foreach ( $property_floorplans as $property_floorplan )
									{
										$floorplans[] = $property_floorplan;
									}
								}
							}
							else
							{
								$this->add_error("Failed to obtain property floorplans JSON: " . print_r($response, TRUE), $property['OID']);
								return false;
							}

							// Get photos
							$photos = array();

							$property_photos_url = 'https://live-api.letmc.com/v4/advertising/' . urlencode($options['short_name']) . '/sales/salesinstructions/' . $property['OID'] . '/photos/0/1000';
							$fields = array(
								'api_key' => urlencode($options['api_key']),
							);

							$fields_string = '';
							foreach ($fields as $key => $value) { $fields_string .= $key . '=' . $value . '&'; }
							$fields_string = rtrim($fields_string, '&');

							$property_photos_url = $property_photos_url . '?' . $fields_string;

							$response = wp_remote_get( $property_photos_url );

							++$requests;
							if ( $requests >= 10 ) { sleep(5); $requests = 0; }

							if ( is_array($response) && isset($response['body']) ) 
							{
								$property_photos_json = json_decode($response['body'], TRUE);

								if ( $property_photos_json === FALSE || is_null($property_photos_json) )
								{
									$this->add_error("Failed to parse property photos JSON: " . $response['body'], $property['OID']);
									return false;
								}
								else
								{
									$property_photos = $property_photos_json['Data'];

									foreach ( $property_photos as $property_photo )
									{
										$photos[] = $property_photo;
									}
								}
							}
							else
							{
								$this->add_error("Failed to obtain property photos JSON: " . print_r($response, TRUE), $property['OID']);
								return false;
							}

							// Get rooms
							$rooms = array();

							$property_rooms_url = 'https://live-api.letmc.com/v4/advertising/' . urlencode($options['short_name']) . '/sales/salesinstructions/' . $property['OID'] . '/rooms/0/1000';
							$fields = array(
								'api_key' => urlencode($options['api_key']),
							);

							$fields_string = '';
							foreach ($fields as $key => $value) { $fields_string .= $key . '=' . $value . '&'; }
							$fields_string = rtrim($fields_string, '&');

							$property_rooms_url = $property_rooms_url . '?' . $fields_string;

							$response = wp_remote_get( $property_rooms_url );

							++$requests;
							if ( $requests >= 10 ) { sleep(5); $requests = 0; }

							if ( is_array($response) && isset($response['body']) ) 
							{
								$property_rooms_json = json_decode($response['body'], TRUE);

								if ( $property_rooms_json === FALSE || is_null($property_rooms_json) )
								{
									$this->add_error("Failed to parse property rooms JSON: " . $response['body'], $property['OID']);
									return false;
								}
								else
								{
									$property_rooms = $property_rooms_json['Data'];

									foreach ( $property_rooms as $property_room )
									{
										$rooms[] = $property_room;
									}
								}
							}
							else
							{
								$this->add_error("Failed to obtain property rooms JSON: " . print_r($response, TRUE), $property['OID']);
								return false;
							}

							$property['features'] = $features;
							$property['floorplans'] = $floorplans;
							$property['photos'] = $photos;
							$property['rooms'] = $rooms;

							$this->properties[] = $property;
						}
					}
				}
				else
				{
					$this->add_error("Failed to obtain sales properties summary JSON: " . print_r($response, TRUE));
					return false;
				}

				// Lettings Properties
				$lettings_instructions_url = 'https://live-api.letmc.com/v4/advertising/' . urlencode($options['short_name']) . '/lettings/advertised/0/1000';
				$fields = array(
					'api_key' => urlencode($options['api_key']),
					'branchID' => $branch['OID'],
				);

				$fields_string = '';
				foreach ($fields as $key => $value) { $fields_string .= $key . '=' . $value . '&'; }
				$fields_string = rtrim($fields_string, '&');

				$lettings_instructions_url = $lettings_instructions_url . '?' . $fields_string;

				$response = wp_remote_get( $lettings_instructions_url );

				++$requests;
				if ( $requests >= 10 ) { sleep(5); $requests = 0; }

				if ( is_array($response) && isset($response['body']) ) 
				{
					$lettings_instructions_json = json_decode($response['body'], TRUE);

					if ( $lettings_instructions_json === FALSE || is_null($lettings_instructions_json) )
					{
						$this->add_error("Failed to parse lettings properties summary JSON: " . $response['body']);
						return false;
					}
					else
					{
						$lettings_instructions = $lettings_instructions_json['Data'];

						$this->add_log("Found " . count($lettings_instructions) . " lettings properties");

						foreach ( $lettings_instructions as $property )
						{
							// Get full lettings data
							$property_url = 'https://live-api.letmc.com/v4/advertising/' . urlencode($options['short_name']) . '/lettings/advertised/' . $property['OID'];
							$fields = array(
								'api_key' => urlencode($options['api_key']),
							);

							$fields_string = '';
							foreach ($fields as $key => $value) { $fields_string .= $key . '=' . $value . '&'; }
							$fields_string = rtrim($fields_string, '&');

							$property_url = $property_url . '?' . $fields_string;

							$response = wp_remote_get( $property_url );

							++$requests;
							if ( $requests >= 10 ) { sleep(5); $requests = 0; }

							if ( is_array($response) && isset($response['body']) ) 
							{
								$property_json = json_decode($response['body'], TRUE);

								if ( $property_json === FALSE || is_null($property_json) )
								{
									$this->add_error("Failed to parse full lettings data JSON: " . $response['body'], $property['PropertyID']);
									return false;
								}
								else
								{
									$property = array_merge($property_json, $property);

									$property['department'] = 'residential-lettings';
								}
							}
							else
							{
								$this->add_error("Failed to obtain full lettings JSON: " . print_r($response, TRUE), $property['PropertyID']);
								return false;
							}

							// Get full property data
							$property_url = 'https://live-api.letmc.com/v4/advertising/' . urlencode($options['short_name']) . '/lettings/properties/' . $property['PropertyID'];
							$fields = array(
								'api_key' => urlencode($options['api_key']),
							);

							$fields_string = '';
							foreach ($fields as $key => $value) { $fields_string .= $key . '=' . $value . '&'; }
							$fields_string = rtrim($fields_string, '&');

							$property_url = $property_url . '?' . $fields_string;

							$response = wp_remote_get( $property_url );

							++$requests;
							if ( $requests >= 10 ) { sleep(5); $requests = 0; }

							if ( is_array($response) && isset($response['body']) ) 
							{
								$property_json = json_decode($response['body'], TRUE);

								if ( $property_json === FALSE || is_null($property_json) )
								{
									$this->add_error("Failed to parse full property JSON: " . $response['body'], $property['PropertyID']);
									return false;
								}
								else
								{
									$property = array_merge($property_json, $property);
								}
							}
							else
							{
								$this->add_error("Failed to obtain full property JSON: " . print_r($response, TRUE), $property['PropertyID']);
								return false;
							}

							// Get features
							$features = array();

							$property_features_url = 'https://live-api.letmc.com/v4/advertising/' . urlencode($options['short_name']) . '/lettings/properties/' . $property['PropertyID'] . '/facilities/0/1000';
							$fields = array(
								'api_key' => urlencode($options['api_key']),
							);

							$fields_string = '';
							foreach ($fields as $key => $value) { $fields_string .= $key . '=' . $value . '&'; }
							$fields_string = rtrim($fields_string, '&');

							$property_features_url = $property_features_url . '?' . $fields_string;

							$response = wp_remote_get( $property_features_url );

							++$requests;
							if ( $requests >= 10 ) { sleep(5); $requests = 0; }

							if ( is_array($response) && isset($response['body']) ) 
							{
								$property_features_json = json_decode($response['body'], TRUE);

								if ( $property_features_json === FALSE || is_null($property_features_json) )
								{
									$this->add_error("Failed to parse property features JSON: " . $response['body'], $property['PropertyID']);
									return false;
								}
								else
								{
									$property_features = $property_features_json['Data'];

									foreach ( $property_features as $property_feature )
									{
										$features[] = $property_feature['Name'];
									}
								}
							}
							else
							{
								$this->add_error("Failed to obtain property features JSON: " . print_r($response, TRUE), $property['PropertyID']);
								return false;
							}

							// Get photos
							$photos = array();

							$property_photos_url = 'https://live-api.letmc.com/v4/advertising/' . urlencode($options['short_name']) . '/lettings/properties/' . $property['PropertyID'] . '/photos/0/1000';
							$fields = array(
								'api_key' => urlencode($options['api_key']),
							);

							$fields_string = '';
							foreach ($fields as $key => $value) { $fields_string .= $key . '=' . $value . '&'; }
							$fields_string = rtrim($fields_string, '&');

							$property_photos_url = $property_photos_url . '?' . $fields_string;

							$response = wp_remote_get( $property_photos_url );

							++$requests;
							if ( $requests >= 10 ) { sleep(5); $requests = 0; }

							if ( is_array($response) && isset($response['body']) ) 
							{
								$property_photos_json = json_decode($response['body'], TRUE);

								if ( $property_photos_json === FALSE || is_null($property_photos_json) )
								{
									$this->add_error("Failed to parse property photos JSON: " . $response['body'], $property['PropertyID']);
									return false;
								}
								else
								{
									$property_photos = $property_photos_json['Data'];

									foreach ( $property_photos as $property_photo )
									{
										$photos[] = $property_photo;
									}
								}
							}
							else
							{
								$this->add_error("Failed to obtain property photos JSON: " . print_r($response, TRUE), $property['PropertyID']);
								return false;
							}

							$property['features'] = $features;
							$property['photos'] = $photos;

							$this->properties[] = $property;
						}
					}
				}
				else
				{
					$this->add_error("Failed to obtain lettings properties summary JSON: " . print_r($response, TRUE));
					return false;
				}
			}
		}
		else
		{
			$this->add_error("Failed to obtain branches JSON: " . print_r($response, TRUE));
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

		$this->add_log( 'Including required wp-admin files to handle media' );

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

        $requests = 0;

        $geocoding_denied = false;

        do_action( "propertyhive_pre_import_properties_agentos_json", $this->properties );
        $this->properties = apply_filters( "propertyhive_agentos_json_properties_due_import", $this->properties );

		$this->add_log( 'Beginning to loop through ' . count($this->properties) . ' properties' );

		$property_row = 1;
		foreach ( $this->properties as $property )
		{
			$this->add_log( 'Importing property ' . $property_row . ' with reference ' . $property['OID'], $property['OID'] );

			$inserted_updated = false;

			$args = array(
	            'post_type' => 'property',
	            'posts_per_page' => 1,
	            'post_status' => 'any',
	            'meta_query' => array(
	            	array(
		            	'key' => $imported_ref_key,
		            	'value' => $property['OID']
		            )
	            )
	        );
	        $property_query = new WP_Query($args);

	        $display_address = array();
	        if ( isset($property['Address1']) && trim($property['Address1']) != '' )
	        {
	        	$display_address[] = trim($property['Address1']);
	        }
	        if ( isset($property['Address2']) && trim($property['Address2']) != '' )
	        {
	        	$display_address[] = trim($property['Address2']);
	        }
	        if ( isset($property['Address3']) && trim($property['Address3']) != '' )
	        {
	        	$display_address[] = trim($property['Address3']);
	        }
	        $display_address = implode(", ", $display_address);

	        $summary_description = substr( strip_tags($property['Description']), 0, 300 );
	        if ( strlen(strip_tags($property['Description'])) > 300 )
	        {
	        	$summary_description .= '...';
	        }
	        
	        if ($property_query->have_posts())
	        {
	        	$this->add_log( 'This property has been imported before. Updating it', $property['OID'] );

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
						$this->add_error( 'Failed to update post. The error was as follows: ' . $post_id->get_error_message(), $property['OID'] );
					}
					else
					{
						$inserted_updated = 'updated';
					}
	            }
	        }
	        else
	        {
	        	$this->add_log( 'This property hasn\'t been imported before. Inserting it', $property['OID'] );

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
					$this->add_error( 'Failed to insert post. The error was as follows: ' . $post_id->get_error_message(), $property['OID'] );
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
					($display_address != '' || $summary_description != '')
				)
				{
					$my_post = array(
				    	'ID'          	 => $post_id,
				    	'post_title'     => htmlentities(mb_convert_encoding(wp_strip_all_tags( $display_address ), 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
				    	'post_excerpt'   => htmlentities(mb_convert_encoding(wp_strip_all_tags( $summary_description ), 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
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

				$this->add_log( 'Successfully added post. The post ID is ' . $post_id, $property['OID'] );

				update_post_meta( $post_id, $imported_ref_key, $property['OID'] );

				// Address
				update_post_meta( $post_id, '_reference_number', $property['GlobalReference'] );
				update_post_meta( $post_id, '_address_name_number', ( ( isset($property['AddressNumber']) ) ? $property['AddressNumber'] : '' ) );
				update_post_meta( $post_id, '_address_street', ( ( isset($property['Address1']) ) ? $property['Address1'] : '' ) );
				update_post_meta( $post_id, '_address_two', ( ( isset($property['Address2']) ) ? $property['Address2'] : '' ) );
				update_post_meta( $post_id, '_address_three', ( ( isset($property['Address3']) ) ? $property['Addres3'] : '' ) );
				update_post_meta( $post_id, '_address_four', ( ( isset($property['Address4']) ) ? $property['Address4'] : '' ) );
				update_post_meta( $post_id, '_address_postcode', ( ( isset($property['Postcode']) ) ? $property['Postcode'] : '' ) );

				$country = get_option( 'propertyhive_default_country', 'GB' );
				update_post_meta( $post_id, '_address_country', $country );

				// Check main address fields and see if this location exists as a taxonomy to try and assign properties to location
				$address_fields_to_check = apply_filters( 'propertyhive_agentos_json_address_fields_to_check', array('Address2', 'Address3', 'Address4') );
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
				$lat = get_post_meta( $post_id, '_latitude', TRUE);
				$lng = get_post_meta( $post_id, '_longitude', TRUE);

				if ( !$geocoding_denied && ( $lat == '' || $lng == '' || $lat == '0' || $lng == '0' ) )
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
							if ( isset($property['AddressNumber']) && $property['AddressNumber'] != '' ) { $address_to_geocode[] = $property['AddressNumber']; }
							if ( isset($property['Address1']) && $property['Address1'] != '' ) { $address_to_geocode[] = $property['Address1']; }
							if ( isset($property['Address2']) && $property['Address2'] != '' ) { $address_to_geocode[] = $property['Address2']; }
							if ( isset($property['Address3']) && $property['Address3'] != '' ) { $address_to_geocode[] = $property['Address3']; }
							if ( isset($property['Address4']) && $property['Address4'] != '' ) { $address_to_geocode[] = $property['Address4']; }
							if ( isset($property['Postcode']) && $property['Postcode'] != '' ) { $address_to_geocode[] = $property['Postcode']; }

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
						        	$this->add_error( 'Google Geocoding service returned status ' . $status, $property['OID'] );
						        	sleep(3);

						        	if ( $status == "REQUEST_DENIED" )
						        	{
						        		$geocoding_denied = true;
						        	}
						        }
						    }
					        else
					        {
					        	$this->add_error( 'Failed to parse XML response from Google Geocoding service', $property['OID'] );
					        }
						}
						else
				        {
				        	$this->add_error( 'Failed to obtain co-ordinates as allow_url_fopen setting is disabled', $property['OID'] );
				        }
				    }
				    else
				    {
				    	$this->add_log( 'Not performing Google Geocoding request as no API key present in settings', $property['OID'] );
				    }
				}

				// Owner
				add_post_meta( $post_id, '_owner_contact_id', '', true );

				// Record Details
				add_post_meta( $post_id, '_negotiator_id', get_current_user_id(), true );

				$office_id = $primary_office_id;
				if ( isset($_POST['mapped_office'][$property['BranchOID']]) && $_POST['mapped_office'][$property['BranchOID']] != '' )
				{
					$office_id = $_POST['mapped_office'][$property['BranchOID']];
				}
				elseif ( isset($options['offices']) && is_array($options['offices']) && !empty($options['offices']) )
				{
					foreach ( $options['offices'] as $ph_office_id => $branch_code )
					{
						if ( $branch_code == $property['BranchOID'] )
						{
							$office_id = $ph_office_id;
							break;
						}
					}
				}
				update_post_meta( $post_id, '_office_id', $office_id );

				// Residential Details
				update_post_meta( $post_id, '_department', $property['department'] );

				$bedrooms = isset($property['Bedrooms']) ? $property['Bedrooms'] : ( isset($property['BedroomCount']) ? $property['BedroomCount'] : '' );
				update_post_meta( $post_id, '_bedrooms', $bedrooms );
				$bathrooms = isset($property['Bathrooms']) ? $property['Bathrooms'] : ( isset($property['BathroomCount']) ? $property['BathroomCount'] : '' );
				update_post_meta( $post_id, '_bathrooms', $bathrooms );
				$reception_rooms = isset($property['ReceptionRooms']) ? $property['ReceptionRooms'] : ( isset($property['ReceptionCount']) ? $property['ReceptionCount'] : '' );
				update_post_meta( $post_id, '_reception_rooms', $reception_rooms );
	
				// Property Type
				if ( isset($_POST['mapped_property_type']) )
				{
					$mapping = $_POST['mapped_property_type'];
				}
				else
				{
					$mapping = isset($options['mappings']['property_type']) ? $options['mappings']['property_type'] : array();
				}
				
				wp_delete_object_term_relationships( $post_id, 'property_type' );

				if ( isset($property['PropertyType']) && $property['PropertyType'] != '' )
				{
					if ( !empty($mapping) && isset($mapping[$property['PropertyType']]) )
					{
		                wp_set_post_terms( $post_id, $mapping[$property['PropertyType']], 'property_type' );
		            }
		            else
		            {
		            	$this->add_log( 'Property received with a type (' . $property['PropertyType'] . ') that is not mapped', $property['OID'] );

		            	$options = $this->add_missing_mapping( $mapping, 'property_type', $property['PropertyType'], $import_id );
		            }
		        }

				// Residential Sales Details
				if ( $property['department'] == 'residential-sales' )
				{
					$price = round(preg_replace("/[^0-9.]/", '', $property['Price']));

					update_post_meta( $post_id, '_price', $price );
					update_post_meta( $post_id, '_price_actual', $price );

					update_post_meta( $post_id, '_poa', ( ( isset($property['POA']) && $property['POA'] === true ) ? 'yes' : '' ) );

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
					if ( !empty($mapping) && isset($property['Tenure']) && isset($mapping[$property['Tenure']]) )
					{
			            wp_set_post_terms( $post_id, $mapping[$property['Tenure']], 'tenure' );
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
					if ( !empty($mapping) && isset($property['SalesBy']) && isset($mapping[$property['SalesBy']]) )
					{
			            wp_set_post_terms( $post_id, $mapping[$property['SalesBy']], 'sale_by' );
		            }
				}
				elseif ( $property['department'] == 'residential-lettings' )
				{
					$price = round(preg_replace("/[^0-9.]/", '', $property['RentAdvertised']));

					$price_actual = $price;
					$rent_frequency = 'pcm';
					switch ($property['RentSchedule'])
					{
						case "Weekly": { $rent_frequency = 'pw'; $price_actual = ($price * 52) / 12; break; }
						case "Monthly": { $rent_frequency = 'pcm'; $price_actual = $price; break; }
						case "Quarterly": { $rent_frequency = 'pq'; $price_actual = ($price * 4) / 12; break; }
						case "Yearly": { $rent_frequency = 'pa'; $price_actual = $price / 12; break; }
					}

					update_post_meta( $post_id, '_rent', $price );
					update_post_meta( $post_id, '_rent_frequency', $rent_frequency );
					update_post_meta( $post_id, '_price_actual', $price_actual );

					$deposit = round(preg_replace("/[^0-9.]/", '', $property['BondRequired']));
					update_post_meta( $post_id, '_deposit', $deposit );
            		update_post_meta( $post_id, '_available_date', ( isset($property['TermStart']) && $property['TermStart'] != '' ) ? date("Y-m-d", strtotime($property['TermStart'])) : '' );

            		// Furnished
		            if ( isset($_POST['mapped_furnished']) )
					{
						$mapping = $_POST['furnished'];
					}
					else
					{
						$mapping = isset($options['mappings']['furnished']) ? $options['mappings']['furnished'] : array();
					}

		            wp_delete_object_term_relationships( $post_id, 'furnished' );
					if ( !empty($mapping) && isset($property['Furnished']) && isset($mapping[$property['Furnished']]) )
					{
			            wp_set_post_terms( $post_id, $mapping[$property['Furnished']], 'furnished' );
		            }
				}

				update_post_meta( $post_id, '_on_market', 'yes' );

				if ( isset($_POST['mapped_' . str_replace('residential-', '', $property['department']) . '_availability']) )
				{
					$mapping = $_POST['mapped_' . str_replace('residential-', '', $property['department']) . '_availability'];
				}
				else
				{
					$mapping = isset($options['mappings'][str_replace('residential-', '', $property['department']) . '_availability']) ? 
						$options['mappings'][str_replace('residential-', '', $property['department']) . '_availability'] : 
						array();
				}

				$availability = '';
				if ( $property['department'] == 'residential-sales' )
				{
					$availability = 'For Sale';
					if ( $property['State'] == 'UnderOffer' )
					{
						$availability = 'Under Offer';
					}
				}
				elseif ( $property['department'] == 'residential-lettings' )
				{
					$availability = 'To Let';
					if ( $property['IsTenancyProposed'] === TRUE )
					{
						$availability = 'Let Agreed';
					}
				}

				wp_delete_object_term_relationships( $post_id, 'availability' );
				if ( !empty($mapping) && isset($mapping[$availability]) )
				{
	                wp_set_post_terms( $post_id, $mapping[$availability], 'availability' );
	            }

				// Features
				update_post_meta( $post_id, '_features', count( $property['features'] ) );
        		
        		$i = 0;
		        foreach ( $property['features'] as $feature )
		        {
		            update_post_meta( $post_id, '_feature_' . $i, $feature );
		            ++$i;
		        }

				// For now put the whole description in one room
				update_post_meta( $post_id, '_rooms', '1' );
				update_post_meta( $post_id, '_room_name_0', '' );
				update_post_meta( $post_id, '_room_dimensions_0', '' );

				// Attempt to solve an encoding issue. Set to blank first, insert, and if blank, utf8encode and insert again
				update_post_meta( $post_id, '_room_description_0', '' );
	            update_post_meta( $post_id, '_room_description_0', $property['Description'] );
	            if ( get_post_meta( $post_id, '_room_description_0', TRUE ) == '' )
	            {
		            update_post_meta( $post_id, '_room_description_0', utf8_encode($property['Description']) );
		        }
			
				// Media - Images
				if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
    			{
    				$this->add_log( 'The AgentOS API format can\'t support storing photos as URLs', $property['OID'] );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_photos', TRUE );

					if ( isset($property['photos']) && !empty($property['photos']) )
					{
						foreach ( $property['photos'] as $image )
						{
							if ( 
								isset($image['PhotoType']) && strtolower($image['PhotoType']) == 'photo'
							)
							{
								// This is a URL
								$url = 'https://live-api.letmc.com/v4/advertising/' . urlencode($options['short_name']) . '/download/' . $image['OID'] . '?api_key=' . urlencode($options['api_key']);
								$description = $image['Name'];
								$etag = $image['ETag'];

								// Check, based on the URL, whether we have previously imported this media
								$imported_previously = false;
								$imported_previously_id = '';
								if ( is_array($previous_media_ids) && !empty($previous_media_ids) )
								{
									foreach ( $previous_media_ids as $previous_media_id )
									{
										if ( 
											get_post_meta( $previous_media_id, '_imported_url', TRUE ) == $url &&
											(
												get_post_meta( $previous_media_id, '_imported_etag', TRUE ) != '' &&
												get_post_meta( $previous_media_id, '_imported_etag', TRUE ) == $etag
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

								    $name = $image['OID'] . '.jpg';

								    $file_array = array(
								        'name' => $name,
								        'tmp_name' => $tmp
								    );

								    // Check for download errors
								    if ( is_wp_error( $tmp ) ) 
								    {
								        @unlink( $file_array[ 'tmp_name' ] );

								        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['OID'] );
								    }
								    else
								    {
									    $id = media_handle_sideload( $file_array, $post_id, $description );

									    // Check for handle sideload errors.
									    if ( is_wp_error( $id ) ) 
									    {
									        @unlink( $file_array['tmp_name'] );
									        
									        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['OID'] );
									    }
									    else
									    {
									    	$media_ids[] = $id;

									    	update_post_meta( $id, '_imported_url', $url );
									    	update_post_meta( $id, '_imported_etag', $etag );

									    	++$new;
									    }
									}

									++$requests;
									if ( $requests >= 10 ) { sleep(5); $requests = 0; }
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

					$this->add_log( 'Imported ' . count($media_ids) . ' photos (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property['OID'] );
				}

				// Media - Floorplans
				if ( get_option('propertyhive_floorplans_stored_as', '') == 'urls' )
    			{
    				$this->add_log( 'The AgentOS API format can\'t support storing floorplans as URLs', $property['OID'] );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_floorplans', TRUE );

					if ( isset($property['photos']) && !empty($property['photos']) )
					{
						foreach ( $property['photos'] as $image )
						{
							if ( 
								isset($image['PhotoType']) && strtolower($image['PhotoType']) == 'floorplan'
							)
							{
								// This is a URL
								$url = 'https://live-api.letmc.com/v4/advertising/' . urlencode($options['short_name']) . '/download/' . $image['OID'] . '?api_key=' . urlencode($options['api_key']);
								$description = $image['Name'];
								$etag = $image['ETag'];

								// Check, based on the URL, whether we have previously imported this media
								$imported_previously = false;
								$imported_previously_id = '';
								if ( is_array($previous_media_ids) && !empty($previous_media_ids) )
								{
									foreach ( $previous_media_ids as $previous_media_id )
									{
										if ( 
											get_post_meta( $previous_media_id, '_imported_url', TRUE ) == $url &&
											(
												get_post_meta( $previous_media_id, '_imported_etag', TRUE ) != '' &&
												get_post_meta( $previous_media_id, '_imported_etag', TRUE ) == $etag
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

								    $name = $image['OID'] . '.jpg';

								    $file_array = array(
								        'name' => $name,
								        'tmp_name' => $tmp
								    );

								    // Check for download errors
								    if ( is_wp_error( $tmp ) ) 
								    {
								        @unlink( $file_array[ 'tmp_name' ] );

								        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['OID'] );
								    }
								    else
								    {
									    $id = media_handle_sideload( $file_array, $post_id, $description );

									    // Check for handle sideload errors.
									    if ( is_wp_error( $id ) ) 
									    {
									        @unlink( $file_array['tmp_name'] );
									        
									        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['OID'] );
									    }
									    else
									    {
									    	$media_ids[] = $id;

									    	update_post_meta( $id, '_imported_url', $url );
									    	update_post_meta( $id, '_imported_etag', $etag );

									    	++$new;
									    }
									}

									++$requests;
									if ( $requests >= 10 ) { sleep(5); $requests = 0; }
								}
							}
						}
					}
					if ( isset($property['floorplans']) && !empty($property['floorplans']) )
					{
						foreach ( $property['floorplans'] as $image )
						{
							if ( 
								isset($image['PhotoType']) && strtolower($image['PhotoType']) == 'floorplan'
							)
							{
								// This is a URL
								$url = 'https://live-api.letmc.com/v4/advertising/' . urlencode($options['short_name']) . '/download/' . $image['OID'] . '?api_key=' . urlencode($options['api_key']);
								$description = $image['Name'];
								$etag = $image['ETag'];

								// Check, based on the URL, whether we have previously imported this media
								$imported_previously = false;
								$imported_previously_id = '';
								if ( is_array($previous_media_ids) && !empty($previous_media_ids) )
								{
									foreach ( $previous_media_ids as $previous_media_id )
									{
										if ( 
											get_post_meta( $previous_media_id, '_imported_url', TRUE ) == $url &&
											(
												get_post_meta( $previous_media_id, '_imported_etag', TRUE ) != '' &&
												get_post_meta( $previous_media_id, '_imported_etag', TRUE ) == $etag
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

								    $name = $image['OID'] . '.jpg';

								    $file_array = array(
								        'name' => $name,
								        'tmp_name' => $tmp
								    );

								    // Check for download errors
								    if ( is_wp_error( $tmp ) ) 
								    {
								        @unlink( $file_array[ 'tmp_name' ] );

								        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['OID'] );
								    }
								    else
								    {
									    $id = media_handle_sideload( $file_array, $post_id, $description );

									    // Check for handle sideload errors.
									    if ( is_wp_error( $id ) ) 
									    {
									        @unlink( $file_array['tmp_name'] );
									        
									        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['OID'] );
									    }
									    else
									    {
									    	$media_ids[] = $id;

									    	update_post_meta( $id, '_imported_url', $url );
									    	update_post_meta( $id, '_imported_etag', $etag );

									    	++$new;
									    }
									}

									++$requests;
									if ( $requests >= 10 ) { sleep(5); $requests = 0; }
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

					$this->add_log( 'Imported ' . count($media_ids) . ' floorplans (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property['OID'] );
				}

				// Media - EPCs
				$unique_id_to_use_for_epcs = $property['OID'];
				if ( $property['department'] == 'residential-lettings' )
				{
					$unique_id_to_use_for_epcs = $property['PropertyID'];
				}
				if ( get_option('propertyhive_epcs_stored_as', '') == 'urls' )
    			{
    				$this->add_log( 'The AgentOS API format can\'t support storing EPCs as URLs', $property['OID'] );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_epcs', TRUE );

					if ( 
						isset($property['EPCCurrentEER']) && !empty($property['EPCCurrentEER']) &&
						isset($property['EPCPotentialEER']) && !empty($property['EPCPotentialEER'])
					)
					{
						// This is a URL
						$url = 'https://live-api.letmc.com/v4/advertising/' . urlencode($options['short_name']) . '/download/' . $unique_id_to_use_for_epcs . '/epc/EnergyEfficiency?api_key=' . urlencode($options['api_key']);
						$description = 'EnergyEfficiency';
					    
						$filename = basename( $url );

						// Check, based on the URL, whether we have previously imported this media
						$imported_previously = false;
						$imported_previously_id = '';
						if ( is_array($previous_media_ids) && !empty($previous_media_ids) )
						{
							foreach ( $previous_media_ids as $previous_media_id )
							{
								if ( 
									get_post_meta( $previous_media_id, '_imported_url', TRUE ) == $url &&
									get_post_meta( $previous_media_id, '_imported_current_eer', TRUE ) == $property['EPCCurrentEER'] &&
									get_post_meta( $previous_media_id, '_imported_potential_eer', TRUE ) == $property['EPCPotentialEER']
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

						    $name = $property['OID'] . '-eer.jpg';

						    $file_array = array(
						        'name' => $name,
						        'tmp_name' => $tmp
						    );

						    // Check for download errors
						    if ( is_wp_error( $tmp ) ) 
						    {
						        @unlink( $file_array[ 'tmp_name' ] );

						        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['OID'] );
						    }
						    else
						    {
							    $id = media_handle_sideload( $file_array, $post_id, $description );

							    // Check for handle sideload errors.
							    if ( is_wp_error( $id ) ) 
							    {
							        @unlink( $file_array['tmp_name'] );
							        
							        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['OID'] );
							    }
							    else
							    {
							    	$media_ids[] = $id;

							    	update_post_meta( $id, '_imported_url', $url );
							    	update_post_meta( $id, '_imported_current_eer', $property['EPCCurrentEER'] );
							    	update_post_meta( $id, '_imported_potential_eer', $property['EPCPotentialEER'] );

							    	++$new;
							    }
							}

							++$requests;
							if ( $requests >= 10 ) { sleep(5); $requests = 0; }
						}
					}
					if ( 
						isset($property['EPCCurrentEI']) && !empty($property['EPCCurrentEI']) &&
						isset($property['EPCPotentialEI']) && !empty($property['EPCPotentialEI'])
					)
					{
						// This is a URL
						$url = 'https://live-api.letmc.com/v4/advertising/' . urlencode($options['short_name']) . '/download/' . $unique_id_to_use_for_epcs . '/epc/EnvironmentalImpact?api_key=' . urlencode($options['api_key']);
						$description = 'EnvironmentalImpact';
					    
						$filename = basename( $url );

						// Check, based on the URL, whether we have previously imported this media
						$imported_previously = false;
						$imported_previously_id = '';
						if ( is_array($previous_media_ids) && !empty($previous_media_ids) )
						{
							foreach ( $previous_media_ids as $previous_media_id )
							{
								if ( 
									get_post_meta( $previous_media_id, '_imported_url', TRUE ) == $url &&
									get_post_meta( $previous_media_id, '_imported_current_eir', TRUE ) == $property['EPCCurrentEI'] &&
									get_post_meta( $previous_media_id, '_imported_potential_eir', TRUE ) == $property['EPCPotentialEI']
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

						    $name = $property['OID'] . '-eir.jpg';

						    $file_array = array(
						        'name' => $name,
						        'tmp_name' => $tmp
						    );

						    // Check for download errors
						    if ( is_wp_error( $tmp ) ) 
						    {
						        @unlink( $file_array[ 'tmp_name' ] );

						        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['OID'] );
						    }
						    else
						    {
							    $id = media_handle_sideload( $file_array, $post_id, $description );

							    // Check for handle sideload errors.
							    if ( is_wp_error( $id ) ) 
							    {
							        @unlink( $file_array['tmp_name'] );
							        
							        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['OID'] );
							    }
							    else
							    {
							    	$media_ids[] = $id;

							    	update_post_meta( $id, '_imported_url', $url );
							    	update_post_meta( $id, '_imported_current_eir', $property['EPCCurrentEI'] );
							    	update_post_meta( $id, '_imported_potential_eir', $property['EPCPotentialEI'] );

							    	++$new;
							    }
							}

							++$requests;
							if ( $requests >= 10 ) { sleep(5); $requests = 0; }
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

					$this->add_log( 'Imported ' . count($media_ids) . ' EPCs (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property['OID'] );
				}

				// Media - Brochures
				if ( get_option('propertyhive_brochures_stored_as', '') == 'urls' )
    			{
    				$this->add_log( 'The AgentOS API format can\'t support storing brochures as URLs', $property['OID'] );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_brochures', TRUE );

					// This is a URL
					$url = 'https://live-api.letmc.com/v4/advertising/' . urlencode($options['short_name']) . '/download/' . $property['OID'] . '/brochure?api_key=' . urlencode($options['api_key']);
					$description = 'Brochure';
				    
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

					    $name = $property['OID'] . '-brochure.pdf';

					    $file_array = array(
					        'name' => $name,
					        'tmp_name' => $tmp
					    );

					    // Check for download errors
					    if ( is_wp_error( $tmp ) ) 
					    {
					        @unlink( $file_array[ 'tmp_name' ] );

					        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['OID'] );
					    }
					    else
					    {
						    $id = media_handle_sideload( $file_array, $post_id, $description );

						    // Check for handle sideload errors.
						    if ( is_wp_error( $id ) ) 
						    {
						        @unlink( $file_array['tmp_name'] );
						        
						        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['OID'] );
						    }
						    else
						    {
						    	$media_ids[] = $id;

						    	update_post_meta( $id, '_imported_url', $url );

						    	++$new;
						    }
						}

						++$requests;
						if ( $requests >= 10 ) { sleep(5); $requests = 0; }
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

					$this->add_log( 'Imported ' . count($media_ids) . ' Brochures (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property['OID'] );
				}

				if ( isset($property['VideoURL']) && trim($property['VideoURL']) != '' )
				{
					update_post_meta($post_id, '_virtual_tours', 1);
				    update_post_meta($post_id, '_virtual_tour_0', $property['VideoURL']);

				    $this->add_log( 'Imported 1 virtual tours', $property['OID'] );
				}
				else
				{
					$this->add_log( 'Imported 0 virtual tours', $property['OID'] );
				}

				do_action( "propertyhive_property_imported_agentos_json", $post_id, $property );

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
							$this->add_log( 'New meta data for ' . trim($key, '_') . ': ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), $property['OID'] );
						}
						elseif ( $metadata_before[$key] != $metadata_after[$key] )
						{
							$this->add_log( 'Updated ' . trim($key, '_') . '. Before: ' . ( ( is_array($metadata_before[$key]) ) ? implode(", ", $metadata_before[$key]) : $metadata_before[$key] ) . ', After: ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), $property['OID'] );
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
							$this->add_log( 'New taxonomy data for ' . $taxonomy_name . ': ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), $property['OID'] );
						}
						elseif ( $taxonomy_terms_before[$taxonomy_name] != $taxonomy_terms_after[$taxonomy_name] )
						{
							$this->add_log( 'Updated ' . $taxonomy_name . '. Before: ' . ( ( is_array($taxonomy_terms_before[$taxonomy_name]) ) ? implode(", ", $taxonomy_terms_before[$taxonomy_name]) : $taxonomy_terms_before[$taxonomy_name] ) . ', After: ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), $property['OID'] );
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

		do_action( "propertyhive_post_import_properties_agentos_json" );

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
				$import_refs[] = $property['OID'];
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

						$this->add_log( 'Property marked as not on market', get_post_meta($post->ID, $imported_ref_key, TRUE) );

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

					do_action( "propertyhive_property_removed_agentos_json", $post->ID );
				}
			}
			wp_reset_postdata();

			unset($import_refs);
		}
	}

	public function get_mappings()
	{
		if ( !empty($this->mappings) )
		{
			return $this->mappings;
		}

		// Build mappings
		if ( get_option( 'propertyhive_active_departments_sales' ) == 'yes' )
		{
			$mapping_values = $this->get_letmc_mapping_values('sales_availability');
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
			$mapping_values = $this->get_letmc_mapping_values('lettings_availability');
			if ( is_array($mapping_values) && !empty($mapping_values) )
			{
				foreach ($mapping_values as $mapping_value => $text_value)
				{
					$this->mappings['lettings_availability'][$mapping_value] = '';
				}
			}
		}

		$mapping_values = $this->get_letmc_mapping_values('property_type');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['property_type'][$mapping_value] = '';
			}
		}

		/*$mapping_values = $this->get_letmc_mapping_values('price_qualifier');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['price_qualifier'][$mapping_value] = '';
			}
		}*/

		$mapping_values = $this->get_letmc_mapping_values('tenure');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['tenure'][$mapping_value] = '';
			}
		}

		$mapping_values = $this->get_letmc_mapping_values('sale_by');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['sale_by'][$mapping_value] = '';
			}
		}

		$mapping_values = $this->get_letmc_mapping_values('furnished');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['furnished'][$mapping_value] = '';
			}
		}

		$mapping_values = $this->get_letmc_mapping_values('office');
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
		return $this->get_letmc_mapping_values($custom_field);
	}

	public function get_letmc_mapping_values($custom_field) 
	{
        if ($custom_field == 'sales_availability')
        {
            return array(
            	'For Sale' => 'For Sale',
                'Under Offer' => 'Under Offer',
            );
        }

        if ($custom_field == 'lettings_availability')
        {
            return array(
            	'To Let' => 'To Let',
                'Let Agreed' => 'Let Agreed',
            );
        }

        if ($custom_field == 'property_type')
        {
        	return array(
                'House' => 'House',
                'DetachedHouse' => 'DetachedHouse',
                'SemiDetachedHouse' => 'SemiDetachedHouse',
				'TerracedHouse' => 'TerracedHouse',
				'EndTerraceHouse' => 'EndTerraceHouse',
				'Cottage' => 'Cottage',
				'Bungalow' => 'Bungalow',
				'FlatApartment' => 'FlatApartment',
				'HouseFlatShare' => 'HouseFlatShare',
            );
        }
        /*if ($custom_field == 'price_qualifier')
        {
        	return array(
        		'NotSpecified' => 'NotSpecified',
                'PriceOnApplication' => 'PriceOnApplication',
                'GuidePrice' => 'GuidePrice',
                'FixedPrice' => 'FixedPrice',
                'OffersInExcessOf' => 'OffersInExcessOf',
                'OffersInRegionOf' => 'OffersInRegionOf',
                'SaleByTender' => 'SaleByTender',
                'From' => 'From',
                'SharedOwnership' => 'SharedOwnership',
                'OffersOver' => 'OffersOver',
                'PartBuyPartRent' => 'PartBuyPartRent',
                'SharedEquity' => 'SharedEquity',
        	);
        }*/
        if ($custom_field == 'tenure')
        {
            return array(
            	'Leasehold' => 'Leasehold',
            	'Freehold' => 'Freehold',
            );
        }
        if ($custom_field == 'sale_by')
        {
            return array(
            	'PrivateTreaty' => 'PrivateTreaty',
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
}

}