<?php

error_reporting( 0 );
set_time_limit( 0 );
ini_set('memory_limit','20000M');

global $wpdb, $post;

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

// Check Property Hive Plugin is active as we'll need this
if( is_plugin_active( 'propertyhive/propertyhive.php' ) )
{
    // Delete logs older that WPP_BLM_Export_Keep_Logs_Days days
    $wpdb->query( "DELETE FROM " . $wpdb->prefix . "ph_propertyimport_logs_instance WHERE start_date < DATE_SUB(NOW(), INTERVAL 7 DAY)" );
    $wpdb->query( "DELETE FROM " . $wpdb->prefix . "ph_propertyimport_logs_instance_log WHERE log_date < DATE_SUB(NOW(), INTERVAL 7 DAY)" );

    $import_options = get_option( 'propertyhive_property_import' );
    if ( is_array($import_options) && !empty($import_options) )
    {
	    $wp_upload_dir = wp_upload_dir();
	    $uploads_dir_ok = true;
	    if( $wp_upload_dir['error'] !== FALSE )
	    {
	        echo "Unable to create uploads folder. Please check permissions";
	        $uploads_dir_ok = false;
	    }
	    else
	    {
	        $uploads_dir = $wp_upload_dir['basedir'] . '/ph_import/';

	        if ( ! @file_exists($uploads_dir) )
	        {
	            if ( ! @mkdir($uploads_dir) )
	            {
	                echo "Unable to create directory " . $uploads_dir;
	                $uploads_dir_ok = false;
	            }
	        }
	        else
	        {
	            if ( ! @is_writeable($uploads_dir) )
	            {
	                echo "Directory " . $uploads_dir . " isn't writeable";
	                $uploads_dir_ok = false;
	            }
	        }
	    }

	    if ($uploads_dir_ok)
	    {
	    	foreach ( $import_options as $import_id => $options )
	    	{
		    	$ok_to_run_import = true;

		    	if ($options['running'] != '1')
	            {
	            	$ok_to_run_import = false;
	            	continue;
	            }

	        	if ( !isset($_GET['force']) )
	        	{
	        		// Make sure there's been no activity in the logs for at least 5 minutes for this feed as that indicates there's possible a feed running
		        	$row = $wpdb->get_row( "
		                SELECT 
		                    log_date
		                FROM 
		                    " . $wpdb->prefix . "ph_propertyimport_logs_instance
		                INNER JOIN " .$wpdb->prefix . "ph_propertyimport_logs_instance_log ON " . $wpdb->prefix . "ph_propertyimport_logs_instance.id = " . $wpdb->prefix . "ph_propertyimport_logs_instance_log.instance_id
		                WHERE
		                    import_id = '" . $import_id . "'
		                AND
		                	end_date = '0000-00-00 00:00:00'
		                ORDER BY log_date DESC
		                LIMIT 1
		            ", ARRAY_A);
		            if ( null !== $row )
		            {
		                if ( ( ( strtotime(gmdate("Y-m-d H:i:s")) - strtotime($row['log_date']) ) / 60 ) < 5 )
		                {
		                	$ok_to_run_import = false;

		                	$message = "There has been activity within the past 5 minutes on an unfinished import. To prevent multiple imports running at the same time and possible duplicate properties being created we won't currently allow manual execution. Please try again in a few minutes or check the logs to see the status of the current import.";
		                	
		                	// if we're running it manually
				            if ( isset($_GET['custom_property_import_cron']) )
				            {
				            	echo $message; die();
				            }
				            // if we're running it via CLI
				            if ( defined( 'WP_CLI' ) && WP_CLI )
		                	{
		                		WP_CLI::error( $message );
		                	}

		                	continue;
		                }
		            }
		        }

		    	// Make sure an import instance isn't already running
	            /*$query = "
	                SELECT 
	                    id
	                FROM 
	                    " .$wpdb->prefix . "ph_propertyimport_logs_instance
	                WHERE 
	                    start_date > end_date
	                    AND
	                    import_id = '" . $import_id . "'
	                    AND
	                    DATE_SUB(NOW(), INTERVAL 6 HOUR) < start_date
	            ";
	            $result = $wpdb->get_results( $query );
	            if ( $result )
	            {
	                $ok_to_run_import = false; 
	            }*/

	            if ( isset($_GET['custom_property_import_cron']) || ( defined( 'WP_CLI' ) && WP_CLI ) )
	            {

	            }
	            else
	            {
		            // Work out if we need to send this portal by looking
		            // at the send frequency and the last date sent
		            $last_start_date = '2000-01-01 00:00:00';
		            $row = $wpdb->get_row( "
		                SELECT 
		                    start_date
		                FROM 
		                    " .$wpdb->prefix . "ph_propertyimport_logs_instance
		                WHERE
		                    import_id = '" . $import_id . "'
		                ORDER BY start_date DESC LIMIT 1
		            ", ARRAY_A);
		            if ( null !== $row )
		            {
		                $last_start_date = $row['start_date'];   
		            }

		            $diff_secs = time() - strtotime($last_start_date);

		            switch ($options['import_frequency'])
		            {
		            	case "every_15_minutes":
		                {
		                    if (($diff_secs / 60 / 60) < 0.25)
		                    {
		                        $ok_to_run_import = false;
		                    }
		                    break;
		                }
		                case "hourly":
		                {
		                    if (($diff_secs / 60 / 60) < 1)
		                    {
		                        $ok_to_run_import = false;
		                    }
		                    break;
		                }
		                case "twicedaily":
		                {
		                    if (($diff_secs / 60 / 60) < 12)
		                    {
		                        $ok_to_run_import = false;
		                    }
		                    break;
		                }
		                default: // daily
		                {
		                    if (($diff_secs / 60 / 60) < 24)
		                    {
		                        $ok_to_run_import = false;
		                    }
		                }
		            }
		        }

	            if ($ok_to_run_import)
	            {
		            // log instance start
		            $wpdb->insert( 
		                $wpdb->prefix . "ph_propertyimport_logs_instance", 
		                array(
		                	'import_id' => $import_id,
		                    'start_date' => gmdate("Y-m-d H:i:s")
		                )
		            );
		            $instance_id = $wpdb->insert_id;

			    	$format = $options['format'];

			    	$logs = array();

			    	switch ($format)
			    	{
			    		case "blm_local":
			    		{
			    			$local_directory = $options['local_directory'];

			    			// Get all zip files in date order
			    			$zip_files = array();
							if ($handle = opendir($local_directory)) 
							{
							    while (false !== ($file = readdir($handle))) 
							    {
							        if (
							        	$file != "." && $file != ".." && 
							        	substr(strtolower($file), -3) == 'zip'
							        ) 
							        {
							           $zip_files[filemtime($local_directory . '/' . $file)] = $local_directory . '/' . $file;
							        }
							    }
							    closedir($handle);
							}
							else
							{
								die('Directory ' . $local_directory . ' is either not readable or does not exist');
							}

							if (!empty($zip_files))
							{
								if ( !class_exists('ZipArchive') ) { die('The ZipArchive class does not exist but is needed to extract the zip files provided'); }

								ksort($zip_files);

								foreach ($zip_files as $mtime => $zip_file)
								{
									$zip = new ZipArchive;
									if ($zip->open($zip_file) === TRUE) 
									{
									    $zip->extractTo($local_directory);
									    $zip->close();
									    sleep(1); // We sleep to ensure each BLM has a different modified time in the same order
									}
									else
									{
										// log
									}
									unlink($zip_file);
								}
							}

							unset($zip_files);

			    			// Now they've all been extracted, get BLM files in date order
							$blm_files = array();
							if ($handle = opendir($local_directory)) 
							{
							    while (false !== ($file = readdir($handle))) 
							    {
							        if (
							        	$file != "." && $file != ".." && 
							        	substr(strtolower($file), -3) == 'blm'
							        ) 
							        {
							           $blm_files[filemtime($local_directory . '/' . $file)] = $local_directory . '/' . $file;
							        }
							    }
							    closedir($handle);
							}

							if (!empty($blm_files))
							{
								ksort($blm_files);

								// We've got at least one BLM to process

				                // includes
		                        require_once dirname( __FILE__ ) . '/includes/class-ph-blm-import.php';

		                        foreach ($blm_files as $mtime => $blm_file)
		                        {
			                        $PH_BLM_Import = new PH_BLM_Import( $blm_file, $instance_id );

			                        $parsed = $PH_BLM_Import->parse();

			                        if ( $parsed !== FALSE )
			                        {
			                        	// Parsed it succesfully. Ok to continue
				                        $PH_BLM_Import->import( $import_id );

					                    $PH_BLM_Import->remove_old_properties( $import_id, (!isset($options['dont_remove']) || $options['dont_remove'] != '1') ?  true : false );
					                }

					                $PH_BLM_Import->archive( $import_id );
			                    }
							}

							// Clean up processed .BLMs and unused media older than 7 days old (7 days = 604800 seconds)
							if ($handle = opendir($local_directory)) 
							{
							    while (false !== ($file = readdir($handle))) 
							    {
							        if (
							        	$file != "." && $file != ".." && 
							        	(
							        		substr($file, -9) == 'processed' || 
							        		substr(strtolower($file), -4) == '.jpg' || 
							        		substr(strtolower($file), -4) == '.gif' || 
							        		substr(strtolower($file), -5) == '.jpeg' || 
							        		substr(strtolower($file), -4) == '.png' || 
							        		substr(strtolower($file), -4) == '.bmp' || 
							        		substr(strtolower($file), -4) == '.pdf'
							        	)
							        ) 
							        {
							        	if ( filemtime($local_directory . '/' . $file) !== FALSE && filemtime($local_directory . '/' . $file) < (time() - 604800) )
							        	{
							        		unlink($local_directory . '/' . $file);
							        	}
							        }
							    }
							    closedir($handle);
							}

			    			break;
			    		}
			    		case "blm_remote":
			    		{
			    			$blm_file = $wp_upload_dir['basedir'] . '/ph_import/blm_properties.xml';

			    			$contents = '';

			    			$response = wp_remote_get( $options['url'], array( 'timeout' => 120 ) );
			    			if ( !is_wp_error($response) && is_array( $response ) ) 
							{
								$contents = $response['body'];
							}
				    		else
				    		{
				    			die("Failed to obtain BLM. Dump of response as follows: " . print_r($response, TRUE));
				    		}

			    			$handle = @fopen($blm_file, 'w+');
			    			if ($handle)
			    			{
			    				fwrite($handle, $contents);
			    				fclose($handle);

				                // includes
		                        require_once dirname( __FILE__ ) . '/includes/class-ph-blm-import.php';

			    				$PH_BLM_Import = new PH_BLM_Import( $blm_file, $instance_id );

		                        $parsed = $PH_BLM_Import->parse();

		                        if ( $parsed !== FALSE )
		                        {
		                        	// Parsed it succesfully. Ok to continue
			                        $PH_BLM_Import->import( $import_id );

				                    $PH_BLM_Import->remove_old_properties( $import_id, (!isset($options['dont_remove']) || $options['dont_remove'] != '1') ?  true : false );
				                }

			    				unlink($blm_file);
			    			}			    			
			    			else
				            {
				                echo "Failed to write BLM file locally. Please check file permissions";
				            }
			    			break;
			    		}
			    		case "xml_dezrez":
			    		{
			            	$xml_file = $wp_upload_dir['basedir'] . '/ph_import/dezrez.xml';

			            	$theGuid = uniqid(uniqid(), true);

			                // includes
	                        require_once dirname( __FILE__ ) . '/includes/class-ph-dezrez-xml-import.php';

	                        $PH_Dezrez_XML_Import = new PH_Dezrez_XML_Import( $theGuid, $instance_id );

	                        // Sales Properties
	                        $search_url = 'http://www.dezrez.com/DRApp/DotNetSites/WebEngine/property/Default.aspx';
							$fields = array(
								'apiKey' => urlencode($options['api_key']),
								'eaid' => urlencode($options['eaid']),
								'sessionGUID' => urlencode($theGuid),
								'xslt' => urlencode('-1'),
								'perpage' => 99999,
								'showSTC' => 'true',
								'rentalPeriod' => 0
							);
							if ( isset($options['branch_ids']) && trim($options['branch_ids']) != '' )
							{
								$fields['branchList'] = urlencode(str_replace(' ', '', $options['branch_ids']));
							}

							$fields_string = '';
							foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
							$fields_string = rtrim($fields_string, '&');

							$sales_search_url = $search_url . '?' . $fields_string;

							$contents = '';
							if ( ini_get('allow_url_fopen') )
        					{
				    			$contents = file_get_contents($sales_search_url);
				    		}
				    		elseif ( function_exists('curl_version') )
							{
								$curl = curl_init();
							    curl_setopt($curl, CURLOPT_URL, $sales_search_url);
							    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
							    $contents = curl_exec($curl);
							    curl_close($curl);
				    		}
				    		else
				    		{
				    			die("Neither allow_url_fopen nor cURL is active on your server");
				    		}

							$handle = fopen($xml_file, 'w+');
							fwrite($handle, $contents);
							fclose($handle);

	                        $PH_Dezrez_XML_Import->set_target_xml($xml_file);

	                        $parsed_sales = $PH_Dezrez_XML_Import->parse( $import_id );

	                        //$PH_Dezrez_XML_Import->import( $import_id ); // Don't parse here as we do it later on once we have all properties

	                        // Lettings Properties
	                        $search_url = 'http://www.dezrez.com/DRApp/DotNetSites/WebEngine/property/Default.aspx';
							$fields = array(
								'apiKey' => urlencode($options['api_key']),
								'eaid' => urlencode($options['eaid']),
								'sessionGUID' => urlencode($theGuid),
								'xslt' => urlencode('-1'),
								'perpage' => 99999,
								'showSTC' => 'true',
								'rentalPeriod' => 4
							);
							if ( isset($options['branch_ids']) && trim($options['branch_ids']) != '' )
							{
								$fields['branchList'] = urlencode(str_replace(' ', '', $options['branch_ids']));
							}

							$fields_string = '';
							foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
							$fields_string = rtrim($fields_string, '&');

							$lettings_search_url = $search_url . '?' . $fields_string;

							$contents = '';
							if ( ini_get('allow_url_fopen') )
        					{
				    			$contents = file_get_contents($lettings_search_url);
				    		}
				    		elseif ( function_exists('curl_version') )
							{
								$curl = curl_init();
							    curl_setopt($curl, CURLOPT_URL, $lettings_search_url);
							    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
							    $contents = curl_exec($curl);
							    curl_close($curl);
				    		}
				    		else
				    		{
				    			die("Neither allow_url_fopen nor cURL is active on your server");
				    		}

							$handle = fopen($xml_file, 'w+');
							fwrite($handle, $contents);
							fclose($handle);

	                        $PH_Dezrez_XML_Import->set_target_xml($xml_file);

	                        $parsed_lettings = $PH_Dezrez_XML_Import->parse( $import_id );

	                        if ( $parsed_sales !== FALSE && $parsed_lettings !== FALSE )
	                        {
		                        $PH_Dezrez_XML_Import->import( $import_id );

			                    $PH_Dezrez_XML_Import->remove_old_properties( $import_id, (!isset($options['dont_remove']) || $options['dont_remove'] != '1') ?  true : false );
			                }

			                unlink($xml_file);

			    			break;
			    		}
			    		case "json_dezrez":
			    		{
			            	$json_file = $wp_upload_dir['basedir'] . '/ph_import/dezrez.json';

			                // includes
	                        require_once dirname( __FILE__ ) . '/includes/class-ph-dezrez-json-import.php';

							$PH_Dezrez_JSON_Import = new PH_Dezrez_JSON_Import( $instance_id );

	                        $PH_Dezrez_JSON_Import->parse( $import_id, $options );

	                        $PH_Dezrez_JSON_Import->import( $import_id );

	                        unlink($json_file);

		                    $PH_Dezrez_JSON_Import->remove_old_properties( $import_id, (!isset($options['dont_remove']) || $options['dont_remove'] != '1') ?  true : false );

			    			break;
			    		}
			    		case "xml_expertagent":
			    		{
			    			// Connect to FTP directory and get file
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
				            	$xml_file = $wp_upload_dir['basedir'] . '/ph_import/' . $options['xml_filename'];

				            	// Get file
				            	if ( ftp_get( $ftp_conn, $xml_file, $options['xml_filename'], FTP_ASCII ) )
				            	{
				            		// We've got the file

					                // includes
			                        require_once dirname( __FILE__ ) . '/includes/class-ph-expertagent-xml-import.php';

			                        $PH_ExpertAgent_XML_Import = new PH_ExpertAgent_XML_Import( $xml_file, $instance_id );

			                        $parsed = $PH_ExpertAgent_XML_Import->parse();

			                        if ( $parsed )
			                        {
				                        $PH_ExpertAgent_XML_Import->import( $import_id );

					                    $PH_ExpertAgent_XML_Import->remove_old_properties( $import_id, (!isset($options['dont_remove']) || $options['dont_remove'] != '1') ?  true : false );
				                	}

			                        unlink($xml_file);
				            	}
				            	else
				            	{
				            		echo "Failed to get file " . $options['xml_filename'] . " from FTP directory";
				            	}

				            	ftp_close( $ftp_conn );
				            }
				            else
				            {
				                echo "Incorrect FTP details provided";
				            }

			    			break;
			    		}
			    		case "xml_jupix":
			    		{
			    			$xml_file = $wp_upload_dir['basedir'] . '/ph_import/jupix_properties.xml';

			    			$contents = '';

			    			$response = wp_remote_get( $options['xml_url'], array( 'timeout' => 120 ) );
			    			if ( !is_wp_error($response) && is_array( $response ) ) 
							{
								$contents = $response['body'];
							}
				    		else
				    		{
				    			die("Failed to obtain XML. Dump of response as follows: " . print_r($response, TRUE));
				    		}

			    			$handle = @fopen($xml_file, 'w+');
			    			if ($handle)
			    			{
			    				fwrite($handle, $contents);
			    				fclose($handle);

				                // includes
		                        require_once dirname( __FILE__ ) . '/includes/class-ph-jupix-xml-import.php';

		                        $PH_Jupix_XML_Import = new PH_Jupix_XML_Import( $xml_file, $instance_id );

		                        $parsed = $PH_Jupix_XML_Import->parse();

		                        if ( $parsed )
		                        {
			                        $PH_Jupix_XML_Import->import( $import_id );

				                    $PH_Jupix_XML_Import->remove_old_properties( $import_id, (!isset($options['dont_remove']) || $options['dont_remove'] != '1') ? true : false );
				                }

				                unlink($xml_file);
			    			}			    			
			    			else
				            {
				                echo "Failed to write XML file locally. Please check file permissions";
				            }

			    			break;
			    		}
			    		case "xml_vebra_api":
			    		{
			    			$date_ran_before = false;
			    			if ( ( isset($options['only_updated']) && $options['only_updated'] == 'yes' ) || !isset($options['only_updated']) )
							{
				    			$query = "
					                SELECT 
					                    id, start_date
					                FROM 
					                    " .$wpdb->prefix . "ph_propertyimport_logs_instance
					                WHERE 
					                    start_date <= end_date
					                    AND
					                    import_id = '" . $import_id . "'
					                ORDER BY
					                	start_date DESC
					               	LIMIT 1
					            ";
					            $results = $wpdb->get_results( $query );
					            if ( $results )
					            {
					            	foreach ( $results as $result ) 
									{
					                	$date_ran_before = $result->start_date;
					                }
					            }
					        }

			                // includes
	                        require_once dirname( __FILE__ ) . '/includes/class-ph-vebra-api-xml-import.php';

	                        $PH_Vebra_API_XML_Import = new PH_Vebra_API_XML_Import( $options['username'], $options['password'], $options['datafeed_id'], $uploads_dir, $instance_id );
	                        
	                        if ( $date_ran_before === FALSE )
	                        {
	                        	// Import never ran before. Need to do initial process which involves getting branches then properties
	                        	$PH_Vebra_API_XML_Import->get_properties_for_initial_population();
	                        }
	                        else
	                        {
	                        	if ( date("I", strtotime($date_ran_before)) == 1 )
	                        	{
	                        		$date_ran_before = date("Y-m-d H:i:s", strtotime($date_ran_before) - 3600); // - 3600 to cater for daylight saving
	                        	}
	                        	$PH_Vebra_API_XML_Import->get_changed_properties( $date_ran_before, $import_id );
	                        }

			    			$PH_Vebra_API_XML_Import->parse();

	                        $PH_Vebra_API_XML_Import->import( $import_id );

	                        if ( $date_ran_before === FALSE )
	                        {
		                    	$PH_Vebra_API_XML_Import->remove_old_properties( $import_id, (!isset($options['dont_remove']) || $options['dont_remove'] != '1') ? true : false );
	                        }

			    			break;
			    		}
			    		case "xml_acquaint":
			    		{
			    			$xml_file = $wp_upload_dir['basedir'] . '/ph_import/acquaint_properties.xml';

			    			$xml_files = array();

			    			$urls = explode( ",", $options['xml_url'] );

			    			$i = 0;
			    			foreach ($urls as $url)
			    			{
			    				$url = trim($url);

			    				if ( $url == '' )
			    				{
			    					continue;
			    				}

			    				$contents = '';

				    			if ( ini_get('allow_url_fopen') )
	        					{
					    			$contents = file_get_contents($url);
					    		}
					    		elseif ( function_exists('curl_version') )
								{
									$curl = curl_init();
								    curl_setopt($curl, CURLOPT_URL, $url);
								    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
								    $contents = curl_exec($curl);
								    curl_close($curl);
					    		}
					    		else
					    		{
					    			die("Neither allow_url_fopen nor cURL is active on your server");
					    		}

					    		$xml_file = $wp_upload_dir['basedir'] . '/ph_import/acquaint_properties-' . $i . '.xml';
					    		$handle = @fopen($xml_file, 'w+');
				    			if ($handle)
				    			{
				    				fwrite($handle, $contents);
			    					fclose($handle);

			    					$xml_files[] = $xml_file;

			    					++$i;
								}			    			
				    			else
					            {
					                echo "Failed to write XML file locally. Please check file permissions";
					            }
			    			}
			    			
			    			if ( !empty($xml_files) )
			    			{
			    				// We've got the file(s)

				                // includes
		                        require_once dirname( __FILE__ ) . '/includes/class-ph-acquaint-xml-import.php';

		                        $PH_Acquaint_XML_Import = new PH_Acquaint_XML_Import( $xml_files, $instance_id );

		                        $parsed = $PH_Acquaint_XML_Import->parse();

		                        if ( $parsed )
		                        {
			                        $PH_Acquaint_XML_Import->import( $import_id );

				                    $PH_Acquaint_XML_Import->remove_old_properties( $import_id, (!isset($options['dont_remove']) || $options['dont_remove'] != '1') ? true : false );
				                }

			                    unlink($xml_file);
			    			}

			    			break;
			    		}
			    		case "xml_citylets":
			    		{
			    			$xml_file = $wp_upload_dir['basedir'] . '/ph_import/citylets_properties.xml';

			    			$contents = '';

			    			if ( ini_get('allow_url_fopen') )
        					{
				    			$contents = file_get_contents($options['xml_url']);
				    		}
				    		elseif ( function_exists('curl_version') )
							{
								$curl = curl_init();
							    curl_setopt($curl, CURLOPT_URL, $options['xml_url']);
							    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
							    $contents = curl_exec($curl);
							    curl_close($curl);
				    		}
				    		else
				    		{
				    			die("Neither allow_url_fopen nor cURL is active on your server");
				    		}

			    			$handle = @fopen($xml_file, 'w+');
			    			if ($handle)
			    			{
			    				fwrite($handle, $contents);
			    				fclose($handle);

			    				// We've got the file

				                // includes
		                        require_once dirname( __FILE__ ) . '/includes/class-ph-citylets-xml-import.php';

		                        $PH_Citylets_XML_Import = new PH_Citylets_XML_Import( $xml_file, $instance_id );

		                        $parsed = $PH_Citylets_XML_Import->parse();

		                        if ( $parsed )
		                        {
			                        $PH_Citylets_XML_Import->import( $import_id );

				                    $PH_Citylets_XML_Import->remove_old_properties( $import_id, (!isset($options['dont_remove']) || $options['dont_remove'] != '1') ? true : false );
				                }

			                    unlink($xml_file);
			    			}			    			
			    			else
				            {
				                echo "Failed to write XML file locally. Please check file permissions";
				            }

			    			break;
			    		}
			    		case "xml_sme_professional":
			    		{
			    			$xml_file = $wp_upload_dir['basedir'] . '/ph_import/sme_professional_properties.xml';

			    			$contents = '';

			    			if ( ini_get('allow_url_fopen') )
        					{
				    			$contents = file_get_contents($options['xml_url']);
				    		}
				    		elseif ( function_exists('curl_version') )
							{
								$curl = curl_init();
							    curl_setopt($curl, CURLOPT_URL, $options['xml_url']);
							    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
							    $contents = curl_exec($curl);
							    curl_close($curl);
				    		}
				    		else
				    		{
				    			die("Neither allow_url_fopen nor cURL is active on your server");
				    		}

			    			$handle = @fopen($xml_file, 'w+');
			    			if ($handle)
			    			{
			    				fwrite($handle, $contents);
			    				fclose($handle);

			    				// We've got the file

				                // includes
		                        require_once dirname( __FILE__ ) . '/includes/class-ph-sme-professional-xml-import.php';

		                        $PH_SME_Professional_XML_Import = new PH_SME_Professional_XML_Import( $xml_file, $instance_id );

		                        $parsed = $PH_SME_Professional_XML_Import->parse();

		                        if ( $parsed )
		                        {
			                        $PH_SME_Professional_XML_Import->import( $import_id );

				                    $PH_SME_Professional_XML_Import->remove_old_properties( $import_id, (!isset($options['dont_remove']) || $options['dont_remove'] != '1') ? true : false );
				                }

			                    unlink($xml_file);
			    			}			    			
			    			else
				            {
				                echo "Failed to write XML file locally. Please check file permissions";
				            }

			    			break;
			    		}
			    		case "thesaurus":
			    		{
			    			// Connect to FTP directory and get file
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
				            	$xml_file = $wp_upload_dir['basedir'] . '/ph_import/' . $options['filename'];

				            	// Get file
				            	if ( ftp_get( $ftp_conn, $xml_file, $options['filename'], FTP_ASCII ) )
				            	{
				            		// We've got the file

					                // includes
			                        require_once dirname( __FILE__ ) . '/includes/class-ph-thesaurus-import.php';

			                        $PH_Thesaurus_XML_Import = new PH_Thesaurus_Import( $xml_file, $instance_id );

			                        $parsed = $PH_Thesaurus_XML_Import->parse();

			                        if ($parsed)
			                        {			                        
				                        $PH_Thesaurus_XML_Import->import( $import_id );

					                    $PH_Thesaurus_XML_Import->remove_old_properties( $import_id, (!isset($options['dont_remove']) || $options['dont_remove'] != '1') ?  true : false );
					                }

					                unlink($xml_file);
				            	}
				            	else
				            	{
				            		echo "Failed to get file " . $options['filename'] . " from FTP directory";
				            	}

				            	ftp_close( $ftp_conn );
				            }
				            else
				            {
				                echo "Incorrect FTP details provided";
				            }

			    			break;
			    		}
			    		case "jet":
			    		{
			                // includes
	                        require_once dirname( __FILE__ ) . '/includes/class-ph-jet-import.php';

			    			$PH_JET_Import = new PH_JET_Import( $instance_id );

	                        $parsed = $PH_JET_Import->parse( $import_id );
	                        
	                        if ( $parsed )
	                        {
		                        $PH_JET_Import->import( $import_id );

			                    $PH_JET_Import->remove_old_properties( $import_id, (!isset($options['dont_remove']) || $options['dont_remove'] != '1') ?  true : false );
			                }

			    			break;
			    		}
			    		case "xml_rentman":
			    		{
			    			$local_directory = $options['local_directory'];

			    			// Get XML files in date order
							$xml_files = array();
							if ($handle = opendir($local_directory)) 
							{
							    while (false !== ($file = readdir($handle))) 
							    {
							        if (
							        	$file != "." && $file != ".." && 
							        	substr(strtolower($file), -3) == 'xml'
							        ) 
							        {
							           $xml_files[filemtime($local_directory . '/' . $file)] = $local_directory . '/' . $file;
							        }
							    }
							    closedir($handle);
							}
							else
							{
								die('Directory ' . $local_directory . ' is either not readable or does not exist');
							}

							if (!empty($xml_files))
							{
								ksort($xml_files);

								// We've got at least one XML to process

				                // includes
		                        require_once dirname( __FILE__ ) . '/includes/class-ph-rentman-xml-import.php';

		                        foreach ($xml_files as $mtime => $xml_file)
		                        {
			                        $PH_Rentman_XML_Import = new PH_Rentman_XML_Import( $xml_file, $instance_id );

			                        $PH_Rentman_XML_Import->parse();

			                        $PH_Rentman_XML_Import->import( $import_id );

				                    $PH_Rentman_XML_Import->remove_old_properties( $import_id, (!isset($options['dont_remove']) || $options['dont_remove'] != '1') ?  true : false );

				                    // Rename to append the date and '.processed' as to not get picked up again. Will be cleaned up every 7 days
			                        rename($xml_file, $xml_file . '-' . gmdate("YmdHis") .'.processed');
			                    }
							}

							// Clean up processed .xML and unused media older than 7 days old (7 days = 604800 seconds)
							if ($handle = opendir($local_directory)) 
							{
							    while (false !== ($file = readdir($handle))) 
							    {
							        if (
							        	$file != "." && $file != ".." && 
							        	(
							        		substr($file, -9) == 'processed' || 
							        		substr(strtolower($file), -4) == '.jpg' || 
							        		substr(strtolower($file), -4) == '.gif' || 
							        		substr(strtolower($file), -5) == '.jpeg' || 
							        		substr(strtolower($file), -4) == '.png' || 
							        		substr(strtolower($file), -4) == '.bmp' || 
							        		substr(strtolower($file), -4) == '.pdf'
							        	)
							        ) 
							        {
							        	if ( filemtime($local_directory . '/' . $file) !== FALSE && filemtime($local_directory . '/' . $file) < (time() - 604800) )
							        	{
							        		unlink($local_directory . '/' . $file);
							        	}
							        }
							    }
							    closedir($handle);
							}

			    			break;
			    		}
			    		case "json_letmc":
			    		{
		            		// Load Importer API
			                require_once ABSPATH . 'wp-admin/includes/import.php';

			                if ( ! class_exists( 'WP_Importer' ) ) {
			                    $class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
			                    if ( file_exists( $class_wp_importer ) ) require_once $class_wp_importer;
			                }

			                // includes
	                        require_once dirname( __FILE__ ) . '/includes/class-ph-letmc-json-import.php';

							$PH_LetMC_JSON_Import = new PH_LetMC_JSON_Import( $instance_id );

	                        $parsed = $PH_LetMC_JSON_Import->parse( $options, $import_id );

	                        if ( $parsed )
	                        {
		                        $PH_LetMC_JSON_Import->import( $import_id );

			                    $PH_LetMC_JSON_Import->remove_old_properties( $import_id, (!isset($options['dont_remove']) || $options['dont_remove'] != '1') ?  true : false );
			                }

			    			break;
			    		}
			    		case "reaxml_local":
			    		{
			    			$local_directory = $options['local_directory'];

			    			// Get all zip files in date order
			    			$zip_files = array();
							if ($handle = opendir($local_directory)) 
							{
							    while (false !== ($file = readdir($handle))) 
							    {
							        if (
							        	$file != "." && $file != ".." && 
							        	substr(strtolower($file), -3) == 'zip'
							        ) 
							        {
							           $zip_files[filemtime($local_directory . '/' . $file)] = $local_directory . '/' . $file;
							        }
							    }
							    closedir($handle);
							}
							else
							{
								die('Directory ' . $local_directory . ' is either not readable or does not exist');
							}

							if (!empty($zip_files))
							{
								if ( !class_exists('ZipArchive') ) { die('The ZipArchive class does not exist but is needed to extract the zip files provided'); }

								ksort($zip_files);

								foreach ($zip_files as $mtime => $zip_file)
								{
									$zip = new ZipArchive;
									if ($zip->open($zip_file) === TRUE) 
									{
									    $zip->extractTo($local_directory);
									    $zip->close();
									    sleep(1); // We sleep to ensure each XML has a different modified time in the same order
									}
									else
									{
										// log
									}
									unlink($zip_file);
								}
							}

							unset($zip_files);

			    			// Now they've all been extracted, get XML files in date order
							$xml_files = array();
							if ($handle = opendir($local_directory)) 
							{
							    while (false !== ($file = readdir($handle))) 
							    {
							        if (
							        	$file != "." && $file != ".." && 
							        	substr(strtolower($file), -3) == 'xml'
							        ) 
							        {
							           $xml_files[filemtime($local_directory . '/' . $file)] = $local_directory . '/' . $file;
							        }
							    }
							    closedir($handle);
							}

							if (!empty($xml_files))
							{
								ksort($xml_files);

								// We've got at least one X<: to process

				                // includes
		                        require_once dirname( __FILE__ ) . '/includes/class-ph-reaxml-import.php';

		                        foreach ($xml_files as $mtime => $xml_file)
		                        {
			                        $PH_REAXML_Import = new PH_REAXML_Import( $xml_file, $instance_id );

			                        $parsed = $PH_REAXML_Import->parse();

			                        if ( $parsed !== FALSE )
			                        {
				                        $PH_REAXML_Import->import( $import_id );

				                        // Shouldn't be needed as we should receive removed properties with a status of 'off market' or similar
					                    //$PH_REAXML_Import->remove_old_properties( $import_id, (!isset($options['dont_remove']) || $options['dont_remove'] != '1') ?  true : false );
				                    }

				                    // Rename to append the date and '.processed' as to not get picked up again. Will be cleaned up every 7 days
			                        rename($xml_file, $xml_file . '-' . gmdate("YmdHis") .'.processed');
			                    }
							}

							// Clean up processed .XMLs and unused media older than 7 days old (7 days = 604800 seconds)
							if ($handle = opendir($local_directory)) 
							{
							    while (false !== ($file = readdir($handle))) 
							    {
							        if (
							        	$file != "." && $file != ".." && 
							        	(
							        		substr($file, -9) == 'processed' || 
							        		substr(strtolower($file), -4) == '.jpg' || 
							        		substr(strtolower($file), -4) == '.gif' || 
							        		substr(strtolower($file), -5) == '.jpeg' || 
							        		substr(strtolower($file), -4) == '.png' || 
							        		substr(strtolower($file), -4) == '.bmp' || 
							        		substr(strtolower($file), -4) == '.pdf'
							        	)
							        ) 
							        {
							        	if ( filemtime($local_directory . '/' . $file) !== FALSE && filemtime($local_directory . '/' . $file) < (time() - 604800) )
							        	{
							        		unlink($local_directory . '/' . $file);
							        	}
							        }
							    }
							    closedir($handle);
							}

			    			break;
			    		}
			    		case "xml_10ninety":
			    		{
			    			$xml_file = $wp_upload_dir['basedir'] . '/ph_import/10ninety_properties.xml';

			    			$contents = '';

			    			if ( ini_get('allow_url_fopen') )
        					{
				    			$contents = file_get_contents($options['xml_url']);
				    		}
				    		elseif ( function_exists('curl_version') )
							{
								$curl = curl_init();
							    curl_setopt($curl, CURLOPT_URL, $options['xml_url']);
							    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
							    $contents = curl_exec($curl);
							    curl_close($curl);
				    		}
				    		else
				    		{
				    			die("Neither allow_url_fopen nor cURL is active on your server");
				    		}

			    			$handle = @fopen($xml_file, 'w+');
			    			if ($handle)
			    			{
			    				fwrite($handle, $contents);
			    				fclose($handle);

			    				// We've got the file

				                // includes
		                        require_once dirname( __FILE__ ) . '/includes/class-ph-10ninety-xml-import.php';

		                        $PH_10ninety_XML_Import = new PH_10ninety_XML_Import( $xml_file, $instance_id );

		                        $parsed = $PH_10ninety_XML_Import->parse();

		                        if ( $parsed )
		                        {
			                        $PH_10ninety_XML_Import->import( $import_id );

				                    $PH_10ninety_XML_Import->remove_old_properties( $import_id, (!isset($options['dont_remove']) || $options['dont_remove'] != '1') ? true : false );
				                }

				                unlink($xml_file);
			    			}			    			
			    			else
				            {
				                echo "Failed to write XML file locally. Please check file permissions";
				            }

			    			break;
			    		}
			    		case "xml_domus":
			    		{
			                // includes
	                        require_once dirname( __FILE__ ) . '/includes/class-ph-domus-xml-import.php';

	                        $PH_Domus_XML_Import = new PH_Domus_XML_Import( $instance_id );

	                        $parsed = $PH_Domus_XML_Import->parse($options);

	                        if ( $parsed )
	                        {
		                        $PH_Domus_XML_Import->import( $import_id );

			                    $PH_Domus_XML_Import->remove_old_properties( $import_id, (!isset($options['dont_remove']) || $options['dont_remove'] != '1') ? true : false );
			                }

			    			break;
			    		}
			    		case "json_realla":
			    		{
			                // includes
	                        require_once dirname( __FILE__ ) . '/includes/class-ph-realla-json-import.php';

	                        $PH_Realla_JSON_Import = new PH_Realla_JSON_Import( $instance_id );

	                        $parsed = $PH_Realla_JSON_Import->parse($options);

	                        if ( $parsed )
	                        {
		                        $PH_Realla_JSON_Import->import( $import_id );

			                    $PH_Realla_JSON_Import->remove_old_properties( $import_id, (!isset($options['dont_remove']) || $options['dont_remove'] != '1') ? true : false );
			                }

			    			break;
			    		}
			    		case "json_agency_pilot":
			    		{
			                // includes
	                        require_once dirname( __FILE__ ) . '/includes/class-ph-agency-pilot-json-import.php';

	                        $PH_Agency_Pilot_JSON_Import = new PH_Agency_Pilot_JSON_Import( $instance_id );

	                        $parsed = $PH_Agency_Pilot_JSON_Import->parse($options);

	                        if ( $parsed )
	                        {
		                        $PH_Agency_Pilot_JSON_Import->import( $import_id );

			                    $PH_Agency_Pilot_JSON_Import->remove_old_properties( $import_id, (!isset($options['dont_remove']) || $options['dont_remove'] != '1') ? true : false );
			                }

			    			break;
			    		}
			    		case "api_agency_pilot":
			    		{
			                // includes
	                        require_once dirname( __FILE__ ) . '/includes/class-ph-agency-pilot-api-import.php';

	                        $PH_Agency_Pilot_API_Import = new PH_Agency_Pilot_API_Import( $instance_id );

	                        $parsed = $PH_Agency_Pilot_API_Import->parse($options);

	                        if ( $parsed )
	                        {
		                        $PH_Agency_Pilot_API_Import->import( $import_id );

			                    $PH_Agency_Pilot_API_Import->remove_old_properties( $import_id, (!isset($options['dont_remove']) || $options['dont_remove'] != '1') ? true : false );
			                }

			    			break;
			    		}
			    		case "xml_propertyadd":
			    		{
			                // includes
	                        require_once dirname( __FILE__ ) . '/includes/class-ph-propertyadd-xml-import.php';

	                        $PH_PropertyADD_XML_Import = new PH_PropertyADD_XML_Import( $instance_id );

	                        $parsed = $PH_PropertyADD_XML_Import->parse($options);

	                        if ( $parsed )
	                        {
		                        $PH_PropertyADD_XML_Import->import( $import_id );

			                    $PH_PropertyADD_XML_Import->remove_old_properties( $import_id, (!isset($options['dont_remove']) || $options['dont_remove'] != '1') ? true : false );
			                }

			    			break;
			    		}
			    		case "xml_gnomen":
			    		{
			                // includes
	                        require_once dirname( __FILE__ ) . '/includes/class-ph-gnomen-xml-import.php';

	                        $PH_Gnomen_XML_Import = new PH_Gnomen_XML_Import( $instance_id );

	                        $parsed = $PH_Gnomen_XML_Import->parse($options);

	                        if ( $parsed )
	                        {
		                        $PH_Gnomen_XML_Import->import( $import_id );

			                    $PH_Gnomen_XML_Import->remove_old_properties( $import_id, (!isset($options['dont_remove']) || $options['dont_remove'] != '1') ? true : false );
			                }

			    			break;
			    		}
			    		case "xml_kyero":
			    		{
		            		$xml_file = $wp_upload_dir['basedir'] . '/ph_import/kyero_properties.xml';

			    			$contents = '';

			    			$response = wp_remote_get( $options['url'], array( 'timeout' => 120 ) );
			    			if ( !is_wp_error($response) && is_array( $response ) ) 
							{
								$contents = $response['body'];
							}
				    		else
				    		{
				    			die("Failed to obtain XML. Dump of response as follows: " . print_r($response, TRUE));
				    		}

			    			$handle = @fopen($xml_file, 'w+');
			    			if ($handle)
			    			{
			    				fwrite($handle, $contents);
			    				fclose($handle);

			    				// We've got the file

				                // includes
		                        require_once dirname( __FILE__ ) . '/includes/class-ph-kyero-xml-import.php';

		                        $PH_Kyero_XML_Import = new PH_Kyero_XML_Import( $xml_file, $instance_id );

		                        $parsed = $PH_Kyero_XML_Import->parse();

		                        if ( $parsed )
		                        {
			                        $PH_Kyero_XML_Import->import( $import_id );

				                    $PH_Kyero_XML_Import->remove_old_properties( $import_id, (!isset($options['dont_remove']) || $options['dont_remove'] != '1') ? true : false );
				                }

				                unlink($xml_file);
			    			}			    			
			    			else
				            {
				                echo "Failed to write XML file locally. Please check file permissions";
				            }

			    			break;
			    		}
			    		case "xml_resales_online":
			    		{
		            		$xml_file = $wp_upload_dir['basedir'] . '/ph_import/resales_online_properties.xml';

			    			$contents = '';

			    			$response = wp_remote_get( $options['url'], array( 'timeout' => 120 ) );
			    			if ( !is_wp_error($response) && is_array( $response ) ) 
							{
								$contents = $response['body'];
							}
				    		else
				    		{
				    			die("Failed to obtain XML. Dump of response as follows: " . print_r($response, TRUE));
				    		}

			    			$handle = @fopen($xml_file, 'w+');
			    			if ($handle)
			    			{
			    				fwrite($handle, $contents);
			    				fclose($handle);

			    				// We've got the file

				                // includes
		                        require_once dirname( __FILE__ ) . '/includes/class-ph-resales-online-xml-import.php';

		                        $PH_ReSales_Online_XML_Import = new PH_ReSales_Online_XML_Import( $xml_file, $instance_id );

		                        $parsed = $PH_ReSales_Online_XML_Import->parse();

		                        if ( $parsed )
		                        {
			                        $PH_ReSales_Online_XML_Import->import( $import_id );

				                    $PH_ReSales_Online_XML_Import->remove_old_properties( $import_id, (!isset($options['dont_remove']) || $options['dont_remove'] != '1') ? true : false );
				                }

				                unlink($xml_file);
			    			}			    			
			    			else
				            {
				                echo "Failed to write XML file locally. Please check file permissions";
				            }

			    			break;
			    		}
			    		case "json_loop":
			    		{
			            	$json_file = $wp_upload_dir['basedir'] . '/ph_import/loop.json';

			                // includes
	                        require_once dirname( __FILE__ ) . '/includes/class-ph-loop-json-import.php';

	                        $contents = '';

			    			$response = wp_remote_get( $options['url'], array( 'timeout' => 120, 'headers' => array(
								'Content-Type' => 'application/json',
								'x-api-key' => $options['client_id'],
							) ) );
			    			if ( !is_wp_error($response) && is_array( $response ) ) 
							{
								$contents = $response['body'];
							}
				    		else
				    		{
				    			die("Failed to obtain JSON. Dump of response as follows: " . print_r($response, TRUE));
				    		}

							$handle = fopen($json_file, 'w+');
							fwrite($handle, $contents);
							fclose($handle);

							$PH_Loop_JSON_Import = new PH_Loop_JSON_Import( $json_file, $instance_id );

	                        $parsed = $PH_Loop_JSON_Import->parse( $import_id );

	                        if ( $parsed !== FALSE )
	                        {
		                        $PH_Loop_JSON_Import->import( $import_id );

			                    $PH_Loop_JSON_Import->remove_old_properties( $import_id, (!isset($options['dont_remove']) || $options['dont_remove'] != '1') ?  true : false );
			                }

			                unlink($json_file);

			    			break;
			    		}
			    		case "json_veco":
			    		{
			                // includes
	                        require_once dirname( __FILE__ ) . '/includes/class-ph-veco-json-import.php';

							$PH_Veco_JSON_Import = new PH_Veco_JSON_Import( $instance_id );

	                        $parsed = $PH_Veco_JSON_Import->parse( $options );

	                        if ( $parsed !== FALSE )
	                        {
		                        $PH_Veco_JSON_Import->import( $import_id );

			                    $PH_Veco_JSON_Import->remove_old_properties( $import_id, (!isset($options['dont_remove']) || $options['dont_remove'] != '1') ?  true : false );
			                }

			                unlink($json_file);

			    			break;
			    		}
			    		case "xml_estatesit":
			    		{
			    			$local_directory = $options['local_directory'];

			    			// Get XML files in date order
							$xml_files = array();
							if ($handle = opendir($local_directory)) 
							{
							    while (false !== ($file = readdir($handle))) 
							    {
							        if (
							        	$file != "." && $file != ".." && 
							        	substr(strtolower($file), -3) == 'xml'
							        ) 
							        {
							           $xml_files[filemtime($local_directory . '/' . $file)] = $local_directory . '/' . $file;
							        }
							    }
							    closedir($handle);
							}

							if (!empty($xml_files))
							{
								ksort($xml_files);

								// We've got at least one XML to process

				                // includes
		                        require_once dirname( __FILE__ ) . '/includes/class-ph-estatesit-xml-import.php';

		                        foreach ($xml_files as $mtime => $xml_file)
		                        {
			                        $PH_EstatesIT_XML_Import = new PH_EstatesIT_XML_Import( $xml_file, $instance_id );

			                        $parsed = $PH_EstatesIT_XML_Import->parse();

			                        if ( $parsed !== FALSE )
			                        {
			                        	// Parsed it succesfully. Ok to continue
				                        $PH_EstatesIT_XML_Import->import( $import_id );

					                    $PH_EstatesIT_XML_Import->remove_old_properties( $import_id, (!isset($options['dont_remove']) || $options['dont_remove'] != '1') ?  true : false );
					                }

					                $PH_EstatesIT_XML_Import->archive( $import_id );
			                    }
							}

							// Clean up processed .XMLs and unused media older than 7 days old (7 days = 604800 seconds)
							if ($handle = opendir($local_directory)) 
							{
							    while (false !== ($file = readdir($handle))) 
							    {
							        if (
							        	$file != "." && $file != ".." && 
							        	(
							        		substr($file, -9) == 'processed' || 
							        		substr(strtolower($file), -4) == '.jpg' || 
							        		substr(strtolower($file), -4) == '.gif' || 
							        		substr(strtolower($file), -5) == '.jpeg' || 
							        		substr(strtolower($file), -4) == '.png' || 
							        		substr(strtolower($file), -4) == '.bmp' || 
							        		substr(strtolower($file), -4) == '.pdf'
							        	)
							        ) 
							        {
							        	if ( filemtime($local_directory . '/' . $file) !== FALSE && filemtime($local_directory . '/' . $file) < (time() - 604800) )
							        	{
							        		unlink($local_directory . '/' . $file);
							        	}
							        }
							    }
							    closedir($handle);
							}

			    			break;
			    		}
			    		case "xml_juvo":
			    		{
			    			$xml_file = $wp_upload_dir['basedir'] . '/ph_import/juvo_properties.xml';

			    			$contents = '';

			    			$response = wp_remote_get( $options['url'], array( 'timeout' => 120 ) );
			    			if ( !is_wp_error($response) && is_array( $response ) ) 
							{
								$contents = $response['body'];
							}
				    		else
				    		{
				    			die("Failed to obtain XML. Dump of response as follows: " . print_r($response, TRUE));
				    		}

			    			$handle = @fopen($xml_file, 'w+');
			    			if ($handle)
			    			{
			    				fwrite($handle, $contents);
			    				fclose($handle);

			    				// We've got the file

				                // includes
		                        require_once dirname( __FILE__ ) . '/includes/class-ph-juvo-xml-import.php';

		                        $PH_Juvo_XML_Import = new PH_Juvo_XML_Import( $xml_file, $instance_id );

		                        $parsed = $PH_Juvo_XML_Import->parse();

		                        if ( $parsed )
		                        {
			                        $PH_Juvo_XML_Import->import( $import_id );

				                    $PH_Juvo_XML_Import->remove_old_properties( $import_id, (!isset($options['dont_remove']) || $options['dont_remove'] != '1') ? true : false );
				                }

				                unlink($xml_file);
			    			}			    			
			    			else
				            {
				                echo "Failed to write XML file locally. Please check file permissions";
				            }

			    			break;
			    		}
			    		case "json_utili":
			    		{
			    			$json_file = $wp_upload_dir['basedir'] . '/ph_import/utili_properties.json';

			    			$contents = '';

			    			$response = wp_remote_post( 
			    				'https://pro.utili.co.uk/api/getProperties', 
			    				array(
									'method' => 'POST',
									'timeout' => 120,
									'headers' => array(),
									'body' => array( 'appkey' => $options['api_key'], 'account' => $options['account_name'] ),
							    )
							);

							if ( !is_wp_error( $response ) && is_array( $response ) ) 
							{
							   $contents = $response['body'];
							} 
							else 
							{
							   die("Failed to obtain JSON. Dump of response as follows: " . print_r($response, TRUE));
							}

			    			$handle = @fopen($json_file, 'w+');
			    			if ($handle)
			    			{
			    				fwrite($handle, $contents);
			    				fclose($handle);

			    				// We've got the file

				                // includes
		                        require_once dirname( __FILE__ ) . '/includes/class-ph-utili-json-import.php';

		                        $PH_Utili_JSON_Import = new PH_Utili_JSON_Import( $json_file, $instance_id );

		                        $parsed = $PH_Utili_JSON_Import->parse();

		                        if ( $parsed )
		                        {
			                        $PH_Utili_JSON_Import->import( $import_id );

				                    $PH_Utili_JSON_Import->remove_old_properties( $import_id, (!isset($options['dont_remove']) || $options['dont_remove'] != '1') ? true : false );
				                }

				                unlink($json_file);
			    			}			    			
			    			else
				            {
				                echo "Failed to write JSON file locally. Please check file permissions";
				            }

			    			break;
			    		}
			    		case "json_arthur":
			    		{
			                // includes
	                        require_once dirname( __FILE__ ) . '/includes/class-ph-arthur-json-import.php';

	                        $PH_Arthur_JSON_Import = new PH_Arthur_JSON_Import( $instance_id );

	                        $parsed = $PH_Arthur_JSON_Import->parse( $import_id );

	                        if ( $parsed )
	                        {
		                        $PH_Arthur_JSON_Import->import( $import_id );

			                    $PH_Arthur_JSON_Import->remove_old_properties( $import_id, (!isset($options['dont_remove']) || $options['dont_remove'] != '1') ? true : false );
			                }

			    			break;
			    		}
			    		case "xml_supercontrol":
			    		{
			                // includes
	                        require_once dirname( __FILE__ ) . '/includes/class-ph-supercontrol-xml-import.php';

	                        $PH_SuperControl_XML_Import = new PH_SuperControl_XML_Import( $instance_id );

	                        $parsed = $PH_SuperControl_XML_Import->parse( $options, $import_id );

	                        if ( $parsed )
	                        {
		                        $PH_SuperControl_XML_Import->import( $import_id );

			                    $PH_SuperControl_XML_Import->remove_old_properties( $import_id, (!isset($options['dont_remove']) || $options['dont_remove'] != '1') ? true : false );
			                }

			    			break;
			    		}
			    		case "xml_agentsinsight":
			    		{
			    			$xml_file = $wp_upload_dir['basedir'] . '/ph_import/agentsinsight_properties.xml';

			    			$contents = '';

			    			$response = wp_remote_get( $options['xml_url'], array( 'timeout' => 120 ) );
			    			if ( !is_wp_error($response) && is_array( $response ) ) 
							{
								$contents = $response['body'];
							}
				    		else
				    		{
				    			die("Failed to obtain XML. Dump of response as follows: " . print_r($response, TRUE));
				    		}

			    			$handle = @fopen($xml_file, 'w+');
			    			if ($handle)
			    			{
			    				fwrite($handle, $contents);
			    				fclose($handle);

				                // includes
		                        require_once dirname( __FILE__ ) . '/includes/class-ph-agentsinsight-xml-import.php';

		                        $PH_agentsinsight_XML_Import = new PH_agentsinsight_XML_Import( $xml_file, $instance_id );

		                        $parsed = $PH_agentsinsight_XML_Import->parse();

		                        if ( $parsed )
		                        {
			                        $PH_agentsinsight_XML_Import->import( $import_id );

				                    $PH_agentsinsight_XML_Import->remove_old_properties( $import_id, (!isset($options['dont_remove']) || $options['dont_remove'] != '1') ? true : false );
				                }

				                unlink($xml_file);
			    			}			    			
			    			else
				            {
				                echo "Failed to write XML file locally. Please check file permissions";
				            }

			    			break;
			    		}
			    		case "json_rex":
			    		{
			                // includes
	                        require_once dirname( __FILE__ ) . '/includes/class-ph-rex-json-import.php';

							$PH_Rex_JSON_Import = new PH_Rex_JSON_Import( $instance_id );

	                        $parsed = $PH_Rex_JSON_Import->parse( $import_id );

	                        if ( $parsed !== FALSE )
	                        {
		                        $PH_Rex_JSON_Import->import( $import_id );

			                    $PH_Rex_JSON_Import->remove_old_properties( $import_id, (!isset($options['dont_remove']) || $options['dont_remove'] != '1') ?  true : false );
			                }

			    			break;
			    		}
			    		case "xml_decorus":
			    		{
			    			$local_directory = $options['local_directory'];

			    			$xml_files = array();
							if ($handle = opendir($local_directory)) 
							{
							    while (false !== ($file = readdir($handle))) 
							    {
							        if (
							        	$file != "." && $file != ".." && 
							        	substr(strtolower($file), -3) == 'xml'
							        ) 
							        {
							           $xml_files[filemtime($local_directory . '/' . $file)] = $local_directory . '/' . $file;
							        }
							    }
							    closedir($handle);
							}

							if (!empty($xml_files))
							{
								ksort($xml_files);

								// We've got at least one XML to process

				                // includes
		                        require_once dirname( __FILE__ ) . '/includes/class-ph-decorus-xml-import.php';

		                        foreach ($xml_files as $mtime => $xml_file)
		                        {
			                        $PH_Decorus_XML_Import = new PH_Decorus_XML_Import( $xml_file, $instance_id );

			                        $parsed = $PH_Decorus_XML_Import->parse();

			                        if ( $parsed !== FALSE )
			                        {
			                        	// Parsed it succesfully. Ok to continue
				                        $PH_Decorus_XML_Import->import( $import_id );

					                    $PH_Decorus_XML_Import->remove_old_properties( $import_id, (!isset($options['dont_remove']) || $options['dont_remove'] != '1') ?  true : false );
					                }

					                $PH_Decorus_XML_Import->archive( $import_id );
			                    }
							}

							// Clean up processed .XMLs and unused media older than 7 days old (7 days = 604800 seconds)
							if ($handle = opendir($local_directory)) 
							{
							    while (false !== ($file = readdir($handle))) 
							    {
							        if (
							        	$file != "." && $file != ".." && 
							        	(
							        		substr($file, -9) == 'processed' || 
							        		substr(strtolower($file), -4) == '.jpg' || 
							        		substr(strtolower($file), -4) == '.gif' || 
							        		substr(strtolower($file), -5) == '.jpeg' || 
							        		substr(strtolower($file), -4) == '.png' || 
							        		substr(strtolower($file), -4) == '.bmp' || 
							        		substr(strtolower($file), -4) == '.pdf'
							        	)
							        ) 
							        {
							        	if ( filemtime($local_directory . '/' . $file) !== FALSE && filemtime($local_directory . '/' . $file) < (time() - 604800) )
							        	{
							        		unlink($local_directory . '/' . $file);
							        	}
							        }
							    }
							    closedir($handle);
							}

			    			break;
			    		}
			    		case "xml_mri":
			    		{
			                // includes
	                        require_once dirname( __FILE__ ) . '/includes/class-ph-mri-xml-import.php';

							$PH_MRI_XML_Import = new PH_MRI_XML_Import( $instance_id );

	                        $parsed = $PH_MRI_XML_Import->parse( $import_id );
	                        
	                        if ( $parsed !== FALSE )
	                        {
		                        $PH_MRI_XML_Import->import( $import_id );

			                    $PH_MRI_XML_Import->remove_old_properties( $import_id, (!isset($options['dont_remove']) || $options['dont_remove'] != '1') ?  true : false );
			                }

			    			break;
			    		}
			    	}

			    	do_action( 'propertyhive_property_import_cron', $options, $instance_id, $import_id );

			    	// log instance end
			    	$wpdb->update( 
			            $wpdb->prefix . "ph_propertyimport_logs_instance", 
			            array( 
			                'end_date' => gmdate("Y-m-d H:i:s")
			            ),
			            array( 'id' => $instance_id )
			        );

			        delete_transient("ph_featured_properties");

			        // Email report
			        if ( isset($options['email_reports']) && $options['email_reports'] == 'yes' )
			        {
			        	if ( isset($options['email_reports_to']) && $options['email_reports_to'] != '' )
				        {
				        	$to = $options['email_reports_to'];
				        	$subject = get_bloginfo('name') . ' Property Import Log';
				        	$body = "";

				        	$logs = $wpdb->get_results( 
								"
								SELECT *
								FROM " . $wpdb->prefix . "ph_propertyimport_logs_instance
								INNER JOIN 
									" . $wpdb->prefix . "ph_propertyimport_logs_instance_log ON  " . $wpdb->prefix . "ph_propertyimport_logs_instance.id = " . $wpdb->prefix . "ph_propertyimport_logs_instance_log.instance_id
								WHERE 
									instance_id = '" . $instance_id . "'
								ORDER BY " . $wpdb->prefix . "ph_propertyimport_logs_instance_log.id ASC
								"
							);

							$import_id = '';
							foreach ( $logs as $log ) 
							{
								$body .= date("H:i:s jS F Y", strtotime($log->log_date)) . ' - ' . $log->entry;
								$body .= "\n";
							}

				        	wp_mail( $to, $subject, $body );
				        }
			        }
		    	}
		    }
	    }
	}

}

?>