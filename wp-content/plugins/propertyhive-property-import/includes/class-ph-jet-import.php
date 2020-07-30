<?php
/**
 * Class for managing the import process of a JET file
 *
 * @package WordPress
 */
if ( class_exists( 'PH_Property_Import_Process' ) ) {

class PH_JET_Import extends PH_Property_Import_Process {

	/**
	 * @var array
	 */
	private $property_ids;

	public function __construct( $instance_id = '' ) 
	{
		$this->instance_id = $instance_id;

		if ( $this->instance_id != '' && isset($_GET['custom_property_import_cron']) )
	    {
	    	$current_user = wp_get_current_user();

	    	$this->add_log("Executed manually by " . ( ( isset($current_user->display_name) ) ? $current_user->display_name : '' ) );
	    }
	}

	public function parse( $import_id = '' )
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

		$this->add_log("Parsing properties");

		$this->properties = array(); // Reset properties in the event we're importing multiple files

		$property_fields = array(
			'ID',

			// Address
			'Strapline',
			'HouseName',
			'HouseNumber',
			'Address1',
			'Address2',
			'Address3',
			'Address4',
			'Postcode',
			'Area',
			'Country',
			'Latitude',
			'Longitude',

			'Currency',

			// Office
			'MarketingOffice',
			'Office',
			'Negotiator',

			// Residential Sales
			'SalePrice',
			'PriceQualifier',
			'Tenure',
			
			// Residential Lettings
			'WeeklyRent',
			'RentalPeriod',
			'Furnish',
			'AvailableFrom',

			// Residential
			'TotalBedrooms',
			'Bedrooms',
			'Bathrooms',
			'ReceptionRooms',
			'Type',
			'Style',
			'Age',
			'Parking',

			// Commercial
			'Size',

			// Marketing
			'MarketedOnInternet',
			'SaleStatus',
			'LettingStatus',
			'Status',
			'Featured',

			// Descriptions
			'AccommodationSummary',
			'Description',
			'LongDesc',
			'Room',

			// Media
			'Image',
			'Floorplan',
			'PDF',
			'PDFAmendTime',
			'EPC',
			'EPCURL',
			'VTour',
		);

		$property_fields = apply_filters( 'propertyhive_jet_property_fields', $property_fields );

		// Sales
		$criteria = array(
			'SearchType' => 'sales',
			'PropertyField' => $property_fields,
			'Offset' => 0,
			'Limit' => 50,
		);

		if ( isset($options['only_updated']) && $options['only_updated'] == 'yes' )
		{
			$previous_jet_update_date = get_option( 'jet_update_date_' . $import_id, '');
			if ( $previous_jet_update_date != '' )
			{
				$criteria['DateFilter'] = 'amended';
				$criteria['FromDate'] = $previous_jet_update_date;
			}
		}

		$criteria = apply_filters( 'propertyhive_jet_sales_criteria', $criteria );

		$params = array('Criteria' => $criteria);

		try 
		{
			$client = new SoapClient( $options['url'], apply_filters( 'propertyhive_jet_soapclient_options', array() ) );

			$authHeaders = array(
				new SoapHeader('http://soapinterop.org/echoheader/', 'ClientID', $options['user']),
				new SoapHeader('http://soapinterop.org/echoheader/', 'Password', $options['pass'])
			);
			$client->__setSoapHeaders($authHeaders);

			$total = $client->__soapCall('GetNumGeneralProperties', $params);

			if ( !is_soap_fault($total) ) 
			{
				$this->add_log("Found " . $total . " sales properties for parsing");

				if ( $total ) 
				{
					$total_pages = ceil( $total / $criteria['Limit'] );

					for ( $i = 0; $i < $total_pages; ++$i )
					{
						$criteria['Offset'] = $i * $criteria['Limit'];

						$params = array('Criteria' => $criteria);

						$client = new SoapClient($options['url'], array());

						$authHeaders = array(
							new SoapHeader('http://soapinterop.org/echoheader/', 'ClientID', $options['user']),
							new SoapHeader('http://soapinterop.org/echoheader/', 'Password', $options['pass'])
						);
						$client->__setSoapHeaders($authHeaders);

						$properties = $client->__soapCall('GetGeneralProperties', $params);

						foreach ( $properties as $property )
						{
							$property = (object) array_merge( (array)$property, array( 'department' => 'residential-sales' ) );

							$this->properties[] = $property;
						}
					}
				}

				if ( isset($options['only_updated']) && $options['only_updated'] == 'yes' )
				{
					$criteria['PropertyField'] = array('ID');
					$criteria['Offset'] = 0;
					$criteria['Limit'] = 99999;
					if ( isset($criteria['DateFilter']) ) unset($criteria['DateFilter']);
					if ( isset($criteria['FromDate']) ) unset($criteria['FromDate']);

					$params = array('Criteria' => $criteria);

					$client = new SoapClient($options['url'], array());

					$authHeaders = array(
						new SoapHeader('http://soapinterop.org/echoheader/', 'ClientID', $options['user']),
						new SoapHeader('http://soapinterop.org/echoheader/', 'Password', $options['pass'])
					);
					$client->__setSoapHeaders($authHeaders);

					$properties = $client->__soapCall('GetGeneralProperties', $params);

					foreach ( $properties as $property )
					{
						$this->property_ids[] = $property->ID;
					}
				}
			}
		}
		catch (SoapFault $fault) 
		{
		    $this->add_log("SOAP Error whist getting sales properties (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})");

		    // return false if an error is received that isn't relating to invalid method as likely be to http error or similar
		    if ( strpos($fault->faultstring, 'not a valid method') === FALSE )
		    {
		    	die();
		    }
		}

		// Lettings
		$criteria = array(
			'SearchType' => 'lettings',
			'PropertyField' => $property_fields,
			'Offset' => 0,
			'Limit' => 50,
		);

		if ( isset($options['only_updated']) && $options['only_updated'] == 'yes' )
		{
			$previous_jet_update_date = get_option( 'jet_update_date_' . $import_id, '');
			if ( $previous_jet_update_date != '' )
			{
				$criteria['DateFilter'] = 'amended';
				$criteria['FromDate'] = $previous_jet_update_date;
			}
		}

		$criteria = apply_filters( 'propertyhive_jet_lettings_criteria', $criteria );

		$params = array('Criteria' => $criteria);

		try 
		{
			$client = new SoapClient($options['url'], array());

			$authHeaders = array(
				new SoapHeader('http://soapinterop.org/echoheader/', 'ClientID', $options['user']),
				new SoapHeader('http://soapinterop.org/echoheader/', 'Password', $options['pass'])
			);
			$client->__setSoapHeaders($authHeaders);

			$total = $client->__soapCall('GetNumGeneralProperties', $params);

			if ( !is_soap_fault($total) ) 
			{
				$this->add_log("Found " . $total . " lettings properties for parsing");

				if ( $total ) 
				{
					$total_pages = ceil( $total / $criteria['Limit'] );

					for ( $i = 0; $i < $total_pages; ++$i )
					{
						$criteria['Offset'] = $i * $criteria['Limit'];

						$params = array('Criteria' => $criteria);

						$client = new SoapClient($options['url'], array());

						$authHeaders = array(
							new SoapHeader('http://soapinterop.org/echoheader/', 'ClientID', $options['user']),
							new SoapHeader('http://soapinterop.org/echoheader/', 'Password', $options['pass'])
						);
						$client->__setSoapHeaders($authHeaders);

						$properties = $client->__soapCall('GetGeneralProperties', $params);

						foreach ( $properties as $property )
						{
							$property = (object) array_merge( (array)$property, array( 'department' => 'residential-lettings' ) );

							$this->properties[] = $property;
						}
					}
				}

				if ( isset($options['only_updated']) && $options['only_updated'] == 'yes' )
				{
					$criteria['PropertyField'] = array('ID');
					$criteria['Offset'] = 0;
					$criteria['Limit'] = 99999;
					if ( isset($criteria['DateFilter']) ) unset($criteria['DateFilter']);
					if ( isset($criteria['FromDate']) ) unset($criteria['FromDate']);

					$params = array('Criteria' => $criteria);

					$client = new SoapClient($options['url'], array());

					$authHeaders = array(
						new SoapHeader('http://soapinterop.org/echoheader/', 'ClientID', $options['user']),
						new SoapHeader('http://soapinterop.org/echoheader/', 'Password', $options['pass'])
					);
					$client->__setSoapHeaders($authHeaders);

					$properties = $client->__soapCall('GetGeneralProperties', $params);

					foreach ( $properties as $property )
					{
						$this->property_ids[] = $property->ID;
					}
				}
			}
		}
		catch (SoapFault $fault) 
		{
		    $this->add_log("SOAP Error whist getting lettings properties (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})");

		    // return false if an error is received that isn't relating to invalid method as likely be to http error or similar
		    if ( strpos($fault->faultstring, 'not a valid method') === FALSE )
		    {
		    	die();
		    }
		}

		// Commercial Sales
		$criteria = array(
			'SearchType' => 'sales',
			'PropertyField' => $property_fields,
			'Offset' => 0,
			'Limit' => 50,
		);

		if ( isset($options['only_updated']) && $options['only_updated'] == 'yes' )
		{
			$previous_jet_update_date = get_option( 'jet_update_date_' . $import_id, '');
			if ( $previous_jet_update_date != '' )
			{
				$criteria['DateFilter'] = 'amended';
				$criteria['FromDate'] = $previous_jet_update_date;
			}
		}

		$criteria = apply_filters( 'propertyhive_jet_commercial_sales_criteria', $criteria );

		$params = array('Criteria' => $criteria);

		try 
		{
			$client = new SoapClient($options['url'], array());

			$authHeaders = array(
				new SoapHeader('http://soapinterop.org/echoheader/', 'ClientID', $options['user']),
				new SoapHeader('http://soapinterop.org/echoheader/', 'Password', $options['pass'])
			);
			$client->__setSoapHeaders($authHeaders);

			$total = $client->__soapCall('GetNumCommercialProperties', $params);

			if ( !is_soap_fault($total) ) 
			{
				$this->add_log("Found " . $total . " commercial sales properties for parsing");
				
				if ( $total ) 
				{
					$total_pages = ceil( $total / $criteria['Limit'] );

					for ( $i = 0; $i < $total_pages; ++$i )
					{
						$criteria['Offset'] = $i * $criteria['Limit'];

						$params = array('Criteria' => $criteria);

						$client = new SoapClient($options['url'], array());

						$authHeaders = array(
							new SoapHeader('http://soapinterop.org/echoheader/', 'ClientID', $options['user']),
							new SoapHeader('http://soapinterop.org/echoheader/', 'Password', $options['pass'])
						);
						$client->__setSoapHeaders($authHeaders);

						$properties = $client->__soapCall('GetCommercialProperties', $params);

						foreach ( $properties as $property )
						{
							$property = (object) array_merge( (array)$property, array( 'department' => 'commercial' ) );

							$this->properties[] = $property;
						}
					}
				}

				if ( isset($options['only_updated']) && $options['only_updated'] == 'yes' )
				{
					$criteria['PropertyField'] = array('ID');
					$criteria['Offset'] = 0;
					$criteria['Limit'] = 99999;
					if ( isset($criteria['DateFilter']) ) unset($criteria['DateFilter']);
					if ( isset($criteria['FromDate']) ) unset($criteria['FromDate']);

					$params = array('Criteria' => $criteria);

					$client = new SoapClient($options['url'], array());

					$authHeaders = array(
						new SoapHeader('http://soapinterop.org/echoheader/', 'ClientID', $options['user']),
						new SoapHeader('http://soapinterop.org/echoheader/', 'Password', $options['pass'])
					);
					$client->__setSoapHeaders($authHeaders);

					$properties = $client->__soapCall('GetCommercialProperties', $params);

					foreach ( $properties as $property )
					{
						$this->property_ids[] = $property->ID;
					}
				}
			}
		}
		catch (SoapFault $fault) 
		{
		    $this->add_log("SOAP Error whist getting commercial sales properties (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})");

		    // return false if an error is received that isn't relating to invalid method as likely be to http error or similar
		    if ( strpos($fault->faultstring, 'not a valid method') === FALSE )
		    {
		    	die();
		    }
		}

		// Commercial Lettings
		$criteria = array(
			'SearchType' => 'lettings',
			'PropertyField' => $property_fields,
			'Offset' => 0,
			'Limit' => 50,
		);

		if ( isset($options['only_updated']) && $options['only_updated'] == 'yes' )
		{
			$previous_jet_update_date = get_option( 'jet_update_date_' . $import_id, '');
			if ( $previous_jet_update_date != '' )
			{
				$criteria['DateFilter'] = 'amended';
				$criteria['FromDate'] = $previous_jet_update_date;
			}
		}

		$criteria = apply_filters( 'propertyhive_jet_commercial_lettings_criteria', $criteria );

		$params = array('Criteria' => $criteria);

		try 
		{
			$client = new SoapClient($options['url'], array());

			$authHeaders = array(
				new SoapHeader('http://soapinterop.org/echoheader/', 'ClientID', $options['user']),
				new SoapHeader('http://soapinterop.org/echoheader/', 'Password', $options['pass'])
			);
			$client->__setSoapHeaders($authHeaders);

			$total = $client->__soapCall('GetNumCommercialProperties', $params);

			if ( !is_soap_fault($total) ) 
			{
				$this->add_log("Found " . $total . " commercial lettings properties for parsing");
				
				if ( $total ) 
				{
					$total_pages = ceil( $total / $criteria['Limit'] );

					for ( $i = 0; $i < $total_pages; ++$i )
					{
						$criteria['Offset'] = $i * $criteria['Limit'];

						$params = array('Criteria' => $criteria);

						$client = new SoapClient($options['url'], array());

						$authHeaders = array(
							new SoapHeader('http://soapinterop.org/echoheader/', 'ClientID', $options['user']),
							new SoapHeader('http://soapinterop.org/echoheader/', 'Password', $options['pass'])
						);
						$client->__setSoapHeaders($authHeaders);

						$properties = $client->__soapCall('GetCommercialProperties', $params);

						foreach ( $properties as $property )
						{
							$property = (object) array_merge( (array)$property, array( 'department' => 'commercial' ) );

							$this->properties[] = $property;
						}
					}
				}

				if ( isset($options['only_updated']) && $options['only_updated'] == 'yes' )
				{
					$criteria['PropertyField'] = array('ID');
					$criteria['Offset'] = 0;
					$criteria['Limit'] = 99999;
					if ( isset($criteria['DateFilter']) ) unset($criteria['DateFilter']);
					if ( isset($criteria['FromDate']) ) unset($criteria['FromDate']);

					$params = array('Criteria' => $criteria);

					$client = new SoapClient($options['url'], array());

					$authHeaders = array(
						new SoapHeader('http://soapinterop.org/echoheader/', 'ClientID', $options['user']),
						new SoapHeader('http://soapinterop.org/echoheader/', 'Password', $options['pass'])
					);
					$client->__setSoapHeaders($authHeaders);

					$properties = $client->__soapCall('GetCommercialProperties', $params);

					foreach ( $properties as $property )
					{
						$this->property_ids[] = $property->ID;
					}
				}
			}
		}
		catch (SoapFault $fault) 
		{
		    $this->add_log("SOAP Error whist getting commercial lettings properties (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})");

		    // return false if an error is received that isn't relating to invalid method as likely be to http error or similar
		    if ( strpos($fault->faultstring, 'not a valid method') === FALSE )
		    {
		    	die();
		    }
		}

		return true;
	}

	public function import( $import_id = '' )
	{
		global $wpdb;

		$imported_ref_key = ( ( $import_id != '' ) ? '_imported_ref_' . $import_id : '_imported_ref' );

		$import_start_time = date("Y-m-d");

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

        // get array of users for negotiator mapping
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
                $negotiators[$user->ID] = array(
                    'display_name' => $user->display_name
                );
            }
        }

        do_action( "propertyhive_pre_import_properties_jet", $this->properties );
        $this->properties = apply_filters( "propertyhive_jet_properties_due_import", $this->properties );

		$this->add_log( 'Beginning to loop through ' . count($this->properties) . ' properties' );

		$property_row = 1;
		foreach ( $this->properties as $property )
		{
			$this->add_log( 'Importing property ' . $property_row . ' with reference ' . $property->ID, $property->ID );

			$inserted_updated = false;

			$args = array(
	            'post_type' => 'property',
	            'posts_per_page' => 1,
	            'post_status' => 'any',
	            'meta_query' => array(
	            	array(
		            	'key' => $imported_ref_key,
		            	'value' => $property->ID
		            )
	            )
	        );
	        $property_query = new WP_Query($args);

        	$display_address = '';
        	if ( isset($property->Address1) && $property->Address1 != '')
        	{
        		if ($display_address != '') { $display_address .= ', '; }
        		$display_address .= $property->Address1;
        	}
        	if ( isset($property->Address2) && $property->Address2 != '')
        	{
        		if ($display_address != '') { $display_address .= ', '; }
        		$display_address .= $property->Address2;
        	}
        	elseif ( isset($property->Address3) && $property->Address3 != '')
        	{
        		if ($display_address != '') { $display_address .= ', '; }
        		$display_address .= $property->Address3;
        	}
	        
	        if ($property_query->have_posts())
	        {
	        	$this->add_log( 'This property has been imported before. Updating it', $property->ID );

	        	// We've imported this property before
	            while ($property_query->have_posts())
	            {
	                $property_query->the_post();

	                $post_id = get_the_ID();

	                $my_post = array(
				    	'ID'          	 => $post_id,
				    	'post_title'     => wp_strip_all_tags( $display_address ),
				    	'post_excerpt'   => utf8_encode(str_replace( array("\r\n", "\r", "\n"), "<br>", ( (isset($property->Strapline)) ? $property->Strapline : '' ) )),
				    	'post_content' 	 => '',
				    	'post_status'    => 'publish',
				  	);

				 	// Update the post into the database
				    $post_id = wp_update_post( $my_post );

				    if ( is_wp_error( $post_id ) ) 
					{
						$this->add_error( 'Failed to update post. The error was as follows: ' . $post_id->get_error_message(), $property->ID );
					}
					else
					{
						$inserted_updated = 'updated';
					}
	            }
	        }
	        else
	        {
	        	$this->add_log( 'This property hasn\'t been imported before. Inserting it', $property->ID );

	        	// We've not imported this property before
				$postdata = array(
					'post_excerpt'   => utf8_encode(str_replace( array("\r\n", "\r", "\n"), "<br>", ( (isset($property->Strapline)) ? $property->Strapline : '' ) )),
					'post_content' 	 => '',
					'post_title'     => wp_strip_all_tags( $display_address ),
					'post_status'    => 'publish',
					'post_type'      => 'property',
					'comment_status' => 'closed',
				);

				$post_id = wp_insert_post( $postdata, true );

				if ( is_wp_error( $post_id ) ) 
				{
					$this->add_error( 'Failed to insert post. The error was as follows: ' . $post_id->get_error_message(), $property->ID );
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
					($display_address != '' || ( (isset($property->Strapline)) ? $property->Strapline : '' ) != '')
				)
				{
					$my_post = array(
				    	'ID'          	 => $post_id,
				    	'post_title'     => htmlentities(mb_convert_encoding(wp_strip_all_tags( $display_address ), 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
				    	'post_excerpt'   => htmlentities(mb_convert_encoding(str_replace( array("\r\n", "\r", "\n"), "<br>", ( (isset($property->Strapline)) ? $property->Strapline : '' ) ), 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8"),
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

				$this->add_log( 'Successfully ' . $inserted_updated . ' post. The post ID is ' . $post_id, $property->ID );

				update_post_meta( $post_id, $imported_ref_key, $property->ID );

				// Address
				update_post_meta( $post_id, '_reference_number', $property->ID );
				update_post_meta( $post_id, '_address_name_number', trim( ( (isset($property->HouseName)) ? $property->HouseName : '' ) . ' ' . ( (isset($property->HouseNumber)) ? $property->HouseNumber : '' ) ) );
				update_post_meta( $post_id, '_address_street', ( (isset($property->Address1)) ? $property->Address1 : '' ) );
				update_post_meta( $post_id, '_address_two', ( (isset($property->Address2)) ? $property->Address2 : '' ) );
				update_post_meta( $post_id, '_address_three', ( (isset($property->Address3)) ? $property->Address3 : '' ) );
				update_post_meta( $post_id, '_address_four', ( (isset($property->Address4)) ? $property->Address4 : '' ) );
				update_post_meta( $post_id, '_address_postcode', ( (isset($property->Postcode)) ? $property->Postcode : '' ) );

				$country = ( (isset($property->Country)) ? $property->Country : get_option( 'propertyhive_default_country', 'GB' ) );
				update_post_meta( $post_id, '_address_country', $country );

				// Check main address fields and see if this location exists as a taxonomy to try and assign properties to location
            	$address_fields_to_check = apply_filters( 'propertyhive_jet_address_fields_to_check', array('Address2', 'Address3', 'Address4') );
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
				if ( isset($property->Latitude) && isset($property->Longitude) && (string)$property->Latitude != '' && (string)$property->Longitude != '' && (string)$property->Latitude != '0' && (string)$property->Longitude != '0' )
				{
					update_post_meta( $post_id, '_latitude', ( ( isset($property->Latitude) ) ? (string)$property->Latitude : '' ) );
					update_post_meta( $post_id, '_longitude', ( ( isset($property->Longitude) ) ? (string)$property->Longitude : '' ) );
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
								if ( isset($property->HouseName) && $property->HouseName != '' ) { $address_to_geocode[] = $property->HouseName; }
								if ( isset($property->HouseNumber) && $property->HouseNumber != '' ) { $address_to_geocode[] = $property->HouseNumber; }
								if ( isset($property->Address1) && $property->Address1 != '' ) { $address_to_geocode[] = $property->Address1; }
								if ( isset($property->Address2) && $property->Address2 != '' ) { $address_to_geocode[] = $property->Address2; }
								if ( isset($property->Address3) && $property->Address3 != '' ) { $address_to_geocode[] = $property->Address3; }
								if ( isset($property->Address4) && $property->Address4 != '' ) { $address_to_geocode[] = $property->Address4; }
								if ( isset($property->Postcode) && $property->Postcode != '' ) { $address_to_geocode[] = $property->Postcode; }

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
							        	$this->add_error( 'Google Geocoding service returned status ' . $status, $property->ID );
							        	sleep(3);
							        }
						        }
						        else
						        {
						        	$this->add_error( 'Failed to parse XML response from Google Geocoding service.', $property->ID );
						        }
							}
							else
					        {
					        	$this->add_error( 'Failed to obtain co-ordinates as allow_url_fopen setting is disabled', $property->ID );
					        }
					    }
					    else
					    {
					    	$this->add_log( 'Not performing Google Geocoding request as no API key present in settings', $property->ID );
					    }
					}
				}

				// Owner
				add_post_meta( $post_id, '_owner_contact_id', '', true );

				// Record Details
				$negotiator_id = get_current_user_id();
				// Check if negotiator exists with this name
				if ( isset($property->Negotiator->Name) )
				{
					foreach ( $negotiators as $negotiator_key => $negotiator )
					{
						if ( strtolower(trim($negotiator['display_name'])) == strtolower(trim( (string)$property->Negotiator->Name )) )
						{
							$negotiator_id = $negotiator_key;
						}
					}
				}
				update_post_meta( $post_id, '_negotiator_id', $negotiator_id );
					
				$office_id = $primary_office_id;

				$jet_office_id = '';
				if ( isset($property->MarketingOffice) )
				{
					if ( is_array($property->MarketingOffice) && isset($property->MarketingOffice[0]) && isset($property->MarketingOffice[0]->ID) && $property->MarketingOffice[0]->ID != '' )
					{
						$jet_office_id = $property->MarketingOffice[0]->ID;
					}
				}
				if ( $jet_office_id == '' )
				{
					$jet_office_id = ( ( isset($property->Office->ID) ) ? $property->Office->ID : '' );
				}

				if ( isset($_POST['mapped_office'][$jet_office_id]) && $_POST['mapped_office'][$jet_office_id] != '' )
				{
					$office_id = $_POST['mapped_office'][$jet_office_id];
				}
				elseif ( isset($options['offices']) && is_array($options['offices']) && !empty($options['offices']) )
				{
					foreach ( $options['offices'] as $ph_office_id => $branch_code )
					{
						if ( $branch_code == $jet_office_id )
						{
							$office_id = $ph_office_id;
							break;
						}
					}
				}
				update_post_meta( $post_id, '_office_id', $office_id );

				$department = $property->department;

				// Residential Details
				update_post_meta( $post_id, '_department', $department );

				if ( $department == 'residential-sales' || $department == 'residential-lettings' )
				{
					update_post_meta( $post_id, '_bedrooms', ( ( isset($property->TotalBedrooms) ) ? $property->TotalBedrooms : ( ( isset($property->Bedrooms) ) ? $property->Bedrooms : '' ) ) );
					update_post_meta( $post_id, '_bathrooms', ( ( isset($property->Bathrooms) ) ? $property->Bathrooms : '' ) );
					update_post_meta( $post_id, '_reception_rooms', ( ( isset($property->ReceptionRooms) ) ? $property->ReceptionRooms : '' ) );

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

		            if ( isset($property->Type) )
		            {	
		            	$type_term_ids = array();
			            $types_to_check = array();

			            foreach ( $property->Type as $type )
			            {
			            	$types_to_check[] = $type;

			            	if ( isset($property->Style) )
			            	{
			            		foreach ( $property->Style as $style )
			            		{
			            			$types_to_check[] = $type . ' - ' . $style;
			            		}
			            	}
			            }

			            foreach ( $types_to_check as $type )
			            {
			            	if ( !empty($mapping) && isset($mapping[$type]) )
							{
					            $type_term_ids[] = $mapping[$type];
				            }
				            else
							{
								$this->add_log( 'Property received with a type (' . $type . ') that is not mapped', $property->ID );

								$options = $this->add_missing_mapping( $mapping, 'property_type', $type, $import_id );
							}
			            }

			            if ( !empty($type_term_ids) )
			            {
				            wp_set_post_terms( $post_id, $type_term_ids, 'property_type' );
				        }
			        }
				}

				// Residential Sales Details
				if ( $department == 'residential-sales' )
				{
					$price = ( ( isset($property->SalePrice) ) ? $property->SalePrice : '' );
					update_post_meta( $post_id, '_price', $price );
					update_post_meta( $post_id, '_price_actual', $price );

					$poa = '';
					if ( isset($property->PriceQualifier) && $property->PriceQualifier == 'PA' )
					{
						$poa = 'yes';
					}
					update_post_meta( $post_id, '_poa', $poa );

					// Price Qualifier
					$price_qualifier = ( ( isset($property->PriceQualifier) ) ? $property->PriceQualifier : '' );

					if ( isset($_POST['mapped_price_qualifier']) )
					{
						$mapping = $_POST['mapped_price_qualifier'];
					}
					else
					{
						$mapping = isset($options['mappings']['price_qualifier']) ? $options['mappings']['price_qualifier'] : array();
					}

					wp_delete_object_term_relationships( $post_id, 'price_qualifier' );

					if ( $price_qualifier != '' )
					{
						if ( !empty($mapping) && isset($mapping[$price_qualifier]) )
						{
				            wp_set_post_terms( $post_id, $mapping[$price_qualifier], 'price_qualifier' );
			            }
			            else
						{
							$this->add_log( 'Property received with a price qualifier (' . $price_qualifier . ') that is not mapped', $property->ID );

							$options = $this->add_missing_mapping( $mapping, 'price_qualifier', $price_qualifier, $import_id );
						}
					}

		            // Tenure
		            $tenure = ( ( isset($property->Tenure) ) ? $property->Tenure : '' );

		            if ( isset($_POST['mapped_tenure']) )
					{
						$mapping = $_POST['mapped_tenure'];
					}
					else
					{
						$mapping = isset($options['mappings']['tenure']) ? $options['mappings']['tenure'] : array();
					}

		            wp_delete_object_term_relationships( $post_id, 'tenure' );
					if ( !empty($mapping) && isset($mapping[$tenure]) )
					{
			            wp_set_post_terms( $post_id, $mapping[$tenure], 'tenure' );
		            }
				}
				elseif ( $department == 'residential-lettings' )
				{
					$weekly_rent = ( ( isset($property->WeeklyRent) ) ? $property->WeeklyRent : '' );
					$price = $weekly_rent;
					$jet_rent_frequency = ( ( isset($property->RentalPeriod) ) ? $property->RentalPeriod : 'month' );
					
					$rent_frequency = 'pw';

					switch ($jet_rent_frequency)
					{
						case "week":
						{
							$rent_frequency = 'pw';
							$price = $weekly_rent;
							$price_actual = ($weekly_rent * 52) / 12;
							break;
						}
						case "month":
						{
							$rent_frequency = 'pcm';
							$price = ($weekly_rent * 52) / 12;
							$price_actual = ($weekly_rent * 52) / 12;
							break;
						}
						case "year":
						{
							$rent_frequency = 'pa';
							$price = ($weekly_rent * 52);
							$price_actual = ($weekly_rent * 52) / 12;
							break;
						}
					}

					update_post_meta( $post_id, '_rent', round($price) );
					update_post_meta( $post_id, '_rent_frequency', $rent_frequency );
					update_post_meta( $post_id, '_price_actual', $price_actual );
					
					$poa = '';
					if ( isset($property->PriceQualifier) && $property->PriceQualifier == 'PA' )
					{
						$poa = 'yes';
					}
					update_post_meta( $post_id, '_poa', $poa );

					update_post_meta( $post_id, '_deposit', '' );
            		update_post_meta( $post_id, '_available_date', ( ( isset($property->AvailableFrom) ) ? $property->AvailableFrom : '' ) ); // Need to do. Think we do receive this

            		// Furnished
            		$furnished = ( ( isset($property->Furnish) ) ? $property->Furnish : '' );

            		if ( isset($_POST['mapped_furnished']) )
					{
						$mapping = $_POST['mapped_furnished'];
					}
					else
					{
						$mapping = isset($options['mappings']['furnished']) ? $options['mappings']['furnished'] : array();
					}

            		wp_delete_object_term_relationships( $post_id, 'furnished' );
					if ( !empty($mapping) && isset($mapping[$furnished]) )
					{
		                wp_set_post_terms( $post_id, $mapping[$furnished], 'furnished' );
		            }
				}
				elseif ( $department == 'commercial' )
				{
					update_post_meta( $post_id, '_for_sale', '' );
            		update_post_meta( $post_id, '_to_rent', '' );

            		if ( isset($property->WeeklyRent) && $property->WeeklyRent != '' && $property->WeeklyRent != 0 )
	                {
	                	update_post_meta( $post_id, '_to_rent', 'yes' );

	                	$currency = ( ( isset($property->Currency) && strlen($property->Currency) == 3 ) ? strtoupper($property->Currency) : 'GBP' );
	                    update_post_meta( $post_id, '_commercial_rent_currency', $currency );

	                    $rent = preg_replace("/[^0-9.]/", '', $property->WeeklyRent);
	                    $rent_frequency = 'pw';
	                    switch ($property->RentalPeriod)
	                    {
	                    	case "month": { $rent = ($rent * 52) / 12; $rent_frequency = 'pcm'; break; }
	                    	case "year": { $rent = $rent * 52; $rent_frequency = 'pa'; break; }
	                    }
	                    update_post_meta( $post_id, '_rent_from', round($rent, 2) );
	                    update_post_meta( $post_id, '_rent_to', round($rent, 2) );

	                    update_post_meta( $post_id, '_rent_units', $rent_frequency);

	                    $poa = '';
						if ( isset($property->PriceQualifier) && $property->PriceQualifier == 'PA' )
						{
							$poa = 'yes';
						}
	                    update_post_meta( $post_id, '_rent_poa', $poa );
	                }
	                if ( isset($property->SalePrice) && $property->SalePrice != '' && $property->SalePrice != 0 )
	                {
	                    update_post_meta( $post_id, '_for_sale', 'yes' );

	                    $currency = ( ( isset($property->Currency) && strlen($property->Currency) == 3 ) ? strtoupper($property->Currency) : 'GBP' );
	                    update_post_meta( $post_id, '_commercial_price_currency', $currency );

	                    $price = preg_replace("/[^0-9.]/", '', $property->SalePrice);
	                    update_post_meta( $post_id, '_price_from', $price );
	                    update_post_meta( $post_id, '_price_to', $price );

	                    update_post_meta( $post_id, '_price_units', '' );

	                    $poa = '';
						if ( isset($property->PriceQualifier) && $property->PriceQualifier == 'PA' )
						{
							$poa = 'yes';
						}
	                    update_post_meta( $post_id, '_price_poa', $poa );

	                    // Tenure
			            $tenure = ( ( isset($property->Tenure) ) ? $property->Tenure : '' );

			            if ( isset($_POST['mapped_commercial_tenure']) )
						{
							$mapping = $_POST['mapped_commercial_tenure'];
						}
						else
						{
							$mapping = isset($options['mappings']['commercial_tenure']) ? $options['mappings']['commercial_tenure'] : array();
						}

			            wp_delete_object_term_relationships( $post_id, 'commercial_tenure' );
						if ( !empty($mapping) && isset($mapping[$tenure]) )
						{
				            wp_set_post_terms( $post_id, $mapping[$tenure], 'commercial_tenure' );
			            }
	                }

	                // Store price in common currency (GBP) used for ordering
		            $ph_countries = new PH_Countries();
		            $ph_countries->update_property_price_actual( $post_id );

		            $size = ( ( isset($property->Size) && $property->Size != 0 ) ? preg_replace("/[^0-9.]/", '', $property->Size) : '' );
		            $size_unit = 'sqft';
		            update_post_meta( $post_id, '_floor_area_from', $size );
		            update_post_meta( $post_id, '_floor_area_from_sqft', convert_size_to_sqft( $size, $size_unit ) );
		            update_post_meta( $post_id, '_floor_area_to', $size );
		            update_post_meta( $post_id, '_floor_area_to_sqft', convert_size_to_sqft( $size, $size_unit ) );
		            update_post_meta( $post_id, '_floor_area_units', $size_unit );

		            $size = '';
		            update_post_meta( $post_id, '_site_area_from', $size );
		            update_post_meta( $post_id, '_site_area_from_sqft', convert_size_to_sqft( $size, $size_unit ) );
		            update_post_meta( $post_id, '_site_area_to', $size );
		            update_post_meta( $post_id, '_site_area_to_sqft', convert_size_to_sqft( $size, $size_unit ) );
		            update_post_meta( $post_id, '_site_area_units', $size_unit );

		            // Property Type
		            wp_delete_object_term_relationships( $post_id, 'commercial_property_type' );
		            if ( isset($property->Type) )
		            {	
		            	if ( isset($_POST['mapped_commercial_property_type']) )
						{
							$mapping = $_POST['mapped_commercial_property_type'];
						}
						else
						{
							$mapping = isset($options['mappings']['commercial_property_type']) ? $options['mappings']['commercial_property_type'] : array();
						}

		            	$type_term_ids = array();
		            	$types = $property->Type;
		            	if ( !is_array($property->Type) )
		            	{
		            		$types = array($property->Type);
		            	}
		            	foreach ( $types as $type )
		            	{
		            		if ( !empty($mapping) && isset($mapping[$type]) )
							{
					            $type_term_ids[] = $mapping[$type];
				            }
				            else
							{
								$this->add_log( 'Property received with a type (' . $type . ') that is not mapped', $property->ID );

								$options = $this->add_missing_mapping( $mapping, 'commercial_property_type', $type, $import_id );
							}
		            	}
		            	if ( !empty($type_term_ids) )
		            	{
					        wp_set_post_terms( $post_id, $type_term_ids, 'commercial_property_type' );
		            	}
		            }
				}

				$currency = ( ( isset($property->Currency) && strlen($property->Currency) == 3 ) ? strtoupper($property->Currency) : 'GBP' );
				update_post_meta( $post_id, '_currency', $currency );

				// Marketing
				update_post_meta( $post_id, '_on_market', ( (isset($property->MarketedOnInternet) && $property->MarketedOnInternet == '1') ? 'yes' : '' ) );
				update_post_meta( $post_id, '_featured', ( (isset($property->Featured) && $property->Featured == '1') ? 'yes' : '' ) );

				// Availability
				$availability = ( ( isset($property->Status) ) ? $property->Status : '' );
				if ( $availability == '' && ( $department == 'residential-sales' || ($department == 'commercial' && isset($property->SalePrice) && $property->SalePrice != '' && $property->SalePrice != 0 ) ) )
				{
					$availability = 'For Sale';
				}
				elseif ( $availability == '' && ( $department == 'residential-lettings' || ($department == 'commercial' && isset($property->WeeklyRent) && $property->WeeklyRent != '' && $property->WeeklyRent != 0 ) ) )
				{
					$availability = 'To Let';
				}

				if ( isset($_POST['mapped_availability']) )
				{
					$mapping = $_POST['mapped_availability'];
				}
				else
				{
					$mapping = isset($options['mappings']['availability']) ? $options['mappings']['availability'] : array();
				}

        		wp_delete_object_term_relationships( $post_id, 'availability' );

        		if ( $availability != '' )
        		{
					if ( !empty($mapping) && isset($mapping[$availability]) )
					{
		                wp_set_post_terms( $post_id, $mapping[$availability], 'availability' );
		            }
		            else
					{
						$this->add_log( 'Property received with an availability (' . $availability . ') that is not mapped', $property->ID );

						$options = $this->add_missing_mapping( $mapping, 'availability', $availability, $import_id );
					}
		        }

	            // Features
				$features = array();

				if ( isset($property->AccommodationSummary) && is_array($property->AccommodationSummary) && !empty($property->AccommodationSummary) )
				{
					$features = $property->AccommodationSummary;
				}
				
				update_post_meta( $post_id, '_features', count( $features ) );
        		
        		$i = 0;
		        foreach ( $features as $feature )
		        {
		            update_post_meta( $post_id, '_feature_' . $i, $feature );
		            ++$i;
		        }

		        // Rooms
	            $new_room_count = 0;

	            if ( isset($property->Description) && (string)$property->Description != '' )
	            {
	            	if ( $department == 'commercial' )
	            	{
	            		update_post_meta( $post_id, '_description_name_' . $new_room_count, '' );
			            update_post_meta( $post_id, '_description_' . $new_room_count, ( (isset($property->Description) ) ? (string)$property->Description : '' ) );
	            	}
	            	else
	            	{
		            	update_post_meta( $post_id, '_room_name_' . $new_room_count, '' );
			            update_post_meta( $post_id, '_room_dimensions_' . $new_room_count, '' );
			            update_post_meta( $post_id, '_room_description_' . $new_room_count, ( (isset($property->Description) ) ? (string)$property->Description : '' ) );
			        }

            		++$new_room_count;
	            }

	            if ( isset($property->LongDesc) && (string)$property->LongDesc != '' )
	            {
	            	update_post_meta( $post_id, '_room_name_' . $new_room_count, '' );
		            update_post_meta( $post_id, '_room_dimensions_' . $new_room_count, '' );
		            update_post_meta( $post_id, '_room_description_' . $new_room_count, ( (isset($property->LongDesc) ) ? (string)$property->LongDesc : '' ) );

            		++$new_room_count;
	            }

	            if ( isset($property->Room) )
	            {
	            	foreach ( $property->Room as $room )
	            	{
	            		update_post_meta( $post_id, '_room_name_' . $new_room_count, ( (isset($room->Name) ) ? (string)$room->Name : '' ) );
			            update_post_meta( $post_id, '_room_dimensions_' . $new_room_count, ( (isset($room->Size) ) ? (string)$room->Size : '' ) );
			            update_post_meta( $post_id, '_room_description_' . $new_room_count, ( (isset($room->Description) ) ? (string)$room->Description : '' ) );

	            		++$new_room_count;
	            	}
	            }

	            if ( $department == 'commercial' )
	            {
	            	update_post_meta( $post_id, '_descriptions', $new_room_count );
	            }
	            else
	            {
	            	update_post_meta( $post_id, '_rooms', $new_room_count );
	            }

	            // Media - Images
	            if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();
    				if (isset($property->Image) && !empty($property->Image))
	                {
	                    foreach ($property->Image as $image)
	                    {
							if ( 
								substr( strtolower($image->Filepath), 0, 2 ) == '//' || 
								substr( strtolower($image->Filepath), 0, 4 ) == 'http'
							)
							{
								// This is a URL
								$url = $image->Filepath;

								$media_urls[] = array('url' => $url);
							}
						}
					}
					update_post_meta( $post_id, '_photo_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' photo URLs', $property->ID );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_photos', TRUE );
					if (isset($property->Image) && !empty($property->Image))
	                {
	                    foreach ($property->Image as $image)
	                    {
							if ( 
								substr( strtolower($image->Filepath), 0, 2 ) == '//' || 
								substr( strtolower($image->Filepath), 0, 4 ) == 'http'
							)
							{
								// This is a URL
								$url = $image->Filepath;
								$description = ( ( isset($image->Caption) ) ? $image->Caption : '' );
							    
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

								        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property->ID );
								    }
								    else
								    {
									    $id = media_handle_sideload( $file_array, $post_id, $description, array('post_title' => $filename) );

									    // Check for handle sideload errors.
									    if ( is_wp_error( $id ) ) 
									    {
									        @unlink( $file_array['tmp_name'] );
									        
									        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property->ID );
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

					$this->add_log( 'Imported ' . count($media_ids) . ' photos (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property->ID );
				}

				// Media - Floorplans
				if ( get_option('propertyhive_floorplans_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();
    				if (isset($property->Floorplan) && !empty($property->Floorplan))
	                {
	                    foreach ($property->Floorplan as $floorplan)
	                    {
							if ( 
								substr( strtolower($floorplan->Filepath), 0, 2 ) == '//' || 
								substr( strtolower($floorplan->Filepath), 0, 4 ) == 'http'
							)
							{
								// This is a URL
								$url = $floorplan->Filepath;

								$media_urls[] = array('url' => $url);
							}
						}
					}
					update_post_meta( $post_id, '_floorplan_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' floorplan URLs', $property->ID );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_floorplans', TRUE );
					if (isset($property->Floorplan) && !empty($property->Floorplan))
	                {
	                    foreach ($property->Floorplan as $floorplan)
	                    {
							if ( 
								substr( strtolower($floorplan->Filepath), 0, 2 ) == '//' || 
								substr( strtolower($floorplan->Filepath), 0, 4 ) == 'http'
							)
							{
								// This is a URL
								$url = $floorplan->Filepath;
								$description = ( ( isset($floorplan->Caption) ) ? $floorplan->Caption : '' );
							    
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

								        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property->ID );
								    }
								    else
								    {
									    $id = media_handle_sideload( $file_array, $post_id, $description, array('post_title' => $filename) );

									    // Check for handle sideload errors.
									    if ( is_wp_error( $id ) ) 
									    {
									        @unlink( $file_array['tmp_name'] );
									        
									        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property->ID );
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

					$this->add_log( 'Imported ' . count($media_ids) . ' floorplans (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property->ID );
				}

				// Media - Brochure
				if ( get_option('propertyhive_brochures_stored_as', '') == 'urls' )
    			{
    				// Need to work out how to store brochure received from SOAP call as URL. Maybe we still have to download it?
    				
    				$media_urls = array();
    				
					update_post_meta( $post_id, '_brochure_urls', $media_urls );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_brochures', TRUE );
					if ( isset($property->PDF) && isset($property->PDFAmendTime) && (string)$property->PDFAmendTime != '' )
					{
						$last_amended = get_post_meta( $post_id, '_jet_pdf_amended_date_time', TRUE );

						if ( $last_amended == '' || strtotime($last_amended) != strtotime((string)$property->PDFAmendTime) )
						{
							try {
								$client = new SoapClient($options['url']);

								$authHeaders = array(
									new SoapHeader('http://soapinterop.org/echoheader/', 'ClientID', $options['user']),
									new SoapHeader('http://soapinterop.org/echoheader/', 'Password', $options['pass'])
								);
								$client->__setSoapHeaders($authHeaders);

								$params = array('ID' => $property->ID);

								$pdf = $client->__soapCall('GetPropertyPDF', $params);

								$filename = $property->ID . '.pdf';

								// Convert received data into file
								$upload_dir = wp_upload_dir();

								$upload_path = str_replace( '/', DIRECTORY_SEPARATOR, $upload_dir['path'] ) . DIRECTORY_SEPARATOR;

								$tmp_filename = md5( $filename . microtime() ) . '_' . $filename;

								$image_upload = file_put_contents( $upload_path . $tmp_filename, $pdf );

								$file_array = array(
							        'name' => $filename,
							        'tmp_name' => $upload_path . $tmp_filename
							    );

							    // Check for download errors
							    $id = media_handle_sideload( $file_array, $post_id, '', array('post_title' => $filename) );

							    // Check for handle sideload errors.
							    if ( is_wp_error( $id ) ) 
							    {
							        @unlink( $file_array['tmp_name'] );
							        
							        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property->ID );

							        $media_ids = $previous_media_ids;
							    }
							    else
							    {
							    	$media_ids[] = $id;

							    	update_post_meta( $post_id, '_jet_pdf_amended_date_time', (string)$property->PDFAmendTime );

							    	++$new;
							    }

							    @unlink($tmp_filename);
							}
							catch (SoapFault $fault) 
							{
							    $this->add_error("SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", $property->ID );

							    $media_ids = $previous_media_ids;
							}
						}
						else
						{
							// PDF not been modified
							$media_ids = $previous_media_ids;

							++$existing;

							if ( (!is_array($media_ids) && $media_ids == '') || ( is_array($media_ids) && empty($media_ids) ) )
							{
								// if for some reason we're here and no brochures exist then reset amended time for next time the import runs
								update_post_meta( $post_id, '_jet_pdf_amended_date_time', '' );
							}
						}
					}
					else
					{
						// No PDF received
						update_post_meta( $post_id, '_jet_pdf_amended_date_time', '' );
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

					$this->add_log( 'Imported ' . count($media_ids) . ' brochures (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property->ID );
				}

				// Media - EPCs
				if ( get_option('propertyhive_epcs_stored_as', '') == 'urls' )
    			{
    				$media_urls = array();

    				if (isset($property->EPC) && !empty($property->EPC))
	                {
	                    foreach ($property->EPC as $epc)
	                    {
							if ( 
								substr( strtolower($epc->Filepath), 0, 2 ) == '//' || 
								substr( strtolower($epc->Filepath), 0, 4 ) == 'http'
							)
							{
								// This is a URL
								$url = $epc->Filepath;

								$media_urls[] = array('url' => $url);
							}
						}
					}
					update_post_meta( $post_id, '_epc_urls', $media_urls );

					$this->add_log( 'Imported ' . count($media_urls) . ' EPC URLs', $property->ID );
    			}
    			else
    			{
					$media_ids = array();
					$new = 0;
					$existing = 0;
					$deleted = 0;
					$previous_media_ids = get_post_meta( $post_id, '_epcs', TRUE );
					if (isset($property->EPC) && !empty($property->EPC))
	                {
	                    foreach ($property->EPC as $epc)
	                    {
							if ( 
								substr( strtolower($epc->Filepath), 0, 2 ) == '//' || 
								substr( strtolower($epc->Filepath), 0, 4 ) == 'http'
							)
							{
								// This is a URL
								$url = $epc->Filepath;
								$description = ( ( isset($epc->Caption) ) ? $epc->Caption : '' );
							    
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

								        $this->add_error( 'An error occurred whilst importing ' . $url . '. The error was as follows: ' . $tmp->get_error_message(), $property->ID );
								    }
								    else
								    {
									    $id = media_handle_sideload( $file_array, $post_id, $description, array('post_title' => $filename) );

									    // Check for handle sideload errors.
									    if ( is_wp_error( $id ) ) 
									    {
									        @unlink( $file_array['tmp_name'] );
									        
									        $this->add_error( 'ERROR: An error occurred whilst importing ' . $url . '. The error was as follows: ' . $id->get_error_message(), $property->ID );
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

					$this->add_log( 'Imported ' . count($media_ids) . ' EPCs (' . $new . ' new, ' . $existing . ' existing, ' . $deleted . ' deleted)', $property->ID );
				}

				// Media - Virtual Tours
				$updated_virtual_tour = false;
				if ( isset($property->VTour) && (string)$property->VTour != '' )
                {
					if ( 
						substr( strtolower((string)$property->VTour), 0, 2 ) == '//' || 
						substr( strtolower((string)$property->VTour), 0, 4 ) == 'http'
					)
					{
						// This is a URL
						update_post_meta($post_id, '_virtual_tours', 1);
				        update_post_meta($post_id, '_virtual_tour_0', (string)$property->VTour);

				        $updated_virtual_tour = true;
					}
				}
				if ( $updated_virtual_tour == false )
				{
					update_post_meta($post_id, '_virtual_tours', 0);
				}

				do_action( "propertyhive_property_imported_jet", $post_id, $property );

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
							$this->add_log( 'New meta data for ' . trim($key, '_') . ': ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), (string)$property->ID );
						}
						elseif ( $metadata_before[$key] != $metadata_after[$key] )
						{
							$this->add_log( 'Updated ' . trim($key, '_') . '. Before: ' . ( ( is_array($metadata_before[$key]) ) ? implode(", ", $metadata_before[$key]) : $metadata_before[$key] ) . ', After: ' . ( ( is_array($value) ) ? implode(", ", $value) : $value ), (string)$property->ID );
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
							$this->add_log( 'New taxonomy data for ' . $taxonomy_name . ': ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), (string)$property->ID );
						}
						elseif ( $taxonomy_terms_before[$taxonomy_name] != $taxonomy_terms_after[$taxonomy_name] )
						{
							$this->add_log( 'Updated ' . $taxonomy_name . '. Before: ' . ( ( is_array($taxonomy_terms_before[$taxonomy_name]) ) ? implode(", ", $taxonomy_terms_before[$taxonomy_name]) : $taxonomy_terms_before[$taxonomy_name] ) . ', After: ' . ( ( is_array($ids) ) ? implode(", ", $ids) : $ids ), (string)$property->ID );
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

		do_action( "propertyhive_post_import_properties_jet" );

		update_option( 'jet_update_date_' . $import_id, $import_start_time, FALSE);

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
		if ( isset($options['only_updated']) && $options['only_updated'] == 'yes' )
		{
			$import_refs = $this->property_ids;
		}
		else
		{
			foreach ($this->properties as $property)
			{
				$import_refs[] = $property->ID;
			}
		}

		$args = array(
			'post_type' => 'property',
			'nopaging' => true,
			'meta_query' => array(
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

		$args = apply_filters( 'propertyhive_jet_remove_query_args', $args );

		$property_query = new WP_Query( $args );
		if ( $property_query->have_posts() )
		{
			$this->add_log( 'Found ' . $property_query->found_posts . ' properties to take off the market' );

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

				do_action( "propertyhive_property_removed_jet", $post->ID );
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

		$mapping_values = $this->get_xml_mapping_values('commercial_tenure');
		if ( is_array($mapping_values) && !empty($mapping_values) )
		{
			foreach ($mapping_values as $mapping_value => $text_value)
			{
				$this->mappings['commercial_tenure'][$mapping_value] = '';
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
                'New Instruction' => 'New Instruction',
                'Under Offer' => 'Under Offer',
            );
        }
        if ($custom_field == 'property_type')
        {
        	return array(
        		'House' => 'House',
                'House - Terraced' => 'House - Terraced',
                'House - End of Terrace' => 'House - End of Terrace',
                'House - Detached' => 'House - Detached',
                'House - Semi Detached' => 'House - Semi Detached',
                'House - Link Detached' => 'House - Link Detached',
                'House - Mews' => 'House - Mews',
                'Bungalow' => 'Bungalow',
                'Bungalow - Terraced' => 'Bungalow - Terraced',
                'Bungalow - End of Terrace' => 'Bungalow - End of Terrace',
                'Bungalow - Detached' => 'Bungalow - Detached',
                'Bungalow - Semi Detached' => 'Bungalow - Semi Detached',
                'Bungalow - Link Detached' => 'Bungalow - Link Detached',
                'Bungalow - Mews' => 'Bungalow - Mews',
                'Flat/Apartment' => 'Flat/Apartment',
                'Flat/Apartment - Mews' => 'Flat/Apartment - Mews',
                'Flat/Apartment - Duplex' => 'Flat/Apartment - Duplex',
                'Flat/Apartment - Basement' => 'Flat/Apartment - Basement',
                'Flat/Apartment - Ground Floor' => 'Flat/Apartment - Ground Floor',
                'Flat/Apartment - First Floor' => 'Flat/Apartment - First Floor',
                'Flat/Apartment - Upper Floor' => 'Flat/Apartment - Upper Floor',
                'Flat/Apartment - Penthouse' => 'Flat/Apartment - Penthouse',
                'Maisonette' => 'Maisonette',
                'Maisonette - Duplex' => 'Maisonette - Duplex',
                'Maisonette - Basement' => 'Maisonette - Basement',
                'Maisonette - Ground Floor' => 'Maisonette - Ground Floor',
                'Maisonette - First Floor' => 'Maisonette - First Floor',
                'Maisonette - Upper Floor' => 'Maisonette - Upper Floor',
                'Maisonette - Penthouse' => 'Maisonette - Penthouse',
                'Land' => 'Land',
                'Farm' => 'Farm',
                'Development Plot' => 'Development Plot',
            );
        }
        if ($custom_field == 'commercial_property_type')
        {
        	return array(
        		'Retail' => 'Retail',
        		'Office' => 'Office',
        		'Industrial' => 'Industrial',
        		'Land / Development' => 'Land / Development',
        		'Restaurant' => 'Restaurant',
        		'Hotel / Guesthouse' => 'Hotel / Guesthouse',
        	);
        }
        if ($custom_field == 'price_qualifier')
        {
        	return array(
        		'AP' => 'Asking Price',
                'PA' => 'Price On Application',
                'GP' => 'Guide Price',
                'OR' => 'Offers In The Region Of',
                'OO' => 'Offers Over',
                'OE' => 'Offers In Excess Of',
                'FP' => 'Fixed Price',
                'PR' => 'Price Reduced To',
        	);
        }
        if ($custom_field == 'tenure')
        {
            return array(
                'Freehold' => 'Freehold',
                'Leasehold' => 'Leasehold',
                'Share of Freehold' => 'Share of Freehold',
            );
        }
        if ($custom_field == 'commercial_tenure')
        {
            return array(
                'Freehold' => 'Freehold',
                'Leasehold' => 'Leasehold',
                'Share of Freehold' => 'Share of Freehold',
            );
        }
        if ($custom_field == 'furnished')
        {
            return array(
            	'Furnished' => 'Furnished',
                'Unfurnished' => 'Unfurnished',
            );
        }
    }

}

}