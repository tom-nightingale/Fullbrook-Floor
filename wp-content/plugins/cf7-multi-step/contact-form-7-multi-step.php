<?php

/**
Plugin Name: Multi Step for Contact Form 7 (Lite)
Plugin URI: https://ninjateam.org/contact-form-7-multi-step/
Description: Break your long form into user-friendly steps.
Version: 2.5
Author: NinjaTeam
Author URI: http://ninjateam.org
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// if (!defined('WPCF7_AUTOP')) {
// define('WPCF7_AUTOP', false);
// }
define( 'CF7MLS_PLUGIN_DIR', dirname( __FILE__ ) );
define( 'CF7MLS_PLUGIN_URL', plugins_url( '', __FILE__ ) );
define( 'CF7MLS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'CF7MLS_NTA_VERSION', '2.5.6' );

require_once CF7MLS_PLUGIN_DIR . '/inc/admin/init.php';
require_once CF7MLS_PLUGIN_DIR . '/inc/admin/settings.php';

require_once CF7MLS_PLUGIN_DIR . '/inc/frontend/init.php';


/*
 * Languages
 */
add_action( 'plugins_loaded', 'cf7mlsLoadTextdomain' );
function cf7mlsLoadTextdomain() {
	load_plugin_textdomain( 'cf7mls', false, plugin_basename( CF7MLS_PLUGIN_DIR ) . '/languages/' );
}
// Add Go Pro Action Link
add_filter( 'plugin_action_links_' . CF7MLS_PLUGIN_BASENAME, 'plugin_action_links' );
function plugin_action_links( $links ) {
	$links[] = '<a target="_blank" href="https://1.envato.market/Multi-Step-Form" style="color: #43B854; font-weight: bold">' . __( 'Go Pro', 'cf7mls' ) . '</a>';
	return $links;
}
add_filter( 'plugin_row_meta', 'plugin_row_meta', 10, 2 );
function plugin_row_meta( $links, $file ) {
	if ( strpos( $file, 'contact-form-7-multi-step.php' ) !== false ) {
		$new_links = array(
			'doc' => '<a href="https://ninjateam.org/contact-form-7-multi-step-pro-doc/" target="_blank">' . __( 'Documentation', 'cf7mls' ) . '</a>',
		);

		$links = array_merge( $links, $new_links );
	}
	return $links;
}
function cf7mls_is_active_cf7db() {
	 return defined( 'CF7D_FILE' );
}
function cf7mls_sanitize_posted_data( $value ) {
	if ( is_array( $value ) ) {
		$value = array_map( 'cf7mls_sanitize_posted_data', $value );
	} elseif ( is_string( $value ) ) {
		$value = wp_check_invalid_utf8( $value );
		$value = wp_kses_no_null( $value );
	}
	return $value;
}
function cf7mls_cf7d_add_more_fields( $posted_data ) {
	// time
	$posted_data['submit_time'] = date( 'Y-m-d H:i:s' );
	// ip
	$posted_data['submit_ip'] = ( isset( $_SERVER['X_FORWARDED_FOR'] ) ) ? $_SERVER['X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
	// user id
	$posted_data['submit_user_id'] = 0;
	if ( function_exists( 'is_user_logged_in' ) && is_user_logged_in() ) {
		$current_user                  = wp_get_current_user(); // WP_User
		$posted_data['submit_user_id'] = $current_user->ID;
	}
	return $posted_data;
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
/*
 * Ajax
 */
add_action( 'wp_ajax_cf7mls_validation', 'cf7mls_validation_callback' );
add_action( 'wp_ajax_nopriv_cf7mls_validation', 'cf7mls_validation_callback' );

function cf7mls_validation_callback() {
	 global $wpdb;

	if ( isset( $_POST['_wpcf7'] ) ) {
		$id       = (int) $_POST['_wpcf7'];
		$unit_tag = wpcf7_sanitize_unit_tag( $_POST['_wpcf7_unit_tag'] );

		$spam = false;
		if ( $contact_form = wpcf7_contact_form( $id ) ) {
			$items = array(
				'mailSent' => false,
				'into'     => '#' . $unit_tag,
				'captcha'  => null,
			);
			/* Begin validation */
			require_once WPCF7_PLUGIN_DIR . '/includes/validation.php';
			$result = new WPCF7_Validation();

			$tags = $contact_form->scan_form_tags(
				array(
					'feature' => '! file-uploading',
				)
			);

			foreach ( $tags as $tag ) {
				$result = apply_filters( 'wpcf7_validate_' . $tag['type'], $result, $tag );
			}
			$result = apply_filters( 'wpcf7_validate', $result, $tags );

			$invalid_fields = $result->get_invalid_fields();
			$return         = array(
				'success'        => $result->is_valid(),
				'invalid_fields' => $invalid_fields,
			);
			if ( $return['success'] == false ) {
				$messages          = $contact_form->prop( 'messages' );
				$return['message'] = $messages['validation_error'];
				if ( empty( $return['message'] ) ) {
					$default_messages  = wpcf7_messages();
					$return['message'] = $default_messages['validation_error']['default'];
				}
			} else {
				$return['message'] = '';
			}
			if ( cf7mls_is_active_cf7db() ) {
				if ( get_post_meta( $id, '_cf7mls_db_save_every_step', true ) == 'yes' ) {
					$_cf7mls_db_form_data_id = ( isset( $_POST['_cf7mls_db_form_data_id'] ) ? intval( $_POST['_cf7mls_db_form_data_id'] ) : '' );
					if ( empty( $_cf7mls_db_form_data_id ) ) {
						$wpdb->insert( $wpdb->prefix . 'cf7_data', array( 'created' => date( 'Y-m-d H:i:s' ) ), array( '%s' ) );
						$_cf7mls_db_form_data_id = $wpdb->insert_id;
					}

					/*
					 * Insert / update to database
					 */
					$contact_form = cf7d_get_posted_data( $contact_form );

					// Modify $contact_form
					$contact_form = apply_filters( 'cf7d_modify_form_before_insert_data', $contact_form );
					$tags         = $contact_form->scan_form_tags();

					$posted_data = $_POST;
					$posted_data = cf7mls_cf7d_add_more_fields( $posted_data );

					foreach ( $tags as $k => $v ) {
						if ( isset( $posted_data[ $v['name'] ] ) ) {
							$posted_data[ $v['name'] ] = cf7mls_sanitize_posted_data( $posted_data[ $v['name'] ] );
						} else {
							unset( $posted_data[ $v['name'] ] );
						}
					}
					// install to database
					$cf7d_no_save_fields = cf7d_no_save_fields();
					foreach ( $posted_data as $k => $v ) {
						if ( in_array( $k, $cf7d_no_save_fields ) ) {
							continue;
						} else {
							if ( is_array( $v ) ) {
								$v = implode( "\n", $v );
							}
							$check_existing = $wpdb->get_results( 'SELECT `id` FROM ' . $wpdb->prefix . 'cf7_data_entry WHERE `cf7_id` = ' . (int) $id . ' AND `data_id` = ' . (int) $_cf7mls_db_form_data_id . " AND `name` = '" . $k . "'" );
							if ( count( $check_existing ) > 0 ) {
								/* Update */
								$data         = array(
									'name'  => $k,
									'value' => $v,
								);
								$data_format  = array( '%s', '%s' );
								$where        = array(
									'cf7_id'  => (int) $id,
									'data_id' => (int) $_cf7mls_db_form_data_id,
								);
								$where_format = array( '%d', '%d' );
								$wpdb->update( $wpdb->prefix . 'cf7_data_entry', $data, $where, $data_format, $where_format );
							} else {
								/* Insert */
								$data   = array(
									'cf7_id'  => (int) $id,
									'data_id' => (int) $_cf7mls_db_form_data_id,
									'name'    => $k,
									'value'   => $v,
								);
								$format = array( '%d', '%d', '%s', '%s' );
								$wpdb->insert( $wpdb->prefix . 'cf7_data_entry', $data, $format );
							}
						}
					}
					$return['_cf7mls_db_form_data_id'] = (int) $_cf7mls_db_form_data_id;
				}
			}
			$json = json_encode( $return );
			exit( $json );
		}
	}
}

/*
 * Dashboard Widget
 *
 */
add_action( 'admin_enqueue_scripts', 'admin_dashboard_load_style' );
function admin_dashboard_load_style() {
	wp_register_style( 'dashboard_cf7mls_css', CF7MLS_PLUGIN_URL . '/assets/admin/css/admin-dashboard.css' );
	wp_enqueue_style( 'dashboard_cf7mls_css' );
}

function wpdocs_add_dashboard_widgets() {
	wp_add_dashboard_widget( 'dashboard_cf7ms', __( 'Contact Form 7 Multi-Step Pro' ), 'dashboard_widget_function' );
}

function dashboard_widget_function( $post, $callback_args ) {   ?>

	<div class="cf7ms-wrap-postbox">
		<div class="cf7mls-postbox-title-wrap">
			<h3>Unlock the Best of Contact Form 7</h3>
		</div>
		<div class="cf7mls-postbox-list-wrap">
			<ul class="cf7mls-list-checked">
				<li>Bring your form to life with progress bar and better design</li>
				<li>Spice up steps with animation effects, and more</li>
				<li>Let audience know what to expect and <strong>collect more leads!</strong></li>
			</ul>
		</div>
		<div class="cf7mls-postbox-img-wrap">
			<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 842 505">
				<image width="842" height="505" xlink:href="data:img/png;base64,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"/>
			</svg>
		</div>
		<div class="cf7mls-postbox-btn-wrap">
			<a class="button button-primary" href="https://1.envato.market/MultiStep-Form" target="_blank">Upgrade to Pro</a>
		</div>
	</div>
	<?php
}

add_action( 'wp_dashboard_setup', 'wpdocs_add_dashboard_widgets' );
