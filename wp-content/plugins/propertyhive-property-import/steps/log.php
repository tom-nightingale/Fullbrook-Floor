<h3>Step 4. Import Finished</h3>

<pre style="overflow:auto; max-height:450px; background:#FFF; border-top:1px solid #CCC; border-bottom:1px solid #CCC"><?php
	
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
		echo date("H:i:s jS F Y", strtotime($log->log_date)) . ' - ' . $log->entry;
		echo "\n";

		$import_id = $log->import_id;
	}

?></pre>

<br>
<a href="<?php echo admin_url('admin.php?page=propertyhive_import_properties&logs=' . $import_id); ?>" class="button">Back To Logs</a>

<?php
	$logs = $wpdb->get_results( 
		"
		SELECT * 
		FROM " . $wpdb->prefix . "ph_propertyimport_logs_instance
		INNER JOIN 
			" . $wpdb->prefix . "ph_propertyimport_logs_instance_log ON  " . $wpdb->prefix . "ph_propertyimport_logs_instance.id = " . $wpdb->prefix . "ph_propertyimport_logs_instance_log.instance_id
		WHERE 
			import_id = '" . (int)$_GET['import_id'] . "'
		AND
			instance_id < '" . $instance_id . "'
		GROUP BY " . $wpdb->prefix . "ph_propertyimport_logs_instance.id
		ORDER BY start_date DESC
		LIMIT 1
		"
	);

	if ( $logs )
	{
		foreach ( $logs as $log ) 
		{
?>
<a href="<?php echo admin_url('admin.php?page=propertyhive_import_properties&import_id=' . (int)$_GET['import_id'] . '&log=' . $log->instance_id); ?>" class="button">&lt; Previous Log</a>
<?php
		}
	}

	$logs = $wpdb->get_results( 
		"
		SELECT * 
		FROM " . $wpdb->prefix . "ph_propertyimport_logs_instance
		INNER JOIN 
			" . $wpdb->prefix . "ph_propertyimport_logs_instance_log ON  " . $wpdb->prefix . "ph_propertyimport_logs_instance.id = " . $wpdb->prefix . "ph_propertyimport_logs_instance_log.instance_id
		WHERE 
			import_id = '" . (int)$_GET['import_id'] . "'
		AND
			instance_id > '" . $instance_id . "'
		GROUP BY " . $wpdb->prefix . "ph_propertyimport_logs_instance.id
		ORDER BY start_date ASC
		LIMIT 1
		"
	);

	if ( $logs )
	{
		foreach ( $logs as $log ) 
		{
?>
<a href="<?php echo admin_url('admin.php?page=propertyhive_import_properties&import_id=' . (int)$_GET['import_id'] . '&log=' . $log->instance_id); ?>" class="button">Next Log &gt;</a>
<?php
		}
	}
?>

