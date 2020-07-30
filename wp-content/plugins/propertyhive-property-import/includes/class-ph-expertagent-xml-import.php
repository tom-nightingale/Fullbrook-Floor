<?php
/**
 * Class for managing the import process of an ExpertAgent XML file
 *
 * @package WordPress
 */
if ( class_exists( 'PH_Property_Import_Process' ) ) {

class PH_ExpertAgent_XML_Import extends PH_Property_Import_Process {

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

		$departments_to_import = array( 'sales', 'lettings', 'commercial' );
		$departments_to_import = apply_filters( 'propertyhive_expertagent_departments_to_import', $departments_to_import );

		if ($xml !== FALSE)
		{
			$this->add_log("Parsing properties");
			
            $properties_imported = 0;
            
			foreach ($xml->branches as $branches)
			{
			    foreach ($branches->branch as $branch)
                {
                	$branch_attributes = $branch->attributes();

                	$branch_name = (string)$branch_attributes['name'];

                    foreach ($branch->properties as $properties)
                    {
                        foreach ($properties->property as $property)
                        {
                        	$property_attributes = $property->attributes();

                        	$department = (string)$property->department;

                        	$ok_to_import = false;
                        	foreach ( $departments_to_import as $department_to_import )
                        	{
                        		if ( strpos(strtolower($department), $department_to_import) !== FALSE )
                        		{
                        			$ok_to_import = true;
                        			break;
                        		}
                        	}

                        	if ( $ok_to_import )
                            { 
                            	// Add branch to the property object so we can access it later.
	                        	$property->addChild('branch', htmlentities($branch_name));

	                        	// Add branch to the property object so we can access it later.
	                        	$property->addChild('reference', apply_filters( 'propertyhive_expertagent_unique_identifier_field', $property_attributes['reference'], $property ));

	                            $this->properties[] = $property;
	                        }

                        } // end foreach property
                    } // end foreach properties
                } // end foreach branch
            } // end foreach branches
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

        do_action( "propertyhive_pre_import_properties_expertagent_xml", $this->properties );
        $this->properties = apply_filters( "propertyhive_expertagent_xml_properties_due_import", $this->properties );

		$this->add_log( 'Beginning to loop through ' . count($this->properties) . ' properties' );

		$property_row = 1;
		foreach ( $this->properties as $property )
		{
			$this->add_log( 'Importing property ' . $property_row . ' with reference ' . (string)$property->reference, (string)$property->reference );

			$inserted_updated = false;

			$args = array(
	            'post_type' => 'property',
	            'posts_per_page' => 1,
	            'post_status' => 'any',
	            'meta_query' => array(
	            	array(
		            	'key' => $imported_ref_key,
		            	'value' => (string)$property->reference
		            )
	            )
	        );
	        $property_query = new WP_Query($args);
	        
	        if ($property_query->have_posts())
	        {
	        	$this->add_log( 'This property has been imported before. Updating it', (string)$property->reference );

	        	// We've imported this property before
	            while ($property_query->have_posts())
	            {
	                $property_query->the_post();

	                $post_id = get_the_ID();

	                $my_post = array(
				    	'ID'          	 => $post_id,
				    	'post_title'     => wp_strip_all_tags( (string)$property->advert_heading ),
				    	'post_excerpt'   => (string)$property->main_advert,
				    	'post_content' 	 => '',
				    	'post_status'    => 'publish',
				  	);

				 	// Update the post into the database
				    $post_id = wp_update_post( $my_post );

				    if ( is_wp_error( $post_id ) ) 
					{
						$this->add_error( 'Failed to update post. The error was as follows: ' . $post_id->get_error_message(), (string)$property->reference );
					}
					else
					{
						$inserted_updated = 'updated';
					}
	            }
	        }
	        else
	        {
	        	$this->add_log( 'This property hasn\'t been imported before. Inserting it', (string)$property->reference );

	        	// We've not imported this property before
				$postdata = array(
					'post_excerpt'   => (string)$property->main_advert,
					'post_content' 	 => '',
					'post_title'     => wp_strip_all_tags( (string)$property->advert_heading ),
					'post_status'    => 'publish',
					'post_type'      => 'property',
					'comment_status' => 'closed',
				);

				$post_id = wp_insert_post( $postdata, true );

				if ( is_wp_error( $post_id ) ) 
				{
					$this->add_error( 'Failed to insert post. The error was as follows: ' . $post_id->get_error_message(), (string)$property->reference );
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
					((string)$property->advert_heading != '' || (string)$property->main_advert != '')
				)
				{
					$my_post = array(
				    	'ID'          	 => $post_id,
				    	'post_title'     => htmlentities(mb_convert_encoding(wp_strip_all_tags( (string)$property->advert_heading ), 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
				    	'post_excerpt'   => htmlentities(mb_convert_encoding((string)$property->main_advert, 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
				    	'post_content' 	 => '',
				    	'post_name' 	 => sanitize_title((string)$property->advert_heading),
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

				$this->add_log( 'Successfully ' . $inserted_updated . ' post. The post ID is ' . $post_id, (string)$property->reference );

				update_post_meta( $post_id, $imported_ref_key, (string)$property->reference );

				// Address
				update_post_meta( $post_id, '_reference_number', (string)$property->property_reference );
				update_post_meta( $post_id, '_address_name_number', ( ( isset($property->house_number) ) ? (string)$property->house_number : '' ) );
				update_post_meta( $post_id, '_address_street', ( ( isset($property->street) ) ? (string)$property->street : '' ) );
				update_post_meta( $post_id, '_address_two', ( ( isset($property->district) ) ? (string)$property->district : '' ) );
				update_post_meta( $post_id, '_address_three', ( ( isset($property->town) ) ? (string)$property->town : '' ) );
				update_post_meta( $post_id, '_address_four', ( ( isset($property->county) ) ? (string)$property->county : '' ) );
				update_post_meta( $post_id, '_address_postcode', ( ( isset($property->postcode) ) ? (string)$property->postcode : '' ) );

				$country = 'GB';
				if ( isset($property->country) && (string)$property->country != '' && class_exists('PH_Countries') )
				{
					$ph_countries = new PH_Countries();
					foreach ( $ph_countries->countries as $country_code => $country_details )
					{
						if ( strtolower((string)$property->country) == strtolower($country_details['name']) || ( strtolower((string)$property->country) == strtolower($country_code) ) )
						{
							$country = $country_code;
							break;
						}
					}
					if ( $country == '' )
					{
						switch (strtolower((string)$property->country))
						{
							case "uk": { $country = 'GB'; break; }
						}
					}
				}
				update_post_meta( $post_id, '_address_country', $country );

				// Check main address fields and see if this location exists as a taxonomy to try and assign properties to location
				$address_fields_to_check = apply_filters( 'propertyhive_expertagent_xml_address_fields_to_check', array('district', 'town', 'county') );
				foreach ( $address_fields_to_check as $address_field )
				{
					if ( isset($property->{$address_field}) && trim((string)$property->{$address_field}) != '' ) 
					{
						$term = term_exists( trim((string)$property->{$address_field}), 'location');
						if ( $term !== 0 && $term !== null && isset($term['term_id']) )
						{
							wp_set_post_terms( $post_id, (int)$term['term_id'], 'location' );
							break;
						}
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
				if ( isset($_POST['mapped_office'][(string)$property->branch]) && $_POST['mapped_office'][(string)$property->branch] != '' )
				{
					$office_id = $_POST['mapped_office'][(string)$property->branch];
				}
				elseif ( isset($options['offices']) && is_array($options['offices']) && !empty($options['offices']) )
				{
					foreach ( $options['offices'] as $ph_office_id => $branch_code )
					{
						if ( html_entity_decode($branch_code) == html_entity_decode((string)$property->branch) )
						{
							$office_id = $ph_office_id;
							break;
						}
					}
				}
				update_post_meta( $post_id, '_office_id', $office_id );

				// Residential Details
				$department = ( ( strpos(strtolower((string)$property->department), 'lettings') !== FALSE ) ? 'residential-lettings' : 'residential-sales' );
				if ( ( strpos(strtolower((string)$property->department), 'commercial') !== FALSE ) )
				{
					$department = 'commercial';
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

	        	// Property Type
	        	$prefix = '';
	        	$expert_agent_type = (string)$property->property_type . ' - ' . (string)$property->property_style;
				if ( ( strpos(strtolower((string)$property->department), 'commercial') !== FALSE ) )
				{
					$prefix = 'commercial_';
					$expert_agent_type = (string)$property->commercial_type;
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

	            if ( $expert_agent_type != '' )
	            {
					if ( !empty($mapping) && isset($mapping[$expert_agent_type]) )
					{
			            wp_set_post_terms( $post_id, $mapping[$expert_agent_type], $prefix . 'property_type' );
		            }
		            else
					{
						$this->add_log( 'Property received with a type (' . $expert_agent_type . ') that is not mapped', (string)$property->reference );

						$options = $this->add_missing_mapping( $mapping, $prefix . 'property_type', $expert_agent_type, $import_id );
					}
				}

				// Clean price
				$price = round(preg_replace("/[^0-9.]/", '', (string)$property->numeric_price));

	        	if ( $department != 'commercial' )
	        	{
	        		// Residential
					update_post_meta( $post_id, '_bedrooms', ( ( isset($property->bedrooms) ) ? (string)$property->bedrooms : '' ) );
					update_post_meta( $post_id, '_bathrooms', ( ( isset($property->bathrooms) ) ? (string)$property->bathrooms : '' ) );
					update_post_meta( $post_id, '_reception_rooms', ( ( isset($property->receptions) ) ? (string)$property->receptions : '' ) );

					// Residential Sales Details
					if ( strpos(strtolower((string)$property->department), 'sales') !== FALSE )
					{
						update_post_meta( $post_id, '_price', $price );
						update_post_meta( $post_id, '_price_actual', $price );

						$poa = '';
						if (
							strpos(strtolower((string)$property->price_text), 'poa') !== FALSE || 
							strpos(strtolower((string)$property->price_text), 'p.o.a') !== FALSE || 
							strpos(strtolower((string)$property->price_text), 'on application') !== FALSE
						)
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

						$price_qualifier_term_id = '';
						foreach ($mapping as $feed_value => $ph_term_id)
						{
							if (strpos(strtolower((string)$property->price_text), strtolower((string)$feed_value)) !== FALSE)
							{
								$price_qualifier_term_id = $ph_term_id;
								break;
							}
						}

						wp_delete_object_term_relationships( $post_id, 'price_qualifier' );
						if ( $price_qualifier_term_id != '' )
						{
			                wp_set_post_terms( $post_id, $price_qualifier_term_id, 'price_qualifier' );
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
						if ( !empty($mapping) && isset($property->tenure) && isset($mapping[(string)$property->tenure]) )
						{
				            wp_set_post_terms( $post_id, $mapping[(string)$property->tenure], 'tenure' );
			            }
					}
					elseif ( strpos(strtolower((string)$property->department), 'lettings') !== FALSE )
					{
						update_post_meta( $post_id, '_rent', $price );

						$rent_frequency = 'pcm';
						$price_actual = $price;

						if (
							strpos(strtolower((string)$property->price_text), 'pcm') !== FALSE || 
							strpos(strtolower((string)$property->price_text), 'month') !== FALSE
						)
						{
							$rent_frequency = 'pcm';
							$price_actual = $price;
						}

						if (
							strpos(strtolower((string)$property->price_text), 'pw') !== FALSE || 
							strpos(strtolower((string)$property->price_text), 'week') !== FALSE
						)
						{
							$rent_frequency = 'pw';
							$price_actual = ($price * 52) / 12;
						}

						if (
							strpos(strtolower((string)$property->price_text), 'pq') !== FALSE || 
							strpos(strtolower((string)$property->price_text), 'quarter') !== FALSE
						)
						{
							$rent_frequency = 'pq';
							$price_actual = ($price * 4) / 12;
						}

						if (
							strpos(strtolower((string)$property->price_text), 'pa') !== FALSE || 
							strpos(strtolower((string)$property->price_text), 'annum') !== FALSE || 
							strpos(strtolower((string)$property->price_text), 'annual') !== FALSE
						)
						{
							$rent_frequency = 'pa';
							$price_actual = $price / 12;
						}

						update_post_meta( $post_id, '_rent_frequency', $rent_frequency );
						update_post_meta( $post_id, '_price_actual', $price_actual );
						
						$poa = '';
						if (
							strpos(strtolower((string)$property->price_text), 'poa') !== FALSE || 
							strpos(strtolower((string)$property->price_text), 'p.o.a') !== FALSE || 
							strpos(strtolower((string)$property->price_text), 'on application') !== FALSE
						)
						{
							$poa = 'yes';
						}
						update_post_meta( $post_id, '_poa', $poa );

						update_post_meta( $post_id, '_deposit', '' );
	            		update_post_meta( $post_id, '_available_date', '' );

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
						if ( !empty($mapping) && isset($property->furnished) && isset($mapping[(string)$property->furnished]) )
						{
			                wp_set_post_terms( $post_id, $mapping[(string)$property->furnished], 'furnished' );
			            }
					}
				}
				else
				{
					// Commercial
					update_post_meta( $post_id, '_for_sale', '' );
            		update_post_meta( $post_id, '_to_rent', '' );

            		if ( strpos( (string)$property->price_text, 'Rental' ) === FALSE )
	                {
	                    update_post_meta( $post_id, '_for_sale', 'yes' );

	                    update_post_meta( $post_id, '_commercial_price_currency', (string)$property->currency );

	                    update_post_meta( $post_id, '_price_from', $price );
	                    update_post_meta( $post_id, '_price_to', $price );

	                    update_post_meta( $post_id, '_price_units', '' );

	                    $poa = '';
						if (
							strpos(strtolower((string)$property->price_text), 'poa') !== FALSE || 
							strpos(strtolower((string)$property->price_text), 'p.o.a') !== FALSE || 
							strpos(strtolower((string)$property->price_text), 'on application') !== FALSE
						)
						{
							$poa = 'yes';
						}
	                    update_post_meta( $post_id, '_price_poa', $poa );

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
						if ( !empty($mapping) && isset($property->tenure) && isset($mapping[(string)$property->tenure]) )
						{
				            wp_set_post_terms( $post_id, $mapping[(string)$property->tenure], 'commercial_tenure' );
			            }
	                }
	                else
	                {
	                    update_post_meta( $post_id, '_to_rent', 'yes' );

	                    update_post_meta( $post_id, '_commercial_rent_currency', (string)$property->currency );

	                    update_post_meta( $post_id, '_rent_from', $price );
	                    update_post_meta( $post_id, '_rent_to', $price );

	                    $rent_frequency = 'pcm';
						if (
							strpos(strtolower((string)$property->price_text), 'pcm') !== FALSE || 
							strpos(strtolower((string)$property->price_text), 'month') !== FALSE
						)
						{
							$rent_frequency = 'pcm';
						}

						if (
							strpos(strtolower((string)$property->price_text), 'pw') !== FALSE || 
							strpos(strtolower((string)$property->price_text), 'week') !== FALSE
						)
						{
							$rent_frequency = 'pw';
						}

						if (
							strpos(strtolower((string)$property->price_text), 'pq') !== FALSE || 
							strpos(strtolower((string)$property->price_text), 'quarter') !== FALSE
						)
						{
							$rent_frequency = 'pq';
						}

						if (
							strpos(strtolower((string)$property->price_text), 'pa') !== FALSE || 
							strpos(strtolower((string)$property->price_text), 'annum') !== FALSE || 
							strpos(strtolower((string)$property->price_text), 'annual') !== FALSE
						)
						{
							$rent_frequency = 'pa';
						}
	                    update_post_meta( $post_id, '_rent_units', $rent_frequency);

	                    $poa = '';
						if (
							strpos(strtolower((string)$property->price_text), 'poa') !== FALSE || 
							strpos(strtolower((string)$property->price_text), 'p.o.a') !== FALSE || 
							strpos(strtolower((string)$property->price_text), 'on application') !== FALSE
						)
						{
							$poa = 'yes';
						}
	                    update_post_meta( $post_id, '_rent_poa', $poa );
	                }

	                // Store price in common currency (GBP) used for ordering
		            $ph_countries = new PH_Countries();
		            $ph_countries->update_property_price_actual( $post_id );

		            $size = '';
		            update_post_meta( $post_id, '_floor_area_from', $size );
		            update_post_meta( $post_id, '_floor_area_from_sqft', $size );

		            update_post_meta( $post_id, '_floor_area_to', $size );
		            update_post_meta( $post_id, '_floor_area_to_sqft', $size );

		            update_post_meta( $post_id, '_floor_area_units', '' );

		            update_post_meta( $post_id, '_site_area_from', $size );
		            update_post_meta( $post_id, '_site_area_from_sqft', $size );

		            update_post_meta( $post_id, '_site_area_to', $size );
		            update_post_meta( $post_id, '_site_area_to_sqft', $size );

		            update_post_meta( $post_id, '_site_area_units', '' );
				}

				// Marketing
				update_post_meta( $post_id, '_on_market', 'yes' );
				$featured = '';
				if ( isset($property->featuredProperty) && strtolower((string)$property->featuredProperty) == 'yes' )
				{
					$featured = 'yes';
				}
				elseif ( isset($property->propertyofweek) && strtolower((string)$property->propertyofweek) == 'yes' )
				{
					$featured = 'yes';
				}
				update_post_meta( $post_id, '_featured', $featured );

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

        		if ( isset($property->priority)  )
	            {
					if ( !empty($mapping) && isset($property->priority) && isset($mapping[(string)$property->priority]) )
					{
		                wp_set_post_terms( $post_id, $mapping[(string)$property->priority], 'availability' );
		            }
		            else
					{
						$this->add_log( 'Property received with an availability (' . (string)$property->priority . ') that is not mapped', (string)$property->reference );
					}
		        }

	            // Features
				$features = array();
				for ( $i = 1; $i <= 20; ++$i )
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

		        // Rooms
		        $i = 0;
				if ( isset($property->rooms) && !empty($property->rooms) )
				{
					foreach ($property->rooms as $rooms)
					{
						foreach ( $rooms->room as $room )
						{
							$room_attributes = $room->attributes();

							update_post_meta( $post_id, '_room_name_' . $i, (string)$room_attributes['name'] );
				            update_post_meta( $post_id, '_room_dimensions_' . $i, (string)$room->measurement_text );
				            update_post_meta( $post_id, '_room_description_' . $i, (string)$room->description );

				            ++$i;
						}
					}
				}
				update_post_meta( $post_id, '_rooms', $i );

	            // Media - Images
	            if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();
    				if ( isset($property->pictures) && !empty($property->pictures) )
					{
						foreach ( $property->pictures as $pictures )
						{
							foreach ( $pictures->picture as $picture )
							{
								$picture_attributes = $picture->attributes();

								if ( isset($picture->filename) && trim((string)$picture->filename) != '' )
								{
									if ( 
										substr( strtolower((string)$picture->filename), 0, 2 ) == '//' || 
										substr( strtolower((string)$picture->filename), 0, 4 ) == 'http'
									)
									{
										// This is a URL
										$url = str_replace(" ", "%20", (string)$picture->filename);

										$media_urls[] = array('url' => $url);
									}
								}
							}
						}
					}
					update_post_meta( $post_id, '_photo_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' photo URLs', (string)$property->reference );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_photos', TRUE );
					if ( isset($property->pictures) && !empty($property->pictures) )
					{
						foreach ( $property->pictures as $pictures )
						{
							foreach ( $pictures->picture as $picture )
							{
								$picture_attributes = $picture->attributes();

								if ( isset($picture->filename) && trim((string)$picture->filename) != '' )
								{
									if ( 
										substr( strtolower((string)$picture->filename), 0, 2 ) == '//' || 
										substr( strtolower((string)$picture->filename), 0, 4 ) == 'http'
									)
									{
										// This is a URL
										$url = str_replace(" ", "%20", (string)$picture->filename);
										$description = ( ( isset($picture_attributes['name']) && (string)$picture_attributes['name'] != '' ) ? (string)$picture_attributes['name'] : '' );
									    
										$filename = basename( $url );

										// Check, based on the URL, whether we have previously imported this media
										$imported_previously = false;
										$imported_previously_id = '';
										if ( is_array($previous_media_ids) && !empty($previous_media_ids) )
										{
											foreach ( $previous_media_ids as $previous_media_id )
											{
												$previous_url = get_post_meta( $previous_media_id, '_imported_url', TRUE );
												$new_url = $url;

												// Should contain 'expert' in URL but check first in case EA ever change their media hosting
												if ( strpos($previous_url, 'expert') !== FALSE && strpos($new_url, 'expert') !== FALSE )
												{
													// Need to remove first part of URLs before comparing as it seems to differ between http://med01. and http://www.
													$remove_from_previous_url = substr( strtolower($previous_url), 0, strpos( strtolower($previous_url), 'expert' ) );
													$previous_url = str_replace( $remove_from_previous_url, "", $previous_url);

													$remove_from_new_url = substr( strtolower($new_url), 0, strpos( strtolower($new_url), 'expert' ) );
													$new_url = str_replace( $remove_from_new_url, "", $new_url);
												}

												if ( $previous_url == $new_url )
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

										        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), (string)$property->reference );
										    }
										    else
										    {
											    $id = media_handle_sideload( $file_array, $post_id, $description );

											    // Check for handle sideload errors.
											    if ( is_wp_error( $id ) ) 
											    {
											        @unlink( $file_array['tmp_name'] );
											        
											        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), (string)$property->reference );
											    }
											    else
											    {
											    	$media_ids[] = $id;

											    	update_post_meta( $id, '_imported_url', addslashes($url));

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

					$this->add_log( 'Imported ' . count($media_ids) . ' photos (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->reference );
				}

				// Media - Floorplans
				if ( get_option('propertyhive_floorplans_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();
    				if ( isset($property->floorplans) && !empty($property->floorplans) )
					{
						foreach ( $property->floorplans as $floorplans )
						{
							foreach ( $floorplans->floorplan as $floorplan )
							{
								$floorplan_attributes = $floorplan->attributes();

								if ( isset($floorplan->filename) && trim((string)$floorplan->filename) != '' )
								{
									if ( 
										substr( strtolower((string)$floorplan->filename), 0, 2 ) == '//' || 
										substr( strtolower((string)$floorplan->filename), 0, 4 ) == 'http'
									)
									{
										// This is a URL
										$url = str_replace(" ", "%20", (string)$floorplan->filename);

										$media_urls[] = array('url' => $url);
									}
								}
							}
						}
					}
					update_post_meta( $post_id, '_floorplan_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' floorplan URLs', (string)$property->reference );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_floorplans', TRUE );
					if ( isset($property->floorplans) && !empty($property->floorplans) )
					{
						foreach ( $property->floorplans as $floorplans )
						{
							foreach ( $floorplans->floorplan as $floorplan )
							{
								$floorplan_attributes = $floorplan->attributes();

								if ( isset($floorplan->filename) && trim((string)$floorplan->filename) != '' )
								{
									if ( 
										substr( strtolower((string)$floorplan->filename), 0, 2 ) == '//' || 
										substr( strtolower((string)$floorplan->filename), 0, 4 ) == 'http'
									)
									{
										// This is a URL
										$url = str_replace(" ", "%20", (string)$floorplan->filename);
										$description = ( ( isset($floorplan_attributes['name']) && (string)$floorplan_attributes['name'] != '' ) ? (string)$floorplan_attributes['name'] : '' );
									    
										$filename = basename( $url );

										// Check, based on the URL, whether we have previously imported this media
										$imported_previously = false;
										$imported_previously_id = '';
										if ( is_array($previous_media_ids) && !empty($previous_media_ids) )
										{
											foreach ( $previous_media_ids as $previous_media_id )
											{
												$previous_url = get_post_meta( $previous_media_id, '_imported_url', TRUE );
												$new_url = $url;

												// Should contain 'expert' in URL but check first in case EA ever change their media hosting
												if ( strpos($previous_url, 'expert') !== FALSE && strpos($new_url, 'expert') !== FALSE )
												{
													// Need to remove first part of URLs before comparing as it seems to differ between http://med01. and http://www.
													$remove_from_previous_url = substr( strtolower($previous_url), 0, strpos( strtolower($previous_url), 'expert' ) );
													$previous_url = str_replace( $remove_from_previous_url, "", $previous_url);

													$remove_from_new_url = substr( strtolower($new_url), 0, strpos( strtolower($new_url), 'expert' ) );
													$new_url = str_replace( $remove_from_new_url, "", $new_url);
												}

												if ( $previous_url == $new_url )
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

										        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), (string)$property->reference );
										    }
										    else
										    {
											    $id = media_handle_sideload( $file_array, $post_id, $description );

											    // Check for handle sideload errors.
											    if ( is_wp_error( $id ) ) 
											    {
											        @unlink( $file_array['tmp_name'] );
											        
											        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), (string)$property->reference );
											    }
											    else
											    {
											    	$media_ids[] = $id;

											    	update_post_meta( $id, '_imported_url', addslashes($url));

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

					$this->add_log( 'Imported ' . count($media_ids) . ' floorplans (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->reference );
				}

				// Media - Brochures
				if ( get_option('propertyhive_brochures_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();
    				if ( isset($property->brochure) && trim((string)$property->brochure) != '' )
					{
						if ( 
							substr( strtolower((string)$property->brochure), 0, 2 ) == '//' || 
							substr( strtolower((string)$property->brochure), 0, 4 ) == 'http'
						)
						{
							// This is a URL
							$url = str_replace(" ", "%20", (string)$property->brochure);

							$media_urls[] = array('url' => $url);
						}
					}
					update_post_meta( $post_id, '_brochure_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' brochure URLs', (string)$property->reference );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_brochures', TRUE );

					if ( isset($property->brochure) && trim((string)$property->brochure) != '' )
					{
						if ( 
							substr( strtolower((string)$property->brochure), 0, 2 ) == '//' || 
							substr( strtolower((string)$property->brochure), 0, 4 ) == 'http'
						)
						{
							// This is a URL
							$url = str_replace(" ", "%20", (string)$property->brochure);
							$description = 'Brochure';
						    
							$filename = basename( $url );

							// Check, based on the URL, whether we have previously imported this media
							$imported_previously = false;
							$imported_previously_id = '';
							if ( is_array($previous_media_ids) && !empty($previous_media_ids) )
							{
								foreach ( $previous_media_ids as $previous_media_id )
								{
									$previous_url = get_post_meta( $previous_media_id, '_imported_url', TRUE );
									$new_url = $url;

									// Should contain 'expert' in URL but check first in case EA ever change their media hosting
									if ( strpos($previous_url, 'expert') !== FALSE && strpos($new_url, 'expert') !== FALSE )
									{
										// Need to remove first part of URLs before comparing as it seems to differ between http://med01. and http://www.
										$remove_from_previous_url = substr( strtolower($previous_url), 0, strpos( strtolower($previous_url), 'expert' ) );
										$previous_url = str_replace( $remove_from_previous_url, "", $previous_url);

										$remove_from_new_url = substr( strtolower($new_url), 0, strpos( strtolower($new_url), 'expert' ) );
										$new_url = str_replace( $remove_from_new_url, "", $new_url);
									}

									if ( $previous_url == $new_url )
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

							        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), (string)$property->reference );
							    }
							    else
							    {
								    $id = media_handle_sideload( $file_array, $post_id, $description );

								    // Check for handle sideload errors.
								    if ( is_wp_error( $id ) ) 
								    {
								        @unlink( $file_array['tmp_name'] );
								        
								        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), (string)$property->reference );
								    }
								    else
								    {
								    	$media_ids[] = $id;

								    	update_post_meta( $id, '_imported_url', addslashes($url));

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

					$this->add_log( 'Imported ' . count($media_ids) . ' brochures (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->reference );
				}

				// Media - EPCs
				if ( get_option('propertyhive_epcs_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();

    				if ( isset($property->epc) && trim((string)$property->epc) != '' )
					{
						if ( 
							substr( strtolower((string)$property->epc), 0, 2 ) == '//' || 
							substr( strtolower((string)$property->epc), 0, 4 ) == 'http'
						)
						{
							// This is a URL
							$url = str_replace(" ", "%20", (string)$property->epc);

							$media_urls[] = array('url' => $url);
						}
					}

					update_post_meta( $post_id, '_epc_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' EPC URLs', (string)$property->reference );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_epcs', TRUE );

					if ( isset($property->epc) && trim((string)$property->epc) != '' )
					{
						if ( 
							substr( strtolower((string)$property->epc), 0, 2 ) == '//' || 
							substr( strtolower((string)$property->epc), 0, 4 ) == 'http'
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
									$new_url = $url;

									// Should contain 'expert' in URL but check first in case EA ever change their media hosting
									if ( strpos($previous_url, 'expert') !== FALSE && strpos($new_url, 'expert') !== FALSE )
									{
										// Need to remove first part of URLs before comparing as it seems to differ between http://med01. and http://www.
										$remove_from_previous_url = substr( strtolower($previous_url), 0, strpos( strtolower($previous_url), 'expert' ) );
										$previous_url = str_replace( $remove_from_previous_url, "", $previous_url);

										$remove_from_new_url = substr( strtolower($new_url), 0, strpos( strtolower($new_url), 'expert' ) );
										$new_url = str_replace( $remove_from_new_url, "", $new_url);
									}
									
									if ( $previous_url == $new_url )
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

							        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), (string)$property->reference );
							    }
							    else
							    {
								    $id = media_handle_sideload( $file_array, $post_id, $description );

								    // Check for handle sideload errors.
								    if ( is_wp_error( $id ) ) 
								    {
								        @unlink( $file_array['tmp_name'] );
								        
								        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), (string)$property->reference );
								    }
								    else
								    {
								    	$media_ids[] = $id;

								    	update_post_meta( $id, '_imported_url', addslashes($url));

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

					$this->add_log( 'Imported ' . count($media_ids) . ' epcs (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', (string)$property->reference );
				}

				// Media - Virtual Tours
				if ( isset($property->virtual_tour_url) && trim((string)$property->virtual_tour_url) != '' )
				{
					if ( 
						substr( strtolower((string)$property->virtual_tour_url), 0, 2 ) == '//' || 
						substr( strtolower((string)$property->virtual_tour_url), 0, 4 ) == 'http'
					)
					{
						// This is a URL
						$url = (string)$property->virtual_tour_url;

						update_post_meta( $post_id, '_virtual_tours', 1 );
						update_post_meta( $post_id, '_virtual_tour_0', $url );

						$this->add_log( 'Imported 1 virtual tour', (string)$property->reference );
					}
					else
					{
						$this->add_log( 'Imported 0 virtual tours', (string)$property->reference );
					}
				}
				else
				{
					$this->add_log( 'Imported 0 virtual tours', (string)$property->reference );
				}

				do_action( "propertyhive_property_imported_expert_agent_xml", $post_id, $property );

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
							$this->add_log( 'New meta data for ' . trim($key, '_') . ': ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), (string)$property->reference );
						}
						elseif ( $metadata_before[$key] != $metadata_after[$key] )
						{
							$this->add_log( 'Updated ' . trim($key, '_') . '. Before: ' . ( ( is_array($metadata_before[$key]) ) ? implode(", ", $metadata_before[$key]) : $metadata_before[$key] ) . ', After: ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), (string)$property->reference );
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
							$this->add_log( 'New taxonomy data for ' . $taxonomy_name . ': ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), (string)$property->reference );
						}
						elseif ( $taxonomy_terms_before[$taxonomy_name] != $taxonomy_terms_after[$taxonomy_name] )
						{
							$this->add_log( 'Updated ' . $taxonomy_name . '. Before: ' . ( ( is_array($taxonomy_terms_before[$taxonomy_name]) ) ? implode(", ", $taxonomy_terms_before[$taxonomy_name]) : $taxonomy_terms_before[$taxonomy_name] ) . ', After: ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), (string)$property->reference );
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

		do_action( "propertyhive_post_import_properties_expertagent_xml" );

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
			||
			apply_filters('propertyhive_ignore_portal_add_on_when_removing_properties', false) === true
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
				$import_refs[] = (string)$property->reference;
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
			if (
				class_exists('PH_Property_Portal')
				&& 
				apply_filters('propertyhive_ignore_portal_add_on_when_removing_properties', false) !== true
			)
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

					do_action( "propertyhive_property_removed_expert_agent_xml", $post->ID );
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
                'On Market' => 'On Market',
                'Available to Let' => 'Available to Let',
                'Sold STC' => 'Sold STC',
                'Under Offer' => 'Under Offer',
                'Exchanged' => 'Exchanged',
                'Let' => 'Let',
                'Let STC' => 'Let STC',
                'Withdrawn' => 'Withdrawn',
            );
        }
        if ($custom_field == 'sales_availability')
        {
            return array(
                'On Market' => 'On Market',
                'Sold STC' => 'Sold STC',
                'Under Offer' => 'Under Offer',
                'Exchanged' => 'Exchanged',
                'Withdrawn' => 'Withdrawn',
            );
        }
        if ($custom_field == 'lettings_availability')
        {
            return array(
                'On Market' => 'On Market',
                'Available to Let' => 'Available to Let',
                'Let' => 'Let',
                'Let STC' => 'Let STC',
                'Withdrawn' => 'Withdrawn',
            );
        }
        if ($custom_field == 'property_type')
        {
        	return array(
                'House - Detached' => 'House - Detached',
                'House - Semi Detached' => 'House - Semi Detached',
                'House - Terraced' => 'House - Terraced',
                'House - End of Terrace' => 'House - End of Terrace',

                'Flat - Lower Ground Floor Flat' => 'Flat - Lower Ground Floor Flat',
                'Flat - Ground Floor Flat' => 'Flat - Ground Floor Flat',
                'Flat - Upper Floor Flat' => 'Flat - Upper Floor Flat',

                'Bungalow - Detached' => 'Bungalow - Detached',
                'Bungalow - Semi Detached' => 'Bungalow - Semi Detached',
                'Bungalow - Terraced' => 'Bungalow - Terraced',
                'Bungalow - End of Terrace' => 'Bungalow - End of Terrace',
            );
        }
        if ($custom_field == 'commercial_property_type')
        {
        	return array(
                'Leasehold Offices' => 'Leasehold Offices',
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
                'Freehold' => 'Freehold',
                'Share of Freehold' => 'Share of Freehold',
                'Leasehold' => 'Leasehold',
                'Private Company Ownership' => 'Private Company Ownership',
                'Unknown' => 'Unknown',
            );
        }
        if ($custom_field == 'furnished')
        {
            return array(
            	'Landlord Flexible' => 'Landlord Flexible',
                'Furnished' => 'Furnished',
                'Part Furnished' => 'Part Furnished',
                'Un-Furnished' => 'Un-Furnished',
            );
        }
    }

}

}