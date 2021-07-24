<?php
if ( ! class_exists( 'NjtCF7MLSI18n' ) ) {
	class NjtCF7MLSI18n {

		public function __construct() {
			$this->doHooks();
		}

		private function doHooks() {
			add_action( 'plugins_loaded', array( $this, 'cf7mlsLoadTextdomain' ) );
		}
		public function cf7mlsLoadTextdomain() {
			load_plugin_textdomain( 'cf7mls', false, plugin_basename( CF7MLS_PLUGIN_DIR ) . '/languages/' );
		}
	}
	new NjtCF7MLSI18n();
}
