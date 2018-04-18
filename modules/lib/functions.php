<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function get_formlift_setting( $id, $default = false )
{
    $option = get_option( FORMLIFT_SETTINGS );

    if ( isset( $option[$id] ) ){
        return $option[$id];
    } else {
        return $default;
    }
}

function formlift_option_exists( $option_name ){
    $option = get_option( $option_name );
    return !empty( $option );
}

function formlift_get_auto_fill_query_extension( $fieldsOrInt )
{
    if ( is_int( $fieldsOrInt ) ){
        $form = new FormLift_Form( $fieldsOrInt );
        $fields = $form->get_fields();
    } else if ( is_array( $fieldsOrInt ) ) {
        $fields = $fieldsOrInt;
    } else {
        $form = new FormLift_Form( intval( $fieldsOrInt ) );
        $fields = $form->get_fields();
    }

    $params = "?";
    if (empty($fields))
        return "";
    foreach ( $fields as $fieldname => $field_params ) {
        if ( strpos( $fieldname, '_field_' ) || strpos( $fieldname, '_other_' ) )
        {
            $params .= $fieldname . "=" . "~Contact." . substr( $fieldname, strrpos( $fieldname, "_" ) + 1 ) . "~";
            $params .= "&";
        } elseif ( strpos( $fieldname, '_custom_' ) ) {
            $params .= $fieldname . "=" . "~Contact." . substr( $fieldname, strrpos( $fieldname, "_" ) ) . "~";
            $params .= "&";
        }
    }
    return $params;
}

function get_all_formlift_forms()
{
    $args = array('numberposts' => '-1', 'post_type'=>'infusion_form', 'post_status' => 'any' );
    $postslist = get_posts( $args );
    return $postslist;
}

function get_formlift_form_drop_down()
{
    $forms = get_all_formlift_forms(); //array(Post)
    $list = array();
    foreach ($forms as $form){
        $list[$form->ID] = $form->post_title;
    }
    return $list;
}


add_action( 'plugins_loaded', 'formlift_update_web_form_list' );

function formlift_update_web_form_list()
{
    if ( isset( $_POST[ FORMLIFT_SETTINGS ]['formlift_update_webform_list'] ) ){
        _formlift_update_web_form_list();
    }
}

function _formlift_update_web_form_list()
{
    try {
        $array = FormLift_Infusionsoft_Manager::getWebForms();
        update_option( 'formlift_web_forms', $array );
		FormLift_Notice_Manager::add_success( "refresh_success", "Successfully retrieved new web forms." );
        return $array;
    } catch ( Exception $e ){
        FormLift_Notice_Manager::add_notice( 'oauth_error', array(
            'is_dismissable' => true,
            'is_premium' => 'both',
            'is_specific' => false,
            'type' => 'notice-error',
            'html' => 'Something went wrong pulling the webform list. Try manually refreshing your connection in the settings. If the problem persists copy the following and send it to <a href="mailto:info@formlift.net">info@formlift.net</a>. Error Code: '.$e->getMessage()
        ));
        return array();
    }
}

function formlift_get_infusionsoft_webforms()
{
          
    $forms = get_option( 'formlift_web_forms', array() );

    if ( !empty( $forms ) )
        return $forms;
    else
        return _formlift_update_web_form_list();
}

function get_formlift_html( $id )
{
    try {
        return FormLift_Infusionsoft_Manager::getWebFormHtml( $id );
    } catch ( Exception $e ) {
        FormLift_Notice_Manager::add_notice( 'oauth_error', array(
            'is_dismissable' => true,
            'is_premium' => 'both',
            'is_specific' => false,
            'type' => 'notice-error',
            'html' => 'Something went wrong pulling the webform html. Try manually refreshing your connection in the settings. If the problem persists copy the following and send it to <a href="mailto:info@formlift.net">info@formlift.net</a>. Error Code: '.$e->getMessage()
        ));
        return '';
    }
}

function formlift_is_connected()
{
    if ( FormLift_Infusionsoft_Manager::$app->hasTokens() ) {
        return true;
    } else {
        $app_name = get_formlift_setting('infusionsoft_app_name');
        $key = get_formlift_setting('infusionsoft_api_key');
        return !empty($app_name) && !empty($key);
    }
}

function formlift_convert_to_time_picker_usuable( $date_string )
{
    if ( $date_string == date( 'Y-m-d', strtotime( $date_string ) ) ){
        return "new Date( '{$date_string}' )";
    } else {
        return "'$date_string'";
    }
}

function get_formlift_field_types()
{
	$options = array(
		'Standard' => array(
			'hidden'        => 'Hidden Field',
			'text'          => 'Text Field',
			'textarea'      => 'Text Area',
			'name'          => 'Name',
			'email'         => 'Email',
			'date'          => 'Date Picker',
			'phone'         => 'Phone Number',
			'number'        => 'Whole Number',
			'postal_code'   => 'Postal Code',
			'zip_code'      => 'Zip Code',
			'website'       => 'Website',
			'select'        => 'Dropdown',
			'listbox'       => 'List Box',
			'radio'         => 'Radio Buttons',
			'checkbox'      => 'Checkbox'
		),
		'Additional' => array(
			'GDPR'      => 'GDPR Compliance',
			'password'  => 'Password',
			'custom'    => 'Custom HTML',
			'button'    => 'Submit Button'
		)
	);

	$options = apply_filters( "formlift_add_field_types", $options );

	return $options;
}

function get_formlift_field_type_name( $name )
{
//    echo $name;
    $types = get_formlift_field_types();
    $type = array_column( $types, $name );
    return array_pop( $type );
}
