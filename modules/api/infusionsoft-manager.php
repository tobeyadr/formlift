<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class FormLift_Infusionsoft_Manager {
	/**
	 * @var $app FormLift_App
	 */
	static $app;

	const AUTH_URI = 'https://formlift.net/oauth/';

	public static function app_init() {
		$hostname    = ( get_option( 'Oauth_App_Domain' ) ) ? get_option( 'Oauth_App_Domain', '' ) : get_formlift_setting( 'infusionsoft_app_name' );
		static::$app = new FormLift_App( $hostname );
	}

	public static function connect() {

		if ( isset( $_POST[ FORMLIFT_SETTINGS ]['activate_OAuth'] ) ) {

			if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
				wp_die( 'You do not have permission to perform this action.' );
			}

			// nonce located in settings-page.php
			if ( ! isset( $_POST['formlift_options'] ) || ! wp_verify_nonce( $_POST['formlift_options'], 'update' ) ) {
				wp_die( 'Nonce verification failed.' );
			}

			$pass = wp_generate_password( 8, false, false );

			set_transient( 'formlift_auth_pass', $pass, 60 * 5 );

			$params = array(
				'redirectUri'     => admin_url( 'edit.php?post_type=infusion_form&page=formlift_settings_page' ),
				'OauthConnect'    => true,
				'OauthClientPass' => $pass
			);

			$query = http_build_query( $params );

			wp_redirect( static::AUTH_URI . '?' . $query ); //Send To OAuth Page...
			die();
		}
	}

	public static function refresh_oauth() {
		if ( isset( $_POST[ FORMLIFT_SETTINGS ]['refresh_OAuth'] ) ) {
			static::$app->refreshTokens();
		}
	}

	public static function listen_for_tokens() {
		if ( isset( $_REQUEST['OauthClientPass'] ) ) {

			if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
				wp_die( 'Unauthorized access.' );
			}

			$pass = get_transient( 'formlift_auth_pass' );

			if ( empty( $pass ) ) {
				wp_die( 'Could not verify server authorization for ' . site_url() . '. Please Try Again.' );
			} elseif ( $pass != $_REQUEST['OauthClientPass'] ) {
				wp_die( 'Incorrect password. Please try again...' );
			}

			$app_domain    = sanitize_text_field( $_REQUEST['appDomain'] );
			$access_token  = sanitize_text_field( $_REQUEST['access_token'] );
			$refresh_token = sanitize_text_field( $_REQUEST['refresh_token'] );
			$expires_in    = sanitize_text_field( $_REQUEST['expires_in'] );

			static::$app = new FormLift_App( $app_domain );
			static::$app->updateAndSaveTokens( $access_token, $refresh_token, $expires_in );

			update_option( 'Oauth_App_Domain', $app_domain );

			update_option( 'oauth_last_status', 'Authorized token at ' . date( 'Y/m/d H:i:s' ) . ' for app ' . static::$app->getHostname() );

			FormLift_Notice_Manager::add_notice( 'connection_success', array(
				'is_dismissable' => true,
				'is_premium'     => 'both',
				'is_specific'    => true,
				'type'           => 'notice-success',
				'html'           => 'Your application has been successfully connected!'
			) );

			delete_transient( 'formlift_auth_pass' );

			wp_safe_redirect( admin_url( '/edit.php?post_type=infusion_form&page=formlift_settings_page' ) );
			die();
		}
	}

	public static function refreshTokens( $token ) {

		$params = array(
			'OauthToken'   => $token,
			'OauthRefresh' => 'refresh_token',
			'sourceURI'    => get_site_url(),
		);

		$response = wp_remote_post( static::AUTH_URI, array(
			'timeout'   => 20,
			'sslverify' => true,
			'body'      => $params
		) );

		if ( is_wp_error( $response ) ) {
			FormLift_Notice_Manager::add_error( 'api_error', "Something went wrong: " . $response->get_error_message() );

			return null;
		}

		$decodedResponse = json_decode( $response['body'], true );

		if ( isset( $decodedResponse['error'] ) ) {

			FormLift_Notice_Manager::add_notice( 'oauth_error', array(
				'is_dismissable' => true,
				'is_premium'     => 'both',
				'is_specific'    => false,
				'type'           => 'notice-error',
				'html'           => 'Something went wrong automatically re-authenticating your connection to Infusionsoft, Please Re-Authenticate your app in the settings or use the Legacy API. Error Response: ' . $decodedResponse['error']
			) );

			update_option( 'oauth_last_status', 'Failed to refresh Token at: ' . date( 'Y/m/d H:i:s' ) );

			return null;

		} else if ( ! isset( $decodedResponse['access_token'] ) ) {

			FormLift_Notice_Manager::add_notice( 'oauth_error', array(
				'is_dismissable' => true,
				'is_premium'     => 'both',
				'is_specific'    => false,
				'type'           => 'notice-error',
				'html'           => 'Something went wrong automatically re-authenticating your connection to Infusionsoft, Please Re-Authenticate your app in the settings or use the Legacy API.'
			) );

			update_option( 'oauth_last_status', 'Failed to refresh Token at: ' . date( 'Y/m/d H:i:s' ) );

			return null;
		}

		update_option( 'oauth_last_status', 'Refreshed Token at ' . date( 'Y/m/d H:i:s' ) . ' for app ' . static::$app->getHostname() );

		return $decodedResponse;
	}

	public static function disconnect_app() {
		if ( isset( $_POST[ FORMLIFT_SETTINGS ]['disconnect_Oauth'] ) && is_user_logged_in() && is_admin() ) {
			if ( static::$app->hasTokens() ) {
				static::$app->deleteTokens();
				update_option( 'oauth_last_status', 'Disconnected App ' . static::$app->getHostname() . ' at ' . date( 'Y/m/d H:i:s' ) );
				delete_option( 'Oauth_App_Domain' );
			}
		}
	}

	/**
	 * Get a webform list
	 *
	 * @return object|WP_Error
	 */
	public static function getWebForms() {
		$response = static::$app->restRequest( 'rest/v2/webforms', [
			'filter'    => 'webform_type==legacy',
			'order_by'  => 'name,asc',
			'page_size' => 999
		], 'GET' );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		return $response->webforms;
	}

	/**
	 * Get the HTML of a webform
	 *
	 * @param $webformId
	 *
	 * @return string
	 */
	public static function getWebFormHtml( $webformId ) {

		// since the content-type of the response will be HTML, the response array will be returned
		$response = static::$app->restRequest( 'rest/v2/webforms/' . $webformId, [], 'GET' );

		return wp_remote_retrieve_body( $response );
	}

	/**
	 * Upload a file
	 *
	 * @param $contactId
	 * @param $fileName
	 * @param $base64encoded
	 *
	 * @return object|WP_Error
	 */
	public static function fileUpload( $contactId, $fileName, $base64encoded ) {

		if ( ! $contactId ) {
			return new WP_Error( 'no_contact_id', 'No contact id provided' );
		}

		return static::$app->restRequest( 'rest/v1/files', [
			'file_name'        => $fileName,
			'file_data'        => $base64encoded,
			'is_public'        => false,
			'file_association' => 'CONTACT',
			'contact_id'       => $contactId,
		] );
	}

	/**
	 * Achieve a goal
	 *
	 * @param $contactId
	 * @param $callName
	 *
	 * @return object|WP_Error
	 */
	public static function achieveGoal( $contactId, $callName ) {
		return static::$app->restRequest( 'rest/v2/automations/goals/achieve', [
			'integration' => 'FormLift',
			'callName'    => $callName,
			'contactId'   => $contactId,
		] );
	}

	/**
	 * Get custom fields
	 *
	 * @return mixed
	 */
	public static function getCustomFields() {
		$response = static::$app->restRequest( 'rest/v2/contacts/model', [], 'GET' );
		return $response->custom_fields;
	}

	/**
	 * update a contact
	 *
	 * @param $contactId
	 * @param $data
	 *
	 * @return object|WP_Error
	 */
	public static function updateContact( $contactId, $data ) {

		$update_mask = implode( ',' , array_keys( $data ) );

		return static::$app->restRequest( 'rest/v2/contacts/' . $contactId . '?update_mask=' . $update_mask , $data, 'PATCH' );
	}

	/**
	 * Add a contact
	 *
	 * @param  array  $data per docs
	 * @param  string  $fields csv of fields, "name,email,phone,..."
	 * @param  string  $duplicate_option Email, EmailAndName, EmailAndNameAndCompany
	 *
	 * @return object|WP_Error
	 */
	public static function addContact( array $data, string $fields = '', string $duplicate_option = 'Email' ) {

		$query = array_filter( [ 'duplicate_option' => $duplicate_option, 'fields' => $fields ] );

		return static::$app->restRequest( add_query_arg( $query, 'rest/v2/contacts' ) , $data, 'post' );
	}
}

add_action( 'plugins_loaded', array( 'FormLift_Infusionsoft_Manager', 'listen_for_tokens' ) );
add_action( 'plugins_loaded', array( 'FormLift_Infusionsoft_Manager', 'connect' ) );
add_action( 'plugins_loaded', array( 'FormLift_Infusionsoft_Manager', 'refresh_oauth' ) );
add_action( 'plugins_loaded', array( 'FormLift_Infusionsoft_Manager', 'disconnect_app' ) );
add_action( 'formlift_loaded', array( 'FormLift_Infusionsoft_Manager', 'app_init' ) );
