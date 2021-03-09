<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// add js, css
add_action( 'wp_enqueue_scripts', 'cf7mls_frontend_scripts_callback' );
function cf7mls_frontend_scripts_callback() {
	$cf7d_messages_error = '';

	wp_register_script( 'cf7mls', CF7MLS_PLUGIN_URL . '/assets/frontend/js/cf7mls.js', array( 'jquery' ), CF7MLS_NTA_VERSION, true );
	wp_enqueue_script( 'cf7mls' );

	if ( apply_filters( 'is_using_cf7mls_css', true ) ) {
		wp_register_style( 'cf7mls', CF7MLS_PLUGIN_URL . '/assets/frontend/css/cf7mls.css', array(), CF7MLS_NTA_VERSION );
		wp_enqueue_style( 'cf7mls' );

		wp_register_style( 'cf7mls_animate', CF7MLS_PLUGIN_URL . '/assets/frontend/animate/animate.min.css', array(), CF7MLS_NTA_VERSION );
		wp_enqueue_style( 'cf7mls_animate' );
	}
	wp_localize_script(
		'cf7mls',
		'cf7mls_object',
		array(
			'ajax_url'                 => admin_url( 'admin-ajax.php' ),
			'is_rtl'                   => apply_filters( 'cf7mls_is_rtl', is_rtl() ),
			'disable_submit'           => apply_filters( 'cf7mls_disable_submit', 'true' ),
			'cf7mls_error_message'     => $cf7d_messages_error,
			'scroll_step'              => apply_filters( 'cf7mls-scroll-step', 'true' ),
			'disable_enter_key'        => apply_filters( 'cf7mls-disable-enter-key', 'false' ),
			'check_step_before_submit' => apply_filters( 'cf7mls_check_step_before_submit', 'true' ),
		)
	);
}

/**
 * Wpcf7 shortcode.
 */
function cf7mls_add_shortcode_step() {
	wpcf7_add_form_tag( array( 'cf7mls_step', 'cf7mls_step*' ), 'cf7mls_multistep_shortcode_callback', true );
}
add_action( 'wpcf7_init', 'cf7mls_add_shortcode_step' );
function cf7mls_multistep_shortcode_callback( $tag ) {
	$tag        = new WPCF7_FormTag( $tag );
	$name       = $tag->name;
	$numberStep = (int) explode( '-', $name )[1];
	$back       = $next = false;

	// Check button back last in step.
	$checkBackLast = false;
	if ( count( $tag->values ) == 2 ) {
		if ( $numberStep === 1 ) {
			$next = $tag->values[0];
		} else {
			$checkBackLast = true;
			$back          = $tag->values[0];
		}
	} elseif ( count( $tag->values ) > 2 ) {
		$back = $tag->values[0];
		$next = $tag->values[1];
	}

	$html = '<div class="cf7mls-btns">';
	// TODO add form id to btn to prevent duplicate
	if ( $checkBackLast === true && $back ) {
		$html  = '';
		$html .= apply_filters( 'cf7_step_before_back_btn', '', $name );
		$html .= '<button type="button" class="cf7mls_back action-button" name="cf7mls_back" id="cf7mls-back-btn-' . $name . '">' . $back . '</button>';
		$html .= apply_filters( 'cf7_step_after_back_btn', '', $name );
	} elseif ( $back ) {
		$html .= apply_filters( 'cf7_step_before_back_btn', '', $name );
		$html .= '<button type="button" class="cf7mls_back action-button" name="cf7mls_back" id="cf7mls-back-btn-' . $name . '">' . $back . '</button>';
		$html .= apply_filters( 'cf7_step_after_back_btn', '', $name );
	}

	// TODO add form id to btn to prevent duplicate
	if ( $next ) {
		$loader = apply_filters( 'cf7mls_loader_img', CF7MLS_PLUGIN_URL . '/assets/frontend/img/loader.svg' );
		$html  .= apply_filters( 'cf7_step_before_next_btn', '', $name );

		$html .= '<button type="button" class="cf7mls_next cf7mls_btn action-button" name="cf7mls_next" id="cf7mls-next-btn-' . $name . '">' . $next . '<img src="' . $loader . '" alt="" /></button>';
		$html .= apply_filters( 'cf7_step_after_next_btn', '', $name );
	}
	$contact_form         = wpcf7_get_current_contact_form();
	if ( $checkBackLast === false ) {
		$html .= '</div></fieldset><fieldset class="fieldset-cf7mls">';
	}
	// else {
	// $html .= '</div></fieldset>';
	// }

	return $html;
}

