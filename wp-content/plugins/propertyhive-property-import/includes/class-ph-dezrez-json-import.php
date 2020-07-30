<?php
/**
 * Class for managing the import process of an Dezrez JSON file
 *
 * @package WordPress
 */
if ( class_exists( 'PH_Property_Import_Process' ) ) {

class PH_Dezrez_JSON_Import extends PH_Property_Import_Process {

	public function __construct( $instance_id = '' ) 
	{
		$this->instance_id = $instance_id;

		if ( $this->instance_id != '' && isset($_GET['custom_property_import_cron']) )
	    {
	    	$current_user = wp_get_current_user();

	    	$this->add_log("Executed manually by " . ( ( isset($current_user->display_name) ) ? $current_user->display_name : '' ) );
	    }
	}

	public function parse( $import_id, $options = array() )
	{
		$search_url = 'https://api.dezrez.com/api/simplepropertyrole/search';
		$fields = array(
			'APIKey' => urlencode($options['api_key']),
		);

		$fields_string = '';
		foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
		$fields_string = rtrim($fields_string, '&');

		$search_url = $search_url . '?' . $fields_string;
		$contents = '';

		$post_fields = array( 'IncludeStc' => 'true', 'PageSize' => '999' );
		if ( isset($options['branch_ids']) && trim($options['branch_ids']) != '' )
		{
			$post_fields['BranchIdList'] = array();
			$branch_ids = explode(",", $options['branch_ids']);
			foreach ( $branch_ids as $branch_id )
			{
				$post_fields['BranchIdList'][] = trim($branch_id);
			}
		}
		if ( isset($options['tags']) && trim($options['tags']) != '' )
		{
			$post_fields['Tags'] = array();
			$tags = explode(",", $options['tags']);
			foreach ( $tags as $tag )
			{
				$post_fields['Tags'][] = trim($tag);
			}
		}

		$contents = '';

		$response = wp_remote_post( 
			$search_url, 
			array(
				'method' => 'POST',
				'timeout' => 120,
				'headers' => array(
					'Rezi-Api-Version' => '1.0',
					'Content-Type' => 'application/json'
				),
				'body' => json_encode( $post_fields ),
		    )
		);

		if ( !is_wp_error( $response ) && is_array( $response ) ) 
		{
			$contents = $response['body'];

			$json = json_decode( $contents, TRUE );

			if ($json !== FALSE && isset($json['Collection']) && !empty($json['Collection']))
			{
				$this->add_log("Parsing properties");
				
	            $properties_imported = 0;

	            $properties_array = $json['Collection'];

				$imported_ref_key = ( ( $import_id != '' ) ? '_imported_ref_' . $import_id : '_imported_ref' );

				$this->add_log("Found " . count($properties_array) . " properties in JSON ready for parsing");

				foreach ($properties_array as $property)
				{
					$property_id = $property['RoleId'];

					$agent_ref = $property_id;

					$ok_to_import = true;

					if ( ( isset($options['only_updated']) && $options['only_updated'] == 'yes' ) || !isset($options['only_updated']) )
					{
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
				        	while ($property_query->have_posts())
				        	{
				        		$property_query->the_post();

			                	$dezrez_last_updated = $property['LastUpdated'];
			                	$last_imported_date = get_the_modified_date( 'Y-m-d H:i:s' );
			                	if ($last_imported_date != '')
			                	{
			                		if (strtotime($last_imported_date) >= strtotime($dezrez_last_updated))
			                		{
			                			$ok_to_import = false;
			                		}
			                	}
			                }
		                }
		            }

	                if ($ok_to_import)
	                {
						$property_url = 'https://api.dezrez.com/api/simplepropertyrole/' . $property_id;
						$fields = array(
							'APIKey' => urlencode($options['api_key']),
						);

						//url-ify the data for the POST
						$fields_string = '';
						foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
						$fields_string = rtrim($fields_string, '&');

						$property_url = $property_url . '?' . $fields_string;
						$contents = '';

						if ( function_exists('curl_version') )
						{
							$curl = curl_init();
						    curl_setopt($curl, CURLOPT_URL, $property_url);
						    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
						    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
						    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
								'Rezi-Api-Version: 1.0',
								'Content-Type: application/json'
							));
						    $contents = curl_exec($curl);
						    curl_close($curl);
			    		}
			    		else
			    		{
			    			die("cURL is required, however not active on your server");
			    		}

						$property_json = json_decode($contents, TRUE);
						if ($property_json !== FALSE)
						{
							$property_json['RoleId'] = $property_id;
							$property_json['SummaryTextDescription'] = ( ( isset($property['SummaryTextDescription']) && !empty($property['SummaryTextDescription']) ) ? $property['SummaryTextDescription'] : '' );
							$this->properties[] = $property_json;
						}
					}
					else
					{
						// Property not been updated.
						// Lets create our own array so at least the property gets put into the $this->properties array
						$property_json = array(
							'RoleId' => $property_id,
							'fake' => 'yes'
						);
						$this->properties[] = $property_json;
					}
				}
	        }
	        else
	        {
	        	// Failed to parse JSON
	        	$this->add_error( 'Failed to parse JSON file. Possibly invalid JSON' );
	        }
	    }
        else
        {
        	$this->add_error( 'Failed to obtain JSON. Dump of response as follows: ' . print_r($response, TRUE) );
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

        do_action( "propertyhive_pre_import_properties_dezrez_json", $this->properties );
        $this->properties = apply_filters( "propertyhive_dezrez_json_properties_due_import", $this->properties );

		$this->add_log( 'Beginning to loop through ' . count($this->properties) . ' properties' );

		$property_row = 1;
		foreach ( $this->properties as $property )
		{
			if ( !isset($property['fake']) )
			{
				$this->add_log( 'Importing property ' . $property_row . ' with reference ' . $property['RoleId'], $property['RoleId'] );

				$inserted_updated = false;

				$args = array(
		            'post_type' => 'property',
		            'posts_per_page' => 1,
		            'post_status' => 'any',
		            'meta_query' => array(
		            	array(
			            	'key' => $imported_ref_key,
			            	'value' => $property['RoleId']
			            )
		            )
		        );
		        $property_query = new WP_Query($args);

		        $display_address = array();
		        if ( isset($property['Address']['Street']) && trim($property['Address']['Street']) != '' )
		        {
		        	$display_address[] = trim($property['Address']['Street']);
		        }
		        if ( isset($property['Address']['Town']) && trim($property['Address']['Town']) != '' )
		        {
		        	$display_address[] = trim($property['Address']['Town']);
		        }
		        elseif ( isset($property['Address']['Locality']) && trim($property['Address']['Locality']) != '' )
		        {
		        	$display_address[] = trim($property['Address']['Locality']);
		        }
		        elseif ( isset($property['Address']['County']) && trim($property['Address']['County']) != '' )
		        {
		        	$display_address[] = trim($property['Address']['County']);
		        }
		        $display_address = implode(", ", $display_address);
		        
		        if ($property_query->have_posts())
		        {
		        	$this->add_log( 'This property has been imported before. Updating it', $property['RoleId'] );

		        	// We've imported this property before
		            while ($property_query->have_posts())
		            {
		                $property_query->the_post();

		                $post_id = get_the_ID();

		                $my_post = array(
					    	'ID'          	 => $post_id,
					    	'post_title'     => wp_strip_all_tags( $display_address ),
					    	'post_excerpt'   => $property['SummaryTextDescription'],
					    	'post_content' 	 => '',
					    	'post_status'    => 'publish',
					  	);

					 	// Update the post into the database
					    $post_id = wp_update_post( $my_post );

					    if ( is_wp_error( $post_id ) ) 
						{
							$this->add_error( 'Failed to update post. The error was as follows: ' . $post_id->get_error_message(), $property['RoleId'] );
						}
						else
						{
							$inserted_updated = 'updated';
						}
		            }
		        }
		        else
		        {
		        	$this->add_log( 'This property hasn\'t been imported before. Inserting it', $property['RoleId'] );

		        	// We've not imported this property before
					$postdata = array(
						'post_excerpt'   => $property['SummaryTextDescription'],
						'post_content' 	 => '',
						'post_title'     => wp_strip_all_tags( $display_address ),
						'post_status'    => 'publish',
						'post_type'      => 'property',
						'comment_status' => 'closed',
					);

					$post_id = wp_insert_post( $postdata, true );

					if ( is_wp_error( $post_id ) ) 
					{
						$this->add_error( 'Failed to insert post. The error was as follows: ' . $post_id->get_error_message(), $property['RoleId'] );
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
					    	'post_excerpt'   => htmlentities(mb_convert_encoding(wp_strip_all_tags( $property['SummaryTextDescription'] ), 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
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

					$this->add_log( 'Successfully ' . $inserted_updated . ' post. The post ID is ' . $post_id, $property['RoleId'] );

					update_post_meta( $post_id, $imported_ref_key, $property['RoleId'] );

					// Address
					update_post_meta( $post_id, '_reference_number', $property['RoleId'] );
					update_post_meta( $post_id, '_address_name_number', trim( ( ( isset($property['Address']['BuildingName']) ) ? $property['Address']['BuildingName'] : '' ) . ' ' . ( ( isset($property['Address']['Number']) ) ? $property['Address']['Number'] : '' ) ) );
					update_post_meta( $post_id, '_address_street', ( ( isset($property['Address']['Street']) ) ? $property['Address']['Street'] : '' ) );
					update_post_meta( $post_id, '_address_two', ( ( isset($property['Address']['Locality']) ) ? $property['Address']['Locality'] : '' ) );
					update_post_meta( $post_id, '_address_three', ( ( isset($property['Address']['Town']) ) ? $property['Address']['Town'] : '' ) );
					update_post_meta( $post_id, '_address_four', ( ( isset($property['Address']['County']) ) ? $property['Address']['County'] : '' ) );
					update_post_meta( $post_id, '_address_postcode', ( ( isset($property['Address']['Postcode']) ) ? $property['Address']['Postcode'] : '' ) );

					$country = get_option( 'propertyhive_default_country', 'GB' );
					update_post_meta( $post_id, '_address_country', $country );

					// Check main address fields and see if this location exists as a taxonomy to try and assign properties to location
					$address_fields_to_check = apply_filters( 'propertyhive_dezrez_json_address_fields_to_check', array('Locality', 'Town', 'County') );
					$location_term_ids = array();

					foreach ( $address_fields_to_check as $address_field )
					{
						if ( isset($property['Address'][$address_field]) && trim($property['Address'][$address_field]) != '' ) 
						{
							$term = term_exists( trim($property['Address'][$address_field]), 'location');
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
					update_post_meta( $post_id, '_latitude', ( ( isset($property['Address']['Location']['Latitude']) ) ? $property['Address']['Location']['Latitude'] : '' ) );
					update_post_meta( $post_id, '_longitude', ( ( isset($property['Address']['Location']['Longitude']) ) ? $property['Address']['Location']['Longitude'] : '' ) );

					// Owner
					add_post_meta( $post_id, '_owner_contact_id', '', true );

					// Record Details
					add_post_meta( $post_id, '_negotiator_id', get_current_user_id(), true );
					
					$office_id = $primary_office_id;
					if ( 
						(isset($_POST['mapped_office'][$property['BranchDetails']['Name']]) && $_POST['mapped_office'][$property['BranchDetails']['Name']] != '') 
						||
						(isset($_POST['mapped_office'][$property['BranchDetails']['Id']]) && $_POST['mapped_office'][$property['BranchDetails']['Id']] != '') 
					)
					{
						$office_id = $_POST['mapped_office'][$property['BranchDetails']['Name']];
					}
					elseif ( isset($options['offices']) && is_array($options['offices']) && !empty($options['offices']) )
					{
						foreach ( $options['offices'] as $ph_office_id => $branch_code )
						{
							if ( 
								$branch_code == $property['BranchDetails']['Name']
								|| 
								$branch_code == $property['BranchDetails']['Id']
							)
							{
								$office_id = $ph_office_id;
								break;
							}
						}
					}
					update_post_meta( $post_id, '_office_id', $office_id );

					$department = ( (strtolower($property['RoleType']['SystemName']) != 'selling') ? 'residential-lettings' : 'residential-sales' );

					if ( isset($property['PropertyType']['SystemName']) && $property['PropertyType']['SystemName'] != '' )
					{
						if ( get_option( 'propertyhive_active_departments_commercial' ) == 'yes' )
						{
							$commercial_types = array( 'commercial', 'retail', 'restaurant', 'office', 'industrial' );
							$commercial_types = apply_filters( 'propertyhive_dezrez_json_commercial_property_types', $commercial_types );

							foreach ( $commercial_types as $commercial_type )
							{
								if ( strpos( strtolower($property['PropertyType']['SystemName']), $commercial_type) !== FALSE )
								{
									$department = 'commercial';
								}
							}
						}
			        }

			        update_post_meta( $post_id, '_department', $department );

					if ( isset($property['Descriptions']) && is_array($property['Descriptions']) && !empty($property['Descriptions']) )
					{
						foreach ( $property['Descriptions'] as $description )
						{
							// Room Counts
							if ( 
								$description['Name'] == 'Room Counts' ||  
								( isset($description['DescriptionType']['SystemName']) && $description['DescriptionType']['SystemName'] == 'RoomCount' )
							)
							{
								update_post_meta( $post_id, '_bedrooms', ( ( isset($description['Bedrooms']) ) ? $description['Bedrooms'] : '' ) );
								update_post_meta( $post_id, '_bathrooms', ( ( isset($description['Bathrooms']) ) ? $description['Bathrooms'] : '' ) );
								update_post_meta( $post_id, '_reception_rooms', ( ( isset($description['Receptions']) ) ? $description['Receptions'] : '' ) );
							}

							if ( 
								$description['Name'] == 'StyleAge' ||
								( isset($description['DescriptionType']['SystemName']) && $description['DescriptionType']['SystemName'] == 'StyleAge' )
							)
							{
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
								
								if ( isset($description['PropertyType']['SystemName']) && $description['PropertyType']['SystemName'] != '' )
								{
									if ( !empty($mapping) && isset($mapping[$description['PropertyType']['SystemName']]) )
									{
							            wp_set_post_terms( $post_id, $mapping[$description['PropertyType']['SystemName']], 'property_type' );
						            }
						            else
						            {
						            	$this->add_log( 'Property received with a type (' . $description['PropertyType']['SystemName'] . ') that is not mapped', $property['RoleId'] );

						            	$options = $this->add_missing_mapping( $mapping, 'property_type', $description['PropertyType']['SystemName'], $import_id );
						            }
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
								if ( !empty($mapping) && isset($description['LeaseType']['SystemName']) && isset($mapping[$description['LeaseType']['SystemName']]) )
								{
						            wp_set_post_terms( $post_id, $mapping[$description['LeaseType']['SystemName']], 'tenure' );
					            }
							}

							// Features
							if ( $description['Name'] == 'Feature Description' ||  $description['DescriptionType']['SystemName'] == 'Feature' )
							{
								$features = array();
								if ( isset($description['Features']) && is_array($description['Features']) && !empty($description['Features']) )
								{
									foreach ( $description['Features'] as $feature )
									{
										if ( isset($feature['Feature']) && trim($feature['Feature']) != '' )
										{
											$features[] = $feature['Feature'];
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
							}

							// Rooms
							if ( isset($description['Rooms']) && is_array($description['Rooms']) && !empty($description['Rooms']) )
							{
								if ( $department != 'commercial' )
								{
							        $new_room_count = 0;
									foreach ($description['Rooms'] as $room)
									{
										update_post_meta( $post_id, '_room_name_' . $new_room_count, $room['Name'] );
							            update_post_meta( $post_id, '_room_dimensions_' . $new_room_count, '' );
							            update_post_meta( $post_id, '_room_description_' . $new_room_count, $room['Text'] );

								        ++$new_room_count;
									}
									update_post_meta( $post_id, '_rooms', $new_room_count );
								}
								else
								{
									$new_room_count = 0;
									foreach ($description['Rooms'] as $room)
									{
										update_post_meta( $post_id, '_description_name_' . $new_room_count, $room['Name'] );
							            update_post_meta( $post_id, '_description_' . $new_room_count, $room['Text'] );

								        ++$new_room_count;
									}
									update_post_meta( $post_id, '_descriptions', $new_room_count );
								}
							}
						}
					}

					$poa = '';
					$featured = '';
					$on_market = '';

					if ( isset($property['Flags']) && is_array($property['Flags']) && !empty($property['Flags']) )
					{
						foreach ( $property['Flags'] as $flag )
						{
							if ( isset($flag['SystemName']) && !empty($flag['SystemName']) )
							{
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
									if ( isset($_POST['mapped_availability']) )
									{
										$mapping = $_POST['mapped_availability'];
									}
									else
									{
										$mapping = isset($options['mappings']['availability']) ? $options['mappings']['availability'] : array();
									}
								}

								if ( !empty($mapping) && isset($mapping[$flag['SystemName']]) )
								{
					                wp_set_post_terms( $post_id, $mapping[$flag['SystemName']], 'availability' );
					            }

					            if ( $flag['SystemName'] == 'ApprovedForMarketingWebsite' || $flag['SystemName'] == 'OnMarket' )
					            {
					            	$on_market = 'yes';
					            }

					            if ( $flag['SystemName'] == 'Featured' )
					            {
					            	$featured = 'yes';
					            }

					            if ( $flag['SystemName'] == 'PriceOnApplication' )
					            {
					            	$poa = 'yes';
					            }
							}
						}
					}

					update_post_meta( $post_id, '_on_market', $on_market );
					update_post_meta( $post_id, '_featured', $featured );

		            $price = preg_replace("/[^0-9.]/", '', $property['Price']['PriceValue']);

					if (
						isset($property['Price']['PriceQualifierType']['SystemName']) && 
						( strtolower($property['Price']['PriceQualifierType']['SystemName']) == 'priceonapplication' || strtolower($property['Price']['PriceQualifierType']['SystemName']) == 'poa' )
					)
					{
						$poa = 'yes';
					}

					// Residential Sales Details
					if ( $department == 'residential-sales' )
					{
						update_post_meta( $post_id, '_price', $price );
						update_post_meta( $post_id, '_price_actual', $price );

						update_post_meta( $post_id, '_currency', 'GBP' );

						update_post_meta( $post_id, '_poa', $poa );

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
						if ( !empty($mapping) )
						{
							if ( isset($property['Price']['PriceQualifierType']['SystemName']) && isset($mapping[$property['Price']['PriceQualifierType']['SystemName']]) )
							{
				                wp_set_post_terms( $post_id, $mapping[$property['Price']['PriceQualifierType']['SystemName']], 'price_qualifier' );
				            }
				            if ( isset($property['Price']['PriceQualifierType']['DisplayName']) && isset($mapping[$property['Price']['PriceQualifierType']['DisplayName']]) )
							{
				                wp_set_post_terms( $post_id, $mapping[$property['Price']['PriceQualifierType']['DisplayName']], 'price_qualifier' );
				            }
			            }			            
					}
					elseif ( $department == 'residential-lettings' )
					{
						$rent_frequency = 'pcm';
						$price_actual = $price;

						if ( isset($property['Price']['PriceType']['SystemName']) )
						{
							switch ($property['Price']['PriceType']['SystemName'])
							{
								case "Daily": { $rent_frequency = 'pw'; $price = ($price * 365) / 52; $price_actual = ($price * 52) / 12; break; }
								case "Weekly": { $rent_frequency = 'pw'; $price_actual = ($price * 52) / 12; break; }
								case "Fortnightly": { $rent_frequency = 'pw'; $price = ($price / 2); $price_actual = ($price * 52) / 12; break; }
								case "FourWeekly": { $rent_frequency = 'pcm'; $price = ($price * 13) / 12; $price_actual = $price; break; }
								case "Quarterly": { $rent_frequency = 'pq'; $price_actual = ($price * 4) / 12; break; }
								case "SixMonthly": { $rent_frequency = 'pa'; $price = ($price * 2); $price_actual = $price / 12; break; }
								case "Yearly": { $rent_frequency = 'pa'; $price_actual = $price / 12; break; }
							}
						}

						update_post_meta( $post_id, '_rent', $price );
						update_post_meta( $post_id, '_rent_frequency', $rent_frequency );
						update_post_meta( $post_id, '_price_actual', $price_actual );

						update_post_meta( $post_id, '_currency', 'GBP' );

						update_post_meta( $post_id, '_poa', $poa );

						update_post_meta( $post_id, '_deposit', '' );
	            		update_post_meta( $post_id, '_available_date', '' );

	            		// We don't receive furnished options in the feed
					}
					elseif ( $department == 'commercial' )
					{
						update_post_meta( $post_id, '_for_sale', '' );
	            		update_post_meta( $post_id, '_to_rent', '' );

						if ( strtolower($property['RoleType']['SystemName']) == 'selling' )
		                {
		                    update_post_meta( $post_id, '_for_sale', 'yes' );

		                    update_post_meta( $post_id, '_commercial_price_currency', 'GBP' );

		                    update_post_meta( $post_id, '_price_from', $price );
		                    update_post_meta( $post_id, '_price_to', $price );

		                    update_post_meta( $post_id, '_price_units', '' );

		                    update_post_meta( $post_id, '_price_poa', $poa );
						}
						else
		                {
		                    update_post_meta( $post_id, '_to_rent', 'yes' );

		                    update_post_meta( $post_id, '_commercial_rent_currency', 'GBP' );

		                    update_post_meta( $post_id, '_rent_from', $price );
		                    update_post_meta( $post_id, '_rent_to', $price );

		                    $rent_frequency = 'pcm';
		                    update_post_meta( $post_id, '_rent_units', $rent_frequency );

		                    update_post_meta( $post_id, '_rent_poa', $poa );
						}

						update_post_meta( $post_id, '_floor_area_from', '' );
						update_post_meta( $post_id, '_floor_area_from_sqft', '' );
						update_post_meta( $post_id, '_floor_area_to', '' );
						update_post_meta( $post_id, '_floor_area_to_sqft', '' );
						update_post_meta( $post_id, '_floor_area_units', 'sqft');
					}
				
					// Media - Images
					if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
        			{
        				$media_urls = array();
        				if ( isset($property['Images']) && !empty($property['Images']) )
						{
							foreach ( $property['Images'] as $image )
							{
								if ( 
									isset($image['Url']) && $image['Url'] != ''
									&&
									(
										substr( strtolower($image['Url']), 0, 2 ) == '//' || 
										substr( strtolower($image['Url']), 0, 4 ) == 'http'
									)
									&&
									isset($image['DocumentType']['SystemName']) && $image['DocumentType']['SystemName'] == 'Image'
									&&
									isset($image['DocumentSubType']['SystemName']) && $image['DocumentSubType']['SystemName'] == 'Photo'
								)
								{
									// This is a URL
									$url = $image['Url'];
									if ( strpos(strtolower($url), 'width=') === FALSE )
									{
										// If no width passed then set to 2048
										$url .= ( ( strpos($url, '?') === FALSE ) ? '?' : '&' ) . 'width=';
										$url .= apply_filters( 'propertyhive_dezrez_json_image_width', '2048' );
									}

									$media_urls[] = array('url' => $url);
								}
							}
						}
						update_post_meta( $post_id, '_photo_urls', $media_urls );

						$this->add_log( 'Imported ' . count($media_urls) . ' photo URLs', $property['RoleId'] );
        			}
        			else
	        		{
						$media_ids = array();
						$new = 0;
						$existing = 0;
						$deleted = 0;
						$previous_media_ids = get_post_meta( $post_id, '_photos', TRUE );

						if ( isset($property['Images']) && !empty($property['Images']) )
						{
							foreach ( $property['Images'] as $image )
							{
								if ( 
									isset($image['Url']) && $image['Url'] != ''
									&&
									(
										substr( strtolower($image['Url']), 0, 2 ) == '//' || 
										substr( strtolower($image['Url']), 0, 4 ) == 'http'
									)
									&&
									isset($image['DocumentType']['SystemName']) && $image['DocumentType']['SystemName'] == 'Image'
									&&
									isset($image['DocumentSubType']['SystemName']) && $image['DocumentSubType']['SystemName'] == 'Photo'
								)
								{
									// This is a URL
									$url = $image['Url'];
									if ( strpos(strtolower($url), 'width=') === FALSE )
									{
										// If no width passed then set to 2048
										$url .= ( ( strpos($url, '?') === FALSE ) ? '?' : '&' ) . 'width=';
										$url .= apply_filters( 'propertyhive_dezrez_json_image_width', '2048' );
									}

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

									    $exploded_filename = explode(".", $filename);
									    $ext = 'jpg';
									    if (strlen($exploded_filename[count($exploded_filename)-1]) == 3)
									    {
									    	$ext = $exploded_filename[count($exploded_filename)-1];
									    }
									    $name = $property['RoleId'] . '_' . $image['Id'] . '.' . $ext;

									    $file_array = array(
									        'name' => $name,
									        'tmp_name' => $tmp
									    );

									    // Check for download errors
									    if ( is_wp_error( $tmp ) ) 
									    {
									        @unlink( $file_array[ 'tmp_name' ] );

									        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['RoleId'] );
									    }
									    else
									    {
										    $id = media_handle_sideload( $file_array, $post_id, $description );

										    // Check for handle sideload errors.
										    if ( is_wp_error( $id ) ) 
										    {
										        @unlink( $file_array['tmp_name'] );
										        
										        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['RoleId'] );
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

						$this->add_log( 'Imported ' . count($media_ids) . ' photos (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property['RoleId'] );
					}

					// Media - Floorplans
					if ( get_option('propertyhive_floorplans_stored_as', '') == 'urls' )
        			{
        				$media_urls = array();
        				if ( isset($property['Documents']) && !empty($property['Documents']) )
						{
							foreach ( $property['Documents'] as $document )
							{
								if ( 
									isset($document['Url']) && $document['Url'] != ''
									&&
									(
										substr( strtolower($document['Url']), 0, 2 ) == '//' || 
										substr( strtolower($document['Url']), 0, 4 ) == 'http'
									)
									&&
									isset($document['DocumentType']['SystemName']) && $document['DocumentType']['SystemName'] == 'Image'
									&&
									isset($document['DocumentSubType']['SystemName']) && $document['DocumentSubType']['SystemName'] == 'Floorplan'
								)
								{
									// This is a URL
									$url = $document['Url'];
									if ( strpos(strtolower($url), 'width=') === FALSE )
									{
										// If no width passed then set to 2048
										$url .= ( ( strpos($url, '?') === FALSE ) ? '?' : '&' ) . 'width=';
										$url .= apply_filters( 'propertyhive_dezrez_json_floorplan_width', '2048' );
									}

									$media_urls[] = array('url' => $url);
								}
							}
						}
						update_post_meta( $post_id, '_floorplan_urls', $media_urls );

						$this->add_log( 'Imported ' . count($media_urls) . ' floorplan URLs', $property['RoleId'] );
        			}
        			else
        			{
						$media_ids = array();
						$new = 0;
						$existing = 0;
						$deleted = 0;
						$previous_media_ids = get_post_meta( $post_id, '_floorplans', TRUE );
						if ( isset($property['Documents']) && !empty($property['Documents']) )
						{
							foreach ( $property['Documents'] as $document )
							{
								if ( 
									isset($document['Url']) && $document['Url'] != ''
									&&
									(
										substr( strtolower($document['Url']), 0, 2 ) == '//' || 
										substr( strtolower($document['Url']), 0, 4 ) == 'http'
									)
									&&
									isset($document['DocumentType']['SystemName']) && $document['DocumentType']['SystemName'] == 'Image'
									&&
									isset($document['DocumentSubType']['SystemName']) && $document['DocumentSubType']['SystemName'] == 'Floorplan'
								)
								{
									// This is a URL
									$url = $document['Url'];
									if ( strpos(strtolower($url), 'width=') === FALSE )
									{
										// If no width passed then set to 2048
										$url .= ( ( strpos($url, '?') === FALSE ) ? '?' : '&' ) . 'width=';
										$url .= apply_filters( 'propertyhive_dezrez_json_floorplan_width', '2048' );
									}
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

									    $exploded_filename = explode(".", $filename);
									    $ext = 'jpg';
									    if (strlen($exploded_filename[count($exploded_filename)-1]) == 3)
									    {
									    	$ext = $exploded_filename[count($exploded_filename)-1];
									    }
									    $name = $property['RoleId'] . '_' . $document['Id'] . '.' . $ext;

									    $file_array = array(
									        'name' => $name,
									        'tmp_name' => $tmp
									    );

									    // Check for download errors
									    if ( is_wp_error( $tmp ) ) 
									    {
									        @unlink( $file_array[ 'tmp_name' ] );

									        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['RoleId'] );
									    }
									    else
									    {
										    $id = media_handle_sideload( $file_array, $post_id, $description );
										    
										    // Check for handle sideload errors.
										    if ( is_wp_error( $id ) ) 
										    {
										        @unlink( $file_array['tmp_name'] );
										        
										        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['RoleId'] );
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

						$this->add_log( 'Imported ' . count($media_ids) . ' floorplans (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property['RoleId'] );
					}

					// Media - Brochures
					if ( get_option('propertyhive_brochures_stored_as', '') == 'urls' )
        			{
        				$media_urls = array();
        				if ( isset($property['Documents']) && !empty($property['Documents']) )
						{
							foreach ( $property['Documents'] as $document )
							{
								if ( 
									isset($document['Url']) && $document['Url'] != ''
									&&
									(
										substr( strtolower($document['Url']), 0, 2 ) == '//' || 
										substr( strtolower($document['Url']), 0, 4 ) == 'http'
									)
									&&
									isset($document['DocumentType']['SystemName']) && $document['DocumentType']['SystemName'] == 'Document'
			                        &&
			                        isset($document['DocumentSubType']['SystemName']) && $document['DocumentSubType']['SystemName'] == 'Brochure'
								)
								{
									// This is a URL
									$url = $document['Url'];

									$media_urls[] = array('url' => $url);
								}
							}
						}
						update_post_meta( $post_id, '_brochure_urls', $media_urls );

						$this->add_log( 'Imported ' . count($media_urls) . ' brochure URLs', $property['RoleId'] );
        			}
        			else
        			{
						$media_ids = array();
						$new = 0;
						$existing = 0;
						$deleted = 0;
						$previous_media_ids = get_post_meta( $post_id, '_brochures', TRUE );
						if ( isset($property['Documents']) && !empty($property['Documents']) )
						{
							foreach ( $property['Documents'] as $document )
							{
								if ( 
									isset($document['Url']) && $document['Url'] != ''
									&&
									(
										substr( strtolower($document['Url']), 0, 2 ) == '//' || 
										substr( strtolower($document['Url']), 0, 4 ) == 'http'
									)
									&&
									isset($document['DocumentType']['SystemName']) && $document['DocumentType']['SystemName'] == 'Document'
			                        &&
			                        isset($document['DocumentSubType']['SystemName']) && $document['DocumentSubType']['SystemName'] == 'Brochure'
								)
								{
									// This is a URL
									$url = $document['Url'];
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

									    $exploded_filename = explode(".", $filename);
									    $ext = 'pdf';
									    if (strlen($exploded_filename[count($exploded_filename)-1]) == 3)
									    {
									    	$ext = $exploded_filename[count($exploded_filename)-1];
									    }
									    $name = $property['RoleId'] . '_' . $document['Id'] . '.' . $ext;

									    $file_array = array(
									        'name' => $name,
									        'tmp_name' => $tmp
									    );

									    // Check for download errors
									    if ( is_wp_error( $tmp ) ) 
									    {
									        @unlink( $file_array[ 'tmp_name' ] );

									        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['RoleId'] );
									    }
									    else
									    {
										    $id = media_handle_sideload( $file_array, $post_id, $description );
										    
										    // Check for handle sideload errors.
										    if ( is_wp_error( $id ) ) 
										    {
										        @unlink( $file_array['tmp_name'] );
										        
										        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['RoleId'] );
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

						$this->add_log( 'Imported ' . count($media_ids) . ' brochures (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property['RoleId'] );
					}

					// Media - EPCs
					if ( get_option('propertyhive_epcs_stored_as', '') == 'urls' )
        			{
        				$media_urls = array();

        				if ( 
							isset($property['EPC']['Image']['Url']) && 
							!empty($property['EPC']['Image']['Url'])  && 
							(
								substr( strtolower($property['EPC']['Image']['Url']), 0, 2 ) == '//' || 
								substr( strtolower($property['EPC']['Image']['Url']), 0, 4 ) == 'http'
							)
						)
	            		{
							// This is a URL
							$url = $property['EPC']['Image']['Url'];

							$media_urls[] = array('url' => $url);
						}
						if ( isset($property['Documents']) && !empty($property['Documents']) )
						{
							foreach ( $property['Documents'] as $document )
							{
								if ( 
									isset($document['Url']) && $document['Url'] != ''
									&&
									(
										substr( strtolower($document['Url']), 0, 2 ) == '//' || 
										substr( strtolower($document['Url']), 0, 4 ) == 'http'
									)
									&&
									isset($document['DocumentType']['SystemName']) && $document['DocumentType']['SystemName'] == 'Document'
			                        &&
			                        isset($document['DocumentSubType']['SystemName']) && $document['DocumentSubType']['SystemName'] == 'EPC'
								)
								{
									// This is a URL
									$url = $document['Url'];

									$media_urls[] = array('url' => $url);
								}
							}
						}

						update_post_meta( $post_id, '_epc_urls', $media_urls );

						$this->add_log( 'Imported ' . count($media_urls) . ' EPC URLs', $property['RoleId'] );
        			}
        			else
        			{
						$media_ids = array();
						$new = 0;
						$existing = 0;
						$deleted = 0;
						$previous_media_ids = get_post_meta( $post_id, '_epcs', TRUE );
						if ( 
							isset($property['EPC']['Image']['Url']) && 
							!empty($property['EPC']['Image']['Url'])  && 
							(
								substr( strtolower($property['EPC']['Image']['Url']), 0, 2 ) == '//' || 
								substr( strtolower($property['EPC']['Image']['Url']), 0, 4 ) == 'http'
							)
						)
	            		{
							// This is a URL
							$url = $property['EPC']['Image']['Url'];
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

							    $exploded_filename = explode(".", $filename);
							    $ext = 'jpg';
							    if (strlen($exploded_filename[count($exploded_filename)-1]) == 3)
							    {
							    	$ext = $exploded_filename[count($exploded_filename)-1];
							    }
							    $name = $property['RoleId'] . '_' . $document['Id'] . '.' . $ext;

							    $file_array = array(
							        'name' => $name,
							        'tmp_name' => $tmp
							    );

							    // Check for download errors
							    if ( is_wp_error( $tmp ) ) 
							    {
							        @unlink( $file_array[ 'tmp_name' ] );

							        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['RoleId'] );
							    }
							    else
							    {
								    $id = media_handle_sideload( $file_array, $post_id, $description );
								    
								    // Check for handle sideload errors.
								    if ( is_wp_error( $id ) ) 
								    {
								        @unlink( $file_array['tmp_name'] );
								        
								        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['RoleId'] );
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

						if ( isset($property['Documents']) && !empty($property['Documents']) )
						{
							foreach ( $property['Documents'] as $document )
							{
								if ( 
									isset($document['Url']) && $document['Url'] != ''
									&&
									(
										substr( strtolower($document['Url']), 0, 2 ) == '//' || 
										substr( strtolower($document['Url']), 0, 4 ) == 'http'
									)
									&&
									isset($document['DocumentType']['SystemName']) && $document['DocumentType']['SystemName'] == 'Document'
			                        &&
			                        isset($document['DocumentSubType']['SystemName']) && $document['DocumentSubType']['SystemName'] == 'EPC'
								)
								{
									// This is a URL
									$url = $document['Url'];
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

									    $exploded_filename = explode(".", $filename);
									    $ext = 'pdf';
									    if (strlen($exploded_filename[count($exploded_filename)-1]) == 3)
									    {
									    	$ext = $exploded_filename[count($exploded_filename)-1];
									    }
									    $name = $property['RoleId'] . '_' . $document['Id'] . '.' . $ext;

									    $file_array = array(
									        'name' => $name,
									        'tmp_name' => $tmp
									    );

									    // Check for download errors
									    if ( is_wp_error( $tmp ) ) 
									    {
									        @unlink( $file_array[ 'tmp_name' ] );

									        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['RoleId'] );
									    }
									    else
									    {
										    $id = media_handle_sideload( $file_array, $post_id, $description );
										    
										    // Check for handle sideload errors.
										    if ( is_wp_error( $id ) ) 
										    {
										        @unlink( $file_array['tmp_name'] );
										        
										        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['RoleId'] );
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

						$this->add_log( 'Imported ' . count($media_ids) . ' epcs (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property['RoleId'] );
					}

					// Media - Virtual Tours
					$virtual_tours = array();
					if ( isset($property['Documents']) && !empty($property['Documents']) )
		            {
		                foreach ( $property['Documents'] as $document )
		                {
		                    if ( 
		                        isset($document['Url']) && $document['Url'] != ''
		                        &&
		                        (
		                            substr( strtolower($document['Url']), 0, 2 ) == '//' || 
		                            substr( strtolower($document['Url']), 0, 4 ) == 'http'
		                        )
		                        &&
		                        ( isset($document['DocumentType']['SystemName']) && ( $document['DocumentType']['SystemName'] == 'Link' || $document['DocumentType']['SystemName'] == 'Video' ) )
		                        &&
		                        isset($document['DocumentSubType']['SystemName']) && $document['DocumentSubType']['SystemName'] == 'VirtualTour'
		                    )
		                    {
		                        $virtual_tours[] = $document['Url'];
		                    }
		                }
	                }

	                update_post_meta( $post_id, '_virtual_tours', count($virtual_tours) );
	                foreach ( $virtual_tours as $i => $virtual_tour )
	                {
	                	update_post_meta( $post_id, '_virtual_tour_' . $i, (string)$virtual_tour );
	                }

					$this->add_log( 'Imported ' . count($virtual_tours) . ' virtual tours', $property['RoleId'] );

					do_action( "propertyhive_property_imported_dezrez_json", $post_id, $property );

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
								$this->add_log( 'New meta data for ' . trim($key, '_') . ': ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), $property['RoleId'] );
							}
							elseif ( $metadata_before[$key] != $metadata_after[$key] )
							{
								$this->add_log( 'Updated ' . trim($key, '_') . '. Before: ' . ( ( is_array($metadata_before[$key]) ) ? implode(", ", $metadata_before[$key]) : $metadata_before[$key] ) . ', After: ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), $property['RoleId'] );
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
								$this->add_log( 'New taxonomy data for ' . $taxonomy_name . ': ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), $property['RoleId'] );
							}
							elseif ( $taxonomy_terms_before[$taxonomy_name] != $taxonomy_terms_after[$taxonomy_name] )
							{
								$this->add_log( 'Updated ' . $taxonomy_name . '. Before: ' . ( ( is_array($taxonomy_terms_before[$taxonomy_name]) ) ? implode(", ", $taxonomy_terms_before[$taxonomy_name]) : $taxonomy_terms_before[$taxonomy_name] ) . ', After: ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), $property['RoleId'] );
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

			}

		} // end foreach property

		do_action( "propertyhive_post_import_properties_dezrez_json" );

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
				$import_refs[] = $property['RoleId'];
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

					do_action( "propertyhive_property_removed_dezrez_json", $post->ID );
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
		if ( 
			( $import_id == '' && time() > PH_PROPERTYIMPORT_DEPARTMENT_AVAILABILITY_UPDATE ) || 
			( $import_id != '' && $import_id > PH_PROPERTYIMPORT_DEPARTMENT_AVAILABILITY_UPDATE )
		)
		{
			if ( get_option( 'propertyhive_active_departments_sales' ) == 'yes' )
			{
				$mapping_values = $this->get_dezrez_mapping_values('sales_availability');
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
				$mapping_values = $this->get_dezrez_mapping_values('lettings_availability');
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
			$mapping_values = $this->get_dezrez_mapping_values('availability');
			if ( is_array($mapping_values) && !empty($mapping_values) )
			{
				foreach ($mapping_values as $mapping_value => $text_value)
				{
					$this->mappings['availability'][$mapping_value] = '';
				}
			}
		}

		$mapping_values = $this->get_dezrez_mapping_values('property_type');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['property_type'][$mapping_value] = '';
			}
		}

		$mapping_values = $this->get_dezrez_mapping_values('price_qualifier');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['price_qualifier'][$mapping_value] = '';
			}
		}

		$mapping_values = $this->get_dezrez_mapping_values('tenure');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['tenure'][$mapping_value] = '';
			}
		}

		$mapping_values = $this->get_dezrez_mapping_values('furnished');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['furnished'][$mapping_value] = '';
			}
		}

		$mapping_values = $this->get_dezrez_mapping_values('office');
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
		return $this->get_dezrez_mapping_values($custom_field);
	}

	public function get_dezrez_mapping_values($custom_field) 
	{
        if ($custom_field == 'availability')
        {
            return array(
            	'Reduced' => 'Reduced',
                'OnMarket' => 'OnMarket',
                'UnderOffer' => 'UnderOffer',
                'OfferAccepted' => 'OfferAccepted',
            );
        }
        if ($custom_field == 'sales_availability')
        {
            return array(
            	'Reduced' => 'Reduced',
                'OnMarket' => 'OnMarket',
                'UnderOffer' => 'UnderOffer',
                'OfferAccepted' => 'OfferAccepted',
            );
        }
        if ($custom_field == 'lettings_availability')
        {
            return array(
            	'Reduced' => 'Reduced',
                'OnMarket' => 'OnMarket',
                'UnderOffer' => 'UnderOffer',
                'OfferAccepted' => 'OfferAccepted',
            );
        }
        if ($custom_field == 'property_type')
        {
        	return array(
                'TerracedHouse' => 'TerracedHouse',
				'EndTerraceHouse' => 'EndTerraceHouse',
				'MidTerraceHouse' => 'MidTerraceHouse',
				'SemiDetachedHouse' => 'SemiDetachedHouse',
				'DetachedHouse' => 'DetachedHouse',
				'RemoteDetachedHouse' => 'RemoteDetachedHouse',
				'EndLinkHouse' => 'EndLinkHouse',
				'MidLinkHouse' => 'MidLinkHouse',
				'Flat' => 'Flat',
				'Apartment' => 'Apartment',
				'TerracedBungalow' => 'TerracedBungalow',
				'EndTerraceBungalow' => 'EndTerraceBungalow',
				'MidTerraceBungalow' => 'MidTerraceBungalow',
				'SemiDetachedBungalow' => 'SemiDetachedBungalow',
				'DetachedBungalow' => 'DetachedBungalow',
				'RemoteDetachedBungalow' => 'RemoteDetachedBungalow',
				'EndLinkBungalow' => 'EndLinkBungalow',
				'MidLinkBungalow' => 'MidLinkBungalow',
				'Cottage' => 'Cottage',
				'TerracedCottage' => 'TerracedCottage',
				'EndTerraceCottage' => 'EndTerraceCottage',
				'MidTerraceCottage' => 'MidTerraceCottage',
				'SemiDetachedCottage' => 'SemiDetachedCottage',
				'DetachedCottage' => 'DetachedCottage',
				'RemoteDetachedCottage' => 'RemoteDetachedCottage',
				'TerracedTownHouse' => 'TerracedTownHouse',
				'EndTerraceTownHouse' => 'EndTerraceTownHouse',
				'MidTerraceTownHouse' => 'MidTerraceTownHouse',
				'SemiDetachedTownHouse' => 'SemiDetachedTownHouse',
				'DetachedTownHouse' => 'DetachedTownHouse',
				'DetachedCountryHouse' => 'DetachedCountryHouse',
				'NorthWingCountryHouse' => 'NorthWingCountryHouse',
				'SouthWingCountryHouse' => 'SouthWingCountryHouse',
				'EastWingCountryHouse' => 'EastWingCountryHouse',
				'WestWingCountryHouse' => 'WestWingCountryHouse',
				'TerracedChalet' => 'TerracedChalet',
				'EndTerraceChalet' => 'EndTerraceChalet',
				'MidTerraceChalet' => 'MidTerraceChalet',
				'SemiDetachedChalet' => 'SemiDetachedChalet',
				'DetachedChalet' => 'DetachedChalet',
				'DetachedBarnConversion' => 'DetachedBarnConversion',
				'RemoteDetachedBarnConversion' => 'RemoteDetachedBarnConversion',
				'MewsStyleBarnConversion' => 'MewsStyleBarnConversion',
				'GroundFloorPurposeBuiltFlat' => 'GroundFloorPurposeBuiltFlat',
				'FirstFloorPurposeBuiltFlat' => 'FirstFloorPurposeBuiltFlat',
				'GroundFloorConvertedFlat' => 'GroundFloorConvertedFlat',
				'FirstFloorConvertedFlat' => 'FirstFloorConvertedFlat',
				'SecondAndFloorConvertedFlat' => 'SecondAndFloorConvertedFlat',
				'GroundAndFirstFloorMaisonette' => 'GroundAndFirstFloorMaisonette',
				'FirstandSecondFloorMaisonette' => 'FirstandSecondFloorMaisonette',
				'PenthouseApartment' => 'PenthouseApartment',
				'DuplexApartment' => 'DuplexApartment',
				'Mansion' => 'Mansion',
				'QType' => 'QType',
				'TType' => 'TType',
				'Cluster' => 'Cluster',
				'BuildingPlot' => 'BuildingPlot',
				'ApartmentLowDensity' => 'ApartmentLowDensity',
				'ApartmentStudio' => 'ApartmentStudio',
				'Business' => 'Business',
				'CornerTownhouse' => 'CornerTownhouse',
				'VillaDetached' => 'VillaDetached',
				'VillaLinkdetached' => 'VillaLinkdetached',
				'VillaSemidetached' => 'VillaSemidetached',
				'VillageHouse' => 'VillageHouse',
				'LinkDetached' => 'LinkDetached',
				'Studio' => 'Studio',
				'Maisonette' => 'Maisonette',
				'Shell' => 'Shell',
				'Commercial' => 'Commercial',
				'RetirementFlat' => 'RetirementFlat',
				'Bedsit' => 'Bedsit',
				'ParkHome' => 'ParkHome',
				'ParkHomeMobileHome' => 'ParkHomeMobileHome',
				'CommercialLand' => 'CommercialLand',
				'Land' => 'Land',
				'FarmLand' => 'FarmLand',
            );
        }
        if ($custom_field == 'price_qualifier')
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
        }
        if ($custom_field == 'tenure')
        {
            return array(
            	'Leasehold' => 'Leasehold',
            	'Freehold' => 'Freehold',
            	'NotApplicable' => 'NotApplicable',
            	'FreeholdToBeConfirmed' => 'FreeholdToBeConfirmed',
            	'LeaseholdToBeConfirmed' => 'LeaseholdToBeConfirmed',
            	'ToBeAdvised' => 'ToBeAdvised',
            	'ShareofLeasehold' => 'ShareofLeasehold',
            	'ShareofFreehold' => 'ShareofFreehold',
            	'FlyingFreehold' => 'FlyingFreehold',
            	'LeaseholdShareofFreehold' => 'LeaseholdShareofFreehold',
            );
        }
        if ($custom_field == 'furnished')
        {
            return array(
            	/*'Landlord Flexible' => 'Landlord Flexible',
                'Furnished' => 'Furnished',
                'Part Furnished' => 'Part Furnished',
                'Un-Furnished' => 'Un-Furnished',*/
            );
        }
    }
}

}