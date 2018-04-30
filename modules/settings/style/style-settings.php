<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class FormLift_Style_Settings
{
    public static function get_advanced_css()
    {
        $fields = array(
            new FormLift_Style_Field(FORMLIFT_INPUT, 'formlift_button', 'transition', 'Button Hover Fade Time'),
            new FormLift_Style_Field(FORMLIFT_INPUT, 'formlift_button:hover', 'transition', 'Button Hover Fade Time 2'),
            new FormLift_Style_Field(FORMLIFT_INPUT, 'formlift_input:focus', 'transition', 'Input Focus Color Fade Time'),
            new FormLift_Style_Field(FORMLIFT_INPUT, 'formlift_check_style', 'transition', 'Radio Fade In/Out Time'),

            /* changed from input to .formlift_input */
            new FormLift_Style_Field(FORMLIFT_COLOR, 'formlift_field .formlift_input::placeholder', 'color', 'Webkit Placeholder Color'),
            new FormLift_Style_Field(FORMLIFT_COLOR, 'formlift_field .formlift_input::-ms-input-placeholder', 'color', 'Microsoft Edge Placeholder Color'),
            new FormLift_Style_Field(FORMLIFT_COLOR, 'formlift_field .formlift_input:-ms-input-placeholder', 'color', 'Internet Explorer Placeholder Color')

        );

        return apply_filters('formlift_get_advanced_css', $fields);
    }

    /**
     * Returns a list of formlift_Fields for the Button Settings Section
     *
     * @return array
     */
    public static function get_button_css()
    {
        $fields = array(
            new FormLift_Style_Field(FORMLIFT_COLOR, 'formlift_button', 'background-color', 'Color'),
            new FormLift_Style_Field(FORMLIFT_COLOR, 'formlift_button', 'border-color', 'Border Color'),
            new FormLift_Style_Field(FORMLIFT_COLOR, 'formlift_button', 'color', 'Font Color'),
            new FormLift_Style_Field(FORMLIFT_COLOR, 'formlift_button:hover', 'background-color', 'Hover Color'),
            new FormLift_Style_Field(FORMLIFT_COLOR, 'formlift_button:hover', 'border-color', 'Hover Border Color'),
            new FormLift_Style_Field(FORMLIFT_COLOR, 'formlift_button:hover', 'color', 'Hover Font Color'),
            new FormLift_Style_Field(FORMLIFT_INPUT, 'formlift_button', 'width', 'Width'),
            new FormLift_Style_Field(FORMLIFT_SELECT, 'formlift_button', 'border-style', 'Border Style', array(
                'none' => 'No Border',
                'solid' => 'Solid Line',
                'dotted' => 'Dotted Line',
                'dashed' => 'Dashed Line',
                'double' => 'Double Line',
                'groove' => 'Grouve Bevel',
                'ridge' => 'Ridge Bevel',
                'outset' => 'Outset Bevel',
                'inset' => 'Inset Bevel'
            )),
            new FormLift_Style_Field(FORMLIFT_INPUT, 'formlift_button', 'border-width', 'Border Width'),
            new FormLift_Style_Field(FORMLIFT_INPUT, 'formlift_button', 'border-radius', 'Border Radius'),
            new FormLift_Style_Field(FORMLIFT_INPUT, 'formlift_button', 'padding-top', 'Top Padding'),
            new FormLift_Style_Field(FORMLIFT_INPUT, 'formlift_button', 'padding-bottom', 'Bottom Padding'),
            new FormLift_Style_Field(FORMLIFT_INPUT, 'formlift_button', 'font-family', 'Font Family'),
            new FormLift_Style_Field(FORMLIFT_INPUT, 'formlift_button', 'font-size', 'Font Size'),
            new FormLift_Style_Field(FORMLIFT_INPUT, 'formlift_button', 'font-weight', 'Font Weight'),
            new FormLift_Style_Field(FORMLIFT_INPUT, 'formlift_button', 'box-shadow', 'Box Shadow'),
            new FormLift_Style_Field(FORMLIFT_SELECT, 'formlift_button_container', 'text-align', 'Alignment', array(
                'left' => 'Left',
                'center' => 'Center',
                'right' => 'Right'
            ))
        );

        return apply_filters('formlift_get_button_css', $fields);
    }

    /**
     * Returns a list of FormLift_Style_Fields for the input settings section
     *
     * @return array
     */
    public static function get_input_css()
    {
        $fields = array(
            new FormLift_Style_Field(FORMLIFT_COLOR, 'formlift_input', 'background-color', 'Background Color'),
            new FormLift_Style_Field(FORMLIFT_COLOR, 'formlift_input', 'border-color', 'Border Color'),
            new FormLift_Style_Field(FORMLIFT_COLOR, 'formlift_input', 'color', 'Font Color'),
            new FormLift_Style_Field(FORMLIFT_COLOR, 'formlift_input:focus', 'background-color', 'Focus Background-color'),
            new FormLift_Style_Field(FORMLIFT_COLOR, 'formlift_input:focus', 'border-color', 'Focus Border Color'),
            NEW FormLift_Style_Field(FORMLIFT_COLOR, 'formlift_input:focus', 'color', 'Focus Font Color'),
            new FormLift_Style_Field(FORMLIFT_SELECT, 'formlift_input', 'border-style', 'Border Style', array(
                'none' => 'No Border',
                'solid' => 'Solid Line',
                'dotted' => 'Dotted Line',
                'dashed' => 'Dashed Line',
                'double' => 'Double Line',
                'groove' => 'Grouve Bevel',
                'ridge' => 'Ridge Bevel',
                'outset' => 'Outset Bevel',
                'inset' => 'Inset Bevel'
            )),
            new FormLift_Style_Field(FORMLIFT_INPUT, 'formlift_input', 'border-width', 'Border Width'),
            new FormLift_Style_Field(FORMLIFT_INPUT, 'formlift_input', 'border-radius', 'Border Radius'),
            new FormLift_Style_Field(FORMLIFT_INPUT, 'formlift_input', 'width', 'Input Width'),
            new FormLift_Style_Field(FORMLIFT_INPUT, 'formlift_input', 'height', 'Input Height'),
            new FormLift_Style_Field(FORMLIFT_INPUT, 'formlift_input', 'padding', 'Text Padding'),
            new FormLift_Style_Field(FORMLIFT_INPUT, 'formlift_input', 'font-family', 'Font Family'),
            new FormLift_Style_Field(FORMLIFT_INPUT, 'formlift_input', 'font-size', 'Font Size'),
            new FormLift_Style_Field(FORMLIFT_INPUT, 'formlift_input', 'font-weight', 'Font Weight'),
            new FormLift_Style_Field(FORMLIFT_INPUT, 'formlift_input', 'box-shadow', 'Box Shadow'),
        );

        return apply_filters('formlift_get_input_css', $fields);
    }

    public static function get_radio_checkbox_css()
    {
        $fields = array(
            new FormLift_Style_Field(FORMLIFT_INPUT, 'formlift_radio_option_container', 'padding', 'Option Spacing'),
            new FormLift_Style_Field(FORMLIFT_INPUT, 'formlift_radio_option_container .formlift_radio_label_container', 'font-size', 'Radio Option Font Size'),
//            new FormLift_Style_Field(FORMLIFT_INPUT, 'formlift_check_style', 'height', 'Radio Button Height'),
//            new FormLift_Style_Field(FORMLIFT_INPUT, 'formlift_check_style', 'width', 'Radio Button Width'),
            new FormLift_Style_Field(FORMLIFT_INPUT, 'formlift_check_style, .formlift_radio_label_container', '--rb-size', 'Radio Button Size'),
            new FormLift_Style_Field(FORMLIFT_INPUT, 'formlift_radio_label_container .formlift_is_checkbox ~ .formlift_check_style:after', 'font-size', 'Check Mark Size'),
            new FormLift_Style_Field(FORMLIFT_SELECT, 'formlift_check_style', 'border-style', 'Border Style', array(
                'none' => 'No Border',
                'solid' => 'Solid Line',
                'dotted' => 'Dotted Line',
                'dashed' => 'Dashed Line',
                'double' => 'Double Line',
                'groove' => 'Grouve Bevel',
                'ridge' => 'Ridge Bevel',
                'outset' => 'Outset Bevel',
                'inset' => 'Inset Bevel'
            )),
            new FormLift_Style_Field(FORMLIFT_INPUT, 'formlift_check_style', 'border-width', 'Radio Button Border Size'),
            new FormLift_Style_Field(FORMLIFT_COLOR, 'formlift_check_style', 'background-color', 'Background Color'),
            new FormLift_Style_Field(FORMLIFT_COLOR, 'formlift_check_style', 'border-color', 'Border Color'),
            new FormLift_Style_Field(FORMLIFT_COLOR, 'formlift_radio_label_container:hover input ~ .formlift_check_style', 'background-color', 'Hover Background Color'),
            new FormLift_Style_Field(FORMLIFT_COLOR, 'formlift_radio_label_container:hover input ~ .formlift_check_style', 'border-color', 'Hover Border Color'),
            new FormLift_Style_Field(FORMLIFT_COLOR, 'formlift_radio_label_container input:checked ~ .formlift_check_style', 'background-color', 'Checked Background Color'),
            new FormLift_Style_Field(FORMLIFT_COLOR, 'formlift_radio_label_container input:checked ~ .formlift_check_style', 'border-color', 'Checked Border Color'),
            new FormLift_Style_Field(FORMLIFT_COLOR, 'formlift_radio_label_container .formlift_radio ~ .formlift_check_style:after', 'background-color', 'Checked Dot Color'),
            new FormLift_Style_Field(FORMLIFT_COLOR, 'formlift_radio_label_container .formlift_is_checkbox ~ .formlift_check_style:after', 'color', 'Checkmark Color'),
            new FormLift_Style_Field(FORMLIFT_SELECT, 'formlift_radio_option_container', 'display', 'Radio List Display Type', array(
                'block' => 'List',
                'inline-block' => 'Inline'
            ))
        );

        return apply_filters('formlift_get_radio_checkbox_css', $fields);
    }

    /**
     * Returns a list of FormLift_Style_Fields for the form settings section
     *
     * @return array
     */
    public static function get_form_css()
    {
        $fields = array(
            new FormLift_Style_Field(FORMLIFT_COLOR, 'formlift-infusion-form', 'background-color', 'Background Color'),
            new FormLift_Style_Field(FORMLIFT_COLOR, 'formlift-infusion-form', 'border-color', 'Border Color'),
            new FormLift_Style_Field(FORMLIFT_MULTI, 'formlift-infusion-form', 'padding', 'Padding', array(), array(
                '&#8679;' => 'padding-top',
                '&#8680;' => 'padding-right',
                '&#8681;' => 'padding-bottom',
                '&#8678;' => 'padding-left',
            )),
            new FormLift_Style_Field(FORMLIFT_INPUT, 'formlift-infusion-form', 'width', 'Width')
        );

        return apply_filters('formlift_get_form_css', $fields);
    }

    /**
     * Returns a list of FormLift_Style_Fields for the field settings section
     *
     * @return array
     */
    public static function get_field_css()
    {
        $fields = array(
            new FormLift_Style_Field(FORMLIFT_COLOR, 'formlift_field', 'background-color', 'Background Color'),
            new FormLift_Style_Field(FORMLIFT_COLOR, 'formlift_field', 'border-color', 'Border Color'),
            new FormLift_Style_Field(FORMLIFT_MULTI, 'formlift_field', 'padding', 'Padding', array(), array(
                '&#8679;' => 'padding-top',
                '&#8680;' => 'padding-right',
                '&#8681;' => 'padding-bottom',
                '&#8678;' => 'padding-left',
            ))
        );

        return apply_filters('formlift_get_field_css', $fields);
    }

    /**
     * Returns a list of FormLift_Style_Fields for the field settings section
     *
     * @return array
     */
    public static function get_label_css()
    {
        $fields = array(
            new FormLift_Style_Field(FORMLIFT_COLOR, 'formlift_label', 'color','Font Color'),
            new FormLift_Style_Field(FORMLIFT_INPUT, 'formlift_label', 'font-family', 'Font Family'),
            new FormLift_Style_Field(FORMLIFT_INPUT, 'formlift_label', 'font-size', 'Font Size'),
            new FormLift_Style_Field(FORMLIFT_INPUT, 'formlift_label', 'font-weight', 'Font Weight'),
            new FormLift_Style_Field(FORMLIFT_INPUT, 'formlift_label', 'margin-bottom', 'Margin Bottom')
        );

        return apply_filters('formlift_get_label_css', $fields);
    }

    /**
     * Returns a list of FormLift_Style_Fields for the error settings section
     *
     * @return array
     */
    public static function get_error_css()
    {
        $fields = array(
            new FormLift_Style_Field(FORMLIFT_COLOR, 'formlift-error-response', 'background-color', 'Background Color'),
            new FormLift_Style_Field(FORMLIFT_COLOR, 'formlift-error-response', 'border-color', 'Border Color'),
            new FormLift_Style_Field(FORMLIFT_INPUT, 'formlift-error-response', 'border-radius', 'Border Radius'),
            new FormLift_Style_Field(FORMLIFT_INPUT, 'formlift-error-response', 'border-width', 'Border Width'),
            new FormLift_Style_Field(FORMLIFT_INPUT, 'formlift-error-response', 'padding', 'Text Padding'),
            new FormLift_Style_Field(FORMLIFT_COLOR, 'formlift-error-response', 'color', 'Font Color'),
            new FormLift_Style_Field(FORMLIFT_INPUT, 'formlift-error-response', 'font-family', 'Font Family'),
            new FormLift_Style_Field(FORMLIFT_INPUT, 'formlift-error-response', 'font-size', 'Font Size'),
            new FormLift_Style_Field(FORMLIFT_INPUT, 'formlift-error-response', 'font-weight', 'Font Weight')
        );

        return apply_filters('formlift_get_error_css', $fields);
    }

    /**
     * Function that saves the default settings
     */
    public static function save_settings()
    {
        if ( isset( $_POST['formlift_options'] ) && wp_verify_nonce( $_POST['formlift_options'], 'update' ) && current_user_can('manage_options') ){
            $options = $_POST[ FORMLIFT_STYLE ];
            $options = apply_filters( 'formlift_sanitize_style_settings', $options );
            update_option( FORMLIFT_STYLE, $options);
        } 
    }

    public static function clean_settings( $options )
    {
	    foreach ($options as $class => $attributes) {
		    foreach ($attributes as $meta_key => $value) {
			    $options[$class][$meta_key] = sanitize_text_field( stripslashes( $value ) );
		    }
	    }

	    return $options;
    }

    public static function export_settings()
    {
	    if ( isset( $_POST[FORMLIFT_SETTINGS]['export_style'] ) && wp_verify_nonce( $_POST['formlift_options'], 'update' ) && current_user_can('manage_options') ){
		    $filename = "formlift_style_settings_".date("Y-m-d_H-i",time() );

		    header("Content-type: text/plain");
		    //header("Content-disposition: csv" . date("Y-m-d") . ".csv");
		    header( "Content-disposition: attachment; filename=".$filename.".txt");
		    // do not cache the file
		    //header('Pragma: no-cache');
		    //header('Expires: 0');

		    $file = fopen('php://output', 'w');

		    fputs($file, json_encode( get_option( FORMLIFT_STYLE ) ) );

		    // output each row of the data

		    fclose($file);

		    exit();
	    }
    }

    public static function import_settings()
    {
	    if ( isset( $_POST[FORMLIFT_SETTINGS]['import_style'] ) && wp_verify_nonce( $_POST['formlift_options'], 'update' ) && current_user_can('manage_options') ){

		    $options = stripslashes( $_POST[ FORMLIFT_SETTINGS ]['import_style_settings'] );

		    if ( empty( $options ) ){
		    	FormLift_Notice_Manager::add_error('bad_import', "No settings to import..." );
		    	return;
		    }

		    $options = apply_filters( 'formlift_sanitize_style_settings', json_decode( $options, true ) );
		    update_option( FORMLIFT_STYLE, $options );

	    }
    }
}

add_filter( 'formlift_sanitize_style_settings',  array( 'FormLift_Style_Settings', 'clean_settings' ) );
add_action( 'init' , array( 'FormLift_Style_Settings', 'save_settings' ) );
add_action( 'init' , array( 'FormLift_Style_Settings', 'export_settings' ) );
add_action( 'init' , array( 'FormLift_Style_Settings', 'import_settings' ) );