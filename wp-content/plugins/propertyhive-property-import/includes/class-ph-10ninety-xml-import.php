<?php
/**
 * Class for managing the import process of a 10ninety XML file
 *
 * @package WordPress
 */
if ( class_exists( 'PH_Property_Import_Process' ) ) {

class PH_10ninety_XML_Import extends PH_Property_Import_Process {

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

        do_action( "propertyhive_pre_import_properties_10ninety_xml", $this->properties );
        $this->properties = apply_filters( "propertyhive_10ninety_xml_properties_due_import", $this->properties );

		$this->add_log( 'Beginning to loop through ' . count($this->properties) . ' properties' );

		$property_row = 1;
		foreach ( $this->properties as $property )
		{
			$this->add_log( 'Importing property ' . $property_row .' with reference ' . (string)$property->AGENT_REF, (string)$property->AGENT_REF );

			$inserted_updated = false;

			$args = array(
	            'post_type' => 'property',
	            'posts_per_page' => 1,
	            'post_status' => 'any',
	            'meta_query' => array(
	            	array(
		            	'key' => $imported_ref_key,
		            	'value' => (string)$property->AGENT_REF
		            )
	            )
	        );
	        $property_query = new WP_Query($args);
	        
	        if ($property_query->have_posts())
	        {
	        	$this->add_log( 'This property has been imported before. Updating it', (string)$property->AGENT_REF );

	        	// We've imported this property before
	            while ($property_query->have_posts())
	            {
	                $property_query->the_post();

	                $post_id = get_the_ID();

	                $my_post = array(
				    	'ID'          	 => $post_id,
				    	'post_title'     => wp_strip_all_tags( (string)$property->DISPLAY_ADDRESS ),
				    	'post_excerpt'   => (string)$property->SUMMARY,
				    	'post_content' 	 => '',
				    	'post_status'    => 'publish',
				  	);

				 	// Update the post into the database
				    $post_id = wp_update_post( $my_post );

				    if ( is_wp_error( $post_id ) ) 
					{
						$this->add_error( 'ERROR: Failed to update post. The error was as follows: ' . $post_id->get_error_message(), (string)$property->AGENT_REF );
					}
					else
					{
						$inserted_updated = 'updated';
					}
	            }
	        }
	        else
	        {
	        	$this->add_log( 'This property hasn\'t been imported before. Inserting it', (string)$property->AGENT_REF );

	        	// We've not imported this property before
				$postdata = array(
					'post_excerpt'   => (string)$property->SUMMARY,
					'post_content' 	 => '',
					'post_title'     => wp_strip_all_tags( (string)$property->DISPLAY_ADDRESS ),
					'post_status'    => 'publish',
					'post_type'      => 'property',
					'comment_status' => 'closed',
				);

				$post_id = wp_insert_post( $postdata, true );

				if ( is_wp_error( $post_id ) ) 
				{
					$this->add_error( 'Failed to insert post. The error was as follows: ' . $post_id->get_error_message(), (string)$property->AGENT_REF );
				}
				else
				{
					$inserted_updated = 'inserted';
				}
			}
			$property_query->reset_postdata();

			if ( $inserted_updated !== FALSE )
			{
				// Need to check title and excerpt and see if they've gone in as blank but weren't blank in the feed
				// If they are, then do the encoding
				$inserted_post = get_post( $post_id );
				if ( 
					$inserted_post && 
					$inserted_post->post_title == '' && $inserted_post->post_excerpt == '' && 
					((string)$property->DISPLAY_ADDRESS != '' || (string)$property->SUMMARY != '')
				)
				{
					$my_post = array(
				    	'ID'          	 => $post_id,
				    	'post_title'     => htmlentities(mb_convert_encoding(wp_strip_all_tags( (string)$property->DISPLAY_ADDRESS ), 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
				    	'post_excerpt'   => htmlentities(mb_convert_encoding((string)$property->SUMMARY, 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
				    	'post_content' 	 => '',
				    	'post_name' 	 => sanitize_title((string)$property->DISPLAY_ADDRESS),
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

				$this->add_log( 'Successfully ' . $inserted_updated . ' post. The post ID is ' . $post_id, (string)$property->AGENT_REF );

				update_post_meta( $post_id, $imported_ref_key, (string)$property->AGENT_REF );

				// Address
				update_post_meta( $post_id, '_reference_number', (string)$property->AGENT_REF );
				update_post_meta( $post_id, '_address_name_number', trim( ( ( isset($property->ADDRESS_1) ) ? (string)$property->ADDRESS_1 : '' ) ) );
				update_post_meta( $post_id, '_address_street', ( ( isset($property->ADDRESS_2) ) ? (string)$property->ADDRESS_2 : '' ) );
				update_post_meta( $post_id, '_address_two', ( ( isset($property->ADDRESS_3) ) ? (string)$property->ADDRESS_3 : '' ) );
				update_post_meta( $post_id, '_address_three', ( ( isset($property->TOWN) ) ? (string)$property->TOWN : '' ) );
				update_post_meta( $post_id, '_address_four', ( ( isset($property->COUNTY) ) ? (string)$property->COUNTY : ( ( isset($property->ADDRESS_4) ) ? (string)$property->ADDRESS_4 : '' ) ) );
				update_post_meta( $post_id, '_address_postcode', trim( ( ( isset($property->POSTCODE1) ) ? (string)$property->POSTCODE1 : '' ) . ' ' . ( ( isset($property->POSTCODE2) ) ? (string)$property->POSTCODE2 : '' ) ) );

				$country = get_option( 'propertyhive_default_country', 'GB' );
				update_post_meta( $post_id, '_address_country', $country );

				// Check main address fields and see if this location exists as a taxonomy to try and assign properties to location
				$address_fields_to_check = apply_filters( 'propertyhive_10ninety_xml_address_fields_to_check', array('ADDRESS_2', 'ADDRESS_3', 'TOWN', 'ADDRESS_4', 'COUNTY') );
				$location_term_ids = array();

				if ( isset($property->SEARCHABLE_AREAS) )
				{
					foreach ( $property->SEARCHABLE_AREAS as $searchable_areas )
					{
						if ( isset($searchable_areas->SEARCHABLE_AREA) )
						{
							foreach ( $searchable_areas->SEARCHABLE_AREA as $searchable_area )
							{
								$term = term_exists( trim((string)$searchable_area), 'location');
								if ( $term !== 0 && $term !== null && isset($term['term_id']) )
								{
									$location_term_ids[] = (int)$term['term_id'];
								}
							}
						}
					}
				}

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

				// Coordinates
				if ( isset($property->LATITUDE) && isset($property->LONGITUDE) && (string)$property->LATITUDE != '' && (string)$property->LONGITUDE != '' && (string)$property->LATITUDE != '0' && (string)$property->LONGITUDE != '0' )
				{
					update_post_meta( $post_id, '_latitude', ( ( isset($property->LATITUDE) ) ? (string)$property->LATITUDE : '' ) );
					update_post_meta( $post_id, '_longitude', ( ( isset($property->LONGITUDE) ) ? (string)$property->LONGITUDE : '' ) );
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
								if ( isset($property->ADDRESS_1) && trim((string)$property->ADDRESS_1) != '' ) { $address_to_geocode[] = (string)$property->ADDRESS_1; }
								if ( isset($property->ADDRESS_2) && trim((string)$property->ADDRESS_2) != '' ) { $address_to_geocode[] = (string)$property->ADDRESS_2; }
								if ( isset($property->TOWN) && trim((string)$property->TOWN) != '' ) { $address_to_geocode[] = (string)$property->TOWN; }
								if ( isset($property->ADDRESS_3) && trim((string)$property->ADDRESS_3) != '' ) { $address_to_geocode[] = (string)$property->ADDRESS_3; }
								if ( isset($property->ADDRESS_4) && trim((string)$property->ADDRESS_4) != '' ) { $address_to_geocode[] = (string)$property->ADDRESS_4; }
								if ( isset($property->POSTCODE1) && isset($property->POSTCODE2) ) { $address_to_geocode[] = (string)$property->POSTCODE1 . ' ' . (string)$property->POSTCODE2; }

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
							        	$this->add_error( 'Google Geocoding service returned status ' . $status, (string)$property->AGENT_REF );
							        	sleep(3);
							        }
							    }
							    else
						        {
						        	$this->add_error( 'Failed to parse XML response from Google Geocoding service.', (string)$property->AGENT_REF );
						        }
							}
							else
					        {
					        	$this->add_error( 'Failed to obtain co-ordinates as allow_url_fopen setting is disabled', (string)$property->AGENT_REF );
					        }
					    }
					    else
					    {
					    	$this->add_log( 'Not performing Google Geocoding request as no API key present in settings', (string)$property->AGENT_REF );
					    }
					}
				}

				// Owner
				add_post_meta( $post_id, '_owner_contact_id', '', true );

				// Record Details
				add_post_meta( $post_id, '_negotiator_id', get_current_user_id(), true );
					
				$office_id = $primary_office_id;
				if ( isset($_POST['mapped_office'][(string)$property->BRANCH_ID]) && $_POST['mapped_office'][(string)$property->BRANCH_ID] != '' )
				{
					$office_id = $_POST['mapped_office'][(string)$property->BRANCH_ID];
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
				if ( (string)$property->TRANS_TYPE_ID == '2' )
				{
					$department = 'residential-lettings';
				}
				update_post_meta( $post_id, '_department', $department );
				update_post_meta( $post_id, '_bedrooms', ( ( isset($property->BEDROOMS) ) ? (string)$property->BEDROOMS : '' ) );
				update_post_meta( $post_id, '_bathrooms', ( ( isset($property->BATHROOMS) ) ? (string)$property->BATHROOMS : '' ) );
				update_post_meta( $post_id, '_reception_rooms', ( ( isset($property->RECEPTIONS) ) ? (string)$property->RECEPTIONS : '' ) );

				if ( isset($_POST['mapped_property_type']) )
				{
					$mapping = $_POST['mapped_property_type'];
				}
				else
				{
					$mapping = isset($options['mappings']['property_type']) ? $options['mappings']['property_type'] : array();
				}

				wp_delete_object_term_relationships( $post_id, 'property_type' );

				if ( isset($property->PROP_SUB_ID) && (string)$property->PROP_SUB_ID != '' )
				{
					if ( !empty($mapping) && isset($mapping[(string)$property->PROP_SUB_ID]) )
					{
						wp_set_post_terms( $post_id, $mapping[(string)$property->PROP_SUB_ID], 'property_type' );
					}
					else
					{
						$this->add_log( 'Property received with a type (' . (string)$property->propertyType . ') that is not mapped', (string)$property->AGENT_REF );

						$options = $this->add_missing_mapping( $mapping, 'property_type', (string)$property->propertyType, $import_id );
					}
				}

				// Residential Sales Details
				if ( $department == 'residential-sales' )
				{
					// Clean price
					$price = round(preg_replace("/[^0-9.]/", '', (string)$property->PRICE));

					update_post_meta( $post_id, '_price', $price );
					update_post_meta( $post_id, '_price_actual', $price );
					update_post_meta( $post_id, '_poa', ( ( isset($property->PRICE_QUALIFIER) && (string)$property->PRICE_QUALIFIER == '1' ) ? 'yes' : '') );

					update_post_meta( $post_id, '_currency', 'GBP' );

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
					if ( !empty($mapping) && isset($property->PRICE_QUALIFIER) && isset($mapping[(string)$property->PRICE_QUALIFIER]) )
					{
		                wp_set_post_terms( $post_id, $mapping[(string)$property->PRICE_QUALIFIER], 'price_qualifier' );
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
					if ( !empty($mapping) && isset($property->TENURE_TYPE_ID) && isset($mapping[(string)$property->TENURE_TYPE_ID]) )
					{
			            wp_set_post_terms( $post_id, $mapping[(string)$property->TENURE_TYPE_ID], 'tenure' );
		            }
				}
				elseif ( $department == 'residential-lettings' )
				{
					// Clean price
					$price = round(preg_replace("/[^0-9.]/", '', (string)$property->PRICE));

					update_post_meta( $post_id, '_rent', $price );

					$rent_frequency = 'pcm';
					$price_actual = $price;
					switch ((string)$property->LET_RENT_FREQUENCY)
					{
						case "0": { $rent_frequency = 'pw'; $price_actual = ($price * 52) / 12; break; }
						case "1": { $rent_frequency = 'pcm'; $price_actual = $price; break; }
						case "2": { $rent_frequency = 'pq'; $price_actual = ($price * 4) / 12; break; }
						case "3": { $rent_frequency = 'pa'; $price_actual = $price / 12; break; }
						case "5": 
						{
							$rent_frequency = 'pppw';
							$bedrooms = ( isset($property->BEDROOMS) ? (string)$property->BEDROOMS : '0' );
							if ( $bedrooms != '' && $bedrooms != 0 )
							{
								$price_actual = (($price * 52) / 12) * $bedrooms;
							}
							else
							{
								$price_actual = ($price * 52) / 12;
							}
							break; 
						}
					}
					update_post_meta( $post_id, '_rent_frequency', $rent_frequency );
					update_post_meta( $post_id, '_price_actual', $price_actual );
	
					update_post_meta( $post_id, '_currency', 'GBP' );

					update_post_meta( $post_id, '_poa', ( ( isset($property->PRICE_QUALIFIER) && (string)$property->PRICE_QUALIFIER == '1' ) ? 'yes' : '') );

					update_post_meta( $post_id, '_deposit', preg_replace( "/[^0-9.]/", '', ( ( isset($property->LET_BOND) ) ? (string)$property->LET_BOND : '' ) ) );
            		update_post_meta( $post_id, '_available_date', ( (isset($property->LET_DATE_AVAILABLE) && (string)$property->LET_DATE_AVAILABLE != '') ? date("Y-m-d", strtotime((string)$property->LET_DATE_AVAILABLE)) : '' ) );

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
					if ( !empty($mapping) && isset($property->LET_FURN_ID) && isset($mapping[(string)$property->LET_FURN_ID]) )
					{
		                wp_set_post_terms( $post_id, $mapping[(string)$property->LET_FURN_ID], 'furnished' );
		            }
				}

				// Marketing
				update_post_meta( $post_id, '_on_market', ( !isset($property->PUBLISHED_FLAG) || ( isset($property->PUBLISHED_FLAG) && (string)$property->PUBLISHED_FLAG == '1' ) ) ? 'yes' : '' );
				update_post_meta( $post_id, '_featured', ( isset($property->FEATURE_ON_HOMEPAGE) && (string)$property->FEATURE_ON_HOMEPAGE == 'True' ) ? 'yes' : '' );

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

        		wp_delete_object_term_relationships( $post_id, 'availability' );
				if ( !empty($mapping) && isset($property->STATUS_ID) && isset($mapping[(string)$property->STATUS_ID]) )
				{
	                wp_set_post_terms( $post_id, $mapping[(string)$property->STATUS_ID], 'availability' );
	            }

	            // Features
				$features = array();
				for ( $i = 1; $i <= 10; ++$i )
				{
					if ( isset($property->{'FEATURE' . $i}) && trim((string)$property->{'FEATURE' . $i}) != '' )
					{
						$features[] = trim((string)$property->{'FEATURE' . $i});
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
	            update_post_meta( $post_id, '_room_description_0', (string)$property->DESCRIPTION );

	            // Media - Images
	            if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();

    				for ( $i = 0; $i <= 49; ++$i )
					{
						$j = str_pad( $i, 2, '0', STR_PAD_LEFT );

						if ( isset($property->{'MEDIA_IMAGE_' . $j}) && trim((string)$property->{'MEDIA_IMAGE_' . $j}) != '' )
						{
							if ( 
								substr( strtolower((string)$property->{'MEDIA_IMAGE_' . $j}), 0, 2 ) == '//' || 
								substr( strtolower((string)$property->{'MEDIA_IMAGE_' . $j}), 0, 4 ) == 'http'
							)
							{
								// This is a URL
								$url = (string)$property->{'MEDIA_IMAGE_' . $j};

								$media_urls[] = array('url' => $url);
							}
						}
					}

    				update_post_meta( $post_id, '_photo_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' photo URLs', (string)$property->AGENT_REF );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_photos', TRUE );
					for ( $i = 0; $i <= 49; ++$i )
					{
						$j = str_pad( $i, 2, '0', STR_PAD_LEFT );

						if ( isset($property->{'MEDIA_IMAGE_' . $j}) && trim((string)$property->{'MEDIA_IMAGE_' . $j}) != '' )
						{
							if ( 
								substr( strtolower((string)$property->{'MEDIA_IMAGE_' . $j}), 0, 2 ) == '//' || 
								substr( strtolower((string)$property->{'MEDIA_IMAGE_' . $j}), 0, 4 ) == 'http'
							)
							{
								// This is a URL
								$url = (string)$property->{'MEDIA_IMAGE_' . $j};
								$explode_url = explode('?', $url);

								$description = ( ( isset($property->{'MEDIA_IMAGE_TEXT_' . $j}) && (string)$property->{'MEDIA_IMAGE_TEXT_' . $j} != '' ) ? (string)$property->{'MEDIA_IMAGE_TEXT_' . $j} : '' );
							    
								$filename = basename( $url );

								// Check, based on the URL, whether we have previously imported this media
								$imported_previously = false;
								$imported_previously_id = '';
								if ( is_array($previous_media_ids) && !empty($previous_media_ids) )
								{
									foreach ( $previous_media_ids as $previous_media_id )
									{
										if ( get_post_meta( $previous_media_id, '_imported_url', TRUE ) == $explode_url[0] )
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
								        'name' => basename( $url ) . '.jpg',
								        'tmp_name' => $tmp
								    );

								    // Check for download errors
								    if ( is_wp_error( $tmp ) ) 
								    {
								        @unlink( $file_array[ 'tmp_name' ] );

								        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), (string)$property->AGENT_REF );
								    }
								    else
								    {
									    $id = media_handle_sideload( $file_array, $post_id, $description );

									    // Check for handle sideload errors.
									    if ( is_wp_error( $id ) ) 
									    {
									        @unlink( $file_array['tmp_name'] );
									        
									        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), (string)$property->AGENT_REF );
									    }
									    else
									    {
									    	$media_ids[] = $id;

									    	update_post_meta( $id, '_imported_url', $explode_url[0]);

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

					$this->add_log( 'Imported ' . count($media_ids) . ' photos (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->AGENT_REF );
				}

				// Media - Floorplans
				if ( get_option('propertyhive_floorplans_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();

    				for ( $i = 0; $i <= 10; ++$i )
					{
						$j = str_pad( $i, 2, '0', STR_PAD_LEFT );

						if ( isset($property->{'MEDIA_FLOOR_PLAN_' . $j}) && trim((string)$property->{'MEDIA_FLOOR_PLAN_' . $j}) != '' )
						{
							if ( 
								substr( strtolower((string)$property->{'MEDIA_FLOOR_PLAN_' . $j}), 0, 2 ) == '//' || 
								substr( strtolower((string)$property->{'MEDIA_FLOOR_PLAN_' . $j}), 0, 4 ) == 'http'
							)
							{
								// This is a URL
								$url = (string)$property->{'MEDIA_FLOOR_PLAN_' . $j};

								$media_urls[] = array('url' => $url);
							}
						}
					}

    				update_post_meta( $post_id, '_floorplan_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' floorplan URLs', (string)$property->AGENT_REF );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_floorplans', TRUE );
					for ( $i = 0; $i <= 10; ++$i )
					{
						$j = str_pad( $i, 2, '0', STR_PAD_LEFT );

						if ( isset($property->{'MEDIA_FLOOR_PLAN_' . $j}) && trim((string)$property->{'MEDIA_FLOOR_PLAN_' . $j}) != '' )
						{
							if ( 
								substr( strtolower((string)$property->{'MEDIA_FLOOR_PLAN_' . $j}), 0, 2 ) == '//' || 
								substr( strtolower((string)$property->{'MEDIA_FLOOR_PLAN_' . $j}), 0, 4 ) == 'http'
							)
							{
								// This is a URL
								$url = (string)$property->{'MEDIA_FLOOR_PLAN_' . $j};
								$explode_url = explode('?', $url);

								$description = ( ( isset($property->{'MEDIA_FLOOR_PLAN_TEXT_' . $j}) && (string)$property->{'MEDIA_FLOOR_PLAN_TEXT_' . $j} != '' ) ? (string)$property->{'MEDIA_FLOOR_PLAN_TEXT_' . $j} : '' );
							    
								$filename = basename( $url );

								// Check, based on the URL, whether we have previously imported this media
								$imported_previously = false;
								$imported_previously_id = '';
								if ( is_array($previous_media_ids) && !empty($previous_media_ids) )
								{
									foreach ( $previous_media_ids as $previous_media_id )
									{
										if ( get_post_meta( $previous_media_id, '_imported_url', TRUE ) == $explode_url[0] )
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
								        'name' => $filename . '.jpg',
								        'tmp_name' => $tmp
								    );

								    // Check for download errors
								    if ( is_wp_error( $tmp ) ) 
								    {
								        @unlink( $file_array[ 'tmp_name' ] );

								        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), (string)$property->AGENT_REF );
								    }
								    else
								    {
									    $id = media_handle_sideload( $file_array, $post_id, $description );

									    // Check for handle sideload errors.
									    if ( is_wp_error( $id ) ) 
									    {
									        @unlink( $file_array['tmp_name'] );
									        
									        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), (string)$property->AGENT_REF );
									    }
									    else
									    {
									    	$media_ids[] = $id;

									    	update_post_meta( $id, '_imported_url', $explode_url[0]);

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

					$this->add_log( 'Imported ' . count($media_ids) . ' floorplans (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->AGENT_REF );
				}

				// Media - Brochures
				if ( get_option('propertyhive_brochures_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();

    				for ( $i = 0; $i <= 10; ++$i )
					{
						$j = str_pad( $i, 2, '0', STR_PAD_LEFT );

						if ( isset($property->{'MEDIA_DOCUMENT_' . $j}) && trim((string)$property->{'MEDIA_DOCUMENT_' . $j}) != '' )
						{
							if ( 
								substr( strtolower((string)$property->{'MEDIA_DOCUMENT_' . $j}), 0, 2 ) == '//' || 
								substr( strtolower((string)$property->{'MEDIA_DOCUMENT_' . $j}), 0, 4 ) == 'http'
							)
							{
								// This is a URL
								$url = (string)$property->{'MEDIA_DOCUMENT_' . $j};

								$media_urls[] = array('url' => $url);
							}
						}
					}

    				update_post_meta( $post_id, '_brochure_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' brochure URLs', (string)$property->AGENT_REF );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_brochures', TRUE );
					for ( $i = 0; $i <= 10; ++$i )
					{
						$j = str_pad( $i, 2, '0', STR_PAD_LEFT );

						if ( isset($property->{'MEDIA_DOCUMENT_' . $j}) && trim((string)$property->{'MEDIA_DOCUMENT_' . $j}) != '' )
						{
							if ( 
								substr( strtolower((string)$property->{'MEDIA_DOCUMENT_' . $j}), 0, 2 ) == '//' || 
								substr( strtolower((string)$property->{'MEDIA_DOCUMENT_' . $j}), 0, 4 ) == 'http'
							)
							{
								// This is a URL
								$url = (string)$property->{'MEDIA_DOCUMENT_' . $j};
								$explode_url = explode('?', $url);

								$description = ( ( isset($property->{'MEDIA_DOCUMENT_TEXT_' . $j}) && (string)$property->{'MEDIA_DOCUMENT_TEXT_' . $j} != '' ) ? $property->{'MEDIA_DOCUMENT_TEXT_' . $j} : '' );
							    
								$filename = basename( $url );

								// Check, based on the URL, whether we have previously imported this media
								$imported_previously = false;
								$imported_previously_id = '';
								if ( is_array($previous_media_ids) && !empty($previous_media_ids) )
								{
									foreach ( $previous_media_ids as $previous_media_id )
									{
										if ( get_post_meta( $previous_media_id, '_imported_url', TRUE ) == $explode_url[0] )
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
								        'name' => $filename . '.pdf',
								        'tmp_name' => $tmp
								    );

								    // Check for download errors
								    if ( is_wp_error( $tmp ) ) 
								    {
								        @unlink( $file_array[ 'tmp_name' ] );

								        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), (string)$property->AGENT_REF );
								    }
								    else
								    {
									    $id = media_handle_sideload( $file_array, $post_id, $description );

									    // Check for handle sideload errors.
									    if ( is_wp_error( $id ) ) 
									    {
									        @unlink( $file_array['tmp_name'] );
									        
									        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), (string)$property->AGENT_REF );
									    }
									    else
									    {
									    	$media_ids[] = $id;

									    	update_post_meta( $id, '_imported_url', $explode_url[0]);

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

					$this->add_log( 'Imported ' . count($media_ids) . ' brochures (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->AGENT_REF );
				}

				// Media - EPCS
				if ( get_option('propertyhive_epcs_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();

    				for ( $i = 60; $i <= 61; ++$i )
					{
						$j = str_pad( $i, 2, '0', STR_PAD_LEFT );

						if ( isset($property->{'MEDIA_IMAGE_' . $j}) && trim((string)$property->{'MEDIA_IMAGE_' . $j}) != '' )
						{
							if ( 
								substr( strtolower((string)$property->{'MEDIA_IMAGE_' . $j}), 0, 2 ) == '//' || 
								substr( strtolower((string)$property->{'MEDIA_IMAGE_' . $j}), 0, 4 ) == 'http'
							)
							{
								// This is a URL
								$url = (string)$property->{'MEDIA_IMAGE_' . $j};

								$media_urls[] = array('url' => $url);
							}
						}
					}
					for ( $i = 50; $i <= 55; ++$i )
					{
						$j = str_pad( $i, 2, '0', STR_PAD_LEFT );

						if ( isset($property->{'MEDIA_DOCUMENT_' . $j}) && trim((string)$property->{'MEDIA_DOCUMENT_' . $j}) != '' )
						{
							if ( 
								substr( strtolower((string)$property->{'MEDIA_DOCUMENT_' . $j}), 0, 2 ) == '//' || 
								substr( strtolower((string)$property->{'MEDIA_DOCUMENT_' . $j}), 0, 4 ) == 'http'
							)
							{
								// This is a URL
								$url = (string)$property->{'MEDIA_DOCUMENT_' . $j};

								$media_urls[] = array('url' => $url);
							}
						}
					}

    				update_post_meta( $post_id, '_epc_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' EPC URLs', (string)$property->AGENT_REF );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_epcs', TRUE );
					for ( $i = 60; $i <= 61; ++$i )
					{
						$j = str_pad( $i, 2, '0', STR_PAD_LEFT );

						if ( isset($property->{'MEDIA_IMAGE_' . $j}) && trim((string)$property->{'MEDIA_IMAGE_' . $j}) != '' )
						{
							if ( 
								substr( strtolower((string)$property->{'MEDIA_IMAGE_' . $j}), 0, 2 ) == '//' || 
								substr( strtolower((string)$property->{'MEDIA_IMAGE_' . $j}), 0, 4 ) == 'http'
							)
							{
								// This is a URL
								$url = (string)$property->{'MEDIA_IMAGE_' . $j};
								$explode_url = explode('?', $url);

								$description = ( ( isset($property->{'MEDIA_IMAGE_TEXT_' . $j}) && (string)$property->{'MEDIA_IMAGE_TEXT_' . $j} != '' ) ? (string)$property->{'MEDIA_IMAGE_TEXT_' . $j} : '' );
							    
								$filename = basename( $url );

								// Check, based on the URL, whether we have previously imported this media
								$imported_previously = false;
								$imported_previously_id = '';
								if ( is_array($previous_media_ids) && !empty($previous_media_ids) )
								{
									foreach ( $previous_media_ids as $previous_media_id )
									{
										if ( get_post_meta( $previous_media_id, '_imported_url', TRUE ) == $explode_url[0] )
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
								        'name' => $filename . '.jpg',
								        'tmp_name' => $tmp
								    );

								    // Check for download errors
								    if ( is_wp_error( $tmp ) ) 
								    {
								        @unlink( $file_array[ 'tmp_name' ] );

								        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), (string)$property->AGENT_REF );
								    }
								    else
								    {
									    $id = media_handle_sideload( $file_array, $post_id, $description );

									    // Check for handle sideload errors.
									    if ( is_wp_error( $id ) ) 
									    {
									        @unlink( $file_array['tmp_name'] );
									        
									        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), (string)$property->AGENT_REF );
									    }
									    else
									    {
									    	$media_ids[] = $id;

									    	update_post_meta( $id, '_imported_url', $explode_url[0]);

									    	++$new;
									    }
									}
								}
							}
						}
					}
					for ( $i = 50; $i <= 55; ++$i )
					{
						$j = str_pad( $i, 2, '0', STR_PAD_LEFT );

						if ( isset($property->{'MEDIA_DOCUMENT_' . $j}) && trim((string)$property->{'MEDIA_DOCUMENT_' . $j}) != '' )
						{
							if ( 
								substr( strtolower((string)$property->{'MEDIA_DOCUMENT_' . $j}), 0, 2 ) == '//' || 
								substr( strtolower((string)$property->{'MEDIA_DOCUMENT_' . $j}), 0, 4 ) == 'http'
							)
							{
								// This is a URL
								$url = (string)$property->{'MEDIA_DOCUMENT_' . $j};
								$explode_url = explode('?', $url);

								$description = ( ( isset($property->{'MEDIA_DOCUMENT_TEXT_' . $j}) && (string)$property->{'MEDIA_DOCUMENT_TEXT_' . $j} != '' ) ? (string)$property->{'MEDIA_DOCUMENT_TEXT_' . $j} : '' );
							    
								$filename = basename( $url );

								// Check, based on the URL, whether we have previously imported this media
								$imported_previously = false;
								$imported_previously_id = '';
								if ( is_array($previous_media_ids) && !empty($previous_media_ids) )
								{
									foreach ( $previous_media_ids as $previous_media_id )
									{
										if ( get_post_meta( $previous_media_id, '_imported_url', TRUE ) == $explode_url[0] )
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
								        'name' => $filename . '.pdf',
								        'tmp_name' => $tmp
								    );

								    // Check for download errors
								    if ( is_wp_error( $tmp ) ) 
								    {
								        @unlink( $file_array[ 'tmp_name' ] );

								        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), (string)$property->AGENT_REF );
								    }
								    else
								    {
									    $id = media_handle_sideload( $file_array, $post_id, $description );

									    // Check for handle sideload errors.
									    if ( is_wp_error( $id ) ) 
									    {
									        @unlink( $file_array['tmp_name'] );
									        
									        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), (string)$property->AGENT_REF );
									    }
									    else
									    {
									    	$media_ids[] = $id;

									    	update_post_meta( $id, '_imported_url', $explode_url[0]);

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

					$this->add_log( 'Imported ' . count($media_ids) . ' EPCs (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->AGENT_REF );
				}

				if ( !empty($files_to_unlink) )
				{
					foreach ( $files_to_unlink as $file_to_unlink )
					{
						unlink($file_to_unlink);
					}
				}

				// Media - Virtual Tours
				$urls = array();

				for ( $i = 0; $i <= 5; ++$i )
				{
					$j = str_pad( $i, 2, '0', STR_PAD_LEFT );

					if ( isset($property->{'MEDIA_VIRTUAL_TOUR_' . $j}) && trim((string)$property->{'MEDIA_VIRTUAL_TOUR_' . $j}) != '' )
					{
						if ( 
							substr( strtolower((string)$property->{'MEDIA_VIRTUAL_TOUR_' . $j}), 0, 2 ) == '//' || 
							substr( strtolower((string)$property->{'MEDIA_VIRTUAL_TOUR_' . $j}), 0, 4 ) == 'http'
						)
						{
							$urls[] = trim((string)$property->{'MEDIA_VIRTUAL_TOUR_' . $j});
						}
					}
				}

				if ( !empty($urls) )
				{
					update_post_meta($post_id, '_virtual_tours', count($urls) );
        
			        foreach ($urls as $i => $url)
			        {
			            update_post_meta($post_id, '_virtual_tour_' . $i, $url);
			        }

			        $this->add_log( 'Imported ' . count($urls) . ' virtual tours', (string)$property->AGENT_REF );
				}

				do_action( "propertyhive_property_imported_10ninety_xml", $post_id, $property );

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
							$this->add_log( 'New meta data for ' . trim($key, '_') . ': ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), (string)$property->AGENT_REF );
						}
						elseif ( $metadata_before[$key] != $metadata_after[$key] )
						{
							$this->add_log( 'Updated ' . trim($key, '_') . '. Before: ' . ( ( is_array($metadata_before[$key]) ) ? implode(", ", $metadata_before[$key]) : $metadata_before[$key] ) . ', After: ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), (string)$property->AGENT_REF );
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
							$this->add_log( 'New taxonomy data for ' . $taxonomy_name . ': ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), (string)$property->AGENT_REF );
						}
						elseif ( $taxonomy_terms_before[$taxonomy_name] != $taxonomy_terms_after[$taxonomy_name] )
						{
							$this->add_log( 'Updated ' . $taxonomy_name . '. Before: ' . ( ( is_array($taxonomy_terms_before[$taxonomy_name]) ) ? implode(", ", $taxonomy_terms_before[$taxonomy_name]) : $taxonomy_terms_before[$taxonomy_name] ) . ', After: ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), (string)$property->AGENT_REF );
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

		do_action( "propertyhive_post_import_properties_10ninety_xml" );

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
			$import_refs[] = (string)$property->AGENT_REF;
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

				do_action( "propertyhive_property_removed_10ninety_xml", $post->ID );
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

			/*if ( get_option( 'propertyhive_active_departments_commercial' ) == 'yes' )
			{
				$mapping_values = $this->get_xml_mapping_values('commercial_availability');
				if ( is_array($mapping_values) && !empty($mapping_values) )
				{
					foreach ($mapping_values as $mapping_value => $text_value)
					{
						$this->mappings['commercial_availability'][$mapping_value] = '';
					}
				}
			}*/
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
        if ($custom_field == 'availability' || $custom_field == 'commercial_availability')
        {
            return array(
                '0' => 'Available',
                '1' => 'SSTC',
                '2' => 'SSTCM (Scotland only)',
                '3' => 'Under Offer',
                '4' => 'Reserved',
                '5' => 'Let Agreed',
                '6' => 'Sold',
                '7' => 'Let',
            );
        }
        if ($custom_field == 'sales_availability')
        {
            return array(
                '0' => 'Available',
                '1' => 'SSTC',
                '2' => 'SSTCM (Scotland only)',
                '3' => 'Under Offer',
                '6' => 'Sold',
            );
        }
        if ($custom_field == 'lettings_availability')
        {
            return array(
                '0' => 'Available',
                '4' => 'Reserved',
                '5' => 'Let Agreed',
                '7' => 'Let',
            );
        }
        if ($custom_field == 'property_type' || $custom_field == 'commercial_property_type')
        {
        	$commercial_property_types = array(
        		'19' => 'Commercial Property',
        		'80' => 'Restaurant',
                '83' => 'Cafe',
                '86' => 'Mill',
                '134' => 'Bar / Nightclub',
                '137' => 'Shop',
                '178' => 'Office',
                '181' => 'Business Park',
                '184' => 'Serviced Office',
                '187' => 'Retail Property (High Street)',
                '190' => 'Retail Property (Out of Town)',
                '193' => 'Convenience Store',
                '196' => 'Garages',
                '199' => 'Hairdresser/Barber Shop',
                '202' => 'Hotel',
                '205' => 'Petrol Station',
                '208' => 'Post Office',
                '211' => 'Pub',
                '214' => 'Workshop & Retail Space',
                '217' => 'Distribution Warehouse',
                '220' => 'Factory',
                '223' => 'Heavy Industrial',
                '226' => 'Industrial Park',
                '229' => 'Light Industrial',
                '232' => 'Storage',
                '235' => 'Showroom',
                '238' => 'Warehouse',
                '241' => 'Land (Commercial)',
                '244' => 'Commercial Development',
                '247' => 'Industrial Development',
                '250' => 'Residential Development',
                '253' => 'Commercial Property',
                '256' => 'Data Centre',
                '259' => 'Farm',
                '262' => 'Healthcare Facility',
                '265' => 'Marine Property',
                '268' => 'Mixed Use',
                '271' => 'Research & Development Facility',
                '274' => 'Science Park',
                '277' => 'Guest House',
                '280' => 'Hospitality',
                '283' => 'Leisure Facility',
        	);
        }
        if ($custom_field == 'property_type')
        {
            $return = array(
                '0' => 'Not Specified',
                '1' => 'Terraced',
                '2' => 'End of Terrace',
                '3' => 'Semi-Detached ',
                '4' => 'Detached',
                '5' => 'Mews',
                '6' => 'Cluster House',
                '7' => 'Ground Flat',
                '8' => 'Flat',
                '9' => 'Studio',
                '10' => 'Ground Maisonette',
                '11' => 'Maisonette',
                '12' => 'Bungalow',
                '13' => 'Terraced Bungalow',
                '14' => 'Semi-Detached Bungalow',
                '15' => 'Detached Bungalow',
                '16' => 'Mobile Home',
                '17' => 'Hotel',
                '18' => 'Guest House',
                '20' => 'Land',
                '21' => 'Link Detached House',
                '22' => 'Town House',
                '23' => 'Cottage',
                '24' => 'Chalet',
                '27' => 'Villa',
                '28' => 'Apartment',
                '29' => 'Penthouse',
                '30' => 'Finca',
                '43' => 'Barn Conversion',
                '44' => 'Serviced Apartments',
                '45' => 'Parking',
                '46' => 'Sheltered Housing',
                '47' => 'Retirement Property',
                '48' => 'House Share',
                '49' => 'Flat Share',
                '51' => 'Garages',
                '52' => 'Farm House',
                '53' => 'Equestrian',
                '56' => 'Duplex',
                '59' => 'Triplex',
                '62' => 'Longere',
                '65' => 'Gite',
                '68' => 'Barn',
                '71' => 'Trulli',
                '74' => 'Mill',
                '77' => 'Ruins',
                '89' => 'Trulli',
                '92' => 'Castle',
                '95' => 'Village House',
                '101' => 'Cave House',
                '104' => 'Cortijo',
                '107' => 'Farm Land',
                '110' => 'Plot',
                '113' => 'Country House',
                '116' => 'Stone House',
                '117' => 'Caravan',
                '118' => 'Lodge',
                '119' => 'Log Cabin',
                '120' => 'Manor House',
                '121' => 'Stately Home',
                '125' => 'Off-Plan',
                '128' => 'Semi-detached Villa',
                '131' => 'Detached Villa',
                '140' => 'Riad',
                '141' => 'House Boat',
                '142' => 'Hotel Room',
                '143' => 'Block of Apartments',
                '144' => 'Private Halls',
                '253' => 'Commercial Property',
            );

			// If commercial department not active then add commercial types to normal list of types
			if ( get_option( 'propertyhive_active_departments_commercial', '' ) == '' )
			{
				$return = array_merge( $return, $commercial_property_types );
			}

			return $return;
        }
        if ($custom_field == 'commercial_property_type')
        {
        	return $commercial_property_types;
        }
        if ($custom_field == 'price_qualifier')
        {
            return array(
                '0' => 'Default',
                '1' => 'POA',
                '2' => 'Guide Price',
                '3' => 'Fixed Price',
                '4' => 'Offers in Excess of',
                '5' => 'OIRO',
                '6' => 'Sale by Tender',
                '7' => 'From',
                '9' => 'Shared Ownership',
                '10' => 'Offers Over',
                '11' => 'Part Buy Part Rent',
                '12' => 'Shared Equity',
            );
        }
        if ($custom_field == 'tenure')
        {
            return array(
                '1' => 'Freehold',
                '2' => 'Leasehold',
                '3' => 'Feudal',
                '4' => 'Commonhold',
                '5' => 'Share of Freehold',
            );
        }
        if ($custom_field == 'furnished')
        {
            return array(
                '0' => 'Furnished',
                '1' => 'Part Furnished',
                '2' => 'Unfurnished',
                '3' => 'Not Specified',
                '4' => 'Furnished/Un Furnished',
            );
        }
    }

}

}