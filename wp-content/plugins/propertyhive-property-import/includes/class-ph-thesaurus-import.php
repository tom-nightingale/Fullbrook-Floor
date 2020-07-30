<?php
/**
 * Class for managing the import process of a Thesaurus file
 *
 * @package WordPress
 */
if ( class_exists( 'PH_Property_Import_Process' ) ) {

class PH_Thesaurus_Import extends PH_Property_Import_Process {

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
		$this->add_log("Parsing properties");

		$this->properties = array(); // Reset properties in the event we're importing multiple files

		$handle = fopen( $this->target_file, "r" );
		if ($handle) 
		{
		    while (($property = fgets($handle)) !== false) 
		    {
		        // process the line read.

		        $this->properties[] = explode("|", $property);
		    }

		    fclose($handle);
		} 
		else 
		{
		    $this->add_error( 'Error opening file' );
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

		// get lat/lngs from geocode.file
		$lat_lngs = array();

		$ftp_connected = false;
        $ftp_conn = ftp_connect( $options['ftp_host'] );
        if ( $ftp_conn !== FALSE )
        {
            $ftp_login = ftp_login( $ftp_conn, $options['ftp_user'], $options['ftp_pass'] );
            if ( $ftp_login !== FALSE )
            {
            	if ( isset($options['ftp_passive']) && $options['ftp_passive'] == '1' )
            	{
            		ftp_pasv( $ftp_conn, true );
            	}

                if ( ftp_chdir( $ftp_conn, $options['ftp_dir'] ) )
                {
                    $ftp_connected = true;
                }
            }
            
        }
        
        if ( $ftp_connected )
        { 
        	$wp_upload_dir = wp_upload_dir();

        	$xml_file = $wp_upload_dir['basedir'] . '/ph_import/geocode.file';

        	// Get file
        	if ( ftp_get( $ftp_conn, $xml_file, 'geocode.file', FTP_ASCII ) )
        	{
        		$handle = fopen( $xml_file, "r" );
				if ($handle) 
				{
				    while (($lat_lng_row = fgets($handle)) !== false) 
				    {
				        // process the line read.

				        $lat_lng_row = explode("|", $lat_lng_row);

				        if ( 
				        	isset($lat_lng_row[0]) && isset($lat_lng_row[3]) && isset($lat_lng_row[2]) 
				        	&&
				        	$lat_lng_row[0] != '' && $lat_lng_row[3] != '' && $lat_lng_row[2] != ''
				        )
				        {
				        	$lat_lngs[$lat_lng_row[0]] = array(
				        		'lat' => $lat_lng_row[3],
				        		'lng' => $lat_lng_row[2],
				        	);
				        }
				    }

				    fclose($handle);
				} 

        		unset($xml_file);
        	}
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

        do_action( "propertyhive_pre_import_properties_thesaurus", $this->properties );
        $this->properties = apply_filters( "propertyhive_thesaurus_properties_due_import", $this->properties );

		$this->add_log( 'Beginning to loop through ' . count($this->properties) . ' properties' );

		$property_row = 1;
		foreach ( $this->properties as $property )
		{
			$this->add_log( 'Importing property ' . $property_row . ' with reference ' . $property[0], $property[0] );

			$inserted_updated = false;
			$new_property = false;

			$args = array(
	            'post_type' => 'property',
	            'posts_per_page' => 1,
	            'post_status' => 'any',
	            'meta_query' => array(
	            	array(
		            	'key' => $imported_ref_key,
		            	'value' => $property[0]
		            )
	            )
	        );
	        $property_query = new WP_Query($args);

	        $display_address = $property[139];
	        if (trim($display_address) == '')
	        {
	        	$display_address = $property[8];
	        	if ($property[7] != '')
	        	{
	        		if ($display_address != '') { $display_address .= ', '; }
	        		$display_address .= $property[7];
	        	}
	        	elseif ($property[6] != '')
	        	{
	        		if ($display_address != '') { $display_address .= ', '; }
	        		$display_address .= $property[6];
	        	}
	        }
	        
	        if ($property_query->have_posts())
	        {
	        	$this->add_log( 'This property has been imported before. Updating it', $property[0] );

	        	// We've imported this property before
	            while ($property_query->have_posts())
	            {
	                $property_query->the_post();

	                $post_id = get_the_ID();

	                $my_post = array(
				    	'ID'          	 => $post_id,
				    	'post_title'     => wp_strip_all_tags( $display_address ),
				    	'post_excerpt'   => utf8_encode($property[25]),
				    	'post_content' 	 => '',
				    	'post_status'    => 'publish',
				  	);

				 	// Update the post into the database
				    $post_id = wp_update_post( $my_post );

				    if ( is_wp_error( $post_id ) ) 
					{
						$this->add_error( 'Failed to update post. The error was as follows: ' . $post_id->get_error_message(), $property[0] );
					}
					else
					{
						$inserted_updated = 'updated';
					}
	            }
	        }
	        else
	        {
	        	$this->add_log( 'This property hasn\'t been imported before. Inserting it', $property[0] );

	        	// We've not imported this property before
				$postdata = array(
					'post_excerpt'   => utf8_encode($property[25]),
					'post_content' 	 => '',
					'post_title'     => wp_strip_all_tags( $display_address ),
					'post_status'    => 'publish',
					'post_type'      => 'property',
					'comment_status' => 'closed',
				);

				$post_id = wp_insert_post( $postdata, true );

				if ( is_wp_error( $post_id ) ) 
				{
					$this->add_error( 'Failed to insert post. The error was as follows: ' . $post_id->get_error_message(), $property[0] );
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
					($display_address != '' || $property[25] != '')
				)
				{
					$my_post = array(
				    	'ID'          	 => $post_id,
				    	'post_title'     => htmlentities(mb_convert_encoding(wp_strip_all_tags( $display_address ), 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
				    	'post_excerpt'   => htmlentities(mb_convert_encoding($property[25], 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
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

				$this->add_log( 'Successfully ' . $inserted_updated . ' post. The post ID is ' . $post_id, $property[0] );

				$previous_thesaurus_update_date = get_post_meta( $post_id, '_thesaurus_update_date_' . $import_id, TRUE);

				$skip_property = false;
				if (
					( isset($options['only_updated']) && $options['only_updated'] == 'yes' )
				)
				{
					if (
						$previous_thesaurus_update_date == $property[204]
					)
					{
						$skip_property = true;
					}
				}

				// Coordinates
				if ( isset($lat_lngs[$property[0]]) )
				{
					update_post_meta( $post_id, '_latitude', $lat_lngs[$property[0]]['lat'] );
					update_post_meta( $post_id, '_longitude', $lat_lngs[$property[0]]['lng'] );
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
								if ( $property[8] != '' ) { $address_to_geocode[] = $property[8]; }
								if ( $property[7] != '' ) { $address_to_geocode[] = $property[7]; }
								if ( $property[6] != '' ) { $address_to_geocode[] = $property[6]; }
								if ( $property[5] != '' ) { $address_to_geocode[] = $property[5]; }
								if ( $property[9] != '' ) { $address_to_geocode[] = $property[9]; }

								$request_url = "https://maps.googleapis.com/maps/api/geocode/xml?address=" . urlencode( implode( ", ", $address_to_geocode ) ) . "&sensor=false&region=gb"; // the request URL you'll send to google to get back your XML feed

			                    if ( $api_key != '' ) { $request_url .= "&key=" . $api_key; }

					            $response = wp_remote_get($request_url);

			                	if ( is_array( $response ) && !is_wp_error( $response ) ) 
			                	{
									$header = $response['headers']; // array of http header lines
									$body = $response['body']; // use the content

						            $xml = simplexml_load_string($body);

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
								        	$this->add_error( 'Google Geocoding service returned status ' . $status, $property[0] );
								        	sleep(3);
								        }
								    }
							        else
							        {
							        	$this->add_error( 'Failed to parse XML response from Google Geocoding service', $property[0] );
							        }
							    }
						        else
						        {
						        	$this->add_error( 'Invalid response when trying to obtain co-ordinates', $property[0] );
						        }
							}
							else
					        {
					        	$this->add_error( 'Failed to obtain co-ordinates as allow_url_fopen setting is disabled', $property[0] );
					        }
					    }
					    else
					    {
					    	$this->add_log( 'Not performing Google Geocoding request as no API key present in settings', $property[0] );
					    }
					}
				}

				if ( !$skip_property )
				{
					update_post_meta( $post_id, $imported_ref_key, $property[0] );

					// Address
					update_post_meta( $post_id, '_reference_number', $property[0] );
					update_post_meta( $post_id, '_address_name_number', '' );
					update_post_meta( $post_id, '_address_street', $property[8] );
					update_post_meta( $post_id, '_address_two', $property[7] );
					update_post_meta( $post_id, '_address_three', $property[6] );
					update_post_meta( $post_id, '_address_four', $property[5] );
					update_post_meta( $post_id, '_address_postcode', $property[9] );

					update_post_meta( $post_id, '_address_country', get_option( 'propertyhive_default_country', 'GB' ) );

					// Check main address fields and see if this location exists as a taxonomy to try and assign properties to location
					$address_fields_to_check = apply_filters( 'propertyhive_thesaurus_address_fields_to_check', array(5, 6, 7) );
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

					// Owner
					add_post_meta( $post_id, '_owner_contact_id', '', true );

					// Record Details
					add_post_meta( $post_id, '_negotiator_id', get_current_user_id(), true );
						
					$office_id = $primary_office_id;
					if ( isset($_POST['mapped_office'][$property[265]]) && $_POST['mapped_office'][$property[265]] != '' )
					{
						$office_id = $_POST['mapped_office'][$property[265]];
					}
					elseif ( isset($options['offices']) && is_array($options['offices']) && !empty($options['offices']) )
					{
						foreach ( $options['offices'] as $ph_office_id => $branch_code )
						{
							if ( $branch_code == $property[265] )
							{
								$office_id = $ph_office_id;
								break;
							}
						}
					}
					update_post_meta( $post_id, '_office_id', $office_id );

					// Residential Details
					update_post_meta( $post_id, '_department', ( ( in_array($property[1], array('L','6','7','F','W')) ) ? 'residential-lettings' : 'residential-sales' ) );
					update_post_meta( $post_id, '_bedrooms', $property[17] );
					update_post_meta( $post_id, '_bathrooms', $property[18] );
					update_post_meta( $post_id, '_reception_rooms', $property[19] );

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

		            if ( isset($property[14]) && $property[14] != '' )
		            {
						if ( !empty($mapping) && isset($mapping[$property[14]]) )
						{
				            wp_set_post_terms( $post_id, $mapping[$property[14]], 'property_type' );
			            }
			            else
						{
							$this->add_log( 'Property received with a type (' . $property[14] . ') that is not mapped', $property[0] );

							$options = $this->add_missing_mapping( $mapping, 'property_type', $property[14], $import_id );
						}
					}

					// Clean price
					$price = round(preg_replace("/[^0-9.]/", '', $property[3]));

					// Residential Sales Details
					if ( !in_array($property[1], array('L','6','7','F','W') ) )
					{
						update_post_meta( $post_id, '_price', $price );
						update_post_meta( $post_id, '_price_actual', $price );
						update_post_meta( $post_id, '_currency', 'GBP' );

						$poa = '';
						if ( strtolower(substr($property[231], 0, 1)) == 'y' )
						{
							$poa = 'yes';
						}
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

						if ( !empty($mapping) && isset($property[4]) && isset($mapping[$property[4]]) )
						{
				            wp_set_post_terms( $post_id, $mapping[$property[4]], 'price_qualifier' );
			            }
			            elseif ( !empty($mapping) && isset($property[211]) && isset($mapping[$property[211]]) )
						{
				            wp_set_post_terms( $post_id, $mapping[$property[211]], 'price_qualifier' );
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
						if ( !empty($mapping) && isset($property[2]) && isset($mapping[$property[2]]) )
						{
				            wp_set_post_terms( $post_id, $mapping[$property[2]], 'tenure' );
			            }
					}
					elseif ( in_array($property[1], array('L','6','7','F','W') ) )
					{
						update_post_meta( $post_id, '_rent', $price );

						$rent_frequency = 'pcm';
						$price_actual = $price;

						switch ($property[4])
						{
							case "per week":
							{
								$rent_frequency = 'pw';
								$price_actual = ($price * 52) / 12;
								break;
							}
							case "per month":
							{
								$rent_frequency = 'pcm';
								$price_actual = $price;
								break;
							}
							case "per year":
							{
								$rent_frequency = 'pa';
								$price_actual = $price / 12;
								break;
							}
						}

						update_post_meta( $post_id, '_rent_frequency', $rent_frequency );
						update_post_meta( $post_id, '_price_actual', $price_actual );
						update_post_meta( $post_id, '_currency', 'GBP' );
						
						$poa = '';
						if ( strtolower(substr($property[231], 0, 1)) == 'y' )
						{
							$poa = 'yes';
						}
						update_post_meta( $post_id, '_poa', $poa );

						update_post_meta( $post_id, '_deposit', ( ($property[252] != '' && $property[252] > 0) ? $property[252] : '' ) );
	            		
						$available_date = ''; // Sometimes provided as 2015-04-24, other times as 24 Apr 2015
						if ( isset($property[138]) && $property[138] != '' )
						{
							$available_date = date("Y-m-d", strtotime($property[138]));
						}
	            		update_post_meta( $post_id, '_available_date', $available_date ); // Need to do. Provided as 24 Apr 2015

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
						if ( !empty($mapping) && isset($property[16]) && isset($mapping[$property[16]]) )
						{
			                wp_set_post_terms( $post_id, $mapping[$property[16]], 'furnished' );
			            }
					}

					// Marketing
					update_post_meta( $post_id, '_on_market', 'yes' );
					$featured = '';
					if ( $property[255] != '' )
					{
						$featured = 'yes';
					}
					update_post_meta( $post_id, '_featured', $featured );

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
					if ( !empty($mapping) && isset($property[1]) && isset($mapping[$property[1]]) )
					{
		                wp_set_post_terms( $post_id, $mapping[$property[1]], 'availability' );
		            }

		            // Features
					$features = array();
					if ($property[20] != '') { $features[] = $property[20]; }
					if ($property[21] != '') { $features[] = $property[21]; }
					if ($property[22] != '') { $features[] = $property[22]; }
					if ($property[129] != '') { $features[] = $property[129]; }
					if ($property[130] != '') { $features[] = $property[130]; }
					if ($property[137] != '') { $features[] = $property[137]; }
					if ($property[238] != '') { $features[] = $property[238]; }
					if ($property[239] != '') { $features[] = $property[239]; }
					if ($property[240] != '') { $features[] = $property[240]; }
					if ($property[241] != '') { $features[] = $property[241]; }

					update_post_meta( $post_id, '_features', count( $features ) );
	        		
	        		$i = 0;
			        foreach ( $features as $feature )
			        {
			            update_post_meta( $post_id, '_feature_' . $i, $feature );
			            ++$i;
			        }

			        // Rooms
			        $room_title_start = 26;
			        $room_dimensions_start = 140;
			        $room_desc_start = 90;

			        $new_room_count = 0;

			        for ( $i = 0; $i < 32; ++$i )
			        {
			        	$room_title = ( isset( $property[$room_title_start+$i] ) ? $property[$room_title_start+$i] : '' );
			        	$room_dimensions = ( isset( $property[$room_dimensions_start+$i] ) ? $property[$room_dimensions_start+$i] : '' );
			        	$room_desc = ( isset( $property[$room_desc_start+$i] ) ? strip_tags($property[$room_desc_start+$i]) : '' );

			        	if ( trim($room_title) != '' || trim($room_desc) != '' )
			        	{
			        		update_post_meta( $post_id, '_room_name_' . $new_room_count, trim(utf8_encode($room_title)) );
				            update_post_meta( $post_id, '_room_dimensions_' . $new_room_count, trim(utf8_encode($room_dimensions)) );
				            update_post_meta( $post_id, '_room_description_' . $new_room_count, trim(utf8_encode($room_desc)) );

			        		++$new_room_count;
			        	}
			        }

			        update_post_meta( $post_id, '_rooms', $new_room_count );

					$pictures = array();
					if ($property[23] != '') { $pictures[] = $property[23]; }
					for ($i = 58; $i <= 89; ++$i)
					{
						if ($property[$i] != '') { $pictures[] = $property[$i]; }
					}

					$floorplans = array();
					for ($i = 132; $i <= 135; ++$i)
					{
						if ($property[$i] != '') { $floorplans[] = $property[$i]; }
					}

					$image_ftp_dir = $options['image_ftp_dir'];
					$image_ftp_dirs = explode(",", $image_ftp_dir);

					if (!empty($pictures) || !empty($floorplans))
					{
						// Connect to FTP and do media related functionality
						$ftp_connected = false;
			            $ftp_conn = ftp_connect( $options['ftp_host'] );
			            if ( $ftp_conn !== FALSE )
			            {
			                $ftp_login = ftp_login( $ftp_conn, $options['ftp_user'], $options['ftp_pass'] );
			                if ( $ftp_login !== FALSE )
			                {
			                	if ( isset($options['ftp_passive']) && $options['ftp_passive'] == '1' )
			                	{
			                		ftp_pasv( $ftp_conn, true );
			                	}

			                	$ftp_connected = true;
			                }
			            }

			            if ( $ftp_connected )
			            { 
			            	// Media - Images
			            	if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
		        			{
		        				$media_urls = array();

								update_post_meta( $post_id, '_photo_urls', $media_urls );

								$this->add_log( 'Imported ' . count($media_urls) . ' photo URLs', $property[0] );
		        			}
		        			else
		        			{
				            	$media_ids = array();
				            	$new = 0;
								$existing = 0;
								$deleted = 0;
								$previous_media_ids = get_post_meta( $post_id, '_photos', TRUE );

								if ( !empty($pictures) )
								{
									$i = 0;
									foreach ( $pictures as $picture )
									{
										$media_file_name = $picture;
										$media_folder = dirname( $this->target_file );

						            	// Get file
						            	$got_file = false;
						            	foreach ( $image_ftp_dirs as $image_ftp_dir )
						            	{
						            		if ( ftp_get( $ftp_conn, $media_folder . '/' . $media_file_name, $image_ftp_dir . '/' . $media_file_name, FTP_BINARY ) )
							            	{
							            		$got_file = true;
							            		break;
							            	}
						            	}
						            	
						            	if ( $got_file )
						            	{
											$description = '';

											if ( file_exists( $media_folder . '/' . $media_file_name ) )
											{
												$upload = true;
				                                $replacing_attachment_id = '';
				                                if ( isset($previous_media_ids[$i]) ) 
				                                {
				                                    // get this attachment
				                                    $current_image_path = get_post_meta( $previous_media_ids[$i], '_imported_path', TRUE );
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
					                                    	$this->add_error( 'Failed to get filesize of new image file ' . $media_folder . '/' . $media_file_name, $property[0] );
					                                    }
				                                        
				                                        unset($new_image_size);
				                                    }
				                                    else
				                                    {
				                                    	$this->add_error( 'Failed to get filesize of existing image file ' . $current_image_path, $property[0] );
				                                    }
				                                    
				                                    unset($current_image_size);
				                                }

				                                if ($upload)
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
				                                        	$this->add_error( 'Failed inserting image attachment ' . $upload['file'] . ' - ' . print_r($attachment, TRUE), $property[0] );
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

					                            unlink($media_folder . '/' . $media_file_name);
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

											$this->add_error( 'Failed to get file ' . $media_file_name . ' as ' . $media_folder . '/' . $media_file_name, $property[0] );
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

								$this->add_log( 'Imported ' . count($media_ids) . ' photos (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property[0] );
							}

							// Media - Floorplans
							if ( get_option('propertyhive_floorplans_stored_as', '') == 'urls' )
		        			{
		        				$media_urls = array();

								update_post_meta( $post_id, '_floorplan_urls', $media_urls );

								$this->add_log( 'Imported ' . count($media_urls) . ' floorplan URLs', $property[0] );
		        			}
		        			else
		        			{
								$media_ids = array();
								$new = 0;
								$existing = 0;
								$deleted = 0;
								$previous_media_ids = get_post_meta( $post_id, '_floorplans', TRUE );
								if ( !empty($floorplans) )
								{
									$i = 0;
									foreach ( $floorplans as $floorplan )
									{
										$media_file_name = $floorplan;
										$media_folder = dirname( $this->target_file );

						            	// Get file
						            	$got_file = false;
						            	foreach ( $image_ftp_dirs as $image_ftp_dir )
						            	{
						            		if ( ftp_get( $ftp_conn, $media_folder . '/' . $media_file_name, $image_ftp_dir . '/' . $media_file_name, FTP_BINARY ) )
							            	{
							            		$got_file = true;
							            		break;
							            	}
						            	}
						            	
						            	if ( $got_file )
						            	{
											$description = '';

											if ( file_exists( $media_folder . '/' . $media_file_name ) )
											{
												$upload = true;
				                                $replacing_attachment_id = '';
				                                if ( isset($previous_media_ids[$i]) ) 
				                                {                                    
				                                    // get this attachment
				                                    $current_image_path = get_post_meta( $previous_media_ids[$i], '_imported_path', TRUE );
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
					                                    	$this->add_error( 'Failed to get filesize of new image file ' . $media_folder . '/' . $media_file_name, $property[0] );
					                                    }
				                                        
				                                        unset($new_image_size);
				                                    }
				                                    else
				                                    {
				                                    	$this->add_error( 'Failed to get filesize of existing image file ' . $current_image_path, $property[0] );
				                                    }
				                                    
				                                    unset($current_image_size);
				                                }

				                                if ($upload)
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
				                                        	$this->add_error( 'Failed inserting floorplan attachment ' . $upload['file'] . ' - ' . print_r($attachment, TRUE), $property[0] );
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

					                            unlink($media_folder . '/' . $media_file_name);
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

											$this->add_error( 'Failed to get file ' . $media_file_name . ' as ' . $media_folder . '/' . $media_file_name, $property[0] );
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

								$this->add_log( 'Imported ' . count($media_ids) . ' floorplans (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property[0] );
							}

							ftp_close( $ftp_conn );
						}
					}

					// Media - Brochures
					if ( get_option('propertyhive_brochures_stored_as', '') == 'urls' )
	    			{
	    				$media_urls = array();

						update_post_meta( $post_id, '_brochure_urls', $media_urls );

						$this->add_log( 'Imported ' . count($media_urls) . ' brochure URLs', $property[0] );
	    			}
	    			else
	    			{
						$media_ids = array();
						$new = 0;
						$existing = 0;
						$deleted = 0;
						$previous_media_ids = get_post_meta( $post_id, '_brochures', TRUE );

						if ($property[237] != '')
						{
							$ftp_connected = false;
				            $ftp_conn = ftp_connect( $options['ftp_host'] );
				            if ( $ftp_conn !== FALSE )
				            {
				                $ftp_login = ftp_login( $ftp_conn, $options['ftp_user'], $options['ftp_pass'] );
				                if ( $ftp_login !== FALSE )
				                {
				                	if ( isset($options['ftp_passive']) && $options['ftp_passive'] == '1' )
				                	{
				                		ftp_pasv( $ftp_conn, true );
				                	}

				                    if ( ftp_chdir( $ftp_conn, $options['brochure_ftp_dir'] ) )
				                    {
				                        $ftp_connected = true;
				                    }
				                }
				            }

				            if ( $ftp_connected )
				            {
								$media_file_name = $property[237];
								$media_folder = dirname( $this->target_file );

				            	// Get file
				            	if ( ftp_get( $ftp_conn, $media_folder . '/' . $media_file_name, $media_file_name, FTP_BINARY ) )
				            	{	
				            		$i = 0;

									$description = '';

									if ( file_exists( $media_folder . '/' . $media_file_name ) )
									{
										$upload = true;
			                            $replacing_attachment_id = '';
			                            if ( isset($previous_media_ids[$i]) ) 
			                            {                                    
			                                // get this attachment
			                                $current_image_path = get_post_meta( $previous_media_ids[$i], '_imported_path', TRUE );
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
			                                    	$this->add_error( 'Failed to get filesize of new image file ' . $media_folder . '/' . $media_file_name, $property[0] );
			                                    }
			                                    
			                                    unset($new_image_size);
			                                }
			                                else
			                                {
			                                	$this->add_error( 'Failed to get filesize of existing image file ' . $current_image_path, $property[0] );
			                                }
			                                
			                                unset($current_image_size);
			                            }

			                            if ($upload)
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
			                                    	$this->add_error( 'Failed inserting floorplan attachment ' . $upload['file'] . ' - ' . print_r($attachment, TRUE), $property[0] );
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

			                            unlink($media_folder . '/' . $media_file_name);
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
									$this->add_error( 'Failed to get file ' . $picture . ' as ' . $media_folder . '/' . $media_file_name, $property[0] );
								}

								ftp_close( $ftp_conn );
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

						$this->add_log( 'Imported ' . count($media_ids) . ' brochures (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property[0] );
					}

					// Media - EPCs
					if ( get_option('propertyhive_epcs_stored_as', '') == 'urls' )
	    			{
	    				$media_urls = array();

	    				if ( 
							substr( strtolower($property[269]), 0, 2 ) == '//' || 
							substr( strtolower($property[269]), 0, 4 ) == 'http'
						)
						{
							// This is a URL
							$url = str_replace(" ", "%20", (string)$property->epc);

							$media_urls[] = array('url' => $url);
						}
						if ( 
							$property[257] != '' && $property[258] != '' && $property[259] != '' && $property[260] != '' &&
							$property[257] != '0' && $property[258] != '0' && $property[259] != '0' && $property[260] != '0' 
						)
						{
							// We've received EER and EIR numbers. 
							// This is a URL
							$url = 'http://www2.housescape.org.uk/cgi-bin/epc.aspx?epc1=' . $property[257] . '&epc2=' . $property[258] . '&epc3=' . $property[259] . '&epc4=' . $property[260];
							
							$media_urls[] = array('url' => $url);
						}

						update_post_meta( $post_id, '_epc_urls', $media_urls );

						$this->add_log( 'Imported ' . count($media_urls) . ' EPC URLs', $property[0] );
	    			}
	    			else
	    			{
						$media_ids = array();
						$new = 0;
						$existing = 0;
						$deleted = 0;
						$previous_media_ids = get_post_meta( $post_id, '_epcs', TRUE );

						if ( 
							substr( strtolower($property[269]), 0, 2 ) == '//' || 
							substr( strtolower($property[269]), 0, 4 ) == 'http'
						)
						{
							// This is a URL
							$url = str_replace(" ", "%20", (string)$property->epc);
							$description = 'EPC';
						    
							$filename = basename( $url );

							// Check, based on the URL, whether we have previously imported this media
							$imported_previously = false;
							$imported_previously_id = '';
							if ( is_array($previous_media_ids) && !empty($previous_media_ids) )
							{
								foreach ( $previous_media_ids as $previous_media_id )
								{
									$previous_url = get_post_meta( $previous_media_id, '_imported_url', TRUE );

									if ( $previous_url == $url )
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
							        'name' => $filename,
							        'tmp_name' => $tmp
							    );

							    // Check for download errors
							    if ( is_wp_error( $tmp ) ) 
							    {
							        @unlink( $file_array[ 'tmp_name' ] );

							        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property[0] );
							    }
							    else
							    {
								    $id = media_handle_sideload( $file_array, $post_id, $description );

								    // Check for handle sideload errors.
								    if ( is_wp_error( $id ) ) 
								    {
								        @unlink( $file_array['tmp_name'] );
								        
								        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property[0] );
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

						if ( 
							$property[257] != '' && $property[258] != '' && $property[259] != '' && $property[260] != '' &&
							$property[257] != '0' && $property[258] != '0' && $property[259] != '0' && $property[260] != '0' 
						)
						{
							// We've received EER and EIR numbers. 
							// This is a URL
							$url = 'http://www2.housescape.org.uk/cgi-bin/epc.aspx?epc1=' . $property[257] . '&epc2=' . $property[258] . '&epc3=' . $property[259] . '&epc4=' . $property[260];
							$description = 'EPC';
						    
							$filename = basename( $url );

							// Check, based on the URL, whether we have previously imported this media
							$imported_previously = false;
							$imported_previously_id = '';
							if ( is_array($previous_media_ids) && !empty($previous_media_ids) )
							{
								foreach ( $previous_media_ids as $previous_media_id )
								{
									$previous_url = get_post_meta( $previous_media_id, '_imported_url', TRUE );

									if ( $previous_url == $url )
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

							        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property[0] );
							    }
							    else
							    {
								    $id = media_handle_sideload( $file_array, $post_id, $description );

								    // Check for handle sideload errors.
								    if ( is_wp_error( $id ) ) 
								    {
								        @unlink( $file_array['tmp_name'] );
								        
								        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property[0] );
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

						$this->add_log( 'Imported ' . count($media_ids) . ' epcs (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property[0] );
					}
				}
				else
				{
					$this->add_log( 'Skipping property as not been updated', $property[0] );
				}
				
				update_post_meta( $post_id, '_thesaurus_update_date_' . $import_id, $property[204] );

				do_action( "propertyhive_property_imported_thesaurus", $post_id, $property );

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
							$this->add_log( 'New meta data for ' . trim($key, '_') . ': ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), $property[0] );
						}
						elseif ( $metadata_before[$key] != $metadata_after[$key] )
						{
							$this->add_log( 'Updated ' . trim($key, '_') . '. Before: ' . ( ( is_array($metadata_before[$key]) ) ? implode(", ", $metadata_before[$key]) : $metadata_before[$key] ) . ', After: ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), $property[0] );
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
							$this->add_log( 'New taxonomy data for ' . $taxonomy_name . ': ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), $property[0] );
						}
						elseif ( $taxonomy_terms_before[$taxonomy_name] != $taxonomy_terms_after[$taxonomy_name] )
						{
							$this->add_log( 'Updated ' . $taxonomy_name . '. Before: ' . ( ( is_array($taxonomy_terms_before[$taxonomy_name]) ) ? implode(", ", $taxonomy_terms_before[$taxonomy_name]) : $taxonomy_terms_before[$taxonomy_name] ) . ', After: ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), $property[0] );
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

		do_action( "propertyhive_post_import_properties_thesaurus" );

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
			$import_refs[] = $property[0];
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

				do_action( "propertyhive_property_removed_thesaurus", $post->ID );
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
                'S' => 'For Sale',
				'L' => 'To Let',
				'1' => 'Sold',
				'2' => 'Exchanged',
				'3' => 'Withdrawn',
				'4' => 'Under Offer (sales)',
				'5' => 'Sold STC',
				'6' => 'Under Offer (rentals)',
				'7' => 'Rented',
				'A' => 'Archived',
				'D' => 'Draft',
				'F' => 'Draft Rentals',
				'W' => 'Withdrawn Rentals',
            );
        }
        if ($custom_field == 'property_type')
        {
        	return array(
                'Terraced' => 'Terraced',
                'Mews' => 'Mews',
                'Semi' => 'Semi',
                'Detached' => 'Detached',
                'Bungalow' => 'Bungalow',
                'Flat' => 'Flat',
                'Maisonette' => 'Maisonette',
                'Commercial' => 'Commercial',
                'Land' => 'Land',
            );
        }
        if ($custom_field == 'price_qualifier')
        {
        	return array(
        		'offers around' => 'offers around',
                'offer over' => 'offer over',
                'fixed price' => 'fixed price',
                'offers invited' => 'offers invited',
        	);
        }
        if ($custom_field == 'tenure')
        {
            return array(
                'Freehold' => 'Freehold',
                'Leasehold' => 'Leasehold',
                'Feudal' => 'Feudal',
                'Commonhold' => 'Commonhold',
                'Share of Freehold' => 'Share of Freehold',
            );
        }
        if ($custom_field == 'furnished')
        {
            return array(
            	'Y' => 'Yes',
                'N' => 'No',
            );
        }
    }

}

}