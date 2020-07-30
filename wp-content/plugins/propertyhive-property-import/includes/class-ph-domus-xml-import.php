<?php
/**
 * Class for managing the import process of a Domus XML file
 *
 * @package WordPress
 */
if ( class_exists( 'PH_Property_Import_Process' ) ) {

class PH_Domus_XML_Import extends PH_Property_Import_Process {

	/**
	 * @var array
	 */
	private $featured_properties;

	public function __construct( $instance_id = '' ) 
	{
		$this->instance_id = $instance_id;

		if ( isset($_GET['custom_property_import_cron']) )
	    {
	    	$current_user = wp_get_current_user();

	    	$this->add_log("Executed manually by " . ( ( isset($current_user->display_name) ) ? $current_user->display_name : '' ) );
	    }
	}

	public function parse( $options )
	{
		$this->properties = array(); // Reset properties in the event we're importing multiple files

		// Sales Properties
		$this->add_log("Obtaining sales properties");

		$contents = '';

		$response = wp_remote_get( $options['xml_url'] . '/search?items=9999&includeUnavailable=true', array( 'timeout' => 120, 'sslverify' => FALSE ) );
		if ( !is_wp_error($response) && is_array( $response ) ) 
		{
			$contents = $response['body'];
		}
		else
		{
			$this->add_error( 'Failed to obtain sales XML file. Dump of response as follows: ' . print_r($response, TRUE) );
			return false;
		}

		$xml = simplexml_load_string( $contents );

		if ($xml !== FALSE)
		{
			$this->add_log("Parsing sales properties");
			
			foreach ($xml->property as $property)
			{
				if ( isset($property->status) && ( (string)$property->status == 'Sold' || (string)$property->status == 'Let' ) )
				{

				}
				else
				{
	                $property = $this->get_property( (string)$property->id, $options );

					if ( $property !== FALSE )
					{
			            $this->properties[] = $property;
		            }
		        }
            } // end foreach property
        }
        else
        {
        	// Failed to parse XML
        	$this->add_error( 'Failed to parse sales XML file. Possibly invalid XML' );

        	return false;
        }

        // Lettings Properties
        $this->add_log("Obtaining lettings properties");

		$contents = '';

		$response = wp_remote_get( $options['xml_url'] . '/search?sales=false&items=9999&includeUnavailable=true', array( 'timeout' => 120, 'sslverify' => FALSE ) );
		if ( !is_wp_error($response) && is_array( $response ) ) 
		{
			$contents = $response['body'];
		}
		else
		{
			$this->add_error( 'Failed to obtain lettings XML file. Dump of response as follows: ' . print_r($response, TRUE) );
			return false;
		}

		$xml = simplexml_load_string( $contents );

		if ($xml !== FALSE)
		{
			$this->add_log("Parsing lettings properties");
			
			foreach ($xml->property as $property)
			{
				if ( isset($property->status) && ( (string)$property->status == 'Sold' || (string)$property->status == 'Let' ) )
				{

				}
				else
				{
					$property = $this->get_property( (string)$property->id, $options );

					if ( $property !== FALSE )
					{
		                $this->properties[] = $property;
		            }
		        }
            } // end foreach property
        }
        else
        {
        	// Failed to parse XML
        	$this->add_error( 'Failed to parse lettings XML file. Possibly invalid XML' );

        	return false;
        }

        // Featured Properties
        $this->add_log("Obtaining featured properties");

		$contents = '';

		$response = wp_remote_get( $options['xml_url'] . '/featured', array( 'timeout' => 120, 'sslverify' => FALSE ) );
		if ( !is_wp_error($response) && is_array( $response ) ) 
		{
			$contents = $response['body'];
		}
		else
		{
			$this->add_error( 'Failed to obtain featured XML file. Dump of response as follows: ' . print_r($response, TRUE) );
			return false;
		}

		$xml = simplexml_load_string( $contents );

		if ($xml !== FALSE)
		{
			$this->add_log("Parsing featured properties");
			
			foreach ($xml->property as $property)
			{
		        $this->featured_properties[] = (string)$property->id;
            } // end foreach property
        }
        else
        {
        	// Failed to parse XML
        	$this->add_error( 'Failed to parse featured XML file. Possibly invalid XML' );

        	return false;
        }

        return true;
	}

	private function get_property( $id, $options )
	{
		$contents = '';

		$response = wp_remote_get( $options['xml_url'] . '/property?propertyID=' . $id, array( 'timeout' => 120, 'sslverify' => FALSE ) );
		if ( !is_wp_error($response) && is_array( $response ) ) 
		{
			$contents = $response['body'];
		}
		else
		{
			$this->add_error( 'Failed to obtain property XML file ' . $options['xml_url'] . '/property?propertyID=' . $id . '. Dump of response as follows: ' . print_r($response, TRUE) );
			return false;
		}

		$xml = simplexml_load_string( $contents );

		if ($xml !== FALSE)
		{
			return $xml;
		}
		else
		{
			// Failed to parse XML
        	$this->add_error( 'Failed to parse property XML file from ' . $options['xml_url'] . '/property?propertyID=' . $id . '. Possibly invalid XML' );

        	return false;
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

        do_action( "propertyhive_pre_import_properties_domus_xml", $this->properties );
        $this->properties = apply_filters( "propertyhive_domus_xml_properties_due_import", $this->properties );

		$this->add_log( 'Beginning to loop through ' . count($this->properties) . ' properties' );

		$property_row = 1;
		foreach ( $this->properties as $property )
		{
			$this->add_log( 'Importing property ' . $property_row .' with reference ' . (string)$property->id, (string)$property->id );

			$inserted_updated = false;

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
				    	'post_title'     => wp_strip_all_tags( (string)$property->address->advertising ),
				    	'post_excerpt'   => ( ( isset($property->description) ) ? (string)$property->description : '' ),
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
					'post_excerpt'   => ( ( isset($property->description) ) ? (string)$property->description : '' ),
					'post_content' 	 => '',
					'post_title'     => wp_strip_all_tags( (string)$property->address->advertising ),
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
					((string)$property->address->advertising != '' || (string)$property->description != '')
				)
				{
					$my_post = array(
				    	'ID'          	 => $post_id,
				    	'post_title'     => htmlentities(mb_convert_encoding(wp_strip_all_tags( (string)$property->address->advertising ), 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
				    	'post_excerpt'   => htmlentities(mb_convert_encoding((string)$property->description, 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
				    	'post_content' 	 => '',
				    	'post_name' 	 => sanitize_title((string)$property->address->advertising),
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

				update_post_meta( $post_id, $imported_ref_key, (string)$property->id );

				// Address
				update_post_meta( $post_id, '_reference_number', ( ( isset($property->reference) ) ? (string)$property->reference : '' ) );
				update_post_meta( $post_id, '_address_name_number', trim( ( ( isset($property->address->name) ) ? (string)$property->address->name : '' ) . ' ' . ( ( isset($property->address->number) ) ? (string)$property->address->number : '' ) ) );
				update_post_meta( $post_id, '_address_street', ( ( isset($property->address->street) ) ? (string)$property->address->street : '' ) );
				update_post_meta( $post_id, '_address_two', ( ( isset($property->address->locality) ) ? (string)$property->address->locality : '' ) );
				update_post_meta( $post_id, '_address_three', ( ( isset($property->address->town) ) ? (string)$property->address->town : '' ) );
				update_post_meta( $post_id, '_address_four', ( ( isset($property->address->county) ) ? (string)$property->address->county : '' ) );
				update_post_meta( $post_id, '_address_postcode', ( ( isset($property->address->postcode) ) ? (string)$property->address->postcode : '' ) );

				$country = get_option( 'propertyhive_default_country', 'GB' );
				if ( isset($property->address->country) && (string)$property->address->country != '' && class_exists('PH_Countries') )
				{
					$ph_countries = new PH_Countries();
					foreach ( $ph_countries->countries as $country_code => $country_details )
					{
						if ( strtolower((string)$property->address->country) == strtolower($country_details['name']) )
						{
							$country = $country_code;
							break;
						}
					}
				}
				update_post_meta( $post_id, '_address_country', $country );

				// Check main address fields and see if this location exists as a taxonomy to try and assign properties to location
				$address_fields_to_check = apply_filters( 'propertyhive_domus_xml_address_fields_to_check', array('locality', 'town', 'county') );
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

				// Coordinates
				if ( isset($property->address->latitude) && isset($property->address->longitude) && (string)$property->address->latitude != '' && (string)$property->address->longitude != '' && (string)$property->address->latitude != '0' && (string)$property->address->longitude != '0' )
				{
					update_post_meta( $post_id, '_latitude', ( ( isset($property->address->latitude) ) ? (string)$property->address->latitude : '' ) );
					update_post_meta( $post_id, '_longitude', ( ( isset($property->address->longitude) ) ? (string)$property->address->longitude : '' ) );
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
								if ( trim($property->address->name) != '' ) { $address_to_geocode[] = (string)$property->address->name; }
								if ( trim($property->address->number) != '' ) { $address_to_geocode[] = (string)$property->address->number; }
								if ( trim($property->address->street) != '' ) { $address_to_geocode[] = (string)$property->address->street; }
								if ( trim($property->address->locality) != '' ) { $address_to_geocode[] = (string)$property->address->locality; }
								if ( trim($property->address->town) != '' ) { $address_to_geocode[] = (string)$property->address->town; }
								if ( trim($property->address->county) != '' ) { $address_to_geocode[] = (string)$property->address->county; }
								if ( trim($property->address->postcode) != '' ) { $address_to_geocode[] = (string)$property->address->postcode; }

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
				if ( isset($property->sale) &&  (string)$property->sale == 'false' )
				{
					$department = 'residential-lettings';
				}
				update_post_meta( $post_id, '_department', $department );
				update_post_meta( $post_id, '_bedrooms', ( ( isset($property->bedrooms) ) ? (string)$property->bedrooms : '' ) );
				update_post_meta( $post_id, '_bathrooms', '' );
				update_post_meta( $post_id, '_reception_rooms', '' );

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

				if ( isset($property->type) )
				{
					if ( !empty($mapping) && isset($mapping[(string)$property->type]) )
					{
						wp_set_post_terms( $post_id, $mapping[(string)$property->type], $prefix . 'property_type' );
					}
					else
					{
						$this->add_log( 'Property received with a type (' . (string)$property->type . ') that is not mapped', (string)$property->id );

						$options = $this->add_missing_mapping( $mapping, $prefix . 'property_type', (string)$property->type, $import_id );
					}
				}

				// Residential Sales Details
				if ( $department == 'residential-sales' )
				{
					// Clean price
					$price = round(preg_replace("/[^0-9.]/", '', (string)$property->price));

					update_post_meta( $post_id, '_price', $price );
					update_post_meta( $post_id, '_price_actual', $price );
					update_post_meta( $post_id, '_poa', '' );

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
				}
				elseif ( $department == 'residential-lettings' )
				{
					// Clean price
					$price = round(preg_replace("/[^0-9.]/", '', (string)$property->price));

					update_post_meta( $post_id, '_rent', $price );

					$rent_frequency = 'pcm';
					$price_actual = $price;
					switch ((string)$property->rentFrequency)
					{
						case "per month": { $rent_frequency = 'pcm'; $price_actual = $price; break; }
						case "per week": { $rent_frequency = 'pw'; $price_actual = ($price * 52) / 12; break; }
					}
					update_post_meta( $post_id, '_rent_frequency', $rent_frequency );
					update_post_meta( $post_id, '_price_actual', $price_actual );
					
					update_post_meta( $post_id, '_poa', '' );

					update_post_meta( $post_id, '_deposit', '' );
            		update_post_meta( $post_id, '_available_date', '' );
				}

				// Marketing
				update_post_meta( $post_id, '_on_market', ( ( isset($property->status) && ( (string)$property->status == 'Sold' || (string)$property->status == 'Let' ) ) ? '' : 'yes' ) );
				update_post_meta( $post_id, '_featured', ( in_array((string)$property->id, $this->featured_properties) ? 'yes' : '' ) );

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
				if ( !empty($mapping) && isset($property->status) && isset($mapping[(string)$property->status]) )
				{
	                wp_set_post_terms( $post_id, $mapping[(string)$property->status], 'availability' );
	            }

	            // Features
				$features = array();
				if ( isset($property->features) && !empty($property->features) )
				{
					foreach ( $property->features as $property_features )
					{
						foreach ( $property_features as $feature )
						{
							$features[] = trim((string)$feature);
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

		        // Rooms
		        // For now put the whole description in one room
		        $i = 0;
		        if ( isset($property->description) && (string)$property->description != '' )
		        {
		        	update_post_meta( $post_id, '_room_name_' . $i, '' );
		            update_post_meta( $post_id, '_room_dimensions_' . $i, '' );
		            update_post_meta( $post_id, '_room_description_' . $i, (string)$property->description );

		            ++$i;
		        }
		        if ( isset($property->location) && (string)$property->location != '' )
		        {
		        	update_post_meta( $post_id, '_room_name_' . $i, 'Location' );
		            update_post_meta( $post_id, '_room_dimensions_' . $i, '' );
		            update_post_meta( $post_id, '_room_description_' . $i, (string)$property->location );

		            ++$i;
		        }
		        if (isset($property->floors) && !empty($property->floors))
                {
                    foreach ($property->floors as $floor)
                    {
                        if (!empty($floors->floor))
                        {
                            foreach ($floors->floor as $floor)
                            {
                            	if (isset($floor->rooms) && !empty($floor->rooms))
				                {
				                    foreach ($floor->rooms as $rooms)
				                    {
				                        if (!empty($rooms->room))
				                        {
				                            foreach ($rooms->room as $room)
				                            {
				                            	update_post_meta( $post_id, '_room_name_' . $i, ( ( isset($room->name) ) ? (string)$room->name : '' ) );
									            update_post_meta( $post_id, '_room_dimensions_' . $i, ( ( isset($room->size) ) ? (string)$room->size : '' ) );
									            update_post_meta( $post_id, '_room_description_' . $i, ( ( isset($room->description) ) ? (string)$room->description : '' ) );

									            ++$i;
				                            }
				                        }
				                    }
				                }
                            }
                        }
                    }
                }

	            update_post_meta( $post_id, '_rooms', $i );

	            // Media - Images
	            if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();
    				if (isset($property->photos) && !empty($property->photos))
	                {
	                    foreach ($property->photos as $photos)
	                    {
	                        if (!empty($photos->photo))
	                        {
	                            foreach ($photos->photo as $photo)
	                            {
									if ( 
										substr( strtolower((string)$photo->url), 0, 2 ) == '//' || 
										substr( strtolower((string)$photo->url), 0, 4 ) == 'http'
									)
									{
										// This is a URL
										$url = (string)$photo->url;

										$media_urls[] = array('url' => $url);
									}
								}
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

					if (isset($property->photos) && !empty($property->photos))
	                {
	                    foreach ($property->photos as $photos)
	                    {
	                        if (!empty($photos->photo))
	                        {
	                            foreach ($photos->photo as $photo)
	                            {
									if ( 
										substr( strtolower((string)$photo->url), 0, 2 ) == '//' || 
										substr( strtolower((string)$photo->url), 0, 4 ) == 'http'
									)
									{
										// This is a URL
										$url = (string)$photo->url;
										$description = ( (isset($photo->caption)) ? (string)$photo->caption : '' );

										$modified = ( (isset($photo->modified)) ? (string)$photo->modified : '' );

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
										$url = (string)$floorplan->url;

										$media_urls[] = array('url' => $url);
									}
								}
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
										$url = (string)$floorplan->url;
										$description = ( (isset($floorplan->caption)) ? (string)$floorplan->caption : '' );

										$modified = ( (isset($floorplan->modified)) ? (string)$floorplan->modified : '' );
									    
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

										        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), (string)$property->id );
										    }
										    else
										    {
											    $id = media_handle_sideload( $file_array, $post_id, $description, array('post_title' => $filename) );

											    // Check for handle sideload errors.
											    if ( is_wp_error( $id ) ) 
											    {
											        @unlink( $file_array['tmp_name'] );
											        
											        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), (string)$property->id );
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

					$this->add_log( 'Imported ' . count($media_ids) . ' floorplans (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->id );
				}

				// Media - Brochures
				/*$media_ids = array();
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

									        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), (string)$property->id );
									    }
									    else
									    {
										    $id = media_handle_sideload( $file_array, $post_id, $description, array('post_title' => $filename) );

										    // Check for handle sideload errors.
										    if ( is_wp_error( $id ) ) 
										    {
										        @unlink( $file_array['tmp_name'] );
										        
										        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), (string)$property->id );
										    }
										    else
										    {
										    	$media_ids[] = $id;

										    	update_post_meta( $id, '_imported_url', $url);
										    	update_post_meta( $id, '_modified', $modified);
										    }
										}
									}
								}
							}
						}
					}
				}
				update_post_meta( $post_id, '_brochures', $media_ids );

				$this->add_log( 'Imported ' . count($media_ids) . ' brochures (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->id );*/

				// Media - EPCs
				if ( get_option('propertyhive_epcs_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();

    				if (
						isset($property->epcgraph) &&
						(
							substr( strtolower((string)$property->epcgraph), 0, 2 ) == '//' || 
							substr( strtolower((string)$property->epcgraph), 0, 4 ) == 'http'
						)
					)
	                {
						// This is a URL
						$url = (string)$property->epcgraph;

						$media_urls[] = array('url' => $url);
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
					if (
						isset($property->epcgraph) &&
						(
							substr( strtolower((string)$property->epcgraph), 0, 2 ) == '//' || 
							substr( strtolower((string)$property->epcgraph), 0, 4 ) == 'http'
						)
					)
	                {
						// This is a URL
						$url = (string)$property->epcgraph;
						$description = '';
					    
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
						    $file_array = array(
						        'name' => $filename,
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
							        
							        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), (string)$property->id );
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

					$this->add_log( 'Imported ' . count($media_ids) . ' EPCs (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->id );
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

				$this->add_log( 'Imported ' . count($virtual_tours) . ' virtual tours', (string)$property->id );*/

				do_action( "propertyhive_property_imported_domus_xml", $post_id, $property );

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

		do_action( "propertyhive_post_import_properties_domus_xml" );

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

				do_action( "propertyhive_property_removed_domus_xml", $post->ID );
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
                'Sold Subject to Contract' => 'Sold Subject to Contract',
                'Sold' => 'Sold',
                'Let Subject to Contract' => 'Let Subject to Contract',
                'Let' => 'Let',
            );
        }
        if ($custom_field == 'sales_availability')
        {
            return array(
                'Available' => 'Available',
                'Under Offer' => 'Under Offer',
                'Sold Subject to Contract' => 'Sold Subject to Contract',
                'Sold' => 'Sold',
            );
        }
        if ($custom_field == 'lettings_availability')
        {
            return array(
                'Available' => 'Available',
                'Let Subject to Contract' => 'Let Subject to Contract',
                'Let' => 'Let',
            );
        }
        if ($custom_field == 'property_type')
        {
        	return array(
                'Detached' => 'Detached',
                'Semi-Detached' => 'Semi-Detached',
                'End Terraced' => 'End Terraced',
                'Flat' => 'Flat',
                'Studio' => 'Studio',
            );
        }
        if ($custom_field == 'price_qualifier')
        {
        	return array(
        		'Guide Price' => 'Guide Price',
        		'Offers in Excess of' => 'Offers in Excess of',
        	);
        }
        if ($custom_field == 'tenure')
        {
            return array(
                'Freehold' => 'Freehold',
                'Leasehold' => 'Leasehold',
            );
        }
    }

}

}