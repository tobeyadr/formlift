<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class FormLift_Infusionsoft_Manager
{
    /**
     * @var $app FormLift_App
     */
	static $app;

	const AUTH_URI = 'https://oauth.formlift.net';

	public static function app_init()
	{
		$hostname = ( get_option( 'Oauth_App_Domain' ) ) ? get_option( 'Oauth_App_Domain', '' ) : get_formlift_setting( 'infusionsoft_app_name' );
		static::$app = new FormLift_App( $hostname );
	}

	public static function connect()
	{
		if ( isset( $_POST[ FORMLIFT_SETTINGS ]['activate_OAuth'] ) )
        {
        	$pass = wp_generate_password( 8, false );

        	set_transient( 'formlift_auth_pass', $pass, 60*5 );

           	$params = array(
        		'redirectUri' => get_site_url(null, '/wp-admin/edit.php?post_type=infusion_form&page=default_settings_page' ),
        		'OauthConnect' => true,
        		'OauthClientPass' => $pass
        	);

        	$query = http_build_query( $params );

            wp_redirect( static::AUTH_URI.'?'.$query ); //Send To OAuth Page...
            die();
        }
	}

	public static function refresh_oauth()
	{
		if ( isset( $_POST[ FORMLIFT_SETTINGS ]['refresh_OAuth'] ) )
        {
        	static::$app->refreshTokens();
        } 
	}

	public static function listen_for_tokens()
	{
		if ( isset( $_REQUEST['OauthClientPass'] ) ){

			$pass = get_transient( 'formlift_auth_pass' );

			if ( empty( $pass ) ){
				wp_die( 'Could not verify server authorization for '. site_url() . '. Please Try Again.');
			}

			elseif ( $pass != base64_decode( $_REQUEST['OauthClientPass'] ) ){
			    wp_die( 'Incorrect password. Please try again...' );
			}

			static::$app = new FormLift_App(  base64_decode( $_REQUEST['appDomain'] ) );
	        static::$app->updateAndSaveTokens(  base64_decode( $_REQUEST['access_token'] ),  base64_decode( $_REQUEST['refresh_token'] ),  base64_decode( $_REQUEST['expires_in'] ) );

	        update_option( 'Oauth_App_Domain',  base64_decode( $_REQUEST['appDomain'] ) );

	        update_option( 'oauth_last_status', 'Authorized token at ' . date('Y/m/d H:i:s') . ' for app ' . static::$app->getHostname() );

	        FormLift_Notice_Manager::add_notice( 'connection_success', array(
	        	'is_dismissable' => true,
	            'is_premium' => 'both',
	            'is_specific' => true,
	            'type' => 'notice-success',
	            'html' => 'Your application has been successfully connected!'
		    ));

	        delete_transient( 'formlift_auth_pass' );

	        wp_redirect( get_site_url(null, '/wp-admin/edit.php?post_type=infusion_form&page=formlift_settings_page' ) );
	        die();
		} 
	}

	public static function refreshTokens( $token )
	{
		$params = array(
			'OauthToken' => base64_encode( $token ),
			'OauthRefresh' => 'refresh_token'
		);

        $response = wp_remote_post( static::AUTH_URI, array(
            'timeout' => 20,
            'sslverify' => true,
            'body' => $params
        ) );

        if ( is_wp_error( $response ) ) {
            FormLift_Notice_Manager::add_error( 'api_error', "Something went wrong: " . $response->get_error_message( ) );
            return array(
                'access_token' => '',
                'refresh_token' => '',
                'expires_in' => 0
            );
        }

        $decodedResponse = json_decode( $response['body'], true );

        if ( isset( $decodedResponse['error'] ) ){
        	
        	FormLift_Notice_Manager::add_notice( 'oauth_error', array(
		    	'is_dismissable' => true,
	            'is_premium' => 'both',
	            'is_specific' => false,
	            'type' => 'notice-error',
	            'html' => 'Something went wrong automatically re-authenticating your connection to Infusionsoft, Please Re-Authenticate your app in the settings or use the Legacy API.'
		    ));

		    update_option( 'oauth_last_status', 'Failed to refresh Token at: ' . date('Y/m/d H:i:s') . '. Error: ' . $decodedResponse['error'] );

		    return array(
		    	'access_token' => '',
		    	'refresh_token' => '',
		    	'expires_in' => 0
		    );

      	} else if ( !isset( $decodedResponse['access_token'] ) ) {

        	FormLift_Notice_Manager::add_notice( 'oauth_error', array(
		    	'is_dismissable' => true,
	            'is_premium' => 'both',
	            'is_specific' => false,
	            'type' => 'notice-error',
	            'html' => 'Something went wrong automatically re-authenticating your connection to Infusionsoft, Please Re-Authenticate your app in the settings or use the Legacy API.'
		    ));

		    update_option( 'oauth_last_status', 'Failed to refresh Token at: ' . date('Y/m/d H:i:s') );

		    return array(
		    	'access_token' => '',
		    	'refresh_token' => '',
		    	'expires_in' => 0
		    );
      	}

      	update_option( 'oauth_last_status', 'Refreshed Token at ' . date('Y/m/d H:i:s'). ' for app ' . static::$app->getHostname() );

        return $decodedResponse;
	}

	public static function disconnect_app()
	{
		if ( isset( $_POST[ FORMLIFT_SETTINGS ]['disconnect_Oauth'] ) && is_user_logged_in() && is_admin() ){
			if ( static::$app->hasTokens() ){
				static::$app->deleteTokens();
				update_option('oauth_last_status', 'Disconnected App '. static::$app->getHostname(). ' at '. date('Y/m/d H:i:s') );
				delete_option('Oauth_App_Domain');
			}
		}
	}

	/**
	 * @throws Exception
	 */
	public static function getWebForms()
	{

		return static::$app->send( 'WebFormService.getMap', array() );

	}

	/**
	 * @param $webformId int
	 * @return array();
	 * @throws Exception
	 */
	public static function getWebFormHtml( $webformId )
	{
		$params = array(
            (int) $webformId
        );

        return static::$app->send( "WebFormService.getHTML", $params);
	}

	/**
	 * @param $contactId int
	 * @param $fileName string
	 * @param $base64encoded string
	 * @return array();
	 * @throws Exception
	 */
	public static function fileUpload( $contactId, $fileName, $base64encoded )
	{
		if ($contactId === null) {
            $params = array(
                $fileName,
                $base64encoded
            );
        } else {
            $params = array(
                (int) $contactId,
                $fileName,
                $base64encoded
            );
        }

        return static::$app->send( "FileService.uploadFile", $params );
	}
}

add_action( 'plugins_loaded', array( 'FormLift_Infusionsoft_Manager', 'listen_for_tokens' ) );
add_action( 'plugins_loaded', array( 'FormLift_Infusionsoft_Manager', 'connect' ) );
add_action( 'plugins_loaded', array( 'FormLift_Infusionsoft_Manager', 'refresh_oauth' ) );
add_action( 'plugins_loaded', array( 'FormLift_Infusionsoft_Manager', 'disconnect_app') );
add_action( 'formlift_loaded', array( 'FormLift_Infusionsoft_Manager', 'app_init' ) );
