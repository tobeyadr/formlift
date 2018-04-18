<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Form_Settings_Meta_Box
{

    public static function add_meta_box()
    {
        add_meta_box(
            "formlift_form_settings",
            "Form Settings",
            array('Form_Settings_Meta_Box', "create_form_settings_box"),
            "infusion_form"
        );
    }

    /**
     * Add the settings panel to the post type
     *
     * @param $post
     */
    public static function create_form_settings_box( $post )
    {

        wp_nonce_field( 'formlift_saving_settings', 'formlift_settings_nonce' );

        $meta_box = new FormLift_Options_Skin( $post->ID, 'form_settings' );

        $meta_box->add_section( 'formlift_import_settings', 'Import Form', FormLift_Settings::import_settings());
        $meta_box->add_section( 'formlift_form_settings', 'Submission Settings', FormLift_Settings::submission_settings() );
        $meta_box->add_section( 'formlift_error_settings', 'Messages', FormLift_Settings::error_settings() );
        //$meta_box->add_section( 'formlift_tracking_settings', 'Tracking Settings', FormLift_Settings::tracking_settings() );

        $meta_box = apply_filters( 'formlift_settings_widget', $meta_box );

        echo $meta_box;
    }

    public static function remove_import_form_setting_pre_import( $fields )
    {
        //$screen = get_current_screen();
        if ( ! isset( $_GET['post'] ) )
            unset( $fields[ "infusionsoft_form_id" ] );

        return $fields;
    }
}

add_action( 'add_meta_boxes', array( 'Form_Settings_Meta_Box', 'add_meta_box' ) );
add_filter( 'formlift_import_settings', array( 'Form_Settings_Meta_Box', 'remove_import_form_setting_pre_import' ) );