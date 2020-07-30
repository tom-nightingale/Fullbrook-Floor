<?php

include('../../../wp-load.php');

global $wpdb;

$options = get_option( 'propertyhive_property_import' );
if (isset($options[$_GET['import_id']]))
{
	$options = $options[$_GET['import_id']];
}
else
{
	$options = array();
}

switch ( $options['format'] )
{
	case "blm_local":
	case "xml_estatesit":
	case "xml_decorus":
	{
		$file = $options['local_directory'] . '/' . base64_decode($_GET['file']);
		header('Content-Disposition: attachment; filename="' . base64_decode($_GET['file']) . '"');
		readfile($file);
    	exit;
	}
	default:
	{
		die('Unknown format: ' . $options['format']);
	}
}