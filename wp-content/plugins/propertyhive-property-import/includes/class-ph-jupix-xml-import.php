<?php
/**
 * Class for managing the import process of a Jupix XML file
 *
 * @package WordPress
 */
if ( class_exists( 'PH_Property_Import_Process' ) ) {

class PH_Jupix_XML_Import extends PH_Property_Import_Process {

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
            
			foreach ($xml->property as $property)
			{
            	$department = (string)$property->department;
                
                if ($department == 'Sales' || $department == 'Lettings' || $department == 'Commercial' || $department == 'Agricultural' )
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

        // Is the property portal add on activated
        if (class_exists('PH_Property_Portal'))
        {
        	$branch_mappings = array();
        	$branch_mappings['sales'] = array();
        	$branch_mappings['lettings'] = array();
        	$branch_mappings['commercial'] = array();

        	$args = array(
	            'post_type' => 'agent',
	            'nopaging' => true
	        );
	        $agent_query = new WP_Query($args);
	        
	        if ($agent_query->have_posts())
	        {
	            while ($agent_query->have_posts())
	            {
	                $agent_query->the_post();

	                $agent_id = get_the_ID();

	                $args = array(
			            'post_type' => 'branch',
			            'nopaging' => true,
			            'meta_query' => array(
			            	array(
			            		'key' => '_agent_id',
			            		'value' => $agent_id
			            	)
			            )
			        );
			        $branch_query = new WP_Query($args);
			        
			        if ($branch_query->have_posts())
			        {
			            while ($branch_query->have_posts())
			            {
			            	$branch_query->the_post();

			            	if ( get_post_meta( get_the_ID(), '_branch_code_sales', true ) != '' )
			            	{
				            	$branch_mappings['sales'][get_post_meta( get_the_ID(), '_branch_code_sales', true )] = $agent_id . '|' . get_the_ID();
				            }
				            if ( get_post_meta( get_the_ID(), '_branch_code_lettings', true ) != '' )
			            	{
				            	$branch_mappings['lettings'][get_post_meta( get_the_ID(), '_branch_code_lettings', true )] = $agent_id . '|' . get_the_ID();
				            }
				            if ( get_post_meta( get_the_ID(), '_branch_code_commercial', true ) != '' )
			            	{
				            	$branch_mappings['commercial'][get_post_meta( get_the_ID(), '_branch_code_commercial', true )] = $agent_id . '|' . get_the_ID();
				            }
			            }
			        }
			        $branch_query->reset_postdata();
	            }
	        }
	        $agent_query->reset_postdata();
        }

        do_action( "propertyhive_pre_import_properties_jupix_xml", $this->properties );
        $this->properties = apply_filters( "propertyhive_jupix_xml_properties_due_import", $this->properties );

		$this->add_log( 'Beginning to loop through ' . count($this->properties) . ' properties' );

		$property_row = 1;
		foreach ( $this->properties as $property )
		{
			$this->add_log( 'Importing property ' . $property_row .' with reference ' . (string)$property->propertyID, (string)$property->propertyID );

			$inserted_updated = false;
			$new_property = false;

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
				    	'post_title'     => wp_strip_all_tags( (string)$property->displayAddress ),
				    	'post_excerpt'   => (string)$property->mainSummary,
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
					'post_excerpt'   => (string)$property->mainSummary,
					'post_content' 	 => '',
					'post_title'     => wp_strip_all_tags( (string)$property->displayAddress ),
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
					((string)$property->displayAddress != '' || (string)$property->mainSummary != '')
				)
				{
					$my_post = array(
				    	'ID'          	 => $post_id,
				    	'post_title'     => htmlentities(mb_convert_encoding(wp_strip_all_tags( (string)$property->displayAddress ), 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
				    	'post_excerpt'   => htmlentities(mb_convert_encoding((string)$property->mainSummary, 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
				    	'post_content' 	 => '',
				    	'post_name' 	 => sanitize_title((string)$property->displayAddress),
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

				$previous_jupix_xml_update_date = get_post_meta( $post_id, '_jupix_xml_update_date_' . $import_id, TRUE);

				$skip_property = false;
				if (
					( isset($options['only_updated']) && $options['only_updated'] == 'yes' )
				)
				{
					if (
						$previous_jupix_xml_update_date == (string)$property->dateLastModified . ' ' . (string)$property->timeLastModified
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
								if ( trim($property->addressName) != '' ) { $address_to_geocode[] = (string)$property->addressName; }
								if ( trim($property->addressNumber) != '' ) { $address_to_geocode[] = (string)$property->addressNumber; }
								if ( trim($property->addressStreet) != '' ) { $address_to_geocode[] = (string)$property->addressStreet; }
								if ( trim($property->address2) != '' ) { $address_to_geocode[] = (string)$property->address2; }
								if ( trim($property->address3) != '' ) { $address_to_geocode[] = (string)$property->address3; }
								if ( trim($property->address4) != '' ) { $address_to_geocode[] = (string)$property->address4; }
								if ( trim($property->addressPostcode) != '' ) { $address_to_geocode[] = (string)$property->addressPostcode; }

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
				}

				if ( !$skip_property )
				{
					update_post_meta( $post_id, $imported_ref_key, (string)$property->propertyID );

					// Address
					update_post_meta( $post_id, '_reference_number', (string)$property->referenceNumber );
					update_post_meta( $post_id, '_address_name_number', trim( ( ( isset($property->addressName) ) ? (string)$property->addressName : '' ) . ' ' . ( ( isset($property->addressNumber) ) ? (string)$property->addressNumber : '' ) ) );
					update_post_meta( $post_id, '_address_street', ( ( isset($property->addressStreet) ) ? (string)$property->addressStreet : '' ) );
					update_post_meta( $post_id, '_address_two', ( ( isset($property->address2) ) ? (string)$property->address2 : '' ) );
					update_post_meta( $post_id, '_address_three', ( ( isset($property->address3) ) ? (string)$property->address3 : '' ) );
					update_post_meta( $post_id, '_address_four', ( ( isset($property->address4) ) ? (string)$property->address4 : '' ) );
					update_post_meta( $post_id, '_address_postcode', ( ( isset($property->addressPostcode) ) ? (string)$property->addressPostcode : '' ) );

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

					if ( isset($_POST['mapped_location']) )
					{
						$mapping = $_POST['mapped_location'];
					}
					else
					{
						$mapping = isset($options['mappings']['location']) ? $options['mappings']['location'] : array();
					}

	        		wp_delete_object_term_relationships( $post_id, 'location' );
	        		$found_location = false;
					if ( !empty($mapping) && isset($property->regionID) )
					{
						$region_id = ( ( (string)$property->regionID != '' ) ? (string)$property->regionID : '0' );
						if ( isset($mapping[$region_id]) && $mapping[$region_id] != '' )
						{
		                	wp_set_post_terms( $post_id, $mapping[$region_id], 'location' );
		                	$found_location = true;
						}
		            }

		            if ( !$found_location )
		            {
		            	// We didn't find a location by doing mapping. Let's just look at address fields to see if we find a match
		            	$address_fields_to_check = apply_filters( 'propertyhive_jupix_xml_address_fields_to_check', array('address2', 'address3', 'address4') );
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
		            }

					// Owner
					add_post_meta( $post_id, '_owner_contact_id', '', true );

					// Record Details
					add_post_meta( $post_id, '_negotiator_id', get_current_user_id(), true );
						
					$office_id = $primary_office_id;
					if ( isset($_POST['mapped_office'][(string)$property->branchID]) && $_POST['mapped_office'][(string)$property->branchID] != '' )
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
					}
					update_post_meta( $post_id, '_office_id', $office_id );

					// Residential Details
					$department = 'residential-sales';
					if ( (string)$property->department == 'Lettings' )
					{
						$department = 'residential-lettings';
					}
					elseif ( (string)$property->department == 'Commercial' )
					{
						$department = 'commercial';
					}

					// Is the property portal add on activated
					if (class_exists('PH_Property_Portal'))
	        		{
	        			if ( 
	        				isset($branch_mappings[str_replace("residential-", "", $department)][(string)$property->branchID]) &&
	        				$branch_mappings[str_replace("residential-", "", $department)][(string)$property->branchID] != ''
	        			)
	        			{
	        				$explode_agent_branch = explode("|", $branch_mappings[str_replace("residential-", "", $department)][(string)$property->branchID]);
	        				update_post_meta( $post_id, '_agent_id', $explode_agent_branch[0] );
	        				update_post_meta( $post_id, '_branch_id', $explode_agent_branch[1] );

	        				$this->branch_ids_processed[] = $explode_agent_branch[1];
	        			}
	        			else
	        			{
	        				update_post_meta( $post_id, '_agent_id', '' );
	        				update_post_meta( $post_id, '_branch_id', '' );
	        			}
	        		}

					update_post_meta( $post_id, '_department', $department );
					update_post_meta( $post_id, '_bedrooms', ( ( isset($property->propertyBedrooms) ) ? (string)$property->propertyBedrooms : '' ) );
					update_post_meta( $post_id, '_bathrooms', ( ( isset($property->propertyBathrooms) ) ? (string)$property->propertyBathrooms : '' ) );
					update_post_meta( $post_id, '_reception_rooms', ( ( isset($property->propertyReceptionRooms) ) ? (string)$property->propertyReceptionRooms : '' ) );

					$prefix = '';
					if ( (string)$property->department == 'Commercial' )
					{
						$prefix = 'commercial_';
					}
					if ( isset($_POST['mapped_' . $prefix . 'property_type']) )
					{
						$mapping = $_POST['mapped_' . $prefix . 'property_type'];
					}
					else
					{
						$mapping = isset($options['mappings'][$prefix . 'property_type']) ? $options['mappings'][$prefix . 'property_type'] : array();
					}

					wp_delete_object_term_relationships( $post_id, $prefix . 'property_type' );

					if ( (string)$property->department == 'Agricultural' )
					{
						// Find type with name 'Land' or 'Agricultural'
						// In future we would add mapping for this but scenario is so rare
						$term = get_term_by('name', 'Land', 'property_type');
						if ( $term !== FALSE )
						{
							wp_set_post_terms( $post_id, (int)$term->term_id, 'property_type' );
						}
						else
						{
							$term = get_term_by('name', 'Agricultural', 'property_type');
							if ( $term !== FALSE )
							{
								wp_set_post_terms( $post_id, (int)$term->term_id, 'property_type' );
							}
						}
					}
					elseif ( (string)$property->department == 'Commercial' )
					{
						
						if ( isset($property->propertyTypes) && isset($property->propertyTypes->propertyType) )
						{
							$property_types = $property->propertyTypes->propertyType;
							if ( !is_array($property_types) )
							{
								$property_types = array($property_types);
							}
							
							foreach ( $property->propertyTypes->propertyType as $propertyType )
							{
								$propertyType = (string)$propertyType;
								if ( !empty($mapping) && isset($mapping[$propertyType]) )
								{
									wp_set_post_terms( $post_id, $mapping[$propertyType], $prefix . 'property_type', TRUE );
								}
								else
								{
									$this->add_log( 'Property received with a type (' . $propertyType . ') that is not mapped', (string)$property->propertyID );

									$options = $this->add_missing_mapping( $mapping, $prefix . 'property_type', $propertyType, $import_id );
								}
							}
						}
					}
					else
					{
						if ( isset($property->propertyType) && isset($property->propertyStyle) )
						{
							if ( !empty($mapping) && isset($mapping[(string)$property->propertyType . ' - ' . (string)$property->propertyStyle]) )
							{
								wp_set_post_terms( $post_id, $mapping[(string)$property->propertyType . ' - ' . (string)$property->propertyStyle], $prefix . 'property_type' );
							}
							else
							{
								$this->add_log( 'Property received with a type (' . (string)$property->propertyType . ' - ' . (string)$property->propertyStyle . ') that is not mapped', (string)$property->propertyID );

								$options = $this->add_missing_mapping( $mapping, $prefix . 'property_type', (string)$property->propertyType . ' - ' . (string)$property->propertyStyle, $import_id );
							}
						}
					}

					// Residential Sales Details
					if ( (string)$property->department == 'Sales' || (string)$property->department == 'Agricultural' )
					{
						// Clean price
						$price = '';
						if ( (string)$property->department == 'Agricultural' )
						{
							$price = round(preg_replace("/[^0-9.]/", '', (string)$property->priceTo));
						}
						else
						{
							$price = round(preg_replace("/[^0-9.]/", '', (string)$property->price));
						}
						update_post_meta( $post_id, '_price', $price );
						update_post_meta( $post_id, '_price_actual', $price );
						update_post_meta( $post_id, '_poa', ( ( isset($property->forSalePOA) && $property->forSalePOA == '1' ) ? 'yes' : '') );

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
						if ( !empty($mapping) && isset($property->priceQualifier) && isset($mapping[(string)$property->priceQualifier]) )
						{
			                wp_set_post_terms( $post_id, $mapping[(string)$property->priceQualifier], 'price_qualifier' );
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
						if ( !empty($mapping) && isset($property->propertyTenure) && isset($mapping[(string)$property->propertyTenure]) )
						{
				            wp_set_post_terms( $post_id, $mapping[(string)$property->propertyTenure], 'tenure' );
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
						if ( !empty($mapping) && isset($property->saleBy) && isset($mapping[(string)$property->saleBy]) )
						{
				            wp_set_post_terms( $post_id, $mapping[(string)$property->saleBy], 'sale_by' );
			            }
					}
					elseif ( (string)$property->department == 'Lettings' )
					{
						// Clean price
						$price = round(preg_replace("/[^0-9.]/", '', (string)$property->rent));

						update_post_meta( $post_id, '_rent', $price );

						$rent_frequency = 'pcm';
						$price_actual = $price;
						switch ((string)$property->rentFrequency)
						{
							case "1": { $rent_frequency = 'pcm'; $price_actual = $price; break; }
							case "2": { $rent_frequency = 'pw'; $price_actual = ($price * 52) / 12; break; }
							case "3": { $rent_frequency = 'pa'; $price_actual = $price / 12; break; }
						}
						update_post_meta( $post_id, '_rent_frequency', $rent_frequency );
						update_post_meta( $post_id, '_price_actual', $price_actual );
						
						update_post_meta( $post_id, '_poa', ( ( isset($property->toLetPOA) && $property->toLetPOA == '1' ) ? 'yes' : '') );

						update_post_meta( $post_id, '_deposit', '' );
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
					}
					elseif ( (string)$property->department == 'Commercial' )
					{
						update_post_meta( $post_id, '_for_sale', '' );
	            		update_post_meta( $post_id, '_to_rent', '' );

	            		if ( (string)$property->forSale == '1' )
		                {
		                    update_post_meta( $post_id, '_for_sale', 'yes' );

		                    update_post_meta( $post_id, '_commercial_price_currency', 'GBP' );

		                    $price = preg_replace("/[^0-9.]/", '', (string)$property->priceFrom);
		                    if ( $price == '' || $price == '0' )
		                    {
		                        $price = preg_replace("/[^0-9.]/", '', (string)$property->priceTo);
		                    }
		                    update_post_meta( $post_id, '_price_from', $price );

		                    $price = preg_replace("/[^0-9.]/", '', (string)$property->priceTo);
		                    if ( $price == '' || $price == '0' )
		                    {
		                        $price = preg_replace("/[^0-9.]/", '', (string)$property->priceFrom);
		                    }
		                    update_post_meta( $post_id, '_price_to', $price );

		                    update_post_meta( $post_id, '_price_units', '' );

		                    update_post_meta( $post_id, '_price_poa', ( (string)$property->forSalePOA == '1' ? 'yes' : '' ) );

		                    // Tenure
				            if ( isset($_POST['mapped_tenure']) )
							{
								$mapping = $_POST['mapped_tenure'];
							}
							else
							{
								$mapping = isset($options['mappings']['tenure']) ? $options['mappings']['tenure'] : array();
							}

				            wp_delete_object_term_relationships( $post_id, 'commercial_tenure' );
							if ( !empty($mapping) && isset($property->propertyTenure) && isset($mapping[(string)$property->propertyTenure]) )
							{
					            wp_set_post_terms( $post_id, $mapping[(string)$property->propertyTenure], 'commercial_tenure' );
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
							if ( !empty($mapping) && isset($property->saleBy) && isset($mapping[(string)$property->saleBy]) )
							{
					            wp_set_post_terms( $post_id, $mapping[(string)$property->saleBy], 'sale_by' );
				            }
		                }

		                if ( (string)$property->toLet == '1' )
		                {
		                    update_post_meta( $post_id, '_to_rent', 'yes' );

		                    update_post_meta( $post_id, '_commercial_rent_currency', 'GBP' );

		                    $rent = preg_replace("/[^0-9.]/", '', (string)$property->rentFrom);
		                    if ( $rent == '' || $rent == '0' )
		                    {
		                        $rent = preg_replace("/[^0-9.]/", '', (string)$property->rentTo);
		                    }
		                    update_post_meta( $post_id, '_rent_from', $rent );

		                    $rent = preg_replace("/[^0-9.]/", '', (string)$property->rentTo);
		                    if ( $rent == '' || $rent == '0' )
		                    {
		                        $rent = preg_replace("/[^0-9.]/", '', (string)$property->rentFrom);
		                    }
		                    update_post_meta( $post_id, '_rent_to', $rent );

		                    update_post_meta( $post_id, '_rent_units', (string)$property->rentFrequency);

		                    update_post_meta( $post_id, '_rent_poa', ( (string)$property->toLetPOA == '1' ? 'yes' : '' ) );
		                }

		                // Store price in common currency (GBP) used for ordering
			            $ph_countries = new PH_Countries();
			            $ph_countries->update_property_price_actual( $post_id );

			            $size = preg_replace("/[^0-9.]/", '', (string)$property->floorAreaFrom);
			            if ( $size == '' )
			            {
			                $size = preg_replace("/[^0-9.]/", '', (string)$property->floorAreaTo);
			            }
			            if ( (string)$property->floorAreaFrom == '0.00' && (string)$property->floorAreaTo == '0.00' )
			            {
			            	$size = '';
			            }
			            update_post_meta( $post_id, '_floor_area_from', $size );

			            update_post_meta( $post_id, '_floor_area_from_sqft', convert_size_to_sqft( $size, str_replace(" ", "", (string)$property->floorAreaUnits ) ) );

			            $size = preg_replace("/[^0-9.]/", '', (string)$property->floorAreaTo);
			            if ( $size == '' )
			            {
			                $size = preg_replace("/[^0-9.]/", '', (string)$property->floorAreaFrom);
			            }
			            if ( (string)$property->floorAreaFrom == '0.00' && (string)$property->floorAreaTo == '0.00' )
			            {
			            	$size = '';
			            }
			            update_post_meta( $post_id, '_floor_area_to', $size );

			            update_post_meta( $post_id, '_floor_area_to_sqft', convert_size_to_sqft( $size, str_replace(" ", "", (string)$property->floorAreaUnits ) ) );

			            update_post_meta( $post_id, '_floor_area_units', str_replace(" ", "", (string)$property->floorAreaUnits ) );

			            $size = preg_replace("/[^0-9.]/", '', (string)$property->siteArea);
			            if ( (string)$property->siteArea == '0.00' )
			            {
			            	$size = '';
			            }

			            update_post_meta( $post_id, '_site_area_from', $size );

			            update_post_meta( $post_id, '_site_area_from_sqft', convert_size_to_sqft( $size, str_replace(" ", "", (string)$property->siteAreaUnits ) ) );

			            update_post_meta( $post_id, '_site_area_to', $size );

			            update_post_meta( $post_id, '_site_area_to_sqft', convert_size_to_sqft( $size, str_replace(" ", "", (string)$property->siteAreaUnits ) ) );

			            update_post_meta( $post_id, '_site_area_units', str_replace(" ", "", (string)$property->siteAreaUnits ) );
					}

					// Marketing
					update_post_meta( $post_id, '_on_market', 'yes' );
					update_post_meta( $post_id, '_featured', ( isset($property->featuredProperty) && (string)$property->featuredProperty == '1' ) ? 'yes' : '' );

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
						if ( (string)$property->department == 'Commercial' )
						{
							$prefix = 'commercial_';
						}
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
					if ( !empty($mapping) && isset($property->availability) && isset($mapping[(string)$property->availability]) )
					{
		                wp_set_post_terms( $post_id, $mapping[(string)$property->availability], 'availability' );
		            }

		            // Features
					$features = array();
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
			        }	     

			        // Rooms / Descriptions
			        // For now put the whole description in one room / description
			        if ( (string)$property->department == 'Commercial' )
					{
						update_post_meta( $post_id, '_descriptions', '1' );
						update_post_meta( $post_id, '_description_name_0', '' );
			            update_post_meta( $post_id, '_description_0', str_replace(array("\r\n", "\n"), "", (string)$property->fullDescription) );
					}
					else
					{
						update_post_meta( $post_id, '_rooms', '1' );
						update_post_meta( $post_id, '_room_name_0', '' );
			            update_post_meta( $post_id, '_room_dimensions_0', '' );
			            update_post_meta( $post_id, '_room_description_0', str_replace(array("\r\n", "\n"), "", (string)$property->fullDescription) );
			        }

		            // Media - Images
		            if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
	    			{
	    				$media_urls = array();
	    				if (isset($property->images) && !empty($property->images))
		                {
		                    foreach ($property->images as $images)
		                    {
		                        if (!empty($images->image))
		                        {
		                            foreach ($images->image as $image)
		                            {
										if ( 
											substr( strtolower((string)$image), 0, 2 ) == '//' || 
											substr( strtolower((string)$image), 0, 4 ) == 'http'
										)
										{
											// This is a URL
											$url = str_replace("http://", "https://", (string)$image);
											$url = apply_filters('propertyhive_jupix_image_url', $url);

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
						if (isset($property->images) && !empty($property->images))
		                {
		                    foreach ($property->images as $images)
		                    {
		                        if (!empty($images->image))
		                        {
		                            foreach ($images->image as $image)
		                            {
										if ( 
											substr( strtolower((string)$image), 0, 2 ) == '//' || 
											substr( strtolower((string)$image), 0, 4 ) == 'http'
										)
										{
											// This is a URL
											$url = apply_filters('propertyhive_jupix_image_url', (string)$image);
											$description = '';

											$media_attributes = $image->attributes();
											$modified = (string)$media_attributes['modified'];

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
											substr( strtolower((string)$floorplan), 0, 2 ) == '//' || 
											substr( strtolower((string)$floorplan), 0, 4 ) == 'http'
										)
										{
											// This is a URL
											$url = str_replace("http://", "https://", (string)$floorplan);
											$url = apply_filters('propertyhive_jupix_floorplan_url', $url);

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
											substr( strtolower((string)$floorplan), 0, 2 ) == '//' || 
											substr( strtolower((string)$floorplan), 0, 4 ) == 'http'
										)
										{
											// This is a URL
											$url = apply_filters('propertyhive_jupix_floorplan_url', (string)$floorplan);
											$description = '';

											$media_attributes = $floorplan->attributes();
											$modified = (string)$media_attributes['modified'];
										    
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

					// Media - Brochures
					if ( get_option('propertyhive_brochures_stored_as', '') == 'urls' )
	    			{
	    				$media_urls = array();
	    				if (isset($property->brochures) && !empty($property->brochures))
		                {
		                    foreach ($property->brochures as $brochures)
		                    {
		                        if (!empty($brochures->brochure))
		                        {
		                            foreach ($brochures->brochure as $brochure)
		                            {
										if ( 
											substr( strtolower((string)$brochure), 0, 2 ) == '//' || 
											substr( strtolower((string)$brochure), 0, 4 ) == 'http'
										)
										{
											// This is a URL
											$url = str_replace("http://", "https://", (string)$brochure);

											$media_urls[] = array('url' => $url);
										}
									}
								}
							}
						}
						update_post_meta( $post_id, '_brochure_urls', $media_urls );

						$this->add_log( 'Imported ' . count($media_urls) . ' brochure URLs', (string)$property->propertyID );
	    			}
	    			else
	    			{
						$media_ids = array();
						$new = 0;
						$existing = 0;
						$deleted = 0;
						$previous_media_ids = get_post_meta( $post_id, '_brochures', TRUE );
						if (isset($property->brochures) && !empty($property->brochures))
		                {
		                    foreach ($property->brochures as $brochures)
		                    {
		                        if (!empty($brochures->brochure))
		                        {
		                            foreach ($brochures->brochure as $brochure)
		                            {
										if ( 
											substr( strtolower((string)$brochure), 0, 2 ) == '//' || 
											substr( strtolower((string)$brochure), 0, 4 ) == 'http'
										)
										{
											// This is a URL
											$url = (string)$brochure;
											$description = '';

											$media_attributes = $brochure->attributes();
											$modified = (string)$media_attributes['modified'];
										    
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

						$this->add_log( 'Imported ' . count($media_ids) . ' brochures (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->propertyID );
					}

					// Media - EPCs
					if ( get_option('propertyhive_epcs_stored_as', '') == 'urls' )
	    			{
	    				$media_urls = array();

	    				if (isset($property->epcGraphs) && !empty($property->epcGraphs))
		                {
		                    foreach ($property->epcGraphs as $epcGraphs)
		                    {
		                        if (!empty($epcGraphs->epcGraph))
		                        {
		                            foreach ($epcGraphs->epcGraph as $epcGraph)
		                            {
										if ( 
											substr( strtolower((string)$epcGraph), 0, 2 ) == '//' || 
											substr( strtolower((string)$epcGraph), 0, 4 ) == 'http'
										)
										{
											// This is a URL
											$url = str_replace("http://", "https://", (string)$epcGraph);

											$media_urls[] = array('url' => $url);
										}
									}
								}
							}
						}
						if (isset($property->epcFrontPages) && !empty($property->epcFrontPages))
		                {
		                    foreach ($property->epcFrontPages as $epcFrontPages)
		                    {
		                        if (!empty($epcFrontPages->epcFrontPage))
		                        {
		                            foreach ($epcFrontPages->epcFrontPage as $epcFrontPage)
		                            {
										if ( 
											substr( strtolower((string)$epcFrontPage), 0, 2 ) == '//' || 
											substr( strtolower((string)$epcFrontPage), 0, 4 ) == 'http'
										)
										{
											// This is a URL
											$url = str_replace("http://", "https://", (string)$epcFrontPage);

											$media_urls[] = array('url' => $url);
										}
									}
								}
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
						if (isset($property->epcGraphs) && !empty($property->epcGraphs))
		                {
		                    foreach ($property->epcGraphs as $epcGraphs)
		                    {
		                        if (!empty($epcGraphs->epcGraph))
		                        {
		                            foreach ($epcGraphs->epcGraph as $epcGraph)
		                            {
										if ( 
											substr( strtolower((string)$epcGraph), 0, 2 ) == '//' || 
											substr( strtolower((string)$epcGraph), 0, 4 ) == 'http'
										)
										{
											// This is a URL
											$url = (string)$epcGraph;
											$description = '';

											$media_attributes = $epcGraph->attributes();
											$modified = (string)$media_attributes['modified'];
										    
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
						if (isset($property->epcFrontPages) && !empty($property->epcFrontPages))
		                {
		                    foreach ($property->epcFrontPages as $epcFrontPages)
		                    {
		                        if (!empty($epcFrontPages->epcFrontPage))
		                        {
		                            foreach ($epcFrontPages->epcFrontPage as $epcFrontPage)
		                            {
										if ( 
											substr( strtolower((string)$epcFrontPage), 0, 2 ) == '//' || 
											substr( strtolower((string)$epcFrontPage), 0, 4 ) == 'http'
										)
										{
											// This is a URL
											$url = (string)$epcFrontPage;
											$description = '';

											$media_attributes = $epcFrontPage->attributes();
											$modified = (string)$media_attributes['modified'];
										    
											$filename = basename( $url );

											// Make sure it has an extension
											if ( strpos($filename, '.') === FALSE )
											{
												$filename .= '.pdf';
											}

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

					$this->add_log( 'Imported ' . count($virtual_tours) . ' virtual tours', (string)$property->propertyID );
				}
				else
				{
					$this->add_log( 'Skipping property as not been updated', (string)$property->propertyID );
				}
				
				update_post_meta( $post_id, '_jupix_xml_update_date_' . $import_id, (string)$property->dateLastModified . ' ' . (string)$property->timeLastModified );

				do_action( "propertyhive_property_imported_jupix_xml", $post_id, $property );

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

		do_action( "propertyhive_post_import_properties_jupix_xml" );

		$this->import_end();

		$this->add_log( 'Finished import' );
	}

	public function remove_old_properties( $import_id = '', $do_remove = true )
	{
		global $wpdb, $post;

		if (
			(class_exists('PH_Property_Portal') && !empty($this->branch_ids_processed))
			||
			!class_exists('PH_Property_Portal')
		)
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
				$import_refs[] = (string)$property->propertyID;
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

			// Is the property portal add on activated
			if (class_exists('PH_Property_Portal'))
			{
				$meta_query[] = array(
					'relation' => 'OR',
					array(
						'key'     => '_branch_id',
						'value'   => $this->branch_ids_processed,
						'compare' => 'IN',
					),
					array(
						'key'     => '_branch_id',
						'value'   => '',
						'compare' => '=',
					)
				);
			}

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

					do_action( "propertyhive_property_removed_jupix_xml", $post->ID );
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

		if ( get_option( 'propertyhive_active_departments_commercial', '' ) == 'yes' )
		{
			$mapping_values = $this->get_xml_mapping_values('commercial_availability');
			if ( is_array($mapping_values) && !empty($mapping_values) )
			{
				foreach ($mapping_values as $mapping_value => $text_value)
				{
					$this->mappings['commercial_availability'][$mapping_value] = '';
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

		$mapping_values = $this->get_xml_mapping_values('commercial_property_type');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['commercial_property_type'][$mapping_value] = '';
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

		$mapping_values = $this->get_xml_mapping_values('sale_by');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['sale_by'][$mapping_value] = '';
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

	public function get_mapping_values($custom_field, $import_id)
	{
		return $this->get_xml_mapping_values($custom_field);
	}

	public function get_xml_mapping_values($custom_field) 
	{
        if ($custom_field == 'availability')
        {
            return array(
                '1' => 'On Hold',
                '2' => 'For Sale / To Let',
                '3' => 'Under Offer / References Pending',
                '4' => 'Sold STC / Let Agreed',
                '5' => 'Sold / Let',
                '6' => 'Withdrawn',
                '7' => 'Withdrawn',
            );
        }
        if ($custom_field == 'sales_availability')
        {
            return array(
                '1' => 'On Hold',
                '2' => 'For Sale',
                '3' => 'Under Offer',
                '4' => 'Sold STC',
                '5' => 'Sold',
                '6' => 'Withdrawn',
                '7' => 'Withdrawn',
            );
        }
        if ($custom_field == 'lettings_availability')
        {
            return array(
                '1' => 'On Hold',
                '2' => 'To Let',
                '3' => 'References Pending',
                '4' => 'Let Agreed',
                '5' => 'Let',
                '6' => 'Withdrawn',
                '7' => 'Withdrawn',
            );
        }
        if ($custom_field == 'commercial_availability')
        {
            return array(
                '1' => 'On Hold',
                '2' => 'For Sale',
                '3' => 'To Let',
                '4' => 'For Sale / To Let',
                '5' => 'Under Offer',
                '6' => 'Sold STC',
                '7' => 'Exchanged',
                '8' => 'Completed',
                '9' => 'Let Agreed',
                '10' => 'Let',
                '11' => 'Withdrawn',
            );
        }
        if ($custom_field == 'property_type')
        {
        	return array(
                '1 - 1' => 'House - Barn Conversion',
                '1 - 2' => 'House - Cottage',
                '1 - 3' => 'House - Chalet',
                '1 - 4' => 'House - Detached House',
                '1 - 5' => 'House - Semi-Detached House',
                '1 - 28' => 'House - Link Detached',
                '1 - 6' => 'House - Farm House',
                '1 - 7' => 'House - Manor House',
                '1 - 8' => 'House - Mews',
                '1 - 9' => 'House - Mid Terraced House',
                '1 - 10' => 'House - End Terraced House',
                '1 - 11' => 'House - Town House',
                '1 - 12' => 'House - Villa',
                '1 - 29' => 'House - Shared House',
                '1 - 31' => 'House - Sheltered Housing',

                '2 - 13' => 'Flat - Apartment',
                '2 - 14' => 'Flat - Bedsit',
                '2 - 15' => 'Flat - Ground Floor Flat',
                '2 - 16' => 'Flat - Flat',
                '2 - 17' => 'Flat - Ground Floor Maisonette',
                '2 - 18' => 'Flat - Maisonette',
                '2 - 19' => 'Flat - Penthouse',
                '2 - 20' => 'Flat - Studio',
                '2 - 30' => 'Flat - Shared Flat',

                '3 - 21' => 'Bungalow - Detached Bungalow',
                '3 - 22' => 'Bungalow - Semi-Detached Bungalow',
                '3 - 34' => 'Bungalow - Mid Terraced Bungalow',
                '3 - 35' => 'Bungalow - End Terraced Bungalow',

                '4 - 23' => 'Other - Building Plot / Land',
                '4 - 24' => 'Other - Garage',
                '4 - 25' => 'Other - House Boat',
                '4 - 26' => 'Other - Mobile Home',
                '4 - 27' => 'Other - Parking',
                '4 - 32' => 'Other - Equestrian',
                '4 - 33' => 'Other - Unconverted Barn',
            );
        }
        if ($custom_field == 'commercial_property_type')
        {
        	return array(
                '1' => 'Offices',
                '2' => 'Serviced Offices',
                '3' => 'Business Park',
                '4' => 'Science / Tech / R and D',
                '5' => 'A1 - High Street',
                '6' => 'A1 - Centre',
                '7' => 'A1 - Out Of Town',
                '8' => 'A1 - Other',
                '9' => 'A2 - Financial Services',
                '10' => 'A3 - Restaurants / Cafes',
                '11' => 'A4 - Pubs / Bars / Clubs',
                '12' => 'A5 - Take Away',
                '13' => 'B1 - Light Industrial',
                '14' => 'B2 - Heavy Industrial',
                '15' => 'B8 - Warehouse / Distribution',
                '16' => 'Science / Tech / R and D',
                '17' => 'Other Industrial',
                '18' => 'Caravan Park',
                '19' => 'Cinema',
                '20' => 'Golf Property',
                '21' => 'Guest  House / Hotel',
                '22' => 'Leisure Park',
                '23' => 'Leisure Other',
                '24' => 'Day Nursery / Child Care',
                '25' => 'Nursing & Care Homes',
                '26' => 'Surgeries',
                '27' => 'Petrol Stations',
                '28' => 'Show Room',
                '29' => 'Garage',
                '30' => 'Industrial (land)',
                '31' => 'Office (land)',
                '32' => 'Residential (land)',
                '33' => 'Retail (land)',
                '34' => 'Leisure (land)',
                '35' => 'Commercial / Other (land)',
            );
        }
        if ($custom_field == 'price_qualifier')
        {
        	return array(
        		'1' => 'Asking Price Of',
        		'7' => 'Auction Guide Price',
        		'2' => 'Fixed Price',
        		'3' => 'From',
        		'4' => 'Guide Price',
        		'10' => 'Offers In Excess Of',
        		'5' => 'Offers In Region Of',
        		'11' => 'Offers Invited',
        		'6' => 'Offers Over',
        		'8' => 'Sale By Tender',
        		'9' => 'Shared Ownership',
        		'12' => 'Starting Bid',
        	);
        }
        if ($custom_field == 'tenure')
        {
            return array(
                '1' => 'Freehold',
                '2' => 'Leasehold',
                '3' => 'Commonhold',
                '4' => 'Share of Freehold',
                '5' => 'Flying Freehold',
                '6' => 'Share Transfer',
                '7' => 'Unknown',
            );
        }
        if ($custom_field == 'sale_by')
        {
            return array(
                '1' => 'Private Treaty',
                '2' => 'By Auction',
                '3' => 'Confidential',
                '4' => 'By Tender',
                '5' => 'Offers Invited',
            );
        }
        if ($custom_field == 'furnished')
        {
            return array(
            	'1' => 'Furnished',
            	'2' => 'Furnished Optional',
            	'3' => 'Part Furnished',
            	'4' => 'Unfurnished',
            );
        }
        if ($custom_field == 'location')
        {
            return array(
            	'0' => '(blank)'
            );
        }
    }

}

}