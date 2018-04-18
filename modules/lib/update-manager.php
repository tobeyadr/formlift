<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class FormLift_Update_Manager
{
    function __construct()
    {
        add_action( 'plugins_loaded' , array('FormLift_Update_Manager', 'update'));
    }

    public static function update(){

        //check if installing
        if ( get_option( FORMLIFT_VERSION_KEY ) === false ){
            
            update_option( FORMLIFT_VERSION_KEY, FORMLIFT_VERSION );
            update_option( FORMLIFT_SETTINGS, FormLift_Defaults::$style_defaults);
            update_option( FORMLIFT_STYLE, FormLift_Defaults::$settings_defaults);
            FormLift_Notice_Manager::get_notices_from_formlift();

        } elseif ( get_option( FORMLIFT_VERSION_KEY ) < 7000) { //upgrade to 7.0.0
            
            /* bring version Id up to date */
            //wp_die('still doing updates');
            update_option( FORMLIFT_VERSION_KEY, 7000);
            /* migrate global style settings */
            $old_options = get_option(FORMLIFT_SETTINGS);
            $new_options = self::migrate_style_settings($old_options);
            update_option(FORMLIFT_STYLE, $new_options);
            /* migrate global settings */
            $new_options = self::migrate_global_settings($old_options);
            update_option(FORMLIFT_SETTINGS, $new_options);

            $forms = get_all_formlift_forms();

            foreach ($forms as $form) {
            /* migrate form local style settings */
                $old_options = get_post_meta($form->ID, FORMLIFT_SETTINGS, true);
                $new_options = self::migrate_style_settings($old_options);
                update_post_meta($form->ID, FORMLIFT_STYLE, $new_options);
            /* migrate Form Level glob settings*/
                $new_options = self::migrate_form_settings($old_options);
                update_post_meta($form->ID, FORMLIFT_SETTINGS, $new_options);
            /* parse html */
                $html = get_post_meta($form->ID, 'form_code', true);
                $form_bits = For_Form_Builder::parse_html($html, $form->ID);
            /* save form bits */
                update_post_meta($form->ID, FORMLIFT_FIELDS, $form_bits);

            /* save post url */
                if (get_transient( 'formlift_post_url' )){   
                    $form_settings = get_post_meta($form->ID, FORMLIFT_SETTINGS, true);
                    $form_settings['post_url'] = get_transient( 'formlift_post_url' );
                    update_post_meta($form->ID, FORMLIFT_SETTINGS, $form_settings);
                    delete_transient( 'formlift_post_url' );
                }
            }
        } if (get_option(FORMLIFT_VERSION_KEY) < 7010) { //upgrade to 7.0.10
            
            /* bring version Id up to date */
            update_option(FORMLIFT_VERSION_KEY, 7010);
            /* migrate global style settings */
            $current_options = get_option(FORMLIFT_SETTINGS);
            $current_options['password_error'] = FormLift_Defaults::$settings_defaults['password_error'];
            update_option(FORMLIFT_SETTINGS, $current_options);

        } if (get_option(FORMLIFT_VERSION_KEY) < 7011) { //upgrade to 7.0.10
            
            update_option(FORMLIFT_VERSION_KEY, 7011);
            $current_style_options = get_option(FORMLIFT_STYLE);
            $current_style_options['formlift_field .formlift_input::-webkit-input-placeholder'] = $current_style_options['formlift_field input::-webkit-input-placeholder'];
            $current_style_options['formlift_field .formlift_input:-moz-placeholder'] = $current_style_options['formlift_field input:-moz-placeholder'];
            $current_style_options['formlift_field .formlift_input::-moz-placeholder'] = $current_style_options['formlift_field input::-moz-placeholder'];
            $current_style_options['formlift_field .formlift_input::-ms-input-placeholder'] = $current_style_options['formlift_field input:-ms-input-placeholder'];
            update_option(FORMLIFT_STYLE, $current_style_options);
            
        } if (get_option(FORMLIFT_VERSION_KEY) < 7100 ){
            update_option(FORMLIFT_VERSION_KEY, 7100);
            FormLift_Submissions_Page::create_table();
            //FormLift_Notice_Manager::get_notices_from_formlift( $user->user_login, $user );
        } if (get_option(FORMLIFT_VERSION_KEY) < 7102) {
            /* add the default css options for radio and checkboxs */
            update_option(FORMLIFT_VERSION_KEY, 7102);
            $current_options = get_option(FORMLIFT_STYLE);
            foreach (FormLift_Defaults::$style_defaults as $class => $attributes) {
                if (!isset($current_options[$class]) || empty($current_options[$class])){
                    $current_options[$class] = $attributes;
                }
            }
            update_option(FORMLIFT_STYLE, $current_options);
        } if ( get_option( FORMLIFT_VERSION_KEY ) < 7300) {
            /* add the default css options for radio and checkboxs */
            update_option(FORMLIFT_VERSION_KEY, 7300);
            $current_options = get_option(FORMLIFT_SETTINGS);

            $current_options['logged_in_error'] = 'You must be logged in to submit this form.';

            update_option(FORMLIFT_SETTINGS, $current_options);
        
        } if ( get_option( FORMLIFT_VERSION_KEY ) < 7306 ) {
            //remove all special fields from web forms and place them in the post meta instead
            update_option( FORMLIFT_VERSION_KEY, 7306 );
            $forms = get_all_formlift_forms();
            foreach ( $forms as $form ) {
                $fields = get_post_meta( $form->ID, FORMLIFT_FIELDS, true );

                if ( isset( $fields['inf_form_xid'] ) ){
                    update_post_meta( $form->ID, 'inf_form_xid', $fields[ 'inf_form_xid' ]['value'] );
                    unset( $fields['inf_form_xid'] );
                }

                if ( isset( $fields['inf_form_name'] ) ){
                    update_post_meta( $form->ID, 'inf_form_name', $fields[ 'inf_form_name' ]['value'] );
                    unset( $fields['inf_form_name'] );
                }

                if ( isset( $fields['infusionsoft_version'] ) ){
                    update_post_meta( $form->ID, 'infusionsoft_version', $fields[ 'infusionsoft_version' ]['value'] );
                    unset( $fields['infusionsoft_version'] );
                }

                update_post_meta( $form->ID, FORMLIFT_FIELDS, $fields );
            }
        } if ( get_option( FORMLIFT_VERSION_KEY ) < 7307 ) {
            update_option( FORMLIFT_VERSION_KEY, 7307 );
            $current_style_options = get_option(FORMLIFT_STYLE);
            $current_style_options['formlift_radio_option_container .formlift_radio_label_container']['font-size'] = $current_style_options['formlift_label']['font-size'];
            $current_style_options['formlift_button']['border-style'] = 'solid';
            update_option( FORMLIFT_STYLE, $current_style_options );
        } if ( get_option( FORMLIFT_VERSION_KEY ) < 7308 ) {
            update_option(FORMLIFT_VERSION_KEY, 7308);
            $current_options = get_option(FORMLIFT_SETTINGS);
            $current_options['please_wait_text'] = 'Please Wait...';
            update_option(FORMLIFT_SETTINGS, $current_options);
        } if (get_option( FORMLIFT_VERSION_KEY ) < 7309 ) {
            update_option(FORMLIFT_VERSION_KEY, 7309);
            $current_style_options = get_option(FORMLIFT_STYLE);
            $current_style_options['formlift_field .formlift_input::placeholder'] = $current_style_options['formlift_field .formlift_input::-webkit-input-placeholder'];
            $current_style_options['formlift_field .formlift_input:-ms-input-placeholder'] = $current_style_options['formlift_field .formlift_input::-webkit-input-placeholder'];
            $current_style_options['formlift_field .formlift_input::-ms-input-placeholder'] = $current_style_options['formlift_field .formlift_input::-webkit-input-placeholder'];
            update_option(FORMLIFT_STYLE, $current_style_options);
        }
    }

    public static function migrate_style_settings($old_options)
    {
        $new_array = array(
            'formlift_button'        => array(
                'background-color'  => $old_options['button_color'],
                'border-color'      => $old_options['button_border_color'],
                'color'             => $old_options['button_font_color'],
                'width'             => $old_options['button_width'],
                'border-width'      => $old_options['button_border_width'],
                'border-radius'     => $old_options['button_border_radius'],
                'padding-top'       => $old_options['button_padding'],
                'padding-bottom'    => $old_options['button_padding'],
                'font-family'       => $old_options['button_font_family'],
                'font-size'         => $old_options['button_font_size'],
                'font-weight'       => $old_options['button_font_weight'],
                'transition'        => '0.4s'
            ),
            'formlift_button::hover' => array(
                'background-color'  => $old_options['button_hover_color'],
                'border-color'      => $old_options['button_border_hover_color'],
                'color'             => $old_options['button_font_hover_color'],
                'transition'        => '0.4s'
            ),
            'formlift_button_container' => array(
                'text-align'        => $old_options['button_align']
            ),
            'formlift_radio_option_container' => array(
                'display'           => $old_options['input_placeholder_color']
            ),
            'formlift_field .formlift_input::-webkit-input-placeholder' => array(
                'color'             => $old_options['input_placeholder_color']
            ),
            'formlift_field .formlift_input:-moz-placeholder' => array(
                'color'             => $old_options['input_placeholder_color']
            ),
            'formlift_field .formlift_input::-moz-placeholder' => array(
                'color'             => $old_options['input_placeholder_color']
            ),
            'formlift_field .formlift_input:-ms-input-placeholder' => array(
                'color'             => $old_options['input_placeholder_color']
            ),
            'formlift_input' => array(
                'background-color'  => $old_options['input_background_color'],
                'border-color'      => $old_options['input_border_color'],
                'color'             => $old_options['input_font_color'],
                'width'             => $old_options['input_width'],
                'border-width'      => $old_options['input_border_width'],
                'border-radius'     => $old_options['input_border_radius'],
                'padding'           => $old_options['input_padding'],
                'height'            => $old_options['input_height'],
                'font-family'       => $old_options['input_font_family'],
                'font-size'         => $old_options['input_font_size'],
                'font-weight'       => $old_options['input_font_weight']
            ),
            'formlift_input::focus' => array(
                'background-color'  => $old_options['input_background_color'],
                'border-color'      => $old_options['input_focus_color'],
                'color'             => $old_options['input_font_color'],
                'transition'        => '0.4s'
            ),
            'formlift-infusion-form' => array(
                'background-color'  => '',
                'border-color'      => '',
                'padding-top'       => $old_options['form_padding_top'],
                'padding-right'     => $old_options['form_padding_right'],
                'padding-bottom'    => $old_options['form_padding_bottom'],
                'padding-left'      => $old_options['form_padding_left'],
                'width'             => $old_options['form_width']
            ),
            'formlift_field' => array(
                'background-color'  => '',
                'border-color'      => '',
                'padding-top'       => $old_options['field_padding_top'],
                'padding-right'     => $old_options['field_padding_right'],
                'padding-bottom'    => $old_options['field_padding_bottom'],
                'padding-left'      => $old_options['field_padding_left']
            ),
            'formlift_label' => array(
                'font-family'       => $old_options['label_font_family'],
                'font-size'         => $old_options['label_font_size'],
                'font-weight'       => $old_options['label_font_weight'],
                'color'             => $old_options['label_font_color'],
                'margin-bottom'     => '10px'
            ),
            'formlift-error-response' => array(
                'background-color'  => $old_options['error_background_color'],
                'border-color'      => $old_options['error_border_color'],
                'border-width'      => $old_options['error_border_width'],
                'border-radius'     => $old_options['error_border_radius'],
                'padding'           => '10px',
                'color'             => $old_options['error_font_color'],
                'font-family'       => $old_options['error_font_family'],
                'font-size'         => $old_options['error_font_size'],
                'font-weight'       => '400'
            )
        );

        return $new_array;
    }

    public static function migrate_global_settings($old_options)
    {
        $new_array = array(
            'invalid_data_error'   => 'Something is wrong with your submission.',
            'required_error'       => 'This field is required.',
            'email_error'          => 'Please enter a valid email.',
            'phone_error'          => 'Please enter a valid phone number.',
            'date_error'           => 'Please enter a valid date.',
            'captcha_error'        => 'Please verify that you are not a robot.',
            'url_error'            => 'Please enter a valid url.',

            'tracking_method'      => $old_options['tracking_method'],

            'infusionsoft_app_name'     => $old_options['infusionsoft_app_name'],
            'infusionsoft_api_key'      => $old_options['infusionsoft_api_key'],

            'google_site_key'      => $old_options['google_captcha_key'],
            'google_secret_key'    => '',

            'formlift_license_key'      => $old_options['formlift_license_key']
        );

        return $new_array;
    }

    public static function migrate_form_settings($old_options)
    {
        $new_array = array(
            'invalid_data_error'    => '',
            'required_error'        => '',
            'email_error'           => '',
            'phone_error'           => '',
            'date_error'            => '',
            'captcha_error'         => '',
            'url_error'             => '',

            'tracking_method'       => $old_options['tracking_method'],

            'display_condition'     => $old_options['display_condition'],
            'start_date'            => $old_options['start_date'],
            'start_time'            => $old_options['start_time'],
            'end_date'              => $old_options['end_date'],
            'end_time'              => $old_options['end_time'],
            'max_submissions'       => $old_options['max_submissions'],
            'no_display_msg'        => $old_options['no_display_msg'],

            'infusionsoft_form_id'  => $old_options['infusionsoft_form_id']
        );

        return $new_array;
    }
}

