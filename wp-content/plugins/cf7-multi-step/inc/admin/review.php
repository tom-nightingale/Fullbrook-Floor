<?php
if ( ! class_exists( 'NjtCF7MLSReview' ) ) {
	class NjtCF7MLSReview {

		public function __construct() {
			$this->doHooks();
		}

		private function doHooks() {
			add_action( 'wp_ajax_cf7mls_save_review', array( $this, 'cf7mls_save_review' ) );

			$option = get_option( 'cf7mls_review' );
			if ( time() >= (int) $option && $option !== '0' ) {
				add_action( 'admin_notices', array( $this, 'give_review' ) );
			}
		}

		public function checkNonce( $nonce ) {
			if ( ! wp_verify_nonce( $nonce, 'cf7mls_review_nonce' ) ) {
				wp_send_json_error( array( 'status' => 'Wrong nonce validate!' ) );
				exit();
			}
		}

		public function hasField( $field, $request ) {
			return isset( $request[ $field ] ) ? sanitize_text_field( $request[ $field ] ) : null;
		}

		public function cf7mls_save_review() {
			if ( count( $_REQUEST ) ) {
				$nonce = $this->hasField( 'nonce', $_REQUEST );
				$field = $this->hasField( 'field', $_REQUEST );

				$this->checkNonce( $nonce );

				if ( $field == 'later' ) {
					update_option( 'cf7mls_review', time() + 3 * 60 * 60 * 24 ); // After 3 days show
				} elseif ( $field == 'alreadyDid' ) {
					update_option( 'cf7mls_review', 0 );
				}
				wp_send_json_success();
			}
			wp_send_json_error( array( 'message' => 'Update fail!' ) );
		}

		public function give_review() {
			if ( function_exists( 'get_current_screen' ) ) {
				if ( get_current_screen()->id == 'dashboard' || get_current_screen()->id == 'toplevel_page_wpcf7' || strpos( get_current_screen()->id, 'contact_page_wpcf7' ) !== false || get_current_screen()->id == 'upload' || get_current_screen()->id == 'plugins' ) {
					?>
				<div class="notice notice-success is-dismissible" id="njt-cf7mls-review">
					<h3 style="margin: 1em 0;"><?php _e( 'Give Multi Step for Contact Form 7 a review', 'cf7mls' ); ?></h3>
					<p>
						<?php _e( 'Thank you for choosing Multi Step for Contact Form 7. We hope you love it. Could you take a couple of seconds posting a nice review to share your happy experience?', 'cf7mls' ); ?>
					</p>
					<p>
						<?php _e( 'We will be forever grateful. Thank you in advance ;)', 'cf7mls' ); ?>
					</p>
					<p>
						<a href="javascript:;" data="rateNow" class="button button-primary" style="margin-right: 5px"><?php _e( 'Rate now', 'cf7mls' ); ?></a>
						<a href="javascript:;" data="later" class="button" style="margin-right: 5px"><?php _e( 'Later', 'cf7mls' ); ?></a>
						<a href="javascript:;" data="alreadyDid" class="button"><?php _e( 'Already did', 'cf7mls' ); ?></a>
					</p>
				</div>
				<script>
					jQuery(document).ready(function () {
					jQuery("#njt-cf7mls-review a").on("click", function () {
						var thisElement = this;
						var fieldValue = jQuery(thisElement).attr("data");
						var proLink = "https://codecanyon.net/item/contact-form-7-multistep/reviews/15232990";
						var freeLink = "https://wordpress.org/support/plugin/cf7-multi-step/reviews/?filter=5#new-post";
						var hidePopup = false;
						if (fieldValue == "rateNow") {
						window.open(freeLink, "_blank");
						} else {
						hidePopup = true;
						}

						jQuery
						.ajax({
							dataType: 'json',
							url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
							type: "post",
							data: {
							action: "cf7mls_save_review",
							field: fieldValue,
							nonce: '<?php echo wp_create_nonce( 'cf7mls_review_nonce' ); ?>',
							},
						})
						.done(function (result) {
							if (result.success) {
							if (hidePopup == true) {
								jQuery("#njt-cf7mls-review").hide("slow");
							}
							} else {
							console.log("Error", result.message);
							if (hidePopup == true) {
								jQuery("#njt-cf7mls-review").hide("slow");
							}
							}
						})
						.fail(function (res) {
							console.log(res.responseText);

							if (hidePopup == true) {
							jQuery("#njt-cf7mls-review").hide("slow");
							}
						});
					});
					});
				</script>
					<?php
				}
			}
		}
	}
	new NjtCF7MLSReview();
}