/**
 * Wrap form
 */
add_filter( 'wpcf7_form_elements', 'cf7mls_wrap_form_elements_func', 10 );
function cf7mls_wrap_form_elements_func( $code ) {
	if ( $contact_form = wpcf7_get_current_contact_form() ) {
		/* If the form has multistep's shortcode */
		if ( strpos( $code, '<fieldset class="fieldset-cf7mls' ) ) {
			if ( defined( 'WPCF7_AUTOP' ) && ( WPCF7_AUTOP == true ) ) {
				$code = preg_replace( '#<p>(.*?)<\/fieldset><fieldset class=\"fieldset-cf7mls\"><\/p>#', '$1</fieldset><fieldset class="fieldset-cf7mls">', $code );
			}
			// progress bar
			$code = '<div class="fieldset-cf7mls-wrapper" data-transition-effects><fieldset class="fieldset-cf7mls">' . $code;

			$code .= '</fieldset></div>';
			// $code .= '</fieldset>';
		}
	}
	$ex = explode( '<fieldset class="fieldset-cf7mls">', $code );
	if ( count( $ex ) > 1 ) {
		$code = '';
		foreach ( $ex as $k => $v ) {
			$code .= $v;
			if ( $k == 0 ) {
				$code .= '<fieldset class="fieldset-cf7mls cf7mls_current_fs">';
			} elseif ( $k < ( count( $ex ) - 1 ) ) {
				$code .= '<fieldset class="fieldset-cf7mls">';
			}
		}
	}
	return $code;
}

// add css to wp_head
add_action( 'wp_head', 'cf7mls_css_to_wp_head' );
function cf7mls_css_to_wp_head() {
	$args  = array(
		'post_type'   => 'wpcf7_contact_form',
		'post_status' => 'publish',
	);
	$query = new WP_Query( $args );
	if ( $query->have_posts() ) {
		echo '<style type="text/css">';
		while ( $query->have_posts() ) {
			$query->the_post();
			$id              = get_the_id();
			$next_bg_color   = get_post_meta( $id, '_cf7mls_next_bg_color', true );
			$next_text_color = get_post_meta( $id, '_cf7mls_next_text_color', true );

			$back_bg_color   = get_post_meta( $id, '_cf7mls_back_bg_color', true );
			$back_text_color = get_post_meta( $id, '_cf7mls_back_text_color', true );

			echo 'div[id^="wpcf7-f' . $id . '-p"] button.cf7mls_next { ' . ( ( ! empty( $next_bg_color ) ) ? 'background-color: ' . $next_bg_color . ';' : '' ) . ' ' . ( ( ! empty( $next_text_color ) ) ? 'color: ' . $next_text_color : '' ) . ' }';
			echo 'div[id^="wpcf7-f' . $id . '-p"] button.cf7mls_back { ' . ( ( ! empty( $back_bg_color ) ) ? 'background-color: ' . $back_bg_color . ';' : '' ) . ' ' . ( ( ! empty( $back_text_color ) ) ? 'color: ' . $back_text_color : '' ) . ' }';
		}
		echo '</style>';
	}
	wp_reset_postdata();
}
add_filter( 'wpcf7_form_class_attr', 'cf7mls_add_auto_scroll_class' );
function cf7mls_add_auto_scroll_class( $class ) {
	if ( $contact_form = wpcf7_get_current_contact_form() ) {
		if ( empty( trim( ( get_post_meta( $contact_form->id(), '_cf7_mls_auto_scroll_animation', true ) ) ) ) ) {
			$class .= ' cf7mls-no-scroll';
		}
		$class .= ' cf7mls-no-moving-animation';
	}
	return $class;
}
