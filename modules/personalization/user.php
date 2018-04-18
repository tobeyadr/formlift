<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Class FormLift_User
 */
class FormLift_User
{
    /**
     * @var $instance FormLift_User
     */
    var $ID;
    var $attributes;

	static $instance;

	function __construct()
	{
        if ( ! isset( $_COOKIE[ 'FORMLIFT_ID' ] ) ){
            $this->ID = uniqid( 'formlift_session_', TRUE );

            $expiresInDays = get_formlift_setting( 'time_to_live', 30 );

            setcookie( 'FORMLIFT_ID', $this->ID, time() + $expiresInDays * 24 * 60 * 60, COOKIEPATH, COOKIE_DOMAIN, true, true );
	        set_transient( $this->ID, array(), $expiresInDays * 24 * HOUR_IN_SECONDS );
        } else {
            $this->ID = sanitize_text_field( $_COOKIE[ 'FORMLIFT_ID' ] );
        }

        $attributes = get_transient( $this->ID );
        $this->attributes = ( !empty( $attributes ) )? $attributes : array();
	}

	function set_user_data( $field, $data )
	{
	    if ( is_ssl() )
            $this->attributes[$field] = $data;
	}

    function remove_user_data( $field )
	{
        unset ( $this->attributes[$field] );
	}

    /* exists just because it has a better name */
    function get_user_data( $field, $default = false )
    {
        if ( isset( $this->attributes[ $field ] ) && is_ssl() ){
            return $this->attributes[ $field ] ;
        } else if ( is_user_logged_in() ) {
            return $this->get_user_data_from_wp( $field , $default );
        } else {
            return $default;
        }
    }

    function addImpression( $formID )
    {
        $this->set_user_data( $formID . '-impression', $formID );
        $this->update();
    }

    function addSubmission( $formID )
    {
        $this->set_user_data( $formID . '-submission', $formID );
        $this->update();
    }

    function hasImpression( $formID )
    {
        return $this->get_user_data( $formID . '-impression' ) == $formID;
    }

    function hasSubmission( $formID )
    {
        return $this->get_user_data( $formID . '-submission' ) == $formID;
    }

    function update()
    {
        if ( get_formlift_setting( "disable_session_storage" ) )
            return;

        $expiresInDays = get_formlift_setting( 'time_to_live', 30 );
        set_transient( $this->ID, $this->attributes, $expiresInDays * 24 * HOUR_IN_SECONDS );
    }

	function get_user_data_from_wp( $field, $default )
	{
	        /* because I'm lazy I'll only serve certain fields*/
        $user = wp_get_current_user();

        if ("inf_field_Email" == $field)
            return $user->user_email;
        elseif ("inf_field_FirstName" == $field)
            return $user->user_firstname;
        elseif ("inf_field_LastName" == $field)
            return $user->user_lastname;
        elseif ("inf_field_Username" == $field)
            return $user->user_login;
        else {
            //added for memberium support
            return apply_filters( 'formlift_get_user_data', $default, $field );
        }

	}

    function sanitize_headers()
    {
        if ( get_formlift_setting( 'disable_utm_removal' ) || ! is_ssl() )
            return;

        $do = false;
        // check for doing a redirect
        if ( !empty( $_GET ) && !isset( $_GET['form_action'] ) && !isset( $_GET['utm_formlift'] ))
        {
            if ( preg_match('/inf_custom/', $_SERVER['QUERY_STRING'] ) || preg_match('/inf_other/', $_SERVER['QUERY_STRING']) || preg_match('/contactId/', $_SERVER['QUERY_STRING']) || preg_match('/inf_field/', $_SERVER['QUERY_STRING'] ) ){
                $do = true;
            }
        }

        if ( !$do )
            return;

        $filters = get_formlift_setting( 'exclude_from_utm_removal' );
        $filters = explode( PHP_EOL, $filters );
        $filters = array_map('trim', $filters);

        foreach ( $_GET as $key => $value ) {
            $unfiltered = urldecode( $value );
            /* special case for emails from the $_GET*/
            if ( preg_match( '/inf_field_Email[2-3]?/', $key ) )
                $filtered = str_replace( ' ', '+', $unfiltered );
            else
                $filtered = $unfiltered;

            $this->attributes[ sanitize_text_field( $key )] = sanitize_textarea_field( $filtered ) ;
            //remove special case for inf_contact_key
            if ( ( preg_match( '/inf_custom/', $key ) || preg_match( '/inf_other/', $key ) || preg_match( '/inf_field/', $key ) ) && !in_array( $key, $filters ) ){
                unset( $_GET[$key] );
            }
        }

        $uri = preg_replace( "/\?.*/", '', $_SERVER['REQUEST_URI'] );
        //redirect to a clean version of the URL to protect user data.

        if ( isset( $_GET["contactId"]) ){
            $this->set_user_data( "contactId", intval( $_GET["contactId"] ) );
        }

        $this->update();

        if ( empty( $_GET ) ){
            wp_redirect( $uri );
            die();
        } else {
            $_GET[ 'utm_formlift' ] = 'safe';
            wp_redirect( $uri . "?" . http_build_query( $_GET ) );
            die();
        }
    }

