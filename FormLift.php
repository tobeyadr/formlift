<?php
/*
 * Plugin Name: FormLift
 * Description: The Ultimate Web Form Solution for WordPress and Infusionsoft. Style your web forms, create personalized pages, and create epic automation with them too.
 * Version: 7.4.0
 * Author: Adrian Tobey
 * Plugin URI: https://formlift.net
 * Author URI: https://formlift.net/blog
 *
 * Copyright (c) Training Business Pros 2016
 * 25 Lesmill Road, Toronto, Ontario, July 2016
 * License: GPLv2
 *
 * For Support Please send emails to info@formlift.net or visit https://formlift.net/contact-us.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define( 'FORMLIFT_VERSION', '7.4.0' );
define( 'FORMLIFT_CSS_VERSION', '7.4.0.1' );
define( 'FORMLIFT_JS_VERSION', '7.4.0' );
define( 'FORMLIFT_VERSION_KEY', 'formlift_version' );
define( 'FORMLIFT_SETTINGS', 'formlift_form_settings' );
define( 'FORMLIFT_STYLE', 'formlift_style_settings');
define( 'FORMLIFT_FIELDS', 'formlift_form_bits' );


/* Load Modules */

if ( version_compare( PHP_VERSION, '5.6.0', '>=' ) ):

	include dirname( __FILE__ ) . "/modules/modules-loader.php" ;

    do_action( 'formlift_loaded' );

    if ( !is_ssl() ){
        FormLift_Notice_Manager::add_error( "ssl_error", "Some of FormLift's features are deactivated because you do NOT have an SSL. You should invest in an SSL to keep you user's information safe and to stay compliant with GDPR and HIPAA. If your are with a good host, it's likely that you can install a <b>Let's Encrypt SSL Certificate</b> by yourself for free! We appologize for any inconvenience." );
    } else {
        FormLift_Notice_Manager::remove_notice( "ssl_error" );
    }


    add_filter( 'single_template', 'formlift_form_template_hack' );

    function formlift_form_template_hack( $page_template )
    {
        if ( get_post_type( get_the_ID() ) == 'infusion_form'  ) {
            $page_template = dirname( __FILE__ ) . '/templates/formlift-single.php';
        }
        return $page_template;
    }

    register_activation_hook( __FILE__, 'formlift_set_plugin_defaults' );

    function formlift_set_plugin_defaults()
    {
        FormLift_Defaults::set_defaults_on_activation();
    }

else:
	function formlift_php_error_notice(){
        ?>
        <div class='notice notice-error'><p>Uh oh... your <strong>PHP level must be 5.6 or greater</strong> for FormLift to activate. Please update
        your PHP level to 5.6 or better (7+ is recommended)! You can use 
        <a href='https://wordpress.org/plugins/php-compatibility-checker/'>this plugin</a> to determine if it is safe 
        for you to upgrade your PHP.</p></div>
        <?php
    }
    add_action('admin_notices', 'formlift_php_error_notice');
endif;