<?php
/**
 * Class for managing the import process of an Dezrez XML file
 *
 * @package WordPress
 */
if ( class_exists( 'PH_Property_Import_Process' ) ) {

class PH_Dezrez_XML_Import extends PH_Property_Import_Process {

	/**
	 * @var string
	 */
	private $target_file;

	/**
	 * @var string
	 */
	private $guid;

	/**
	 * @var array
	 */
	private $branch_ids_processed;

	public function __construct( $guid = '', $instance_id = '' ) 
	{
		$this->guid = $guid;
		$this->instance_id = $instance_id;

		if ( $this->instance_id != '' && isset($_GET['custom_property_import_cron']) )
	    {
	    	$current_user = wp_get_current_user();

	    	$this->add_log("Executed manually by " . ( ( isset($current_user->display_name) ) ? $current_user->display_name : '' ) );
	    }
	}

	public function set_target_xml($xml_file)
	{
		$this->target_file = $xml_file;
	}

	public function parse( $import_id = '' )
	{
		$xml = simplexml_load_file( $this->target_file );

		if ($xml !== FALSE)
		{
			$this->add_log("Parsing properties");
			
            $properties_imported = 0;

            $properties_xml_array = '';

            if (isset($xml->propertySearchSales->properties))
			{
				$this->add_log("Parsing sales properties");

				if (isset($xml->propertySearchSales->properties->property))
				{
					$properties_xml_array = $xml->propertySearchSales->properties->property;
				}
			}
            
			if (isset($xml->propertySearchLettings->properties))
			{
				$this->add_log("Parsing lettings properties");

				if (isset($xml->propertySearchLettings->properties->property))
				{
					$properties_xml_array = $xml->propertySearchLettings->properties->property;
				}
			}

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

			$this->add_log("Found " . count($properties_xml_array) . " properties in XML array ready for parsing");

			foreach ($properties_xml_array as $property)
			{
				$attributes = $property->attributes();
				$property_id = (string)$attributes['id'];

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

		                	$dezrez_last_updated = (string)$attributes['updated'];
		                	$explode_dezrez_last_updated = explode(" ", $dezrez_last_updated);

		                	$explode_dezrez_last_updated_date = explode("/", $explode_dezrez_last_updated[0]);

		                	$dezrez_last_updated = $explode_dezrez_last_updated_date[2] . '-' . $explode_dezrez_last_updated_date[1] . '-' . $explode_dezrez_last_updated_date[0] . ' ' . $explode_dezrez_last_updated[1];

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
					$property_url = 'http://www.dezrez.com/DRApp/DotNetSites/WebEngine/property/Property.aspx';
					$fields = array(
						'apiKey' => urlencode($options['api_key']),
						'eaid' => urlencode($options['eaid']),
						'sessionGUID' => urlencode($this->guid),
						'xslt' => urlencode('-1'),
						'pid' => $property_id
					);

					//url-ify the data for the POST
					$fields_string = '';
					foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
					$fields_string = rtrim($fields_string, '&');

					$contents = '';
					if ( ini_get('allow_url_fopen') )
					{
		    			$contents = file_get_contents($property_url . '?' . $fields_string);
		    		}
		    		elseif ( function_exists('curl_version') )
					{
						$curl = curl_init();
					    curl_setopt($curl, CURLOPT_URL, $property_url . '?' . $fields_string);
					    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
					    $contents = curl_exec($curl);
					    curl_close($curl);
		    		}
		    		else
		    		{
		    			die("Neither allow_url_fopen nor cURL is active on your server");
		    		}
					
					$property_xml = simplexml_load_string($contents);

					if ($property_xml !== FALSE)
					{
						if (isset($property_xml->propertyFullDetails->property))
						{
							$property_attributes = $property_xml->propertyFullDetails->property->attributes();
							if ((string)$property_attributes['deleted'] != 'true')
							{
								$property_xml->propertyFullDetails->property->addChild('summaryDescription', (string)$property->summaryDescription);

								$this->properties[] = $property_xml->propertyFullDetails->property;
							}
						}
					}
				}
				else
				{
					// Property not been updated.
					// Lets create our own XML so at least the property gets put into the $this->properties array
					$xml = '<?xml version="1.0" standalone="yes"?>
<response>
<propertyFullDetails>
<property id="' . $property_id . '" fake="yes">
<dummy></dummy>
</property>
</propertyFullDetails>
</response>';
					$property_xml = new SimpleXMLElement($xml);

					$this->properties[] = $property_xml->propertyFullDetails->property;
				}
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

        do_action( "propertyhive_pre_import_properties_dezrez_xml", $this->properties );
        $this->properties = apply_filters( "propertyhive_dezrez_xml_properties_due_import", $this->properties );

		$this->add_log( 'Beginning to loop through ' . count($this->properties) . ' properties' );

		$property_row = 1;
		foreach ( $this->properties as $property )
		{
			$property_attributes = $property->attributes();

			if ( !isset($property_attributes['fake']) )
			{
				$property_address = $property->address;
				$property_media = $property->media;
				$property_text = $property->text;

				$this->add_log( 'Importing property ' . $property_row . ' with reference ' . (string)$property_attributes['id'], (string)$property_attributes['id'] );

				$inserted_updated = false;

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
					    	'post_title'     => wp_strip_all_tags( (string)$property_address->useAddress ),
					    	'post_excerpt'   => (string)$property->summaryDescription,
					    	'post_content' 	 => '',
					    	'post_status'    => 'publish',
					  	);

					 	// Update the post into the database
					    $post_id = wp_update_post( $my_post );

					    if ( is_wp_error( $post_id ) ) 
						{
							$this->add_error( 'Failed to update post. The error was as follows: ' . $post_id->get_error_message(), (string)$property_attributes['id'] );
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
						'post_excerpt'   => (string)$property->summaryDescription,
						'post_content' 	 => '',
						'post_title'     => wp_strip_all_tags( (string)$property_address->useAddress ),
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
						((string)$property_address->useAddress != '' || (string)$property->summaryDescription != '')
					)
					{
						$my_post = array(
					    	'ID'          	 => $post_id,
					    	'post_title'     => htmlentities(mb_convert_encoding(wp_strip_all_tags( (string)$property_address->useAddress ), 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
					    	'post_excerpt'   => htmlentities(mb_convert_encoding((string)$property->summaryDescription, 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
					    	'post_content' 	 => '',
					    	'post_name' 	 => sanitize_title((string)$property_address->useAddress),
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

					// Address
					update_post_meta( $post_id, '_reference_number', '' );
					update_post_meta( $post_id, '_address_name_number', (string)$property_address->num );
					update_post_meta( $post_id, '_address_street', (string)$property_address->sa1 );
					update_post_meta( $post_id, '_address_two', (string)$property_address->sa2 );
					update_post_meta( $post_id, '_address_three', trim( (string)$property_address->town . ' ' . (string)$property_address->city ) );
					update_post_meta( $post_id, '_address_four', ( ( isset($property_address->county) ) ? (string)$property_address->county : '' ) );
					update_post_meta( $post_id, '_address_postcode', ( ( isset($property_address->postcode) ) ? (string)$property_address->postcode : '' ) );

					$country = 'GB';
					if ( isset($property_address->country) && (string)$property_address->country != '' && class_exists('PH_Countries') )
					{
						$ph_countries = new PH_Countries();
						foreach ( $ph_countries->countries as $country_code => $country_details )
						{
							if ( strtolower((string)$property_address->country) == strtolower($country_details['name']) )
							{
								$country = $country_code;
								break;
							}
						}
						if ( $country == '' )
						{
							switch (strtolower((string)$property_address->country))
							{
								case "uk": { $country = 'GB'; break; }
							}
						}
					}
					update_post_meta( $post_id, '_address_country', $country );

					// Check main address fields and see if this location exists as a taxonomy to try and assign properties to location
					$address_fields_to_check = apply_filters( 'propertyhive_dezrez_xml_address_fields_to_check', array('town', 'city', 'county') );
					$location_term_ids = array();

					foreach ( $address_fields_to_check as $address_field )
					{
						if ( isset($property_address->{$address_field}) && trim((string)$property_address->{$address_field}) != '' ) 
						{
							$term = term_exists( trim((string)$property_address->{$address_field}), 'location');
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
					update_post_meta( $post_id, '_latitude', ( ( isset($property_attributes['latitude']) ) ? (string)$property_attributes['latitude'] : '' ) );
					update_post_meta( $post_id, '_longitude', ( ( isset($property_attributes['longitude']) ) ? (string)$property_attributes['longitude'] : '' ) );

					// Owner
					add_post_meta( $post_id, '_owner_contact_id', '', true );

					// Record Details
					add_post_meta( $post_id, '_negotiator_id', get_current_user_id(), true );

					$office_id = $primary_office_id;
					if ( isset($_POST['mapped_office'][(string)$property_attributes['bid']]) && $_POST['mapped_office'][(string)$property_attributes['bid']] != '' )
					{
						$office_id = $_POST['mapped_office'][(string)$property_attributes['bid']];
					}
					elseif ( isset($options['offices']) && is_array($options['offices']) && !empty($options['offices']) )
					{
						foreach ( $options['offices'] as $ph_office_id => $branch_code )
						{
							if ( $branch_code == (string)$property_attributes['bid'] )
							{
								$office_id = $ph_office_id;
								break;
							}
						}
					}
					update_post_meta( $post_id, '_office_id', $office_id );

					$department = ((string)$property_attributes['sale'] != 'true') ? 'residential-lettings' : 'residential-sales';

					$commercial_property_types = array("70");
					$commercial_property_types = apply_filters( 'propertyhive_dezrez_xml_commercial_property_types', $commercial_property_types );

					if ( 
						isset($property_attributes['propertyType']) && 
						in_array((string)$property_attributes['propertyType'], $commercial_property_types) &&
						get_option( 'propertyhive_active_departments_commercial' ) == 'yes'
					)
					{
						$department = 'commercial';
			        }

					// Is the property portal add on activated
					if (class_exists('PH_Property_Portal'))
	        		{
	        			if ( 
	        				isset($branch_mappings[str_replace("residential-", "", $department)][(string)$property_attributes['bid']]) &&
	        				$branch_mappings[str_replace("residential-", "", $department)][(string)$property_attributes['bid']] != ''
	        			)
	        			{
	        				$explode_agent_branch = explode("|", $branch_mappings[str_replace("residential-", "", $department)][(string)$property_attributes['bid']]);
	        				update_post_meta( $post_id, '_agent_id', $explode_agent_branch[0] );
	        				update_post_meta( $post_id, '_branch_id', $explode_agent_branch[1] );

	        				$this->branch_ids_processed[] = $explode_agent_branch[1];
	        			}
	        		}

					// Residential Details
					update_post_meta( $post_id, '_department', $department );
					update_post_meta( $post_id, '_bedrooms', ( ( isset($property_attributes['bedrooms']) ) ? (string)$property_attributes['bedrooms'] : '' ) );
					update_post_meta( $post_id, '_bathrooms', ( ( isset($property_attributes['bathrooms']) ) ? (string)$property_attributes['bathrooms'] : '' ) );
					update_post_meta( $post_id, '_reception_rooms', ( ( isset($property_attributes['receptions']) ) ? (string)$property_attributes['receptions'] : '' ) );

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

					if ( isset($property_attributes['propertyType']) && (string)$property_attributes['propertyType'] != '' )
					{
						if ( !empty($mapping) && isset($mapping[(string)$property_attributes['propertyType']]) )
						{
				            wp_set_post_terms( $post_id, $mapping[(string)$property_attributes['propertyType']], 'property_type' );
			            }
			            else
						{
							$this->add_log( 'Property received with a type (' . (string)$property_attributes['propertyType'] . ') that is not mapped', (string)$property_attributes['id'] );

							$options = $this->add_missing_mapping( $mapping, 'property_type', (string)$property_attributes['propertyType'], $import_id );
						}
					}

		            $price = (string)$property_attributes['priceVal'];

		            $poa = '';
					if (
						isset($property_attributes['POA']) && 
						(string)$property_attributes['POA'] == 'true'
					)
					{
						$poa = 'yes';
					}
					update_post_meta( $post_id, '_poa', $poa );

					// Residential Sales Details
					if ( $department == 'residential-sales' || $department == 'residential-lettings' )
					{
						if ( (string)$property_attributes['sale'] == 'true' )
						{
							update_post_meta( $post_id, '_price', $price );
							update_post_meta( $post_id, '_price_actual', $price );

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
							if ( !empty($mapping) && isset($property_text->pricetext) && isset($mapping[(string)$property_text->pricetext]) )
							{
				                wp_set_post_terms( $post_id, $mapping[(string)$property_text->pricetext], 'price_qualifier' );
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
							if ( !empty($mapping) && isset($property_attributes['leaseType']) && isset($mapping[(string)$property_attributes['leaseType']]) )
							{
					            wp_set_post_terms( $post_id, $mapping[(string)$property_attributes['leaseType']], 'tenure' );
				            }
						}
						elseif ( (string)$property_attributes['sale'] != 'true' )
						{
							update_post_meta( $post_id, '_rent', $price );

							$rent_frequency = 'pcm';
							$price_actual = $price;

							if ( isset($property_attributes['rentalperiod']) )
							{
								switch ( (string)$property_attributes['rentalperiod'] )
								{	
									case "2": { break; } // per day
									case "3": { $rent_frequency = 'pw'; $price_actual = ($price * 52) / 12; break; } // per week
									case "4": { $rent_frequency = 'pcm'; $price_actual = $price; break; } // per month
									case "5": { $rent_frequency = 'pq'; $price_actual = ($price * 4) / 12; break; } // per quarter
									case "6": { $rent_frequency = 'pa'; $price_actual = $price / 12; break; } // per year
								}
							}

							update_post_meta( $post_id, '_rent_frequency', $rent_frequency );
							update_post_meta( $post_id, '_price_actual', $price_actual );

							update_post_meta( $post_id, '_currency', 'GBP' );

							update_post_meta( $post_id, '_deposit', '' );
		            		update_post_meta( $post_id, '_available_date', '' );

		            		// We don't receive furnished options in the feed
						}
					}
					elseif ( $department == 'commercial' )
					{
						update_post_meta( $post_id, '_for_sale', '' );
	            		update_post_meta( $post_id, '_to_rent', '' );

						if ( (string)$property_attributes['sale'] == 'true' )
		                {
		                    update_post_meta( $post_id, '_for_sale', 'yes' );

		                    update_post_meta( $post_id, '_commercial_price_currency', 'GBP' );

		                    update_post_meta( $post_id, '_price_from', $price );
		                    update_post_meta( $post_id, '_price_to', $price );

		                    update_post_meta( $post_id, '_price_units', '' );

		                    update_post_meta( $post_id, '_price_poa', $poa );
						}
						elseif ( (string)$property_attributes['sale'] != 'true' )
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

					// Marketing
					update_post_meta( $post_id, '_on_market', 'yes' );
					update_post_meta( $post_id, '_featured', ( isset($property_attributes['featured']) && (string)$property_attributes['featured'] == 'true' ) ? 'yes' : '' );

					// Availability
					if ( isset($_POST['mapped_availability']) )
					{
						$mapping = $_POST['mapped_availability'];
					}
					else
					{
						$mapping = isset($options['mappings']['availability']) ? $options['mappings']['availability'] : array();
					}

					$availability_term_id = '';
					if (
						((string)$property_attributes['sold'] == '0' || (string)$property_attributes['sold'] == '1')
						&&
						(string)$property_attributes['sale'] == 'true'
						&&
						isset($mapping['Available (Sales)'])
					)
					{
						$availability_term_id = $mapping['Available (Sales)'];
					}
					if (
						((string)$property_attributes['sold'] == '0' || (string)$property_attributes['sold'] == '1')
						&&
						(string)$property_attributes['sale'] != 'true'
						&&
						isset($mapping['Available (Lettings)'])
					)
					{
						$availability_term_id = $mapping['Available (Lettings)'];
					}
					if (
						(string)$property_attributes['UO_LA'] == 'true'
						&&
						(string)$property_attributes['sale'] == 'true'
						&&
						isset($mapping['Under Offer'])
					)
					{
						$availability_term_id = $mapping['Under Offer'];
					}
					if (
						(string)$property_attributes['UO_LA'] == 'true'
						&&
						(string)$property_attributes['sale'] != 'true'
						&&
						isset($mapping['Let Agreed'])
					)
					{
						$availability_term_id = $mapping['Let Agreed'];
					}
					if (
						(string)$property_attributes['sold'] == '2'
						&&
						(string)$property_attributes['sale'] == 'true'
						&&
						isset($mapping['Sold STC'])
					)
					{
						$availability_term_id = $mapping['Sold STC'];
					}
					if (
						(string)$property_attributes['sold'] == '2'
						&&
						(string)$property_attributes['sale'] != 'true'
						&&
						isset($mapping['Let Agreed'])
					)
					{
						$availability_term_id = $mapping['Let Agreed'];
					}

	        		wp_delete_object_term_relationships( $post_id, 'availability' );
					if ( $availability_term_id != '' )
					{
		                wp_set_post_terms( $post_id, (int)$availability_term_id, 'availability' );
		            }

		            // No features sent in feed


		            // Rooms
		            if ( $department != 'commercial' )
					{
			            $previous_room_count = get_post_meta( $post_id, '_rooms', TRUE );
				        $new_room_count = 0;
			            if (
		                	isset($property_text->areas)
		                )
						{
							foreach ($property_text->areas->area as $xml_area)
							{
								if (isset($xml_area->feature))
								{
									foreach ($xml_area->feature as $xml_feature)
									{
										update_post_meta( $post_id, '_room_name_' . $new_room_count, (string)$xml_feature->heading );
							            update_post_meta( $post_id, '_room_dimensions_' . $new_room_count, '' );
							            update_post_meta( $post_id, '_room_description_' . $new_room_count, (string)$xml_feature->description );

								        ++$new_room_count;
									}
								}
							}
						}
						update_post_meta( $post_id, '_rooms', $new_room_count );
					}
					else
					{
						$previous_room_count = get_post_meta( $post_id, '_descriptions', TRUE );
				        $new_room_count = 0;
			            if (
		                	isset($property_text->areas)
		                )
						{
							foreach ($property_text->areas->area as $xml_area)
							{
								if (isset($xml_area->feature))
								{
									foreach ($xml_area->feature as $xml_feature)
									{
										update_post_meta( $post_id, '_description_name_' . $new_room_count, (string)$xml_feature->heading );
							            update_post_meta( $post_id, '_description_' . $new_room_count, (string)$xml_feature->description );

								        ++$new_room_count;
									}
								}
							}
						}
						update_post_meta( $post_id, '_descriptions', $new_room_count );
					}

					// Media - Images
					if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
        			{
        				$media_urls = array();
        				if ( isset($property_media->picture) && !empty($property_media->picture) )
						{
							foreach ( $property_media->picture as $picture )
							{
								$picture_attributes = $picture->attributes();

								if ( 
									trim((string)$picture) != '' &&
									((string)$picture_attributes['categoryID'] == '1' || (string)$picture_attributes['categoryID'] == '2')
								)
								{
									if ( 
										substr( strtolower((string)$picture), 0, 2 ) == '//' || 
										substr( strtolower((string)$picture), 0, 4 ) == 'http'
									)
									{
										// This is a URL
										$url = (string)$picture . '&width=';
										$url .= apply_filters( 'propertyhive_dezrez_xml_image_width', '2048' );

										$media_urls[] = array('url' => $url);
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
						if ( isset($property_media->picture) && !empty($property_media->picture) )
						{
							foreach ( $property_media->picture as $picture )
							{
								$picture_attributes = $picture->attributes();

								if ( 
									trim((string)$picture) != '' &&
									((string)$picture_attributes['categoryID'] == '1' || (string)$picture_attributes['categoryID'] == '2')
								)
								{
									if ( 
										substr( strtolower((string)$picture), 0, 2 ) == '//' || 
										substr( strtolower((string)$picture), 0, 4 ) == 'http'
									)
									{
										// This is a URL
										$url = (string)$picture . '&width=';
										$url .= apply_filters( 'propertyhive_dezrez_xml_image_width', '2048' );
										
										$meta_url = $url . '&updated=' . ( ( isset($picture_attributes['updated']) && (string)$picture_attributes['updated'] != '' ) ? (string)$picture_attributes['updated'] : '' );
										$description = ( ( isset($picture_attributes['caption']) && (string)$picture_attributes['caption'] != '' ) ? (string)$picture_attributes['caption'] : '' );
									    
										$filename = basename( $url );

										// Check, based on the URL, whether we have previously imported this media
										$imported_previously = false;
										$imported_previously_id = '';
										if ( is_array($previous_media_ids) && !empty($previous_media_ids) )
										{
											foreach ( $previous_media_ids as $previous_media_id )
											{
												if ( get_post_meta( $previous_media_id, '_imported_url', TRUE ) == $meta_url )
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

										    $name = (string)$property_attributes['id'] . '_' . (string)$picture_attributes['id'] . '.jpg';

										    $file_array = array(
										        'name' => $name,
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
											    $id = media_handle_sideload( $file_array, $post_id, $description );

											    // Check for handle sideload errors.
											    if ( is_wp_error( $id ) ) 
											    {
											        @unlink( $file_array['tmp_name'] );
											        
											        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), (string)$property_attributes['id'] );
											    }
											    else
											    {
											    	$media_ids[] = $id;

											    	update_post_meta( $id, '_imported_url', $meta_url);

											    	++$new;
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
        				if ( isset($property_media->picture) && !empty($property_media->picture) )
						{
							foreach ( $property_media->picture as $picture )
							{
								$picture_attributes = $picture->attributes();

								if ( 
									trim((string)$picture) != '' &&
									((string)$picture_attributes['categoryID'] == '3')
								)
								{
									if ( 
										substr( strtolower((string)$picture), 0, 2 ) == '//' || 
										substr( strtolower((string)$picture), 0, 4 ) == 'http'
									)
									{
										// This is a URL
										$url = (string)$picture;
										if ( strpos(strtolower($url), 'width=') === FALSE )
										{
											// If no width passed then set to 2048
											$url .= '&width=';
											$url .= apply_filters( 'propertyhive_dezrez_xml_floorplan_width', '2048' );
										}

										$media_urls[] = array('url' => $url);
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
						if ( isset($property_media->picture) && !empty($property_media->picture) )
						{
							foreach ( $property_media->picture as $picture )
							{
								$picture_attributes = $picture->attributes();

								if ( 
									trim((string)$picture) != '' &&
									((string)$picture_attributes['categoryID'] == '3')
								)
								{
									if ( 
										substr( strtolower((string)$picture), 0, 2 ) == '//' || 
										substr( strtolower((string)$picture), 0, 4 ) == 'http'
									)
									{
										// This is a URL
										$url = (string)$picture;
										if ( strpos(strtolower($url), 'width=') === FALSE )
										{
											// If no width passed then set to 2048
											$url .= '&width=';
											$url .= apply_filters( 'propertyhive_dezrez_xml_floorplan_width', '2048' );
										}
										$meta_url = $url . '&updated=' . ( ( isset($picture_attributes['updated']) && (string)$picture_attributes['updated'] != '' ) ? (string)$picture_attributes['updated'] : '' );
										$description = ( ( isset($picture_attributes['caption']) && (string)$picture_attributes['caption'] != '' ) ? (string)$picture_attributes['caption'] : '' );
									    
										$filename = basename( $url );

										// Check, based on the URL, whether we have previously imported this media
										$imported_previously = false;
										$imported_previously_id = '';
										if ( is_array($previous_media_ids) && !empty($previous_media_ids) )
										{
											foreach ( $previous_media_ids as $previous_media_id )
											{
												if ( get_post_meta( $previous_media_id, '_imported_url', TRUE ) == $meta_url )
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

										    $name = (string)$property_attributes['id'] . '_' . (string)$picture_attributes['id'] . '.jpg';

										    $file_array = array(
										        'name' => $name,
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
											    $id = media_handle_sideload( $file_array, $post_id, $description );
											    
											    // Check for handle sideload errors.
											    if ( is_wp_error( $id ) ) 
											    {
											        @unlink( $file_array['tmp_name'] );
											        
											        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), (string)$property_attributes['id'] );
											    }
											    else
											    {
											    	$media_ids[] = $id;

											    	update_post_meta( $id, '_imported_url', $meta_url);

											    	++$new;
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
        				if ( isset($property_media->document) && !empty($property_media->document) )
						{
							foreach ( $property_media->document as $document )
							{
								$document_attributes = $document->attributes();

								if ( 
									trim((string)$document) != '' &&
									((string)$document_attributes['category'] == 'brochure') &&
									((string)$document_attributes['source'] == 'document-location-url')
								)
								{
									if ( 
										substr( strtolower((string)$document), 0, 2 ) == '//' || 
										substr( strtolower((string)$document), 0, 4 ) == 'http'
									)
									{
										// This is a URL
										$url = (string)$document;

										$media_urls[] = array('url' => $url);
									}
								}
							}
						}
						update_post_meta( $post_id, '_brochure_urls', $media_urls );

						$this->add_log( 'Imported ' . count($media_urls) . ' brochure URLs', (string)$property_attributes['id'] );
        			}
        			else
        			{
						// Need to work out how to not download url if it links to an invalid page
						$media_ids = array();
						$new = 0;
						$existing = 0;
						$deleted = 0;
						$previous_media_ids = get_post_meta( $post_id, '_brochures', TRUE );
						if ( isset($property_media->document) && !empty($property_media->document) )
						{
							foreach ( $property_media->document as $document )
							{
								$document_attributes = $document->attributes();

								if ( 
									trim((string)$document) != '' &&
									((string)$document_attributes['category'] == 'brochure') &&
									((string)$document_attributes['source'] == 'document-location-url')
								)
								{
									if ( 
										substr( strtolower((string)$document), 0, 2 ) == '//' || 
										substr( strtolower((string)$document), 0, 4 ) == 'http'
									)
									{
										// This is a URL
										$url = (string)$document;
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

										    $name = (string)$property_attributes['id'] . '_brochure_' . count($media_ids) . '.pdf';

										    $file_array = array(
										        'name' => $name,
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
											    $id = media_handle_sideload( $file_array, $post_id, $description );
											    
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

        				if ( isset($property_media->picture) && !empty($property_media->picture) )
						{
							foreach ( $property_media->picture as $picture )
							{
								$picture_attributes = $picture->attributes();

								if ( 
									trim((string)$picture) != '' &&
									((string)$picture_attributes['category'] == 'EER' || (string)$picture_attributes['category'] == 'EIR')
								)
								{
									if ( 
										substr( strtolower((string)$picture), 0, 2 ) == '//' || 
										substr( strtolower((string)$picture), 0, 4 ) == 'http'
									)
									{
										// This is a URL
										$url = (string)$picture;
										if ( strpos(strtolower($url), 'width=') === FALSE )
										{
											// If no width passed then set to 500
											$url .= '&width=';
											$url .= apply_filters( 'propertyhive_dezrez_xml_epc_width', '500' );
										}

										$media_urls[] = array('url' => $url);
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
						if ( isset($property_media->picture) && !empty($property_media->picture) )
						{
							foreach ( $property_media->picture as $picture )
							{
								$picture_attributes = $picture->attributes();

								if ( 
									trim((string)$picture) != '' &&
									((string)$picture_attributes['category'] == 'EER' || (string)$picture_attributes['category'] == 'EIR')
								)
								{
									if ( 
										substr( strtolower((string)$picture), 0, 2 ) == '//' || 
										substr( strtolower((string)$picture), 0, 4 ) == 'http'
									)
									{
										// This is a URL
										$url = (string)$picture;
										if ( strpos(strtolower($url), 'width=') === FALSE )
										{
											// If no width passed then set to 500
											$url .= '&width=';
											$url .= apply_filters( 'propertyhive_dezrez_xml_epc_width', '500' );
										}
										$description = ( ( isset($picture_attributes['caption']) && (string)$picture_attributes['caption'] != '' ) ? (string)$picture_attributes['caption'] : '' );
									    
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

										    $name = (string)$property_attributes['id'] . '_' . (string)$picture_attributes['id'] . '.jpg';

										    $file_array = array(
										        'name' => $name,
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
											    $id = media_handle_sideload( $file_array, $post_id, $description );
											    
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
					if ( isset($property_media->virtualtour) && !empty($property_media->virtualtour) )
	                {
	                	if ( !is_array($property_media->virtualtour) )
	                	{
	                		// If theres only one it's treated as a string so turn into an array
	                		$property_media->virtualtour = array( (string)$property_media->virtualtour );
	                	}
	                	if ( !empty($property_media->virtualtour) )
	                	{
		                    foreach ( $property_media->virtualtour as $virtualtour )
		                    {
		                        $virtual_tours[] = (string)$virtualtour;
		                    }
		                }
	                }

	                update_post_meta( $post_id, '_virtual_tours', count($virtual_tours) );
	                foreach ( $virtual_tours as $i => $virtual_tour )
	                {
	                	update_post_meta( $post_id, '_virtual_tour_' . $i, (string)$virtual_tour );
	                }

					$this->add_log( 'Imported ' . count($virtual_tours) . ' virtual tours', (string)$property_attributes['id'] );

					do_action( "propertyhive_property_imported_dezrez_xml", $post_id, $property );

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
						$taxonomy_names = get_post_taxonomies( $post_id );
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
			}
			else
			{
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
		        	// We've imported this property before
		            while ($property_query->have_posts())
		            {
		                $property_query->the_post();

		                $post_id = get_the_ID();

		                update_post_meta( $post_id, '_on_market', 'yes' );
		            }
		        }
			}

		} // end foreach property

		do_action( "propertyhive_post_import_properties_dezrez_xml" );

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

						do_action( "propertyhive_property_removed_dezrez_xml", $post->ID );
					}
				}
				wp_reset_postdata();

				unset($import_refs);
			}
		}
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
                'Available (Sales)' => 'Available (Sales)',
                'Available (Lettings)' => 'Available (Lettings)',
                'Under Offer' => 'Under Offer',
                'Sold STC' => 'Sold STC',
                'Let Agreed' => 'Let Agreed',
            );
        }
        if ($custom_field == 'property_type')
        {
        	return array(
                10 => 'Apartment',
				58 => 'Apartment (Low Density)',
				59 => 'Apartment (Studio)',
				57 => 'Building Plot',
				60 => 'Business',
				56 => 'Cluster',
				70 => 'Commercial',
				61 => 'Corner Townhouse',',',
				40 => 'Detached (Barn Conversion)',
				15 => 'Detached (Bungalow)',
				39 => 'Detached (Chalet)',
				23 => 'Detached (Cottage)',
				30 => 'Detached (Country House)',
				5 => 'Detached (House)',
				29 => 'Detached (Town House)',
				52 => 'Duplex Apartment',
				33 => 'East Wing (Country House)',
				17 => 'End Link (Bungalow)',
				7 => 'End Link (House)',
				12 => 'End Terrace (Bungalow)',
				36 => 'End Terrace (Chalet)',
				20 => 'End Terrace (Cottage)',
				2 => 'End Terrace (House)',
				26 => 'End Terrace (Town House)',
				47 => 'First Floor Converted (Flat)',
				44 => 'First Floor Purpose Built (Flat)',
				50 => 'First &amp; Second Floor (Maisonette)',
				9 => 'Flat',
				49 => 'Ground &amp; First Floor (Maisonette)',
				46 => 'Ground Floor Converted (Flat)',
				43 => 'Ground Floor Purpose Built (Flat)',
				66 => 'Link Detached',
				53 => 'Mansion',
				68 => 'Maisonette',
				42 => 'Mews Style (Barn Conversion)',
				18 => 'Mid Link (Bungalow)',
				8 => 'Mid Link (House)',
				13 => 'Mid Terrace (Bungalow)',
				37 => 'Mid Terrace (Chalet)',
				21 => 'Mid Terrace (Cottage)',
				3 => 'Mid Terrace (House)',
				27 => 'Mid Terrace (Town House)',
				31 => 'North Wing (Country House)',
				51 => 'Penthouse Apartment',
				54 => 'Q-Type',
				41 => 'Remote Detached (Barn Conversion)',
				16 => 'Remote Detached (Bungalow)',
				24 => 'Remote Detached(Cottage)',
				6 => 'Remote Detached (House)',
				48 => 'Second Floor Converted (Flat)',
				45 => 'Second Floor Purpose Built (Flat)',
				14 => 'Semi-Detached (Bungalow)',
				38 => 'Semi-Detached(Chalet)',
				22 => 'Semi-Detached(Cottage)',
				4 => 'Semi-Detached (House)',
				28 => 'Semi-Detached (Town House)',
				69 => 'Shell',
				32 => 'South Wing (Country House)',
				67 => 'Studio',
				11 => 'Terraced (Bungalow)',
				35 => 'Terraced (Chalet)',
				19 => 'Terraced (Cottage)',
				1 => 'Terraced (House)',
				25 => 'Terraced (Town House)',
				55 => 'T-Type',
				65 => 'Village House',
				62 => 'Villa (Detached)',
				63 => 'Villa (Link-Detached)',
				64 => 'Villa (Semi-Detached)',
				34 => 'West Wing (Country House)',
				71 => 'Retirement Flat',
				72 => 'Bedsit',
				73 => 'Park Home/Mobile Home'
            );
        }
        if ($custom_field == 'price_qualifier')
        {
        	return array(
        		'Guide Price' => 'Guide Price',
                'Fixed Price' => 'Fixed Price',
                'Offers in Excess of' => 'Offers in Excess of',
                'OIRO' => 'OIRO',
                'Sale by Tender' => 'Sale by Tender',
                'From' => 'From',
                'Shared Ownership' => 'Shared Ownership',
                'Offers Over' => 'Offers Over',
                'Part Buy Part Rent' => 'Part Buy Part Rent',
                'Shared Equity' => 'Shared Equity',
        	);
        }
        if ($custom_field == 'tenure')
        {
            return array(
                '1' => 'Not Applicable',
                '3' => 'Freehold',
                '5' => 'Freehold (to be confirmed)',
                '2' => 'Leasehold',
                '4' => 'Leasehold (to be confirmed)',
                '6' => 'To be Advised',
                '7' => 'Share of Leasehold',
                '8' => 'Share of Freehold',
                '9' => 'Flying Freehold',
                '11' => 'Leasehold (Share of Freehold)',
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