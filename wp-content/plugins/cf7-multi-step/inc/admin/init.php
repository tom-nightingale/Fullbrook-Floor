<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! function_exists( 'cf7cmlsIsMoanaActivated' ) ) {
	function cf7cmlsIsMoanaActivated() {
		return defined( 'CF7_VC_DIR' );
	}
}

add_filter( 'wpcf7_editor_panels', 'cf7mls_wpcf7_editor_panels' );
function cf7mls_wpcf7_editor_panels( $panels ) {
	$panels['form-panel'] = array(
		'title'    => __( 'Form', 'contact-form-7' ),
		'callback' => 'cf7mls_wpcf7_editor_panel_form',
	);
	return $panels;
}
function cf7mls_wpcf7_editor_panel_form( $post ) {
	$desc_link   = wpcf7_link(
		__( 'https://contactform7.com/editing-form-template/', 'contact-form-7' ),
		__( 'Editing Form Template', 'contact-form-7' )
	);
	$description = __( 'You can edit the form template here. For details, see %s.', 'contact-form-7' );
	$description = sprintf( esc_html( $description ), $desc_link );
	?>

  <h2 class="cf7mls-title-form"><?php echo esc_html( __( 'Form', 'contact-form-7' ) ); ?></h2>
  <fieldset class="cf7mls-wrap-form">
	<legend class="cf7mls-description-form"><?php echo $description; ?></legend>
	<?php
	  $tag_generator = WPCF7_TagGenerator::get_instance();
	  $tag_generator->print_buttons();
	?>
	<textarea id="wpcf7-form" name="wpcf7-form" cols="100" rows="24" class="large-text code" data-config-field="form.body"><?php echo esc_textarea( $post->prop( 'form' ) ); ?></textarea>
	<div id="cf7mls-app"></div>
	<div id="cf7mls_PostBoxUpgradePro" style="display:none;"></div>
  </fieldset>
  
	<?php
}

// add js, css
add_action( 'admin_enqueue_scripts', 'cf7mls_admin_scripts_callback' );
function cf7mls_admin_scripts_callback( $hook_suffix ) {
	$load_js_css = false;
	if ( ( substr( $hook_suffix, -15 ) == '_page_wpcf7-new' ) || ( $hook_suffix == 'toplevel_page_wpcf7' ) ) {
		$load_js_css = true;
	}
	if ( $load_js_css === true ) {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		wp_enqueue_style( 'vue-css', CF7MLS_PLUGIN_URL . '/assets/dist/css/main.css' );
		wp_register_script( 'vue-js', CF7MLS_PLUGIN_URL . '/assets/dist/js/main.js' );
		wp_enqueue_script( 'vue-js' );
		wp_localize_script(
			'vue-js',
			'cf7_mls_vue_js',
			array(
				'textInputStep'           => __( 'Step Name', 'contact-form-7' ),
				'textInputBack'           => __( 'Back Button', 'contact-form-7' ),
				'textInputNext'           => __( 'Next Button', 'contact-form-7' ),
				'textEditMoana'           => __( 'Edit With Moana', 'cf7mls' ),
				'cf7cmlsIsMoanaActivated' => cf7cmlsIsMoanaActivated(),
			)
		);

		wp_register_script( 'cf7mls', CF7MLS_PLUGIN_URL . '/assets/admin/js/cf7mls.js', array( 'jquery' ) );
		wp_enqueue_script( 'cf7mls' );

		$form_content = '';
		$manager      = WPCF7_FormTagsManager::get_instance();
		if ( isset( $_GET['post'] ) && (int) $_GET['post'] > 0 ) {
			$form         = WPCF7_ContactForm::get_instance( sanitize_text_field( $_GET['post'] ) );
			$form_content = $form->prop( 'form' );
		} else {
			$form_content = WPCF7_ContactFormTemplate::get_default( 'form' );
		}
		$scan = $manager->scan( $form_content );

		$steps = array();

		$numberStep = 0;
		foreach ( $scan as $k => $v ) {
			if ( $v->type == 'cf7mls_step' ) {
				if ( count( $v->values ) == 2 ) {
							$numberStep = (int) ( explode( '-', $v->name )[1] );

					if ( $numberStep === 1 ) {
						$steps[] = array(
							'back'  => '',
							'next'  => $v->values[0],
							'title' => $v->values[1],
						);
					} else {
						$steps[] = array(
							'back'  => $v->values[0],
							'next'  => '',
							'title' => $v->values[1],
						);
					}
				} elseif ( count( $v->values ) == 3 ) {
								  $steps[] = array(
									  'back'  => $v->values[0],
									  'next'  => $v->values[1],
									  'title' => $v->values[2],
								  );
				}
			}
		}
		wp_localize_script(
			'cf7mls',
			'cf7mls',
			array(
				'steps'      => $steps,
				'cf7mls_app' => null,
			)
		);

		wp_register_style( 'cf7mls', CF7MLS_PLUGIN_URL . '/assets/admin/css/cf7mls.css' );
		wp_enqueue_style( 'cf7mls' );

		wp_register_style( 'cf7mls_progress_bar', CF7MLS_PLUGIN_URL . '/assets/frontend/css/progress_bar.css' );
		wp_enqueue_style( 'cf7mls_progress_bar' );
	}
}

