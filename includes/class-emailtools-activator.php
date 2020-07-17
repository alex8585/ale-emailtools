<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *

 */
class Emailtools_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		set_transient( 'emt_terms_and_conditions', true, 60 );
		if ( !class_exists( 'WooCommerce' ) ) {
            set_transient( 'emt_required_wc', true, 30 );
        } 
	}

    
}
