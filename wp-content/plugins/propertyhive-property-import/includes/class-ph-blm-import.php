<?php
/**
 * Class for managing the import process of a BLM file
 *
 * @package WordPress
 */
if ( class_exists( 'PH_Property_Import_Process' ) ) {

class PH_BLM_Import extends PH_Property_Import_Process {

	/**
	 * @var string
	 */
	private $target_file;

	/**
	 * @var array
	 */
	private $branch_ids_processed;

	/**
	 * @var string
	 */
	private $eof = '';

	/**
	 * @var string
	 */
	private $eor = '';

	/**
	 * @var array
	 */
	private $definitions;

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

		// Get BLM contents into memory
		$handle = fopen($this->target_file, "r");
        $blm_contents = fread($handle, filesize($this->target_file));
        fclose($handle);

        $this->parse_header($blm_contents);

        if (!empty($this->errors)) return false;

        $this->parse_definitions($blm_contents);

        if (!empty($this->errors)) return false;

        $this->parse_data($blm_contents);

        if (!empty($this->errors)) return false;

        return true;
	}

	private function parse_header( $blm_contents )
	{
		if ( strpos($blm_contents, '#HEADER#') !== FALSE )
		{
			$header = trim(substr($blm_contents, strpos($blm_contents, '#HEADER#')+8, strpos($blm_contents, '#DEFINITION#')-8));
	        $header_data = explode("\n", $header);

	        foreach ( $header_data as $header_row ) 
	        {
	            // get end of field character
	            if ( strpos($header_row, "EOF") !== FALSE ) 
	            {
	                $replace_array = array("EOF", " ", ":", "'", "\n", "\r");
	                $this->eof = str_replace($replace_array, "", $header_row);
	            }

	            // get end of record character
	            if ( strpos($header_row, "EOR") !== FALSE ) 
	            {
	                $replace_array = array("EOR", " ", ":", "'", "\n", "\r");
	                $this->eor = str_replace($replace_array, "", $header_row);
	            }
	        }

	        if ( $this->eof == '' )
		    {
		    	$this->add_error( 'The #HEADER# section does not specify an EOF character' );
		    }
		    if ( $this->eor == '' )
		    {
		    	$this->add_error( 'The #HEADER# section does not specify an EOR character' );
		    }
	    }
	    else
	    {
	    	$this->add_error( 'The uploaded BLM file is missing a #HEADER# section' );
	    }
	}

	private function parse_definitions( $blm_contents )
	{
		if ( strpos($blm_contents, '#DEFINITION#') !== FALSE )
		{
			$definition_length = strpos($blm_contents, $this->eor, strpos($blm_contents,'#DEFINITION#'))-strpos($blm_contents,'#DEFINITION#')-12;
	        $definition = trim( substr($blm_contents, strpos($blm_contents, '#DEFINITION#') + 12, $definition_length) );
	        $definitions = explode($this->eof, $definition);
	        
	        array_pop($definitions); // remove last blank definition field

	        $this->definitions = $definitions;
	    }
	    else
	    {
	    	$this->add_error( 'The uploaded BLM file is missing a #DEFINITION# section' );
	    }
	}

	private function parse_data( $blm_contents )
	{
		if ( strpos($blm_contents, '#DATA#') !== FALSE && strpos($blm_contents, '#END#') !== FALSE )
		{
			$data_length = strpos($blm_contents, '#END#')-strpos($blm_contents, '#DATA#')-6;
	        $data = trim(substr($blm_contents, strpos($blm_contents, '#DATA#')+6, $data_length)); 
	        $data = explode($this->eor, $data);

	        $num_with_wrong_fields = array();

	        // Loop through properties 
	        $i = 1;
	        foreach ($data as $property) 
	        {
	            $property = trim($property); // Remove any new lines from beginning of property row

	            if ( $property != '' )
	            {
		            $field_values = explode($this->eof, $property);
		                            
		            array_pop($field_values); // Remove last blank data field

		            if (count($this->definitions) == count($field_values)) 
		            {
		            	// If the correct number of fields expected
		                                
		                $property = array();
		            
		                // Loop through property fields
		                foreach ($field_values as $field_number=>$field) 
		                {
		                    // Standard fields
		                    $property[$this->definitions[$field_number]] = $field; // set by default to value in .blm
		                
		                } // Finish looping through property fields 

		                $this->properties[] = $property;
		            }
		            else
		            {
		            	// Invalid number of fields
		            	$num_with_wrong_fields[] = $i;
		            }
		        }

	            ++$i;
	        }

	        if ( !empty( $num_with_wrong_fields ) )
	        {
	        	$this->add_error( 'Properties on rows ' . implode(', ', $num_with_wrong_fields) . ' contain in an invalid number of fields' );
	        }
	    }
	    else
	    {
	    	$this->add_error( 'The uploaded BLM file is missing a #DATA# and/or #END# section' );
	    }
	}

	public function pre_test()
	{
		$this->mappings = array(); // Reset mappings in the event we're importing multiple files
		
		$passed_properties = 0;
		$failed_properties = 0;

		foreach ($this->properties as $property)
		{
			$passed = true;
			if ( !isset($property['AGENT_REF']) )
			{
				$this->add_error( 'The AGENT_REF field is missing from the BLM for one or more properties' );
				$passed = false;
			}
			elseif ( trim($property['AGENT_REF']) == '' )
			{
				$this->add_error( 'The AGENT_REF field is blank for one or more properties' );
				$passed = false;
			}

			if ( !isset($property['DISPLAY_ADDRESS']) )
			{
				$this->add_error( 'The DISPLAY_ADDRESS field is missing from the BLM', $property['AGENT_REF'] );
				$passed = false;
			}
			elseif ( trim($property['DISPLAY_ADDRESS']) == '' )
			{
				$this->add_error( 'The DISPLAY_ADDRESS field must not be blank', $property['AGENT_REF'] );
				$passed = false;
			}

			if ( !isset($property['TRANS_TYPE_ID']) )
			{
				$this->add_error( 'The TRANS_TYPE_ID field is missing from the BLM', $property['AGENT_REF'] );
				$passed = false;
			}
			elseif ( $property['TRANS_TYPE_ID'] != 1 && $property['TRANS_TYPE_ID'] != 2 )
			{
				$this->add_error( 'The TRANS_TYPE_ID field must be set to either 1 (sales) or 2 (lettings)', $property['AGENT_REF'] );
				$passed = false;
			}

			if ( $passed )
			{
				++$passed_properties;
			}
			else
			{
				++$failed_properties;
			}

			// Build mappings
			if ( isset($property['STATUS_ID']) )
			{
				if ( time() > PH_PROPERTYIMPORT_DEPARTMENT_AVAILABILITY_UPDATE )
				{	
					if ( !isset($this->mappings['sales_availability']) ) { $this->mappings['sales_availability'] = array(); }
					if ( !isset($this->mappings['lettings_availability']) ) { $this->mappings['lettings_availability'] = array(); }
					if ( !isset($this->mappings['commercial_availability']) ) { $this->mappings['commercial_availability'] = array(); }

					$this->mappings['sales_availability'][$property['STATUS_ID']] = '';
					$this->mappings['lettings_availability'][$property['STATUS_ID']] = '';
					$this->mappings['commercial_availability'][$property['STATUS_ID']] = '';
				}
				else
				{
					if ( !isset($this->mappings['availability']) ) { $this->mappings['availability'] = array(); }
					$this->mappings['availability'][$property['STATUS_ID']] = '';
				}
			}

			if ( isset($property['PROP_SUB_ID']) )
			{
				if ( !isset($this->mappings['property_type']) ) { $this->mappings['property_type'] = array(); }
				if ( !isset($this->mappings['commercial_property_type']) ) { $this->mappings['commercial_property_type'] = array(); }

				$this->mappings['property_type'][$property['PROP_SUB_ID']] = '';
				$this->mappings['commercial_property_type'][$property['PROP_SUB_ID']] = '';
			}

			if ( isset($property['PRICE_QUALIFIER']) )
			{
				if ( !isset($this->mappings['price_qualifier']) ) { $this->mappings['price_qualifier'] = array(); }

				$this->mappings['price_qualifier'][$property['PRICE_QUALIFIER']] = '';
			}

			if ( isset($property['TENURE_TYPE_ID']) )
			{
				if ( !isset($this->mappings['tenure']) ) { $this->mappings['tenure'] = array(); }
				if ( !isset($this->mappings['commercial_tenure']) ) { $this->mappings['commercial_tenure'] = array(); }

				$this->mappings['tenure'][$property['TENURE_TYPE_ID']] = '';
				$this->mappings['commercial_tenure'][$property['TENURE_TYPE_ID']] = '';
			}

			if ( isset($property['LET_FURN_ID']) )
			{
				if ( !isset($this->mappings['furnished']) ) { $this->mappings['furnished'] = array(); }

				$this->mappings['furnished'][$property['LET_FURN_ID']] = '';
			}

			if ( isset($property['BRANCH_ID']) )
			{
				if ( !isset($this->mappings['office']) ) { $this->mappings['office'] = array(); }

				$this->mappings['office'][$property['BRANCH_ID']] = '';
			}
		}

		// Sort mappings
		foreach ( $this->mappings as $custom_field_name => $custom_field_values )
		{
			ksort( $this->mappings[$custom_field_name], SORT_NUMERIC );
		}

		return array( $passed_properties, $failed_properties );
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

		$commercial_active = false;
		if ( get_option( 'propertyhive_active_departments_commercial', '' ) == 'yes' )
		{
			$commercial_active = true;
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

        $geocoding_denied = false;

        do_action( "propertyhive_pre_import_properties_blm", $this->properties );
        $this->properties = apply_filters( "propertyhive_blm_properties_due_import", $this->properties );

		$this->add_log( 'Beginning to loop through ' . count($this->properties) . ' properties' );

		$property_row = 1;
		foreach ( $this->properties as $property )
		{
			$this->add_log( 'Importing property ' . $property_row, $property['AGENT_REF'] );

			$inserted_updated = false;
			$new_property = false;

			$args = array(
	            'post_type' => 'property',
	            'posts_per_page' => 1,
	            'meta_query' => array(
	            	array(
		            	'key' => $imported_ref_key,
		            	'value' => $property['AGENT_REF']
		            )
	            )
	        );
	        $property_query = new WP_Query($args);
	        
	        if ($property_query->have_posts())
	        {
	        	$this->add_log( 'This property has been imported before. Updating it', $property['AGENT_REF'] );

	        	// We've imported this property before
	            while ($property_query->have_posts())
	            {
	                $property_query->the_post();

	                $post_id = get_the_ID();

	                $my_post = array(
				    	'ID'          	 => $post_id,
				    	'post_title'     => utf8_encode(wp_strip_all_tags( $property['DISPLAY_ADDRESS'] )),
				    	'post_excerpt'   => utf8_encode($property['SUMMARY']),
				    	'post_content' 	 => '',
				    	'post_status'    => 'publish',
				  	);

				 	// Update the post into the database
				    $post_id = wp_update_post( $my_post );

				    if ( is_wp_error( $post_id ) ) 
					{
						$this->add_error( 'Failed to update post. The error was as follows: ' . $post_id->get_error_message(), $property['AGENT_REF'] );
					}
					elseif ( $post_id == 0 )
					{
						$this->add_error( 'Failed to update post. The error was as follows: post ID is zero. Possible encoding issue', $property['AGENT_REF'] );
					}
					else
					{
						$inserted_updated = 'updated';
					}
	            }
	        }
	        else
	        {
	        	$this->add_log( 'This property hasn\'t been imported before. Inserting it', $property['AGENT_REF'] );

	        	// We've not imported this property before
				$postdata = array(
					'post_date'      => ( $property['CREATE_DATE'] ) ? date( 'Y-m-d H:i:s', strtotime( $property['CREATE_DATE'] )) : '',
					'post_date_gmt'  => ( $property['CREATE_DATE'] ) ? date( 'Y-m-d H:i:s', strtotime( $property['CREATE_DATE'] )) : '',
					'post_excerpt'   => utf8_encode($property['SUMMARY']),
				    'post_content' 	 => '',
					'post_title'     => utf8_encode(wp_strip_all_tags( $property['DISPLAY_ADDRESS'] )),
				    'post_status'    => 'publish',
					'post_type'      => 'property',
					'comment_status' => 'closed',
				);

				$post_id = wp_insert_post( $postdata, true );

				if ( is_wp_error( $post_id ) ) 
				{
					$this->add_error( 'Failed to insert post. The error was as follows: ' . $post_id->get_error_message(), $property['AGENT_REF'] );
				}
				elseif ( $post_id == 0 )
				{
					$this->add_error( 'Failed to update post. The error was as follows: post ID is zero. Possible encoding issue', $property['AGENT_REF'] );
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
					($property['DISPLAY_ADDRESS'] != '' || $property['SUMMARY'] != '')
				)
				{
					$my_post = array(
				    	'ID'          	 => $post_id,
				    	'post_title'     => htmlentities(mb_convert_encoding(wp_strip_all_tags( $property['DISPLAY_ADDRESS'] ), 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
				    	'post_excerpt'   => htmlentities(mb_convert_encoding($property['SUMMARY'], 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
				    	'post_content' 	 => '',
				    	'post_name' 	 => sanitize_title($property['DISPLAY_ADDRESS']),
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

				$this->add_log( 'Successfully ' . $inserted_updated . ' post. The post ID is ' . $post_id, $property['AGENT_REF'] );

				$previous_blm_update_date = get_post_meta( $post_id, '_blm_update_date_' . $import_id, TRUE);

				$skip_property = true;
				if (
					( isset($options['only_updated']) && $options['only_updated'] == 'yes' ) || 
					!isset($options['only_updated'])
				)
				{
					if (
						$new_property ||
						!isset($property['UPDATE_DATE']) ||
						(
							isset($property['UPDATE_DATE']) &&
							trim($property['UPDATE_DATE']) == ''
						) ||
						$previous_blm_update_date == '' ||
						(
							isset($property['UPDATE_DATE']) &&
							$property['UPDATE_DATE'] != '' &&
							$previous_blm_update_date != '' &&
							strtotime($property['UPDATE_DATE']) > strtotime($previous_blm_update_date)
						)
					)
					{
						$skip_property = false;
					}
				}
				else
				{
					$skip_property = false;
				}

				$lat = get_post_meta( $post_id, '_latitude', TRUE);
				$lng = get_post_meta( $post_id, '_longitude', TRUE);

				if ( !$geocoding_denied && ($lat == '' || $lng == '' || $lat == '0' || $lng == '0') )
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
							if ( isset($property['ADDRESS_1']) && trim($property['ADDRESS_1']) != '' ) { $address_to_geocode[] = $property['ADDRESS_1']; }
							if ( isset($property['ADDRESS_2']) && trim($property['ADDRESS_2']) != '' ) { $address_to_geocode[] = $property['ADDRESS_2']; }
							if ( isset($property['ADDRESS_3']) && trim($property['ADDRESS_3']) != '' ) { $address_to_geocode[] = $property['ADDRESS_3']; }
							if ( isset($property['TOWN']) && trim($property['TOWN']) != '' ) { $address_to_geocode[] = $property['TOWN']; }
							if ( isset($property['ADDRESS_4']) && trim($property['ADDRESS_4']) != '' ) { $address_to_geocode[] = $property['ADDRESS_4']; }
							if ( isset($property['POSTCODE1']) && isset($property['POSTCODE2']) ) { $address_to_geocode[] = trim($property['POSTCODE1'] . ' ' . $property['POSTCODE2']); }

							$country = get_option( 'propertyhive_default_country', 'GB' );
							$request_url = "https://maps.googleapis.com/maps/api/geocode/xml?address=" . urlencode( implode( ", ", $address_to_geocode ) ) . "&sensor=false&region=" . strtolower($country); // the request URL you'll send to google to get back your XML feed
		                    
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
						                //request returned completed time to get lat / lng for storage
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
							        	if ( $status == "REQUEST_DENIED" )
							        	{
							        		$geocoding_denied = true;
							        	}

							        	$this->add_error( 'Google Geocoding service returned status ' . $status, $property['AGENT_REF'] );
							        	sleep(3);
							        }
							    }
						        else
						        {
						        	$this->add_error( 'Failed to parse XML response from Google Geocoding service', $property['AGENT_REF'] );
						        }
						    }
					        else
					        {
					        	$this->add_error( 'Invalid response when trying to obtain co-ordinates', $property['AGENT_REF'] );
					        }
						}
						else
				        {
				        	$this->add_error( 'Failed to obtain co-ordinates as allow_url_fopen setting is disabled', $property['AGENT_REF'] );
				        }
			        }
				    else
				    {
				    	$this->add_log( 'Not performing Google Geocoding request as no API key present in settings', $property['AGENT_REF'] );
				    }
				}

				if ( !$skip_property )
				{
					update_post_meta( $post_id, $imported_ref_key, $property['AGENT_REF'] );

					// Address
					update_post_meta( $post_id, '_reference_number', $property['AGENT_REF'] );
					update_post_meta( $post_id, '_address_name_number', ( ( isset($property['ADDRESS_1']) ) ? $property['ADDRESS_1'] : '' ) );
					update_post_meta( $post_id, '_address_street', ( ( isset($property['ADDRESS_2']) ) ? $property['ADDRESS_2'] : '' ) );
					update_post_meta( $post_id, '_address_two', ( (isset($property['TOWN']) && isset($property['ADDRESS_3'])) ? $property['ADDRESS_3'] : '' ) );
					update_post_meta( $post_id, '_address_three', ( ( ( isset($property['TOWN']) ) ? $property['TOWN'] : ( ( isset($property['ADDRESS_3']) ) ? $property['ADDRESS_3'] : '' ) ) ) );
					update_post_meta( $post_id, '_address_four', ( ( isset($property['ADDRESS_4']) ) ? $property['ADDRESS_4'] : '' ) );
					update_post_meta( $post_id, '_address_postcode', trim( ( ( isset($property['POSTCODE1']) ) ? $property['POSTCODE1'] : '' ) . ' ' . ( ( isset($property['POSTCODE2']) ) ? $property['POSTCODE2'] : '' ) ) );

					// We don't get country in the BLM so assume it's the default country
					$country = get_option( 'propertyhive_default_country', 'GB' );
					update_post_meta( $post_id, '_address_country', $country );

					// Check main address fields and see if this location exists as a taxonomy to try and assign properties to location
					$address_fields_to_check = apply_filters( 'propertyhive_blm_address_fields_to_check', array('ADDRESS_2', 'ADDRESS_3', 'TOWN', 'ADDRESS_4') );
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
					if ( isset($_POST['mapped_office'][$property['BRANCH_ID']]) && $_POST['mapped_office'][$property['BRANCH_ID']] != '' )
					{
						$office_id = $_POST['mapped_office'][$property['BRANCH_ID']];
					}
					elseif ( isset($options['offices']) && is_array($options['offices']) && !empty($options['offices']) )
					{
						foreach ( $options['offices'] as $ph_office_id => $branch_code )
						{
							if ( $branch_code == $property['BRANCH_ID'] )
							{
								$office_id = $ph_office_id;
								break;
							}
						}
					}
					update_post_meta( $post_id, '_office_id', $office_id );

					$department = 'residential-sales';
					if ( $property['TRANS_TYPE_ID'] == '2' )
					{
						$department = 'residential-lettings';
					}
					if ( $commercial_active )
					{
						// Commercial is active.
						// Does this property have any commecial characteristics
						if ( isset( $property['LET_TYPE_ID'] ) && $property['LET_TYPE_ID'] == 4 )
						{
							$department = 'commercial';
						}
						else
						{
							// Check if the type is any of the commercial types
							$commercial_property_types = $this->get_blm_mapping_values('commercial_property_type');
							if ( isset($commercial_property_types[$property['PROP_SUB_ID']]) )
							{
								$department = 'commercial';
							}
						}
					}

					// Is the property portal add on activated
					if (class_exists('PH_Property_Portal'))
	        		{
	        			if ( 
	        				isset($branch_mappings[str_replace("residential-", "", $department)][$property['BRANCH_ID']]) &&
	        				$branch_mappings[str_replace("residential-", "", $department)][$property['BRANCH_ID']] != ''
	        			)
	        			{
	        				$explode_agent_branch = explode("|", $branch_mappings[str_replace("residential-", "", $department)][$property['BRANCH_ID']]);
	        				update_post_meta( $post_id, '_agent_id', $explode_agent_branch[0] );
	        				update_post_meta( $post_id, '_branch_id', $explode_agent_branch[1] );

	        				$this->branch_ids_processed[] = $explode_agent_branch[1];
	        			}
	        		}

					// Residential Details
					update_post_meta( $post_id, '_department', $department );
					update_post_meta( $post_id, '_bedrooms', ( ( isset($property['BEDROOMS']) ) ? $property['BEDROOMS'] : '' ) );
					update_post_meta( $post_id, '_bathrooms', ( ( isset($property['BATHROOMS']) ) ? $property['BATHROOMS'] : '' ) );
					update_post_meta( $post_id, '_reception_rooms', ( ( isset($property['LIVING_ROOMS']) ) ? $property['LIVING_ROOMS'] : '' ) );

					// Property Type
					if ( $department == 'residential-sales' || $department == 'residential-lettings' )
					{
						$taxonomy = 'property_type';
			        }
			        elseif ( $department == 'commercial' )
			        {
			        	$taxonomy = 'commercial_property_type';
			        }

			        if ( isset($_POST['mapped_' . $taxonomy]) )
					{
						$mapping = $_POST['mapped_' . $taxonomy];
					}
					else
					{
						$mapping = isset($options['mappings'][$taxonomy]) ? $options['mappings'][$taxonomy] : array();
					}
					
					wp_delete_object_term_relationships( $post_id, $taxonomy );

					if ( isset($property['PROP_SUB_ID']) && $property['PROP_SUB_ID'] != '' )
					{
						if ( !empty($mapping) && isset($mapping[$property['PROP_SUB_ID']]) )
						{
			                wp_set_post_terms( $post_id, $mapping[$property['PROP_SUB_ID']], $taxonomy );
			            }
			            else
			            {
			            	$this->add_log( 'Property received with a type (' . $property['PROP_SUB_ID'] . ') that is not mapped', $property['AGENT_REF'] );

			            	$options = $this->add_missing_mapping( $mapping, $taxonomy, $property['PROP_SUB_ID'], $import_id );
			            }
			        }

					// Clean price
					$property['PRICE'] = preg_replace("/[^0-9.]/", '', $property['PRICE']);

					// Residential Sales Details
					if ( $department == 'residential-sales' )
					{
						update_post_meta( $post_id, '_price', $property['PRICE'] );
						update_post_meta( $post_id, '_price_actual', $property['PRICE'] );
						update_post_meta( $post_id, '_poa', ( isset($property['PRICE_QUALIFIER']) && $property['PRICE_QUALIFIER'] == '1' ) ? 'yes' : '' );
						
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
						if ( !empty($mapping) && isset($property['PRICE_QUALIFIER']) && isset($mapping[$property['PRICE_QUALIFIER']]) )
						{
			                wp_set_post_terms( $post_id, $mapping[$property['PRICE_QUALIFIER']], 'price_qualifier' );
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
			            if ( !empty($mapping) && isset($property['TENURE_TYPE_ID']) && isset($mapping[$property['TENURE_TYPE_ID']]) )
						{
			                wp_set_post_terms( $post_id, $mapping[$property['TENURE_TYPE_ID']], 'tenure' );
			            }
					}

					// Residential Lettings Details
					if ( $department == 'residential-lettings' )
					{
						update_post_meta( $post_id, '_rent', $property['PRICE'] );

						$rent_frequency = 'pcm';
						$price_actual = $property['PRICE'];
						switch ($property['LET_RENT_FREQUENCY'])
						{
							case "0": { $rent_frequency = 'pw'; $price_actual = ($property['PRICE'] * 52) / 12; break; }
							case "1": { $rent_frequency = 'pcm'; $price_actual = $property['PRICE']; break; }
							case "2": { $rent_frequency = 'pq'; $price_actual = ($property['PRICE'] * 4) / 12; break; }
							case "3": { $rent_frequency = 'pa'; $price_actual = $property['PRICE'] / 12; break; }
							case "5": 
							{
								$rent_frequency = 'pppw';
								$bedrooms = ( isset($property['BEDROOMS']) ? $property['BEDROOMS'] : '0' );
								if ( $bedrooms != '' && $bedrooms != 0 )
								{
									$price_actual = (($property['PRICE'] * 52) / 12) * $bedrooms;
								}
								else
								{
									$price_actual = ($property['PRICE'] * 52) / 12;
								}
								break; 
							}
						}
						update_post_meta( $post_id, '_rent_frequency', $rent_frequency );
						update_post_meta( $post_id, '_price_actual', $price_actual );

						update_post_meta( $post_id, '_currency', 'GBP' );
						
						update_post_meta( $post_id, '_poa', ( isset($property['PRICE_QUALIFIER']) && $property['PRICE_QUALIFIER'] == '1' ) ? 'yes' : '' );

						update_post_meta( $post_id, '_deposit', preg_replace( "/[^0-9.]/", '', ( ( isset($property['LET_BOND']) ) ? $property['LET_BOND'] : '' ) ) );
	            		update_post_meta( $post_id, '_available_date', ( (isset($property['LET_DATE_AVAILABLE']) && $property['LET_DATE_AVAILABLE'] != '') ? date("Y-m-d", strtotime($property['LET_DATE_AVAILABLE'])) : '' ) );

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
						if ( !empty($mapping) && isset($property['LET_FURN_ID']) && isset($mapping[$property['LET_FURN_ID']]) )
						{
			                wp_set_post_terms( $post_id, $mapping[$property['LET_FURN_ID']], 'furnished' );
			            }
					}

					// Commercial Details
					if ( $department == 'commercial' )
					{
						update_post_meta( $post_id, '_for_sale', '' );
	            		update_post_meta( $post_id, '_to_rent', '' );

	            		if ( $property['TRANS_TYPE_ID'] == '1' )
	            		{
	            			update_post_meta( $post_id, '_for_sale', 'yes' );

	            			update_post_meta( $post_id, '_commercial_price_currency', 'GBP' );

	            			update_post_meta( $post_id, '_price_from', $property['PRICE'] );
	            			update_post_meta( $post_id, '_price_to', $property['PRICE'] );
	            			update_post_meta( $post_id, '_price_units', '' );
	            			update_post_meta( $post_id, '_price_poa', ( isset($property['PRICE_QUALIFIER']) && $property['PRICE_QUALIFIER'] == '1' ) ? 'yes' : '' );

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
							if ( !empty($mapping) && isset($property['PRICE_QUALIFIER']) && isset($mapping[$property['PRICE_QUALIFIER']]) )
							{
				                wp_set_post_terms( $post_id, $mapping[$property['PRICE_QUALIFIER']], 'price_qualifier' );
				            }

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
				            if ( !empty($mapping) && isset($property['TENURE_TYPE_ID']) && isset($mapping[$property['TENURE_TYPE_ID']]) )
							{
				                wp_set_post_terms( $post_id, $mapping[$property['TENURE_TYPE_ID']], 'commercial_tenure' );
				            }
	            		}
	            		elseif ( $property['TRANS_TYPE_ID'] == '2' )
	            		{
	            			update_post_meta( $post_id, '_to_rent', 'yes' );

	            			update_post_meta( $post_id, '_commercial_rent_currency', 'GBP' );

	            			update_post_meta( $post_id, '_rent_from', $property['PRICE'] );
	            			update_post_meta( $post_id, '_rent_to', $property['PRICE'] );
	            			$rent_units = '';
	            			switch ($property['LET_RENT_FREQUENCY'])
							{
		            			case "0": { $rent_units = 'pw'; break; }
								case "1": { $rent_units = 'pcm'; break; }
								case "2": { $rent_units = 'pq'; break; }
								case "3": { $rent_units = 'pa'; break; }
							}
							update_post_meta( $post_id, '_rent_units', $rent_units );
	            			update_post_meta( $post_id, '_rent_poa', ( isset($property['PRICE_QUALIFIER']) && $property['PRICE_QUALIFIER'] == '1' ) ? 'yes' : '' );
	            		}

	            		// Store price in common currency (GBP) used for ordering
			            $ph_countries = new PH_Countries();
			            $ph_countries->update_property_price_actual( $post_id );

	            		$size = '';
	            		$unit = 'sqft';
	            		if ( isset($_POST['MIN_SIZE_ENTERED']) )
	            		{
		            		$size = preg_replace("/[^0-9.]/", '', $_POST['MIN_SIZE_ENTERED']);

				            if ( isset($property['AREA_SIZE_UNIT_ID']) )
				            {
				            	switch ( $property['AREA_SIZE_UNIT_ID'] )
				            	{
				            		case "1": { $unit = 'sqft'; break; }
				            		case "2": { $unit = 'sqm'; break; }
				            		case "3": { $unit = 'acre'; break; }
				            		case "4": { $unit = 'hectare'; break; }
				            	}
				            }
				        }
				        update_post_meta( $post_id, '_floor_area_from', $size );
				        update_post_meta( $post_id, '_floor_area_from_sqft', convert_size_to_sqft( $size, $unit ) );

				        $size = '';
	            		$unit = 'sqft';
				        if ( isset($_POST['MAX_SIZE_ENTERED']) )
	            		{
		            		$size = preg_replace("/[^0-9.]/", '', $_POST['MAX_SIZE_ENTERED']);

				            if ( isset($property['AREA_SIZE_UNIT_ID']) )
				            {
				            	switch ( $property['AREA_SIZE_UNIT_ID'] )
				            	{
				            		case "1": { $unit = 'sqft'; break; }
				            		case "2": { $unit = 'sqm'; break; }
				            		case "3": { $unit = 'acre'; break; }
				            		case "4": { $unit = 'hectare'; break; }
				            	}
				            
				            }
				        }
				        update_post_meta( $post_id, '_floor_area_to', $size );
				        update_post_meta( $post_id, '_floor_area_to_sqft', convert_size_to_sqft( $size, $unit ) );
					}

					// Marketing
					update_post_meta( $post_id, '_on_market', ( $property['PUBLISHED_FLAG'] == '1' ) ? 'yes' : '' );
					
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
					if ( !empty($mapping) && isset($property['STATUS_ID']) && isset($mapping[$property['STATUS_ID']]) )
					{
		                wp_set_post_terms( $post_id, $mapping[$property['STATUS_ID']], 'availability' );
		            }

					// Features
					$features = array();
					for ( $i = 1; $i <= 10; ++$i )
					{
						if ( isset($property['FEATURE' . $i]) && trim($property['FEATURE' . $i]) != '' )
						{
							$features[] = trim($property['FEATURE' . $i]);
						}
					}

					update_post_meta( $post_id, '_features', count( $features ) );
	        		
	        		$i = 0;
			        foreach ( $features as $feature )
			        {
			            update_post_meta( $post_id, '_feature_' . $i, $feature );
			            ++$i;
			        }

			        if ( $department != 'commercial' )
					{
						// For now put the whole description in one room
						update_post_meta( $post_id, '_rooms', '1' );
						update_post_meta( $post_id, '_room_name_0', '' );
						update_post_meta( $post_id, '_room_dimensions_0', '' );

						// Attempt to solve an encoding issue. Set to blank first, insert, and if blank, utf8encode and insert again
						update_post_meta( $post_id, '_room_description_0', '' );
			            update_post_meta( $post_id, '_room_description_0', $property['DESCRIPTION'] );
			            if ( get_post_meta( $post_id, '_room_description_0', TRUE ) == '' )
			            {
				            update_post_meta( $post_id, '_room_description_0', utf8_encode($property['DESCRIPTION']) );
				        }
				    }
				    else
				    {
				    	// For now put the whole description in one description
				    	update_post_meta( $post_id, '_descriptions', '1' );
						update_post_meta( $post_id, '_description_name_0', '' );

						update_post_meta( $post_id, '_description_0', '' );
	            		update_post_meta( $post_id, '_description_0', $property['DESCRIPTION'] );
	            		if ( get_post_meta( $post_id, '_description_0', TRUE ) == '' )
			            {
				            update_post_meta( $post_id, '_description_0', utf8_encode($property['DESCRIPTION']) );
				        }
				    }

				    $files_to_unlink = array();

					// Media - Images
					if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
        			{
        				$media_urls = array();
        				for ( $i = 0; $i <= 49; ++$i )
						{
							$j = str_pad( $i, 2, '0', STR_PAD_LEFT );

							if ( isset($property['MEDIA_IMAGE_' . $j]) && trim($property['MEDIA_IMAGE_' . $j]) != '' )
							{
								if ( 
									substr( strtolower($property['MEDIA_IMAGE_' . $j]), 0, 2 ) == '//' || 
									substr( strtolower($property['MEDIA_IMAGE_' . $j]), 0, 4 ) == 'http'
								)
								{
									// This is a URL
									$url = $property['MEDIA_IMAGE_' . $j];

									$media_urls[] = array('url' => $url);
								}
							}
						}
						update_post_meta( $post_id, '_photo_urls', $media_urls );

						$this->add_log( 'Imported ' . count($media_urls) . ' photo URLs', $property['AGENT_REF'] );
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

							if ( isset($property['MEDIA_IMAGE_' . $j]) && trim($property['MEDIA_IMAGE_' . $j]) != '' )
							{
								if ( 
									substr( strtolower($property['MEDIA_IMAGE_' . $j]), 0, 2 ) == '//' || 
									substr( strtolower($property['MEDIA_IMAGE_' . $j]), 0, 4 ) == 'http'
								)
								{
									// This is a URL
									$url = $property['MEDIA_IMAGE_' . $j];
									$description = ( ( isset($property['MEDIA_IMAGE_TEXT_' . $j]) && $property['MEDIA_IMAGE_TEXT_' . $j] != '' ) ? $property['MEDIA_IMAGE_TEXT_' . $j] : '' );
								    
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

									        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['AGENT_REF'] );
									    }
									    else
									    {
										    $id = media_handle_sideload( $file_array, $post_id, $description );

										    // Check for handle sideload errors.
										    if ( is_wp_error( $id ) ) 
										    {
										        @unlink( $file_array['tmp_name'] );
										        
										        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['AGENT_REF'] );
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
								else
								{
									// Not a URL. Must've been physically uploaded or already exists
									$media_file_name = $property['MEDIA_IMAGE_' . $j];
									$media_folder = dirname( $this->target_file );
									$description = ( ( isset($property['MEDIA_IMAGE_TEXT_' . $j]) && $property['MEDIA_IMAGE_TEXT_' . $j] != '' ) ? $property['MEDIA_IMAGE_TEXT_' . $j] : '' );

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
			                                    	$this->add_error( 'Failed to get filesize of new image file ' . $media_folder . '/' . $media_file_name, $property['AGENT_REF'] );
			                                    }
		                                        
		                                        unset($new_image_size);
		                                    }
		                                    else
		                                    {
		                                    	$this->add_error( 'Failed to get filesize of existing image file ' . $current_image_path, $property['AGENT_REF'] );
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
		                                        	$this->add_error( 'Failed inserting image attachment ' . $upload['file'] . ' - ' . print_r($attachment, TRUE), $property['AGENT_REF'] );
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

		                                		if ( $description != '' )
												{
													$my_post = array(
												    	'ID'          	 => $previous_media_ids[$i],
												    	'post_title'     => $description,
												    );

												 	// Update the post into the database
												    wp_update_post( $my_post );
												}

												++$existing;
		                                	}
			                            }

			                            $files_to_unlink[] = $media_folder . '/' . $media_file_name;
									}
									else
									{
										if ( isset($previous_media_ids[$i]) ) 
				                    	{
				                    		$media_ids[] = $previous_media_ids[$i];

				                    		if ( $description != '' )
											{
												$my_post = array(
											    	'ID'          	 => $previous_media_ids[$i],
											    	'post_title'     => $description,
											    );

											 	// Update the post into the database
											    wp_update_post( $my_post );
											}

											++$existing;
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

						$this->add_log( 'Imported ' . count($media_ids) . ' photos (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property['AGENT_REF'] );
					}

					// Media - Floorplans
					if ( get_option('propertyhive_floorplans_stored_as', '') == 'urls' )
        			{
        				$media_urls = array();
        				for ( $i = 0; $i <= 49; ++$i )
						{
							$j = str_pad( $i, 2, '0', STR_PAD_LEFT );

							if ( isset($property['MEDIA_FLOOR_PLAN_' . $j]) && trim($property['MEDIA_FLOOR_PLAN_' . $j]) != '' )
							{
								if ( 
									substr( strtolower($property['MEDIA_FLOOR_PLAN_' . $j]), 0, 2 ) == '//' || 
									substr( strtolower($property['MEDIA_FLOOR_PLAN_' . $j]), 0, 4 ) == 'http'
								)
								{
									// This is a URL
									$url = $property['MEDIA_FLOOR_PLAN_' . $j];

									$media_urls[] = array('url' => $url);
								}
							}
						}
						update_post_meta( $post_id, '_floorplan_urls', $media_urls );

						$this->add_log( 'Imported ' . count($media_urls) . ' floorplan URLs', $property['AGENT_REF'] );
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

							if ( isset($property['MEDIA_FLOOR_PLAN_' . $j]) && trim($property['MEDIA_FLOOR_PLAN_' . $j]) != '' )
							{
								if ( 
									substr( strtolower($property['MEDIA_FLOOR_PLAN_' . $j]), 0, 2 ) == '//' || 
									substr( strtolower($property['MEDIA_FLOOR_PLAN_' . $j]), 0, 4 ) == 'http'
								)
								{
									// This is a URL
									$url = $property['MEDIA_FLOOR_PLAN_' . $j];
									$description = ( ( isset($property['MEDIA_FLOOR_PLAN_TEXT_' . $j]) && $property['MEDIA_FLOOR_PLAN_TEXT_' . $j] != '' ) ? $property['MEDIA_FLOOR_PLAN_TEXT_' . $j] : '' );
								    
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
									        'name' => $filename,
									        'tmp_name' => $tmp
									    );

									    // Check for download errors
									    if ( is_wp_error( $tmp ) ) 
									    {
									        @unlink( $file_array[ 'tmp_name' ] );

									        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['AGENT_REF'] );
									    }
									    else
									    {
										    $id = media_handle_sideload( $file_array, $post_id, $description );

										    // Check for handle sideload errors.
										    if ( is_wp_error( $id ) ) 
										    {
										        @unlink( $file_array['tmp_name'] );
										        
										        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['AGENT_REF'] );
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
								else
								{
									// Not a URL. Must've been physically uploaded or already exists
									$media_file_name = $property['MEDIA_FLOOR_PLAN_' . $j];
									$media_folder = dirname( $this->target_file );
									$description = ( ( isset($property['MEDIA_FLOOR_PLAN_TEXT_' . $j]) && $property['MEDIA_FLOOR_PLAN_TEXT_' . $j] != '' ) ? $property['MEDIA_FLOOR_PLAN_TEXT_' . $j] : '' );

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
			                                    	$this->add_error( 'Failed to get filesize of new floorplan file ' . $media_folder . '/' . $media_file_name, $property['AGENT_REF'] );
			                                    }
		                                        
		                                        unset($new_image_size);
		                                    }
		                                    else
		                                    {
		                                    	$this->add_error( 'Failed to get filesize of existing floorplan file ' . $current_image_path, $property['AGENT_REF'] );
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
		                                        	$this->add_error( 'Failed inserting floorplan attachment ' . $upload['file'] . ' - ' . print_r($attachment, TRUE), $property['AGENT_REF'] );
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

		                                		if ( $description != '' )
												{
													$my_post = array(
												    	'ID'          	 => $previous_media_ids[$i],
												    	'post_title'     => $description,
												    );

												 	// Update the post into the database
												    wp_update_post( $my_post );
												}

												++$existing;
		                                	}
			                            }

			                            $files_to_unlink[] = $media_folder . '/' . $media_file_name;
									}
									else
									{
										if ( isset($previous_media_ids[$i]) ) 
				                    	{
				                    		$media_ids[] = $previous_media_ids[$i];

				                    		if ( $description != '' )
											{
												$my_post = array(
											    	'ID'          	 => $previous_media_ids[$i],
											    	'post_title'     => $description,
											    );

											 	// Update the post into the database
											    wp_update_post( $my_post );
											}

											++$existing;
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

						$this->add_log( 'Imported ' . count($media_ids) . ' floorplans (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property['AGENT_REF'] );
					}

					// Media - Brochures
					if ( get_option('propertyhive_brochures_stored_as', '') == 'urls' )
        			{
        				$media_urls = array();
        				for ( $i = 0; $i <= 49; ++$i )
						{
							$j = str_pad( $i, 2, '0', STR_PAD_LEFT );

							if ( isset($property['MEDIA_DOCUMENT_' . $j]) && trim($property['MEDIA_DOCUMENT_' . $j]) != '' )
							{
								if ( 
									substr( strtolower($property['MEDIA_DOCUMENT_' . $j]), 0, 2 ) == '//' || 
									substr( strtolower($property['MEDIA_DOCUMENT_' . $j]), 0, 4 ) == 'http'
								)
								{
									// This is a URL
									$url = $property['MEDIA_DOCUMENT_' . $j];

									$media_urls[] = array('url' => $url);
								}
							}
						}
						update_post_meta( $post_id, '_brochure_urls', $media_urls );

						$this->add_log( 'Imported ' . count($media_urls) . ' brochure URLs', $property['AGENT_REF'] );
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

							if ( isset($property['MEDIA_DOCUMENT_' . $j]) && trim($property['MEDIA_DOCUMENT_' . $j]) != '' )
							{
								if ( 
									substr( strtolower($property['MEDIA_DOCUMENT_' . $j]), 0, 2 ) == '//' || 
									substr( strtolower($property['MEDIA_DOCUMENT_' . $j]), 0, 4 ) == 'http'
								)
								{
									// This is a URL
									$url = $property['MEDIA_DOCUMENT_' . $j];
									$description = ( ( isset($property['MEDIA_DOCUMENT_TEXT_' . $j]) && $property['MEDIA_DOCUMENT_TEXT_' . $j] != '' ) ? $property['MEDIA_DOCUMENT_TEXT_' . $j] : '' );
								    
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
										$filename = $post_id . '.pdf'; // we do this instead of what's referenced in the BLM as not always linking direct to a PDF. Something it might link to a PHP which then serves the PDF

									    $tmp = download_url( $url );
									    $file_array = array(
									        'name' => $filename,
									        'tmp_name' => $tmp
									    );

									    // Check for download errors
									    if ( is_wp_error( $tmp ) ) 
									    {
									        @unlink( $file_array[ 'tmp_name' ] );

									        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['AGENT_REF'] );
									    }
									    else
									    {
										    $id = media_handle_sideload( $file_array, $post_id, $description );

										    // Check for handle sideload errors.
										    if ( is_wp_error( $id ) ) 
										    {
										        @unlink( $file_array['tmp_name'] );
										        
										        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['AGENT_REF'] );
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
								else
								{
									// Not a URL. Must've been physically uploaded or already exists
									$media_file_name = $property['MEDIA_DOCUMENT_' . $j];
									$media_folder = dirname( $this->target_file );
									$description = ( ( isset($property['MEDIA_DOCUMENT_TEXT_' . $j]) && $property['MEDIA_DOCUMENT_TEXT_' . $j] != '' ) ? $property['MEDIA_DOCUMENT_TEXT_' . $j] : '' );

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
			                                    	$this->add_error( 'Failed to get filesize of new brochure file ' . $media_folder . '/' . $media_file_name, $property['AGENT_REF'] );
			                                    }
		                                        
		                                        unset($new_image_size);
		                                    }
		                                    else
		                                    {
		                                    	$this->add_error( 'Failed to get filesize of existing brochure file ' . $current_image_path, $property['AGENT_REF'] );
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
		                                        	$this->add_error( 'Failed inserting brochure attachment ' . $upload['file'] . ' - ' . print_r($attachment, TRUE), $property['AGENT_REF'] );
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

		                                		if ( $description != '' )
												{
													$my_post = array(
												    	'ID'          	 => $previous_media_ids[$i],
												    	'post_title'     => $description,
												    );

												 	// Update the post into the database
												    wp_update_post( $my_post );
												}

												++$existing;
		                                	}
			                            }

			                            $files_to_unlink[] = $media_folder . '/' . $media_file_name;
									}
									else
									{
										if ( isset($previous_media_ids[$i]) ) 
				                    	{
				                    		$media_ids[] = $previous_media_ids[$i];

				                    		if ( $description != '' )
											{
												$my_post = array(
											    	'ID'          	 => $previous_media_ids[$i],
											    	'post_title'     => $description,
											    );

											 	// Update the post into the database
											    wp_update_post( $my_post );
											}

											++$existing;
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

						$this->add_log( 'Imported ' . count($media_ids) . ' brochures (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property['AGENT_REF'] );
					}

					// Media - EPCS
					if ( get_option('propertyhive_epcs_stored_as', '') == 'urls' )
        			{
        				$media_urls = array();

        				for ( $i = 60; $i <= 61; ++$i )
						{
							$j = str_pad( $i, 2, '0', STR_PAD_LEFT );

							if ( isset($property['MEDIA_IMAGE_' . $j]) && trim($property['MEDIA_IMAGE_' . $j]) != '' )
							{
								if ( 
									substr( strtolower($property['MEDIA_IMAGE_' . $j]), 0, 2 ) == '//' || 
									substr( strtolower($property['MEDIA_IMAGE_' . $j]), 0, 4 ) == 'http'
								)
								{
									// This is a URL
									$url = $property['MEDIA_IMAGE_' . $j];

									$media_urls[] = array('url' => $url);
								}
							}
						}
						for ( $i = 50; $i <= 55; ++$i )
						{
							$j = str_pad( $i, 2, '0', STR_PAD_LEFT );

							if ( isset($property['MEDIA_DOCUMENT_' . $j]) && trim($property['MEDIA_DOCUMENT_' . $j]) != '' )
							{
								if ( 
									substr( strtolower($property['MEDIA_DOCUMENT_' . $j]), 0, 2 ) == '//' || 
									substr( strtolower($property['MEDIA_DOCUMENT_' . $j]), 0, 4 ) == 'http'
								)
								{
									// This is a URL
									$url = $property['MEDIA_DOCUMENT_' . $j];

									$media_urls[] = array('url' => $url);
								}
							}
						}

						update_post_meta( $post_id, '_epc_urls', $media_urls );

						$this->add_log( 'Imported ' . count($media_urls) . ' EPC URLs', $property['AGENT_REF'] );
        			}
        			else
        			{
						$media_ids = array();
						$new = 0;
						$existing = 0;
						$deleted = 0;
						$previous_media_ids = get_post_meta( $post_id, '_epcs', TRUE );

						$previous_media_i = 0;

						for ( $i = 60; $i <= 61; ++$i )
						{
							$j = str_pad( $i, 2, '0', STR_PAD_LEFT );

							if ( isset($property['MEDIA_IMAGE_' . $j]) && trim($property['MEDIA_IMAGE_' . $j]) != '' )
							{
								if ( 
									substr( strtolower($property['MEDIA_IMAGE_' . $j]), 0, 2 ) == '//' || 
									substr( strtolower($property['MEDIA_IMAGE_' . $j]), 0, 4 ) == 'http'
								)
								{
									// This is a URL
									$url = $property['MEDIA_IMAGE_' . $j];
									$description = ( ( isset($property['MEDIA_IMAGE_' . $j]) && $property['MEDIA_IMAGE_' . $j] != '' ) ? $property['MEDIA_IMAGE_' . $j] : '' );
								    
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
									        'name' => $filename,
									        'tmp_name' => $tmp
									    );

									    // Check for download errors
									    if ( is_wp_error( $tmp ) ) 
									    {
									        @unlink( $file_array[ 'tmp_name' ] );

									        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['AGENT_REF'] );
									    }
									    else
									    {
										    $id = media_handle_sideload( $file_array, $post_id, $description );

										    // Check for handle sideload errors.
										    if ( is_wp_error( $id ) ) 
										    {
										        @unlink( $file_array['tmp_name'] );
										        
										        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['AGENT_REF'] );
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
								else
								{
									// Not a URL. Must've been physically uploaded or already exists
									$media_file_name = $property['MEDIA_IMAGE_' . $j];
									$media_folder = dirname( $this->target_file );
									$description = ( ( isset($property['MEDIA_IMAGE_TEXT_' . $j]) && $property['MEDIA_IMAGE_TEXT_' . $j] != '' ) ? $property['MEDIA_IMAGE_TEXT_' . $j] : '' );

									if ( file_exists( $media_folder . '/' . $media_file_name ) )
									{
										$upload = true;
		                                $replacing_attachment_id = '';
		                                if ( isset($previous_media_ids[$previous_media_i]) ) 
		                                {                                    
		                                    // get this attachment
		                                    $current_image_path = get_post_meta( $previous_media_ids[$previous_media_i], '_imported_path', TRUE );
		                                    $current_image_size = filesize( $current_image_path );
		                                    
		                                    if ($current_image_size > 0 && $current_image_size !== FALSE)
		                                    {
		                                        $replacing_attachment_id = $previous_media_ids[$previous_media_i];
		                                        
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
			                                    	$this->add_error( 'Failed to get filesize of new EPC file ' . $media_folder . '/' . $media_file_name, $property['AGENT_REF'] );
			                                    }
		                                        
		                                        unset($new_image_size);
		                                    }
		                                    else
		                                    {
		                                    	$this->add_error( 'Failed to get filesize of existing EPC file ' . $current_image_path, $property['AGENT_REF'] );
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
		                                        	$this->add_error( 'Failed inserting brochure attachment ' . $upload['file'] . ' - ' . print_r($attachment, TRUE), $property['AGENT_REF'] );
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
			                            	if ( isset($previous_media_ids[$previous_media_i]) ) 
		                                	{
		                                		$media_ids[] = $previous_media_ids[$previous_media_i];

		                                		if ( $description != '' )
												{
													$my_post = array(
												    	'ID'          	 => $previous_media_ids[$previous_media_i],
												    	'post_title'     => $description,
												    );

												 	// Update the post into the database
												    wp_update_post( $my_post );
												}

												++$existing;
		                                	}
			                            }

			                            $files_to_unlink[] = $media_folder . '/' . $media_file_name;
									}
									else
									{
										if ( isset($previous_media_ids[$previous_media_i]) ) 
				                    	{
				                    		$media_ids[] = $previous_media_ids[$previous_media_i];

				                    		if ( $description != '' )
											{
												$my_post = array(
											    	'ID'          	 => $previous_media_ids[$previous_media_i],
											    	'post_title'     => $description,
											    );

											 	// Update the post into the database
											    wp_update_post( $my_post );
											}

											++$existing;
				                    	}
									}
								}

								++$previous_media_i;
							}
						}
						for ( $i = 50; $i <= 55; ++$i )
						{
							$j = str_pad( $i, 2, '0', STR_PAD_LEFT );

							if ( isset($property['MEDIA_DOCUMENT_' . $j]) && trim($property['MEDIA_DOCUMENT_' . $j]) != '' )
							{
								if ( 
									substr( strtolower($property['MEDIA_DOCUMENT_' . $j]), 0, 2 ) == '//' || 
									substr( strtolower($property['MEDIA_DOCUMENT_' . $j]), 0, 4 ) == 'http'
								)
								{
									// This is a URL
									$url = $property['MEDIA_DOCUMENT_' . $j];
									$description = ( ( isset($property['MEDIA_DOCUMENT_' . $j]) && $property['MEDIA_DOCUMENT_' . $j] != '' ) ? $property['MEDIA_DOCUMENT_' . $j] : '' );
								    
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
									        'name' => $filename,
									        'tmp_name' => $tmp
									    );

									    // Check for download errors
									    if ( is_wp_error( $tmp ) ) 
									    {
									        @unlink( $file_array[ 'tmp_name' ] );

									        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['AGENT_REF'] );
									    }
									    else
									    {
										    $id = media_handle_sideload( $file_array, $post_id, $description );

										    // Check for handle sideload errors.
										    if ( is_wp_error( $id ) ) 
										    {
										        @unlink( $file_array['tmp_name'] );
										        
										        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['AGENT_REF'] );
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
								else
								{
									// Not a URL. Must've been physically uploaded or already exists
									$media_file_name = $property['MEDIA_DOCUMENT_' . $j];
									$media_folder = dirname( $this->target_file );
									$description = ( ( isset($property['MEDIA_DOCUMENT_TEXT_' . $j]) && $property['MEDIA_DOCUMENT_TEXT_' . $j] != '' ) ? $property['MEDIA_DOCUMENT_TEXT_' . $j] : '' );

									if ( file_exists( $media_folder . '/' . $media_file_name ) )
									{
										$upload = true;
		                                $replacing_attachment_id = '';
		                                if ( isset($previous_media_ids[$previous_media_i]) ) 
		                                {                                    
		                                    // get this attachment
		                                    $current_image_path = get_post_meta( $previous_media_ids[$previous_media_i], '_imported_path', TRUE );
		                                    $current_image_size = filesize( $current_image_path );
		                                    
		                                    if ($current_image_size > 0 && $current_image_size !== FALSE)
		                                    {
		                                        $replacing_attachment_id = $previous_media_ids[$previous_media_i];
		                                        
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
			                                    	$this->add_error( 'Failed to get filesize of new EPC file ' . $media_folder . '/' . $media_file_name, $property['AGENT_REF'] );
			                                    }
		                                        
		                                        unset($new_image_size);
		                                    }
		                                    else
		                                    {
		                                    	$this->add_error( 'Failed to get filesize of existing EPC file ' . $current_image_path, $property['AGENT_REF'] );
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
		                                        	$this->add_error( 'Failed inserting brochure attachment ' . $upload['file'] . ' - ' . print_r($attachment, TRUE), $property['AGENT_REF'] );
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
			                            	if ( isset($previous_media_ids[$previous_media_i]) ) 
		                                	{
		                                		$media_ids[] = $previous_media_ids[$previous_media_i];

		                                		if ( $description != '' )
												{
													$my_post = array(
												    	'ID'          	 => $previous_media_ids[$previous_media_i],
												    	'post_title'     => $description,
												    );

												 	// Update the post into the database
												    wp_update_post( $my_post );
												}

												++$existing;
		                                	}
			                            }

			                            $files_to_unlink[] = $media_folder . '/' . $media_file_name;
									}
									else
									{
										if ( isset($previous_media_ids[$previous_media_i]) ) 
				                    	{
				                    		$media_ids[] = $previous_media_ids[$previous_media_i];

				                    		if ( $description != '' )
											{
												$my_post = array(
											    	'ID'          	 => $previous_media_ids[$previous_media_i],
											    	'post_title'     => $description,
											    );

											 	// Update the post into the database
											    wp_update_post( $my_post );
											}

											++$existing;
				                    	}
									}
								}

								++$previous_media_i;
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

						$this->add_log( 'Imported ' . count($media_ids) . ' EPCs (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property['AGENT_REF'] );
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

						if ( isset($property['MEDIA_VIRTUAL_TOUR_' . $j]) && trim($property['MEDIA_VIRTUAL_TOUR_' . $j]) != '' )
						{
							if ( 
								substr( strtolower($property['MEDIA_VIRTUAL_TOUR_' . $j]), 0, 2 ) == '//' || 
								substr( strtolower($property['MEDIA_VIRTUAL_TOUR_' . $j]), 0, 4 ) == 'http'
							)
							{
								$urls[] = trim($property['MEDIA_VIRTUAL_TOUR_' . $j]);
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

				        $this->add_log( 'Imported ' . count($urls) . ' virtual tours', $property['AGENT_REF'] );
					}
				}
				else
				{
					$this->add_log( 'Skipping property as not been updated', $property['AGENT_REF'] );
				}

				update_post_meta( $post_id, '_blm_update_date_' . $import_id, date("Y-m-d H:i:s", strtotime($property['UPDATE_DATE'])) );

				// Fire actions
				// The realtime feed for example, might need executing

				do_action( "propertyhive_property_imported_blm", $post_id, $property );

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
							$this->add_log( 'New meta data for ' . trim($key, '_') . ': ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), $property['AGENT_REF'] );
						}
						elseif ( $metadata_before[$key] != $metadata_after[$key] )
						{
							$this->add_log( 'Updated ' . trim($key, '_') . '. Before: ' . ( ( is_array($metadata_before[$key]) ) ? implode(", ", $metadata_before[$key]) : $metadata_before[$key] ) . ', After: ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), $property['AGENT_REF'] );
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
							$this->add_log( 'New taxonomy data for ' . $taxonomy_name . ': ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), $property['AGENT_REF'] );
						}
						elseif ( $taxonomy_terms_before[$taxonomy_name] != $taxonomy_terms_after[$taxonomy_name] )
						{
							$this->add_log( 'Updated ' . $taxonomy_name . '. Before: ' . ( ( is_array($taxonomy_terms_before[$taxonomy_name]) ) ? implode(", ", $taxonomy_terms_before[$taxonomy_name]) : $taxonomy_terms_before[$taxonomy_name] ) . ', After: ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), $property['AGENT_REF'] );
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
			
		} // end foreach

		do_action( "propertyhive_post_import_properties_blm" );

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
				$import_refs[] = $property['AGENT_REF'];
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

					do_action( "propertyhive_property_removed_blm", $post->ID );
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
				$mapping_values = $this->get_blm_mapping_values('sales_availability');
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
				$mapping_values = $this->get_blm_mapping_values('lettings_availability');
				if ( is_array($mapping_values) && !empty($mapping_values) )
				{
					foreach ($mapping_values as $mapping_value => $text_value)
					{
						$this->mappings['lettings_availability'][$mapping_value] = '';
					}
				}
			}

			if ( get_option( 'propertyhive_active_departments_commercial' ) == 'yes' )
			{
				$mapping_values = $this->get_blm_mapping_values('commercial_availability');
				if ( is_array($mapping_values) && !empty($mapping_values) )
				{
					foreach ($mapping_values as $mapping_value => $text_value)
					{
						$this->mappings['commercial_availability'][$mapping_value] = '';
					}
				}
			}
		}
		else
		{
			$mapping_values = $this->get_blm_mapping_values('availability');
			if ( is_array($mapping_values) && !empty($mapping_values) )
			{
				foreach ($mapping_values as $mapping_value => $text_value)
				{
					$this->mappings['availability'][$mapping_value] = '';
				}
			}
		}

		$mapping_values = $this->get_blm_mapping_values('property_type');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['property_type'][$mapping_value] = '';
			}
		}

		if ( get_option( 'propertyhive_active_departments_commercial', '' ) == 'yes' )
		{
			$mapping_values = $this->get_blm_mapping_values('commercial_property_type');
			if ( is_array($mapping_values) && !empty($mapping_values) )
			{
				foreach ($mapping_values as $mapping_value => $text_value)
				{
					$this->mappings['commercial_property_type'][$mapping_value] = '';
				}
			}
		}

		$mapping_values = $this->get_blm_mapping_values('price_qualifier');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['price_qualifier'][$mapping_value] = '';
			}
		}

		$mapping_values = $this->get_blm_mapping_values('tenure');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['tenure'][$mapping_value] = '';
			}
		}

		if ( get_option( 'propertyhive_active_departments_commercial', '' ) == 'yes' )
		{
			$mapping_values = $this->get_blm_mapping_values('commercial_tenure');
			if ( is_array($mapping_values) && !empty($mapping_values) )
			{
				foreach ($mapping_values as $mapping_value => $text_value)
				{
					$this->mappings['commercial_tenure'][$mapping_value] = '';
				}
			}
		}

		$mapping_values = $this->get_blm_mapping_values('furnished');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['furnished'][$mapping_value] = '';
			}
		}

		$mapping_values = $this->get_blm_mapping_values('office');
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
		return $this->get_blm_mapping_values($custom_field);
	}

	public function get_blm_mapping_values($custom_field) 
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
        if ($custom_field == 'tenure' || $custom_field == 'commercial_tenure')
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

    public function archive( $import_id )
    {
    	// Rename to append the date and '.processed' as to not get picked up again. Will be cleaned up every 7 days
    	$new_target_file = $this->target_file . '-' . gmdate("YmdHis") .'.processed';
		rename( $this->target_file, $new_target_file );
		
		$this->add_log( "Archived BLM. Available for download for 7 days: " . str_replace("/includes", "", plugin_dir_url( __FILE__ )) . "/download.php?import_id=" . $import_id . "&file=" . base64_encode(basename($new_target_file)));
    }

}

}