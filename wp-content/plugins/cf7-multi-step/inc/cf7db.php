<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
function cf7mls_is_active_cf7db() {
	 return defined( 'CF7D_FILE' );
}
if ( cf7mls_is_active_cf7db() ) {
	add_filter( 'cf7d_no_save_fields', 'cf7mls_cf7d_no_save_fields' );
	function cf7mls_cf7d_no_save_fields( $fields ) {
		// $fields[] = '_cf7mls_db_form_data_id';
		$fields[] = '_wpnonce';
		$fields[] = 'cf7mls_back';
		$fields[] = 'cf7mls_next';

		return $fields;
	}

	/*
	 * Remove user's informations every steps
	 */
	add_action( 'cf7d_after_insert_db', 'cf7mls_cf7d_after_insert_db', 10, 3 );
	function cf7mls_cf7d_after_insert_db( $contact_form, $form_id, $data_id ) {
		 global $wpdb;
		$data_id_be_delete = $wpdb->get_results( 'SELECT `value` FROM ' . $wpdb->prefix . "cf7_data_entry WHERE `cf7_id` = '" . $form_id . "' AND `name` = '_cf7mls_db_form_data_id'" );
		if ( isset( $data_id_be_delete[0] ) ) {
			$data_id_be_delete = $data_id_be_delete[0]->value;
			// delele data_id
			$wpdb->delete( $wpdb->prefix . 'cf7_data', array( 'id' => $data_id_be_delete ) );
			// delete entry
			$wpdb->delete(
				$wpdb->prefix . 'cf7_data_entry',
				array(
					'cf7_id'  => $form_id,
					'data_id' => $data_id_be_delete,
				)
			);
			$wpdb->delete(
				$wpdb->prefix . 'cf7_data_entry',
				array(
					'cf7_id' => $form_id,
					'name'   => '_cf7mls_db_form_data_id',
				)
			);
		}
	}

	// No save fields cf7mls_step-1, cf7mls_step-2,... before when install to database
	add_filter( 'cf7d_posted_data', 'cf7mls_cf7d_posted_data', 10, 1 );
	function cf7mls_cf7d_posted_data( $contact_form ) {
		 $pattern = '/cf7mls_step-/i';
		foreach ( $contact_form as $k => $v ) {
			if ( preg_match( $pattern, $k ) ) {
				unset( $contact_form[ $k ] );
			}
		}
		return $contact_form;
	}
}

