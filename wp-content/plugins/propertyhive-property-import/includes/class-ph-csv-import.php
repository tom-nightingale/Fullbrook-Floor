<?php
/**
 * Class for managing the import process of a CSV file
 *
 * @package WordPress
 */
if ( class_exists( 'PH_Property_Import_Process' ) ) {

class PH_CSV_Import extends PH_Property_Import_Process {

	/**
	 * @var string
	 */
	private $target_file;

	/**
	 * @var array
	 */
	private $branch_ids_processed;

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

		$row = 1;
		if ( ($handle = fopen($this->target_file, "r")) !== FALSE ) 
		{
			$column_mappings = get_option( 'propertyhive_property_import_csv_column_mapping', array() );
	        if ( !is_array($column_mappings) )
	        {
	            $column_mappings = array();
	        }

	        $reverse_column_mappings = array();
	        foreach ( $column_mappings as $ph_field => $csv_i )
	        {
	        	if ( $csv_i != '' )
	        	{
	        		$reverse_column_mappings[$csv_i] = $ph_field;
	        	}
	        }

		    while ( ($data = fgetcsv($handle, 10000, ",")) !== FALSE ) 
		    {
		    	if ( $row > 1 )
		    	{
		    		$property = array();
			        $num = count($data);
			        for ( $c = 0; $c < $num; ++$c ) 
			        {
			        	if ( isset($reverse_column_mappings[$c]) )
			        	{
				            $property[ $reverse_column_mappings[$c] ] = $data[$c];
				        }
			        }

	                $this->properties[] = $property;
			    }

		        ++$row;

		    }
		    fclose($handle);
		}
		else
		{
		    $this->add_error( 'Failed to read CSV file' );
		}
	}

	public function get_csv_column_headers()
	{
		$columns = array();

		$row = 1;
		if ( ($handle = fopen($this->target_file, "r")) !== FALSE ) 
		{
		    while ( ($data = fgetcsv($handle, 10000, ",")) !== FALSE ) 
		    {
		    	if ( $row > 1 )
		    	{
		    		break;
		    	}

		        $num = count($data);
		        for ( $c = 0; $c < $num; ++$c )
		        {
		            $columns[] = $data[$c];
		        }

		        ++$row;
		    }
		    fclose($handle);
		}

		return $columns;
	}

	public function pre_test()
	{
		$passed_properties = 0;
		$failed_properties = 0;

		$ph_fields = $this->get_propertyhive_fields();

		foreach ($this->properties as $i => $property)
		{
			$passed = true;
			if ( !isset($property['imported_ref']) && !isset($property['post_id']) )
			{
				$this->add_error( 'The Unique ID or Post ID field is missing for property on row ' . $i . '. At least one of them must be present.' );
				$passed = false;

				$property['imported_ref'] = ''; // Set here as it's used for errors further down
			}
			elseif ( 
				( isset($property['imported_ref']) && trim($property['imported_ref']) == '' && isset($property['post_id']) && trim($property['post_id']) == '' ) ||
				( !isset($property['imported_ref']) && isset($property['post_id']) && trim($property['post_id']) == '' )||
				( !isset($property['post_id']) && isset($property['imported_ref']) && trim($property['imported_ref']) == '' )
			)
			{
				$this->add_error( 'The Unique ID and Post ID fields are blank for property on row ' . $i . '. At least one of them must be present.' );
				$passed = false;
			}

			if ( 
				( !isset($property['imported_ref']) || ( isset($property['imported_ref']) && trim($property['imported_ref']) == '' ) ) &&
				isset($property['post_id']) && trim($property['post_id']) != ''
			)
			{
				$property['imported_ref'] = trim($property['post_id']);
			}

			foreach ( $ph_fields as $ph_field_id => $ph_field )
			{
				if ( isset($ph_field['possible_values']) && !empty($ph_field['possible_values']) )
				{
					if ( isset($property[$ph_field_id]) && $property[$ph_field_id] != '' )
					{
						if ( isset($ph_field['supports_multiple']) && $ph_field['supports_multiple'] === TRUE )
						{
							$provided_values = explode(",", trim($property[$ph_field_id], ','));
						}
						else
						{
							$provided_values = array($property[$ph_field_id]);
						}
						foreach ( $provided_values as $provided_value )
						{
							if ( !in_array(htmlentities(trim($provided_value)), $ph_field['possible_values']) )
							{
								$this->add_error( 'The ' . __( $ph_field['label'], 'propertyhive' ) . ' field needs to be one of the following: ' . implode(", ", $ph_field['possible_values']) . '. Provided value was: ' . htmlentities($provided_value), $property['imported_ref'] );
								$passed = false;
							}
						}
					}
				}
				if ( $ph_field_id == 'post_id' )
				{
					// Check property with this post ID exists
					if ( isset($property['post_id']) && trim($property['post_id']) != '' )
					{
						if ( get_post_type((int)$property['post_id']) != 'property' )
						{
							$this->add_error( 'No existing property found with post ID ' . trim($property['post_id']) . ' on row ' . $i, $property['imported_ref'] );
							$passed = false;
						}
					}
				}
			}

			if ( $passed && class_exists('PH_Property_Portal') )
        	{
        		if ( 
        			trim($property['agent_id']) != '' &&
        			trim($property['branch_id']) != '' 
        		)
				{
					// Get agent ID from name provided
					if ( ($agent_id = array_search($property['agent_id'], $ph_fields['agent_id']['possible_values'])) !== false ) 
					{
						$found_branch = false;

						// Check branch belongs to agent
						$args = array(
							'post_type' => 'branch',
							'nopaging' => true,
							'meta_query' => array(
				                array(
				                    'key' => '_agent_id',
				                    'value' => trim($agent_id)
				                )
				            )
						);

						$branch_query = new WP_Query( $args );

						if ( $branch_query->have_posts() )
						{
							while ( $branch_query->have_posts() )
							{
								$branch_query->the_post();

								if ( get_the_title() == $property['branch_id'] )
								{
									$found_branch = true;
								}
							}
						}
						wp_reset_postdata();
						
						if ( !$found_branch )
						{
							$this->add_error( 'Branch with name ' . $property['branch_id'] . ' not found for the agent provided: ' . $property['agent_id'], $property['imported_ref'] );
							$passed = false;
						}
					}
					else
					{
						$this->add_error( 'Agent with name ' . $property['agent_id'] . ' not found', $property['imported_ref'] );
						$passed = false;
					}
				}
        	}

			if ( $passed )
			{
				++$passed_properties;
			}
			else
			{
				++$failed_properties;
			}
		}

		return array( $passed_properties, $failed_properties );
	}

	private function get_property_field($property, $key, $get_value = false)
	{
		if (isset($property[$key]))
		{
			if ($get_value)
			{
				$ph_fields = $this->get_propertyhive_fields();
				$field = $ph_fields[$key];

				if (isset($field['possible_values']))
				{
					if ( ($search_key = array_search(htmlentities(trim($property[$key])), $field['possible_values'])) !== false ) 
					{
						return $search_key;
					}
				}
			}
			return trim($property[$key]);
		}

		return '';
	}

	private function conditional_meta_update( $post_id, $property, $csv_name, $meta_name, $inserted_updated, $force_value = '', $get_value = false )
	{
		if ( 
			$inserted_updated == 'inserted' || 
			( $inserted_updated == 'updated' && isset($property[$csv_name]) ) 
		) 
		{ 
			update_post_meta( $post_id, $meta_name, ( $force_value != '' ? $force_value : $this->get_property_field($property, $csv_name, $get_value) ) ); 
		}
	}

	private function conditional_taxonomy_update( $post_id, $property, $csv_name, $taxonomy, $inserted_updated )
	{
		if ( 
			$inserted_updated == 'inserted' || 
			( $inserted_updated == 'updated' && isset($property[$csv_name]) ) 
		) 
		{
			wp_delete_object_term_relationships( $post_id, $taxonomy );
			if ( $this->get_property_field($property, $taxonomy) != '' )
			{
				$term = get_term_by('name', $this->get_property_field($property, $taxonomy), $taxonomy);
				if ($term !== FALSE)
				{
	                wp_set_post_terms( $post_id, $term->term_id, $taxonomy );
	            }
            }
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

		// Get primary office in the event office isn't set
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

        do_action( "propertyhive_pre_import_properties_csv", $this->properties );
        $this->properties = apply_filters( "propertyhive_csv_properties_due_import", $this->properties );

		$this->add_log( 'Beginning to loop through ' . count($this->properties) . ' properties' );

		$property_row = 1;
		foreach ( $this->properties as $property )
		{
			$using_post_id = false;
			if ( 
				( !isset($property['imported_ref']) || ( isset($property['imported_ref']) && trim($property['imported_ref']) == '' ) ) &&
				isset($property['post_id']) && trim($property['post_id']) != ''
			)
			{
				$property['imported_ref'] = trim($property['post_id']);
				$using_post_id = true;
			}

			$this->add_log( 'Importing property ' . $property_row, $property['imported_ref'] );

			$inserted_updated = false;

			$args = array(
	            'post_type' => 'property',
	            'posts_per_page' => 1,
	        );
	        if ( !$using_post_id )
	        {
	        	$args['meta_query'] = array(
	            	array(
		            	'key' => $imported_ref_key,
		            	'value' => $property['imported_ref']
		            )
	            );
	        }
	        else
	        {
	        	$args['p'] = trim((int)$property['post_id']);
	        }
	        $property_query = new WP_Query($args);
	        
	        if ($property_query->have_posts())
	        {
	        	if ( !$using_post_id )
	        	{
	        		$this->add_log( 'This property has been imported before with ' . $imported_ref_key . ' of ' . $property['imported_ref'] . '. Updating it', $property['imported_ref'] );
	        	}
	        	else
	        	{
	        		$this->add_log( 'Updating property with post ID ' . trim((int)$property['post_id']), $property['imported_ref'] );	
	        	}

	        	// We've imported this property before
	            while ($property_query->have_posts())
	            {
	                $property_query->the_post();

	                $post_id = get_the_ID();

	                $my_post = array(
				    	'ID'          	 => $post_id,
				    	'post_content' 	 => '',
				    	'post_status'    => 'publish',
				  	);

	                if ( isset($property['post_title']) )
				  	{
				  		$my_post['post_title'] = utf8_encode($this->get_property_field($property, 'post_title'));
				  	}
				  	if ( isset($property['post_excerpt']) )
				  	{
				  		$my_post['post_excerpt'] = utf8_encode($this->get_property_field($property, 'post_excerpt'));
				  	}

				 	// Update the post into the database
				    $post_id = wp_update_post( $my_post );

				    if ( is_wp_error( $post_id ) ) 
					{
						$this->add_error( 'Failed to update post. The error was as follows: ' . $post_id->get_error_message(), $property['imported_ref'] );
					}
					elseif ( $post_id == 0 )
					{
						$this->add_error( 'Failed to update post. The error was as follows: post ID is zero. Possible encoding issue', $property['imported_ref'] );
					}
					else
					{
						$inserted_updated = 'updated';
					}
	            }
	        }
	        else
	        {
	        	$this->add_log( 'This property hasn\'t been imported before. Inserting it', $property['imported_ref'] );

	        	// We've not imported this property before
				$postdata = array(
					'post_title'     => utf8_encode(wp_strip_all_tags( $this->get_property_field($property, 'post_title') )),
				    'post_excerpt'   => utf8_encode($this->get_property_field($property, 'post_excerpt')),
				    'post_status'    => 'publish',
				    'post_content' 	 => '',
					'post_type'      => 'property',
					'comment_status' => 'closed',
				);

				$post_id = wp_insert_post( $postdata, true );

				if ( is_wp_error( $post_id ) ) 
				{
					$this->add_error( 'Failed to insert post. The error was as follows: ' . $post_id->get_error_message(), $property['imported_ref'] );
				}
				elseif ( $post_id == 0 )
				{
					$this->add_error( 'Failed to update post. The error was as follows: post ID is zero. Possible encoding issue', $property['imported_ref'] );
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
					($this->get_property_field($property, 'post_title') != '' || $this->get_property_field($property, 'post_excerpt') != '')
				)
				{
					$my_post = array(
				    	'ID'          	 => $post_id,
				    	'post_content' 	 => '',
				    	'post_status'    => 'publish',
				  	);

				  	if ( isset($property['post_title']) )
				  	{
				  		$my_post['post_title'] = htmlentities(mb_convert_encoding(wp_strip_all_tags( $this->get_property_field($property, 'post_title') ), 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8");
				  		$my_post['post_name'] = sanitize_title($this->get_property_field($property, 'post_title'));
				  	}
				  	if ( isset($property['post_excerpt']) )
				  	{
				  		$my_post['post_excerpt'] = htmlentities(mb_convert_encoding($this->get_property_field($property, 'post_excerpt'), 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8");
				  	}

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

				$this->add_log( 'Successfully ' . $inserted_updated . ' post. The post ID is ' . $post_id, $property['imported_ref'] );

				if ( !$using_post_id )
	        	{
					update_post_meta( $post_id, $imported_ref_key, $property['imported_ref'] );
				}

				// Address
				$this->conditional_meta_update( $post_id, $property, 'reference_number', '_reference_number', $inserted_updated );
				$this->conditional_meta_update( $post_id, $property, 'address_name_number', '_address_name_number', $inserted_updated );
				$this->conditional_meta_update( $post_id, $property, 'address_street', '_address_street', $inserted_updated );
				$this->conditional_meta_update( $post_id, $property, 'address_2', '_address_two', $inserted_updated );
				$this->conditional_meta_update( $post_id, $property, 'address_3', '_address_three', $inserted_updated );
				$this->conditional_meta_update( $post_id, $property, 'address_4', '_address_four', $inserted_updated );
				$this->conditional_meta_update( $post_id, $property, 'address_postcode', '_address_postcode', $inserted_updated );

				$country = $this->get_property_field($property, 'address_country');
				if ($country == '')
				{
					$country = get_option( 'propertyhive_default_country', 'GB' );
				}
				$this->conditional_meta_update( $post_id, $property, 'address_country', '_address_country', $inserted_updated, $country );

				// Location
				$this->conditional_taxonomy_update( $post_id, $property, 'location', 'location', $inserted_updated );

				$this->conditional_meta_update( $post_id, $property, 'latitude', '_latitude', $inserted_updated );
				$this->conditional_meta_update( $post_id, $property, 'longitude', 'longitude', $inserted_updated );

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
							if ( get_post_meta($post_id, '_address_name_number', TRUE) != '' ) { $address_to_geocode[] = get_post_meta($post_id, '_address_name_number', TRUE); }
							if ( get_post_meta($post_id, '_address_street', TRUE) != '' ) { $address_to_geocode[] = get_post_meta($post_id, '_address_street', TRUE); }
							if ( get_post_meta($post_id, '_address_two', TRUE) != '' ) { $address_to_geocode[] = get_post_meta($post_id, '_address_two', TRUE); }
							if ( get_post_meta($post_id, '_address_three', TRUE) != '' ) { $address_to_geocode[] = get_post_meta($post_id, '_address_three', TRUE); }
							if ( get_post_meta($post_id, '_address_four', TRUE) != '' ) { $address_to_geocode[] = get_post_meta($post_id, '_address_four', TRUE); }
							if ( get_post_meta($post_id, '_address_postcode', TRUE) ) { $address_to_geocode[] = get_post_meta($post_id, '_address_postcode', TRUE); }

							if ( !empty($address_to_geocode) )
							{
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
							        	$this->add_error( 'Google Geocoding service returned status ' . $status, $property['imported_ref'] );
							        	sleep(3);
							        }
						        }
						        else
						        {
						        	$this->add_error( 'Failed to parse XML response from Google Geocoding service.', $property['imported_ref'] );
						        }
					        }
						}
						else
				        {
				        	$this->add_error( 'Failed to obtain co-ordinates as allow_url_fopen setting is disabled', $property['imported_ref'] );
				        }
			        }
				    else
				    {
				    	$this->add_log( 'Not performing Google Geocoding request as no API key present in settings', $property['imported_ref'] );
				    }
				}

				// Owner
				add_post_meta( $post_id, '_owner_contact_id', '', true );

				// Record Details
				if ( 
					$inserted_updated == 'inserted' ||
					( $inserted_updated == 'updated' && isset($property['negotiator']) )
				)
				{
					$negotiator_id = false;
					if ( $this->get_property_field($property, 'negotiator') != '' )
					{
						$negotiator = $this->get_property_field($property, 'negotiator', TRUE);

						if ( is_numeric($negotiator) )
						{
							// ID passed. Check this is a valid user ID. Should be done at validation stage also
							if ( get_user_by('id', $negotiator) !== FALSE )
							{
								$negotiator_id = $negotiator;
							}
						}
						else
						{
							// Must've been passed a display name
							$user = $wpdb->get_row( $wpdb->prepare(
						        "SELECT `ID` FROM $wpdb->users WHERE `display_name` = %s", $negotiator
						    ) );

						    if ( $user !== FALSE )
						    {
						    	$negotiator_id = $user->ID;
						    }
						}
					}
					update_post_meta( $post_id, '_negotiator_id', ( $negotiator_id !== FALSE ? $negotiator_id : get_current_user_id() ) );

					if ( class_exists('PH_Frontend_Property_Submissions') && $negotiator_id !== FALSE )
					{
						update_post_meta( $post_id, '_frontend_submission_user_id', $negotiator_id );
					}
				}

				$office_id = $primary_office_id;
				if ( $this->get_property_field($property, 'office') != '' )
				{
					$office_id = $this->get_property_field($property, 'office', TRUE);
				}
				$this->conditional_meta_update( $post_id, $property, 'office', '_office_id', $inserted_updated, $office_id, TRUE );

				$department = get_option( 'propertyhive_primary_department', 'residential-sales' );
				if ( $this->get_property_field($property, 'department') != '' )
				{
					$department = $this->get_property_field($property, 'department', true);
				}
				$this->conditional_meta_update( $post_id, $property, 'department', '_department', $inserted_updated, $department, TRUE );
			
				// Residential Details
				$department = get_post_meta( $post_id, '_department', TRUE );
				if ( $department == 'residential-sales' || $department == 'residential-lettings' )
				{
					$this->conditional_meta_update( $post_id, $property, 'bedrooms', '_bedrooms', $inserted_updated );
					$this->conditional_meta_update( $post_id, $property, 'bathrooms', '_bathrooms', $inserted_updated );
					$this->conditional_meta_update( $post_id, $property, 'reception_rooms', '_reception_rooms', $inserted_updated );

					// Property Type
					$this->conditional_taxonomy_update( $post_id, $property, 'property_type', 'property_type', $inserted_updated );

		            // Parking
					$this->conditional_taxonomy_update( $post_id, $property, 'parking', 'parking', $inserted_updated );
		        }

				$currency_to_insert = '';
				$PH_Countries = new PH_Countries();

				$currency_passed = $this->get_property_field($property, 'currency');

				if ( $currency_passed == '' || $PH_Countries->get_currency( $currency_passed ) === FALSE )
				{
					$default_country_code = get_option('propertyhive_default_country', 'GB');
				
					$default_country = $PH_Countries->get_country( $default_country_code );
					$currency_to_insert = $default_country['currency_code'];
				}
				else
				{
					$currency_to_insert = $currency_passed;
				}

				// Residential Sales Details
				if ( $department == 'residential-sales' )
				{
					$price = preg_replace("/[^0-9.]/", '', $this->get_property_field($property, 'price'));

					$this->conditional_meta_update( $post_id, $property, 'price', '_price', $inserted_updated, $price );
					$this->conditional_meta_update( $post_id, $property, 'price', '_price_actual', $inserted_updated, $price );
					update_post_meta( $post_id, '_poa', '' );

					update_post_meta( $post_id, '_currency', $currency_to_insert );
					
					// Price Qualifier
					$this->conditional_taxonomy_update( $post_id, $property, 'price_qualifier', 'price_qualifier', $inserted_updated );

		            // Tenure
		            $this->conditional_taxonomy_update( $post_id, $property, 'tenure', 'tenure', $inserted_updated );

		            // Sale By
		            $this->conditional_taxonomy_update( $post_id, $property, 'sale_by', 'sale_by', $inserted_updated );
				}

				// Residential Lettings Details
				if ( $department == 'residential-lettings' )
				{
					$rent = preg_replace("/[^0-9.]/", '', $this->get_property_field($property, 'rent'));

					$this->conditional_meta_update( $post_id, $property, 'rent', '_rent', $inserted_updated, $rent );
					$this->conditional_meta_update( $post_id, $property, 'rent_frequency', '_rent_frequency', $inserted_updated, strtolower($this->get_property_field($property, 'rent_frequency')) );

					$rent_frequency = 'pcm';
					$price_actual = get_post_meta( $post_id, '_rent', TRUE );
					switch ( get_post_meta( $post_id, '_rent_frequency', TRUE ) )
					{
						case "pw": { $rent_frequency = 'pw'; $price_actual = ($price_actual * 52) / 12; break; }
						case "pcm": { $rent_frequency = 'pcm'; $price_actual = $price_actual; break; }
						case "pq": { $rent_frequency = 'pq'; $price_actual = ($price_actual * 4) / 12; break; }
						case "pa": { $rent_frequency = 'pa'; $price_actual = $price_actual / 12; break; }
						case "pppw": 
						{
							$rent_frequency = 'pppw';
							$bedrooms = get_post_meta( $post_id, '_bedrooms', TRUE );
							if ( $bedrooms != '' && $bedrooms != 0 )
							{
								$price_actual = (($price_actual * 52) / 12) * $bedrooms;
							}
							else
							{
								$price_actual = ($price_actual * 52) / 12;
							}
							break; 
						}
					}
					update_post_meta( $post_id, '_rent_frequency', $rent_frequency );
					update_post_meta( $post_id, '_price_actual', $price_actual );
					
					update_post_meta( $post_id, '_poa', '' );

					update_post_meta( $post_id, '_currency', $currency_to_insert );

					$deposit = preg_replace( "/[^0-9.]/", '', $this->get_property_field($property, 'deposit') );
					$this->conditional_meta_update( $post_id, $property, 'deposit', '_deposit', $inserted_updated, $deposit );
            		$this->conditional_meta_update( $post_id, $property, 'available_date', '_available_date', $inserted_updated );

            		// Furnished
            		$this->conditional_taxonomy_update( $post_id, $property, 'furnished', 'furnished', $inserted_updated );
				}

				// Commercial Details
				if ( $department == 'commercial' )
				{
					update_post_meta( $post_id, '_for_sale', $this->get_property_field($property, 'for_sale') );
            		update_post_meta( $post_id, '_to_rent', $this->get_property_field($property, 'to_rent') );

            		if ( $this->get_property_field($property, 'for_sale') == 'yes' )
            		{
            			update_post_meta( $post_id, '_commercial_price_currency', $currency_to_insert );

            			$price = preg_replace("/[^0-9.]/", '', $this->get_property_field($property, 'price_from'));
	                    if ( $price == '' )
	                    {
	                        $price = preg_replace("/[^0-9.]/", '', $this->get_property_field($property, 'price_to'));
	                    }
	                    update_post_meta( $post_id, '_price_from', $price );

	                    $price = preg_replace("/[^0-9.]/", '', $this->get_property_field($property, 'price_to'));
	                    if ( $price == '' )
	                    {
	                        $price = preg_replace("/[^0-9.]/", '', $this->get_property_field($property, 'price_from'));
	                    }
	                    update_post_meta( $post_id, '_price_to', $price );

	                    update_post_meta( $post_id, '_price_units', $this->get_property_field($property, 'price_units', TRUE) );

	                    update_post_meta( $post_id, '_price_poa', '' );

	                    if ( $this->get_property_field($property, 'commercial_sale_by') != '' )
	                    {
	                        wp_set_post_terms( $post_id, $this->get_property_field($property, 'commercial_sale_by'), 'sale_by' );
	                    }
	                    else
	                    {
	                        // Setting to blank
	                        wp_delete_object_term_relationships( $post_id, 'sale_by' );
	                    }
	                    
	                    if ( $this->get_property_field($property, 'commercial_tenure') != '' )
	                    {
	                        wp_set_post_terms( $post_id, $this->get_property_field($property, 'commercial_tenure'), 'commercial_tenure' );
	                    }
	                    else
	                    {
	                        // Setting to blank
	                        wp_delete_object_term_relationships( $post_id, 'commercial_tenure' );
	                    }
            		}

            		if ( $this->get_property_field($property, 'to_rent') == 'yes' )
            		{
            			update_post_meta( $post_id, '_commercial_rent_currency', $currency_to_insert );

            			$rent = preg_replace("/[^0-9.]/", '', $this->get_property_field($property, 'rent_from'));
	                    if ( $rent == '' )
	                    {
	                        $rent = preg_replace("/[^0-9.]/", '', $this->get_property_field($property, 'rent_to'));
	                    }
	                    update_post_meta( $post_id, '_rent_from', $rent );

	                    $rent = preg_replace("/[^0-9.]/", '', $this->get_property_field($property, 'rent_to'));
	                    if ( $rent == '' )
	                    {
	                        $rent = preg_replace("/[^0-9.]/", '', $this->get_property_field($property, 'rent_from'));
	                    }
	                    update_post_meta( $post_id, '_rent_to', $rent );

	                    update_post_meta( $post_id, '_rent_units', $this->get_property_field($property, 'rent_units', TRUE) );

	                    update_post_meta( $post_id, '_rent_poa', '' );
            		}

            		$PH_Countries->update_property_price_actual( $post_id );

		            $taxonomy = 'commercial_property_type';
					wp_delete_object_term_relationships( $post_id, $taxonomy );
					if ( trim($this->get_property_field($property, $taxonomy), ',') != '' )
					{
						$provided_types = explode(",", trim($this->get_property_field($property, $taxonomy), ','));
						$type_ids = array();

						foreach ( $provided_types as $provided_type )
						{
							$term = get_term_by('name', trim($provided_type), $taxonomy);
							if ($term !== FALSE)
							{
				                $type_ids[] = $term->term_id;
				            }
						}
			            wp_set_post_terms( $post_id, $type_ids, $taxonomy );
		            }

		            $size = preg_replace("/[^0-9.]/", '', $this->get_property_field($property, 'floor_area_from'));
		            if ( $size == '' )
		            {
		                $size = preg_replace("/[^0-9.]/", '', $this->get_property_field($property, 'floor_area_to'));
		            }
		            update_post_meta( $post_id, '_floor_area_from', $size );

		            update_post_meta( $post_id, '_floor_area_from_sqft', convert_size_to_sqft( $size, ( ( $this->get_property_field($property, 'floor_area_units', TRUE) != '' ) ? $this->get_property_field($property, 'floor_area_units', TRUE) : 'sqft' ) ) );

		            $size = preg_replace("/[^0-9.]/", '', $this->get_property_field($property, 'floor_area_to'));
		            if ( $size == '' )
		            {
		                $size = preg_replace("/[^0-9.]/", '', $this->get_property_field($property, 'floor_area_from'));
		            }
		            update_post_meta( $post_id, '_floor_area_to', $size );

		            update_post_meta( $post_id, '_floor_area_to_sqft', convert_size_to_sqft( $size, ( ( $this->get_property_field($property, 'floor_area_units', TRUE) != '' ) ? $this->get_property_field($property, 'floor_area_units', TRUE) : 'sqft' ) ) );

		            update_post_meta( $post_id, '_floor_area_units', $this->get_property_field($property, 'floor_area_units', TRUE) );

		            update_post_meta( $post_id, '_site_area_from', '' );

		            update_post_meta( $post_id, '_site_area_from_sqft', '' );

		            update_post_meta( $post_id, '_site_area_to', '' );

		            update_post_meta( $post_id, '_site_area_to_sqft', '' );

		            update_post_meta( $post_id, '_site_area_units', '' );
				}

				// Marketing
				$this->conditional_meta_update( $post_id, $property, 'on_market', '_on_market', $inserted_updated );
				$this->conditional_meta_update( $post_id, $property, 'featured', '_featured', $inserted_updated );
				
				// Availability
				$this->conditional_taxonomy_update( $post_id, $property, 'availability', 'availability', $inserted_updated );

	            // Marketing Flag
	            $this->conditional_taxonomy_update( $post_id, $property, 'marketing_flag', 'marketing_flag', $inserted_updated );

	            // BLM Feeds
	            if (class_exists('PH_Blmexport'))
        		{
        			$portals = array();
			        $current_blmexport_options = get_option( 'propertyhive_blmexport' );
			            
			        if ($current_blmexport_options !== FALSE)
			        {
			            if (isset($current_blmexport_options['portals']))
			            {
			                $portals = $current_blmexport_options['portals'];
			            }
			        }

			        if (!empty($portals))
			        {
			            foreach ($portals as $portal_id => $portal)
			            {
			            	if ($portal['mode'] == 'test' || $portal['mode'] == 'live')
			    			{
			    				$this->conditional_meta_update( $post_id, $property, '_portal_' . $portal_id, '_portal_' . $portal_id, $inserted_updated );
			    			}
			    		}
			    	}
        		}

	            // Real-time Feeds
	            if (class_exists('PH_Realtimefeed'))
		        {
					$portals = array();
			        $current_realtime_feed_options = get_option( 'propertyhive_realtimefeed' );
			            
			        if ($current_realtime_feed_options !== FALSE)
			        {
			            if (isset($current_realtime_feed_options['portals']))
			            {
			                $portals = $current_realtime_feed_options['portals'];
			            }
			        }

			        if (!empty($portals))
			        {
			            foreach ($portals as $portal_id => $portal)
			            {
			            	if ($portal['mode'] == 'test' || $portal['mode'] == 'live')
			    			{
			    				$this->conditional_meta_update( $post_id, $property, '_realtime_portal_' . $portal_id, '_realtime_portal_' . $portal_id, $inserted_updated );
			    			}
			    		}
			    	}
        		}

        		if (class_exists('PH_Zooplarealtimefeed'))
		        {
					$portals = array();
			        $current_realtime_feed_options = get_option( 'propertyhive_zooplarealtimefeed' );
			            
			        if ($current_realtime_feed_options !== FALSE)
			        {
			            if (isset($current_realtime_feed_options['portals']))
			            {
			                $portals = $current_realtime_feed_options['portals'];
			            }
			        }

			        if (!empty($portals))
			        {
			            foreach ($portals as $portal_id => $portal)
			            {
			            	if ($portal['mode'] == 'test' || $portal['mode'] == 'live')
			    			{
			    				$this->conditional_meta_update( $post_id, $property, '_zoopla_realtime_portal_' . $portal_id, '_zoopla_realtime_portal_' . $portal_id, $inserted_updated );
			    			}
			    		}
			    	}
        		}

				// Features
				if ( 
					$inserted_updated == 'inserted' ||
					( $inserted_updated == 'updated' && isset($property['features']) )
				)
				{
					if ( get_option('propertyhive_features_type') == 'checkbox' )
        			{
        				$features = array();
        				if ( $this->get_property_field($property, 'features') != '' )
						{
							$explode_features = explode(",", $this->get_property_field($property, 'features'));
							foreach ( $explode_features as $feature )
							{
								if ( trim($feature) != '' )
								{
									$term = get_term_by('name', trim($feature), 'property_feature');
									if ( $term !== FALSE )
									{
										$features[] = $term->term_id;
									}
								}
							}
						}
						if ( !empty($features) )
			            {
			                wp_set_post_terms( $post_id, $features, 'property_feature' );
			            }
			            else
			            {
			                wp_delete_object_term_relationships( $post_id, 'property_feature' );
			            }
        			}
        			else
        			{
						$features = array();
						if ( $this->get_property_field($property, 'features') != '' )
						{
							$explode_features = explode(",", $this->get_property_field($property, 'features'));
							foreach ( $explode_features as $feature )
							{
								if ( trim($feature) != '' )
								{
									$features[] = trim($feature);
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
			    }

		        // Rooms / Description
		        if ( 
					$inserted_updated == 'inserted' ||
					( $inserted_updated == 'updated' && isset($property['full_description']) )
				)
				{
			        if ( $department == 'commercial' )
					{
						// For now put the whole description in one room
						update_post_meta( $post_id, '_descriptions', '1' );
						update_post_meta( $post_id, '_description_name_0', '' );

						// Attempt to solve an encoding issue. Set to blank first, insert, and if blank, utf8encode and insert again
						update_post_meta( $post_id, '_description_0', '' );
			            update_post_meta( $post_id, '_description_0', $this->get_property_field($property, 'full_description') );
			            if ( get_post_meta( $post_id, '_description_0', TRUE ) == '' )
			            {
				            update_post_meta( $post_id, '_description_0', utf8_encode($this->get_property_field($property, 'full_description')) );
				        }
					}
					else
					{
						// For now put the whole description in one room
						update_post_meta( $post_id, '_rooms', '1' );
						update_post_meta( $post_id, '_room_name_0', '' );
						update_post_meta( $post_id, '_room_dimensions_0', '' );

						// Attempt to solve an encoding issue. Set to blank first, insert, and if blank, utf8encode and insert again
						update_post_meta( $post_id, '_room_description_0', '' );
			            update_post_meta( $post_id, '_room_description_0', $this->get_property_field($property, 'full_description') );
			            if ( get_post_meta( $post_id, '_room_description_0', TRUE ) == '' )
			            {
				            update_post_meta( $post_id, '_room_description_0', utf8_encode($this->get_property_field($property, 'full_description')) );
				        }
				    }
				}

				// Media - Images
				if ( 
					$inserted_updated == 'inserted' ||
					( $inserted_updated == 'updated' && isset($property['photos']) )
				)
				{
					if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
	    			{
	    				$media_urls = array();
	    				$explode_photos = explode(",", $this->get_property_field($property, 'photos'));

						for ( $i = 0; $i <= 49; ++$i )
						{
							if ( isset($explode_photos[$i]) && trim($explode_photos[$i]) != '' )
							{
								if ( 
									substr( strtolower($explode_photos[$i]), 0, 2 ) == '//' || 
									substr( strtolower($explode_photos[$i]), 0, 4 ) == 'http'
								)
								{
									// This is a URL
									$url = $explode_photos[$i];

									$media_urls[] = array('url' => $url);
								}
							}
						}
						update_post_meta( $post_id, '_photo_urls', $media_urls );

						$this->add_log( 'Imported ' . count($media_urls) . ' photo URLs', $property['imported_ref'] );
	    			}
	    			else
	    			{
						$media_ids = array();
						$new = 0;
						$existing = 0;
						$deleted = 0;
						$previous_media_ids = get_post_meta( $post_id, '_photos', TRUE );

						$explode_photos = explode(",", $this->get_property_field($property, 'photos'));

						for ( $i = 0; $i <= 49; ++$i )
						{
							if ( isset($explode_photos[$i]) && trim($explode_photos[$i]) != '' )
							{
								if ( 
									substr( strtolower($explode_photos[$i]), 0, 2 ) == '//' || 
									substr( strtolower($explode_photos[$i]), 0, 4 ) == 'http'
								)
								{
									// This is a URL
									$url = $explode_photos[$i];
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

									        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['imported_ref'] );
									    }
									    else
									    {
										    $id = media_handle_sideload( $file_array, $post_id, $description );

										    // Check for handle sideload errors.
										    if ( is_wp_error( $id ) ) 
										    {
										        @unlink( $file_array['tmp_name'] );
										        
										        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['imported_ref'] );
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

						$this->add_log( 'Imported ' . count($media_ids) . ' photos (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property['imported_ref'] );
					}
				}

				// Media - Floorplans
				if ( 
					$inserted_updated == 'inserted' ||
					( $inserted_updated == 'updated' && isset($property['floorplans']) )
				)
				{
					if ( get_option('propertyhive_floorplans_stored_as', '') == 'urls' )
	    			{
	    				$media_urls = array();
	    				$explode_floorplans = explode(",", $this->get_property_field($property, 'floorplans'));

						for ( $i = 0; $i <= 10; ++$i )
						{
							if ( isset($explode_floorplans[$i]) && trim($explode_floorplans[$i]) != '' )
							{
								if ( 
									substr( strtolower($explode_floorplans[$i]), 0, 2 ) == '//' || 
									substr( strtolower($explode_floorplans[$i]), 0, 4 ) == 'http'
								)
								{
									// This is a URL
									$url = $explode_floorplans[$i];

									$media_urls[] = array('url' => $url);
								}
							}
						}
						update_post_meta( $post_id, '_floorplan_urls', $media_urls );

						$this->add_log( 'Imported ' . count($media_urls) . ' floorplan URLs', $property['imported_ref'] );
	    			}
	    			else
	    			{
						$media_ids = array();
						$new = 0;
						$existing = 0;
						$deleted = 0;
						$previous_media_ids = get_post_meta( $post_id, '_floorplans', TRUE );

						$explode_floorplans = explode(",", $this->get_property_field($property, 'floorplans'));

						for ( $i = 0; $i <= 10; ++$i )
						{
							if ( isset($explode_floorplans[$i]) && trim($explode_floorplans[$i]) != '' )
							{
								if ( 
									substr( strtolower($explode_floorplans[$i]), 0, 2 ) == '//' || 
									substr( strtolower($explode_floorplans[$i]), 0, 4 ) == 'http'
								)
								{
									// This is a URL
									$url = $explode_floorplans[$i];
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
									        'name' => $filename,
									        'tmp_name' => $tmp
									    );

									    // Check for download errors
									    if ( is_wp_error( $tmp ) ) 
									    {
									        @unlink( $file_array[ 'tmp_name' ] );

									        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['imported_ref'] );
									    }
									    else
									    {
										    $id = media_handle_sideload( $file_array, $post_id, $description );

										    // Check for handle sideload errors.
										    if ( is_wp_error( $id ) ) 
										    {
										        @unlink( $file_array['tmp_name'] );
										        
										        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['imported_ref'] );
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

						$this->add_log( 'Imported ' . count($media_ids) . ' floorplans (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property['imported_ref'] );
					}
				}

				// Media - Brochures
				if ( 
					$inserted_updated == 'inserted' ||
					( $inserted_updated == 'updated' && isset($property['brochures']) )
				)
				{
					if ( get_option('propertyhive_brochures_stored_as', '') == 'urls' )
	    			{
	    				$media_urls = array();
	    				$explode_brochures = explode(",", $this->get_property_field($property, 'brochures'));

						for ( $i = 0; $i <= 10; ++$i )
						{
							$j = str_pad( $i, 2, '0', STR_PAD_LEFT );

							if ( isset($explode_brochures[$i]) && trim($explode_brochures[$i]) != '' )
							{
								if ( 
									substr( strtolower($explode_brochures[$i]), 0, 2 ) == '//' || 
									substr( strtolower($explode_brochures[$i]), 0, 4 ) == 'http'
								)
								{
									// This is a URL
									$url = $explode_brochures[$i];

									$media_urls[] = array('url' => $url);
								}
							}
						}
						update_post_meta( $post_id, '_brochure_urls', $media_urls );

						$this->add_log( 'Imported ' . count($media_urls) . ' brochure URLs', $property['imported_ref'] );
	    			}
	    			else
	    			{
						$media_ids = array();
						$new = 0;
						$existing = 0;
						$deleted = 0;
						$previous_media_ids = get_post_meta( $post_id, '_brochures', TRUE );

						$explode_brochures = explode(",", $this->get_property_field($property, 'brochures'));

						for ( $i = 0; $i <= 10; ++$i )
						{
							$j = str_pad( $i, 2, '0', STR_PAD_LEFT );

							if ( isset($explode_brochures[$i]) && trim($explode_brochures[$i]) != '' )
							{
								if ( 
									substr( strtolower($explode_brochures[$i]), 0, 2 ) == '//' || 
									substr( strtolower($explode_brochures[$i]), 0, 4 ) == 'http'
								)
								{
									// This is a URL
									$url = $explode_brochures[$i];
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
									        'name' => $filename,
									        'tmp_name' => $tmp
									    );

									    // Check for download errors
									    if ( is_wp_error( $tmp ) ) 
									    {
									        @unlink( $file_array[ 'tmp_name' ] );

									        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['imported_ref'] );
									    }
									    else
									    {
										    $id = media_handle_sideload( $file_array, $post_id, $description );

										    // Check for handle sideload errors.
										    if ( is_wp_error( $id ) ) 
										    {
										        @unlink( $file_array['tmp_name'] );
										        
										        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['imported_ref'] );
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

						$this->add_log( 'Imported ' . count($media_ids) . ' brochures (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property['imported_ref'] );
					}
				}

				// Media - EPCS
				if ( 
					$inserted_updated == 'inserted' ||
					( $inserted_updated == 'updated' && isset($property['epcs']) )
				)
				{
					if ( get_option('propertyhive_epcs_stored_as', '') == 'urls' )
	    			{
	    				$media_urls = array();

	    				$explode_epcs = explode(",", $this->get_property_field($property, 'epcs'));

						for ( $i = 0; $i <= 10; ++$i )
						{
							if ( isset($explode_epcs[$i]) && trim($explode_epcs[$i]) != '' )
							{
								if ( 
									substr( strtolower($explode_epcs[$i]), 0, 2 ) == '//' || 
									substr( strtolower($explode_epcs[$i]), 0, 4 ) == 'http'
								)
								{
									// This is a URL
									$url = $explode_epcs[$i];

									$media_urls[] = array('url' => $url);
								}
							}
						}

						update_post_meta( $post_id, '_epc_urls', $media_urls );

						$this->add_log( 'Imported ' . count($media_urls) . ' EPC URLs', $property['imported_ref'] );
	    			}
	    			else
	    			{
						$media_ids = array();
						$new = 0;
						$existing = 0;
						$deleted = 0;
						$previous_media_ids = get_post_meta( $post_id, '_epcs', TRUE );

						$explode_epcs = explode(",", $this->get_property_field($property, 'epcs'));

						for ( $i = 0; $i <= 10; ++$i )
						{
							if ( isset($explode_epcs[$i]) && trim($explode_epcs[$i]) != '' )
							{
								if ( 
									substr( strtolower($explode_epcs[$i]), 0, 2 ) == '//' || 
									substr( strtolower($explode_epcs[$i]), 0, 4 ) == 'http'
								)
								{
									// This is a URL
									$url = $explode_epcs[$i];
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
									        'name' => $filename,
									        'tmp_name' => $tmp
									    );

									    // Check for download errors
									    if ( is_wp_error( $tmp ) ) 
									    {
									        @unlink( $file_array[ 'tmp_name' ] );

									        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property['imported_ref'] );
									    }
									    else
									    {
										    $id = media_handle_sideload( $file_array, $post_id, $description );

										    // Check for handle sideload errors.
										    if ( is_wp_error( $id ) ) 
										    {
										        @unlink( $file_array['tmp_name'] );
										        
										        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property['imported_ref'] );
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

						$this->add_log( 'Imported ' . count($media_ids) . ' EPCs (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property['imported_ref'] );
					}
				}
			
				// Media - Virtual Tours
				if ( 
					$inserted_updated == 'inserted' ||
					( $inserted_updated == 'updated' && isset($property['virtual_tours']) )
				)
				{
					if ( $this->get_property_field($property, 'virtual_tours') != '' )
					{
						$explode_virtual_tours = explode(",", $this->get_property_field($property, 'virtual_tours'));

						update_post_meta($post_id, '_virtual_tours', count($explode_virtual_tours) );

						foreach ( $explode_virtual_tours as $i => $virtual_tour )
						{
							update_post_meta($post_id, '_virtual_tour_' . $i, trim($virtual_tour));
						}

						$this->add_log( 'Imported ' . count($explode_virtual_tours) . ' virtual tours', $property['imported_ref'] );
					}
				}

				if ( class_exists('PH_Property_Portal') )
	        	{
	        		if ( isset($property['agent_id']) && trim($property['agent_id']) != '' )
	        		{
	        			update_post_meta( $post_id, '_agent_id', $this->get_property_field($property, 'agent_id', TRUE) );
	        		
	        			if ( isset($property['branch_id']) && trim($property['branch_id']) != '' )
	        			{
							// Check branch belongs to agent
							$args = array(
								'post_type' => 'branch',
								'nopaging' => true,
								'meta_query' => array(
					                array(
					                    'key' => '_agent_id',
					                    'value' => $this->get_property_field($property, 'agent_id', TRUE)
					                )
					            )
							);

							$branch_query = new WP_Query( $args );

							if ( $branch_query->have_posts() )
							{
								while ( $branch_query->have_posts() )
								{
									$branch_query->the_post();

									if ( get_the_title() == $property['branch_id'] )
									{
										update_post_meta( $post_id, '_branch_id', get_the_ID() );
									}
								}
							}
							wp_reset_postdata();
						}
						else
						{
							// No branch provided. Just set it to the first one if branches exist
							$args = array(
								'post_type' => 'branch',
								'posts_per_page' => 1,
								'meta_query' => array(
					                array(
					                    'key' => '_agent_id',
					                    'value' => $this->get_property_field($property, 'agent_id', TRUE)
					                )
					            )
							);

							$branch_query = new WP_Query( $args );

							if ( $branch_query->have_posts() )
							{
								while ( $branch_query->have_posts() )
								{
									$branch_query->the_post();

									update_post_meta( $post_id, '_branch_id', get_the_ID() );
								}
							}
							wp_reset_postdata();
						}
					}
				}

				// Custom Fields
				if (class_exists('PH_Template_Assistant'))
		        {
		        	$current_settings = get_option( 'propertyhive_template_assistant', array() );

		        	$custom_fields = ( ( isset($current_settings['custom_fields']) ) ? $current_settings['custom_fields'] : array() );

		        	if ( !empty($custom_fields) )
		        	{
			        	foreach ( $custom_fields as $custom_field )
			            {
			            	$this->conditional_meta_update( $post_id, $property, $custom_field['field_name'], $custom_field['field_name'], $inserted_updated );
			            }
			        }
			    }

				// Fire actions
				// The realtime feed for example, might need executing

				do_action( "propertyhive_property_imported_csv", $post_id, $property );

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
							$this->add_log( 'New meta data for ' . trim($key, '_') . ': ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), $property['imported_ref'] );
						}
						elseif ( $metadata_before[$key] != $metadata_after[$key] )
						{
							$this->add_log( 'Updated ' . trim($key, '_') . '. Before: ' . ( ( is_array($metadata_before[$key]) ) ? implode(", ", $metadata_before[$key]) : $metadata_before[$key] ) . ', After: ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), $property['imported_ref'] );
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
							$this->add_log( 'New taxonomy data for ' . $taxonomy_name . ': ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), $property['imported_ref'] );
						}
						elseif ( $taxonomy_terms_before[$taxonomy_name] != $taxonomy_terms_after[$taxonomy_name] )
						{
							$this->add_log( 'Updated ' . $taxonomy_name . '. Before: ' . ( ( is_array($taxonomy_terms_before[$taxonomy_name]) ) ? implode(", ", $taxonomy_terms_before[$taxonomy_name]) : $taxonomy_terms_before[$taxonomy_name] ) . ', After: ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), $property['imported_ref'] );
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

		do_action( "propertyhive_post_import_properties_csv" );

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

					do_action( "propertyhive_property_removed_csv", $post->ID );
				}
			}
			wp_reset_postdata();

			unset($import_refs);
		}
	}

	public function get_target_file()
	{
		return $this->target_file;
	}

	public function get_mappings( $import_id = '' )
	{
		if ( !empty($this->mappings) )
		{
			return $this->mappings;
		}

		// Build mappings
		$mapping_values = $this->get_blm_mapping_values('availability');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['availability'][$mapping_value] = '';
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

		$mapping_values = $this->get_blm_mapping_values('commercial_property_type');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['commercial_property_type'][$mapping_value] = '';
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

	public function get_propertyhive_fields()
	{
		$departments = array();
		if ( get_option('propertyhive_active_departments_sales') == 'yes' )
		{
			$departments['residential-sales'] = 'Residential Sales';
		}
		if ( get_option('propertyhive_active_departments_lettings') == 'yes' )
		{
			$departments['residential-lettings'] = 'Residential Lettings';
		}
		if ( get_option('propertyhive_active_departments_commercial') == 'yes' )
		{
			$departments['commercial'] = 'Commercial';
		}

		$fields = array(

			'section_start_general' => array(
				'label' => 'General',
				'value_type' => 'section_start',
			),

			'imported_ref' => array(
				'label' => 'Unique ID',
				'value_type' => 'meta',
				'field_name' => '_imported_ref',
				'desc' => 'Should contain a unique ID (not necessarily the post ID). We\'ll use this if you need to re-run the import to prevent duplicate properties being created'
			),

			'post_id' => array(
				'label' => 'Post ID',
				'value_type' => 'meta',
				'field_name' => '_post_id',
				'desc' => 'Alternatively use the post ID of the original record to update an existing property'
			),

			'section_end_general' => array(
				'value_type' => 'section_end',
			),

			'section_start_property_address' => array(
				'label' => 'Property Address',
				'value_type' => 'section_start',
			),

			'post_title' => array(
				'label' => 'Display Address',
				'value_type' => 'post',
				'field_name' => 'post_title'
			),
			'reference_number' => array(
				'label' => 'Reference Number',
				'value_type' => 'meta',
				'field_name' => '_reference_number'
			),
			'address_name_number' => array(
				'label' => 'Building Name/Number',
				'value_type' => 'meta',
				'field_name' => '_address_name_number'
			),
			'address_street' => array(
				'label' => 'Street',
				'value_type' => 'meta',
				'field_name' => '_address_street'
			),
			'address_2' => array(
				'label' => 'Address Line 2',
				'value_type' => 'meta',
				'field_name' => '_address_2'
			),
			'address_3' => array(
				'label' => 'Town / City',
				'value_type' => 'meta',
				'field_name' => '_address_3'
			),
			'address_4' => array(
				'label' => 'County',
				'value_type' => 'meta',
				'field_name' => '_address_4'
			),
			'address_postcode' => array(
				'label' => 'Postcode',
				'value_type' => 'meta',
				'field_name' => '_address_postcode'
			)
		);

		$countries = get_option( 'propertyhive_countries', array() );
		if ( is_array($countries) && count($countries) > 1 )
		{
			$fields['address_country'] = array(
				'label' => 'Country',
				'value_type' => 'meta',
				'field_name' => '_address_country',
				'desc' => 'If not provided we\'ll set this to ' . get_option('propertyhive_default_country', 'GB')
			);
		}

		$default_country_code = get_option('propertyhive_default_country', 'GB');
		$PH_Countries = new PH_Countries();

		$default_country = $PH_Countries->get_country( $default_country_code );
		$default_currency = $default_country['currency_code'];

		$fields['currency'] = array(
			'label' => 'Currency Code',
			'value_type' => 'meta',
			'field_name' => '_currency',
			'desc' => 'Should contain the three letter ISO code representing currency. If not provided we\'ll set this to ' . $default_currency
		);

		$args = array(
            'hide_empty' => false,
            'parent' => 0
        );
        $terms = get_terms( 'location', $args );
        
        $options = array();
        if ( !empty( $terms ) && !is_wp_error( $terms ) )
        {
            foreach ($terms as $term)
            {
                $options[$term->term_id] = $term->name;

                $args = array(
                    'hide_empty' => false,
                    'parent' => $term->term_id
                );
                $subterms = get_terms( 'location', $args );
                
                if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
                {
                    foreach ($subterms as $term)
                    {
                        $options[$term->term_id] = $term->name;
                        
                        $args = array(
                            'hide_empty' => false,
                            'parent' => $term->term_id
                        );
                        $subsubterms = get_terms( 'location', $args );
                        
                        if ( !empty( $subsubterms ) && !is_wp_error( $subsubterms ) )
                        {
                            foreach ($subsubterms as $term)
                            {
                                $options[$term->term_id] = $term->name;
                            }
                        }
                    }
                }
            }
        }

		$fields['location'] = array(
			'label' => 'Location',
			'value_type' => 'taxonomy',
			'field_name' => 'location',
			'possible_values' => $options
		);

		$fields['latitude'] = array(
			'label' => 'Latitude',
			'value_type' => 'meta',
			'field_name' => '_latitude',
			'desc' => 'If not provided we\'ll try to obtain the coordinates from the address provided using the Google Geocoding service'
		);
		$fields['longitude'] = array(
			'label' => 'Longitude',
			'value_type' => 'meta',
			'field_name' => '_longitude'
		);

		$negotiators = array();
		$args = array(
            'number' => 9999,
            'orderby' => 'display_name',
            'role__not_in' => array('property_hive_contact') 
        );
        $user_query = new WP_User_Query( $args );

        $negotiators = array();

        if ( ! empty( $user_query->results ) ) 
        {
            foreach ( $user_query->results as $user ) 
            {
                $negotiators[$user->ID] = $user->display_name;
            }
        }

        if (count($negotiators) > 1)
        {
			$fields['negotiator'] = array(
				'label' => 'Negotiator',
				'value_type' => 'meta',
				'field_name' => '_negotiator_id',
				'desc' => 'If not provided we\'ll assign properties to you',
				'possible_values' => $negotiators
			);
		}

		$primary_office_id = '';
		$offices = array();
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

                $offices[get_the_ID()] = get_the_title();
            }
        }
        $office_query->reset_postdata();

        if (count($offices) > 1)
        {
			$fields['office'] = array(
				'label' => 'Office',
				'value_type' => 'meta',
				'field_name' => '_office_id',
				'desc' => 'If not provided we\'ll set this to the primary office: ' . get_the_title($primary_office_id),
				'possible_values' => $offices
			);
		}

		$fields['section_end_property_address'] = array(
			'value_type' => 'section_end',
		);

		$fields['section_start_property_details'] = array(
			'label' => 'Property Details',
			'value_type' => 'section_start',
		);

		$fields['department'] = array(
			'label' => 'Department',
			'value_type' => 'meta',
			'field_name' => '_department',
			'desc' => 'If not provided we\'ll set this to the primary department: ' . ucwords(str_replace("-", " ", get_option('propertyhive_primary_department', 'residential-sales'))),
			'possible_values' => $departments
		);

		$fields['section_end_property_details'] = array(
			'value_type' => 'section_end',
		);

		if (in_array('Residential Sales', $departments))
		{
			$fields['section_start_property_residential_sales_details'] = array(
				'label' => 'Residential Sales Details',
				'value_type' => 'section_start',
			);

			$fields['price'] = array(
				'label' => 'Price',
				'value_type' => 'meta',
				'field_name' => '_price',
				'desc' => '',
			);

			// Price Qualifier
	        $args = array(
	            'hide_empty' => false,
	            'parent' => 0
	        );
	        $terms = get_terms( 'price_qualifier', $args );
	        
	        $options = array();
	        if ( !empty( $terms ) && !is_wp_error( $terms ) )
	        {
	            foreach ($terms as $term)
	            {
	                $options[$term->term_id] = $term->name;
	            }
	        }
			$fields['price_qualifier'] = array(
				'label' => 'Price Qualifier',
				'value_type' => 'taxonomy',
				'field_name' => 'price_qualifier',
				'possible_values' => $options,
			);

			// Sale By
	        $args = array(
	            'hide_empty' => false,
	            'parent' => 0
	        );
	        $terms = get_terms( 'sale_by', $args );
	        
	        $options = array();
	        if ( !empty( $terms ) && !is_wp_error( $terms ) )
	        {
	            foreach ($terms as $term)
	            {
	                $options[$term->term_id] = $term->name;
	            }
	        }
			$fields['sale_by'] = array(
				'label' => 'Sale By',
				'value_type' => 'taxonomy',
				'field_name' => 'sale_by',
				'possible_values' => $options,
			);

			// Tenure
	        $args = array(
	            'hide_empty' => false,
	            'parent' => 0
	        );
	        $terms = get_terms( 'tenure', $args );
	        
	        $options = array();
	        if ( !empty( $terms ) && !is_wp_error( $terms ) )
	        {
	            foreach ($terms as $term)
	            {
	                $options[$term->term_id] = $term->name;
	            }
	        }
			$fields['tenure'] = array(
				'label' => 'Tenure',
				'value_type' => 'taxonomy',
				'field_name' => 'tenure',
				'possible_values' => $options,
			);

			$fields['section_end_property_residential_sales_details'] = array(
				'value_type' => 'section_end',
			);
		}

		if (in_array('Residential Lettings', $departments))
		{
			$fields['section_start_property_residential_lettings_details'] = array(
				'label' => 'Residential Lettings Details',
				'value_type' => 'section_start',
			);

			$fields['rent'] = array(
				'label' => 'Rent',
				'value_type' => 'meta',
				'field_name' => '_rent',
				'desc' => '',
			);

			$fields['rent_frequency'] = array(
				'label' => 'Rent Frequency',
				'value_type' => 'meta',
				'field_name' => '_rent_frequency',
				'possible_values' => array('pppw' => 'PPPW', 'pw' => 'PW', 'pcm' => 'PCM', 'pq' => 'PQ', 'pa' => 'PA'),
				'desc' => 'If not provided we\'ll default this to PCM',
			);

			$fields['deposit'] = array(
				'label' => 'Deposit',
				'value_type' => 'meta',
				'field_name' => '_deposit',
			);

			// Furnished
	        $args = array(
	            'hide_empty' => false,
	            'parent' => 0
	        );
	        $terms = get_terms( 'furnished', $args );
	        
	        $options = array();
	        if ( !empty( $terms ) && !is_wp_error( $terms ) )
	        {
	            foreach ($terms as $term)
	            {
	                $options[$term->term_id] = $term->name;
	            }
	        }
			$fields['furnished'] = array(
				'label' => 'Furnishing',
				'value_type' => 'taxonomy',
				'field_name' => 'furnished',
				'possible_values' => $options,
			);

			$fields['available_date'] = array(
				'label' => 'Available Date',
				'value_type' => 'meta',
				'field_name' => '_available_date',
				'desc' => 'If a date is provided it should be in the format YYYY-MM-DD',
			);

			$fields['section_end_property_residential_lettings_details'] = array(
				'value_type' => 'section_end',
			);
		}

		if (in_array('Residential Sales', $departments) || in_array('Residential Lettings', $departments))
		{
			$fields['section_start_property_residential_details'] = array(
				'label' => 'Residential Details',
				'value_type' => 'section_start',
			);

			$fields['bedrooms'] = array(
				'label' => 'Bedrooms',
				'value_type' => 'meta',
				'field_name' => '_bedrooms',
			);

			$fields['bathrooms'] = array(
				'label' => 'Bathrooms',
				'value_type' => 'meta',
				'field_name' => '_bathrooms',
			);

			$fields['reception_rooms'] = array(
				'label' => 'Reception Rooms',
				'value_type' => 'meta',
				'field_name' => '_reception_rooms',
			);

			// Property Type
	        $args = array(
	            'hide_empty' => false,
	            'parent' => 0
	        );
	        $terms = get_terms( 'property_type', $args );
	        
	        $options = array();
	        if ( !empty( $terms ) && !is_wp_error( $terms ) )
	        {
	            foreach ($terms as $term)
	            {
	                $options[$term->term_id] = $term->name;

	                $args = array(
	                    'hide_empty' => false,
	                    'parent' => $term->term_id
	                );
	                $subterms = get_terms( 'property_type', $args );
	                
	                if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
	                {
	                    foreach ($subterms as $term)
	                    {
	                        $options[$term->term_id] = $term->name;
	                    }
	                }
	            }
	        }
			$fields['property_type'] = array(
				'label' => 'Property Type',
				'value_type' => 'taxonomy',
				'field_name' => 'property_type',
				'possible_values' => $options,
			);

			// Parking
	        $args = array(
	            'hide_empty' => false,
	            'parent' => 0
	        );
	        $terms = get_terms( 'parking', $args );
	        
	        $options = array();
	        if ( !empty( $terms ) && !is_wp_error( $terms ) )
	        {
	            foreach ($terms as $term)
	            {
	                $options[$term->term_id] = $term->name;
	            }
	        }
			$fields['parking'] = array(
				'label' => 'Parking',
				'value_type' => 'taxonomy',
				'field_name' => 'parking',
				'possible_values' => $options,
			);

			$fields['section_end_property_residential_details'] = array(
				'value_type' => 'section_end',
			);
		}

		if (in_array('Commercial', $departments))
		{
			$fields['section_start_property_commercial_details'] = array(
				'label' => 'Commercial Details',
				'value_type' => 'section_start',
			);

			$fields['for_sale'] = array(
				'label' => 'For Sale',
				'value_type' => 'meta',
				'field_name' => '_for_sale',
				'possible_values' => array('' => '(empty)', 'yes' => 'yes'),
			);

			$fields['to_rent'] = array(
				'label' => 'To Rent',
				'value_type' => 'meta',
				'field_name' => '_to_rent',
				'possible_values' => array('' => '(empty)', 'yes' => 'yes'),
			);

			$fields['price_from'] = array(
				'label' => 'Price From',
				'value_type' => 'meta',
				'field_name' => '_price_from',
				'desc' => 'Only applicable if \'For Sale\' is \'yes\'',
			);

			$fields['price_to'] = array(
				'label' => 'Price To',
				'value_type' => 'meta',
				'field_name' => '_price_to',
				'desc' => 'Only applicable if \'For Sale\' is \'yes\'',
			);

			$fields['price_units'] = array(
				'label' => 'Price Units',
				'value_type' => 'meta',
				'field_name' => '_price_units',
				'possible_values' => array_merge(array('' => '(empty)'), get_commercial_price_units( )),
				'desc' => 'Only applicable if \'For Sale\' is \'yes\'',
			);

			// Sale By
	        $args = array(
	            'hide_empty' => false,
	            'parent' => 0
	        );
	        $terms = get_terms( 'sale_by', $args );
	        
	        $options = array();
	        if ( !empty( $terms ) && !is_wp_error( $terms ) )
	        {
	            foreach ($terms as $term)
	            {
	                $options[$term->term_id] = $term->name;
	            }
	        }
			$fields['commercial_sale_by'] = array(
				'label' => 'Sale By',
				'value_type' => 'taxonomy',
				'field_name' => 'sale_by',
				'possible_values' => $options,
			);

			// Tenure
	        $args = array(
	            'hide_empty' => false,
	            'parent' => 0
	        );
	        $terms = get_terms( 'commercial_tenure', $args );
	        
	        $options = array();
	        if ( !empty( $terms ) && !is_wp_error( $terms ) )
	        {
	            foreach ($terms as $term)
	            {
	                $options[$term->term_id] = $term->name;
	            }
	        }
			$fields['commercial_tenure'] = array(
				'label' => 'Tenure',
				'value_type' => 'taxonomy',
				'field_name' => 'commercial_tenure',
				'possible_values' => $options,
			);

			$fields['rent_from'] = array(
				'label' => 'Rent From',
				'value_type' => 'meta',
				'field_name' => '_rent_from',
				'desc' => 'Only applicable if \'To Rent\' is \'yes\'',
			);

			$fields['rent_to'] = array(
				'label' => 'Rent To',
				'value_type' => 'meta',
				'field_name' => '_rent_to',
				'desc' => 'Only applicable if \'To Rent\' is \'yes\'',
			);

			$fields['rent_units'] = array(
				'label' => 'Rent Units',
				'value_type' => 'meta',
				'field_name' => '_rent_units',
				'possible_values' => array('pppw' => 'PPPW', 'pw' => 'PW', 'pcm' => 'PCM', 'pq' => 'PQ', 'pa' => 'PA'),
				'desc' => 'Only applicable if \'To Rent\' is \'yes\'. If not provided we\'ll default this to PCM',
			);

			// Commercial Property Type
	        $args = array(
	            'hide_empty' => false,
	            'parent' => 0
	        );
	        $terms = get_terms( 'commercial_property_type', $args );
	        
	        $options = array();
	        if ( !empty( $terms ) && !is_wp_error( $terms ) )
	        {
	            foreach ($terms as $term)
	            {
	                $options[$term->term_id] = $term->name;

	                $args = array(
	                    'hide_empty' => false,
	                    'parent' => $term->term_id
	                );
	                $subterms = get_terms( 'commercial_property_type', $args );
	                
	                if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
	                {
	                    foreach ($subterms as $term)
	                    {
	                        $options[$term->term_id] = $term->name;
	                    }
	                }
	            }
	        }
			$fields['commercial_property_type'] = array(
				'label' => 'Property Type',
				'value_type' => 'taxonomy',
				'field_name' => 'commercial_property_type',
				'possible_values' => $options,
				'supports_multiple' => TRUE,
			);

			$fields['floor_area_from'] = array(
				'label' => 'Floor Area From',
				'value_type' => 'meta',
				'field_name' => '_floor_area_from',
				'desc' => '',
			);

			$fields['floor_area_to'] = array(
				'label' => 'Floor Area To',
				'value_type' => 'meta',
				'field_name' => '_floor_area_to',
				'desc' => '',
			);

			$fields['floor_area_units'] = array(
				'label' => 'Floor Area Units',
				'value_type' => 'meta',
				'field_name' => '_floor_area_units',
				'possible_values' => get_area_units( ),
				'desc' => 'If not provided we\'ll default this to Sq Ft',
			);

			$fields['section_end_property_commercial_details'] = array(
				'value_type' => 'section_end',
			);
		}

		$fields['section_start_marketing'] = array(
			'label' => 'Marketing',
			'value_type' => 'section_start',
		);

		$fields['on_market'] = array(
			'label' => 'On Market',
			'value_type' => 'meta',
			'field_name' => '_on_market',
			'possible_values' => array('' => '(empty)', 'yes' => 'yes'),
		);

		// Availability
        $args = array(
            'hide_empty' => false,
            'parent' => 0
        );
        $terms = get_terms( 'availability', $args );
        
        $options = array();
        if ( !empty( $terms ) && !is_wp_error( $terms ) )
        {
            foreach ($terms as $term)
            {
                $options[$term->term_id] = $term->name;
            }
        }
		$fields['availability'] = array(
			'label' => 'Availability',
			'value_type' => 'taxonomy',
			'field_name' => 'availability',
			'possible_values' => $options,
		);

		$fields['featured'] = array(
			'label' => 'Featured',
			'value_type' => 'meta',
			'field_name' => '_featured',
			'possible_values' => array('' => '(empty)', 'yes' => 'yes'),
		);

		// Marketing Flag
        $args = array(
            'hide_empty' => false,
            'parent' => 0
        );
        $terms = get_terms( 'marketing_flag', $args );
        
        $options = array();
        if ( !empty( $terms ) && !is_wp_error( $terms ) )
        {
            foreach ($terms as $term)
            {
                $options[$term->term_id] = $term->name;
            }
        }
		$fields['marketing_flag'] = array(
			'label' => 'Marketing Flag',
			'value_type' => 'taxonomy',
			'field_name' => 'marketing_flag',
			'possible_values' => $options,
		);

		if (class_exists('PH_Blmexport'))
        {
			$portals = array();
	        $current_blmexport_options = get_option( 'propertyhive_blmexport' );
	            
	        if ($current_blmexport_options !== FALSE)
	        {
	            if (isset($current_blmexport_options['portals']))
	            {
	                $portals = $current_blmexport_options['portals'];
	            }
	        }

	        if (!empty($portals))
	        {
	            foreach ($portals as $portal_id => $portal)
	            {
	            	if ($portal['mode'] == 'test' || $portal['mode'] == 'live')
	    			{
	    				$fields['_portal_' . $portal_id] = array(
							'label' => 'Active on ' . $portal['name'],
							'value_type' => 'meta',
							'field_name' => '_portal_' . $portal_id,
							'possible_values' => array('' => '(empty)', 'yes' => 'yes'),
						);
	    			}
	            }
	        }
		}

		if (class_exists('PH_Realtimefeed'))
        {
			$portals = array();
	        $current_realtime_feed_options = get_option( 'propertyhive_realtimefeed' );
	            
	        if ($current_realtime_feed_options !== FALSE)
	        {
	            if (isset($current_realtime_feed_options['portals']))
	            {
	                $portals = $current_realtime_feed_options['portals'];
	            }
	        }

	        if (!empty($portals))
	        {
	            foreach ($portals as $portal_id => $portal)
	            {
	            	if ($portal['mode'] == 'test' || $portal['mode'] == 'live')
	    			{
	    				$fields['_realtime_portal_' . $portal_id] = array(
							'label' => 'Active on ' . $portal['name'],
							'value_type' => 'meta',
							'field_name' => '_realtime_portal_' . $portal_id,
							'possible_values' => array('' => '(empty)', 'yes' => 'yes'),
						);
	    			}
	            }
	        }
		}

		if (class_exists('PH_Zooplarealtimefeed'))
        {
			$portals = array();
	        $current_realtime_feed_options = get_option( 'propertyhive_zooplarealtimefeed' );
	            
	        if ($current_realtime_feed_options !== FALSE)
	        {
	            if (isset($current_realtime_feed_options['portals']))
	            {
	                $portals = $current_realtime_feed_options['portals'];
	            }
	        }

	        if (!empty($portals))
	        {
	            foreach ($portals as $portal_id => $portal)
	            {
	            	if ($portal['mode'] == 'test' || $portal['mode'] == 'live')
	    			{
	    				$fields['_zoopla_realtime_portal_' . $portal_id] = array(
							'label' => 'Active on ' . $portal['name'],
							'value_type' => 'meta',
							'field_name' => '_zoopla_realtime_portal_' . $portal_id,
							'possible_values' => array('' => '(empty)', 'yes' => 'yes'),
						);
	    			}
	            }
	        }
		}

		$fields['section_end_marketing'] = array(
			'value_type' => 'section_end',
		);

		$fields['section_start_descriptions'] = array(
			'label' => 'Descriptions',
			'value_type' => 'section_start',
		);

		$fields['features'] = array(
			'label' => 'Features',
			'value_type' => 'special',
			'field_name' => 'features',
			'desc' => 'If provided should be a comma separated list of key features',
		);

		$fields['post_excerpt'] = array(
			'label' => 'Summary Description',
			'value_type' => 'post',
			'field_name' => 'post_excerpt',
		);

		$fields['full_description'] = array(
			'label' => 'Full Description',
			'value_type' => 'special',
			'field_name' => 'full_description',
		);

		$fields['section_end_descriptions'] = array(
			'value_type' => 'section_end',
		);

		$fields['section_start_media'] = array(
			'label' => 'Media',
			'value_type' => 'section_start',
		);

		$fields['photos'] = array(
			'label' => 'Photos',
			'value_type' => 'special',
			'field_name' => 'photos',
			'desc' => 'If provided should be a comma separated list of full URLs',
		);

		$fields['floorplans'] = array(
			'label' => 'Floorplans',
			'value_type' => 'special',
			'field_name' => 'floorplans',
			'desc' => 'If provided should be a comma separated list of full URLs',
		);

		$fields['brochures'] = array(
			'label' => 'Brochures',
			'value_type' => 'special',
			'field_name' => 'brochures',
			'desc' => 'If provided should be a comma separated list of full URLs',
		);

		$fields['epcs'] = array(
			'label' => 'EPCs',
			'value_type' => 'special',
			'field_name' => 'epcs',
			'desc' => 'If provided should be a comma separated list of full URLs',
		);

		$fields['virtual_tours'] = array(
			'label' => 'Virtual Tours',
			'value_type' => 'special',
			'field_name' => 'virtual_tours',
			'desc' => 'If provided should be a comma separated list of full URLs',
		);

		$fields['section_end_media'] = array(
			'value_type' => 'section_end',
		);

		if (class_exists('PH_Property_Portal'))
        {
        	$fields['section_start_property_portal'] = array(
				'label' => 'Agent / Branch',
				'value_type' => 'section_start',
			);

        	$args = array(
        		'post_type' => 'agent',
        		'nopaging' => true,
        		'orderby' => 'title',
      			'order' => 'ASC'
        	);

        	$agents_query = new WP_Query( $args );

        	$agents = array();

        	if ( $agents_query->have_posts() )
        	{
        		while ( $agents_query->have_posts() )
        		{
        			$agents_query->the_post();

        			$agents[get_the_ID()] = get_the_title();
        		}
        	}

        	$fields['agent_id'] = array(
				'label' => 'Agent Name',
				'value_type' => 'meta',
				'field_name' => '_agent_id',
				'possible_values' => $agents,
			);

        	$fields['branch_id'] = array(
				'label' => 'Branch Name',
				'value_type' => 'meta',
				'field_name' => '_branch_id',
			);

        	$fields['section_end_property_portal'] = array(
			'value_type' => 'section_end',
		);
        }

		if (class_exists('PH_Template_Assistant'))
        {
        	$current_settings = get_option( 'propertyhive_template_assistant', array() );

        	$custom_fields = ( ( isset($current_settings['custom_fields']) ) ? $current_settings['custom_fields'] : array() );

        	if ( !empty($custom_fields) )
        	{
        		$fields['section_start_custom_fields'] = array(
					'label' => 'Custom Fields',
					'value_type' => 'section_start',
				);

	        	foreach ( $custom_fields as $custom_field )
	            {
	            	$fields[$custom_field['field_name']] = array(
						'label' => $custom_field['field_label'],
						'value_type' => 'meta',
						'field_name' => $custom_field['field_name'],
					);

	            	if ( 
	            		( $custom_field['field_type'] == 'select' || $custom_field['field_type'] == 'multiselect' ) && 
	            		isset($custom_field['dropdown_options']) && 
	            		is_array($custom_field['dropdown_options']) 
	            	)
                    {
                    	$possible_values = array('' => '(empty)');

                    	foreach ( $custom_field['dropdown_options'] as $dropdown_option )
                        {
                            $possible_values[$dropdown_option] = $dropdown_option;
                        }
                    	
                    	$fields[$custom_field['field_name']]['possible_values'] = $possible_values;
                    }
	            }

	            $fields['section_end_custom_fields'] = array(
					'value_type' => 'section_end',
				);
	        }
        }

		$fields = apply_filters( 'propertyhive_csv_fields', $fields );

		return $fields;
	}

}

}