<?php
/**
 * Class for managing the import process of a Vebra API XML file
 *
 * @package WordPress
 */
if ( class_exists( 'PH_Property_Import_Process' ) ) {

class PH_Vebra_API_XML_Import extends PH_Property_Import_Process {

	/**
	 * @var string
	 */
	private $target_file;

	/**
	 * @var string
	 */
	private $username;

	/**
	 * @var string
	 */
	private $password;

	/**
	 * @var string
	 */
	private $datafeed_id;

	/**
	 * @var string
	 */
	private $uploads_dir;

	public function __construct( $username = '', $password = '', $datafeed_id = '', $uploads_dir = '', $instance_id = '' ) 
	{
		$this->username = $username;
		$this->password = $password;
		$this->datafeed_id = $datafeed_id;
		$this->uploads_dir = $uploads_dir;
		$this->instance_id = $instance_id;

		if ( $this->instance_id != '' && isset($_GET['custom_property_import_cron']) )
	    {
	    	$current_user = wp_get_current_user();

	    	$this->add_log("Executed manually by " . ( ( isset($current_user->display_name) ) ? $current_user->display_name : '' ) );
	    }
	}

	// Function to authenticate self to API and return/store the Token
	private function get_token($url, $filename) 
	{
		// Overwriting the response headers from each attempt in this file (for information only)
		$file = $this->uploads_dir . "headers.txt";

		if ( file_exists($file) )
		{
			unlink($file);
		}

		$fh = fopen($file, "w");
		
		// Start curl session
		$ch = curl_init($url);
		// Define Basic HTTP Authentication method
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		// Provide Username and Password Details
		curl_setopt($ch, CURLOPT_USERPWD, $this->username . ":" . $this->password);
		// Show headers in returned data but not body as we are only using this curl session to aquire and store the token
		curl_setopt($ch, CURLOPT_HEADER, 1); 
		curl_setopt($ch, CURLOPT_NOBODY, 1); 
		// write the output (returned headers) to file
		curl_setopt($ch, CURLOPT_FILE, $fh);
		// execute curl session
		curl_exec($ch);
		// close curl session
		curl_close($ch); 

		// close headers.txt file
		fclose($fh);

		// read each line of the returned headers back into an array
		$headers = file($this->uploads_dir . 'headers.txt', FILE_SKIP_EMPTY_LINES);
		
		$token = '';

		// For each line of the array explode the line by ':' (Seperating the header name from its value)
		foreach ( $headers as $headerLine )
		{
			$line = explode(':', $headerLine);
			$header = $line[0];
			
			// If the request is successful and we are returned a token
			if ( $header == "Token" ) 
			{
				$value = trim($line[1]);

				// Save token start and expire time (roughly)
				$tokenStart = time(); 
				$tokenExpire = $tokenStart + (60 * 60);

				$token = base64_encode($value);
				$this->add_log("Got new token: " . $token);

				// For now write this new token, its start and expiry datetime into a .txt (appending not overwriting - this is for reference in case you lose your session data)
				$file = $this->uploads_dir . "tokens.txt";
				$fh = fopen($file, "a+");
				// Write the line in
				$newLine = "" . $token . "," . date('Y-m-d H:i:s', $tokenStart) . "," . date('Y-m-d H:i:s', $tokenExpire) . "" . "\n";
				fwrite($fh, $newLine);
				// Close file
				fclose($fh);
			}
		}
		
		unlink($this->uploads_dir . 'headers.txt');
		
		// If we have been given a token request XML from the API authenticating using the token
		if ( !empty($token) ) 
		{
			$this->connect($url, $filename);
		}
		else
		{
			// If we have not been given a new token its because we already have a live token which has not expired yet (check the tokens.txt file)
			//log_error("There is still an active Token, you must wait for this token to expire before a new one can be requested!");
		}
	}

	// Function to connect to the API authenticating ourself with the token we have been given
	private function connect($url, $filename) {

		$token = '';

		// get latest token
		$file = $this->uploads_dir . "tokens.txt";
		if ( file_exists($file) )
		{
			$tokenRows = file($file, FILE_SKIP_EMPTY_LINES);
			$numTokens = count($tokenRows);

			$timeNowSecs = time();

			foreach ($tokenRows as $tokenRow) 
			{
				$tokenRow = explode(",", $tokenRow);
				$tokenValue = $tokenRow[0];
				$tokenStart = $tokenRow[1];
				$tokenStartSecs = strtotime($tokenStart);
				$tokenExpiry = $tokenRow[2];
				$tokenExpirySecs = strtotime($tokenExpiry);
				//echo "Checking " . $timeNowSecs . " against start " . $tokenStartSecs . " and end " . $tokenExpirySecs . "\n";
				if ( $timeNowSecs >= $tokenStartSecs && $timeNowSecs <= $tokenExpirySecs )
				{
					// We have a token that is currently valid
					$token = $tokenValue;
				}
			}
		}

		// If token is not set skip to else condition to request a new token 
		if ( !empty($token) ) 
		{
			// Set a new file name and create a new file handle for our returned XML
			$file = $filename;

			if ( file_exists($file) )
			{
				unlink($file);
			}

			$fh = fopen($file, "w");
			
			// Initiate a new curl session
			$ch = curl_init($url);
			// Don't require header this time as curl_getinfo will tell us if we get HTTP 200 or 401
			curl_setopt($ch, CURLOPT_HEADER, 0); 
			// Provide Token in header
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Basic ' . $token));
			// Write returned XML to file
			curl_setopt($ch, CURLOPT_FILE, $fh);
			// Execute the curl session
			curl_exec($ch);
			
			// Store the curl session info/returned headers into the $info array
			$info = curl_getinfo($ch);

			// Check if we have been authorised or not
			if ( $info['http_code'] == '401' ) 
			{
				$this->get_token($url, $filename);
			}
			elseif ( $info['http_code'] == '200' )
			{
				
			}
			else
			{
				$this->add_log("Got HTTP code: " . $info['http_code'] . " when making request to " . $url);
			}
			
			// Close the curl session
			curl_close($ch);
			// Close the open file handle
			fclose($fh);
			
		}
		else
		{
			// Run the getToken function above if we are not authenticated
			$this->get_token($url, $filename);
		}
		
	}

	public function get_properties_for_initial_population()
	{
		$this->add_log("Getting all properties for initial population");

		$request = "http://webservices.vebra.com/export/" . $this->datafeed_id . "/v9/branch";

		$branches_file = $this->uploads_dir . "branches.xml";

		$this->connect($request, $branches_file);

		if ( file_exists($branches_file) )
		{
			$branches_xml = @simplexml_load_file($branches_file);

			if ( $branches_xml !== FALSE )
			{
				foreach ( $branches_xml->branch as $branch )
				{
					$branch_xml_url = (string)$branch->url;

					// We have the branch. Now get all properties for this branch
					$request = $branch_xml_url . "/property";

					$properties_file = $this->uploads_dir . "properties.xml";

					$this->connect($request, $properties_file);

					if ( file_exists($properties_file) )
					{
						$properties_xml = @simplexml_load_file($properties_file);

						if ( $properties_xml !== FALSE )
						{
							foreach ( $properties_xml->property as $property )
							{
								$property_xml_url = (string)$property->url;

								$request = $property_xml_url;

								$property_file = $this->uploads_dir . "property.xml";

								$this->connect($request, $property_file);

								if ( file_exists($property_file) )
								{
									$property_xml = @simplexml_load_file($property_file);

									if ( $property_xml !== FALSE )
									{
										$property_xml->addChild('action', 'updated');

										$this->properties[] = $property_xml;
									}
									else
									{
										//echo 'Failed to parse property XML';
									}

									unlink($property_file);
								}
								else
								{
									//echo 'File ' . $property_file . ' doesnt exist';
								}
							}
						}
						else
						{
							//echo 'Failed to parse properties XML';
						}

						unlink($properties_file);
					}
					else
					{
						//echo 'File ' . $properties_file . ' doesnt exist';
					}
				}
			}
			else
			{
				//echo 'Failed to parse branches XML';
			}

			unlink($branches_file);
		}
		else
		{
			//echo 'File ' . $branches_file . ' doesnt exist';
		}
	}

	public function get_changed_properties( $date_ran_before, $import_id )
	{
		$this->add_log("Getting properties updated since " . $date_ran_before);

		$request = "http://webservices.vebra.com/export/" . $this->datafeed_id . "/v9/property/" . 
			date("Y", strtotime($date_ran_before)) . "/" . 
			date("m", strtotime($date_ran_before)) . "/" . 
			date("d", strtotime($date_ran_before)) . "/" . 
			date("H", strtotime($date_ran_before)) . "/" . 
			date("i", strtotime($date_ran_before)) . "/" . 
			date("s", strtotime($date_ran_before));

		$properties_file = $this->uploads_dir . "properties.xml";

		$this->connect($request, $properties_file);

		if ( file_exists($properties_file) )
		{
			$properties_xml = @simplexml_load_file($properties_file);

			if ( $properties_xml !== FALSE )
			{
				foreach ( $properties_xml->property as $property )
				{
					if ( isset($property->action) && (string)$property->action == 'deleted' )
					{
						$options = get_option( 'propertyhive_property_import' );
						if (isset($options[$import_id]))
						{
							$options = $options[$import_id];
						
							$imported_ref_key = ( ( $import_id != '' ) ? '_imported_ref_' . $import_id : '_imported_ref' );

							$args = array(
								'post_type' => 'property',
								'nopaging' => true,
								'meta_query' => array(
									'relation' => 'AND',
									array(
										'key'     => $imported_ref_key,
										'value'   => (string)$property->propid,
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

									if (!isset($options['dont_remove']) || $options['dont_remove'] != '1')
									{
										update_post_meta( $post->ID, '_on_market', '' );

										$this->add_log( 'Property ' . $post->ID . ' marked as not on market', (string)$property->propid );

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

												$this->add_log( 'Deleted property media', (string)$property->propid );
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
													$this->add_error( 'Failed to set post as draft. The error was as follows: ' . $post_id->get_error_message(), (string)$property->propid );
												}
												else
												{
													$this->add_log( 'Drafted property', (string)$property->propid );
												}
											}
											elseif ( $options['remove_action'] == 'remove_property' )
											{
												wp_delete_post( $post->ID, true );
												$this->add_log( 'Deleted property', (string)$property->propid );
											}
										}
									}

									do_action( "propertyhive_property_removed_vebra_api_xml", $post->ID );
								}
							}
							wp_reset_postdata();
						}
					}
					else
					{
						$property_xml_url = (string)$property->url;

						$request = $property_xml_url;

						$property_file = $this->uploads_dir . "property.xml";

						$this->connect($request, $property_file);

						if ( file_exists($property_file) )
						{
							$property_xml = @simplexml_load_file($property_file);

							if ( $property_xml !== FALSE )
							{
								$property_xml->addChild('action', (string)$property->action);

								$this->properties[] = $property_xml;
							}
							else
							{
								//echo 'Failed to parse property XML';
							}

							unlink($property_file);
						}
						else
						{
							//echo 'File ' . $property_file . ' doesnt exist';
						}
					}
				}
			}
			else
			{
				//echo 'Failed to parse properties XML or no properties found';
			}

			unlink($properties_file);
		}
		else
		{
			//echo 'File ' . $properties_file . ' doesnt exist';
		}
	}

	public function parse()
	{
		if ( !empty($this->properties) )
		{
			$this->add_log("Parsing properties");
			
            $properties_imported = 0;
            $properties = array();

			foreach ( $this->properties as $property )
			{
            	$property_attributes = $property->attributes();
                
                // Only import UK residential sales (1), UK residential lettings (2), UK commercial (5), UK new homes (15)
                if ( 
                	isset($property_attributes['database']) 
                	&& 
                	(
                		(string)$property_attributes['database'] == '1' || 
                		(string)$property_attributes['database'] == '2' || 
                		(string)$property_attributes['database'] == '5' || 
                		(string)$property_attributes['database'] == '15'
                	) 
                )
                {
                    $properties[] = $property;
                }

            } // end foreach property

            $this->properties = $properties;
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

        do_action( "propertyhive_pre_import_properties_vebra_api_xml", $this->properties );
        $this->properties = apply_filters( "propertyhive_vebra_api_xml_properties_due_import", $this->properties );

		$this->add_log( 'Beginning to loop through ' . count($this->properties) . ' properties' );

		$property_row = 1;
		foreach ( $this->properties as $property )
		{
			$property_attributes = $property->attributes();

			$this->add_log( 'Importing property ' . $property_row .' with reference ' . (string)$property_attributes['id'], (string)$property_attributes['id'] );

			$inserted_updated = false;

			$create_date = '';
        	if ( isset($property->uploaded) && (string)$property->uploaded != '' )
        	{
        		$explode_create_date = explode("/", (string)$property->uploaded);
				if ( count($explode_create_date) == 3 )
				{
					$create_date = $explode_create_date[2] . '-' . $explode_create_date[1] . '-' . $explode_create_date[0] . ' 00:00:00';
				}
        	}

			$args = array(
	            'post_type' => 'property',
	            'posts_per_page' => 1,
	            'post_status' => 'any',
	            'meta_query' => array(
	            	array(
		            	'key' => $imported_ref_key,
		            	'value' => (string)$property_attributes['id']
		            )
	            )
	        );
	        $property_query = new WP_Query($args);
	        
	        if ($property_query->have_posts())
	        {
	        	$this->add_log( 'This property has been imported before. Updating it', (string)$property_attributes['id'] );

	        	// We've imported this property before
	            while ($property_query->have_posts())
	            {
	                $property_query->the_post();

	                $post_id = get_the_ID();

	                $my_post = array(
				    	'ID'          	 => $post_id,
				    	'post_date'      => $create_date,
						'post_date_gmt'  => $create_date,
				    	'post_title'     => wp_strip_all_tags( (string)$property->address->display ),
				    	'post_excerpt'   => (string)$property->description,
				    	'post_content' 	 => '',
				    	'post_status'    => 'publish',
				  	);

				 	// Update the post into the database
				    $post_id = wp_update_post( $my_post );

				    if ( is_wp_error( $post_id ) ) 
					{
						$this->add_error( 'ERROR: Failed to update post. The error was as follows: ' . $post_id->get_error_message(), (string)$property_attributes['id'] );
					}
					else
					{
						$inserted_updated = 'updated';
					}
	            }
	        }
	        else
	        {
	        	$this->add_log( 'This property hasn\'t been imported before. Inserting it', (string)$property_attributes['id'] );


	        	// We've not imported this property before
				$postdata = array(
					'post_date'      => $create_date,
					'post_date_gmt'  => $create_date,
					'post_excerpt'   => (string)$property->description,
					'post_content' 	 => '',
					'post_title'     => wp_strip_all_tags( (string)$property->address->display ),
					'post_status'    => 'publish',
					'post_type'      => 'property',
					'comment_status' => 'closed',
				);

				$post_id = wp_insert_post( $postdata, true );

				if ( is_wp_error( $post_id ) ) 
				{
					$this->add_error( 'Failed to insert post. The error was as follows: ' . $post_id->get_error_message(), (string)$property_attributes['id'] );
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
					((string)$property->address->display != '' || (string)$property->description != '')
				)
				{
					$my_post = array(
				    	'ID'          	 => $post_id,
				    	'post_date'      => $create_date,
						'post_date_gmt'  => $create_date,
				    	'post_title'     => htmlentities(mb_convert_encoding(wp_strip_all_tags( (string)$property->address->display ), 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
				    	'post_excerpt'   => htmlentities(mb_convert_encoding((string)$property->description, 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
				    	'post_content' 	 => '',
				    	'post_name' 	 => sanitize_title((string)$property->address->display),
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

				$this->add_log( 'Successfully ' . $inserted_updated . ' post. The post ID is ' . $post_id, (string)$property_attributes['id'] );

				update_post_meta( $post_id, $imported_ref_key, (string)$property_attributes['id'] );

				update_post_meta( $post_id, '_property_import_data', $property->asXML() );

				// Address
				update_post_meta( $post_id, '_reference_number', (string)$property->reference->agents );
				update_post_meta( $post_id, '_address_name_number', trim( ( isset($property->address->name) ) ? (string)$property->address->name : '' ) );
				update_post_meta( $post_id, '_address_street', ( ( isset($property->address->street) ) ? (string)$property->address->street : '' ) );
				update_post_meta( $post_id, '_address_two', ( ( isset($property->address->locality) ) ? (string)$property->address->locality : '' ) );
				update_post_meta( $post_id, '_address_three', ( ( isset($property->address->town) ) ? (string)$property->address->town : '' ) );
				update_post_meta( $post_id, '_address_four', ( ( isset($property->address->county) ) ? (string)$property->address->county : '' ) );
				update_post_meta( $post_id, '_address_postcode', ( ( isset($property->address->postcode) ) ? (string)$property->address->postcode : '' ) );

				$country = get_option( 'propertyhive_default_country', 'GB' );
				update_post_meta( $post_id, '_address_country', $country );

				// Check main address fields and see if this location exists as a taxonomy to try and assign properties to location
				$address_lines = array();
				if ( isset($property->address->locality) && trim((string)$property->address->locality) != '' )
				{
					$address_lines[] = $property->address->locality;
				}
				if ( isset($property->address->town) && trim((string)$property->address->town) != '' )
				{
					$address_lines[] = $property->address->town;
				}
				if ( isset($property->address->county) && trim((string)$property->address->county) != '' )
				{
					$address_lines[] = $property->address->county;
				}

				foreach ( $address_lines as $address_line )
				{
					$term = term_exists( trim($address_line), 'location');
					if ( $term !== 0 && $term !== null && isset($term['term_id']) )
					{
						wp_set_post_terms( $post_id, (int)$term['term_id'], 'location' );
						break;
					}
				}

				// Coordinates
				update_post_meta( $post_id, '_latitude', ( ( isset($property->latitude) ) ? (string)$property->latitude : '' ) );
				update_post_meta( $post_id, '_longitude', ( ( isset($property->longitude) ) ? (string)$property->longitude : '' ) );

				// Owner
				add_post_meta( $post_id, '_owner_contact_id', '', true );

				// Record Details
				add_post_meta( $post_id, '_negotiator_id', get_current_user_id(), true );
					
				$office_id = $primary_office_id;
				if ( isset($_POST['mapped_office'][(string)$property_attributes['branchid']]) && $_POST['mapped_office'][(string)$property_attributes['branchid']] != '' )
				{
					$office_id = $_POST['mapped_office'][(string)$property_attributes['branchid']];
				}
				elseif ( isset($options['offices']) && is_array($options['offices']) && !empty($options['offices']) )
				{
					foreach ( $options['offices'] as $ph_office_id => $branch_code )
					{
						$explode_branch_code = explode("-", $branch_code);
						if ( 
							( count($explode_branch_code) == 1 && $branch_code == (string)$property_attributes['branchid'] )
							|| 
							( count($explode_branch_code) == 2 && $explode_branch_code[0] == (string)$property_attributes['firmid'] && $explode_branch_code[1] == (string)$property_attributes['branchid'] )
						)
						{
							$office_id = $ph_office_id;
							break;
						}
					}
				}
				update_post_meta( $post_id, '_office_id', $office_id );

				// Residential Details
				$department = '';
				$prefix = '';
				switch ( (string)$property_attributes['database'] )
				{
					case "1":
					case "15": { $department = 'residential-sales'; break; }
					case "2": { $department = 'residential-lettings'; break; }
					case "5": { $department = 'commercial'; $prefix = 'commercial_'; break; }
				}
				update_post_meta( $post_id, '_department', $department );
				update_post_meta( $post_id, '_bedrooms', ( ( isset($property->bedrooms) ) ? (string)$property->bedrooms : '' ) );
				update_post_meta( $post_id, '_bathrooms', ( ( isset($property->bathrooms) ) ? (string)$property->bathrooms : '' ) );
				update_post_meta( $post_id, '_reception_rooms', ( ( isset($property->receptions) ) ? (string)$property->receptions : '' ) );

				if ( isset($_POST['mapped_property_type']) )
				{
					$mapping = $_POST['mapped_property_type'];
				}
				else
				{
					$mapping = isset($options['mappings'][$prefix . 'property_type']) ? $options['mappings'][$prefix . 'property_type'] : array();
				}

				wp_delete_object_term_relationships( $post_id, $prefix . 'property_type' );

				if ( isset($property->type) )
				{
					$type = $property->type;
					if ( is_array($type) )
					{
						$type = $type[0];
					}

					if ( (string)$type != '' )
					{
						if ( !empty($mapping) && isset($mapping[(string)$type]) )
						{
			                wp_set_object_terms( $post_id, (int)$mapping[(string)$type], $prefix . 'property_type' );
			            }
			            else
						{
							$this->add_log( 'Property received with a type (' . (string)$type . ') that is not mapped', (string)$property_attributes['id'] );

							$options = $this->add_missing_mapping( $mapping, $prefix . 'property_type', (string)$type, $import_id );
						}
					}
				}

				$price_attributes = $property->price->attributes();

				if ( (string)$property_attributes['database'] == '1' || (string)$property_attributes['database'] == '15' ) // UK Residential Sales
				{
					// Clean price
					$price = round(preg_replace("/[^0-9.]/", '', (string)$property->price));

					update_post_meta( $post_id, '_price', $price );
					update_post_meta( $post_id, '_price_actual', $price );
					update_post_meta( $post_id, '_poa', ( ( isset($price_attributes['display']) && (string)$price_attributes['display'] == 'no' ) ? 'yes' : '') );
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
					$mapping = array_change_key_case($mapping, CASE_LOWER);

					wp_delete_object_term_relationships( $post_id, 'price_qualifier' );

					if ( isset($price_attributes['qualifier']) && (string)$price_attributes['qualifier'] != '' )
					{
						if ( !empty($mapping) && isset($mapping[strtolower((string)$price_attributes['qualifier'])]) )
						{
			                wp_set_object_terms( $post_id, (int)$mapping[strtolower((string)$price_attributes['qualifier'])], 'price_qualifier' );
			            }
			            else
						{
							$this->add_log( 'Property received with a price qualifier (' . (string)$price_attributes['qualifier'] . ') that is not mapped', (string)$property_attributes['id'] );

							$options = $this->add_missing_mapping( $mapping, $prefix . 'price_qualifier', (string)$price_attributes['qualifier'], $import_id );
						}
					}
				}
				elseif ( (string)$property_attributes['database'] == '2' ) // UK Residential Lettings
				{
					// Clean price
					$price = round(preg_replace("/[^0-9.]/", '', (string)$property->price));

					update_post_meta( $post_id, '_rent', $price );

					$rent_frequency = 'pcm';
					$price_actual = $price;
					if ( isset($price_attributes['rent']) )
					{
						switch (strtolower((string)$price_attributes['rent']))
						{
							case "pcm": { $rent_frequency = 'pcm'; $price_actual = $price; break; }
							case "pw": { $rent_frequency = 'pw'; $price_actual = ($price * 52) / 12; break; }
							case "pq": { $rent_frequency = 'pq'; $price_actual = ($price * 4) / 12; break; }
							case "pa": { $rent_frequency = 'pa'; $price_actual = $price / 12; break; }
						}
					}
					update_post_meta( $post_id, '_rent_frequency', $rent_frequency );
					update_post_meta( $post_id, '_price_actual', $price_actual );
					update_post_meta( $post_id, '_currency', 'GBP' );
					
					update_post_meta( $post_id, '_poa', ( ( isset($price_attributes['display']) && (string)$price_attributes['display'] == 'no' ) ? 'yes' : '') );

					update_post_meta( $post_id, '_deposit', ( ( isset($property->let_bond) ) ? (string)$property->let_bond : '' ) );

					$available_date = '';
					if ( isset($property->available) && $property->available != '' && $property->available != '01/01/1900' )
					{
						$explode_available = explode("/", $property->available);
						if ( count($explode_available) == 3 )
						{
							$available_date = $explode_available[2] . '-' . $explode_available[1] . '-' . $explode_available[0];
						}
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
		                wp_set_object_terms( $post_id, (int)$mapping[(string)$property->furnished], 'furnished' );
		            }
				}
				elseif ( (string)$property_attributes['database'] == '5' ) // UK Commercial
				{
					update_post_meta( $post_id, '_for_sale', '' );
            		update_post_meta( $post_id, '_to_rent', '' );

            		if ( (string)$property->commercial->transaction == 'sale' )
	                {
	                    update_post_meta( $post_id, '_for_sale', 'yes' );

	                    update_post_meta( $post_id, '_commercial_price_currency', 'GBP' );

	                    $price = (string)$property->price;
	                    update_post_meta( $post_id, '_price_from', $price );
	                    update_post_meta( $post_id, '_price_to', $price );

	                    update_post_meta( $post_id, '_price_units', '' );

	                    update_post_meta( $post_id, '_price_poa', ( ( isset($price_attributes['display']) && (string)$price_attributes['display'] == 'no' ) ? 'yes' : '') );
	                }
	                if ( (string)$property->commercial->transaction == 'rental' )
	                {
		                update_post_meta( $post_id, '_to_rent', 'yes' );

	                    update_post_meta( $post_id, '_commercial_rent_currency', 'GBP' );

	                    $rent = (string)$property->price;
	                    update_post_meta( $post_id, '_rent_from', $rent );
	                    update_post_meta( $post_id, '_rent_to', $rent );

	                    $rent_frequency = 'pa';
						if ( isset($price_attributes['rent']) )
						{
							switch (strtolower((string)$price_attributes['rent']))
							{
								case "pcm": { $rent_frequency = 'pcm'; break; }
								case "pw": { $rent_frequency = 'pw'; break; }
								case "pq": { $rent_frequency = 'pq'; break; }
								case "pa": { $rent_frequency = 'pa'; break; }
							}
						}
	                    update_post_meta( $post_id, '_rent_units', $rent_frequency );

	                    update_post_meta( $post_id, '_rent_poa', ( ( isset($price_attributes['display']) && (string)$price_attributes['display'] == 'no' ) ? 'yes' : '') );
	                }

	                // Store price in common currency (GBP) used for ordering
		            $ph_countries = new PH_Countries();
		            $ph_countries->update_property_price_actual( $post_id );

		            // TO DO: PROPERTY TYPE

		            update_post_meta( $post_id, '_floor_area_from', '' );
		            update_post_meta( $post_id, '_floor_area_from_sqft', '' );
		            update_post_meta( $post_id, '_floor_area_to', '' );
		            update_post_meta( $post_id, '_floor_area_to_sqft', '' );
		            update_post_meta( $post_id, '_floor_area_units', '' );

		            if ( isset($property->area) && !empty($property->area) )
		            {
		            	foreach ( $property->area as $area )
		            	{
		            		$area_attributes = $area->attributes();

		            		if ( (string)$area->min != '' && (string)$area->min != '0' )
		            		{
		            			$size = preg_replace("/[^0-9.]/", '', (string)$area->min);
		            			update_post_meta( $post_id, '_floor_area_from', $size );
		            			update_post_meta( $post_id, '_floor_area_from_sqft', convert_size_to_sqft( $size, (string)$area_attributes['unit'] ) );
		            		}

		            		if ( (string)$area->max != '' && (string)$area->max != '0' )
		            		{
		            			$size = preg_replace("/[^0-9.]/", '', (string)$area->max);
		            			update_post_meta( $post_id, '_floor_area_to', $size );
		            			update_post_meta( $post_id, '_floor_area_to_sqft', convert_size_to_sqft( $size, (string)$area_attributes['unit'] ) );
		            		}

		            		update_post_meta( $post_id, '_floor_area_units', (string)$area_attributes['unit'] );

		            		break;
		            	}
		            }

		            update_post_meta( $post_id, '_site_area_from', '' );
		            update_post_meta( $post_id, '_site_area_from_sqft', '' );
		            update_post_meta( $post_id, '_site_area_to','' );
		            update_post_meta( $post_id, '_site_area_to_sqft', '' );
		            update_post_meta( $post_id, '_site_area_units', '' );

		            if ( isset($property->landarea) )
		            {
		            	$area_attributes = $property->landarea->attributes();

		            	update_post_meta( $post_id, '_site_area_from', (string)$property->landarea->area );
		            	update_post_meta( $post_id, '_site_area_from_sqft', convert_size_to_sqft( (string)$property->landarea->area, (string)$area_attributes['unit'] ) );
		            	update_post_meta( $post_id, '_site_area_to', (string)$property->landarea->area );
		            	update_post_meta( $post_id, '_site_area_to_sqft', convert_size_to_sqft( (string)$property->landarea->area, (string)$area_attributes['unit'] ) );
		            	update_post_meta( $post_id, '_site_area_units', (string)$area_attributes['unit'] );
		            }
				}

				// Marketing
				$on_market = '';
				if ( (string)$property->action != 'deleted' )
				{
					$on_market = 'yes';
				}
				if ( isset($options['dont_remove']) && $options['dont_remove'] == '1' )
				{
					// Keep it on the market if 'dont remove' is checked and it's already on the market
					$previous_on_market = get_post_meta( $post_id, '_on_market', TRUE );
					if ( $previous_on_market == 'yes' )
					{
						$on_market = 'yes';
					}
				}
				update_post_meta( $post_id, '_on_market', $on_market );
				update_post_meta( $post_id, '_featured', ( isset($property_attributes['featured']) && (string)$property_attributes['featured'] == '1' ) ? 'yes' : '' );

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
				if ( isset($property->web_status) && (string)$property->web_status != '' )
				{
					if ( !empty($mapping) && isset($mapping[(string)$property->web_status]) )
					{
		                wp_set_object_terms( $post_id, (int)$mapping[(string)$property->web_status], 'availability' );
		            }
		            else
					{
						$this->add_log( 'Property received with a status (' . (string)$property->web_status . ') that is not mapped', (string)$property_attributes['id'] );

						$options = $this->add_missing_mapping( $mapping, 'availability', (string)$property->web_status, $import_id );
					}
				}

	            // Features
				$features = array();
				if ( isset($property->bullets) )
				{
					foreach ( $property->bullets as $bullets )
					{
						if ( isset($bullets->bullet) )
						{
							foreach ( $bullets->bullet as $bullet )
							{
								$features[] = (string)$bullet;
							}
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

		        // Rooms / Descriptions
		        if ( (string)$property_attributes['database'] == '5' )
		        {
		        	$i = 0;
		        	if ( isset($property->paragraphs) )
					{
						foreach ( $property->paragraphs as $paragraphs )
						{
							if ( isset($paragraphs->paragraph) )
							{
								foreach ( $paragraphs->paragraph as $paragraph )
								{
									update_post_meta( $post_id, '_description_name_' . $i, (string)$paragraph->name );
						            update_post_meta( $post_id, '_description_' . $i, (string)$paragraph->text );

									++$i;
								}
							}
						}
					}

					update_post_meta( $post_id, '_descriptions', $i );
		        }
		        else
		        {
		        	$i = 0;
			        if ( isset($property->paragraphs) )
					{
						foreach ( $property->paragraphs as $paragraphs )
						{
							if ( isset($paragraphs->paragraph) )
							{
								foreach ( $paragraphs->paragraph as $paragraph )
								{
									update_post_meta( $post_id, '_room_name_' . $i, (string)$paragraph->name );
						            update_post_meta( $post_id, '_room_dimensions_' . $i, (string)$paragraph->dimensions->metric );
						            update_post_meta( $post_id, '_room_description_' . $i, (string)$paragraph->text );

									++$i;
								}
							}
						}
					}

					update_post_meta( $post_id, '_rooms', $i );
				}

	            // Media - Images
	            if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();
    				if (isset($property->files) && !empty($property->files))
	                {
	                    foreach ($property->files as $files)
	                    {
	                        if (!empty($files->file))
	                        {
	                            foreach ($files->file as $file)
	                            {
	                            	$file_attributes = $file->attributes();

									if ( 
										(string)$file_attributes['type'] == '0' &&
										(
											substr( strtolower((string)$file->url), 0, 2 ) == '//' || 
											substr( strtolower((string)$file->url), 0, 4 ) == 'http'
										)
									)
									{
										// This is a URL
										$url = (string)$file->url;

										$media_urls[] = array('url' => $url);
									}
								}
							}
						}
					}
					update_post_meta( $post_id, '_photo_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' photo URLs', (string)$property_attributes['id'] );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_photos', TRUE );
					if (isset($property->files) && !empty($property->files))
	                {
	                    foreach ($property->files as $files)
	                    {
	                        if (!empty($files->file))
	                        {
	                            foreach ($files->file as $file)
	                            {
	                            	$file_attributes = $file->attributes();

									if ( 
										(string)$file_attributes['type'] == '0' &&
										(
											substr( strtolower((string)$file->url), 0, 2 ) == '//' || 
											substr( strtolower((string)$file->url), 0, 4 ) == 'http'
										)
									)
									{
										// This is a URL
										$url = (string)$file->url;
										$description = (string)$file->name;
									    
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

										        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), (string)$property_attributes['id'] );
										    }
										    else
										    {
											    $id = media_handle_sideload( $file_array, $post_id, $description, array('post_title' => $filename) );

											    // Check for handle sideload errors.
											    if ( is_wp_error( $id ) ) 
											    {
											        @unlink( $file_array['tmp_name'] );
											        
											        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), (string)$property_attributes['id'] );
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

					$this->add_log( 'Imported ' . count($media_ids) . ' photos (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property_attributes['id'] );
				}

				// Media - Floorplans
				if ( get_option('propertyhive_floorplans_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();
    				if (isset($property->files) && !empty($property->files))
	                {
	                    foreach ($property->files as $files)
	                    {
	                        if (!empty($files->file))
	                        {
	                            foreach ($files->file as $file)
	                            {
	                            	$file_attributes = $file->attributes();

									if ( 
										(string)$file_attributes['type'] == '2' &&
										(
											substr( strtolower((string)$file->url), 0, 2 ) == '//' || 
											substr( strtolower((string)$file->url), 0, 4 ) == 'http'
										)
									)
									{
										// This is a URL
										$url = (string)$file->url;

										$media_urls[] = array('url' => $url);
									}
								}
							}
						}
					}
					update_post_meta( $post_id, '_floorplan_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' floorplan URLs', (string)$property_attributes['id'] );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_floorplans', TRUE );
					if (isset($property->files) && !empty($property->files))
	                {
	                    foreach ($property->files as $files)
	                    {
	                        if (!empty($files->file))
	                        {
	                            foreach ($files->file as $file)
	                            {
	                            	$file_attributes = $file->attributes();

									if ( 
										(string)$file_attributes['type'] == '2' &&
										(
											substr( strtolower((string)$file->url), 0, 2 ) == '//' || 
											substr( strtolower((string)$file->url), 0, 4 ) == 'http'
										)
									)
									{
										// This is a URL
										$url = (string)$file->url;
										$description = (string)$file->name;
									    
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

										        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), (string)$property_attributes['id'] );
										    }
										    else
										    {
											    $id = media_handle_sideload( $file_array, $post_id, $description, array('post_title' => $filename) );

											    // Check for handle sideload errors.
											    if ( is_wp_error( $id ) ) 
											    {
											        @unlink( $file_array['tmp_name'] );
											        
											        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), (string)$property_attributes['id'] );
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

					$this->add_log( 'Imported ' . count($media_ids) . ' floorplans (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property_attributes['id'] );
				}

				// Media - Brochures
				if ( get_option('propertyhive_brochures_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();
    				if (isset($property->files) && !empty($property->files))
	                {
	                    foreach ($property->files as $files)
	                    {
	                        if (!empty($files->file))
	                        {
	                            foreach ($files->file as $file)
	                            {
	                            	$file_attributes = $file->attributes();

									if ( 
										(string)$file_attributes['type'] == '7' &&
										(
											substr( strtolower((string)$file->url), 0, 2 ) == '//' || 
											substr( strtolower((string)$file->url), 0, 4 ) == 'http'
										)
									)
									{
										// This is a URL
										$url = (string)$file->url;

										$media_urls[] = array('url' => $url);
									}
								}
							}
						}
					}
					update_post_meta( $post_id, '_brochure_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' brochure URLs', (string)$property_attributes['id'] );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_brochures', TRUE );
					if (isset($property->files) && !empty($property->files))
	                {
	                    foreach ($property->files as $files)
	                    {
	                        if (!empty($files->file))
	                        {
	                            foreach ($files->file as $file)
	                            {
	                            	$file_attributes = $file->attributes();

									if ( 
										(string)$file_attributes['type'] == '7' &&
										(
											substr( strtolower((string)$file->url), 0, 2 ) == '//' || 
											substr( strtolower((string)$file->url), 0, 4 ) == 'http'
										)
									)
									{
										// This is a URL
										$url = (string)$file->url;
										$description = (string)$file->name;
									    
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

										        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), (string)$property_attributes['id'] );
										    }
										    else
										    {
											    $id = media_handle_sideload( $file_array, $post_id, $description, array('post_title' => $filename) );

											    // Check for handle sideload errors.
											    if ( is_wp_error( $id ) ) 
											    {
											        @unlink( $file_array['tmp_name'] );
											        
											        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), (string)$property_attributes['id'] );
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

					$this->add_log( 'Imported ' . count($media_ids) . ' brochures (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property_attributes['id'] );
				}

				// Media - EPCs
				if ( get_option('propertyhive_epcs_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();

    				if (isset($property->files) && !empty($property->files))
	                {
	                    foreach ($property->files as $files)
	                    {
	                        if (!empty($files->file))
	                        {
	                            foreach ($files->file as $file)
	                            {
	                            	$file_attributes = $file->attributes();

									if ( 
										(string)$file_attributes['type'] == '9' &&
										(
											substr( strtolower((string)$file->url), 0, 2 ) == '//' || 
											substr( strtolower((string)$file->url), 0, 4 ) == 'http'
										)
									)
									{
										// This is a URL
										$url = (string)$file->url;

										$media_urls[] = array('url' => $url);
									}
								}
							}
						}
					}

					update_post_meta( $post_id, '_epc_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' EPC URLs', (string)$property_attributes['id'] );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_epcs', TRUE );
					if (isset($property->files) && !empty($property->files))
	                {
	                    foreach ($property->files as $files)
	                    {
	                        if (!empty($files->file))
	                        {
	                            foreach ($files->file as $file)
	                            {
	                            	$file_attributes = $file->attributes();

									if ( 
										(string)$file_attributes['type'] == '9' &&
										(
											substr( strtolower((string)$file->url), 0, 2 ) == '//' || 
											substr( strtolower((string)$file->url), 0, 4 ) == 'http'
										)
									)
									{
										// This is a URL
										$url = (string)$file->url;
										$description = (string)$file->name;
									    
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

										        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), (string)$property_attributes['id'] );
										    }
										    else
										    {
											    $id = media_handle_sideload( $file_array, $post_id, $description, array('post_title' => $filename) );

											    // Check for handle sideload errors.
											    if ( is_wp_error( $id ) ) 
											    {
											        @unlink( $file_array['tmp_name'] );
											        
											        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), (string)$property_attributes['id'] );
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

					$this->add_log( 'Imported ' . count($media_ids) . ' EPCs (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property_attributes['id'] );
				}

				// Media - Virtual Tours
				$virtual_tours = array();
				if (isset($property->files) && !empty($property->files))
                {
                    foreach ($property->files as $files)
                    {
                        if (!empty($files->file))
                        {
                            foreach ($files->file as $file)
                            {
                            	$file_attributes = $file->attributes();

								if ( 
									(string)$file_attributes['type'] == '11' &&
									(
										substr( strtolower((string)$file->url), 0, 2 ) == '//' || 
										substr( strtolower((string)$file->url), 0, 4 ) == 'http'
									)
								)
								{
                            		$virtual_tours[] = (string)$file->url;
                            	}
                            }
                        }
                    }
                }

                update_post_meta( $post_id, '_virtual_tours', count($virtual_tours) );
                foreach ($virtual_tours as $i => $virtual_tour)
                {
                	update_post_meta( $post_id, '_virtual_tour_' . $i, (string)$virtual_tour );
                }

				$this->add_log( 'Imported ' . count($virtual_tours) . ' virtual tours', (string)$property_attributes['id'] );

				do_action( "propertyhive_property_imported_vebra_api_xml", $post_id, $property );

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
							$this->add_log( 'New meta data for ' . trim($key, '_') . ': ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), (string)$property_attributes['id'] );
						}
						elseif ( $metadata_before[$key] != $metadata_after[$key] )
						{
							$this->add_log( 'Updated ' . trim($key, '_') . '. Before: ' . ( ( is_array($metadata_before[$key]) ) ? implode(", ", $metadata_before[$key]) : $metadata_before[$key] ) . ', After: ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), (string)$property_attributes['id'] );
						}
					}

					$taxonomy_terms_after = array();
					foreach ( $taxonomy_names as $taxonomy_name )
					{
						$taxonomy_terms_after[$taxonomy_name] = wp_get_post_terms( $post_id, $taxonomy_name, array('fields' => 'ids') );
					}

					foreach ( $taxonomy_terms_after as $taxonomy_name => $ids)
					{
						if ( !isset($taxonomy_terms_before[$taxonomy_name]) )
						{
							$this->add_log( 'New taxonomy data for ' . $taxonomy_name . ': ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), (string)$property_attributes['id'] );
						}
						elseif ( $taxonomy_terms_before[$taxonomy_name] != $taxonomy_terms_after[$taxonomy_name] )
						{
							$this->add_log( 'Updated ' . $taxonomy_name . '. Before: ' . ( ( is_array($taxonomy_terms_before[$taxonomy_name]) ) ? implode(", ", $taxonomy_terms_before[$taxonomy_name]) : $taxonomy_terms_before[$taxonomy_name] ) . ', After: ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), (string)$property_attributes['id'] );
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

		do_action( "propertyhive_post_import_properties_vebra_api_xml" );

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
				$property_attributes = $property->attributes();
				
				$import_refs[] = (string)$property_attributes['id'];
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
						}
					}

					do_action( "propertyhive_property_removed_vebra_api_xml", $post->ID );
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

			if ( get_option( 'propertyhive_active_departments_commercial' ) == 'yes' )
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
                '0' => 'Sales: For Sale / Lettings: To Let',
                '1' => 'Sales: Under Offer / Lettings: Let',
                '2' => 'Sales: Sold / Lettings: Under Offer',
                '3' => 'Sales: SSTC / Lettings: Reserved',
                '4' => 'Sales: For Sale By Auction / Lettings: Let Agreed',
                '5' => 'Sales: Reserved',
                '6' => 'Sales: New Instruction',
                '7' => 'Sales: Just on Market',
                '8' => 'Sales: Price Reduction',
                '9' => 'Sales: Keen to Sell',
                '10' => 'Sales: No Chain',
                '11' => 'Sales: Vendor will pay stamp duty',
                '12' => 'Sales: Offers in the region of',
                '13' => 'Sales: Guide Price',
                '200' => 'Sales: For Sale / Lettings: To Let',
                '201' => 'Sales: Under Offer',
                '202' => 'Sales: Sold',
                '203' => 'Sales: SSTC',
                '214' => 'Lettings: Let',
                '255' => 'Not Marketed',
            );
        }
        if ($custom_field == 'sales_availability')
        {
            return array(
                '0' => 'For Sale',
                '1' => 'Under Offer',
                '2' => 'Sold',
                '3' => 'SSTC',
                '4' => 'For Sale By Auction',
                '5' => 'Reserved',
                '6' => 'New Instruction',
                '7' => 'Just on Market',
                '8' => 'Price Reduction',
                '9' => 'Keen to Sell',
                '10' => 'No Chain',
                '11' => 'Vendor will pay stamp duty',
                '12' => 'Offers in the region of',
                '13' => 'Guide Price',
                '200' => 'For Sale',
                '201' => 'Under Offer',
                '202' => 'Sold',
                '203' => 'SSTC',
                '255' => 'Not Marketed',
            );
        }
        if ($custom_field == 'lettings_availability')
        {
            return array(
                '0' => 'To Let',
                '1' => 'Let',
                '2' => 'Under Offer',
                '3' => 'Reserved',
                '4' => 'Let Agreed',
                '200' => 'To Let',
                '214' => 'Let',
                '255' => 'Not Marketed',
            );
        }
        if ($custom_field == 'property_type')
        {
        	return array(
                'House' => 'House',
                'Flat' => 'Flat',
            );
        }
        if ($custom_field == 'commercial_property_type')
        {
        	return array(
                'Commercial' => 'Commercial',
            );
        }
        if ($custom_field == 'price_qualifier')
        {
        	return array(
        		'Asking Price' => 'Asking Price',
        		'Auction Guide' => 'Auction Guide',
        		'Best And Final Offers' => 'Best And Final Offers',
        		'Best Offers Around' => 'Best Offers Around',
        		'Best Offers Over' => 'Best Offers Over',
        		'By Auction' => 'By Auction',
        		'By Public Auction' => 'By Public Auction',
        		'Circa' => 'Circa',
        		'Fixed Asking Price' => 'Fixed Asking Price',
        		'Fixed price' => 'Fixed price',
        		'Guide Price' => 'Guide Price',
        		'No Offers' => 'No Offers',
        		'O.I.R.O' => 'O.I.R.O',
        		'Offers Around' => 'Offers Around',
        		'Offers Based On' => 'Offers Based On',
        		'Offers In Excess Of' => 'Offers In Excess Of',
        		'Offers In The Region Of' => 'Offers In The Region Of',
        		'Offers Invited' => 'Offers Invited',
        		'Offers Over' => 'Offers Over',
        		'Open To Offers' => 'Open To Offers',
        		'Or Nearest Offer' => 'Or Nearest Offer',
        		'Part Exchange Considered' => 'Part Exchange Considered',
        		'POA' => 'POA',
        		'Price Guide' => 'Price Guide',
        		'Price On Application' => 'Price On Application',
        		'Prices From' => 'Prices From',
        	);
        }
        if ($custom_field == 'tenure')
        {
            return array(
                /*'1' => 'Freehold',
                '2' => 'Leasehold',
                '3' => 'Commonhold',
                '4' => 'Share of Freehold',
                '5' => 'Flying Freehold',
                '6' => 'Share Transfer',
                '7' => 'Unknown',*/
            );
        }
        if ($custom_field == 'furnished')
        {
            return array(
            	'0' => 'Furnished',
            	'1' => 'Part Furnished',
            	'2' => 'Un-Furnished',
            	'4' => 'Furnished / Un-Furnished',
            );
        }
    }

}

}