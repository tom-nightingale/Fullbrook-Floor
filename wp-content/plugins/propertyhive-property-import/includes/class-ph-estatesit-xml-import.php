<?php
/**
 * Class for managing the import process of an EstatesIT XML file
 *
 * @package WordPress
 */
if ( class_exists( 'PH_Property_Import_Process' ) ) {

class PH_EstatesIT_XML_Import extends PH_Property_Import_Process {

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
				if ( isset($property->market) && (string)$property->market == 1 )
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

        do_action( "propertyhive_pre_import_properties_estatesit_xml", $this->properties );
        $this->properties = apply_filters( "propertyhive_estatesit_xml_properties_due_import", $this->properties );

		$this->add_log( 'Beginning to loop through ' . count($this->properties) . ' properties' );

		$property_row = 1;
		foreach ( $this->properties as $property )
		{
			$this->add_log( 'Importing property ' . $property_row .' with reference ' . (string)$property->propcode, (string)$property->propcode );

			$inserted_updated = false;

            $display_address = (string)$property->address3;
            if ( (string)$property->address4 != '' )
            {
                if ($display_address != '')
                {
                    $display_address .= ', ';
                }
                $display_address .= (string)$property->address4;
            }
            if ( (string)$property->address5 != '' )
            {
                if ($display_address != '')
                {
                    $display_address .= ', ';
                }
                $display_address .= (string)$property->address5;
            }

			$args = array(
	            'post_type' => 'property',
	            'posts_per_page' => 1,
	            'post_status' => 'any',
	            'meta_query' => array(
	            	array(
		            	'key' => $imported_ref_key,
		            	'value' => (string)$property->propcode
		            )
	            )
	        );
	        $property_query = new WP_Query($args);
	        
	        if ($property_query->have_posts())
	        {
	        	$this->add_log( 'This property has been imported before. Updating it', (string)$property->propcode );

	        	// We've imported this property before
	            while ($property_query->have_posts())
	            {
	                $property_query->the_post();

	                $post_id = get_the_ID();

	                $my_post = array(
				    	'ID'          	 => $post_id,
				    	'post_title'     => wp_strip_all_tags( $display_address ),
				    	'post_excerpt'   => (string)$property->description,
				    	'post_content' 	 => '',
				    	'post_status'    => 'publish',
				  	);

				 	// Update the post into the database
				    $post_id = wp_update_post( $my_post );

				    if ( is_wp_error( $post_id ) ) 
					{
						$this->add_error( 'ERROR: Failed to update post. The error was as follows: ' . $post_id->get_error_message(), (string)$property->propcode );
					}
					else
					{
						$inserted_updated = 'updated';
					}
	            }
	        }
	        else
	        {
	        	$this->add_log( 'This property hasn\'t been imported before. Inserting it', (string)$property->propcode );

	        	// We've not imported this property before
				$postdata = array(
					'post_excerpt'   => (string)$property->description,
					'post_content' 	 => '',
					'post_title'     => wp_strip_all_tags( $display_address ),
					'post_status'    => 'publish',
					'post_type'      => 'property',
					'comment_status' => 'closed',
				);

				$post_id = wp_insert_post( $postdata, true );

				if ( is_wp_error( $post_id ) ) 
				{
					$this->add_error( 'Failed to insert post. The error was as follows: ' . $post_id->get_error_message(), (string)$property->propcode );
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
					($display_address != '' || (string)$property->description != '')
				)
				{
					$my_post = array(
				    	'ID'          	 => $post_id,
				    	'post_title'     => htmlentities(mb_convert_encoding(wp_strip_all_tags( $display_address ), 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
				    	'post_excerpt'   => htmlentities(mb_convert_encoding((string)$property->description, 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
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

				$this->add_log( 'Successfully ' . $inserted_updated . ' post. The post ID is ' . $post_id, (string)$property->propcode );

				update_post_meta( $post_id, $imported_ref_key, (string)$property->propcode );

				// Address
				update_post_meta( $post_id, '_reference_number', (string)$property->propcode );
				update_post_meta( $post_id, '_address_name_number', trim( ( ( isset($property->address0) ) ? (string)$property->address0 : '' ) . ' ' . ( ( isset($property->address1) ) ? (string)$property->address1 : '' ) . ' ' . ( ( isset($property->address2) ) ? (string)$property->address2 : '' ) ) );
				update_post_meta( $post_id, '_address_street', ( ( isset($property->address3) ) ? (string)$property->address3 : '' ) );
				update_post_meta( $post_id, '_address_two', ( ( isset($property->address4) ) ? (string)$property->address4 : '' ) );
				update_post_meta( $post_id, '_address_three', ( ( isset($property->address5) ) ? (string)$property->address5 : '' ) );
				update_post_meta( $post_id, '_address_four', ( ( isset($property->address6) ) ? (string)$property->address6 : '' ) );
				update_post_meta( $post_id, '_address_postcode', ( ( isset($property->postcode) ) ? (string)$property->postcode : '' ) );

				$country = 'GB';
				update_post_meta( $post_id, '_address_country', $country );

            	$address_fields_to_check = apply_filters( 'propertyhive_estatesit_xml_address_fields_to_check', array('address4', 'address5', 'address6') );
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

				// Coordinates
				if ( isset($property->gcodey) && isset($property->gcodex) && (string)$property->gcodey != '' && (string)$property->gcodex != '' && (string)$property->gcodey != '0' && (string)$property->gcodex != '0' )
				{
					update_post_meta( $post_id, '_latitude', ( ( isset($property->gcodey) ) ? (string)$property->gcodey : '' ) );
					update_post_meta( $post_id, '_longitude', ( ( isset($property->gcodex) ) ? (string)$property->gcodex : '' ) );
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
								if ( trim($property->address3) != '' ) { $address_to_geocode[] = (string)$property->address3; }
								if ( trim($property->address4) != '' ) { $address_to_geocode[] = (string)$property->address4; }
								if ( trim($property->address5) != '' ) { $address_to_geocode[] = (string)$property->address5; }
								if ( trim($property->address6) != '' ) { $address_to_geocode[] = (string)$property->address6; }
								if ( trim($property->postcode) != '' ) { $address_to_geocode[] = (string)$property->postcode; }

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
							        	$this->add_error( 'Google Geocoding service returned status ' . $status, (string)$property->propcode );
							        	sleep(3);
							        }
							    }
							    else
						        {
						        	$this->add_error( 'Failed to parse XML response from Google Geocoding service.', (string)$property->propcode );
						        }
							}
							else
					        {
					        	$this->add_error( 'Failed to obtain co-ordinates as allow_url_fopen setting is disabled', (string)$property->propcode );
					        }
				        }
					    else
					    {
					    	$this->add_log( 'Not performing Google Geocoding request as no API key present in settings', (string)$property->propcode );
					    }
					}
				}

				// Owner
				add_post_meta( $post_id, '_owner_contact_id', '', true );

				// Record Details
				add_post_meta( $post_id, '_negotiator_id', get_current_user_id(), true );
					
				$office_id = $primary_office_id;
				if ( isset($_POST['mapped_office'][(string)$property->branch]) && $_POST['mapped_office'][(string)$property->branch] != '' )
				{
					$office_id = $_POST['mapped_office'][(string)$property->branch];
				}
				elseif ( isset($options['offices']) && is_array($options['offices']) && !empty($options['offices']) )
				{
					foreach ( $options['offices'] as $ph_office_id => $branch_code )
					{
						if ( $branch_code == (string)$property->branch )
						{
							$office_id = $ph_office_id;
							break;
						}
					}
				}
				update_post_meta( $post_id, '_office_id', $office_id );

				// Residential Details
				$department = 'residential-sales';
				if ( (string)$property->pricetype != '1' )
				{
					$department = 'residential-lettings';
				}

				// Is the property portal add on activated
				if (class_exists('PH_Property_Portal'))
        		{
        			if ( 
        				isset($branch_mappings[str_replace("residential-", "", $department)][(string)$property->branch]) &&
        				$branch_mappings[str_replace("residential-", "", $department)][(string)$property->branch] != ''
        			)
        			{
        				$explode_agent_branch = explode("|", $branch_mappings[str_replace("residential-", "", $department)][(string)$property->branch]);
        				update_post_meta( $post_id, '_agent_id', $explode_agent_branch[0] );
        				update_post_meta( $post_id, '_branch_id', $explode_agent_branch[1] );

        				$this->branch_ids_processed[] = $explode_agent_branch[1];
        			}
        		}

				update_post_meta( $post_id, '_department', $department );
				update_post_meta( $post_id, '_bedrooms', ( ( isset($property->propbedr) ) ? (string)$property->propbedr : '' ) );
				update_post_meta( $post_id, '_bathrooms', ( ( isset($property->propbath) ) ? (string)$property->propbath : '' ) );
				update_post_meta( $post_id, '_reception_rooms', ( ( isset($property->proprecp) ) ? (string)$property->proprecp : '' ) );

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

				if ( isset($property->proptype) )
				{
					if ( !empty($mapping) && isset($mapping[(string)$property->proptype]) )
					{
						wp_set_post_terms( $post_id, $mapping[(string)$property->proptype], $prefix . 'property_type' );
					}
					else
					{
						$this->add_log( 'Property received with a type (' . (string)$property->proptype . ') that is not mapped', (string)$property->propcode );

						$options = $this->add_missing_mapping( $mapping, $prefix . 'property_type', (string)$property->proptype, $import_id );
					}
				}

				// Residential Sales Details
				if ( $department == 'residential-sales' )
				{
					// Clean price
					$price = round(preg_replace("/[^0-9.]/", '', (string)$property->priceask));

					update_post_meta( $post_id, '_price', $price );
					update_post_meta( $post_id, '_price_actual', $price );
					update_post_meta( $post_id, '_poa', ( ( isset($property->priceaskp) && $property->priceaskp == 'Price On Application' ) ? 'yes' : '') );

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
					if ( !empty($mapping) && isset($property->priceaskp) && isset($mapping[(string)$property->priceaskp]) )
					{
		                wp_set_post_terms( $post_id, $mapping[(string)$property->priceaskp], 'price_qualifier' );
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
					if ( !empty($mapping) && isset($property->proptenu) && isset($mapping[(string)$property->proptenu]) )
					{
			            wp_set_post_terms( $post_id, $mapping[(string)$property->proptenu], 'tenure' );
		            }
				}
				else
				{
					// Clean price
					$price = round(preg_replace("/[^0-9.]/", '', (string)$property->priceask));

					update_post_meta( $post_id, '_rent', $price );

					$rent_frequency = 'pcm';
					$price_actual = $price;
					switch ((string)$property->pricetype)
					{
						case "2": { $rent_frequency = 'pw'; $price_actual = ($price * 52) / 12; break; }
						case "3": { $rent_frequency = 'pcm'; $price_actual = $price; break; }
						case "4": { $rent_frequency = 'pq'; $price_actual = ($price * 4) / 12; break; }
						case "5": { $rent_frequency = 'pa'; $price_actual = $price / 12; break; }
					}
					update_post_meta( $post_id, '_rent_frequency', $rent_frequency );
					update_post_meta( $post_id, '_price_actual', $price_actual );

					update_post_meta( $post_id, '_currency', 'GBP' );
					
					update_post_meta( $post_id, '_poa', ( ( isset($property->priceaskp) && $property->priceaskp == 'Price On Application' ) ? 'yes' : '') );

					update_post_meta( $post_id, '_deposit', '' );

					$available_date = isset($property->availabledate) ? (string)$property->availabledate : '';
					if ( $available_date != '' )
					{
						$available_date = substr($available_date, 0, 4) . '-' . substr($available_date, 4, 2) . '-' . substr($available_date, 6, 2);
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
					if ( !empty($mapping) && isset($property->furnished) && isset($mapping[(string)$property->furnished]) )
					{
		                wp_set_post_terms( $post_id, $mapping[(string)$property->furnished], 'furnished' );
		            }
				}

				// Marketing
				update_post_meta( $post_id, '_on_market', 'yes' );
				update_post_meta( $post_id, '_featured', ( isset($property->featuredproperty) && (string)$property->featuredproperty == '1' ) ? 'yes' : '' );

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
				if ( !empty($mapping) && isset($property->propstat) && isset($mapping[(string)$property->propstat]) )
				{
	                wp_set_post_terms( $post_id, $mapping[(string)$property->propstat], 'availability' );
	            }

	            // Features
				$features = array();
				for ( $i = 1; $i <= 10; ++$i )
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
		        if ( $department == 'commercial' )
				{
					/*update_post_meta( $post_id, '_descriptions', '1' );
					update_post_meta( $post_id, '_description_name_0', '' );
		            update_post_meta( $post_id, '_description_0', str_replace(array("\r\n", "\n"), "", (string)$property->fullDescription) );*/
				}
				else
				{
					update_post_meta( $post_id, '_rooms', '1' );
					update_post_meta( $post_id, '_room_name_0', '' );
		            update_post_meta( $post_id, '_room_dimensions_0', '' );
		            update_post_meta( $post_id, '_room_description_0', str_replace(array("\r\n", "\n"), "", (string)$property->rooms) );
		        }

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
										isset($photo->urlname) &&
										(
											substr( strtolower((string)$photo->urlname), 0, 2 ) == '//' || 
											substr( strtolower((string)$photo->urlname), 0, 4 ) == 'http'
										)
									)
									{
										// This is a URL
										$url = (string)$photo->urlname;

										$media_urls[] = array('url' => $url);
									}
								}
							}
						}
					}
					update_post_meta( $post_id, '_photo_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' photo URLs', (string)$property->propcode );
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
										isset($photo->urlname) &&
										(
											substr( strtolower((string)$photo->urlname), 0, 2 ) == '//' || 
											substr( strtolower((string)$photo->urlname), 0, 4 ) == 'http'
										)
									)
									{
										// This is a URL
										$url = (string)$photo->urlname;
										$description = (string)$photo->caption;

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
										        'name' => basename( $url ),
										        'tmp_name' => $tmp
										    );

										    // Check for download errors
										    if ( is_wp_error( $tmp ) ) 
										    {
										        @unlink( $file_array[ 'tmp_name' ] );

										        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), (string)$property->propcode );
										    }
										    else
										    {
											    $id = media_handle_sideload( $file_array, $post_id, $description, array('post_title' => $filename) );

											    // Check for handle sideload errors.
											    if ( is_wp_error( $id ) ) 
											    {
											        @unlink( $file_array['tmp_name'] );
											        
											        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), (string)$property->propcode );
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

					$this->add_log( 'Imported ' . count($media_ids) . ' photos (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->propcode );
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
										isset($floorplan->urlname) &&
										(
											substr( strtolower((string)$floorplan->urlname), 0, 2 ) == '//' || 
											substr( strtolower((string)$floorplan->urlname), 0, 4 ) == 'http'
										)
									)
									{
										// This is a URL
										$url = (string)$floorplan->urlname;

										$media_urls[] = array('url' => $url);
									}
								}
							}
						}
					}
					update_post_meta( $post_id, '_floorplan_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' floorplan URLs', (string)$property->propcode );
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
										isset($floorplan->urlname) &&
										(
											substr( strtolower((string)$floorplan->urlname), 0, 2 ) == '//' || 
											substr( strtolower((string)$floorplan->urlname), 0, 4 ) == 'http'
										)
									)
									{
										// This is a URL
										$url = (string)$floorplan->urlname;
										$description = (string)$floorplan->caption;
									    
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

										        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), (string)$property->propcode );
										    }
										    else
										    {
											    $id = media_handle_sideload( $file_array, $post_id, $description, array('post_title' => $filename) );

											    // Check for handle sideload errors.
											    if ( is_wp_error( $id ) ) 
											    {
											        @unlink( $file_array['tmp_name'] );
											        
											        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), (string)$property->propcode );
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

					$this->add_log( 'Imported ' . count($media_ids) . ' floorplans (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->propcode );
				}

				// Media - Brochures
				if ( get_option('propertyhive_brochures_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();
    				if (isset($property->pdf) && !empty($property->pdf))
	                {
						if ( 
							substr( strtolower((string)$property->pdf), 0, 2 ) == '//' || 
							substr( strtolower((string)$property->pdf), 0, 4 ) == 'http'
						)
						{
							// This is a URL
							$url = (string)$property->pdf;

							$media_urls[] = array('url' => $url);
						}
					}
					update_post_meta( $post_id, '_brochure_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' brochure URLs', (string)$property->propcode );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_brochures', TRUE );
					if (isset($property->pdf) && !empty($property->pdf))
	                {
						if ( 
							substr( strtolower((string)$property->pdf), 0, 2 ) == '//' || 
							substr( strtolower((string)$property->pdf), 0, 4 ) == 'http'
						)
						{
							// This is a URL
							$url = (string)$property->pdf;
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

							        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), (string)$property->propcode );
							    }
							    else
							    {
								    $id = media_handle_sideload( $file_array, $post_id, $description, array('post_title' => $filename) );

								    // Check for handle sideload errors.
								    if ( is_wp_error( $id ) ) 
								    {
								        @unlink( $file_array['tmp_name'] );
								        
								        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), (string)$property->propcode );
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

					$this->add_log( 'Imported ' . count($media_ids) . ' brochures (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->propcode );
				}

				// Media - EPCs
				if ( get_option('propertyhive_epcs_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();

    				if (isset($property->linkepc) && !empty($property->linkepc))
	                {
						if ( 
							substr( strtolower((string)$property->linkepc), 0, 2 ) == '//' || 
							substr( strtolower((string)$property->linkepc), 0, 4 ) == 'http'
						)
						{
							// This is a URL
							$url = (string)$property->linkepc;

							$media_urls[] = array('url' => $url);
						}
					}
					if (isset($property->epcgraph) && !empty($property->epcgraph))
	                {
						if ( 
							substr( strtolower((string)$property->epcgraph), 0, 2 ) == '//' || 
							substr( strtolower((string)$property->epcgraph), 0, 4 ) == 'http'
						)
						{
							// This is a URL
							$url = (string)$property->epcgraph;

							$media_urls[] = array('url' => $url);
						}
					}

					update_post_meta( $post_id, '_epc_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' EPC URLs', (string)$property->propcode );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_epcs', TRUE );
					if (isset($property->linkepc) && !empty($property->linkepc))
	                {
						if ( 
							substr( strtolower((string)$property->linkepc), 0, 2 ) == '//' || 
							substr( strtolower((string)$property->linkepc), 0, 4 ) == 'http'
						)
						{
							// This is a URL
							$url = (string)$property->linkepc;
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

							        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), (string)$property->propcode );
							    }
							    else
							    {
								    $id = media_handle_sideload( $file_array, $post_id, $description, array('post_title' => $filename) );

								    // Check for handle sideload errors.
								    if ( is_wp_error( $id ) ) 
								    {
								        @unlink( $file_array['tmp_name'] );
								        
								        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), (string)$property->propcode );
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
					if (isset($property->epcgraph) && !empty($property->epcgraph))
	                {
						if ( 
							substr( strtolower((string)$property->epcgraph), 0, 2 ) == '//' || 
							substr( strtolower((string)$property->epcgraph), 0, 4 ) == 'http'
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

							        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), (string)$property->propcode );
							    }
							    else
							    {
								    $id = media_handle_sideload( $file_array, $post_id, $description, array('post_title' => $filename) );

								    // Check for handle sideload errors.
								    if ( is_wp_error( $id ) ) 
								    {
								        @unlink( $file_array['tmp_name'] );
								        
								        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), (string)$property->propcode );
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

					$this->add_log( 'Imported ' . count($media_ids) . ' EPCs (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->propcode );
				}

				// Media - Virtual Tours
				$virtual_tours = array();
				if (isset($property->link360) && !empty($property->link360))
                {
                    $virtual_tours[] = (string)$property->link360;
                }

                update_post_meta( $post_id, '_virtual_tours', count($virtual_tours) );
                foreach ($virtual_tours as $i => $virtual_tour)
                {
                	update_post_meta( $post_id, '_virtual_tour_' . $i, (string)$virtual_tour );
                }

				$this->add_log( 'Imported ' . count($virtual_tours) . ' virtual tours', (string)$property->propcode );

				do_action( "propertyhive_property_imported_estatesit_xml", $post_id, $property );

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
							$this->add_log( 'New meta data for ' . trim($key, '_') . ': ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), (string)$property->propcode );
						}
						elseif ( $metadata_before[$key] != $metadata_after[$key] )
						{
							$this->add_log( 'Updated ' . trim($key, '_') . '. Before: ' . ( ( is_array($metadata_before[$key]) ) ? implode(", ", $metadata_before[$key]) : $metadata_before[$key] ) . ', After: ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), (string)$property->propcode );
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
							$this->add_log( 'New taxonomy data for ' . $taxonomy_name . ': ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), (string)$property->propcode );
						}
						elseif ( $taxonomy_terms_before[$taxonomy_name] != $taxonomy_terms_after[$taxonomy_name] )
						{
							$this->add_log( 'Updated ' . $taxonomy_name . '. Before: ' . ( ( is_array($taxonomy_terms_before[$taxonomy_name]) ) ? implode(", ", $taxonomy_terms_before[$taxonomy_name]) : $taxonomy_terms_before[$taxonomy_name] ) . ', After: ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), (string)$property->propcode );
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

		do_action( "propertyhive_post_import_properties_estatesit_xml" );

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
				$import_refs[] = (string)$property->propcode;
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
					'key'     => '_branch_id',
					'value'   => $this->branch_ids_processed,
					'compare' => 'IN',
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

					do_action( "propertyhive_property_removed_estatesit_xml", $post->ID );
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
                'BOM' => 'Back On Market',
                'Exchanged' => 'Exchanged',
                'Let' => 'Let',
                'Let Agreed' => 'Let Agreed',
                'Promotion' => 'Promotion',
                'Reserved' => 'Reserved',
                'SSTC' => 'SSTC',
                'Sold' => 'Sold',
                'Unavailable' => 'Unavailable',
                'Under Offer' => 'Under Offer',
                'Valuation' => 'Valuation',
                'Withdrawn' => 'Withdrawn',
            );
        }
        if ($custom_field == 'sales_availability')
        {
            return array(
                'Available' => 'Available',
                'BOM' => 'Back On Market',
                'Exchanged' => 'Exchanged',
                'Promotion' => 'Promotion',
                'Reserved' => 'Reserved',
                'SSTC' => 'SSTC',
                'Sold' => 'Sold',
                'Unavailable' => 'Unavailable',
                'Under Offer' => 'Under Offer',
                'Valuation' => 'Valuation',
                'Withdrawn' => 'Withdrawn',
            );
        }
        if ($custom_field == 'lettings_availability')
        {
            return array(
                'Available' => 'Available',
                'BOM' => 'Back On Market',
                'Let' => 'Let',
                'Let Agreed' => 'Let Agreed',
                'Promotion' => 'Promotion',
                'Reserved' => 'Reserved',
                'Unavailable' => 'Unavailable',
                'Valuation' => 'Valuation',
                'Withdrawn' => 'Withdrawn',
            );
        }
        if ($custom_field == 'property_type')
        {
        	return array(
                'Apartment' => 'Apartment',
                'Barn Conversion' => 'Barn Conversion',
                'Bedsit' => 'Bedsit',
                'Building Plot' => 'Building Plot',
                'Bungalow' => 'Bungalow',
                'Commercial' => 'Commercial',
                'Conversion' => 'Conversion',
                'Cottage' => 'Cottage',
                'Detached' => 'Detached',
                'Duplex' => 'Duplex',
                'End Of Terrace' => 'End Of Terrace',
                'Flat' => 'Flat',
                'Flatshare' => 'Flatshare',
                'Garage' => 'Garage',
                'House' => 'House',
                'House Share' => 'House Share',
                'Houseboat' => 'Houseboat',
                'Land' => 'Land',
                'Light Industrial' => 'Light Industrial',
                'Live/Work' => 'Live/Work',
                'Loft' => 'Loft',
                'Maisonette' => 'Maisonette',
                'Mansion Block' => 'Mansion Block',
                'Mews' => 'Mews',
                'Mobile Home' => 'Mobile Home',
                'Office' => 'Office',
                'Parking' => 'Parking',
                'Penthouse' => 'Penthouse',
                'Public House' => 'Public House',
                'Purpose Built' => 'Purpose Built',
                'Restaurant' => 'Restaurant',
                'Retirement' => 'Retirement',
                'Room To Let' => 'Room To Let',
                'Semi Detached' => 'Semi Detached',
                'Serviced Apartment' => 'Serviced Apartment',
                'Shop' => 'Shop',
                'Studio' => 'Studio',
                'Studio Space' => 'Studio Space',
                'Terraced' => 'Terraced',
                'Town House' => 'Town House',
                'Warehouse' => 'Warehouse',
                'Warehouse Conversion' => 'Warehouse Conversion',
            );
        }
        if ($custom_field == 'price_qualifier')
        {
        	return array(
        		'Asking price of' => 'Asking price of',
        		'Auction guide price of' => 'Auction guide price of',
        		'Fixed Price' => 'Fixed Price',
        		'Guide Price' => 'Guide Price',
        		'Keen to sell' => 'Keen to sell',
        		'Must be seen' => 'Must be seen',
        		'Offers above' => 'Offers above',
        		'Offers in excess of' => 'Offers in excess of',
        		'Offers in the region of' => 'Offers in the region of',
        		'Prices from' => 'Prices from',
        		'Reduced' => 'Reduced',
        		'Reduced for Quick Sale' => 'Reduced for Quick Sale',
        		'Sale by Tender' => 'Sale by Tender',
        		'Subject to contract' => 'Subject to contract',
        	);
        }
        if ($custom_field == 'tenure')
        {
            return array(
                'Freehold' => 'Freehold',
                'Leasehold' => 'Leasehold',
                'LH+ShareFH' => 'LH+ShareFH',
                'Share of Freehold' => 'Share of Freehold',
            );
        }
        if ($custom_field == 'furnished')
        {
            return array(
            	'Fully Furnished' => 'Fully Furnished',
            	'Part Furnished' => 'Part Furnished',
            	'Unfurnished' => 'Unfurnished',
            	'Furnished' => 'Furnished',
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