/**
 * Add step buttin to the wpcf7 tag generator.
 */
function cf7mls_add_tag_generator_multistep() {
	if ( class_exists( 'WPCF7_TagGenerator' ) ) {
		$tag = WPCF7_TagGenerator::get_instance();
		$tag->add(
			'cf7mls_step',
			__( 'Step', 'cf7mls' ),
			'cf7mls_multistep_tag_generator_callback'
		);
	}
}
add_action( 'admin_init', 'cf7mls_add_tag_generator_multistep', 30 );
/**
 * [cf7mls_multistep_tag_generator_callback description]
 */
function cf7mls_multistep_tag_generator_callback( $contact_form, $args = '' ) {
	 $args = wp_parse_args( $args, array() );
	?>
<div class="control-box">
	<fieldset>
		<legend><?php _e( 'Generate buttons for form\'s steps.', 'cf7mls' ); ?></legend>
		<table class="form-table cf7mls-table">
			<tbody>
				<tr>
					<th scope="row"><label for="tag-generator-panel-cf7mls_step-name"><?php _e( 'Name', 'cf7mls' ); ?></label></th>
					<td><input type="text" id="tag-generator-panel-cf7mls_step-name" class="tg-name oneline" name="name"></td>
				</tr>
				<tr>
					<th scope="row">                        
						<label for="tag-generator-panel-cf7mls_step-btns-title"><?php _e( 'Back, Next Buttons Title', 'cf7mls' ); ?></label>
					</th>
					<td>
						<textarea name="values" id="tag-generator-panel-cf7mls_step-btns-title" class="cf7mls-values"><?php echo "Back\nNext"; ?></textarea>
						<br />
						<label for="tag-generator-panel-cf7mls_step-back">
							<span class="description"><?php _e( 'One title per line. Back Button\'s title on the first line and Next Button\'s title on the second line.<br />If this is a first step, type only one line for Next Button', 'cf7mls' ); ?></span>
						</label>
					</td>
				</tr>
			</tbody>
		</table>
	</fieldset>
</div>
<div class="insert-box">
	
	<input type="text" name="cf7mls_step" class="tag code" readonly="readonly" onfocus="this.select()" />

	<div class="submitbox">
		<input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr( __( 'Insert Tag', 'cf7mls' ) ); ?>" />
	</div>

	<br class="clear" />

	<p class="description mail-tag"><label><?php echo esc_html( __( 'This field should not be used on the Mail tab.', 'cf7mls' ) ); ?></label>
	</p>
</div>
	<?php
}
