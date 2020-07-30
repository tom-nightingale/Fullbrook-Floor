<?php
/**
 * Class for managing the import process of a REAXML file
 *
 * @package WordPress
 */
if ( class_exists( 'PH_Property_Import_Process' ) ) {

class PH_REAXML_Import extends PH_Property_Import_Process {

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
            
            if (isset($xml->residential))
            {
				foreach ($xml->residential as $property)
				{
					$property->addChild('department', 'residential-sales');
	                $this->properties[] = $property;
	            } // end foreach property
	        }

	        if (isset($xml->rental))
            {
				foreach ($xml->rental as $property)
				{
					$property->addChild('department', 'residential-lettings');
	                $this->properties[] = $property;
	            } // end foreach property
	        }
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

        do_action( "propertyhive_pre_import_properties_reaxml", $this->properties );
        $this->properties = apply_filters( "propertyhive_reaxml_properties_due_import", $this->properties );

		$this->add_log( 'Beginning to loop through ' . count($this->properties) . ' properties' );

		$PH_Countries = new PH_Countries();

		$property_row = 1;
		foreach ( $this->properties as $property )
		{
			$this->add_log( 'Importing property ' . $property_row .' with reference ' . (string)$property->uniqueID, (string)$property->uniqueID );

			$property_attributes = $property->attributes();

			if ( $property_attributes['status'] != 'current' )
			{
				$args = array(
		            'post_type' => 'property',
		            'posts_per_page' => 1,
		            'post_status' => 'any',
		            'meta_query' => array(
		            	array(
			            	'key' => $imported_ref_key,
			            	'value' => (string)$property->uniqueID
			            )
		            )
		        );
		        $property_query = new WP_Query($args);
		        
		        if ($property_query->have_posts())
		        {
		        	// We've imported this property before
		            while ($property_query->have_posts())
		            {
		            	$property_query->the_post();

		            	update_post_meta( get_the_ID(), '_on_market', '' );

						$this->add_log( 'Property ' . get_the_ID() . ' marked as not on market', (string)$property->uniqueID );

						if ( isset($options['remove_action']) && $options['remove_action'] != '' )
						{
							if ( $options['remove_action'] == 'remove_all_media' || $options['remove_action'] == 'remove_all_media_except_first_image' )
							{
								// Remove all EPCs
								$this->delete_media( get_the_ID(), '_epcs' );

								// Remove all Brochures
								$this->delete_media( get_the_ID(), '_brochures' );

								// Remove all Floorplans
								$this->delete_media( get_the_ID(), '_floorplans' );

								// Remove all Images (except maybe the first)
								$this->delete_media( get_the_ID(), '_photos', ( ( $options['remove_action'] == 'remove_all_media_except_first_image' ) ? TRUE : FALSE ) );

								$this->add_log( 'Deleted property media', (string)$property->uniqueID );
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

						do_action( "propertyhive_property_removed_reaxml", get_the_ID() );
		            }
		        }

		        ++$property_row;

				continue;
			}

			// From this point forward we will be processing current properties

			$display_address = '';
			if ( (string)$property->address->street != '' )
			{
				$display_address .= (string)$property->address->street;
			}
			if ( (string)$property->address->suburb != '' )
			{
				$suburb_attributes = $property->address->suburb->attributes();
				if ( $suburb_attributes['display'] == 'yes' )
				{
					if ( $display_address != '' ) { $display_address .= ', '; }
					$display_address .= (string)$property->address->suburb;
				}
			}

			$inserted_updated = false;

			$args = array(
	            'post_type' => 'property',
	            'posts_per_page' => 1,
	            'post_status' => 'any',
	            'meta_query' => array(
	            	array(
		            	'key' => $imported_ref_key,
		            	'value' => (string)$property->uniqueID
		            )
	            )
	        );
	        $property_query = new WP_Query($args);
	        
	        if ($property_query->have_posts())
	        {
	        	$this->add_log( 'This property has been imported before. Updating it', (string)$property->uniqueID );

	        	// We've imported this property before
	            while ($property_query->have_posts())
	            {
	                $property_query->the_post();

	                $post_id = get_the_ID();

	                $my_post = array(
				    	'ID'          	 => $post_id,
				    	'post_title'     => wp_strip_all_tags( $display_address ),
				    	'post_excerpt'   => (string)$property->headline,
				    	'post_content' 	 => '',
				    	'post_status'    => 'publish',
				  	);

				 	// Update the post into the database
				    $post_id = wp_update_post( $my_post );

				    if ( is_wp_error( $post_id ) ) 
					{
						$this->add_error( 'ERROR: Failed to update post. The error was as follows: ' . $post_id->get_error_message(), (string)$property->uniqueID );
					}
					else
					{
						$inserted_updated = 'updated';
					}
	            }
	        }
	        else
	        {
	        	$this->add_log( 'This property hasn\'t been imported before. Inserting it', (string)$property->uniqueID );

	        	// We've not imported this property before
				$postdata = array(
					'post_excerpt'   => (string)$property->headline,
					'post_content' 	 => '',
					'post_title'     => wp_strip_all_tags( $display_address ),
					'post_status'    => 'publish',
					'post_type'      => 'property',
					'comment_status' => 'closed',
				);

				$post_id = wp_insert_post( $postdata, true );

				if ( is_wp_error( $post_id ) ) 
				{
					$this->add_error( 'Failed to insert post. The error was as follows: ' . $post_id->get_error_message(), (string)$property->uniqueID );
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
					($display_address != '' || (string)$property->headline != '')
				)
				{
					$my_post = array(
				    	'ID'          	 => $post_id,
				    	'post_title'     => htmlentities(mb_convert_encoding(wp_strip_all_tags( $display_address ), 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
				    	'post_excerpt'   => htmlentities(mb_convert_encoding((string)$property->headline, 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
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

				$this->add_log( 'Successfully ' . $inserted_updated . ' post. The post ID is ' . $post_id, (string)$property->uniqueID );

				update_post_meta( $post_id, $imported_ref_key, (string)$property->uniqueID );

				// Address
				update_post_meta( $post_id, '_reference_number', (string)$property->uniqueID );
				update_post_meta( $post_id, '_address_name_number', ( ( isset($property->address->streetNumber) ) ? (string)$property->address->streetNumber : '' ) );
				update_post_meta( $post_id, '_address_street', ( ( isset($property->address->street) ) ? (string)$property->address->street : '' ) );
				update_post_meta( $post_id, '_address_two', '' );
				update_post_meta( $post_id, '_address_three', ( ( isset($property->address->suburb ) ) ? (string)$property->address->suburb  : '' ) );
				update_post_meta( $post_id, '_address_four', ( ( isset($property->address->state) ) ? (string)$property->address->state : '' ) );
				update_post_meta( $post_id, '_address_postcode', ( ( isset($property->address->postcode) ) ? (string)$property->address->postcode : '' ) );

				$country = 'AU';
				/*if ( isset($property->country) && (string)$property->country != '' && class_exists('PH_Countries') )
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
				}*/
				update_post_meta( $post_id, '_address_country', $country );

				// Coordinates
				$lat = get_post_meta( $post_id, '_latitude', TRUE);
				$lng = get_post_meta( $post_id, '_longitude', TRUE);

				if ( $lat == '' || $lng == '' || $lat == '0' || $lng == '0' )
				{
					if ( ini_get('allow_url_fopen') )
					{
						// No lat lng. Let's get it
						$address_to_geocode = array();
						if ( isset($property->address->streetNumber) && trim((string)$property->address->streetNumber) != '' ) { $address_to_geocode[] = (string)$property->address->streetNumber; }
						if ( isset($property->address->street) && trim((string)$property->address->street) != '' ) { $address_to_geocode[] = (string)$property->address->street; }
						if ( isset($property->address->suburb) && trim((string)$property->address->suburb) != '' ) { $address_to_geocode[] = (string)$property->address->suburb; }
						if ( isset($property->address->state) && trim((string)$property->address->state) != '' ) { $address_to_geocode[] = (string)$property->address->state; }
						if ( isset($property->address->postcode) && trim((string)$property->address->postcode) != '' ) { $address_to_geocode[] = (string)$property->address->postcode; }

						$request_url = "https://maps.googleapis.com/maps/api/geocode/xml?address=" . urlencode( implode( ", ", $address_to_geocode ) ) . "&sensor=false&region=" . strtolower($country); // the request URL you'll send to google to get back your XML feed
	                    
						$api_key = get_option('propertyhive_google_maps_geocoding_api_key', '');
			            if ( $api_key == '' )
			            {
			                $api_key = get_option('propertyhive_google_maps_api_key', '');
			            }
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
					        	$this->add_error( 'Google Geocoding service returned status ' . $status, (string)$property->uniqueID );
					        	sleep(3);
					        }
					    }
					    else
				        {
				        	$this->add_error( 'Failed to parse XML response from Google Geocoding service.', (string)$property->uniqueID );
				        }
					}
					else
			        {
			        	$this->add_error( 'Failed to obtain co-ordinates as allow_url_fopen setting is disabled', (string)$property->uniqueID );
			        }
				}

				// Owner
				add_post_meta( $post_id, '_owner_contact_id', '', true );

				// Record Details
				add_post_meta( $post_id, '_negotiator_id', get_current_user_id(), true );
					
				$office_id = $primary_office_id;
				/*if ( isset($_POST['mapped_office'][(string)$property->branchID]) && $_POST['mapped_office'][(string)$property->branchID] != '' )
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
				}*/
				update_post_meta( $post_id, '_office_id', $office_id );

				// Residential Details
				update_post_meta( $post_id, '_department', (string)$property->department );
				update_post_meta( $post_id, '_bedrooms', ( ( isset($property->features->bedrooms) ) ? (string)$property->features->bedrooms : '' ) );
				update_post_meta( $post_id, '_bathrooms', ( ( isset($property->features->bathrooms) ) ? (string)$property->features->bathrooms : '' ) );
				update_post_meta( $post_id, '_reception_rooms', '' );

				if ( isset($_POST['mapped_property_type']) )
				{
					$mapping = $_POST['mapped_property_type'];
				}
				else
				{
					$mapping = isset($options['mappings']['property_type']) ? $options['mappings']['property_type'] : array();
				}

				wp_delete_object_term_relationships( $post_id, 'property_type' );

				$property_type = '';
				$category_attributes = $property->category->attributes();
				if ( isset($category_attributes['name']) )
				{
					$property_type = (string)$category_attributes['name'];
				}

				if ( !empty($mapping) && isset($mapping[$property_type]) )
				{
					wp_set_post_terms( $post_id, $mapping[$property_type], 'property_type' );
				}
				else
				{
					$this->add_log( 'Property received with a type (' . $property_type . ') that is not mapped', (string)$property->uniqueID );

					$options = $this->add_missing_mapping( $mapping, 'property_type', $property_type, $import_id );
				}

				$default_country = $PH_Countries->get_country( $country );
				$currency_to_insert = $default_country['currency_code'];
				update_post_meta( $post_id, '_currency', $currency_to_insert );

				// Residential Sales Details
				if ( (string)$property->department == 'residential-sales' )
				{
					$price_attributes = $property->price->attributes();

					// Clean price
					$price = round(preg_replace("/[^0-9.]/", '', (string)$property->price));

					update_post_meta( $post_id, '_price', $price );
					update_post_meta( $post_id, '_price_actual', $price );
					update_post_meta( $post_id, '_poa', ( ( isset($price_attributes['display']) && $price_attributes['display'] == 'no' ) ? 'yes' : '') );
				}
				elseif ( (string)$property->department == 'residential-lettings' )
				{
					$rent_attributes = $property->rent->attributes();

					// Clean price
					$price = round(preg_replace("/[^0-9.]/", '', (string)$property->rent));

					update_post_meta( $post_id, '_rent', $price );

					$rent_frequency = 'pcm';
					$price_actual = $price;
					switch ($rent_attributes['period'])
					{
						case "month":
						case "monthly": { $rent_frequency = 'pcm'; $price_actual = $price; break; }
						default: { $rent_frequency = 'pw'; $price_actual = ($price * 52) / 12; break; }
					}
					update_post_meta( $post_id, '_rent_frequency', $rent_frequency );
					update_post_meta( $post_id, '_price_actual', $price_actual );
					
					update_post_meta( $post_id, '_poa', ( ( isset($rent_attributes['display']) && $rent_attributes['display'] == 'no' ) ? 'yes' : '') );

					update_post_meta( $post_id, '_deposit', ( ( isset($property->bond) ) ? (string)$property->bond : '' ) );
            		update_post_meta( $post_id, '_available_date', '' ); // TO DO: Fix date available. Received in format 2009-01-26-12:30:00

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
					if ( !empty($mapping) && isset($property->allowances->furnished) && isset($mapping[(string)$property->allowances->furnished]) )
					{
		                wp_set_post_terms( $post_id, $mapping[(string)$property->allowances->furnished], 'furnished' );
		            }
				}

				// Marketing
				update_post_meta( $post_id, '_on_market', 'yes' );
				update_post_meta( $post_id, '_featured', '' );

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

        		$availability = '';
        		if ( isset($property->underOffer) )
        		{
        			$underoffer_attributes = $property->underOffer->attributes();
        			if (isset($underoffer_attributes['value']) && strtolower($underoffer_attributes['value']) == 'yes')
        			{
	        			$availability = 'Under Offer';
	        		}
        		}
        		if ( $availability == '' )
        		{
        			if ( (string)$property->department == 'residential-sales' )
        			{
        				$availability = 'For Sale';
        			}
        			elseif ( (string)$property->department == 'residential-lettings' )
        			{
        				$availability = 'To Let';
        			}
        		}
        		if ( isset($property->soldDetails) && isset($property->soldDetails->price) )
        		{
        			$availability = 'Sold';
        		}
        		
				if ( !empty($mapping) && isset($mapping[$availability]) )
				{
	                wp_set_post_terms( $post_id, $mapping[$availability], 'availability' );
	            }
	            else
	            {
		            $this->add_log( 'Property received with an availability (' . $availability . ') that is not mapped', (string)$property->uniqueID );

		            $options = $this->add_missing_mapping( $mapping, 'availability', $availability, $import_id );
		        }

	            // Features
				/*$features = array();
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
		        }	  */   

		        // Rooms
		        // For now put the whole description in one room
				update_post_meta( $post_id, '_rooms', '1' );
				update_post_meta( $post_id, '_room_name_0', '' );
	            update_post_meta( $post_id, '_room_dimensions_0', '' );
	            update_post_meta( $post_id, '_room_description_0', (string)$property->description );

	            // Media - Images
	            $property_images = array();

				if (isset($property->images) && !empty($property->images))
                {
                	foreach ($property->images as $images)
                    {
                    	if (isset($images->img))
	                    {
                            foreach ($images->img as $image)
                            {
                            	$image_attributes = $image->attributes();

                            	if ( 
									(isset($image_attributes['url']) &&
									trim((string)$image_attributes['url']) != '')
									||
									(isset($image_attributes['file']) &&
									trim((string)$image_attributes['file']) != '')
								)
                            	{
	                				$property_images[] = $image;
	                			}
                			}
                		}
                	}
                }
                if ( empty($property_images) )
                {
	                if (isset($property->objects) && !empty($property->objects))
	                {
	                	foreach ($property->objects as $images)
	                    {
	                    	if (isset($images->img))
	                        {
	                            foreach ($images->img as $image)
	                            {
	                            	$image_attributes = $image->attributes();

	                            	if ( 
										(isset($image_attributes['url']) &&
										trim((string)$image_attributes['url']) != '')
										||
										(isset($image_attributes['file']) &&
										trim((string)$image_attributes['file']) != '')
									)
	                            	{
		                				$property_images[] = $image;
		                			}
	                			}
	                		}
	                	}
	                }
	            }

	            if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();
    				if ( !empty($property_images) )
	                {
	                	$i = 0;
	                    foreach ($property_images as $image)
	                    {
	                    	$image_attributes = $image->attributes();

							if ( 
								isset($image_attributes['url']) &&
								trim((string)$image_attributes['url']) != '' &&
								(
									substr( strtolower((string)$image_attributes['url']), 0, 2 ) == '//' || 
									substr( strtolower((string)$image_attributes['url']), 0, 4 ) == 'http'
								)
							)
							{
								// This is a URL
								$url = (string)$image_attributes['url'];

								$media_urls[] = array('url' => $url);
							}
						}
					}
					update_post_meta( $post_id, '_photo_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' photo URLs', (string)$property->uniqueID );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_photos', TRUE );
						            
	                if ( !empty($property_images) )
	                {
	                	$i = 0;
	                    foreach ($property_images as $image)
	                    {
	                    	$image_attributes = $image->attributes();

							if ( 
								isset($image_attributes['url']) &&
								trim((string)$image_attributes['url']) != '' &&
								(
									substr( strtolower((string)$image_attributes['url']), 0, 2 ) == '//' || 
									substr( strtolower((string)$image_attributes['url']), 0, 4 ) == 'http'
								)
							)
							{
								// This is a URL
								$url = (string)$image_attributes['url'];
								$description = '';

								$modified = (string)$image_attributes['modTime'];

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

								        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), (string)$property->uniqueID );
								    }
								    else
								    {
									    $id = media_handle_sideload( $file_array, $post_id, $description, array('post_title' => $filename) );

									    // Check for handle sideload errors.
									    if ( is_wp_error( $id ) ) 
									    {
									        @unlink( $file_array['tmp_name'] );
									        
									        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), (string)$property->uniqueID );
									    }
									    else
									    {
									    	$media_ids[] = $id;

									    	update_post_meta( $id, '_id', trim((string)$image_attributes['id']) );
									    	update_post_meta( $id, '_imported_url', $url );
									    	update_post_meta( $id, '_modified', $modified );

									    	++$new;
									    }
									}
								}
							}
							elseif (
								isset($image_attributes['file']) &&
								trim((string)$image_attributes['file']) != ''
							)
							{
								$media_folder = dirname( $this->target_file );
								$media_file_name = $image_attributes['file'];

								if ( file_exists( $media_folder . '/' . $media_file_name ) )
								{
	                           		$replacing_attachment_id = '';

									// See if we have an image with this id
	                           		foreach ( $previous_media_ids as $previous_media_id )
	                           		{
	                           			$id = get_post_meta( $previous_media_id, '_id', TRUE );

	                           			if ( $id == $image_attributes['id'] )
	                           			{
	                           				$replacing_attachment_id = $previous_media_id;
	                           			}
	                           		}

	                           		$upload = true;
	                           		if ( $replacing_attachment_id == '' )
	                           		{
	                           			// This is new media in a slot not used before
	                           		}
	                           		else
	                           		{
	                           			// This is a photo for a slot already used. Compare filesizes to see if we need to import it
	                                    $current_image_path = get_post_meta( $replacing_attachment_id, '_imported_path', TRUE );
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
		                                    	$this->add_error( 'Failed to get filesize of new image file ' . $media_folder . '/' . $media_file_name, (string)$property->uniqueID );
		                                    }
	                                        
	                                        unset($new_image_size);
	                                    }
	                                    else
	                                    {
	                                    	$this->add_error( 'Failed to get filesize of existing image file ' . $current_image_path, (string)$property->uniqueID );
	                                    }
	                                    
	                                    unset($current_image_size);
	                           		}

	                           		if ( $upload )
	                           		{
	                           			// We've physically received the file
										$upload = wp_upload_bits(trim($media_file_name, '_'), null, file_get_contents($media_folder . '/' . $media_file_name));  
		                                
		                                if( isset($upload['error']) && $upload['error'] !== FALSE )
		                                {
		                                	$this->add_error( print_r($upload['error'], TRUE), $property['AGENT_REF'] );
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
	                                        	$this->add_error( 'Failed inserting image attachment ' . $upload['file'] . ' - ' . print_r($attachment, TRUE), (string)$property->uniqueID );
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

				                    		++$existing;
				                    	}
	                               	}
	                           	}
	                           	else
	                           	{
	                           		if ( isset($previous_media_ids[$i]) ) 
			                    	{
			                    		$media_ids[] = $previous_media_ids[$i];

			                    		++$existing;
			                    	}
	                           	}
							}

							++$i;
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

					$this->add_log( 'Imported ' . count($media_ids) . ' photos (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->uniqueID );
				}

				// Media - Floorplans
				$property_floorplans = array();

                if (isset($property->objects) && !empty($property->objects))
                {
                	foreach ($property->objects as $images)
                    {
                    	if (isset($images->floorplan))
                        {
                            foreach ($images->floorplan as $floorplan)
                            {
                            	$floorplan_attributes = $floorplan->attributes();

                            	if ( 
									(isset($floorplan_attributes['url']) &&
									trim((string)$floorplan_attributes['url']) != '')
									||
									(isset($floorplan_attributes['file']) &&
									trim((string)$floorplan_attributes['file']) != '')
								)
                            	{
	                				$property_floorplans[] = $floorplan;
	                			}
                			}
                		}
                	}
                }

                if ( get_option('propertyhive_floorplans_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();
    				if ( !empty($property_floorplans) )
	                {
	                	$i = 0;
	                    foreach ($property_floorplans as $floorplan)
	                    {
	                    	$floorplan_attributes = $floorplan->attributes();
	                    	
							if ( 
								isset($floorplan_attributes['url']) &&
								trim((string)$floorplan_attributes['url']) != '' &&
								(
									substr( strtolower((string)$floorplan_attributes['url']), 0, 2 ) == '//' || 
									substr( strtolower((string)$floorplan_attributes['url']), 0, 4 ) == 'http'
								)
							)
							{
								// This is a URL
								$url = (string)$floorplan_attributes['url'];

								$media_urls[] = array('url' => $url);
							}
						}
					}
					update_post_meta( $post_id, '_floorplan_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' floorplan URLs', (string)$property->uniqueID );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_floorplans', TRUE );
					
	                if ( !empty($property_floorplans) )
	                {
	                	$i = 0;
	                    foreach ($property_floorplans as $floorplan)
	                    {
	                    	$floorplan_attributes = $floorplan->attributes();
	                    	
							if ( 
								isset($floorplan_attributes['url']) &&
								trim((string)$floorplan_attributes['url']) != '' &&
								(
									substr( strtolower((string)$floorplan_attributes['url']), 0, 2 ) == '//' || 
									substr( strtolower((string)$floorplan_attributes['url']), 0, 4 ) == 'http'
								)
							)
							{
								// This is a URL
								$url = (string)$floorplan_attributes['url'];
								$description = '';

								$modified = (string)$floorplan_attributes['modTime'];

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

								        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), (string)$property->uniqueID );
								    }
								    else
								    {
									    $id = media_handle_sideload( $file_array, $post_id, $description, array('post_title' => $filename) );

									    // Check for handle sideload errors.
									    if ( is_wp_error( $id ) ) 
									    {
									        @unlink( $file_array['tmp_name'] );
									        
									        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), (string)$property->uniqueID );
									    }
									    else
									    {
									    	$media_ids[] = $id;

									    	update_post_meta( $id, '_id', trim((string)$floorplan_attributes['id']) );
									    	update_post_meta( $id, '_imported_url', $url );
									    	update_post_meta( $id, '_modified', $modified );

									    	++$new;
									    }
									}
								}
							}
							elseif (
								isset($floorplan_attributes['file']) &&
								trim((string)$floorplan_attributes['file']) != ''
							)
							{
								$media_folder = dirname( $this->target_file );
								$media_file_name = $floorplan_attributes['file'];

								if ( file_exists( $media_folder . '/' . $media_file_name ) )
								{
	                           		$replacing_attachment_id = '';

									// See if we have an floorplan with this id
	                           		foreach ( $previous_media_ids as $previous_media_id )
	                           		{
	                           			$id = get_post_meta( $previous_media_id, '_id', TRUE );

	                           			if ( $id == $floorplan_attributes['id'] )
	                           			{
	                           				$replacing_attachment_id = $previous_media_id;
	                           			}
	                           		}

	                           		$upload = true;
	                           		if ( $replacing_attachment_id == '' )
	                           		{
	                           			// This is new media in a slot not used before
	                           		}
	                           		else
	                           		{
	                           			// This is a photo for a slot already used. Compare filesizes to see if we need to import it
	                                    $current_image_path = get_post_meta( $replacing_attachment_id, '_imported_path', TRUE );
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
		                                    	$this->add_error( 'Failed to get filesize of new floorplan file ' . $media_folder . '/' . $media_file_name, (string)$property->uniqueID );
		                                    }
	                                        
	                                        unset($new_image_size);
	                                    }
	                                    else
	                                    {
	                                    	$this->add_error( 'Failed to get filesize of existing floorplan file ' . $current_image_path, (string)$property->uniqueID );
	                                    }
	                                    
	                                    unset($current_image_size);
	                           		}

	                           		if ( $upload )
	                           		{
	                           			// We've physically received the file
										$upload = wp_upload_bits(trim($media_file_name, '_'), null, file_get_contents($media_folder . '/' . $media_file_name));  
		                                
		                                if( isset($upload['error']) && $upload['error'] !== FALSE )
		                                {
		                                	$this->add_error( print_r($upload['error'], TRUE), $property['AGENT_REF'] );
		                                }
		                                else
		                                {
		                                	// We don't already have a thumbnail and we're presented with a floorplan
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
	                                        	$this->add_error( 'Failed inserting floorplan attachment ' . $upload['file'] . ' - ' . print_r($attachment, TRUE), (string)$property->uniqueID );
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

				                    		++$existing;
				                    	}
	                               	}
	                           	}
	                           	else
	                           	{
	                           		if ( isset($previous_media_ids[$i]) ) 
			                    	{
			                    		$media_ids[] = $previous_media_ids[$i];

			                    		++$existing;
			                    	}
	                           	}
							}

							++$i;
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

					$this->add_log( 'Imported ' . count($media_ids) . ' floorplans (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->uniqueID );
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

									        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), (string)$property->uniqueID );
									    }
									    else
									    {
										    $id = media_handle_sideload( $file_array, $post_id, $description, array('post_title' => $filename) );

										    // Check for handle sideload errors.
										    if ( is_wp_error( $id ) ) 
										    {
										        @unlink( $file_array['tmp_name'] );
										        
										        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), (string)$property->uniqueID );
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

				$this->add_log( 'Imported ' . count($media_ids) . ' brochures (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->uniqueID );

				// Media - EPCs
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

									        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), (string)$property->uniqueID );
									    }
									    else
									    {
										    $id = media_handle_sideload( $file_array, $post_id, $description, array('post_title' => $filename) );

										    // Check for handle sideload errors.
										    if ( is_wp_error( $id ) ) 
										    {
										        @unlink( $file_array['tmp_name'] );
										        
										        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), (string)$property->uniqueID );
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
				update_post_meta( $post_id, '_epcs', $media_ids );

				$this->add_log( 'Imported ' . count($media_ids) . ' EPCs (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->uniqueID );*/

				// Media - Virtual Tours
				$virtual_tours = array();
				if (isset($property->videoLink))
                {
                	$video_link_attributes = $property->videoLink->attributes();
                	if ( isset($video_link_attributes['href']) && $video_link_attributes['href'] != '' )
                	{
	                    $virtual_tours[] = $video_link_attributes['href'];
	                }
                }

                update_post_meta( $post_id, '_virtual_tours', count($virtual_tours) );
                foreach ($virtual_tours as $i => $virtual_tour)
                {
                	update_post_meta( $post_id, '_virtual_tour_' . $i, (string)$virtual_tour );
                }

				$this->add_log( 'Imported ' . count($virtual_tours) . ' virtual tours', (string)$property->uniqueID );

				do_action( "propertyhive_property_imported_reaxml", $post_id, $property );

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
							$this->add_log( 'New meta data for ' . trim($key, '_') . ': ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), (string)$property->uniqueID );
						}
						elseif ( $metadata_before[$key] != $metadata_after[$key] )
						{
							$this->add_log( 'Updated ' . trim($key, '_') . '. Before: ' . ( ( is_array($metadata_before[$key]) ) ? implode(", ", $metadata_before[$key]) : $metadata_before[$key] ) . ', After: ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), (string)$property->uniqueID );
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
							$this->add_log( 'New taxonomy data for ' . $taxonomy_name . ': ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), (string)$property->uniqueID );
						}
						elseif ( $taxonomy_terms_before[$taxonomy_name] != $taxonomy_terms_after[$taxonomy_name] )
						{
							$this->add_log( 'Updated ' . $taxonomy_name . '. Before: ' . ( ( is_array($taxonomy_terms_before[$taxonomy_name]) ) ? implode(", ", $taxonomy_terms_before[$taxonomy_name]) : $taxonomy_terms_before[$taxonomy_name] ) . ', After: ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), (string)$property->uniqueID );
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

		do_action( "propertyhive_post_import_properties_reaxml" );

		$this->import_end();

		$this->add_log( 'Finished import' );
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
            	'For Sale' => 'For Sale',
            	'To Let' => 'To Let',
                'Under Offer' => 'Under Offer',
                'Sold' => 'Sold',
            );
        }
        if ($custom_field == 'property_type')
        {
        	return array(
                'House' => 'House',
                'Unit' => 'Unit',
                'Townhouse' => 'Townhouse',
                'Villa' => 'Villa',
                'Apartment' => 'Apartment',
                'Flat' => 'Flat',
                'Studio' => 'Studio',
                'Warehouse' => 'Warehouse',
                'DuplexSemi-detached' => 'DuplexSemi-detached',
                'Alpine' => 'Alpine',
                'AcreageSemi-rural' => 'AcreageSemi-rural',
                'BlockOfUnits' => 'BlockOfUnits',
                'Terrace' => 'Terrace',
                'Retirement' => 'Retirement',
                'ServicedApartment' => 'ServicedApartment',
                'Other' => 'Other',
            );
        }
        /*if ($custom_field == 'price_qualifier')
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
        }*/
        if ($custom_field == 'furnished')
        {
            return array(
            	'1' => '1',
            	'yes' => 'yes',
            	'true' => 'true',
            );
        }
    }

}

}