<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
add_filter( 'wpcf7_editor_panels', 'cf7mls_admin_settings' );
function cf7mls_admin_settings( $panels ) {
	 $panels['cf7mls-settings-panel'] = array(
		 'title'    => __( 'Multi-Step Settings', 'cf7mls' ),
		 'callback' => 'cf7mls_settings_func',
	 );
	 $panels['cf7mls-progress-bar']   = array(
		 'title'    => __( 'Progress Bar (PRO)', 'cf7mls' ),
		 'callback' => 'cf7mls_progress_bar_func',
	 );
	 return $panels;
}
function cf7mls_settings_func( $post ) {    ?>
	<div id="cf7mls_multi_step_wrap" class="cf7mls_multi_step_wrap">
		<h2 class="cf7mls-title cf7mls-title-color"><?php echo esc_html( __( 'Color', 'cf7mls' ) ); ?></h2>
				
		<fieldset class="cf7mls-group-color">
					<legend class="cf7mls-color-caption"><?php _e( 'You can change the background-color or text-color of Back, Next buttons here.', 'cf7mls' ); ?></legend>
					
					<div class="cf7mls-group-color-bt-back">
						<p class="cf7mls-title"><?php _e( 'Back Button', 'cf7mls' ); ?></p>

						<div class="cf7mls-wrap-bg-color">
							<p class="cf7mls-label"><?php _e( 'BG color', 'cf7mls' ); ?></p>
							<input type="text" class="cf7mls-color-field" name="back-btn-bg-color" value="<?php echo $post->prop( 'cf7mls_back_bg_color' ); ?>" />
						</div>

						<div class="cf7mls-wrap-text-color">
							<p class="cf7mls-label"><?php _e( 'Text color', 'cf7mls' ); ?></p>
							<input type="text" class="cf7mls-color-field" name="back-btn-text-color" value="<?php echo $post->prop( 'cf7mls_back_text_color' ); ?>" />
						</div>
					</div>
					
					<div class="cf7mls-group-color-bt-next">
						<p class="cf7mls-title"><?php _e( 'Next Button', 'cf7mls' ); ?></p>
						
						<div class="cf7mls-wrap-bg-color">
							<p class="cf7mls-label"><?php _e( 'BG color', 'cf7mls' ); ?></p>
							<input type="text" class="cf7mls-color-field" name="next-btn-bg-color" value="<?php echo $post->prop( 'cf7mls_next_bg_color' ); ?>" />
						</div>

						<div class="cf7mls-wrap-text-color">
							<p class="cf7mls-label"><?php _e( 'Text color', 'cf7mls' ); ?></p>
							<input type="text" class="cf7mls-color-field" name="next-btn-text-color" value="<?php echo $post->prop( 'cf7mls_next_text_color' ); ?>" />
						</div>
					</div>
				</fieldset>
				
				<div class="cf7mls-auto-scroll-wrap">
					<div class="cf7mls-auto-scroll-title-wrap">
						<h2 class="cf7mls-auto-scroll-title"><?php echo esc_html( __( 'Auto Scroll to Top', 'cf7mls' ) ); ?></h2>
						<a tooltip="It will be scrolled to the top of the form after each step." class="cf7mls-tooltip">
								<img src=<?php echo ( '"' . CF7MLS_PLUGIN_URL . '/assets/admin/img/help-circle.svg' . '"' ); ?> alt="help circle">
						</a>
					</div>
					<div class="cf7mls-wrap-switch cf7mls-wrap-switch-scroll">
						<label class="cf7mls_switch">
								<input class="cf7mls_scroll_animation" type="checkbox" name="auto-scroll-animation" <?php echo ( $post->prop( 'cf7_mls_auto_scroll_animation' ) ? 'checked' : '' ); ?> value="on" />
								<span class="cf7mls_slider cf7mls_round"></span>
						</label>
					</div>
				</div>
				
				<div class="cf7mls-transition-effects-wrap">
					<h2 class="cf7mls-transition-effects-wrap-title"><?php echo _e( 'Transition Effects (PRO)', 'cf7mls' ); ?></h2>
					<div class="cf7mls-wrap-switch">
						<a tooltip="This feature only available in PRO version." class="cf7mls-tooltip">
							<label class="cf7mls_switch">
								<input class="cf7mls_toggle_transition_effects" type="checkbox" disabled name="auto-moving-animation" value="on" />
								<span class="cf7mls_slider cf7mls_round"></span>
							</label>
						</a>	
					</div>
					<div class="cf7mls-stype-transition-wrap">
						<h2 class="cf7mls_stype_transition_title"><?php echo _e( 'Animation', 'cf7mls' ); ?></h2>
						<div class="cf7mls_select_stype_transition">
								<select name="cf7mls_select_stype_transition" id="cf7mls_select_stype_transition">
									<?php
										$selected_style_tran = trim( $post->prop( 'cf7mls_select_stype_transition' ) );
										$stype_transitions   = array(
											'in_fadeIn next_fadeInRight back_fadeInLeft' => 'Fade In',
											'in_fadeInUp next_fadeInUp back_fadeInUp' => 'Fade Up',
										);
										foreach ( $stype_transitions as $key_transition => $stype_transition ) {
												echo sprintf( '<option value="%1$s" %2$s>%3$s</option>', $key_transition, selected( trim( $key_transition ), $selected_style_tran, false ), esc_html( __( $stype_transition, 'cf7mls' ) ) );
										}
										?>
								</select>
						</div>
					</div>
				</div>
		</div>

	<?php
	if ( cf7mls_is_active_cf7db() ) {
		?>
		<h2><?php echo esc_html( __( 'Save to database', 'cf7mls' ) ); ?></h2>
		<fieldset>
			<p class="description">
				<label for="cf7mls_db_save_every_step">
					<?php _e( 'Save form\'s every step?', 'cf7mls' ); ?>
					<br />
					<input type="checkbox" name="cf7mls_db_save_every_step" value="yes" id="cf7mls_db_save_every_step" <?php echo checked( $post->prop( 'cf7mls_db_save_every_step' ), 'yes' ); ?> />
				</label>
			</p>
		</fieldset>
		<?php
	}
}
function cf7mls_progress_bar_func( $post ) {

	?>
	<div class="cf7mls-progress-bar-upgrade-pro-wrap">
		<span>This features only available in PRO version - </span>
		<a href="https://1.envato.market/Contact-Form-7-Multi-Step" class="cf7mls-progress-bar-upgrade-pro-title">Upgrade Now</a>
	</div>
	<div class="cf7mls_pogress_bar_wrap">
		<div class="cf7mls-group-pogress-bar"> 
			<div class="cf7mls-pogress-bar">
				<h2 class="cf7mls-title-pogress-bar"><?php echo esc_html( __( 'Progress Bar', 'cf7mls' ) ); ?></h2>
				<div class="cf7mls-wrap-switch">
					<label class="cf7mls_switch cf7mls_progress_bars_witch">
						<input class="cf7mls_enable_progress_bar" disabled type="checkbox" id="cf7_mls_enable_progress_bar" name="cf7_mls_enable_progress_bar" checked="checked" value="1" />
						<span class="cf7mls_slider cf7mls_round"></span>
					</label>
				</div>
			</div>

			<div class="cf7mls_bg_color_wrap cf7mls_bg_color_progress">
				<h2><?php _e( 'Background Color', 'cf7mls' ); ?></h2> 
				<input disabled type="text" class="cf7mls_progress_bar_filter" name="progress-bar-bg-color" value="#0073aa" />
			</div>
			
			<div class="cf7mls_progress_style_wrap">
				<h2><?php _e( 'Progress Bar Style', 'cf7mls' ); ?></h2>

				<select name="cf7mls_progress_bar_style" id="cf7mls_progress_bar_style" disabled>
					<option value="navigation_horizontal" selected="selected">Horizontal</option>
					<option value="horizontal">Vertical</option>
				</select>
				<h2><?php _e( 'Border Style', 'cf7mls' ); ?></h2>

				<select name="cf7mls_progress_bar_icon_style" id="cf7mls_progress_bar_icon_style" disabled>
					<option value="squaren" selected="selected">Squaren</option>
					<option value="round">Round</option>
				</select>

			</div>

			<div class="title_options_wrap">
				<h2><?php _e( 'Title Options', 'cf7mls' ); ?></h2>
				<div class="cf7mls-select-style-text">
					<input value="vertical" name="cf7mls-style-text" type="text" class="cf7mls-style-text hidden" />

					<div data-style-text="horizontal" class="cf7mls-style-text-wrap">
						<p class="cf7mls-style-text"><?php _e( 'Horizontal text', 'cf7mls' ); ?></p>
					</div>

					<div data-style-text="vertical" class="cf7mls-style-text-wrap active">
						<p class="cf7mls-style-text"><?php _e( 'Vertical text', 'cf7mls' ); ?></p>
					</div>

					<div data-style-text="no" class="cf7mls-style-text-wrap">
						<p class="cf7mls-style-text"><?php _e( 'No text', 'cf7mls' ); ?></p>
					</div>
				</div>
			</div>

			<div class="cf7mls-pogress-bar-percent">
				<h2 class="cf7mls-title-pogress-bar-percent"><?php echo esc_html( __( 'Progress Bar Percent', 'cf7mls' ) ); ?></h2>
				<div class="cf7mls-wrap-switch">
					<label class="cf7mls_switch cf7mls_progress_bars_witch">
						<input class="cf7mls_enable_progress_bar_percent" disabled type="checkbox" id="cf7_mls_enable_progress_bar_percent" name="cf7_mls_enable_progress_bar_percent" checked="checked" value="1" />
						<span class="cf7mls_slider cf7mls_round"></span>
					</label>
				</div>
				<div class="cf7mls_bg_color_wrap cf7mls_bg_color_progress_percent">
					<h2><?php _e( 'Background Color', 'cf7mls' ); ?></h2> 
					<input disabled type="text" class="cf7mls_progress_bar_percent_filter" disabled name="progress-bar-percent-color" value="#0073aa" />
				</div>
			</div>

			<div class="cf7mls-allow-choose-step-wrap">
				<input id="cf7mls-allow-choose-step" type="checkbox" name="cf7mls-allow-choose-step" checked="checked" value="on" disabled />
				<label for="cf7mls-allow-choose-step" class="cf7mls-allow-choose-step-checkbox" data-checked="on"></label>
				<span class="cf7mls-allow-choose-step-text">Allow Choose Step
					<!-- <a tooltip="User can click on each step to see its content before fill" class="cf7mls-tooltip"> -->
						<img src=<?php echo ( '"' . CF7MLS_PLUGIN_URL . '/assets/admin/img/help-circle.svg' . '"' ); ?> alt="help circle">
					<!-- </a> -->
				</span>
			</div>

		</div>
	</div>
	<div class="cf7mls_preview">
		<div class="cf7mls_browser">   
			<div class="cf7mls_circle_wrap">
				<div class="cf7mls_circle cf7mls_red_circle"></div>
				<div class="cf7mls_circle cf7mls_yellow_circle"></div>
				<div class="cf7mls_circle cf7mls_green_circle"></div>
			</div> 
			
			<div class="cf7mls_block">
				
				<div class="cf7mls_check_step_progress_bar">

					<?php
					$cf7mls_steps = array(
						0 => 'Step 1',
						1 => 'Step 2',
						2 => 'Step 3',
					);
					?>

					<ul id="cf7mls_progress_bar" data-bg-color="#0073aa" class="cf7mls_progress_bar cf7mls_bar_style_navigation_horizontal_squaren cf7mls_bar_style_text_vertical" data-width-progress-bar="42%">

					<?php

					foreach ( $cf7mls_steps as $k => $v ) {
						if ( $k < 3 && count( $cf7mls_steps ) > 1 ) {
							$format_step  = '';
							$format_step .= '<li class="cf7_mls_steps_item" style="width : 33.3333%">';
							$format_step .= '<div class="cf7_mls_steps_item_container">';
							$format_step .= '<div class="cf7_mls_steps_item_icon">';
							$format_step .= '<span class="cf7_mls_count_step">' . ( (int) $k + 1 ) . '</span>';
							$format_step .= '<span class="cf7_mls_check">';
							$format_step .= '<i>';
							$format_step .= '<svg viewBox="64 64 896 896" data-icon="check" width="14px" height="14px" fill="currentColor" aria-hidden="true" focusable="false" class="">';
							$format_step .= '<path d="M912 190h-69.9c-9.8 0-19.1 4.5-25.1 12.2L404.7 724.5 207 474a32 32 0 0 0-25.1-12.2H112c-6.7 0-10.4 7.7-6.3 12.9l273.9 347c12.8 16.2 37.4 16.2 50.3 0l488.4-618.9c4.1-5.1.4-12.8-6.3-12.8z"></path>';
							$format_step .= '</svg>';
							$format_step .= '</i>';
							$format_step .= '</span>';
							$format_step .= '</div>';
							$format_step .= '<div class="cf7_mls_steps_item_content">';
							$format_step .= '<p class="cf7mls_progress_bar_title">' . $v . '</p>';
							$format_step .= '<span class="cf7_mls_arrow_point_to_righ">';
							$format_step .= '<i>';
							$format_step .= '<svg x="0px" y="0px" width="8px" height="14px" viewBox="0 0 451.846 451.847" style="enable-background:new 0 0 451.846 451.847; xml:space="preserve">';
							$format_step .= '<g>';
							$format_step .= '<path d="M345.441,248.292L151.154,442.573c-12.359,12.365-32.397,12.365-44.75,0c-12.354-12.354-12.354-32.391,0-44.744
											L278.318,225.92L106.409,54.017c-12.354-12.359-12.354-32.394,0-44.748c12.354-12.359,32.391-12.359,44.75,0l194.287,194.284
											c6.177,6.18,9.262,14.271,9.262,22.366C354.708,234.018,351.617,242.115,345.441,248.292z"/>';
							$format_step .= '</g>';
							$format_step .= '</svg>';
							$format_step .= '</i>';
							$format_step .= '</span>';
							$format_step .= '</div>';
							$format_step .= '</div>';
							$format_step .= '</li>';
							echo ( $format_step );
						} else {
								break;
						}
					}

					?>

					</ul>

					<!-- Show in ipad, mobie phone -->
					<?php
					if ( count( $cf7mls_steps ) > 1 ) {
						?>
					
						<div class="cf7mls_number_step_wrap">
							<p class="cf7mls_number">2/3</p>
							<p class="cf7mls_step_current">Step 2</p>
							<div class="cf7mls_progress_percent">
								<div class="cf7mls_progress_bar_percent">
									<div class="cf7mls_progress_barinner" style="width: 50%"></div>
								</div>
							</div>
						</div>

						<?php
					}
					?>
				</div>

				<div class="cf7mls_form_demo_one"></div>
				<div class="cf7mls_form_demo_two"></div>
				<div class="cf7mls_form_textarea_demo"></div>
				<div>
					<div class="cf7mls_bt_wrap">
						<div class="cf7mls_back_demo"><?php _e( 'Back', 'cf7mls' ); ?></div>
						<div class="cf7mls_next_demo"><?php _e( 'Next', 'cf7mls' ); ?></div>
					</div>
				</div>
				<!-- Progress Bar percent on ipad, mobie, computer-->
				<div class="cf7mls_progress_bar_per_mobie_wrap">
					<div class="cf7mls_progress_percent">
						<div class="cf7mls_progress_bar_percent">
							<div class="cf7mls_progress_barinner" style="<?php echo( ( count( $cf7mls_steps ) == 2 ) ? 'width: 100%' : 'width: 50%' ); ?>"></div>
						</div>
					</div>
					<div>
						<p><?php echo( ( count( $cf7mls_steps ) == 2 ) ? '100%' : '50%' ); ?></p>
					</div>
				</div>
			</div>
		</div>
		<p class="cf7mls_note"><?php _e( 'Preview only shows 3 steps.', 'cf7mls' ); ?></p>
	</div>
	<?php
}
add_filter( 'wpcf7_contact_form_properties', 'cf7mls_form_properties' );
function cf7mls_form_properties( $properties ) {
	// Check data old of old version when active plugin.
	if ( is_array( maybe_unserialize( $properties ) ) && array_key_exists( 'cf7mls_step_name', $properties ) && array_key_exists( 'form', $properties ) ) {
		$cf7mls_step_name = maybe_unserialize( $properties['cf7mls_step_name'] );
		$manager          = WPCF7_FormTagsManager::get_instance();
		$scan             = $manager->scan( $properties['form'] );
		$checkData        = false;
		foreach ( $scan as $k => $v ) {
			if ( $v->type == 'cf7mls_step' ) {
				if ( count( $v->values ) == 1 ) {
						$checkData = true;
						break;
				}
			}
		}

		if ( $checkData ) {
			$forms = explode( ']', $properties['form'] );
			$n     = 0;
			if ( is_array( $forms ) ) {
				foreach ( $forms as $key => $form ) {
					if ( strstr( $form, 'cf7mls_step' ) ) {
						$forms[ $key ] = $forms[ $key ] . ' "' . $cf7mls_step_name[ $n ] . '"';
						$n++;
					}

					if ( strstr( $form, '[' ) ) {
						$forms[ $key ] = $forms[ $key ] . ']';
					}

					if ( ( count( $forms ) - 1 ) == $key ) {
						$stepLast      = '';
						$stepLast     .= '[cf7mls_step cf7mls_step-' . $key;
						$stepLast     .= ' "' . $properties['cf7mls_back_button_title'] . '"';
						$stepLast     .= ' "' . $cf7mls_step_name[ $n ] . '"]';
						$forms[ $key ] = $stepLast . $forms[ $key ];
					}
				}
				$forms              = implode( '', $forms );
				$properties['form'] = $forms;
			}
		}
	}

	// Add variable cf7mls_back_button_title to check data old of old version when active plugin.
	$more_properties = array(
		'cf7mls_back_bg_color'                => '',
		'cf7mls_back_text_color'              => '',
		'cf7mls_next_bg_color'                => '',
		'cf7mls_next_text_color'              => '',
		'cf7mls_db_save_every_step'           => '',
		'cf7mls_step_name'                    => json_encode( array() ),
		'cf7mls_progress_bar_style'           => 'navigation_horizontal_squaren',
		'cf7_mls_enable_progress_bar'         => '0',
		'cf7mls_progress_bar_bg_color'        => '#0073aa',
		'cf7_mls_auto_scroll_animation'       => '',
		'cf7_mls_auto_moving_animation'       => '',
		'cf7mls_select_stype_transition'      => '',
		'cf7mls_style_text'                   => 'vertical',
		'cf7_mls_enable_progress_bar_percent' => '0',
		'cf7mls_progress_bar_percent_color'   => '#0073aa',
		'cf7mls_allow_choose_step'            => 'off',
		'cf7mls_back_button_title'            => '',
	);
	return array_merge( $more_properties, $properties );
}

