<?php
if ( ! class_exists( 'NjtCF7MLSValidation' ) ) {
	class NjtCF7MLSValidation {

		public function __construct() {
			$this->doHooks();
		}

		private function doHooks() {
			add_action( 'wp_ajax_cf7mls_validation', array( $this, 'cf7mls_validation_callback' ) );
			add_action( 'wp_ajax_nopriv_cf7mls_validation', array( $this, 'cf7mls_validation_callback' ) );
		}
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

					$invalid_fields        = $result->get_invalid_fields();
					$upload_files          = $this->unship_uploaded_files( $contact_form );
					$success               = false;
					$invalid_fields_return = array_merge( $result->get_invalid_fields(), $upload_files['invalid_fields'] );
					if ( false === $result->is_valid() ) {
						$success = $result->is_valid();
					} else {
						$success = $upload_files['valid'];
					}
					$return = array(
						'success'        => $success,
						'invalid_fields' => $invalid_fields_return,
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
							$posted_data = $this->cf7mls_cf7d_add_more_fields( $posted_data );

							foreach ( $tags as $k => $v ) {
								if ( isset( $posted_data[ $v['name'] ] ) ) {
									$posted_data[ $v['name'] ] = $this->cf7mls_sanitize_posted_data( $posted_data[ $v['name'] ] );
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
		public function cf7mls_sanitize_posted_data( $value ) {
			if ( is_array( $value ) ) {
				$value = array_map( 'cf7mls_sanitize_posted_data', $value );
			} elseif ( is_string( $value ) ) {
				$value = wp_check_invalid_utf8( $value );
				$value = wp_kses_no_null( $value );
			}
			return $value;
		}
		public function cf7mls_cf7d_add_more_fields( $posted_data ) {
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
		private function unship_uploaded_files( $contact_form ) {
			$file_result = new WPCF7_Validation();

			$file_tags = $contact_form->scan_form_tags(
				array(
					'feature' => 'file-uploading',
				)
			);

			foreach ( $file_tags as $tag ) {
				if ( false != $tag->is_required() ) {
					$file = isset( $_FILES[ $tag->name ] ) ? $_FILES[ $tag->name ] : null;
					$args = array(
						'tag'       => $tag,
						'name'      => $tag->name,
						'required'  => $tag->is_required(),
						'filetypes' => $tag->get_option( 'filetypes' ),
						'limit'     => $tag->get_limit_option(),
					);

					$new_files = wpcf7_unship_uploaded_file( $file, $args );

					$file_result = apply_filters(
						"wpcf7_validate_{$tag->type}",
						$file_result,
						$tag,
						array(
							'uploaded_files' => $new_files,
						)
					);
				}
			}

			$file_invalid_fields = $file_result->get_invalid_fields();
			 return array(
				 'valid'          => $file_result->is_valid(),
				 'invalid_fields' => $file_invalid_fields,
			 );
		}
	}
	new NjtCF7MLSValidation();
}