	public static function db_extend( $field )
    {
        if ( strpos( $field, "_" ) && ! strpos( $field, "inf_" ) ){
            return "inf_custom" . $field;
        } else if ( !strpos( $field, "inf_" ) && !empty( $field ) ){
            return "inf_field_" . $field;
        } else {
            return $field;
        }
    }

	public static function display_field( $atts, $content )
    {
        global $FormLiftUser;

        $atters = shortcode_atts(array(
            'name' => '',
            'value' => '',
            'default' => '',
            'id' => '',
	        'everything' => false
        ), $atts);

        if ( $atters['everything'] ){
        	return $FormLiftUser;
        }

        if ( !empty( $atters['id'] ) ){
            $atters['name'] = $atters['id'];
        }

	    $atters[ 'name' ] = self::db_extend( $atters['name'] );
	    //return $atters['name'];

	    if ( $content ) {
            if ( ! empty( $atters['name'] ) ){
                $val = $FormLiftUser->get_user_data( $atters['name'] );
                if ( empty( $val ) ){
	                return '';
                } elseif ( !empty( $atters['value'] ) && $val != $atters[ 'value' ] ){
                    return '';
                }
            }

            preg_match_all('/%%[\w\d]+%%/', $content, $matches);
            $actual_matches = $matches[0];

            foreach ($actual_matches as $pattern) {
                $field = str_replace('%%', '', $pattern);

                $value = $FormLiftUser->get_user_data( self::db_extend( $field ) );

                if ( empty( $value ) ) {
                    $value = '{No Data}';
                }

                $content = preg_replace('/' . $pattern . '/', $value, $content);
            }
            $content = do_shortcode( $content );
        } else {
            $val = $FormLiftUser->get_user_data( $atters['name'] );
            if ( empty( $val ) ){
                return '';
            } elseif ( !empty($atters['value']) && $val != $atters['value'] ){
                return '';
            }
            $content = $val;
        }

        return $content;
    }

    public function __toString() {
		$content = "<table><tbody>";
        foreach ( $this->attributes as $attribute => $value ){
            $content .= "<tr><td>{$attribute}</td><td>{$value}</td></tr>";
        }
        $content.= "</table></tbody>";

        return $content;
    }

    public static function delete_sessions()
    {
        global $wpdb;
        return $wpdb->query(
            $wpdb->prepare("
                DELETE FROM $wpdb->options
                 WHERE option_name LIKE %s
             ", "%_formlift_session_%" ) );
    }
}

$FormLiftUser = new FormLift_User();

add_action( 'plugins_loaded', array( $FormLiftUser, 'sanitize_headers' ), 1 );

add_shortcode( 'infusion_field', array( 'FormLift_User', 'display_field' ) );
add_shortcode( 'formlift_user', array( 'FormLift_User', 'display_field' ) );
add_shortcode( 'formlift_data', array( 'FormLift_User', 'display_field' ) );


function formlift_delete_transients()
{
    if ( !is_user_logged_in() || !is_admin() || ! isset( $_POST[FORMLIFT_SETTINGS]['delete_all_sessions'] ) || ! wp_verify_nonce($_POST['delete_sessions_nonce'],'formlift_delete_sessions') )
        return;

    if ( FormLift_User::delete_sessions() ){
        FormLift_Notice_Manager::add_success('sessions-deleted', 'Sessions deleted successfully!');
    } else {
        FormLift_Notice_Manager::add_error('sessions-deleted', 'Something went wrong deleteing the sessions...');
    }

}

add_action('init', 'formlift_delete_transients');