if ( ! function_exists( 'cf7mls_sanitize_arr' ) ) {
	function cf7mls_sanitize_arr( $arr ) {
		return is_array( $arr ) ? array_map( 'cf7mls_sanitize_arr', $arr ) : sanitize_text_field( $arr );
	}
}

add_action( 'wpcf7_save_contact_form', 'cf7mls_save_contact_form' );
function cf7mls_save_contact_form( $contact_form ) {
	$properties = $contact_form->get_properties();

	if ( isset( $_POST['back-btn-bg-color'] ) ) {
		$properties['cf7mls_back_bg_color'] = trim( sanitize_text_field( $_POST['back-btn-bg-color'] ) );
	}
	if ( isset( $_POST['back-btn-text-color'] ) ) {
		$properties['cf7mls_back_text_color'] = trim( sanitize_text_field( $_POST['back-btn-text-color'] ) );
	}
	if ( isset( $_POST['next-btn-bg-color'] ) ) {
		$properties['cf7mls_next_bg_color'] = trim( sanitize_text_field( $_POST['next-btn-bg-color'] ) );
	}
	if ( isset( $_POST['next-btn-text-color'] ) ) {
		$properties['cf7mls_next_text_color'] = trim( sanitize_text_field( $_POST['next-btn-text-color'] ) );
	}
	if ( isset( $_POST['cf7mls_db_save_every_step'] ) ) {
		$properties['cf7mls_db_save_every_step'] = 'yes';
	} else {
		$properties['cf7mls_db_save_every_step'] = 'no';
	}
	if ( isset( $_POST['cf7mls_step_name'] ) ) {
		$properties['cf7mls_step_name'] = cf7mls_sanitize_arr( $_POST['cf7mls_step_name'] );
	}
	if ( isset( $_POST['auto-scroll-animation'] ) ) {
		$properties['cf7_mls_auto_scroll_animation'] = trim( sanitize_text_field( $_POST['auto-scroll-animation'] ) );
	} else {
		$properties['cf7_mls_auto_scroll_animation'] = '';
	}
	if ( isset( $_POST['auto-moving-animation'] ) ) {
		$properties['cf7_mls_auto_moving_animation'] = trim( sanitize_text_field( $_POST['auto-moving-animation'] ) );
	} else {
		$properties['cf7_mls_auto_moving_animation'] = '';
	}
	$contact_form->set_properties( $properties );
}
