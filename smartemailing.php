<?php

/*
Plugin Name: 	Smart Emailing Fluent Form Integration
Plugin URI:
Description: 	Integrate Smart Emailing with Fluent Form
Version: 		1.0.0
Author: 		Jan Ranostaj
Author URI: 	https://ranostaj.com
Text Domain: 	smartemailing
License: 		GPLv2 or later
License URI:	http://www.gnu.org/licenses/gpl-2.0.html

*/

define( 'SMART_EMAILING_PATH', plugin_dir_path( __FILE__ ) );

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

require_once( SMART_EMAILING_PATH . 'FluentFormIntegration.php' );
require_once( SMART_EMAILING_PATH . 'Api.php' );


if ( ! is_plugin_active( 'fluentform/fluentform.php' ) ) {
	add_action( 'admin_notices', 'smartemailing_fluentform_not_installed_notice' );

	return;
}

function smartemailing_fluentform_not_installed_notice() {
	?>
    <div class="notice notice-error is-dismissible">
        <p><?php _e( 'Smart Emailing Fluent Form Integration requires Fluent Form plugin to be installed and activated.', 'smartemailing' ); ?></p>
    </div>
	<?php
}


// Initialize the integration
add_action( 'init', function () {
	new \SmartEmailing\FluentFormIntegration( wpFluentForm() );
} );

