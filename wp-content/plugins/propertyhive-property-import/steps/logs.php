<h3>Logs</h3>

<?php
	$logs = $wpdb->get_results( 
		"
		SELECT * 
		FROM " . $wpdb->prefix . "ph_propertyimport_logs_instance
		INNER JOIN 
			" . $wpdb->prefix . "ph_propertyimport_logs_instance_log ON  " . $wpdb->prefix . "ph_propertyimport_logs_instance.id = " . $wpdb->prefix . "ph_propertyimport_logs_instance_log.instance_id
		WHERE 
			import_id = '" . $import_id . "'
		GROUP BY " . $wpdb->prefix . "ph_propertyimport_logs_instance.id
		ORDER BY start_date ASC
		"
	);

	if ( $logs )
	{
		$time_offset = (int) get_option('gmt_offset') * 60 * 60;

		foreach ( $logs as $log ) 
		{
			echo '<a href="' . admin_url('admin.php?page=propertyhive_import_properties&import_id=' . $import_id . '&log=' . $log->instance_id) . '">' . date("H:i jS F Y", strtotime($log->start_date) + $time_offset) . '</a><br>';
		}
	}
	else
	{
		echo '<p>No logs found. The import may not have ran, or hasn\'t ran in the past 7 days</p>';
	}
?>

<br>
<a href="<?php echo admin_url('admin.php?page=propertyhive_import_properties'); ?>" class="button">Back</a>